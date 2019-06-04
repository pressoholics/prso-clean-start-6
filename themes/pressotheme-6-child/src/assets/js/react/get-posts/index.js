/**
 * get-posts App
 *
 * This app will render results from a REST API endpoint request.
 * The App provides a pagination mechanism out of the box, and has the optional features of rendering Taxonomy filters and a search field
 *
 * Render the App via this markup in your page template: <div id="react-app-get-posts"></div>
 *
 * The App is configured via the 'reactConfig' node of the 'prsoThemeLocalVars' local JS object rendered by WP.
 *
 * Configure the App by changing the config array in the theme's functions.php file within the prso_theme_localize() OR
 * use the WP filter below to alter the config based on context:
 *
 * add_filter( 'prso_theme_localize__react_config', 'your_custom_react_config', 10, 1 );
 * function( $reactConfig ) { //context based config here// return reactConfig; }
 *
 * @access public
 * @author Ben Moody
 */
import 'react-app-polyfill/ie11';
import 'react-app-polyfill/stable';

import React from "react";
import ReactDOM from "react-dom";

//Redux store
import {Provider} from 'react-redux';
import store from './redux/store';

//app components
import App from './App';

const rootElement = document.getElementById("react-app-get-posts");

if (rootElement) {
    ReactDOM.render(
        <Provider store={store}>
            <App/>
        </Provider>,
        rootElement
    );
}
