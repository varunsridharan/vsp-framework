window.vsp_helper = require( 'vsp-js-helper/index' );

module.exports = ( ( $, window, document, wp ) => {
	window.VSP_FRAMEWORK = ( typeof Object.create !== 'undefined' ) ? Object.create( null ) : {};

	$.VSP_FRAMEWORK = {};

	$.VSP_FRAMEWORK.inline_ajax = ( e ) => {
		e.preventDefault();
		let $elem = $( e.currentTarget );

		if( $elem.hasClass( 'disabled' ) || $elem.hasClass( 'in-process' ) ) {
			return;
		}

		let url          = $elem.attr( 'href' ),
			method       = $elem.attr( 'data-method' ),
			trigger_code = $elem.attr( 'data-triggername' );

		if( method === undefined ) {
			method = 'GET';
		}

		new VSPAjax( {
			url: url, method: method
		}, {
			trigger_code: trigger_code, element: $elem, element_lock: true
		} );
	};

	$.VSP_FRAMEWORK.log_view_file_change = () => {
		$( '#vsp-log-view-wrap a.button' ).attr( 'disabled', 'disabled' );
		$( '.wponion-form' ).removeAttr( 'action' ).submit();
	};

	window.vsp_js_function = ( $data ) => window.vsp_helper.to_js_func( $data );

	$( () => {
		$( 'body' ).on( 'click', 'a.vsp-inline-ajax, button.vsp-inline-ajax', $.VSP_FRAMEWORK.inline_ajax );

		if( $( '#vsp-log-view-wrap' ).length > 0 ) {
			$( '#vsp-log-view-wrap' ).on( 'change', '.log-header select', $.VSP_FRAMEWORK.log_view_file_change );
		}
	} );

} )( jQuery, window, document, window.wp );

