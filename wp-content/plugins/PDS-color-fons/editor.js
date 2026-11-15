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

	// ¿Color oscuro? Misma regla que en PHP: luminancia WCAG < 0.179.
	function esFosc( c ) {
		var m, r, g, b;
		if ( ! c ) { return false; }
		if ( c.charAt( 0 ) === '#' ) {
			var h = c.slice( 1 );
			if ( h.length === 3 ) { h = h[0] + h[0] + h[1] + h[1] + h[2] + h[2]; }
			r = parseInt( h.slice( 0, 2 ), 16 ); g = parseInt( h.slice( 2, 4 ), 16 ); b = parseInt( h.slice( 4, 6 ), 16 );
		} else if ( ( m = c.match( /[\d.]+/g ) ) ) {
			r = +m[0]; g = +m[1]; b = +m[2];
			if ( m.length > 3 && +m[3] === 0 ) { return false; }
		} else { return false; }
		function lin( v ) { v /= 255; return v <= 0.03928 ? v / 12.92 : Math.pow( ( v + 0.055 ) / 1.055, 2.4 ); }
		return 0.2126 * lin( r ) + 0.7152 * lin( g ) + 0.0722 * lin( b ) < 0.179;
	}

	// El color en el lienzo del editor (va en un iframe; si no, en el propio documento).
	// Se reintenta un rato porque el iframe se monta después del panel.
	function pintaLlenc( color ) {
		var css = '';
		var transparent = /rgba\([^)]*,\s*0(\.0+)?\s*\)$/.test( color || '' );
		if ( color && ! transparent ) {
			css = '.editor-styles-wrapper{background-color:' + color + ' !important;background-image:none !important}';
			if ( esFosc( color ) ) {
				css += '.editor-styles-wrapper :where(h1,h2,h3,h4,h5,h6,p,li,figcaption,.wp-block-post-title):not(.has-text-color){color:#fff}';
			}
		}
		var intents = 0;
		( function aplica() {
			var ifr = document.querySelector( 'iframe[name="editor-canvas"]' );
			var doc = ifr && ifr.contentDocument && ifr.contentDocument.head ? ifr.contentDocument : ( ifr ? null : document );
			if ( ! doc ) { if ( intents++ < 40 ) { setTimeout( aplica, 250 ); } return; }
			var st = doc.getElementById( 'pds-color-fons-editor' );
			if ( ! st ) { st = doc.createElement( 'style' ); st.id = 'pds-color-fons-editor'; doc.head.appendChild( st ); }
			st.textContent = css;
		} )();
	}

	function Panel() {
		var postType = useSelect( function ( s ) { return s( 'core/editor' ).getCurrentPostType(); }, [] );
		var meta = useEntityProp( 'postType', postType, 'meta' );
		var values = meta[ 0 ] || {};
		var setMeta = meta[ 1 ];
		var color = values[ KEY ] || '';

		wp.element.useEffect( function () { pintaLlenc( color ); }, [ color ] );

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
