//Redux
import {createStore, applyMiddleware} from 'redux';
import thunk from 'redux-thunk';
import reducer from './reducers';

//Create redux store
const store = createStore(reducer, applyMiddleware(thunk));

//Dev debug build
// const composeEnhancers = window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__ || compose;
// const store = createStore(reducer, /* preloadedState, */ composeEnhancers(
//     applyMiddleware(thunk)
// ));

export default store;