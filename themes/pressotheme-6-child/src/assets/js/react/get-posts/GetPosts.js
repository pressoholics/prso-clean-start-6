import React from "react";

//Redux
import {connect} from 'react-redux';
import {fetchPosts, updateRestRequestParams} from './redux/actions';

//import components
import Results from "./components/Results";
import Pagination from "./components/Pagination";
import Filters from "./components/Filters";

//Data helpers
import {initalQueryParams, setupUrlQuery} from './api/getData';

export class GetPosts extends React.Component {

    componentDidMount() {

        if( this.shouldAppRender() ) {

            //Base app init request params
            let requestParams = initalQueryParams;

            //Get any valid params from the browser URL
            requestParams = setupUrlQuery( requestParams );

            //Update request params for initial app load state
            this.props.updateRestRequestParams( requestParams );

        }

    }

    shouldComponentUpdate( nextProps ) {

        const newRequestParams = nextProps.requestParams;

        //Make request to get posts based on new requestParams
        this.props.fetchPosts( newRequestParams );

        //This component should never update due to changes in requestParams prop
        return false;
    }

    /**
     * shouldAppRender
     *
     * Detect if we have react config local object
     *
     * @access public
     * @author Ben Moody
     */
    shouldAppRender = () => {

        const reactConfig = prsoThemeLocalVars.reactConfig;

        if (reactConfig) {
            return true;
        }

        return false;
    };

    render() {

        return (
            <React.Fragment>
                {this.shouldAppRender() ? (
                    <section id="react-get-posts">

                        <Filters/>

                        <div id='react-get-post-results'>
                            <div className="results-wrapper">

                                <Results/>

                            </div>
                        </div>

                        <Pagination/>

                    </section>
                ) : console.error('React config object missing')}
            </React.Fragment>
        );

    }

}

const mapStateToProps = state => ({
    requestParams: state.restParams,
});

export default connect(mapStateToProps, {fetchPosts, updateRestRequestParams})(GetPosts);