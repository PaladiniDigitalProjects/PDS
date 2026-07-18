/**
 * PDS-Web-Talk — admin: chat de prueba (Fase 0).
 */
( function ( $ ) {
	'use strict';

	$( function () {
		var $btn    = $( '#pdswt-test-send' );
		var $input  = $( '#pdswt-test-input' );
		var $output = $( '#pdswt-test-output' );

		if ( ! $btn.length ) {
			return;
		}

		$btn.on( 'click', function () {
			var message = $.trim( $input.val() );
			if ( ! message ) {
				$output.html( '<em>' + pdswtAdmin.i18n.empty + '</em>' );
				return;
			}

			$btn.prop( 'disabled', true );
			$output.html( '<em>' + pdswtAdmin.i18n.thinking + '</em>' );

			$.post( pdswtAdmin.ajaxUrl, {
				action: 'pdswt_test_chat',
				nonce: pdswtAdmin.nonce,
				message: message
			} ).done( function ( res ) {
				if ( res && res.success ) {
					$output.empty();
					$( '<div>' ).text( res.data.reply ).appendTo( $output );
					if ( res.data.sources && res.data.sources.length ) {
						var $src = $( '<div class="pdswt-sources"></div>' ).appendTo( $output );
						$src.append( '<strong>Fuentes:</strong> ' );
						$.each( res.data.sources, function ( i, s ) {
							$( '<a>' ).attr( 'href', s.link ).attr( 'target', '_blank' ).text( s.title ).appendTo( $src );
							if ( i < res.data.sources.length - 1 ) { $src.append( ', ' ); }
						} );
					}
				} else {
					var msg = res && res.data && res.data.message ? res.data.message : pdswtAdmin.i18n.error;
					$output.html( '<strong>' + pdswtAdmin.i18n.error + ':</strong> ' + $( '<div>' ).text( msg ).html() );
				}
			} ).fail( function () {
				$output.html( '<strong>' + pdswtAdmin.i18n.error + '</strong>' );
			} ).always( function () {
				$btn.prop( 'disabled', false );
			} );
		} );
	} );
} )( jQuery );
