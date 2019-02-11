import {combineReducers} from 'redux';

import {GET_POSTS} from './actions';

const postsReducer = ( state = [], action ) => {

    switch (action.type) {
        case GET_POSTS:

            return [
                ...state,
                ...action.payload,
            ];

        default:
            return state;
    }

};

const reducer = combineReducers({
    posts: postsReducer,
});

export default reducer;