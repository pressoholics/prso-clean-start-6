import React from "react";

//Redux
import {connect} from 'react-redux';

//imported app components
import RenderResultsGrid from "./RenderResultsGrid";

export class Results extends React.Component {

    /**
     * render
     *
     * If the app is currently making an async REST api request show the loading spinner.
     * If not, render the results via RenderResultsGrid component
     *
     * @param type name
     * @var type name
     * @return type name
     * @access public
     * @author Ben Moody
     */
    render() {
        const {madeRequest} = this.props.requestStatus;
        const {page} = this.props.requestParams;

        if (madeRequest) {

            return (
                <React.Fragment>
                    {page > 1 &&
                    <RenderResultsGrid/>
                    }

                    <div className='loading-spinner'>
                        <i className="fa fa-circle-o-notch fa-spin" aria-hidden="true"></i>
                    </div>
                </React.Fragment>
            );

        } else {

            return (
                <RenderResultsGrid/>
            );

        }

    }

}

Results.defaultProps = {
    requestStatus: {
        madeRequest: false,
        requestStatus: true,
    }
};

const mapStateToProps = state => ({
    requestStatus: state.restStatus,
    requestParams: state.restParams,
});

export default connect(mapStateToProps)(Results);