( function ( $, window, document ) {
	$.VSPFRAMEWORK                  = $.VSPFRAMEWORK || {};
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
