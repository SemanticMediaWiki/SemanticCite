/**
 * MW Api handler to create article content from data-content-selector
 */

/*global jQuery, mediaWiki */
/*global confirm */

( function ( $, mw ) {

	'use strict';

	$( function ( $ ) {

		function addPageContent( title, content ) {

			var api = new mw.Api();

			// Just duplicate the content as pre tag above the actual content
			content = '<pre>' + content + '</pre>' + "\n" + content;

			api.postWithToken( "edit", {
				action: "edit",
				title: title,
				section: "new",
				// summary: section, // no need for a section heading
				text: content
			} ).done( function( result, jqXHR ) {
				location.reload();
				// $( '#scite-status' ).append( 'Added: ' + title ); // not sure we need an update not
			} ).fail( function( code, result ) {

				var apiErrorText = '';

				if ( code === "http" ) {
					apiErrorText = "HTTP error: " + result.textStatus; // result.xhr contains the jqXHR object
				} else if ( code === "ok-but-empty" ) {
					apiErrorText = "Got an empty response from the server";
				} else {
					apiErrorText = "API error: " + code;
				}

				$( '#scite-status' ).append( apiErrorText );
			} );
		}

		$( '.scite-create' ).on( 'click', function( event ) {

			// Dynamically find out which element contains the content
			// to be copied
			addPageContent(
				$( this ).data( 'title' ),
				$( $( this ).data( 'content-selector' ) ).text()
			);

			event.preventDefault();
		} );

	} );
}( jQuery, mediaWiki ) );
