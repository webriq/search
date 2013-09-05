/**
 * Search functionalities
 * @package zork
 * @subpackage search
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
( function ( global, $, js )
{
    "use strict";

    if ( typeof js.search !== "undefined" )
    {
        return;
    }

    var searchAdded = false,
        addSearch   = function () {
            if ( ! searchAdded && typeof global.external.AddSearchProvider !== "undefined" ) {
                global.external.AddSearchProvider(
                    global.location.protocol + "//" +
                    ( global.location.host ? global.location.host : global.location.hostname ) +
                    "/app/" + js.core.defaultLocale + "/search/opensearch/description.xml"
                );
            }

            searchAdded = true;
        };

    /**
     * @class Search module
     * @constructor
     * @memberOf Zork
     */
    global.Zork.Search = function ()
    {
        this.version = "1.0";
        this.modulePrefix = [ "zork", "search" ];
    };

    global.Zork.prototype.search = new global.Zork.Search();

    /**
     * Search query element
     *
     * @param {$|HtmlElement} element
     */
    global.Zork.Search.prototype.query = function ( element )
    {
        element = $( element );
        element.autocomplete( {
            "minLength": 2,
            "source": "/app/" + js.core.defaultLocale + "/search/autocomplete.json"
        } );
    };

    global.Zork.Search.prototype.query.isElementConstructor = true;

    /**
     * Search submit element
     *
     * @param {$|HtmlElement} element
     */
    global.Zork.Search.prototype.submit = function ( element )
    {
        element = $( element );
        element.on( "click", addSearch )
               .closest( "form" )
               .on( "submit", addSearch );
    };

    global.Zork.Search.prototype.query.isElementConstructor = true;

} ( window, jQuery, zork ) );
