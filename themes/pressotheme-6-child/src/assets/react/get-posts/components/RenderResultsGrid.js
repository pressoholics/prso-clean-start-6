import React from "react";

//Redux
import {connect} from 'react-redux';

export class RenderResultsGrid extends React.Component {

    i18n = prsoThemeLocalVars.reactConfig.i18n;

    renderTemplatePart = (post) => ({
        __html: post.html
    });

    /**
     * render
     *
     * If we have made a request and have no data, render no results alert box
     *
     * If we have data after a request, loop data and render html node contents into DOM
     * @access public
     * @author Ben Moody
     */
    render() {

        const {resultsData = [], requestStatus} = this.props;
        const {noResultsText = ''} = this.i18n;

        const childElements = resultsData.map(post => (
            <li className='item' key={`${post.title.rendered}-${post.id}`} dangerouslySetInnerHTML={this.renderTemplatePart(post)}></li>
        ));

        if (resultsData.length > 0) {

            return (
                <ul id="the-results-grid">
                    {childElements}
                </ul>
            );

        } else if (requestStatus.madeRequest === false) {

            return (
                <div className='no-results'>
                    <div data-alert className="alert-box">
                        {noResultsText}
                    </div>
                </div>
            );

        } else {
            return null;
        }
    }

}

RenderResultsGrid.defaultProps = {
    requestStatus: {
        madeRequest: false,
        requestStatus: true,
    }
};

const mapStateToProps = state => ({
    resultsData: state.posts,
    requestStatus: state.restStatus,
});

export default connect(mapStateToProps)(RenderResultsGrid);