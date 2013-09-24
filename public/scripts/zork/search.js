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
        provider    = global.location.protocol + "//"
                    + ( global.location.host ? global.location.host : global.location.hostname )
                    + "/app/" + js.core.defaultLocale + "/search/opensearch/description.xml",
        showInstall = function () {
            if ( ! searchAdded
                && typeof global.external !== "undefined"
                && typeof global.external.IsSearchProviderInstalled !== "undefined"
                && typeof global.external.AddSearchProvider !== "undefined" )
            {
                return ! global.external.IsSearchProviderInstalled( provider );
            }

            return false;
        },
        addSearch = function () {
            if ( ! searchAdded
                && typeof global.external !== "undefined"
                && typeof global.external.IsSearchProviderInstalled !== "undefined"
                && typeof global.external.AddSearchProvider !== "undefined" )
            {
                if ( ! global.external.IsSearchProviderInstalled( provider ) )
                {
                    global.external.AddSearchProvider( provider );
                }
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

        var all = element.closest( "form" )
                         .find( "input[type=checkbox][name=all]" ),
            source = function () {
                return "/app/" + js.core.defaultLocale
                     + "/search/autocomplete.json"
                     + ( all.prop( "checked" ) ? '?all=1' : '' );
            };

        element.autocomplete( {
            "minLength": 2,
            "source": source()
        } );

        all.on( "click change", function () {
            element.autocomplete( "option", "source", source() );
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

        if ( showInstall() )
        {
            var isButton = element.is( "button" ),
                install  = $( isButton ? '<button>' : '<input type="button">' ),
                label    = js.core.translate( "search.install.label" ),
                title    = js.core.translate( "search.install.title" );

            if ( isButton )
            {
                install.text( label );
            }
            else
            {
                install.val( label );
            }

            install.attr( "title", title )
                   .on( "click", function () {
                        addSearch();
                        install.remove();
                        element.parent()
                               .inputset();
                    } );

            element.after( install )
                   .parent()
                   .inputset();
        }
    };

    global.Zork.Search.prototype.submit.isElementConstructor = true;

    /**
     * Search items element
     *
     * @param {$|HtmlElement} element
     */
    global.Zork.Search.prototype.items = function ( element )
    {
        element = $( element );
        element.attr( "title", js.core.translate( "search.form.items" ) );
    };

    global.Zork.Search.prototype.items.isElementConstructor = true;

} ( window, jQuery, zork ) );
