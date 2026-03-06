import { __ } from '@wordpress/i18n';
import { useBlockProps, InnerBlocks, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, PanelRow } from '@wordpress/components';

const TEMPLATE = [
    ['core/paragraph', { placeholder: __('Add content here...', 'pds-block-visibility') }]
];

const Edit = ({ attributes, setAttributes }) => {
    const { visibilityMobile, visibilityTablet, visibilityDesktop } = attributes;

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Visibility Settings', 'pds-block-visibility')} initialOpen={true}>
                    <PanelRow>
                        <ToggleControl
                            label={__('Visible on Phone', 'pds-block-visibility')}
                            checked={visibilityMobile}
                            onChange={(value) => setAttributes({ visibilityMobile: value })}
                            help={
                                visibilityMobile
                                    ? __('This block will be visible on phones.', 'pds-block-visibility')
                                    : __('This block will not be visible on phones.', 'pds-block-visibility')
                            }
                        />
                    </PanelRow>
                    <PanelRow>
                        <ToggleControl
                            label={__('Visible on Desktop', 'pds-block-visibility')}
                            checked={visibilityDesktop}
                            onChange={(value) => setAttributes({ visibilityDesktop: value })}
                            help={
                                visibilityDesktop
                                    ? __('This block will be visible on desktops.', 'pds-block-visibility')
                                    : __('This block will not be visible on desktops.', 'pds-block-visibility')
                            }
                        />
                    </PanelRow>
                </PanelBody>
            </InspectorControls>
            <div {...useBlockProps()}>
                <InnerBlocks
                    template={TEMPLATE}
                    placeholder={__('Add child blocks here...', 'pds-block-visibility')}
                />
            </div>
        </>
    );
};

export default Edit;
