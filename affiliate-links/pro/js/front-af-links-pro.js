/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function getQueryParams(qs) {
    qs = qs.split('+').join(' ');

    let params = {},
        tokens,
        re = /[?&]?([^=]+)=([^&]*)/g;

    while (tokens = re.exec(qs)) {
        params[decodeURIComponent(tokens[1])] = decodeURIComponent(tokens[2]);
    }

    return params;
}


(function($){
    const DOUBLE_OPEN_GET_PARAM = 'afbclid';

    $(document).on('click', 'a', function () {
        const paramsString = this.href ? this.href.split('?')[1] : null;

        if (!paramsString) return;

        const params = getQueryParams(paramsString);

        if (!params[DOUBLE_OPEN_GET_PARAM]) return;

        const urlWithoutGetParams = this.href.split('?')[0];

        window.open(urlWithoutGetParams);
    })
})(jQuery)