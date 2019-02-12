export const initalQueryParams = {
    page: 1,
    per_page: prsoThemeLocalVars.reactConfig.perPage,
};

export const restFetchPosts = async ( requestParams ) => {

    const {restEndpoint, nonce} = prsoThemeLocalVars.reactConfig;
    const UrlParams = convertObjectToURLParams( requestParams );

    const requestUrl = `${restEndpoint}?${UrlParams}`;

    const response = await fetch(requestUrl, {
        method: 'GET',
        headers: {
            'content-type': 'application/json',
            'X-WP-Nonce': nonce,
        },
    });

    if (response.ok) {
        const posts = await response.json();

        //Dispatch order total pages action
        const totalPages = await parseInt(response.headers.get('X-WP-TotalPages'));

        return {
            posts,
            totalPages,
        };
    }

    const error = await response.json();
    throw new Error(error.message);

};

const convertObjectToURLParams = ( requestParams ) => {

    let str = "";

    for (const key in requestParams) {
        if (str != "") {
            str += "&";
        }
        str += key + "=" + encodeURIComponent(requestParams[key]);
    }

    return str;
};

/**
 * updateBrowserUrlParams
 *
 * Helper to update the browsers url with params passed as urlParams
 *
 * @access public
 * @author Ben Moody
 */
export const updateBrowserUrlParams = ( requestParams ) => {

    const urlParams = convertObjectToURLParams( requestParams );
    const browserUrl = window.location.toString();

    //Cache clean browser location url without any url params
    let clean_uri = browserUrl.substring(0, browserUrl.indexOf("?"));

    //cache version of clean url with url params
    clean_uri = `${clean_uri}?${urlParams}`;

    //Update browser url
    window.history.pushState('', '', '');

    //Update browser url
    window.history.replaceState({}, document.title, clean_uri);

};

/**
 * setupUrlQuery
 *
 * @CALLED BY componentDidMount
 *
 * Loops array of allowed filter url params and tries to get their values and cache them in selectedFilters ready for rest request
 *
 * @access public
 * @author Ben Moody
 */
export const setupUrlQuery = ( requestParams ) => {

    const allowedFilterUrlParams = prsoThemeLocalVars.reactConfig.requestParamsWhitelist;
    const urlParams = window.location.search.substr(1);

    if( urlParams.length < 1 ) {
        return requestParams;
    }

    let params = new URLSearchParams(urlParams);

    //Loop allowed filter params and see if we have any values
    allowedFilterUrlParams.map(filter => {

        const value = params.get(filter);

        if (value !== null) {

            if (filter === 'page') {

                requestParams.page = Number(value);

            } else if (filter === 'per_page') {

                requestParams.per_page = Number(value);

            } else if (filter === 's') {

                requestParams.search = value;

            } else if (filter === 'search') {

                requestParams.search = value;

            } else {

                requestParams.selectedFilters[filter] = Number(value);

            }

        }

    });

    return requestParams;
};