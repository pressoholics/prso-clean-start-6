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

    const errMessage = await response.json();
    throw new Error(errMessage.message);

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