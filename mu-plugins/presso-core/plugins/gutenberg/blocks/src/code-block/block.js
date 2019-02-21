/**
 * BLOCK: code-block
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

//  Import CSS.
import './style.scss';
import './editor.scss';

const {__} = wp.i18n; // Import __() from wp.i18n
const {registerBlockType} = wp.blocks; // Import registerBlockType() from wp.blocks
const {Placeholder, SelectControl, PanelBody, PanelRow, TextControl} = wp.components;
const {Component, Fragment} = wp.element;
const {InspectorControls} = wp.editor;

/**
 * Register: prso/code-block Gutenberg Block.
 *
 * Block to create a code-block. Users create a WP gallery using media modal.
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
registerBlockType('prso/code-block', {
    // Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
    title: __('Code Block'), // Block title.
    icon: 'media-code', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
    category: 'prso-blocks', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
    keywords: [
        __('code-block'),
        __('fb'),
        __('jam3'),
    ],

    attributes: {
        codeLanguage: {
            type: 'string'
        },
        content: {
            type: 'string'
        },
        caption: {
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

            const { codeLanguage = 'clike' } = this.props.attributes;

            //Generate a random id for editor
            const editorID = 'jam3_code_block_' + Math.random().toString(36).substr(2, 9);

            this.props.editorID = editorID;
            this.props.codeLanguage = codeLanguage;

            this.maybeInitCodeMirror = this.maybeInitCodeMirror.bind(this);;

        }

        /**
         * When the component mounts it calls this function.
         */
        componentDidMount() {

            const { editorID, codeLanguage } = this.props;

            this.maybeInitCodeMirror( editorID, codeLanguage );

        }

        /**
        * maybeInitCodeMirror
        *
        * @CALLED BY 'componentDidMount'
        *
        * Init codemirror editor on the block's textarea with ID = editorID
        *
        * @param editorID
        * @access public
        * @author Ben Moody
        */
        maybeInitCodeMirror( editorID, codeLanguage ) {

            let codeMirrorTextArea;
            let codeMirrorEditor;
            const codeMirrorConfig = {
                lineNumbers: true,
                mode: codeLanguage,
                lineWrapping: true,
                indentWithTabs: true
            };

            codeMirrorTextArea = document.getElementById( editorID );

            //init codemirror editor on textarea
            codeMirrorEditor = CodeMirror.fromTextArea(codeMirrorTextArea, codeMirrorConfig);

            //Store editor instance in props
            this.setState({
                codeMirrorEditor: codeMirrorEditor
            });

            //Ensure textarea is updated with content from editor
            codeMirrorEditor.on('change', (codeMirrorEditor) => {this.codeMirrorUpdateTextArea(codeMirrorEditor)});

        }

        /**
        * codeMirrorUpdateTextArea
        *
        * @CALLED BY ACTION 'codeMirrorEditor onChange'
        *
        * Save the code editor content into attributes when code editro content changes
        *
        * @param codeMirrorEditor
        * @access public
        * @author Ben Moody
        */
        codeMirrorUpdateTextArea( codeMirrorEditor ) {

            codeMirrorEditor.save();

            this.props.setAttributes({
                content: codeMirrorEditor.getValue()
            });

            console.log( this.props );

        }

        render() {

            const { className, editorID, isSelected } = this.props;
            const { content = '', codeLanguage = 'clike', caption = '' } = this.props.attributes;

            return (
                <Fragment>
                    <InspectorControls>
                        <PanelBody title={ __('Code Editor Settings') }>

                            <PanelRow>
                                <SelectControl
                                    label={ __( 'Mode:' ) }
                                    value={ codeLanguage }
                                    options={ [
                                        { value: 'clike', label: 'C Like' },
                                        { value: 'python', label: 'Python' },
                                    ] }
                                    onChange={ ( language ) => {
                                        this.props.setAttributes({ codeLanguage: language });
                                        this.state.codeMirrorEditor.setOption( 'mode', language );
                                    } }
                                />
                            </PanelRow>

                        </PanelBody>
                    </InspectorControls>
                    <div className={className}>

                        <Placeholder
                            key="placeholder"
                            icon="media-code"
                            label={__('Code Block')}
                        >
                        </Placeholder>

                        <textarea
                            id={editorID}
                            className='hidden'
                            key="code-textarea"
                        >
                            {content}
                        </textarea>

                        {isSelected && (
                            <div>
                                <TextControl
                                    label="Caption"
                                    value={ caption }
                                    onChange={ (caption) => { this.props.setAttributes({ caption }) } }
                                />
                            </div>
                        )}
                    </div>
                </Fragment>
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