/**
 * qTip Javascript handler for the scite extension
 */

/*global jQuery, mediaWiki */
/*global confirm */

( function ( $, mw ) {

	'use strict';

	$( function ( $ ) {

		var configuration = mw.config.get( 'ext.scite.config' );
		var cache = mw.libs.scite.cache;

		cache.canUse = $.isNumeric( configuration.tooltipRequestCacheTTL );
		cache.cachePrefix = configuration.cachePrefix;

		var loadingImage = '<img class="scite-tooltip-loading" src="data:image/gif;base64,R0lGODlhEAAQAPQAA' +
			'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' +
			'skCkApIyEAOwAAAAAAAAAAAA==" alt="Loading..." />';

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

					cache.set(
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

						var item = cache.get( reference );

						// Add [+] to the title to indicate it has been cached
						if ( item !== null ) {
							QTip.set( 'content.title', reference + ' [+]' );
							return item;
						}

						doApiRequestFor( reference, QTip );

						// Show a loading image while waiting on the request result
						return loadingImage;
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
}( jQuery, mediaWiki ) );
