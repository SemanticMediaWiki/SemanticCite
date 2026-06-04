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
		 * Extract the rendered citation HTML from an api.parse result.
		 *
		 * The parsed output is placed inside a container element before
		 * searching so that the `.scite-api-parse` marker is found whether
		 * api.parse returns it nested in `mw-parser-output` or as a
		 * top-level node. jQuery's find() only matches descendants, so
		 * searching `$( parsed )` directly misses a top-level marker.
		 *
		 * A native element with innerHTML is used (rather than
		 * `$( '<div>' ).html( parsed )`) because jQuery's .html() executes
		 * any <script> in the input via its append() fallback, whereas
		 * innerHTML never executes scripts.
		 *
		 * Returns the inner HTML string, or undefined when not found.
		 */
		var extractParsedHtml = function( parsed ) {
			var container = document.createElement( 'div' );
			container.innerHTML = parsed;
			var marker = container.querySelector( '.scite-api-parse' );
			return marker ? marker.innerHTML : undefined;
		};

		/**
		 * Fetch citation text via the SMW Ask API, then render it with
		 * api.parse so wiki markup (links, templates) displays correctly.
		 * If api.parse returns no usable output, fall back to the citation
		 * text rendered as escaped plain text.
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
					// mw.msg substitutes $1 without escaping and the result is
					// inserted as HTML by the callback, so escape the reference.
					callback( mw.msg( msgKey, mw.html.escape( reference ) ) );
					return;
				}

				var cacheAndReturn = function( html ) {
					if ( configuration.tooltipRequestCacheTTL > 0 ) {
						cache[ reference ] = {
							html: html,
							time: Date.now()
						};
					}
					callback( html );
				};

				// Render via api.parse for full wikitext rendering (links,
				// templates etc.). If parsing yields nothing usable, show the
				// citation text as escaped plain text rather than raw wikitext
				// or unescaped, attacker-influenced HTML.
				api.parse( '<div class="scite-api-parse">' + citationText + '</div>' )
					.done( function ( parsed ) {
						var html = extractParsedHtml( parsed );
						cacheAndReturn( html || mw.html.escape( citationText ) );
					} )
					.fail( function () {
						cacheAndReturn( mw.html.escape( citationText ) );
					} );

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

			// jQuery's .data() coerces numeric- or boolean-looking attribute
			// values (e.g. a citation key of "2024") to a number/boolean. Force
			// a string so mw.html.escape() — which calls String.prototype.replace
			// and throws on a non-string — and the cache key and Ask query below
			// all behave correctly.
			reference = String( reference );

			var $link = $el.find( 'a' );
			if ( $link.length === 0 ) {
				return;
			}

			// reference round-trips through a data attribute that jQuery
			// HTML-decodes, so escape it before injecting into tooltip markup.
			// The raw string is still used for the cache key and Ask query.
			var safeReference = mw.html.escape( reference );

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
								'<div class="scite-tooltip-title">' + safeReference + '</div>' +
								'<div class="scite-tooltip-content">' + cached + '</div>'
							);
						} else {
							instance.setContent(
								'<div class="scite-tooltip-title">' + safeReference + '</div>' +
								'<div class="scite-tooltip-content"><span class="scite-tooltip-loading"></span> Loading...</div>'
							);
							doApiRequestFor( reference, function( html ) {
								instance.setContent(
									'<div class="scite-tooltip-title">' + safeReference + '</div>' +
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
							'<strong>' + safeReference + '</strong><br>' + cached
						);
					} else {
						$tip.html( '<strong>' + safeReference + '</strong><br>Loading...' );
						doApiRequestFor( reference, function( html ) {
							$tip.html( '<strong>' + safeReference + '</strong><br>' + html );
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
