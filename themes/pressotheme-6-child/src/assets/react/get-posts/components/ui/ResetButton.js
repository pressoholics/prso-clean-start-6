import React from "react";
import propTypes from "prop-types";

//Redux
import {connect} from 'react-redux';
import {resetRestRequestParams} from "../../redux/actions";

export class ResetButton extends React.Component {

    shouldComponentUpdate() {
        return false;
    }

    resetRequestQuery = () => {

        //Clear form
        document.getElementById("the-filters").reset();

        //Update request params for initial app load state
        this.props.resetRestRequestParams();

    };

    render() {

        const { i18n } = prsoThemeLocalVars.reactConfig;

        return(
            <button
                id='the-reset-button'
                className='button reset'
                onClick={(event) => this.resetRequestQuery(event)}
            >
                {i18n.resetButton}
            </button>
        );

    }

}

export default connect(null, {resetRestRequestParams})(ResetButton);