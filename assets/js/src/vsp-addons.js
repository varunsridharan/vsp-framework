var $vsp_addons_list = new Vue({
    el: "#vspAddonListing",
    template: '#VSPAddonsListingTemplate',
    methods: {
        getPluginStatusLabel: function (label) {
            if ( this.status[label] !== undefined ) {
                return this.status[label];
            }
        },
        change_category: function (cat) {
            this.current_category = cat;
        },
        is_show_cateogry: function (addon) {
            if ( this.current_category === 'all' ) {
                return true;
            } else if ( this.current_category === 'inactive' && addon.is_active === false ) {
                return true;
            } else if ( this.current_category === 'active' && addon.is_active === true ) {
                return true;
            } else if ( addon.category !== undefined ) {
                return _.hasIn(addon.category, this.current_category);
            }

            return false;
        },
        pluginViewUrl: function (addon, file) {
            var $data = _.replace(this.text.plugin_view_url, '{{slug}}', file);
            $data = _.replace($data, '{{addon.addon_path_md5}}', addon.addon_path_md5);
            return $data;
        },
        addonHandleButton: function (addon, file, $type) {
            var $this = this;
            $.VSP_ADDONS.blockUI(addon.addon_path_md5);
            var $parentDIV = jQuery('div#' + addon.addon_path_md5);
            var $type = $type;
            wp.ajax.send({
                data: {
                    hook_slug: vsp_addons_settings.hook_slug,
                    addon_slug: file,
                    addon_action: $type,
                    addon_pathid: addon.addon_path_md5,
                    action: "vsp-addon-action",
                },
                error: function (res) {
                    $.VSP_HELPER.ajax_callback(res);
                    $.VSP_ADDONS.unblock(addon.addon_path_md5);
                },
                success: function (response) {
                    $.VSP_HELPER.ajax_callback(response);
                    $.VSP_ADDONS.unblock(addon.addon_path_md5);

                    if ( $type === 'activate' ) {
                        $this.pdata[file].is_active = true;
                    } else {
                        $this.pdata[file].is_active = false;
                    }
                }
            })
        }
    },
    computed: {
        cats: function () {
            if ( this.categoires === undefined ) {
                var $arr = this.pdata;
                var $cats = this.default_cats;
                _.forEach($arr, function (value, key) {
                    if ( typeof value.category !== undefined ) {
                        _.merge($cats, value.category);
                    }
                });

                this.categoires = $cats;
                this.category_slugs = _.keys($cats);
                this.current_category = _.head(this.category_slugs);
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
});

;
( function ($, window, document) {
    'use strict';

    $.VSP_ADDONS = $.VSP_ADDONS || {};

    $.VSP_ADDONS.blockUI = function (id) {
        $('div#' + id).toggleClass("vsp-requested");
        $('div#' + id).block({
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0.6
            }
        });
    };

    $.VSP_ADDONS.unblock = function (id) {
        $('div#' + id).toggleClass("vsp-requested");
        $('div#' + id).unblock();
    };


} )(jQuery, window, document);