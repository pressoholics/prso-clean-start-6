import React from "react";

//Redux
import {connect} from 'react-redux';

export class RenderResultsGrid extends React.Component {

    i18n = prsoThemeLocalVars.reactConfig.i18n;

    renderTemplatePart = ( post ) => ({
        __html: post.html
    });

    render() {

        const {resultsData = []} = this.props;
        const {noResultsText = ''} = this.i18n;

        const childElements = resultsData.map(post => (
            <li key={`${post.title.rendered}-${post.id}`} dangerouslySetInnerHTML={this.renderTemplatePart( post )}></li>
        ));
        
        return (
            <React.Fragment>
                {resultsData.length > 0 ? (
                    <ul id="the-results-grid">
                        {childElements}
                    </ul>
                ) : (
                    <div className='no-results'>
                        <div data-alert className="alert-box">
                            {noResultsText}
                        </div>
                    </div>
                )}
            </React.Fragment>
        );
    }

}

const mapStateToProps = state => ({
    resultsData: state.posts,
});

export default connect(mapStateToProps)(RenderResultsGrid);