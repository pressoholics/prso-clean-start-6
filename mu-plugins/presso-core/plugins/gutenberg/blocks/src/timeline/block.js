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
const {Button, Dashicon, TextareaControl, Placeholder, TextControl} = wp.components;
const {Component} = wp.element;

/**
 * Register: prso/timeline Gutenberg Block.
 *
 * Block to create a timeline. Users create a WP gallery using media modal.
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
registerBlockType('prso/timeline', {
    // Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
    title: __('Timeline'), // Block title.
    icon: 'image-flip-horizontal', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
    category: 'prso-blocks', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
    keywords: [
        __('timeline'),
        __('jam3'),
    ],

    attributes: {
        blockTitle: {
            type: 'string'
        },
        items: {
            type: 'array',
            default: []
        },
        itemsJson: {
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

            const { items = [], itemsJson = false } = this.props.attributes;
            let savedItems = items;

            if( false !== itemsJson ) {

                //Get saved items from json string
                savedItems = JSON.parse(itemsJson);

            }

            this.props.setAttributes({
                items: savedItems,
            });

            this.setState({
                items: savedItems
            });

        }

        /**
         * saveItems
         *
         * Helper to save items data into attributes and react state
         *
         * @param array items
         * @access public
         * @author Ben Moody
         */
        saveItems( items ) {

            this.props.setAttributes({
                items: items,
                itemsJson: JSON.stringify(items),
            });


            this.setState({
                items: items
            });

        }

        onTitleChange(blockTitle) {
            this.props.setAttributes({ blockTitle });
        }

        /**
         * onItemChangeStringValue
         *
         * @CALLED BY ACTION 'onChange'
         *
         * Helper to handle saving any string values during onChange event for items
         * nodeName param tells the method which item node you are saving
         *
         * @param int index
         * @param string value
         * @param string nodeName
         * @access public
         * @author Ben Moody
         */
        onItemChangeStringValue( index, value, nodeName ) {

            const { items = [] } = this.props.attributes;

            items[ index ][ nodeName ] = value;

            this.saveItems( items );

        }

        /**
         * addItem
         *
         * @CALLED BY ACTION 'onClick'
         *
         * Creates a new empty item object and adds to the items array in attributes and react state
         *
         * @access public
         * @author Ben Moody
         */
        addItem() {

            const { items = [] } = this.props.attributes;

            items.push({
                title: '',
                date: '',
                content: '',
            });

            this.saveItems( items );
        }

        /**
         * insertItem
         *
         * @CALLED BY ACTION 'onClick'
         *
         * Insert a new item object into the items array, position param dictates where in the array the item should be added
         *
         * @param index
         * @param position
         * @access public
         * @author Ben Moody
         */
        insertItem( index, position ) {

            const { items = [] } = this.props.attributes;

            const newItem = {
                title: '',
                date: '',
                content: '',
            };

            if( 'below' === position ) {

                index = index + 1;

            }

            items.splice( index, 0, newItem );

            console.log(index);
            console.log(items);

            this.saveItems( items );

        }

        /**
         * deleteItem
         *
         * @CALLED BY ACTION 'onClick'
         *
         * Remove item from items array, index param is used as key for item in array
         *
         * @param index
         * @access public
         * @author Ben Moody
         */
        deleteItem( index ) {

            const { items = [] } = this.props.attributes;

            if( true === confirm('This will delete selected timline item') ) {

                items.splice( index, 1 );

                this.saveItems( items );

            }

        }

        render() {

            const { className, isSelected } = this.props;
            const { blockTitle = '', items } = this.props.attributes;

            return (
                <div className={className}>

                    {!isSelected && (
                    <Placeholder
                        key="placeholder"
                        instructions={__('Select block to edit timeline')}
                        icon="image-flip-horizontal"
                        label={__('Timeline')}
                        >
                    </Placeholder>
                    )}

                    {isSelected && (
                    <div className="wp-block-jam3-timeline-title-wrapper">

                        <TextControl
                        label="Timeline Title"
                        value={blockTitle}
                        onChange={(content) => { this.onTitleChange(content) }}
                        />

                    </div>
                    )}

                    {isSelected && (
                        <div className="wp-block-jam3-timeline-items-wrapper">

                            {
                                items.map(
                                    (item, index) => (
                                        <div
                                            className="wp-block-jam3-timeline-item"
                                            key={index}
                                        >
                                            <div
                                                className="wp-block-jam3-timeline-item-delete"
                                            >
                                                <Button
                                                    onClick={ () => { this.deleteItem( index ) } }
                                                    key="index"
                                                >
                                                    <Dashicon icon="dismiss"/>
                                                </Button>
                                            </div>

                                            <div
                                                className="wp-block-jam3-timeline-item-insert above"
                                            >
                                                <Button
                                                    onClick={ () => { this.insertItem( index, 'above' ) } }
                                                    key="index"
                                                >
                                                    <Dashicon icon="plus-alt"/>
                                                </Button>
                                            </div>

                                            <TextControl
                                                label="Item Title"
                                                value={items[ index ].title}
                                                onChange={(content) => { this.onItemChangeStringValue( index, content, 'title' ) }}
                                            />

                                            <TextControl
                                                label="Item Date"
                                                value={items[ index ].date}
                                                onChange={(content) => { this.onItemChangeStringValue( index, content, 'date' ) }}
                                            />

                                            <TextareaControl
                                                label="Content"
                                                value={ items[ index ].content }
                                                onChange={ ( content ) => { this.onItemChangeStringValue( index, content, 'content' ) } }
                                            />

                                            <div
                                                className="wp-block-jam3-timeline-item-insert below"
                                            >
                                                <Button
                                                    onClick={ () => { this.insertItem( index, 'below' ) } }
                                                    key="index"
                                                >
                                                    <Dashicon icon="plus-alt"/>
                                                </Button>
                                            </div>

                                        </div>
                                    )
                                )

                            }

                        </div>
                    )}

                    {isSelected && (
                    <div className="appender-wrapper">
                        <Button
                            className="block-list-appender__toggle"
                            onClick={() => { this.addItem() }}
                        >
                                <Dashicon icon="insert" /> Add Item
                        </Button>
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