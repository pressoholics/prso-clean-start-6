import React from "react";
import propTypes from "prop-types";

//Import App components
import SelectField from './ui/SelectField';

export default class RenderSingleFilter extends React.Component {

    static propTypes = {
        queryParamKey: propTypes.string.isRequired,
        filterConfig: propTypes.object.isRequired,
    };

    /**
    * render
    *
    * Detect filter type and pass config props to correct Component to render the filter
    *
    * @access public
    * @author Ben Moody
    */
    render() {

        const {type = 'select', defaultValue = 'Select Option', terms = []} = this.props.filterConfig;

        return(
            <React.Fragment>
                {type === 'select' ? (
                    <SelectField queryParamKey={this.props.queryParamKey} values={terms} defaultValue={defaultValue} />
                ) : (
                    <div></div>
                    // <RadioField  values={terms} defaultValue={defaultValue} />
                )}
            </React.Fragment>
        );

    }

}