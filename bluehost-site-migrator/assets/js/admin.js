/* Bluehost Site Migrator - admin page. Plain ES5, no dependencies. */
( function () {
	// Runs in the head, before the page is painted. The stylesheet uses this class to hide the result.
	document.documentElement.className += ' bhsm-js';

	function ready() {
		var panel = document.getElementById( 'bhsm-panel' );
		if ( ! panel ) {
			return;
		}

		// The support request carries the site details only while the box is checked.
		var details = document.getElementById( 'bhsm-details' );
		var link = panel.querySelector( '.bhsm-help-link' );
		if ( details && link ) {
			var plain = link.getAttribute( 'href' );
			details.addEventListener( 'change', function () {
				link.setAttribute( 'href', details.checked ? link.getAttribute( 'data-with-details' ) : plain );
			} );
		}

		// There is no button when the check has run on this site before. The result is already on show.
		var button = document.getElementById( 'bhsm-start' );
		if ( ! button ) {
			return;
		}

		var steps = panel.querySelectorAll( '.bhsm-progress li' );
		var reduce = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

		// The checks are done before the page loads. Each line shows for 1.2 to 2.5 seconds.
		function delay() {
			return reduce ? 0 : 1200 + Math.floor( Math.random() * 1301 );
		}

		function finish() {
			var result = document.getElementById( 'bhsm-result' );
			panel.className += ' bhsm-done';
			if ( result ) {
				result.focus();
			}

			// Remember that the check ran, so the next visit goes straight to the result.
			if ( window.ajaxurl && window.XMLHttpRequest ) {
				var request = new XMLHttpRequest();
				request.open( 'POST', window.ajaxurl );
				request.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' );
				request.send( 'action=bh_site_migrator_checked&_ajax_nonce=' + encodeURIComponent( panel.getAttribute( 'data-nonce' ) ) );
			}
		}

		function run( i ) {
			// One line shows at a time. The last line stays until the result replaces it.
			if ( i >= steps.length ) {
				finish();
				return;
			}
			if ( i > 0 ) {
				steps[ i - 1 ].className = '';
			}
			steps[ i ].className = 'is-active';
			window.setTimeout( function () {
				run( i + 1 );
			}, delay() );
		}

		button.addEventListener( 'click', function () {
			button.disabled = true;
			panel.className += ' bhsm-checking';
			run( 0 );
		} );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', ready );
	} else {
		ready();
	}
}() );
