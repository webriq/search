--------------------------------------------------------------------------------
-- table: search_content                                                      --
--------------------------------------------------------------------------------

CREATE TABLE "search_content"
(
    "id"                SERIAL                      NOT NULL,
    "type"              CHARACTER VARYING           NOT NULL,
    "contentId"         INTEGER                     NOT NULL,
    "published"         BOOLEAN                     NOT NULL    DEFAULT TRUE,
    "publishedFrom"     TIMESTAMP WITH TIME ZONE    NULL        DEFAULT NULL,
    "publishedTo"       TIMESTAMP WITH TIME ZONE    NULL        DEFAULT NULL,
    "allAccess"         BOOLEAN                     NOT NULL    DEFAULT TRUE,
    "accessGroups"      INTEGER ARRAY               NOT NULL    DEFAULT CAST( ARRAY[] AS INTEGER ARRAY ),
    "accessUsers"       INTEGER ARRAY               NOT NULL    DEFAULT CAST( ARRAY[] AS INTEGER ARRAY ),

    PRIMARY KEY ( "id" ),
    UNIQUE ( "type", "contentId" )
);

--------------------------------------------------------------------------------
-- table: search                                                              --
--------------------------------------------------------------------------------

CREATE TABLE "search"
(
    "searchContentId"   INTEGER             NOT NULL,
    "locale"            CHARACTER VARYING   NOT NULL,
    "title"             CHARACTER VARYING   NOT NULL,
    "keywords"          CHARACTER VARYING   NOT NULL,
    "description"       CHARACTER VARYING   NOT NULL,
    "content"           TEXT                NOT NULL,
    "vector"            tsvector            NOT NULL    DEFAULT CAST( '' AS tsvector ),

    PRIMARY KEY ( "searchContentId", "locale" )
);

CREATE INDEX "search_data_idx" ON "search" USING gist (( "title" || ' ' || "keywords" || ' ' || "description" || ' ' || "content" ));
CREATE INDEX ON "search" USING gist ( "vector" );

--------------------------------------------------------------------------------
-- function: search_content_update(varchar, int, bool, timestampwtz, timestampwtz, bool, int[], int[])
--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION "search_content_update"(
    "p_type"            CHARACTER VARYING,
    "p_content_id"      INTEGER,
    "p_published"       BOOLEAN                     DEFAULT TRUE,
    "p_published_from"  TIMESTAMP WITH TIME ZONE    DEFAULT NULL,
    "p_published_to"    TIMESTAMP WITH TIME ZONE    DEFAULT NULL,
    "p_all_access"      BOOLEAN                     DEFAULT TRUE,
    "p_access_groups"   INTEGER ARRAY               DEFAULT CAST( ARRAY[] AS INTEGER ARRAY ),
    "p_access_users"    INTEGER ARRAY               DEFAULT CAST( ARRAY[] AS INTEGER ARRAY )
)
     RETURNS INTEGER
    VOLATILE
         SET search_path FROM CURRENT
    LANGUAGE plpgsql
          AS $$
DECLARE
    "v_result"  INTEGER;
BEGIN

    SELECT "id"
      INTO "v_result"
      FROM "search_content"
     WHERE "type"       = "p_type"
       AND "contentId"  = "p_content_id"
     LIMIT 1;

     IF FOUND THEN

        UPDATE "search_content"
           SET "published"      = "p_published",
               "publishedFrom"  = "p_published_from",
               "publishedTo"    = "p_published_to",
               "allAccess"      = "p_all_access",
               "accessGroups"   = "p_access_groups",
               "accessUsers"    = "p_access_users"
         WHERE "id"             = "v_result";

     ELSE

        INSERT INTO "search_content"( "type",
                                      "contentId",
                                      "published",
                                      "publishedFrom",
                                      "publishedTo",
                                      "allAccess",
                                      "accessGroups",
                                      "accessUsers" )
                             VALUES ( "p_type",
                                      "p_content_id",
                                      "p_published",
                                      "p_published_from",
                                      "p_published_to",
                                      "p_all_access",
                                      "p_access_groups",
                                      "p_access_users" );

        "v_result" = currval( 'search_content_id_seq' );

     END IF;

     RETURN "v_result";

END $$;

--------------------------------------------------------------------------------
-- function: search_update(int, varchar, varchar, varchar, varchar, text)     --
--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION "search_update"(
    "p_search_content_id"   INTEGER,
    "p_locale"              CHARACTER VARYING,
    "p_title"               CHARACTER VARYING,
    "p_keywords"            CHARACTER VARYING,
    "p_description"         CHARACTER VARYING,
    "p_content"             TEXT
)
     RETURNS BOOLEAN
    VOLATILE
         SET search_path FROM CURRENT
    LANGUAGE plpgsql
          AS $$
BEGIN

    UPDATE "search"
       SET "title"              = "p_title",
           "keywords"           = "p_keywords",
           "description"        = "p_description",
           "content"            = "p_content"
     WHERE "searchContentId"    = "p_search_content_id"
       AND "locale"             = "p_locale";

     IF NOT FOUND THEN

        INSERT INTO "search"( "searchContentId",
                              "locale",
                              "title",
                              "keywords",
                              "description",
                              "content" )
                     VALUES ( "p_search_content_id",
                              "p_locale",
                              "p_title",
                              "p_keywords",
                              "p_description",
                              "p_content" );

     END IF;

     RETURN TRUE;

END $$;

--------------------------------------------------------------------------------
-- function: search_result(varchar, varchar, varchar, bool, int, int, int, int, bool, float, float, float, float, int, varchar, varchar, int, int, int, int, varchar)
--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION "search_result"(
    "p_locale"              CHARACTER VARYING,
    "p_query"               CHARACTER VARYING,
    "p_type_like"           CHARACTER VARYING   DEFAULT '%',
    "p_all"                 BOOLEAN             DEFAULT FALSE,
    "p_user_id"             INTEGER             DEFAULT 0,
    "p_group_id"            INTEGER             DEFAULT 0,
    "p_is_admin"            BOOLEAN             DEFAULT FALSE,
    "p_limit"               INTEGER             DEFAULT 10,
    "p_offset"              INTEGER             DEFAULT 0,
    "p_cover_density"       BOOLEAN             DEFAULT TRUE,
    "p_weight_title"        FLOAT               DEFAULT 1.0,
    "p_weight_keywords"     FLOAT               DEFAULT 0.4,
    "p_weight_description"  FLOAT               DEFAULT 0.2,
    "p_weight_content"      FLOAT               DEFAULT 0.1,
    "p_normalization"       INTEGER             DEFAULT 36,
    "p_start_selection"     CHARACTER VARYING   DEFAULT '<mark>',
    "p_stop_selection"      CHARACTER VARYING   DEFAULT '</mark>',
    "p_max_words"           INTEGER             DEFAULT 35,
    "p_min_words"           INTEGER             DEFAULT 15,
    "p_short_word"          INTEGER             DEFAULT 3,
    "p_max_fragments"       INTEGER             DEFAULT 0,
    "p_fragment_delimiter"  CHARACTER VARYING   DEFAULT ' ... '
)
     RETURNS TABLE (
                 "id"               INTEGER,
                 "type"             CHARACTER VARYING,
                 "contentId"        INTEGER,
                 "locale"           CHARACTER VARYING,
                 "title"            CHARACTER VARYING,
                 "keywords"         CHARACTER VARYING,
                 "description"      CHARACTER VARYING,
                 "content"          TEXT,
                 "headline"         TEXT,
                 "rank"             FLOAT
             )
      STABLE
      CALLED ON NULL INPUT
         SET search_path FROM CURRENT
    LANGUAGE plpgsql
          AS $$
DECLARE
    "v_normalization"   INTEGER             DEFAULT 0;
    "v_user_id"         INTEGER             DEFAULT 0;
    "v_group_id"        INTEGER             DEFAULT 0;
    "v_rank_function"   CHARACTER VARYING;
    "v_type_like"       CHARACTER VARYING;
BEGIN

    "v_normalization" = "p_normalization" | 32;

    IF NOT "p_cover_density" THEN
        "v_normalization" = "v_normalization" & ~4;
    END IF;

    IF "p_cover_density" THEN
        "v_rank_function" = 'ts_rank_cd';
    ELSE
        "v_rank_function" = 'ts_rank';
    END IF;

    IF '' = "p_type_like" THEN
        "v_type_like" = '%';
    ELSE
        "v_type_like" = "p_type_like";
    END IF;

    IF "p_user_id" IS NOT NULL THEN
        "v_user_id" = "p_user_id";
    END IF;

    IF "p_group_id" IS NOT NULL THEN
        "v_group_id" = "p_group_id";
    END IF;

    RETURN QUERY EXECUTE format( '
                  SELECT "id",
                         "type",
                         "contentId",
                         "locale",
                         "title",
                         "keywords",
                         "description",
                         "content",
                         ts_headline(
                             "title" || '' '' ||
                             "keywords" || '' '' ||
                             "description" || '' '' ||
                             "content", "query", $15
                         ) AS "headline",
                         CAST( SQRT( 1 - ( 1 - "rank" ) ^ 2 ) AS FLOAT ) AS "rank"
                    FROM (
                         SELECT "id",
                                "type",
                                "contentId",
                                "locale",
                                "title",
                                "keywords",
                                "description",
                                "content",
                                %s( ARRAY[$13, $12, $11, $10]::float4[], "vector", "query", $14 ) AS "rank",
                                "query"
                           FROM "search"
                     INNER JOIN "search_content"
                             ON "search_content"."id" = "search"."searchContentId",
                                "_common"."search_to_query"( $1, $2 ) AS "query"
                          WHERE ( $4 OR "locale" IN ( $1, SUBSTRING( $1 FOR 2 ) ) )
                            AND "type" LIKE $3
                            AND ( $7 OR ( (
                                    "published" OR (
                                            ( "publishedFrom" IS NULL OR "publishedFrom" < CURRENT_TIMESTAMP )
                                        AND ( "publishedTo"   IS NULL OR "publishedTo"   > CURRENT_TIMESTAMP )
                                    )
                                ) AND (
                                        "allAccess"
                                     OR $6 = ANY ( "accessGroups" )
                                     OR $5 = ANY ( "accessUsers" )
                                ) ) )
                            AND "vector" @@ "query"
                       ORDER BY "rank" DESC
                          LIMIT $8
                         OFFSET $9
                    ) AS "search_result_tmp"
                ', "v_rank_function" )
          USING "p_locale",             -- $1
                "p_query",              -- $2
                "v_type_like",          -- $3
                "p_all",                -- $4
                "v_user_id",            -- $5
                "v_group_id",           -- $6
                "p_is_admin",           -- $7
                "p_limit",              -- $8
                "p_offset",             -- $9
                "p_weight_title",       -- $10
                "p_weight_keywords",    -- $11
                "p_weight_description", -- $12
                "p_weight_content",     -- $13
                "v_normalization",      -- $14
                format(                 -- $15
                    'StartSel=%I,StopSel=%I,'           ||
                    'MaxWords=%s,MinWords=%s,'          ||
                    'ShortWord=%s,HighlightAll=FALSE,'  ||
                    'MaxFragments=%s,FragmentDelimiter=%I',
                    "p_start_selection",
                    "p_stop_selection",
                    "p_max_words",
                    "p_min_words",
                    "p_short_word",
                    "p_max_fragments",
                    "p_fragment_delimiter"
                );                      -- $15

END $$;

--------------------------------------------------------------------------------
-- function: search_result_count(varchar, varchar, varchar, bool, int, int)   --
--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION "search_result_count"(
    "p_locale"              CHARACTER VARYING,
    "p_query"               CHARACTER VARYING,
    "p_type_like"           CHARACTER VARYING   DEFAULT '%',
    "p_all"                 BOOLEAN             DEFAULT FALSE,
    "p_user_id"             INTEGER             DEFAULT 0,
    "p_group_id"            INTEGER             DEFAULT 0,
    "p_is_admin"            BOOLEAN             DEFAULT FALSE
)
     RETURNS INTEGER
      STABLE
      CALLED ON NULL INPUT
         SET search_path FROM CURRENT
    LANGUAGE plpgsql
          AS $$
DECLARE
    "v_type_like"   CHARACTER VARYING;
    "v_result"      INTEGER             DEFAULT 0;
    "v_user_id"     INTEGER             DEFAULT 0;
    "v_group_id"    INTEGER             DEFAULT 0;
BEGIN

    IF '' = "p_type_like" THEN
        "v_type_like" = '%';
    ELSE
        "v_type_like" = "p_type_like";
    END IF;

    IF "p_user_id" IS NOT NULL THEN
        "v_user_id" = "p_user_id";
    END IF;

    IF "p_group_id" IS NOT NULL THEN
        "v_group_id" = "p_group_id";
    END IF;

    SELECT COUNT(*)
      INTO "v_result"
      FROM "search"
INNER JOIN "search_content"
        ON "search_content"."id" = "search"."searchContentId",
           "_common"."search_to_query"( "p_locale", "p_query" ) AS "query"
     WHERE ( "p_all" OR "locale" IN ( "p_locale", SUBSTRING( "p_locale" FOR 2 ) ) )
       AND "type" LIKE "v_type_like"
       AND ( "p_is_admin" OR ( (
               "published" OR (
                       ( "publishedFrom" IS NULL OR "publishedFrom" < CURRENT_TIMESTAMP )
                   AND ( "publishedTo"   IS NULL OR "publishedTo"   > CURRENT_TIMESTAMP )
               )
           ) AND (
                   "allAccess"
                OR "v_group_id" = ANY ( "accessGroups" )
                OR "v_user_id"  = ANY ( "accessUsers" )
           ) ) )
       AND "vector" @@ "query";

    RETURN "v_result";

END $$;

--------------------------------------------------------------------------------
-- function: search_suggestion(varchar, varchar, varchar, bool, int, int, int, varchar)
--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION "search_suggestion"(
    "p_locale"              CHARACTER VARYING,
    "p_query"               CHARACTER VARYING,
    "p_type_like"           CHARACTER VARYING   DEFAULT '%',
    "p_all"                 BOOLEAN             DEFAULT FALSE,
    "p_user_id"             INTEGER             DEFAULT 0,
    "p_group_id"            INTEGER             DEFAULT 0,
    "p_is_admin"            BOOLEAN             DEFAULT FALSE,
    "p_limit"               INTEGER             DEFAULT 10,
    "p_wordchars"           CHARACTER VARYING   DEFAULT '[:alnum:]''-'
)
     RETURNS SETOF TEXT
      STABLE
      CALLED ON NULL INPUT
         SET search_path FROM CURRENT
    LANGUAGE plpgsql
          AS $$
DECLARE
    "v_type_like"   CHARACTER VARYING;
    "v_query"       CHARACTER VARYING;
    "v_user_id"     INTEGER             DEFAULT 0;
    "v_group_id"    INTEGER             DEFAULT 0;
BEGIN

    IF '' = "p_type_like" THEN
        "v_type_like" = '%';
    ELSE
        "v_type_like" = "p_type_like";
    END IF;

    IF "p_user_id" IS NOT NULL THEN
        "v_user_id" = "p_user_id";
    END IF;

    IF "p_group_id" IS NOT NULL THEN
        "v_group_id" = "p_group_id";
    END IF;

    "v_query" = regexp_replace( "p_query", '^.*?([' || "p_wordchars" || ']+)[^' || "p_wordchars" || ']*$', '\1' );

    IF '' = "v_query" THEN
        RETURN;
    END IF;

    "v_query" = '(^|[^' || "p_wordchars" || '])' || "v_query" || '([' || "p_wordchars" || ']+|[^' || "p_wordchars" || ']+[' || "p_wordchars" || ']+)';

    RETURN QUERY SELECT "p_query" || regexp_replace(
                            LOWER( ( regexp_matches(
                                "title" || ' ' || "keywords" || ' ' || "description" || ' ' || "content",
                                "v_query",
                                'ig'
                            ) )[2] ),
                            '[^' || "p_wordchars" || ']+',
                            ' ',
                            'g'
                        ) AS "match"
                   FROM "search"
             INNER JOIN "search_content"
                     ON "search_content"."id" = "search"."searchContentId"
                  WHERE ( "p_all" OR "locale" IN ( "p_locale", SUBSTRING( "p_locale" FOR 2 ) ) )
                    AND "type" LIKE "v_type_like"
                    AND ( "p_is_admin" OR ( (
                            "published" OR (
                                    ( "publishedFrom" IS NULL OR "publishedFrom" < CURRENT_TIMESTAMP )
                                AND ( "publishedTo"   IS NULL OR "publishedTo"   > CURRENT_TIMESTAMP )
                            )
                        ) AND (
                                "allAccess"
                             OR "v_group_id" = ANY ( "accessGroups" )
                             OR "v_user_id"  = ANY ( "accessUsers" )
                        ) ) )
               GROUP BY "match"
               ORDER BY COUNT( * ) DESC
                  LIMIT "p_limit";

END $$;

--------------------------------------------------------------------------------
-- function: search_update_vector_trigger()                                   --
--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION "search_update_vector_trigger"()
          RETURNS TRIGGER
         LANGUAGE plpgsql
               AS $$
BEGIN

    NEW."vector" = "_common"."search_data_to_vector"(
        NEW."locale",
        NEW."title",
        NEW."keywords",
        NEW."description",
        NEW."content"
    );

    RETURN NEW;

END $$;

--------------------------------------------------------------------------------
-- trigger: 1000_search_update_vector                                         --
--------------------------------------------------------------------------------

CREATE TRIGGER "1000_search_update_vector"
        BEFORE INSERT
            OR UPDATE
            ON "search"
           FOR EACH ROW
       EXECUTE PROCEDURE "search_update_vector_trigger"();
