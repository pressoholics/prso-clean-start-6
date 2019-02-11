import React from "react";

//Import App components
import SearchField from './ui/SearchField';
import ResetButton from './ui/ResetButton';
import RenderFilters from './RenderFilters';

export default class Filters extends React.Component {

    renderSearch = prsoThemeLocalVars.reactConfig.search;
    filters = prsoThemeLocalVars.reactConfig.filters;

    shouldComponentUpdate() {

        //Should we render Filters?
        return this.shouldRenderFilters();

    }

    shouldRenderFilters = () => {

        //Do we have a search field configured?
        if( this.renderSearch === true ) {
            return true;
        }

        //Do we have filters?
        if( this.filters !== false ) {
            return true;
        }

        return false;
    };

    /**
     * submitHandler
     *
     * Helper to prevent form element default action
     *
     * @access public
     * @author Ben Moody
     */
    submitHandler = (event) => {
        event.preventDefault();
    };

    render() {

        if( this.shouldRenderFilters() ) {

            return(
                <div id="filters-container">
                    <form id='the-filters' onSubmit={this.submitHandler}>
                        {this.filters !== false && <RenderFilters filtersConfig={this.filters}/>}
                        {this.renderSearch && <SearchField />}
                        <ResetButton />
                    </form>
                </div>
            );

        } else {
            return null;
        }

    }

}