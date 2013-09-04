-- insert default values for table: module

INSERT INTO "module" ( "module", "enabled" )
     VALUES ( 'Grid\Search', TRUE );

-- default values for plugins of table: search

SELECT "table_plugin_run_create_triggers"( 'search' );
