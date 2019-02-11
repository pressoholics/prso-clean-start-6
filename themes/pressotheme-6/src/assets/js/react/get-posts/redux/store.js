//Redux
import {createStore, applyMiddleware} from 'redux';
import thunk from 'redux-thunk';
import reducer from './reducers';

//Create redux store
const store = createStore(reducer, applyMiddleware(thunk));

export default store;