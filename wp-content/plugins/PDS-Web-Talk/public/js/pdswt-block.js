/**
 * Bloque Gutenberg pdswt/chat (dinámico, render en servidor).
 * Sin build: usa wp.element.createElement.
 */
( function ( blocks, element, blockEditor, components, i18n ) {
	'use strict';
	var el = element.createElement;
	var __ = i18n.__;

	blocks.registerBlockType( 'pdswt/chat', {
		edit: function ( props ) {
			var atts = props.attributes;
			return el(
				'div',
				blockEditor.useBlockProps ? blockEditor.useBlockProps( { className: 'pdswt-block-placeholder' } ) : { className: 'pdswt-block-placeholder' },
				el(
					components.Placeholder,
					{ icon: 'format-chat', label: __( 'PDS Web Talk — Chat', 'pds-web-talk' ), instructions: __( 'El chat con IA se mostrará aquí en la página publicada.', 'pds-web-talk' ) },
					el( components.TextControl, {
						label: __( 'Título', 'pds-web-talk' ),
						value: atts.title || '',
						onChange: function ( v ) { props.setAttributes( { title: v } ); }
					} ),
					el( components.TextareaControl, {
						label: __( 'Mensaje de bienvenida', 'pds-web-talk' ),
						help: __( 'Puedes usar saltos de línea; se respetan en el chat.', 'pds-web-talk' ),
						rows: 5,
						value: atts.welcome || '',
						onChange: function ( v ) { props.setAttributes( { welcome: v } ); }
					} )
				)
			);
		},
		save: function () {
			return null; // Render dinámico en servidor.
		}
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components, window.wp.i18n );
