import React from "react";

//imported componetns
import RenderResultsGrid from "./RenderResultsGrid";

export default class Results extends React.Component {

    render() {
        const {loadingData = false } = this.props;

        return(
         <div id='react-get-post-results'>

             <div className="results-wrapper">
                 {loadingData ? (
                     <div className='loading-spinner'>
                         <i className="fa fa-circle-o-notch fa-spin" aria-hidden="true"></i>
                     </div>
                 ) : (
                     <React.Fragment>
                         <RenderResultsGrid />
                     </React.Fragment>
                 )}
            </div>
         </div>
        );
    }

}