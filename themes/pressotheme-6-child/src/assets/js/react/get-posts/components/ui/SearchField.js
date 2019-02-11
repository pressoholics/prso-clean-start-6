import React from "react";

//Redux
import {connect} from 'react-redux';
import {updateRestRequestParams} from "../../redux/actions";

//Import App components

export class SearchField extends React.Component {

    typingTimeout = 0;
    typingTimeoutLength = 1200;
    searchQueryParams = {};

    //Initial component state
    state = {
        searchVal: '',
    };

    componentDidUpdate( prevProps ) {

        if( prevProps.requestParams.search !== this.props.requestParams.search ) {

            //Update search query value in component state
            this.setState({
                searchVal: this.props.requestParams.search
            });

        }

    }

    shouldComponentUpdate( nextProps, nextState ) {

        //update when search node in request params changes
        if( nextProps.requestParams.search !== this.props.requestParams.search ) {
            return true;
        }

        //update if component searchVal state changes
        if( nextState.searchVal !== this.state.searchVal ) {
            return true;
        }

        return false;
    }

    /**
     * handleOnChange
     *
     * @CALLED BY onChange
     *
     * Pass input value to callback function on input change
     *
     * @access public
     * @author Ben Moody
     */
    handleOnChange = (event) => {

        const searchQuery = event.target.value;

        //Update search query in component state
        this.setState({
            searchVal: searchQuery
        });

        //Maybe clear timeout due to user input
        this.maybeClearTimeout();

        this.searchQueryParams = {
            page: 1, //reset to first page
            search: searchQuery,
        };

        //Set timeout to make rest api request based on query string
        this.typingTimeout = setTimeout(this.updateSearchQueryParams, this.typingTimeoutLength);

    };

    updateSearchQueryParams = () => {

        //Update request params with new search value
        this.props.updateRestRequestParams( this.searchQueryParams );

    };

    /**
     * maybeClearTimeout
     *
     * @CALLED BY handleSearchQueryChange
     *
     * Helper to clear the search query input field timeout
     *
     * @access public
     * @author Ben Moody
     */
    maybeClearTimeout = () => {

        if (this.typingTimeout) {
            clearTimeout(this.typingTimeout);
        }

    };

    /**
     * handleEnterPress
     *
     * @CALLED BY onKeyPress
     *
     * Detect ENTER keypress in input and prevent default action
     *
     * @access public
     * @author Ben Moody
     */
    handleEnterPress = (event) => {
        if (event.which == '13') {
            event.preventDefault();
        }
    };

    render() {

        const { i18n } = prsoThemeLocalVars.reactConfig;
        const { searchVal = '' } = this.state;

        return(
            <div id='the-search-filter'>
                <input
                    id='search-query'
                    type='text'
                    placeholder={i18n.searchPlaceholder}
                    onChange={(event) => this.handleOnChange(event)}
                    onKeyPress={(event) => this.handleEnterPress(event)}
                    value={searchVal}
                />
            </div>
        );

    }

}

//Default props
SearchField.defaultProps = {
    requestParams: {search: ''},
};

const mapStateToProps = state => ({
    requestParams: state.restParams,
});

export default connect(mapStateToProps, {updateRestRequestParams})(SearchField);