import React from "react";
import propTypes from "prop-types";

//Import App components
import RenderSingleFilter from './RenderSingleFilter';

export default class RenderFilters extends React.Component {

    static propTypes = {
        filtersConfig: propTypes.object.isRequired,
    };

    render() {

        const {filtersConfig = {}} = this.props;

        return(
            <React.Fragment>
            {Object.keys(filtersConfig).map((filterQueryKey) => (
                <RenderSingleFilter
                    key={filterQueryKey}
                    queryParamKey={filterQueryKey}
                    filterConfig={filtersConfig[filterQueryKey]}
                />
            ))}
            </React.Fragment>
        );

    }

}
