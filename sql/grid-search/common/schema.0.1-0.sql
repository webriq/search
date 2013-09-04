-- drop triggers

DROP TRIGGER "1000_search_update_all_vectors" ON "_common"."search_config" CASCADE;
DROP FUNCTION "_common"."search_update_all_vectors_trigger"() CASCADE;

-- drop functions

DROP FUNCTION "_common"."search_to_query"(
    CHARACTER VARYING,
    CHARACTER VARYING
) CASCADE;

DROP FUNCTION "_common"."search_data_to_vector"(
    CHARACTER VARYING,
    CHARACTER VARYING,
    CHARACTER VARYING,
    CHARACTER VARYING,
    TEXT
) CASCADE;

DROP FUNCTION "_common"."search_locale_to_config"(
    CHARACTER VARYING
) CASCADE;

-- drop tables

DROP TABLE "_common"."search_config" CASCADE;
