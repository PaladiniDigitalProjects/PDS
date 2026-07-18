/**
 * PDS-Web-Talk — indexación por lotes con barra de progreso.
 */
( function ( $ ) {
	'use strict';

	$( function () {
		var $start = $( '#pdswt-index-start' );
		var $clear = $( '#pdswt-index-clear' );
		var $wrap  = $( '#pdswt-progress-wrap' );
		var $bar   = $( '#pdswt-progress-bar' );
		var $label = $( '#pdswt-progress-label' );
		var $log   = $( '#pdswt-index-log' );

		if ( ! $start.length ) {
			return;
		}

		function setBusy( busy ) {
			$start.prop( 'disabled', busy );
			$clear.prop( 'disabled', busy );
		}

		function logLine( html ) {
			$log.append( '<div>' + html + '</div>' );
			$log.scrollTop( $log[ 0 ].scrollHeight );
		}

		$start.on( 'click', function () {
			$log.empty();
			$wrap.show();
			$bar.css( 'width', '0%' );
			$label.text( pdswtIndex.i18n.preparing );
			setBusy( true );

			$.post( pdswtIndex.ajaxUrl, { action: 'pdswt_index_ids', nonce: pdswtIndex.nonce } )
				.done( function ( res ) {
					if ( ! res || ! res.success ) {
						fail( res );
						return;
					}
					runBatches( res.data.ids, res.data.total );
				} )
				.fail( function () { fail(); } );
		} );

		function runBatches( ids, total ) {
			var size = pdswtIndex.batchSize || 3;
			var done = 0;
			var totalChunks = 0;

			function next() {
				if ( ! ids.length ) {
					$bar.css( 'width', '100%' );
					$label.text( pdswtIndex.i18n.done + ' (' + totalChunks + ' fragmentos)' );
					$( '#pdswt-stat-chunks' ).html( '<strong>' + totalChunks + '</strong>' );
					$( '#pdswt-stat-posts' ).html( '<strong>' + total + '</strong>' );
					// Regenera el archivo JSON del corpus.
					$.post( pdswtIndex.ajaxUrl, { action: 'pdswt_index_export', nonce: pdswtIndex.nonce } )
						.always( function () { setBusy( false ); } );
					logLine( '📄 ' + ( pdswtIndex.i18n.filewritten || 'Archivo del corpus actualizado.' ) );
					return;
				}
				var batch = ids.splice( 0, size );
				$.post( pdswtIndex.ajaxUrl, { action: 'pdswt_index_batch', nonce: pdswtIndex.nonce, ids: batch } )
					.done( function ( res ) {
						if ( ! res || ! res.success ) {
							fail( res );
							return;
						}
						done += batch.length;
						totalChunks += res.data.chunks;
						var pct = Math.round( ( done / total ) * 100 );
						$bar.css( 'width', pct + '%' );
						$label.text( pdswtIndex.i18n.indexing + ' ' + done + '/' + total );
						$.each( res.data.results, function ( i, r ) {
							logLine( '✓ ' + $( '<span>' ).text( r.title ).html() + ' — ' + r.chunks + ' fragmentos' );
						} );
						next();
					} )
					.fail( function () { fail(); } );
			}
			next();
		}

		$clear.on( 'click', function () {
			if ( ! window.confirm( pdswtIndex.i18n.confirmClear ) ) {
				return;
			}
			setBusy( true );
			$.post( pdswtIndex.ajaxUrl, { action: 'pdswt_index_clear', nonce: pdswtIndex.nonce } )
				.done( function ( res ) {
					if ( res && res.success ) {
						$( '#pdswt-stat-chunks' ).html( '<strong>0</strong>' );
						$( '#pdswt-stat-posts' ).html( '<strong>0</strong>' );
						$log.empty();
						logLine( pdswtIndex.i18n.cleared );
					}
				} )
				.always( function () { setBusy( false ); } );
		} );

		function fail( res ) {
			var msg = ( res && res.data && res.data.message ) ? res.data.message : pdswtIndex.i18n.error;
			$label.text( pdswtIndex.i18n.error + ': ' + msg );
			logLine( '<strong>' + pdswtIndex.i18n.error + ':</strong> ' + $( '<span>' ).text( msg ).html() );
			setBusy( false );
		}
	} );
} )( jQuery );
