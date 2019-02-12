import React from "react";
import propTypes from "prop-types";

//Redux
import {connect} from 'react-redux';
import {updateRestRequestParams} from "../../redux/actions";

//Import App components

export class SelectField extends React.Component {

    static propTypes = {
        values: propTypes.array.isRequired,
        defaultValue: propTypes.string.isRequired,
        queryParamKey: propTypes.string.isRequired,
    };

    /**
     * onFilterChange
     *
     * Callback for Select onChange event
     *
     * If value from select is false, this is a reset request so delete filter node from request params object
     *
     * If it's a valid value, update the reqeust params state via redux action updateRestRequestParams()
     *
     * @access public
     * @author Ben Moody
     */
    onFilterChange = (event) => {

        const {queryParamKey, requestParams} = this.props;
        let value = event.target.value;

        //If default value
        if (value === 'false') {

            //Remove filter value
            delete requestParams[queryParamKey];

            this.props.updateRestRequestParams(requestParams);

        } else {

            //Update request params with this filters params
            this.props.updateRestRequestParams({
                [queryParamKey]: value
            });

        }

    };

    render() {

        const {queryParamKey, defaultValue, values} = this.props;

        return (
            <React.Fragment>
                <select id={queryParamKey} onChange={this.onFilterChange} defaultValue='false'>
                    <option value='false' key='default'>{defaultValue}</option>
                    {values.map(term => (
                        <option value={term.term_id} key={term.slug}>{term.name}</option>
                    ))}
                </select>
            </React.Fragment>
        );

    }

}

const mapStateToProps = state => ({
    requestParams: state.restParams,
});

export default connect(mapStateToProps, {updateRestRequestParams})(SelectField);