-- remove data

DELETE FROM "module"
      WHERE "module" = 'Grid\Search';

-- delete default values for table: search

SELECT "table_plugin_run_drop_triggers"( 'search' );
