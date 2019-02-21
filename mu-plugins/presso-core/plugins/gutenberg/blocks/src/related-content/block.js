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
const {Button, Dashicon, Placeholder, TextControl, SelectControl} = wp.components;
const {Component} = wp.element;

/**
 * Register: prso/related-content Gutenberg Block.
 *
 * Block to create a related-content. Users create a WP gallery using media modal.
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
registerBlockType('prso/related-content', {
    // Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
    title: __('Related Content'), // Block title.
    icon: 'format-aside', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
    category: 'prso-blocks', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
    keywords: [
        __('related-content'),
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
        }

        /**
         * When the component mounts it calls this function.
         * Fetches posts types, selected posts then makes first call for posts
         */
        componentDidMount() {

            const { items = [], itemsJson = false } = this.props.attributes;
            let savedItems = items;

            //Cache saved json data into attributes
            if( false !== itemsJson ) {

                //Get saved items from json string
                savedItems = JSON.parse(itemsJson);

            }

            this.props.setAttributes({
                items: savedItems,
            });

            //Set init react state
            this.setState({
                loading: true,
                type: 'posts',
                types: [],
                items: savedItems,
            });

            //Get all registered post types
            apiFetch( { path: '/wp-json/wp/v2/types' } ).then( postTypes => {

                this.setState({
                    types: postTypes
                });

            } );
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

        /**
        * onTitleChange
        *
        * @CALLED BY ACTION 'onChange'
        *
        * Handle onchange event for section title field, update blockTitle attribute
        *
        * @param string blockTitle
        * @access public
        * @author Ben Moody
        */
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

            const newItem = {
                title: '',
                link: '',
                content: '',
                searchQuery: '',
                searchPosts: [],
                hasPost: false,
                post: {},
                linkType: 'internal',
            };

            items.push( newItem );

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
                link: '',
                content: '',
                searchQuery: '',
                searchPosts: [],
                hasPost: false,
                post: {},
                linkType: 'internal',
            };

            if( 'below' === position ) {

                index = index + 1;

            }

            items.splice( index, 0, newItem );

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

            if( true === confirm('This will delete selected item') ) {

                items.splice( index, 1 );

                this.saveItems( items );

            }

        }

        /**
        * onPostSearchChange
        *
        * @CALLED BY ACTION 'onChange'
        *
        * When post search field value changes init the post search process
        *
        * @param index
        * @param queryString
        * @access public
        * @author Ben Moody
        */
        onPostSearchChange( index, queryString ) {

            const { items = [] } = this.props.attributes;

            items[ index ].searchQuery = queryString;

            this.setState({
                items: items
            });

            //Make rest api request to posts based on queryString
            this.doPostFilter( index, queryString );

        }

        /**
        * doPostFilter
        *
        * Conducts a post search request based on queryString param
        *
        * @param index
        * @param queryString
        * @return boolean
        * @access public
        * @author Ben Moody
        */
        doPostFilter( index, queryString ) {

            const { items = [] } = this.props.attributes;

            if( queryString.length < 3 ) {

                items[ index ].searchPosts = [];

                this.setState({
                    items: items
                });

                return false;
            }

            const defaultArgs = {
                per_page: 10,
                type: this.state.type,
            };

            //Get post data from rest api based on query string for search
            apiFetch( { path: `/wp-json/wp/v2/${defaultArgs['type']}/?search=${queryString}&per_page=${defaultArgs['per_page']}` } ).then( posts => {

                console.log( posts );

                items[ index ].searchPosts = posts;

                this.setState({
                    items: items
                });

            } );

        }

        /**
        * onPostItemClick
        *
        * @CALLED BY ACTION 'onClick'
        *
        * Assingns the clicked post to an item, item is identified in the items array by index param
        *
        * @param index
        * @param post
        * @access public
        * @author Ben Moody
        */
        onPostItemClick( index, post ) {

            const { items = [] } = this.props.attributes;

            //Add post to item
            items[ index ].post = {
                id: post.id,
                title: post.title.rendered,
            };
            items[ index ].hasPost = true;

            this.saveItems( items );

        }

        /**
        * deleteItemPost
        *
        * @CALLED BY ACTION 'onClick'
        *
        * Remove post from an item, item is identified in the items array by index param
        *
        * @param index
        * @access public
        * @author Ben Moody
        */
        deleteItemPost( index ) {

            const { items = [] } = this.props.attributes;

            if( true === confirm('This will remove post from this item') ) {

                //Remove post to item
                items[ index ].post = {};
                items[ index ].hasPost = false;
                items[ index ].searchQuery = '';
                items[ index ].searchPosts = [];

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
                        instructions={__('Select block to edit related content')}
                        icon="format-aside"
                        label={__('Related Content')}
                        >
                    </Placeholder>
                    )}

                    {isSelected && (
                    <div className="wp-block-jam3-related-content-title-wrapper">

                        <TextControl
                        label="Section Title"
                        value={blockTitle}
                        onChange={(content) => { this.onTitleChange(content) }}
                        />

                    </div>
                    )}

                    {isSelected && (
                        <div className="wp-block-jam3-related-content-items-wrapper">

                            {
                                items.map(
                                    (item, index) => (
                                        <div
                                            className="wp-block-jam3-related-content-item"
                                            key={index}
                                        >
                                            <div
                                                className="wp-block-jam3-related-content-item-delete"
                                            >
                                                <Button
                                                    onClick={ () => { this.deleteItem( index ) } }
                                                    key="index"
                                                >
                                                    <Dashicon icon="dismiss"/>
                                                </Button>
                                            </div>

                                            <div
                                                className="wp-block-jam3-related-content-item-insert above"
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

                                            <div className="wp-block-jam3-related-item-type-wrapper">
                                                <SelectControl
                                                    key={index}
                                                    label={ __( 'Select link type:' ) }
                                                    value={ this.state.items[ index ].linkType }
                                                    options={ [
                                                        { value: 'internal', label: 'Internal' },
                                                        { value: 'external', label: 'External' },
                                                    ] }
                                                    onChange={ ( linkType ) => { this.onItemChangeStringValue( index, linkType, 'linkType' ) } }
                                                />
                                            </div>

                                            { this.state.items[ index ].linkType === 'internal' && (
                                                <div className="wp-block-jam3-related-item-search-wrapper">

                                                    { !this.state.items[ index ].hasPost && (
                                                        <TextControl
                                                            label="Search for Posts"
                                                            value={this.state.items[ index ].searchQuery}
                                                            onChange={ (queryString) => { this.onPostSearchChange( index, queryString ) } }
                                                        />
                                                    )}

                                                    { !this.state.items[ index ].hasPost && (
                                                        <ul>
                                                            {
                                                                this.state.items[ index ].searchPosts.map(
                                                                    (post) => (
                                                                        <li
                                                                            key={post.id}
                                                                            postID={post.id}
                                                                            onClick={ () => { this.onPostItemClick( index, post ) } }
                                                                        >
                                                                            {post.title.rendered}
                                                                        </li>
                                                                    )
                                                                )
                                                            }
                                                        </ul>
                                                    )}

                                                    { this.state.items[ index ].hasPost && (
                                                        <Button
                                                            key={index}
                                                            onClick={ () => { this.deleteItemPost( index ) } }
                                                        >
                                                            {this.state.items[ index ].post.title}
                                                            <Dashicon icon="dismiss"/>
                                                        </Button>
                                                    )}

                                                </div>
                                            )}

                                            { this.state.items[ index ].linkType === 'external' && (
                                                <TextControl
                                                    label="External Link URL"
                                                    value={items[ index ].link}
                                                    onChange={(content) => { this.onItemChangeStringValue( index, content, 'link' ) }}
                                                />
                                            )}

                                            <div
                                                className="wp-block-jam3-related-content-item-insert below"
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