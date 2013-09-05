-- remove data

DELETE FROM "module"
      WHERE "module" = 'Grid\Search';

-- delete search meta-contents: search.results

DELETE FROM "paragraph"
      WHERE "type" = 'metaContent'
        AND "name" = 'search.results';

-- delete default values for table: search

SELECT "table_plugin_run_drop_triggers"( 'search' );
