import React from "react";
import propTypes from "prop-types";

export default class PageButton extends React.Component {

    static propTypes = {
        madeRequest: propTypes.bool.isRequired,
        handleLoadMore: propTypes.func.isRequired,
    }

    render() {

        const { i18n } = prsoThemeLocalVars.reactConfig;

        let buttonStateClass = 'loaded';

        if( this.props.madeRequest === true ) {
            buttonStateClass = 'loading';
        }

        return(
            <div className='load-more-wrapper'>
                <button
                    className={`button load-more ${buttonStateClass}`}
                    onClick={this.props.handleLoadMore}
                >
                    {i18n.loadMore}
                </button>
            </div>
        );

    }

}

//Default props
PageButton.defaultProps = {
    madeRequest: true,
};