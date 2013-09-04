--------------------------------------------------------------------------------
-- table: search_config                                                       --
--------------------------------------------------------------------------------

CREATE TABLE "_common"."search_config"
(
    "locale"    CHARACTER VARYING   NOT NULL,
    "config"    regconfig           NOT NULL,

    PRIMARY KEY ( "locale" )
);

--------------------------------------------------------------------------------
-- function: search_locale_to_config( varchar )                               --
--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION "_common"."search_locale_to_config"(
    "p_locale"  CHARACTER VARYING
)
     RETURNS regconfig
         SET search_path FROM CURRENT
      STABLE
    LANGUAGE plpgsql
          AS $$
DECLARE
    "v_language"    CHARACTER VARYING;
    "v_config"      regconfig;
BEGIN

    -- select exact locale

    SELECT "config"
      INTO "v_config"
      FROM "_common"."search_config"
     WHERE "locale" = "p_locale"
     LIMIT 1;

    IF FOUND THEN
        RETURN "v_config";
    END IF;

    -- select language (parts separated with '_')

    "v_language" = split_part( "p_locale", '_', 1 );

    SELECT "config"
      INTO "v_config"
      FROM "_common"."search_config"
     WHERE "locale" = "v_language"
     LIMIT 1;

    IF FOUND THEN
        RETURN "v_config";
    END IF;

    -- select language (first 2 characters)

    "v_language" = SUBSTRING( "p_locale" FOR 2 );

    SELECT "config"
      INTO "v_config"
      FROM "_common"."search_config"
     WHERE "locale" = "v_language"
     LIMIT 1;

    IF FOUND THEN
        RETURN "v_config";
    END IF;

    -- select fallback config

    SELECT "config"
      INTO "v_config"
      FROM "_common"."search_config"
     WHERE "locale" = ''
     LIMIT 1;

    IF FOUND THEN
        RETURN "v_config";
    END IF;

    -- constant fallback config

    RETURN 'simple';

END $$;

--------------------------------------------------------------------------------
-- function: search_data_to_vector( varchar, varchar, varchar, varchar, text )--
--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION "_common"."search_data_to_vector"(
    "p_locale"          CHARACTER VARYING,
    "p_title"           CHARACTER VARYING,
    "p_keywords"        CHARACTER VARYING,
    "p_description"     CHARACTER VARYING,
    "p_content"         TEXT
)
     RETURNS tsvector
         SET search_path FROM CURRENT
      STABLE
    LANGUAGE plpgsql
          AS $$
DECLARE
    "v_config"  regconfig;
BEGIN

    "v_config" = "_common"."search_locale_to_config"( "p_locale" );

    RETURN setweight( to_tsvector( "v_config", COALESCE( "p_title",       '' ) ), 'A' )
        || setweight( to_tsvector( "v_config", COALESCE( "p_keywords",    '' ) ), 'B' )
        || setweight( to_tsvector( "v_config", COALESCE( "p_description", '' ) ), 'C' )
        || setweight( to_tsvector( "v_config", COALESCE( "p_content",     '' ) ), 'D' );

END $$;

--------------------------------------------------------------------------------
-- function: search_to_query( varchar, varchar )                              --
--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION "_common"."search_to_query"(
    "p_locale"  CHARACTER VARYING,
    "p_query"   CHARACTER VARYING
)
     RETURNS tsquery
         SET search_path FROM CURRENT
      STABLE
    LANGUAGE plpgsql
          AS $$
DECLARE
    "v_config"  regconfig;
BEGIN

    "v_config" = "_common"."search_locale_to_config"( "p_locale" );

    BEGIN

        RETURN to_tsquery( "v_config", COALESCE( "p_query", '' ) );

    EXCEPTION

        WHEN syntax_error THEN
            RETURN plainto_tsquery( "v_config", COALESCE( "p_query", '' ) );

    END;

END $$;

--------------------------------------------------------------------------------
-- function: search_update_all_vectors_trigger()                              --
--------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION "_common"."search_update_all_vectors_trigger"()
          RETURNS TRIGGER
         LANGUAGE plpgsql
               AS $$
DECLARE
    "r_update"      RECORD;
    "c_update"      NO SCROLL CURSOR FOR
                              SELECT DISTINCT TABLE_SCHEMA AS "schema"
                                FROM INFORMATION_SCHEMA.COLUMNS
                               WHERE TABLE_NAME  = 'search'
                                 AND COLUMN_NAME = 'vector';
BEGIN

    FOR "r_update" IN "c_update" LOOP

        CONTINUE WHEN "r_update"."schema" IN ( '_common', '_central' );

        EXECUTE format(
            'UPDATE %I."search"
                SET "vector" = "_common"."search_data_to_vector"(
                        "locale",
                        "title",
                        "keywords",
                        "description",
                        "content"
                    )',
            "r_update"."schema"
        );

    END LOOP;

    RETURN NULL;

END $$;

--------------------------------------------------------------------------------
-- trigger: 1000_search_update_all_vectors                                    --
--------------------------------------------------------------------------------

CREATE TRIGGER "1000_search_update_all_vectors"
         AFTER INSERT
            OR UPDATE
            OR DELETE
            OR TRUNCATE
            ON "_common"."search_config"
           FOR EACH STATEMENT
       EXECUTE PROCEDURE "_common"."search_update_all_vectors_trigger"();
