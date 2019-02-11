//REST API
import {initalQueryParams, restFetchPosts, updateBrowserUrlParams} from '../api/getData';

//Action Constants
export const UPDATE_REQUEST_PARAMS = 'react-get-posts/UPDATE_REQUEST_PARAMS'; // Update the REST request params
export const RESET_REQUEST_PARAMS = 'react-get-posts/RESET_REQUEST_PARAMS'; // Reset the REST request params to inital state

export const MADE_POSTS_REQUEST = 'react-get-posts/MADE_POSTS_REQUEST'; // fetching posts from REST
export const POSTS_REQUEST_STATUS = 'react-get-posts/POSTS_REQUEST_SUCCESS'; // fetching posts status

export const INITAL_POSTS = 'react-get-posts/INITAL_POSTS'; // Get first page of posts from REST API action name
export const PAGED_POSTS = 'react-get-posts/PAGED_POSTS'; // Get another page of posts from REST API action name
export const TOTAL_PAGES = 'react-get-posts/TOTAL_PAGES'; // Set total pages of data for last request

//Register Actions
export const updateRequestParams = requestParams => ({
    type: UPDATE_REQUEST_PARAMS,
    payload: requestParams,
});

export const resetRequestParams = requestParams => ({
    type: RESET_REQUEST_PARAMS,
    payload: requestParams,
});

export const getInitialPosts = posts => ({
    type: INITAL_POSTS,
    payload: posts,
});

export const getNextPagePosts = posts => ({
    type: PAGED_POSTS,
    payload: posts,
});

export const cachTotalPages = totalPages => ({
    type: TOTAL_PAGES,
    payload: totalPages,
});

//Actions functions

//Update the rest request params object
export const updateRestRequestParams = ( newRequestParams ) => dispatch => {

    //dispatch UPDATE_REQUEST_PARAMS action
    dispatch(
        updateRequestParams( newRequestParams )
    );

};

//Reset the rest request params object to inital state
export const resetRestRequestParams = () => dispatch => {

    //dispatch RESET_REQUEST_PARAMS action
    dispatch(
        resetRequestParams( initalQueryParams )
    );

};

//Get posts action function
export const fetchPosts = ( requestParams ) => async dispatch => {

    dispatch({
        type: MADE_POSTS_REQUEST,
        payload: true,
    });

    try {

        const data = await restFetchPosts( requestParams );
        const {page = 1} = requestParams;

        if( page === 1 ) {

            //dispatch INITAL_POSTS action
            dispatch(
                getInitialPosts(data.posts)
            );

        } else {

            //dispatch PAGED_POSTS action
            dispatch(
                getNextPagePosts(data.posts)
            );

        }

        //Cache total pages of data for request
        dispatch(
            cachTotalPages(data.totalPages)
        );

        //Update browser history state with new URL params
        updateBrowserUrlParams( requestParams );

        dispatch({
            type: MADE_POSTS_REQUEST,
            payload: false,
        });

        dispatch({
            type: POSTS_REQUEST_STATUS,
            payload: true,
        });

    } catch (e) {

        console.error('error', e);

        dispatch({
            type: MADE_POSTS_REQUEST,
            payload: false,
        });

        dispatch({
            type: POSTS_REQUEST_STATUS,
            payload: false,
        });
    }

};
