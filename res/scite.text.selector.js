/**
 * qTip Javascript handler for the scite extension
 */

/*global jQuery, mediaWiki */
/*global confirm */

( function ( $, mw ) {

	'use strict';

	$( function ( $ ) {

		// http://jsfiddle.net/edelman/KcX6A/1506/
		var highlighter = {
			doSelectFor: function( selector ){

				var context = $( selector );

				var doc = document,
					element = context[0], range, selection;

				if (doc.body.createTextRange) {
					range = document.body.createTextRange();
					range.moveToElementText(element);
					range.select();
				} else if ( window.getSelection ) {
					selection = window.getSelection();
					range = document.createRange();
					range.selectNodeContents(element);
					selection.removeAllRanges();
					selection.addRange(range);
				}
			}
		};

		$( '.scite-highlight' ).on( 'click', function( event ) {

			highlighter.doSelectFor(
				$( this ).data( 'content-selector' )
			);

			event.preventDefault();
		} );

	} );
}( jQuery, mediaWiki ) );
