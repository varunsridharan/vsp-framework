( function ( $, window, document ) {

	$.VSPFRAMEWORK = $.VSPFRAMEWORK || {};

	$.VSP_HELPER = $.VSP_HELPER || {};

	$.VSP_HELPER.string_function_callback = function ( callback ) {
		try {
			window[ callback ]();
		} catch ( err ) {
			console.log( err );
		}
	};

	$.VSP_HELPER.array_function_callback = function ( callback ) {
		$.each( callback, function ( key, value ) {
			ecollab_string_function_callback( value );
		} )
	};

	$.VSP_HELPER.function_callback = function ( key, value ) {
		var $return = false;
		try {
			var CB = new Function( key, value );
			CB();
		} catch ( err ) {
			console.log( err );
		}

		return $return;
	};

	$.VSP_HELPER.ajax_callback = function ( res ) {
		if ( res.callback !== undefined ) {
			if ( typeof res.callback === 'string' ) {
				var callback = res.callback;
				$.VSP_HELPER.string_function_callback( callback );
			} else if ( typeof res.callback === 'object' || typeof res.callback == 'array' ) {
				$.each( res.callback, function ( key, value ) {
					if ( key === parseInt( key ) ) {
						if ( typeof value === 'string' ) {
							$.VSP_HELPER.string_function_callback( value );
						} else if ( typeof value === 'object' || typeof value == 'array' ) {
							$.VSP_HELPER.array_function_callback( value );
						}
					} else {
						$.VSP_HELPER.function_callback( key, value );
					}
				} )

			}
		}
	};

	$.VSPFRAMEWORK.get_element_args = function ( elem, $options ) {
		var $final_data = {};

		$.each( $options, function ( key, defaults ) {
			var $data = elem.data( key );
			if ( $data === undefined ) {
				$final_data[ key ] = defaults;
			} else {
				$final_data[ key ] = $data;
			}
		} );

		return $final_data;
	};

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

		new VSPAjax( {
			url: url,
			method: method,
		}, {
			trigger_code: trigger_code,
			element: $this,
			element_lock: true,
		} );
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
