import React from "react";

//Redux
import {connect} from 'react-redux';
import {fetchPosts} from './redux/actions';

//import components
import Results from "./components/Results";

export class GetPosts extends React.Component {

    componentDidMount() {

        if( this.shouldAppRender() ) {

            const {perPage = 10} = prsoThemeLocalVars.reactConfig;
            const requestParams = {
                page: 1,
                per_page: perPage,
            };

            this.props.fetchPosts( requestParams );

        }

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
                        {/*<Filters/>*/}
                        <Results/>
                        {/*<Pagination/>*/}
                    </section>
                ) : console.error('React config object missing')}
            </React.Fragment>
        );

    }

}

export default connect(null, {fetchPosts})(GetPosts);