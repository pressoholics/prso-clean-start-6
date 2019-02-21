/**
 * BLOCK: my-block
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

//  Import CSS.
import './style.scss';
import './editor.scss';

const {__} = wp.i18n; // Import __() from wp.i18n
const {registerBlockType} = wp.blocks; // Import registerBlockType() from wp.blocks
const {MediaUpload, BlockControls} = wp.editor;
const {Button, Dashicon, Toolbar, Placeholder} = wp.components;
const {Fragment} = wp.element;

/**
 * Register: prso/carousel Gutenberg Block.
 *
 * Block to create a carousel. Users create a WP gallery using media modal.
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
registerBlockType('prso/carousel', {
    // Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
    title: __('Carousel'), // Block title.
    icon: 'images-alt2', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
    category: 'prso-blocks', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
    keywords: [
        __('carousel'),
        __('fb'),
        __('jam3'),
    ],

    attributes: {
        images: {
            type: 'array'
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
    edit({attributes, className, setAttributes, isSelected}) {

        //Try and destructure images attribute
        const {images} = attributes;

        /**
         * mediaUploadButton
         *
         * @CALLED BY MediaUpload
         *
         * Callback to render the main media upload button for the block when first initialized
         *
         * @access public
         * @author Ben Moody
         */
        const mediaUploadButton = (openEvent) => {

            return [
                <Button
                    onClick={openEvent}
                    className="button button-large"
                    key="media-upload-button"
                >
                    Select Images
                </Button>
            ];

        };

        /**
         * mediaEditButton
         *
         * @CALLED BY MediaUpload
         *
         * Callback to render the edit carousel button in the Toolbar when a carousel has images and isSelected
         *
         * @access public
         * @author Ben Moody
         */
        const mediaEditButton = (openEvent) => {

            return [
                <Button
                    onClick={openEvent}
                    className="components-icon-button components-toolbar__control"
                    key="media-edit-button"
                >
                    <Dashicon icon="edit"/>
                </Button>
            ];

        };

        /**
         * controls
         *
         * Render the carousel edit toolbar control if carousel has images and isSelected
         *
         * @access public
         * @author Ben Moody
         */
        const controls = (
            isSelected && (
                <Fragment key="controls-fragment">
                    <BlockControls key="controls">
                        {Array.isArray(images) && (images.length > 0) && (
                            <Toolbar key="toolbar">
                                <MediaUpload
                                    onSelect={media => {
                                        setAttributes({images: media})
                                    }}
                                    type="image"
                                    render={({open}) => mediaEditButton(open)}
                                    multiple={true}
                                    gallery={true}
                                    value={images.map((img) => img.id)}
                                />
                            </Toolbar>
                        )}
                    </BlockControls>
                </Fragment>
            )
        );

        /**
         * Return IF NO IMAGES SELECTED
         *
         * Render the default state of the block, placeholder with MediaUpload button
         */
        if (!Array.isArray(images) || (images.length === 0)) {

            return [
                controls,
                <Placeholder
                    key="placeholder"
                    instructions={__('Build a Carousel using images from your computer or the Media Library')}
                    icon="format-gallery"
                    label={__('Carousel')}
                    className={className}>
                    <MediaUpload
                        onSelect={media => {
                            setAttributes({images: media})
                        }}
                        type="image"
                        render={({open}) => mediaUploadButton(open)}
                        multiple={true}
                        gallery={true}
                    />
                </Placeholder>,
            ];
        }

        /**
         * Return if carousel HAS IMAGES
         *
         * Render the has images state of the block, gallery of images in carousel
         */
        return [
            controls,
            <ul className="wp-block-gallery alignundefined columns-3 is-cropped">
                {images.map((img, index) => (
                    <li className="blocks-gallery-item" key={index}>
                        <img
                            key={img.id}
                            src={img.url}
                            alt={img.alt}
                            id={img.id}
                        />
                    </li>
                ))}
            </ul>,
        ];

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