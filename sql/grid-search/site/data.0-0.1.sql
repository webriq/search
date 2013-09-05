-- insert default values for table: module

INSERT INTO "module" ( "module", "enabled" )
     VALUES ( 'Grid\Search', TRUE );

-- insert search meta-contents: search.results

DO LANGUAGE plpgsql $$
DECLARE
    "vLastId"   INTEGER;
BEGIN

    -- insert search.results

    INSERT INTO "paragraph" ( "type", "left", "right", "name" )
         VALUES ( 'metaContent', 1, 6, 'search.results' );

    "vLastId" = currval( 'paragraph_id_seq' );

    INSERT INTO "paragraph_property" ( "paragraphId", "locale", "name", "value" )
         VALUES ( "vLastId", 'en', 'title', 'Search' );

    INSERT INTO "paragraph" ( "type", "rootId", "left", "right", "name" )
         VALUES ( 'title', "vLastId", 2, 3, NULL );

    INSERT INTO "paragraph" ( "type", "rootId", "left", "right", "name" )
         VALUES ( 'contentPlaceholder', "vLastId", 4, 5, NULL );

END $$;

-- default values for plugins of table: search

SELECT "table_plugin_run_create_triggers"( 'search' );
