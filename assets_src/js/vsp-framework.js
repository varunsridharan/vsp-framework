module.exports = ( ( $, window, document, wp ) => {
	window.vsp_helper    = window.$wponion_helper;
	window.VSP_FRAMEWORK = ( typeof Object.create !== 'undefined' ) ? Object.create( null ) : {};

	$.VSP_FRAMEWORK  = {};
	window.VSP_ADDON = {
		elem: $( '#vsp_addons_listing_container' ),

		error_popup: ( title ) => {
			if( false === title ) {
				title = 'Unknown Error';
			}

			if( false === window.wponion._.isString( title ) ) {
				title = 'Unknown Error';
			}

			window.swal.fire( title, '', 'error' );
		},

		category: () => {
			window.VSP_ADDON.elem.on( 'click', 'ul.addon-category li', ( e ) => {
				let $click = $( e.currentTarget );
				let $cat   = $click.attr( 'data-category' );
				window.VSP_ADDON.elem.find( '.the-list.addon_listing > .addon' ).hide();
				window.VSP_ADDON.elem.find( '.the-list.addon_listing > .addon.' + $cat ).show();
				window.VSP_ADDON.elem.find( 'ul.addon-category li.current' ).removeClass( 'current' );
				$click.addClass( 'current' );
			} );
		},

		actions: () => {
		},

		init: () => {
			window.VSP_ADDON.category();
			window.VSP_ADDON.elem.find( '.the-list.addon_listing > .addon.active' ).find( 'button.activate' ).hide();
			window.VSP_ADDON.elem.find( '.the-list.addon_listing > .addon.inactive' )
				  .find( 'button.deactivate' )
				  .hide();
			jQuery( 'li.addon-category.all' ).click();
			window.VSP_ADDON.actions();
		},
	};

	$.VSP_FRAMEWORK.log_view_file_change = () => {
		$( '#vsp-log-view-wrap a.button' ).attr( 'disabled', 'disabled' );
		$( '.wponion-form' ).removeAttr( 'action' ).submit();
	};

	window.vsp_js_function = ( $data ) => window.vsp_helper.to_js_func( $data );

	$( () => {
		if( $( '#vsp-log-view-wrap' ).length > 0 ) {
			$( '#vsp-log-view-wrap' ).on( 'change', '.log-header select', $.VSP_FRAMEWORK.log_view_file_change );
		}

		if( $( '#vsp_addons_listing_container' ).length > 0 ) {
			window.VSP_ADDON.init();
		}
	} );
} )( jQuery, window, document, window.wp );

