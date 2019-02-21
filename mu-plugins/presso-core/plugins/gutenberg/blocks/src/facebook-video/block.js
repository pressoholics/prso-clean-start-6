/**
 * BLOCK: my-block
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

//  Import CSS.
import './style.scss';
import './editor.scss';

import apiFetch from '@wordpress/api-fetch';

const {__} = wp.i18n; // Import __() from wp.i18n
const {registerBlockType} = wp.blocks; // Import registerBlockType() from wp.blocks
const {Placeholder, TextControl} = wp.components;
const {Component} = wp.element;

/**
 * Register: prso/facebook-video Gutenberg Block.
 *
 * Block to create a facebook-video. Users create a WP gallery using media modal.
 *
 * Teh save method returns null as you are expected to parse the post_content with gutenberg_parse_blocks()
 * to create rest api json object
 *
 * @link https://wordpress.org/gutenberg/handbook/block-api/
 * @param  {string}   name     Block name.
 * @param  {Object}   settings Block settings.
 * @return {?WPBlock}          The block, if it has been successfully
 *                             registered; otherwise `undefined`.
 */
registerBlockType('prso/facebook-video', {
    // Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
    title: __('Facebook Video'), // Block title.
    icon: 'controls-play', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
    category: 'prso-blocks', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
    keywords: [
        __('facebook-video'),
        __('jam3'),
    ],

    attributes: {
        videoID: {
            type: 'string'
        },
        appID: {
            type: 'string'
        },
    },

    /**
     * The edit function describes the structure of your block in the context of the editor.
     * This represents what the editor will render when the block is used.
     *
     * The "edit" property must be a valid function.
     *
     * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
     */
    edit: class extends Component {

        constructor(props) {
            super(...arguments);
            this.props = props;
        }

        /**
         * When the component mounts it calls this function.
         * Fetches posts types, selected posts then makes first call for posts
         */
        componentDidMount() {


        }

        render() {

            const { className, isSelected } = this.props;
            const { videoID = '', appID = '' } = this.props.attributes;

            return (
                <div className={className}>

                    {!isSelected && (
                        <Placeholder
                            key="placeholder"
                            instructions={__('Select block to edit video')}
                            icon="controls-play"
                            label={__('Facebook Video')}
                        >
                        </Placeholder>
                    )}

                    {isSelected && (
                    <div>
                        <TextControl
                            label="Video ID"
                            value={ videoID }
                            onChange={ (videoID) => { this.props.setAttributes({ videoID }) } }
                        />

                        <TextControl
                            label="App ID"
                            value={ appID }
                            onChange={ (appID) => { this.props.setAttributes({ appID }) } }
                        />
                    </div>
                    )}

                </div>
            );
        }

    },

    /**
     * The save function defines the way in which the different attributes should be combined
     * into the final markup, which is then serialized by Gutenberg into post_content.
     *
     * The "save" property must be specified and must be a valid function.
     *
     * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
     *
     * Return null as we will parse this block with gutenberg_parse_blocks() later to create rest output
     *
     */
    save() {
        return null;
    },
});