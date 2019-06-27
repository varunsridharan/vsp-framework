( ( $, w ) => {

	$.VSPF                 = ( typeof Object.create !== 'undefined' ) ? Object.create( null ) : {};
	$.VSPF.log_file_change = () => {
		$( '#vsp-log-view-wrap a.button' ).attr( 'disabled', 'disabled' );
		$( '.wponion-form' ).removeAttr( 'action' ).submit();
	};
	w.VSPFA                = {
		el: $( '#vsp_addons_listing_container' ),
		category: () => {
			w.VSPFA.el.on( 'click', 'ul.addon-category li', ( e ) => {
				let $click = $( e.currentTarget );
				let $cat   = $click.attr( 'data-category' );
				w.VSPFA.el.find( '.the-list.addon_listing > .addon' ).hide();
				w.VSPFA.el.find( '.the-list.addon_listing > .addon.' + $cat ).show();
				w.VSPFA.el.find( 'ul.addon-category li.current' ).removeClass( 'current' );
				$click.addClass( 'current' );
			} );
		},
		init: () => {
			w.VSPFA.category();
			w.VSPFA.el.find( '.the-list.addon_listing > .addon.active' ).find( 'button.activate' ).hide();
			w.VSPFA.el.find( '.the-list.addon_listing > .addon.inactive' ).find( 'button.deactivate' ).hide();
			jQuery( 'li.addon-category.all' ).click();
		},
	};

	$( () => {
		let $logwrap = $( '#vsp-log-view-wrap' );
		if( $logwrap.length > 0 ) {
			$logwrap.on( 'change', '.log-header select', $.VSPF.log_file_change );
		}

		if( $( '#vsp_addons_listing_container' ).length > 0 ) {
			w.VSPFA.init();
		}
	} );
} )( jQuery, window );

