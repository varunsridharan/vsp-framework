;
(function ($, window, document, undefined) {
    'use strict';

    $.VSP_ADDONS = $.VSP_ADDONS || {};

    $.VSP_ADDONS.pending_ajax_requets = [];

    $.VSP_ADDONS.addonsHTML = '';
    
    $.VSP_ADDONS.is_ajax_ongoing = false;

    $.VSP_ADDONS.blockUI = function (id) {
        $('div#' + id).toggleClass("vsp-requested");
        $('div#' + id).block({
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0.6
            }
        });
    }

    $.VSP_ADDONS.unblock = function (id) {
        $('div#' + id).toggleClass("vsp-requested");
        $('div#' + id).unblock();
    }

    $.VSP_ADDONS.kick_start_addon_ajax = function () {
        $.VSP_ADDONS.is_ajax_ongoing = true;
        if ($.VSP_ADDONS.pending_ajax_requets[0] !== undefined) {
            var $elem = $.VSP_ADDONS.pending_ajax_requets[0];
            var $PARENTDIV = $elem.attr("data-outline");
            var $type = 'activate';
            $PARENTDIV = $("div#" + $PARENTDIV);
            var $path_id = $PARENTDIV.data('pathid');
            if ($elem.hasClass("vsp-deactive-addon")) {
                $type = 'deactivate';
            }

            $.ajax({
                url: ajaxurl,
                method: "POST",
                data: {
                    hook_slug: vsp_addons_settings.hook_slug,
                    addon_slug: $elem.attr("data-filename"),
                    addon_action: $type,
                    addon_pathid: $path_id,
                    action: "vsp-addon-action",
                }
            }).done(function (response) {
                var $AjaxDiv = $PARENTDIV.find(".vsp_addon_ajax_response");
                $AjaxDiv.removeClass("vsp_ajax_error");
                $AjaxDiv.removeClass("vsp_ajax_success");
                $AjaxDiv.hide();
                if (response.success === true) {
                    $PARENTDIV.toggleClass("addon-inactive");
                    $PARENTDIV.toggleClass("addon-active");
                    $.VSP_ADDONS.update_action_buttons();
                    $AjaxDiv.addClass("vsp_ajax_success");
                } else {
                    $AjaxDiv.addClass("vsp_ajax_error");
                }

                $AjaxDiv.html(response.data.msg);
                $AjaxDiv.fadeIn('fast', function () {
                    setTimeout(function () {
                        $AjaxDiv.fadeOut("slow")
                    }, 4000);
                });
            }).always(function () {
                $.VSP_ADDONS.unblock($PARENTDIV.attr('id'));
                $.VSP_ADDONS.pending_ajax_requets.shift();
                $.VSP_ADDONS.kick_start_addon_ajax();
            });
        } else {
            $.VSP_ADDONS.is_ajax_ongoing = false;
        }
    }

    $.VSP_ADDONS.handle_action_clicks = function () {
        var $ID = $(this).attr("data-outline");
        if (!$('div#' + $ID).hasClass("vsp-requested")) {
            $.VSP_ADDONS.pending_ajax_requets.push($(this));

            if ($.VSP_ADDONS.is_ajax_ongoing === false) {
                $.VSP_ADDONS.kick_start_addon_ajax();
            }
            $.VSP_ADDONS.blockUI($ID);
        }
    }

    $.VSP_ADDONS.update_action_buttons = function () {
        $(".vsp-deactive-addon, .vsp-active-addon").hide();
        $(".vsp-deactive-addon, .vsp-active-addon").attr('disabled', 'disabled');
        $("div.addon-inactive .vsp-active-addon").show().removeAttr("disabled");
        $("div.addon-active .vsp-deactive-addon").show().removeAttr("disabled");
        $.VSP_ADDONS.addonsHTML = $(".vsp_addon_listing").clone();
    }

    $(document).ready(function () {
        $.VSP_ADDONS.update_action_buttons();

        $('body').on("click", "div.vsp_addon_listing button.vsp-active-addon, div.vsp_addon_listing button.vsp-deactive-addon", $.VSP_ADDONS.handle_action_clicks);

        if ($("ul.vsp-addons-category-listing").length > 0) {
            $(".vsp_settings_content").remove();
            $("p.submit").remove();
            $("ul.vsp-addons-category-listing li:first").addClass('current');
            $("ul.vsp-addons-category-listing a").click(function () {
                var $elem = $(this);
                var $cat = $elem.parent().attr("data-category");
                $("ul.vsp-addons-category-listing li").removeClass("current");
                $elem.parent().addClass('current');

                if ($cat == 'all') {
                    $(".vsp-single-addon").show();
                } else {
                    $(".vsp-single-addon").hide();
                    $('.addon-' + $cat).fadeIn();
                }

            });
            
            $('body').on("keyup",".wp-filter-search",function(){
                var $html = $.VSP_ADDONS.addonsHTML.clone();
            });
        }
    });

})(jQuery, window, document);