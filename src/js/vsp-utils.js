( function (window, document, $, wph) {
    'use strict';

    wph.addAction('wpsf_modal_search_ajax_start', function (baseHandler) {
        baseHandler.$response.block({
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0.8
            }
        })
    });

    wph.addAction('wpsf_modal_search_ajax_end', function (baseHandler) {
        baseHandler.$response.unblock();
    });

} )(window, document, jQuery, wp.hooks);