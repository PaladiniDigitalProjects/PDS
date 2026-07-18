( function( blocks, element, i18n, blockEditor ) {
    const el = element.createElement;
    const __ = i18n.__;
    // Destructure PlainText and InspectorControls from blockEditor.
    const { PlainText, InspectorControls } = blockEditor;
    // Destructure PanelBody and PanelRow from wp.components.
    const { PanelBody, PanelRow } = window.wp.components;

    // Simple Luge Animation Block (unchanged).
    blocks.registerBlockType( 'pds/luge-block', {
        title: __( 'Luge Animation Block', 'PDS-luge' ),
        icon: 'format-gallery',
        category: 'common',
        attributes: {
            message: {
                type: 'string',
                default: 'Luge Animations Activated!',
            },
        },
        edit: function( props ) {
            const { attributes, setAttributes, className } = props;
            function onChangeMessage( newMessage ) {
                setAttributes( { message: newMessage } );
            }
            return el(
                'div',
                { className: className + ' pds-luge-block' },
                el( PlainText, {
                    value: attributes.message,
                    onChange: onChangeMessage,
                    placeholder: __( 'Enter your animation message…', 'PDS-luge' )
                } )
            );
        },
        save: function() {
            // Rendering is handled via PHP.
            return null;
        },
    } );

    // Luge Video Animation Block with translation attributes and Inspector Controls.
    blocks.registerBlockType( 'pds/video-luge-block', {
        title: __( 'Luge Video Animation Block', 'PDS-luge' ),
        icon: 'format-gallery',
        category: 'common',
        attributes: {
            videourl: {
                type: 'string',
                default: 'http://lolliipop.paladinidigital.com/wp-content/uploads/2025/02/video_scrub.mp4',
            },
            leftTrans: {
                type: 'string',
                default: '-50%',
            },
            topTrans: {
                type: 'string',
                default: '-50%',
            },
        },
        edit: function( props ) {
            const { attributes, setAttributes, className } = props;

            function onChangeVideoUrl( newUrl ) {
                setAttributes( { videourl: newUrl } );
            }
            function onChangeLeftTrans( newValue ) {
                setAttributes( { leftTrans: newValue } );
            }
            function onChangeTopTrans( newValue ) {
                setAttributes( { topTrans: newValue } );
            }

            // Return an array with InspectorControls and block content.
            return [
                // Sidebar controls.
                el(
                    InspectorControls,
                    {},
                    el(
                        PanelBody,
                        { title: __( 'Video Settings', 'PDS-luge' ), initialOpen: true },
                        el(
                            PanelRow,
                            {},
                            el( PlainText, {
                                label: __( 'Video URL', 'PDS-luge' ),
                                value: attributes.videourl,
                                onChange: onChangeVideoUrl,
                                placeholder: __( 'Enter your video url…', 'PDS-luge' )
                            } )
                        ),
                        el(
                            PanelRow,
                            {},
                            el( PlainText, {
                                label: __( 'Left Translation', 'PDS-luge' ),
                                value: attributes.leftTrans,
                                onChange: onChangeLeftTrans,
                                placeholder: __( 'Enter left translation (e.g., -50%)', 'PDS-luge' )
                            } )
                        ),
                        el(
                            PanelRow,
                            {},
                            el( PlainText, {
                                label: __( 'Top Translation', 'PDS-luge' ),
                                value: attributes.topTrans,
                                onChange: onChangeTopTrans,
                                placeholder: __( 'Enter top translation (e.g., -50%)', 'PDS-luge' )
                            } )
                        )
                    )
                ),
                // Main block content.
                el(
                    'div',
                    {
                        className: className + ' pds-video-luge-block',
                        'data-left-trans': attributes.leftTrans,
                        'data-top-trans': attributes.topTrans,
                    },
                    // Video element with data attributes.
                    el( 'video', {
                        src: attributes.videourl,
                        playsInline: true,
                        'webkit-playsinline': 'true',
                        autoPlay: true,
                        muted: true,
                        preload: 'auto',
                        className: 'video-background',
                    } )
                )
            ];
        },
        save: function() {
            // Rendering is handled via PHP.
            return null;
        },
    } );
}(
    window.wp.blocks,
    window.wp.element,
    window.wp.i18n,
    window.wp.blockEditor
) );
