import assign from 'lodash.assign';

const { createHigherOrderComponent } = wp.compose;
const { Fragment } = wp.element;
const { InspectorControls } = wp.editor;
const { PanelBody, SelectControl } = wp.components;
const { addFilter } = wp.hooks;
const { __ } = wp.i18n;

// Enable width control on the following blocks
const enableWidthControlOnBlocks = [
  'core/columns',
];

// Available width control options
const widthControlOptions = [
  {
    label: __( 'Wide' ),
    value: 'wide',
  },
  {
    label: __( 'Narrow' ),
    value: 'narrow',
  },
];

/**
 * Add width control attribute to block.
 *
 * @param {object} settings Current block settings.
 * @param {string} name Name of block.
 *
 * @returns {object} Modified block settings.
 */
const addWidthControlAttribute = ( settings, name ) => {
  // Do nothing if it's another block than our defined ones.
  if ( ! enableWidthControlOnBlocks.includes( name ) ) {
    return settings;
  }

  // Use Lodash's assign to gracefully handle if attributes are undefined
  settings.attributes = assign( settings.attributes, {
    width: {
      type: 'string',
      default: widthControlOptions[ 0 ].value,
    },
  } );

  return settings;
};

addFilter( 'blocks.registerBlockType', 'extend-block-example/attribute/width', addWidthControlAttribute );

/**
 * Create HOC to add width control to inspector controls of block.
 */
const withWidthControl = createHigherOrderComponent( ( BlockEdit ) => {
  return ( props ) => {
    // Do nothing if it's another block than our defined ones.
    if ( ! enableWidthControlOnBlocks.includes( props.name ) ) {
      return (
        <BlockEdit { ...props } />
      );
    }

    const { width } = props.attributes;

    // add has-width-xy class to block
    // if ( width ) {
    //   props.attributes.className = `${props.attributes.className} has-width ${ width }`;
    // }

    return (
      <Fragment>
        <BlockEdit { ...props } />
        <InspectorControls>
          <PanelBody
            title={ __( 'Width Control' ) }
            initialOpen={ true }
          >
            <SelectControl
              label={ __( 'Width' ) }
              value={ width }
              options={ widthControlOptions }
              onChange={ ( selectedWidthOption ) => {
                props.setAttributes( {
                  width: selectedWidthOption,
                } );
              } }
            />
          </PanelBody>
        </InspectorControls>
      </Fragment>
    );
  };
}, 'withWidthControl' );

addFilter( 'editor.BlockEdit', 'extend-block-example/with-width-control', withWidthControl );

/**
 * Add margin style attribute to save element of block.
 *
 * @param {object} saveElementProps Props of save element.
 * @param {Object} blockType Block type information.
 * @param {Object} attributes Attributes of block.
 *
 * @returns {object} Modified props of save element.
 */
const addWidthExtraProps = ( saveElementProps, blockType, attributes ) => {
  // Do nothing if it's another block than our defined ones.
  if ( ! enableWidthControlOnBlocks.includes( blockType.name ) ) {
    return saveElementProps;
  }

  const currentClassname = saveElementProps.className;

  // Use Lodash's assign to gracefully handle if attributes are undefined
  assign( saveElementProps, { className: `${currentClassname} ${attributes.width}` } );

  return saveElementProps;
};

addFilter( 'blocks.getSaveContent.extraProps', 'extend-block-example/get-save-content/extra-props', addWidthExtraProps );