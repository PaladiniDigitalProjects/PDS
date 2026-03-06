import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import './style.scss';
import './editor.scss';
import save from './save';

registerBlockType('pds/block-visibility', {
    attributes: {
        visibilityMobile: {
            type: 'boolean',
            default: true,
        },
        visibilityDesktop: {
            type: 'boolean',
            default: true,
        },
    },
    edit: Edit, 
    save: save, 
});
