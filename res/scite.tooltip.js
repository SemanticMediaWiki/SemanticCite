/**
 * qTip Javascript handler for the scite extension
 */

/*global jQuery, mediaWiki, onoi, QTip */
/*global confirm */

( function ( $, mw, onoi ) {

	'use strict';

	$( function ( $ ) {

		var configuration = mw.config.get( 'ext.scite.config' );
		var blobstore = new onoi.blobstore(
			'scite' +  ':' +
			mw.config.get( 'wgCookiePrefix' ) + ':' +
			mw.config.get( 'wgUserLanguage' )
		);

		var util = new onoi.util();

		/**
		 * API instance
		 *
		 * @since 1.0
		 */
		var doApiRequestFor = function( reference, QTip ) {
			var api = new mw.Api();

			api.get( {
			    action: 'ask',
			    format: 'json',
			    query: '[[Citation key::' + reference + ']]|?Citation text|limit=1'
			} ).done ( function ( content ) {

				var citationText = '';

				// Retrieve the text from the request content
				// The query only ask for "Citation text" as printout therefore no
				// further verification is done here
				$.each( content.query.results, function( subjectName, subject ) {
					if ( $.inArray( 'printouts', subject ) ) {
						$.each ( subject.printouts, function( property, values ) {
							// https://github.com/SemanticMediaWiki/SemanticMediaWiki/issues/1208
							citationText = $.type( values ) === "array" ? values.toString() : values[0];
						} );
					};
				} );

				if ( citationText === '' ) {
					var msgKey = content.hasOwnProperty( 'query-continue-offset' ) ?  'sci-tooltip-citation-lookup-failure-multiple' : 'sci-tooltip-citation-lookup-failure';

					QTip.set(
						'content.text',
						mw.msg( msgKey, reference )
					);
					return null;
				};

				// Parse the raw text to ensure that links are correctly
				// displayed
				api.parse( citationText )
				.done( function ( html ) {
					// Remove parse processing details
					html = $( html ).filter( "p" ).html();

					blobstore.set(
						reference,
						html,
						configuration.tooltipRequestCacheTTL
					)

					QTip.set(
						'content.text',
						html
					);
				} );
			} ).fail ( function( xhr, status, error ) {
				// Upon failure... set the tooltip content to error
				QTip.set( 'content.text', status + ': ' + error );
			} );
		};

		/**
		 * qTip tooltip instance
		 *
		 * @since 1.0
		 */
		var tooltip = function () {

			var reference = $( this ).data( 'reference' );

			// Only act on a href link
			$( this ).find( 'a' ).qtip( {
				content: {
					title : reference,
					text  : function( event, QTip ) {

						// Async process
						blobstore.get( reference, function( value ) {
							if ( configuration.tooltipRequestCacheTTL == 0 || value === null ) {
								doApiRequestFor( reference, QTip );
							} else {
								console.log( reference );
								QTip.set( 'content.title', '<span>' + reference + '</span><div class="scite-tooltip-cache-indicator scite-tooltip-cache-browser"></div>' );
								QTip.set( 'content.text', value );
							}
						} );

						// Show a loading image while waiting on the request result
						return util.getLoadingImg( 'scite-tooltip', 'dots' );
					}
				},
				position: {
					viewport: $( window ),
					my: 'bottom left',
					at: 'top middle'
				},
				hide    : {
					fixed: true,
					delay: 300
				},
				style   : {
					classes: $( this ).attr( 'class' ) + ' qtip-default qtip-light qtip-shadow',
					def    : false
				}
			} );
		};

		/**
		 * @since 1.0
		 */
		$.map( configuration.showTooltipForCitationReference, function( selector, i ) {

			switch( selector ) {
			case 2:
				selector = '.scite-citeref-key';
				break;
			case 1:
			default:
				selector = '.scite-citeref-number';
			}

			$( selector ).each( tooltip );
		} );

	} );
}( jQuery, mediaWiki, onoi ) );
