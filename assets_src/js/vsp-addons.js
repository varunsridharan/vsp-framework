/* global vsp_addons_settings:true */
( function( $, window, document ) {
	'use strict';

	$( 'div.wponion-framework.wponion-framework-bootstrap' ).removeClass( 'wponion-framework-bootstrap' );
	$( 'div.wponion-form-actions' ).remove();

	$.VSP_ADDONS = $.VSP_ADDONS || {};
	new Vue( {
		el: '#vspAddonListing',
		template: '#VSPAddonsListingTemplate',
		methods: {
			getPluginStatusLabel: function( label ) {
				if( this.status[ label ] !== undefined ) {
					return this.status[ label ];
				}
			},
			change_category: function( cat ) {
				this.current_category = cat;
			},
			is_show_cateogry: function( addon ) {
				if( this.current_category === 'all' ) {
					return true;
				} else if( this.current_category === 'inactive' && addon.is_active === false ) {
					return true;
				} else if( this.current_category === 'active' && addon.is_active === true ) {
					return true;
				} else if( addon.category !== undefined ) {
					if( addon.category[ this.current_category ] !== undefined ) {
						return true;
					}
				}

				return false;
			},
			pluginViewUrl: function( addon, file ) {
				var $data = this.text.plugin_view_url;
				$data     = $data.replace( '{{slug}}', file );
				return $data.replace( '{{addon.addon_path_md5}}', addon.addon_path_md5 );
			},
			addonHandleButton: function( addon, file, $type ) {
				var $this = this;
				$.VSP_ADDONS.blockUI( addon.addon_path_md5 );
				new window.wponion.ajaxer( {
					method: 'POST',
					data: {
						hook_slug: vsp_addons_settings.hook_slug,
						addon_slug: file,
						addon_action: $type,
						addon_pathid: addon.addon_path_md5,
						action: 'vsp_addon_action',
					},
					always: function() {
						$.VSP_ADDONS.unblock( addon.addon_path_md5 );
					},
					success: function() {
						if( $type === 'activate' ) {
							$this.pdata[ file ].is_active = true;
						} else {
							$this.pdata[ file ].is_active = false;
						}
					}
				} );
			}
		},
		computed: {
			cats: function() {
				if( this.categoires === undefined ) {
					var $arr  = this.pdata;
					var $cats = this.default_cats;
					var $e    = '';
					for( $e in $arr ) {
						if( typeof $arr[ $e ].category !== undefined ) {
							var $c = '';
							for( $c in $arr[ $e ].category ) {
								if( $cats[ $c ] === undefined ) {
									$cats[ $c ] = $arr[ $e ].category[ $c ];
								}
							}
						}
					}
					this.categoires       = $cats;
					this.category_slugs   = Object.keys( $cats );
					this.current_category = this.category_slugs[ 0 ];
					return $cats;
				}
				return this.categoires;
			}
		},
		data: {
			pdata: vsp_addons_settings.plugin_data,
			status: vsp_addons_settings.plugin_status,
			default_cats: vsp_addons_settings.default_cats,
			categories: null,
			category_slugs: null,
			current_category: null,
			text: vsp_addons_settings.texts,
		},
	} );
	$.VSP_ADDONS.blockUI = function( id ) {
		$( 'div#' + id ).toggleClass( 'vsp-requested' ).block( {
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		} );
	};
	$.VSP_ADDONS.unblock = function( id ) {
		$( 'div#' + id ).toggleClass( 'vsp-requested' ).unblock();
	};

} )( jQuery, window, document );