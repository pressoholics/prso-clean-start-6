import React from "react";
import ReactDOM from "react-dom";

//Redux store
import {Provider} from 'react-redux';
import store from './redux/store';

//app components
import App from './App';

const rootElement = document.getElementById("react-app-get-posts");

if(rootElement) {
    ReactDOM.render(
        <Provider store={store}>
            <App />
        </Provider>,
        rootElement
    );
}
