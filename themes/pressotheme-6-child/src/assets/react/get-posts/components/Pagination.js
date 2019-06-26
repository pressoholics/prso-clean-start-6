import React from "react";

//Redux
import {connect} from 'react-redux';
import {updateRestRequestParams} from '../redux/actions';

//App components
import PageButton from './ui/PageButton';

export class Pagination extends React.Component {

    paginationType = prsoThemeLocalVars.reactConfig.paginationType;

    handleLoadMore = () => {

        const newPage = this.props.requestParams.page + 1;

        if( newPage <= this.props.totalPages ) {

            //Update request params for initial app load state
            this.props.updateRestRequestParams({
                page: newPage,
            });

        }

    };

    render() {

        const {page} = this.props.requestParams;
        const {madeRequest} = this.props.requestStatus;
        const {totalPages} = this.props;

        if( (totalPages > 1) && (page < totalPages) ) {

            //switch pagination type and render correct component
            switch (this.paginationType) {
                default :
                    return (
                        <PageButton madeRequest={madeRequest} handleLoadMore={() => this.handleLoadMore()}/>
                    );
            }

        } else {
            return null;
        }

    }

}

//Default props
Pagination.defaultProps = {
    requestParams: {page: 1},
    requestStatus: {madeRequest: true},
    totalPages: 1,
};

const mapStateToProps = state => ({
    requestStatus: state.restStatus,
    requestParams: state.restParams,
    totalPages: state.requestTotalPages
});

export default connect(mapStateToProps, {updateRestRequestParams})(Pagination);