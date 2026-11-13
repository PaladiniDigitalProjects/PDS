/**
 * Panel «Pieza para el chatbot» en la barra lateral del editor de bloques.
 * Sustituye al metabox clásico: mismos campos y mismos metas
 * (registrados con show_in_rest en PDSWT_Piece_Meta::register_meta).
 * JS plano sin build, como el resto del plugin.
 */
( function ( wp ) {
	'use strict';

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var __ = wp.i18n.__;
	var registerPlugin = wp.plugins.registerPlugin;
	var PluginDocumentSettingPanel = ( wp.editor && wp.editor.PluginDocumentSettingPanel ) || ( wp.editPost && wp.editPost.PluginDocumentSettingPanel );
	var useSelect = wp.data.useSelect;
	var useEntityProp = wp.coreData.useEntityProp;
	var MediaUpload = wp.blockEditor.MediaUpload;
	var MediaUploadCheck = wp.blockEditor.MediaUploadCheck;
	var C = wp.components;

	var D = 'pds-web-talk';
	var K = {
		enabled: '_pdswt_piece_enabled',
		image: '_pdswt_piece_image',
		logo: '_pdswt_piece_logo',
		title: '_pdswt_piece_title',
		weight: '_pdswt_piece_weight'
	};

	function mediaUrl( media ) {
		if ( ! media ) {
			return '';
		}
		var sizes = media.media_details && media.media_details.sizes;
		return ( sizes && sizes.medium && sizes.medium.source_url ) || media.source_url || '';
	}

	/** Selector de imagen: vista previa + elegir/cambiar + quitar. */
	function ImageField( props ) {
		var media = useSelect( function ( select ) {
			return props.id ? select( 'core' ).getMedia( props.id ) : null;
		}, [ props.id ] );
		var url = mediaUrl( media ) || props.fallbackUrl || '';

		return el( C.BaseControl, {
			__nextHasNoMarginBottom: true,
			label: props.label,
			help: props.help,
			id: 'pdswt-' + props.name
		},
			url && el( 'img', {
				src: url,
				alt: '',
				style: {
					display: 'block',
					maxWidth: props.small ? '120px' : '100%',
					height: 'auto',
					borderRadius: '2px',
					marginBottom: '8px',
					opacity: props.id ? 1 : 0.6
				}
			} ),
			el( MediaUploadCheck, null,
				el( C.Flex, { justify: 'flex-start', gap: 2 },
					el( MediaUpload, {
						allowedTypes: [ 'image' ],
						value: props.id || undefined,
						onSelect: function ( m ) { props.onChange( m.id ); },
						render: function ( r ) {
							return el( C.Button, {
								variant: 'secondary',
								__next40pxDefaultSize: true,
								onClick: r.open
							}, props.id ? __( 'Cambiar', D ) : props.pickLabel );
						}
					} ),
					props.id ? el( C.Button, {
						variant: 'tertiary',
						isDestructive: true,
						__next40pxDefaultSize: true,
						onClick: function () { props.onChange( 0 ); }
					}, __( 'Quitar', D ) ) : null
				)
			)
		);
	}

	function PiecePanel() {
		var postType = useSelect( function ( select ) {
			return select( 'core/editor' ).getCurrentPostType();
		}, [] );
		var data = useSelect( function ( select ) {
			var ed = select( 'core/editor' );
			var thumbId = ed.getEditedPostAttribute( 'featured_media' );
			return {
				title: ed.getEditedPostAttribute( 'title' ) || '',
				thumb: thumbId ? select( 'core' ).getMedia( thumbId ) : null
			};
		}, [] );
		var metaProp = useEntityProp( 'postType', postType, 'meta' );
		var meta = metaProp[ 0 ] || {};
		var setMeta = metaProp[ 1 ];

		function set( key, value ) {
			var patch = {};
			patch[ key ] = value;
			setMeta( Object.assign( {}, meta, patch ) );
		}

		var enabled = !! meta[ K.enabled ];
		var weight = ( meta[ K.weight ] === undefined || meta[ K.weight ] === null ) ? 5 : meta[ K.weight ];
		var thumbUrl = mediaUrl( data.thumb );

		return el( PluginDocumentSettingPanel, {
			name: 'pdswt-piece',
			title: __( 'Pieza para el chatbot', D ),
			className: 'pdswt-piece-panel'
		},
			el( C.__experimentalVStack || 'div', { spacing: 4 },
				el( C.ToggleControl, {
					__nextHasNoMarginBottom: true,
					label: __( 'Mostrar como tarjeta en el chatbot', D ),
					help: enabled ? null : __( 'El bot puede citar este contenido, pero no lo enseña como tarjeta.', D ),
					checked: enabled,
					onChange: function ( v ) { set( K.enabled, v ); }
				} ),
				enabled && el( Fragment, null,
					el( ImageField, {
						name: 'image',
						label: __( 'Imagen de la tarjeta', D ),
						pickLabel: __( 'Elegir imagen', D ),
						help: ( ! meta[ K.image ] && thumbUrl ) ? __( 'Usando la imagen destacada por defecto.', D ) : null,
						id: meta[ K.image ] || 0,
						fallbackUrl: thumbUrl,
						onChange: function ( id ) { set( K.image, id ); }
					} ),
					el( ImageField, {
						name: 'logo',
						label: __( 'Logo (opcional)', D ),
						pickLabel: __( 'Elegir logo', D ),
						small: true,
						id: meta[ K.logo ] || 0,
						onChange: function ( id ) { set( K.logo, id ); }
					} ),
					el( C.TextControl, {
						__nextHasNoMarginBottom: true,
						__next40pxDefaultSize: true,
						label: __( 'Título de la tarjeta', D ),
						help: __( 'Vacío: usa el título de la página.', D ),
						placeholder: data.title,
						value: meta[ K.title ] || '',
						onChange: function ( v ) { set( K.title, v ); }
					} ),
					el( C.RangeControl, {
						__nextHasNoMarginBottom: true,
						__next40pxDefaultSize: true,
						label: __( 'Importancia', D ),
						help: __( 'Ordena las tarjetas igual de relevantes: no hace salir las que no lo son.', D ),
						min: 0,
						max: 10,
						step: 1,
						withInputField: true,
						marks: true,
						value: weight,
						onChange: function ( v ) { set( K.weight, ( v === undefined || v === null ) ? 5 : v ); }
					} )
				)
			)
		);
	}

	registerPlugin( 'pdswt-piece', { render: PiecePanel } );
} )( window.wp );
