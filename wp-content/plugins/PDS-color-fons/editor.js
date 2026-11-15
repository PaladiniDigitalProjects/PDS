/* PDS Color de fons: panel en la barra lateral del documento.
   Sin compilar (sin JSX): usa los globales de WordPress. */
( function ( wp ) {
	var el = wp.element.createElement;
	var useSelect = wp.data.useSelect;
	var useEntityProp = wp.coreData.useEntityProp;
	var PluginDocumentSettingPanel = ( wp.editor && wp.editor.PluginDocumentSettingPanel ) || ( wp.editPost && wp.editPost.PluginDocumentSettingPanel );
	var C = wp.components;
	var KEY = 'bkg-color';

	// Colores de la marca a mano, además del selector libre.
	var PALETA = [
		{ name: 'Blanco', color: '#ffffff' },
		{ name: 'Crema', color: '#ede8dd' },
		{ name: 'Gris claro', color: '#f0f0f0' },
		{ name: 'Azul claro', color: '#e9f1f8' },
		{ name: 'Azul PDS', color: '#0000ff' },
		{ name: 'Negro', color: '#000000' }
	];

	function Panel() {
		var postType = useSelect( function ( s ) { return s( 'core/editor' ).getCurrentPostType(); }, [] );
		var meta = useEntityProp( 'postType', postType, 'meta' );
		var values = meta[ 0 ] || {};
		var setMeta = meta[ 1 ];
		var color = values[ KEY ] || '';

		function set( v ) {
			var next = {};
			next[ KEY ] = v || '';
			setMeta( Object.assign( {}, values, next ) );
		}

		return el( PluginDocumentSettingPanel, { name: 'pds-color-fons', title: 'Color de fondo', className: 'pds-color-fons-panel' },
			el( 'p', { style: { marginTop: 0, color: '#757575' } }, 'Fondo de la página en la web (solo en modo claro). Si es oscuro, el logo y el menú pasan a blanco.' ),
			el( C.ColorPalette, {
				colors: PALETA,
				value: color,
				onChange: set,
				enableAlpha: false,
				clearable: true
			} ),
			color ? el( C.Button, { variant: 'secondary', isSmall: true, onClick: function () { set( '' ); }, style: { marginTop: '8px' } }, 'Sin color de fondo' ) : null
		);
	}

	wp.plugins.registerPlugin( 'pds-color-fons', { render: Panel, icon: 'art' } );
} )( window.wp );
