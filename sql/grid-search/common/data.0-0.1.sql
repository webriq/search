-- default values for table: search_config

INSERT INTO "_common"."search_config" ( "locale", "config" )
     VALUES ( '',   'simple' ), -- fallback config
            ( 'en', 'english' ),
            ( 'fr', 'french' ),
            ( 'de', 'german' ),
            ( 'es', 'spanish' ),
            ( 'hu', 'hungarian' ),
            ( 'da', 'danish' ),
            ( 'nl', 'dutch' ),
            ( 'fi', 'finnish' ),
            ( 'it', 'italian' ),
            ( 'no', 'norwegian' ),
            ( 'nb', 'norwegian' ),
            ( 'nn', 'norwegian' ),
            ( 'pt', 'portuguese' ),
            ( 'ro', 'romanian' ),
            ( 'ru', 'russian' ),
            ( 'sv', 'swedish' ),
            ( 'tr', 'turkish' );
