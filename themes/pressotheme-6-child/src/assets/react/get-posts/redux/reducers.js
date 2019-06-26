import {combineReducers} from 'redux';

import {
    UPDATE_REQUEST_PARAMS,
    RESET_REQUEST_PARAMS,
    INITAL_POSTS,
    PAGED_POSTS,
    MADE_POSTS_REQUEST,
    POSTS_REQUEST_STATUS,
    TOTAL_PAGES
} from './actions';

const restStatusReducer = (state = {madeRequest: false, requestStatus: true}, action) => {

    switch (action.type) {
        case MADE_POSTS_REQUEST:

            return {
                ...state,
                madeRequest: action.payload,
            };

        case POSTS_REQUEST_STATUS:

            return {
                ...state,
                requestStatus: action.payload,
            };

        default:
            return state;
    }

};

const requestParamsReducer = (state = [], action) => {

    switch (action.type) {
        case UPDATE_REQUEST_PARAMS:

            return {
                ...state,
                ...action.payload,
            };
        case RESET_REQUEST_PARAMS:

            return action.payload;

        default:
            return state;
    }

};

const postsReducer = (state = [], action) => {

    switch (action.type) {
        case PAGED_POSTS:

            //Payload is paged results so merge with current state
            return [
                ...state,
                ...action.payload,
            ];

        case INITAL_POSTS:

            //Payload is first page of posts so override current post state
            return action.payload;

        default:
            return state;
    }

};

const requestTotalPagesReducer = (state = [], action) => {

    switch (action.type) {
        case TOTAL_PAGES:

            return action.payload;

        default:
            return state;
    }

};

const reducer = combineReducers({
    posts: postsReducer,
    restParams: requestParamsReducer,
    restStatus: restStatusReducer,
    requestTotalPages: requestTotalPagesReducer,
});

export default reducer;