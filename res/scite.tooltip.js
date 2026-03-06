/**
 * Tooltip handler for the scite extension
 *
 * Uses tippy.js (via ext.smw.tooltip) with a jQuery hover fallback.
 * Citation text is fetched from the SMW Ask API and cached in-memory.
 *
 * @since 4.0
 */

/*global jQuery, mediaWiki */

( function ( $, mw ) {

	'use strict';

	$( function () {

		var configuration = mw.config.get( 'ext.scite.config' );

		if ( !configuration ) {
			mw.log.warn( 'ext.scite.config not found, tooltips disabled' );
			return;
		}

		var cache = {};

		/**
		 * Fetch citation text via the SMW Ask API.
		 *
		 * @since 4.0
		 */
		var doApiRequestFor = function( reference, callback ) {
			var api = new mw.Api();

			api.get( {
				action: 'ask',
				format: 'json',
				query: '[[Citation key::' + reference + ']]|?Citation text|limit=1'
			} ).done( function ( content ) {

				var citationText = '';

				$.each( content.query.results, function( subjectName, subject ) {
					if ( $.inArray( 'printouts', subject ) ) {
						$.each( subject.printouts, function( property, values ) {
							citationText = $.type( values ) === "array" ? values.toString() : values[0];
						} );
					}
				} );

				if ( citationText === '' ) {
					var msgKey = content.hasOwnProperty( 'query-continue-offset' ) ?
						'sci-tooltip-citation-lookup-failure-multiple' :
						'sci-tooltip-citation-lookup-failure';
					callback( mw.msg( msgKey, reference ) );
					return;
				}

				var html = citationText
					.replace( /'''([^']+)'''/g, '<b>$1</b>' )
					.replace( /''([^']+)''/g, '<i>$1</i>' );

				if ( configuration.tooltipRequestCacheTTL > 0 ) {
					cache[ reference ] = {
						html: html,
						time: Date.now()
					};
				}

				callback( html );
			} ).fail( function( xhr, status, error ) {
				callback( status + ': ' + error );
			} );
		};

		/**
		 * Return cached value if still within TTL, or null.
		 */
		var getCached = function( reference ) {
			if ( cache[ reference ] && configuration.tooltipRequestCacheTTL > 0 ) {
				var age = ( Date.now() - cache[ reference ].time ) / 1000;
				if ( age < configuration.tooltipRequestCacheTTL ) {
					return cache[ reference ].html;
				}
				delete cache[ reference ];
			}
			return null;
		};

		/**
		 * Tooltip initialization per citation reference element.
		 *
		 * @since 4.0
		 */
		var initTooltip = function () {
			var $el = $( this );
			var reference = $el.data( 'reference' );

			if ( !reference ) {
				return;
			}

			var $link = $el.find( 'a' );
			if ( $link.length === 0 ) {
				return;
			}

			if ( typeof tippy === 'function' ) {
				tippy( $link[0], {
					content: 'Loading...',
					allowHTML: true,
					interactive: true,
					placement: 'top',
					theme: 'light-border',
					delay: [ 100, 300 ],
					maxWidth: 450,
					onShow: function( instance ) {
						var cached = getCached( reference );
						if ( cached ) {
							instance.setContent(
								'<div class="scite-tooltip-title">' + reference + '</div>' +
								'<div class="scite-tooltip-content">' + cached + '</div>'
							);
						} else {
							instance.setContent(
								'<div class="scite-tooltip-title">' + reference + '</div>' +
								'<div class="scite-tooltip-content"><span class="scite-tooltip-loading"></span> Loading...</div>'
							);
							doApiRequestFor( reference, function( html ) {
								instance.setContent(
									'<div class="scite-tooltip-title">' + reference + '</div>' +
									'<div class="scite-tooltip-content">' + html + '</div>'
								);
							} );
						}
					}
				} );
			} else {
				$link.on( 'mouseenter', function() {
					var $tip = $( '<div class="scite-simple-tooltip"></div>' );
					var cached = getCached( reference );

					if ( cached ) {
						$tip.html(
							'<strong>' + reference + '</strong><br>' + cached
						);
					} else {
						$tip.html( '<strong>' + reference + '</strong><br>Loading...' );
						doApiRequestFor( reference, function( html ) {
							$tip.html( '<strong>' + reference + '</strong><br>' + html );
						} );
					}

					$tip.css( {
						position: 'absolute',
						zIndex: 10000,
						background: '#fff',
						border: '1px solid #ccc',
						borderRadius: '4px',
						padding: '8px 12px',
						maxWidth: '450px',
						boxShadow: '0 2px 8px rgba(0,0,0,0.15)',
						fontSize: '0.9em'
					} );

					$( 'body' ).append( $tip );

					var offset = $( this ).offset();
					$tip.css( {
						top: offset.top - $tip.outerHeight() - 8,
						left: offset.left
					} );

					$( this ).data( 'scite-tip', $tip );
				} ).on( 'mouseleave', function() {
					var $tip = $( this ).data( 'scite-tip' );
					if ( $tip ) {
						$tip.remove();
					}
				} );
			}
		};

		/**
		 * @since 1.0
		 */
		$.map( configuration.showTooltipForCitationReference, function( selector ) {

			switch( selector ) {
			case 2:
				selector = '.scite-citeref-key';
				break;
			case 1:
			default:
				selector = '.scite-citeref-number';
			}

			$( selector ).each( initTooltip );
		} );

	} );
}( jQuery, mediaWiki ) );
