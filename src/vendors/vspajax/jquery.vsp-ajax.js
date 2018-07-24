( function ( window ) {
	'use strict';

	function define_VSPAjax () {
		var VSPAjax = function ( AjaxOptions, Options ) {
			this.ajax( AjaxOptions, Options );
		};
		return VSPAjax;
	}

	function define_VSPAjaxQ () {
		var VSPAjaxQ = function () {
			return this.init();
		};
		return VSPAjaxQ;
	}

	if ( typeof ( Library ) === 'undefined' ) {
		window.VSPAjax  = define_VSPAjax();
		window.VSPAjaxQ = define_VSPAjaxQ();
	}

	VSPAjax.prototype = {
		VERSION: '1.0',
		_Ajax: '',
		_default_ajax_options: {
			method: "GET",
			data: '',
			url: ajaxurl,
		},
		_AjaxOptions: {},
		_Options: {},

		_mergeArray: function () {
			var args   = Array.prototype.slice.call( arguments ),
				argl   = args.length,
				arg,
				retObj = {},
				k      = '',
				argil  = 0,
				j      = 0,
				i      = 0,
				ct     = 0,
				toStr  = Object.prototype.toString,
				retArr = true;
			for ( i = 0; i < argl; i++ ) {
				if ( toStr.call( args[ i ] ) !== '[object Array]' ) {
					retArr = false
					break;
				}
			}
			if ( retArr ) {
				retArr = [];
				for ( i = 0; i < argl; i++ ) {
					retArr = retArr.concat( args[ i ] );
				}
				return retArr;
			}
			for ( i = 0, ct = 0; i < argl; i++ ) {
				arg = args[ i ];
				if ( toStr.call( arg ) === '[object Array]' ) {
					for ( j = 0, argil = arg.length; j < argil; j++ ) {
						retObj[ ct++ ] = arg[ j ];
					}
				} else {
					for ( k in arg ) {
						if ( arg.hasOwnProperty( k ) ) {
							if ( parseInt( k, 10 ) + '' === k ) {
								retObj[ ct++ ] = arg[ k ];
							} else {
								retObj[ k ] = arg[ k ];
							}
						}
					}
				}
			}
			return retObj;
		},
		_set_Options: function ( $Options ) {
			if ( $Options !== undefined ) {
				this._Options = $Options;
			}
		},
		_set_ajax_options: function ( $Options ) {
			if ( $Options !== undefined ) {
				this._AjaxOptions = this._mergeArray( this._default_ajax_options, $Options );
			}
		},
		_BodyTrigger: function ( $status ) {
			if ( this._Options.trigger_code !== undefined ) {
				jQuery( "body" ).trigger( this._Options.trigger_code, [ $status, Options.element, this._AjaxOptions ] );
			}
		},
		_FuncTrigger: function ( $status, $args ) {
			if ( $status == 'before' && typeof this._Options.before === 'function' ) {
				this._Options.before( $args );
			} else if ( $status == 'after' && typeof this._Options.after === 'function' ) {
				this._Options.after( $args );
			} else if ( $status == 'onSuccess' && typeof this._AjaxOptions.OnSuccess === 'function' ) {
				this._AjaxOptions.OnSuccess( $args );
			} else if ( $status == 'onError' && typeof this._AjaxOptions.onError === 'function' ) {
				this._AjaxOptions.onError( $args );
			} else if ( $status == 'OnAlways' && typeof this._AjaxOptions.OnAlways === 'function' ) {
				this._AjaxOptions.OnAlways( $args );
			} else if ( $status == 'AjaxQ' ) {
				jQuery( "body" ).trigger( 'vsp-ajaxq' );
			}
		},
		_handle_response: function ( res ) {
			if ( res.data !== undefined ) {
				if ( res.data.msg !== undefined ) {
					this._Options.response_element.html( res.data.msg );
					return false;
				}
			}
			return true;
		},
		_string_function_callback: function ( callback ) {
			try {
				window[ callback ]();
			} catch ( err ) {
				console.log( err );
			}
		},
		_array_function_callback: function ( callback ) {
			jQuery.each( callback, function ( key, value ) {
				this._string_function_callback( value );
			} )
		},
		_handle_callback: function ( res ) {
			var $this = this;
			if ( res.data !== undefined ) {
				if ( res.data.callback !== undefined ) {
					if ( typeof res.data.callback == 'string' ) {
						$this._string_function_callback( res.data.callback );
					} else if ( typeof res.data.callback == 'object' || typeof res.data.callback == 'array' ) {

						jQuery.each( res.data.callback, function ( key, value ) {
							if ( key == parseInt( key ) ) {
								if ( typeof value == 'string' ) {
									$this._string_function_callback( value );
								} else if ( typeof value == 'object' || typeof value == 'array' ) {
									$this._array_function_callback( value );
								}
							} else {
								try {
									var CB = new Function( key, value );
									CB();
								} catch ( arr ) {
									console.log( arr );
								}
							}
						} )

					}
				}
			}
		},
		ajax: function ( AjaxOptions, Options ) {
			var $self = this;
			this._set_ajax_options( AjaxOptions );
			this._set_Options( Options );

			if ( AjaxOptions.ajax !== undefined ) {
				this._set_ajax_options( AjaxOptions.ajax );
			}

			if ( AjaxOptions.options !== undefined ) {
				this._set_Options( AjaxOptions.options );
			}

			this._FuncTrigger( "before" );
			this._BodyTrigger( "before" );

			if ( this._Options.response_element === undefined ) {
				this._Options.response_element = jQuery( ".inline-ajax-response" );
			}

			this._Ajax = jQuery.ajax( this._AjaxOptions );
			this._Ajax.done( function ( res ) {
				$self._FuncTrigger( "onSuccess", res );
				$self._BodyTrigger( "success" );
				$self._handle_response( res );
				$self._handle_callback( res );
			} );
			this._Ajax.fail( function ( res ) {
				$self._handle_response( res.responseJSON.data );
				$self._handle_callback( res );
				$self._FuncTrigger( "onError", res );
				$self._BodyTrigger( "failed" );
			} );
			this._Ajax.always( function ( res ) {
				$self._FuncTrigger( "OnAlways", res );
				$self._FuncTrigger( "AjaxQ" );
				$self._BodyTrigger( "always" );
			} );

			this._FuncTrigger( "after" );
			this._BodyTrigger( "after" );
		}
	};

	VSPAjaxQ.prototype = {
		ajax_queue: [],
		is_ajax_ongoing: false,
		init: function () {
			var $self = this;
			jQuery( "body" ).on( "vsp-ajaxq", function () {
				if ( $self.ajax_queue[ 0 ] !== undefined ) {
					var $elem = $self.ajax_queue[ 0 ].elem;
					$elem.removeClass( "vspajaxq-in-queue" );
					$self.ajax_queue.shift();
					$self.kick_start_ajax();
				} else {
					this.is_ajax_ongoing = false;
				}
			} );
			return this;
		},
		kick_start_ajax: function () {
			this.is_ajax_ongoing = true;
			if ( this.ajax_queue[ 0 ] !== undefined ) {
				new VSPAjax( this.ajax_queue[ 0 ].data )
			} else {
				this.is_ajax_ongoing = false;
			}
		},
		add: function ( $ele, $data ) {
			if ( !$ele.hasClass( "vspajaxq-in-queue" ) ) {
				this.ajax_queue.push( {
					elem: $ele,
					data: $data
				} );
				$ele.addClass( "vspajaxq-in-queue" );

				if ( this.is_ajax_ongoing === false ) {
					this.kick_start_ajax();
				}
			}
		},
	};
} )( window );