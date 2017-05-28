/**
 * MW Api handler to create article content from data-content-selector
 */

/*global jQuery, mediaWiki */
/*global confirm */

( function ( $, mw ) {

	'use strict';

	$( function ( $ ) {

		var page = {

			/**
			 * @since  1.0
			 *
			 * MW Api handler
			 */
			api: new mw.Api(),

			/**
			 * @since  1.0
			 *
			 * Create article
			 */
			create: function ( title, content ) {

				this.api.postWithToken( "csrf", {
					action: "edit",
					title: title,
					section: 0, // Existing content will be replaced
					// summary: section, // no need for a section heading
					text: content
				} ).done( function( result, jqXHR ) {
					location.reload();
					// $( '#scite-status' ).append( 'Added: ' + title ); // not sure we need an update not
				} ).fail( function( xhr, status, error ) {

					var apiErrorText = '';

					if ( xhr === "http" ) {
						apiErrorText = "HTTP error: " + status.textStatus; // status.xhr contains the jqXHR object
					} else if ( xhr === "ok-but-empty" ) {
						apiErrorText = "Got an empty response from the server";
					} else {
						apiErrorText = "API error: " + xhr;
					}

					if ( status.hasOwnProperty( 'xhr' ) ) {
						apiErrorText = status.xhr.responseText.replace(/\<br \/\>/g," ");
						var xhr = status.xhr;

						if ( xhr.hasOwnProperty( 'responseText' ) ) {
							apiErrorText = xhr.responseText.replace(/\<br \/\>/g," " );
						};

						if ( xhr.hasOwnProperty( 'statusText' ) ) {
							apiErrorText = 'The API returned with: ' + xhr.statusText.replace(/\<br \/\>/g," " );
						};
					}

					$( '#scite-status' ).append( apiErrorText );
				} );
			}
		}

		$( '.scite-create' ).on( 'click', function( event ) {

			// Dynamically select element that contains the content
			// to be copied
			page.create(
				$( this ).data( 'title' ),
				$( $( this ).data( 'content-selector' ) ).text()
			);

			event.preventDefault();
		} );

	} );
}( jQuery, mediaWiki ) );
