/**
 * FILTER:
 *
 * Wrap core blocks in custom markup
 */

wp.hooks.addFilter(
    'blocks.getSaveElement',
    'prso/wrap-core-elements',
    prsoWrapCoreElementsFilter
);

function prsoWrapCoreElementsFilter( element, blockType, attributes ) {

    //Check for core/columns block
    if( blockType.name === 'core/columns' ) {

        console.log(attributes);

        const {className = ''} = attributes;

        return (
            <div className={`${className} wp-block-columns-wrapper`}>
                <div className="wp-block-columns-container">
                    {element}
                </div>
            </div>
        );

    }

    return element;
};