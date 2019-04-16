module.exports = ( ( $, window, document, wp ) => {
	window.vsp_helper    = window.$wponion_helper;
	window.VSP_FRAMEWORK = ( typeof Object.create !== 'undefined' ) ? Object.create( null ) : {};

	$.VSP_FRAMEWORK = {};

	$.VSP_FRAMEWORK.log_view_file_change = () => {
		$( '#vsp-log-view-wrap a.button' ).attr( 'disabled', 'disabled' );
		$( '.wponion-form' ).removeAttr( 'action' ).submit();
	};

	window.vsp_js_function = ( $data ) => window.vsp_helper.to_js_func( $data );

	$( () => {
		if( $( '#vsp-log-view-wrap' ).length > 0 ) {
			$( '#vsp-log-view-wrap' ).on( 'change', '.log-header select', $.VSP_FRAMEWORK.log_view_file_change );
		}
	} );

} )( jQuery, window, document, window.wp );

