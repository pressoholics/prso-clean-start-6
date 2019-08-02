//https://www.liip.ch/en/blog/how-to-extend-existing-gutenberg-blocks-in-wordpress

import assign from 'lodash.assign';

const { createHigherOrderComponent } = wp.compose;
const { Fragment } = wp.element;
const { InspectorControls } = wp.editor;
const { PanelBody, SelectControl } = wp.components;
const { addFilter } = wp.hooks;
const { __ } = wp.i18n;

// Enable background control on the following blocks
const enableBackgroundControlOnBlocks = [
  'core/columns',
  'prso/content-section',
];

// Available background control options
const backgroundControlOptions = [
  {
    label: __( 'None' ),
    value: '',
  },
  {
    label: __( 'Light' ),
    value: 'bg-light',
  },
  {
    label: __( 'Dark' ),
    value: 'bg-dark',
  },
];

/**
 * Add background control attribute to block.
 *
 * @param {object} settings Current block settings.
 * @param {string} name Name of block.
 *
 * @returns {object} Modified block settings.
 */
const addBackgroundControlAttribute = ( settings, name ) => {
  // Do nothing if it's another block than our defined ones.
  if ( ! enableBackgroundControlOnBlocks.includes( name ) ) {
    return settings;
  }

  // Use Lodash's assign to gracefully handle if attributes are undefined
  settings.attributes = assign( settings.attributes, {
    background: {
      type: 'string',
      default: backgroundControlOptions[ 0 ].value,
    },
  } );

  return settings;
};

addFilter( 'blocks.registerBlockType', 'extend-block-example/attribute/background', addBackgroundControlAttribute );

/**
 * Create HOC to add background control to inspector controls of block.
 */
const withBackgroundControl = createHigherOrderComponent( ( BlockEdit ) => {
  return ( props ) => {
    // Do nothing if it's another block than our defined ones.
    if ( ! enableBackgroundControlOnBlocks.includes( props.name ) ) {
      return (
        <BlockEdit { ...props } />
      );
    }

    const { background } = props.attributes;

    // add has-background-xy class to block
    if ( background ) {
      props.attributes.className = `has-background ${ background }`;
    }

    return (
      <Fragment>
        <BlockEdit { ...props } />
        <InspectorControls>
          <PanelBody
            title={ __( 'Background Control' ) }
            initialOpen={ true }
          >
            <SelectControl
              label={ __( 'Background Colour' ) }
              value={ background }
              options={ backgroundControlOptions }
              onChange={ ( selectedBackgroundOption ) => {
                props.setAttributes( {
                  background: selectedBackgroundOption,
                } );
              } }
            />
          </PanelBody>
        </InspectorControls>
      </Fragment>
    );
  };
}, 'withBackgroundControl' );

addFilter( 'editor.BlockEdit', 'extend-block-example/with-background-control', withBackgroundControl );

/**
 * Add margin style attribute to save element of block.
 *
 * @param {object} saveElementProps Props of save element.
 * @param {Object} blockType Block type information.
 * @param {Object} attributes Attributes of block.
 *
 * @returns {object} Modified props of save element.
 */
const addBackgroundExtraProps = ( saveElementProps, blockType, attributes ) => {
  // Do nothing if it's another block than our defined ones.
  if ( ! enableBackgroundControlOnBlocks.includes( blockType.name ) ) {
    return saveElementProps;
  }

  const currentClassname = saveElementProps.className;

  // Use Lodash's assign to gracefully handle if attributes are undefined
  assign( saveElementProps, { className: `${currentClassname} ${attributes.background}` } );

  return saveElementProps;
};

//addFilter( 'blocks.getSaveContent.extraProps', 'extend-block-example/get-save-content/extra-props', addBackgroundExtraProps );