/**
 * Metabox "Pieza para el chatbot": selector de medios (imagen/logo) y
 * mostrar/ocultar los campos según el interruptor.
 */
( function ( $ ) {
	'use strict';

	$( function () {
		var $box = $( '.pdswt-piece' );
		if ( ! $box.length ) { return; }

		// Mostrar/ocultar campos según el toggle.
		$box.on( 'change', '.pdswt-piece__toggle', function () {
			$box.find( '.pdswt-piece__fields' ).toggle( this.checked );
		} );

		// Selector de medios (imagen y logo).
		$box.on( 'click', '.pdswt-piece__pick', function ( e ) {
			e.preventDefault();
			var $media = $( this ).closest( '.pdswt-piece__media' );
			var frame = wp.media( {
				title: 'Selecciona una imagen',
				library: { type: 'image' },
				multiple: false
			} );
			frame.on( 'select', function () {
				var att = frame.state().get( 'selection' ).first().toJSON();
				var url = ( att.sizes && att.sizes.medium ) ? att.sizes.medium.url : att.url;
				$media.find( 'input[type=hidden]' ).val( att.id );
				$media.find( '.pdswt-piece__preview' ).attr( 'src', url ).show();
				$media.find( '.pdswt-piece__clear' ).show();
			} );
			frame.open();
		} );

		// Quitar imagen/logo.
		$box.on( 'click', '.pdswt-piece__clear', function ( e ) {
			e.preventDefault();
			var $media = $( this ).closest( '.pdswt-piece__media' );
			$media.find( 'input[type=hidden]' ).val( '' );
			$media.find( '.pdswt-piece__preview' ).attr( 'src', '' ).hide();
			$( this ).hide();
		} );
	} );
} )( jQuery );
