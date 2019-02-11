//REST API
import {restFetchPosts} from '../api/getData';

//Action Constants
export const GET_POSTS = 'react-get-posts/GET_POSTS'; // Get posts from REST API action name

//Register Actions
export const getPosts = posts => ({
    type: GET_POSTS,
    payload: posts,
});

//Actions functions

//Get posts action function
export const fetchPosts = ( requestParams ) => async dispatch => {

    try {

        const data = await restFetchPosts( requestParams );

        //dispatch GET_POSTS action
        dispatch(
            getPosts(data.posts)
        );

    } catch (e) {
        console.error('error', e);
    }

};
