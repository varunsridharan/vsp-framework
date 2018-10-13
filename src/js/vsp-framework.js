/**
 * Validates And Handles JS Functions.
 * @param $data
 * @returns {*}
 */
function vsp_js_function ( $data ) {
	var $e = jQuery.VSPFRAMEWORK.validate_js_function( $data );
	return $e;
}

/**
 * VSP Framework Helper.
 * @type {{js_callback: (function(*=): *), init_wponion: VSP_HELPER.init_wponion, set_window_args: VSP_HELPER.set_window_args}}
 */
let VSP_HELPER = {
	/**
	 * Handles Javascript Callback.
	 * @param $data
	 * @returns {*}
	 */
	js_callback: function ( $data ) {
		var $e = jQuery.VSPFRAMEWORK.validate_js_function( $data );
		return $e;
	},

	/**
	 * Inits WPOnion Framework if exists.
	 * @param $place
	 */
	init_wponion: ( $place ) => {
		wponion_field( $place ).reload();
	},

	/**
	 * Converts JSON into Parseable Window Args.
	 * @param $args
	 */
	set_window_args: ( $args ) => {
		jQuery.each( $args, function ( $key, $value ) {
			window[ $key ] = $value;
		} )
	},
};

/**
 * Basic VSP Framework Setup.
 */
( function ( $, window, document ) {
	$.VSPFRAMEWORK = $.VSPFRAMEWORK || {};

	/**
	 * Handles Inline Ajax.
	 * @param e
	 */
	$.VSPFRAMEWORK.init_inline_ajax = function ( e ) {
		e.preventDefault();

		if ( $( this ).hasClass( "disabled" ) || $( this ).hasClass( "in-process" ) ) {
			return;
		}

		var $this        = $( this ),
			url          = $this.attr( "href" ),
			method       = $this.attr( "data-method" ),
			trigger_code = $this.attr( "data-triggername" );

		if ( method === undefined ) {
			method = 'GET';
		}

		new VSPAjax( { url: url, method: method }, { trigger_code: trigger_code, element: $this, element_lock: true } );
	};

	/**
	 * Converts Simple function string into JS functions.
	 * @param $string
	 * @returns {*}
	 */
	$.VSPFRAMEWORK.validate_single_function = function ( $string ) {
		if ( typeof $string === 'object' && $string[ 'js_args' ] !== undefined || $string[ 'js_contents' ] !== undefined ) {
			var $args     = ( $string[ 'js_args' ] === false ) ? false : $string[ 'js_args' ];
			var $contents = ( $string[ 'js_contents' ] === false ) ? false : $string[ 'js_contents' ];
			if ( $args === false && $contents !== false ) {
				return new Function( $contents );
			} else if ( $args !== false && $contents !== false ) {
				return new Function( $args, $contents );
			} else {
				return $string;
			}
		}
		return $string;
	};

	/**
	 * Handles a array of data to check if there any function string that needs to be converted.
	 * @param $data
	 * @returns {*}
	 */
	$.VSPFRAMEWORK.validate_js_function = function ( $data ) {
		if ( typeof $data === 'object' || $data === 'array' ) {
			for ( var $_d in $data ) {
				$data[ $_d ] = $.VSPFRAMEWORK.validate_single_function( $data[ $_d ] );
			}
		}
		return $data;

	};

	$( document ).ready( function () {
		if ( $( "#vsp-sys-status-report-text-btn" ).length > 0 ) {
			$( "#vsp-sys-status-report-text-btn" ).click( function ( e ) {
				e.preventDefault();
				var $textarea = $( this ).parent().parent().find( "textarea" );
				$textarea.slideDown( "slow" );
				$( this ).remove();
			} );
		}

		$( "body" ).on( "click", 'a.vsp-inline-ajax, button.vsp-inline-ajax', $.VSPFRAMEWORK.init_inline_ajax );
	} );
} )( jQuery, window, document );

