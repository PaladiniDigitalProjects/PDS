import { __ } from '@wordpress/i18n';
import { useBlockProps, InnerBlocks, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';

const TEMPLATE = [
    ['core/paragraph', { placeholder: __('Add content here...', 'pds-block-visibility') }],
];

const Edit = ({ attributes, setAttributes }) => {
    const { visibilityMobile, visibilityDesktop } = attributes;

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Visibility Settings', 'pds-block-visibility')}>
                    <ToggleControl
                        label={__('Visible on Phone', 'pds-block-visibility')}
                        checked={visibilityMobile}
                        onChange={(value) => setAttributes({ visibilityMobile: value })}
                    />
                    <ToggleControl
                        label={__('Visible on Desktop', 'pds-block-visibility')}
                        checked={visibilityDesktop}
                        onChange={(value) => setAttributes({ visibilityDesktop: value })}
                    />
                </PanelBody>
            </InspectorControls>
            <div {...useBlockProps()}>
                <InnerBlocks template={TEMPLATE} />
            </div>
        </>
    );
};

export default Edit;
