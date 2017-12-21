;
(function ($, window, document, undefined) {
    'use strict';

    $.VSP_IMAGE_SELECT = $.VSP_IMAGE_SELECT || {};

    $.VSP_IMAGE_SELECT.browsers = [];

    $.VSP_IMAGE_SELECT.uuid = function (C) {
        return ([1e7] + -1e3 + -4e3 + -8e3 + -1e11).replace(/[018]/g, function (c) {
            return (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16);
        });
    }

    $.fn.VSP_HANDLE_IMAGE_SELECT = function () {
        var $this = $(this);
        var $instance_id = $this.attr("data-instanceid");
        var $title = $this.attr("data-popup-title");
        var $buttontext = $this.attr("data-button-label");
        var $multiple = $this.attr("data-is_multiple");
        var $parent = $this.parent().parent();
        var $data_type = $this.attr('data-output-type');
        if ($.VSP_IMAGE_SELECT.browsers[$instance_id] != undefined) {
            $.VSP_IMAGE_SELECT.browsers[$instance_id]['popup'].open();
            return;
        }

        $instance_id = $.VSP_IMAGE_SELECT.uuid(6);
        $.VSP_IMAGE_SELECT.browsers[$instance_id] = {};
        $.VSP_IMAGE_SELECT.browsers[$instance_id]['e'] = $this;
        $.VSP_IMAGE_SELECT.browsers[$instance_id]['popup'] = wp.media.frames.downloadable_file = wp.media({
            title: $title,
            button: {
                text: $buttontext
            },
            multiple: $multiple
        });

        $.VSP_IMAGE_SELECT.browsers[$instance_id]['popup'].on('select', function () {
            var attachment = $.VSP_IMAGE_SELECT.browsers[$instance_id]['popup'].state().get('selection').first().toJSON();
            var value = JSON.stringify(attachment);
            if ($data_type == 'id') {
                value = attachment.id;
            }
            $parent.find('input[type=hidden]').val(value);
            $parent.find('img').attr('src', attachment.sizes.thumbnail.url);
            $parent.find('.remove_image_button').show();
        });

        $.VSP_IMAGE_SELECT.browsers[$instance_id]['popup'].open();
    }
    
    $(document).ready(function () {
        $('div.vsp_image_select_field .upload_image_button').click(function (event) {
            event.preventDefault();
            $(this).VSP_HANDLE_IMAGE_SELECT();
        });

        jQuery('div.vsp_image_select_field .remove_image_button').click(function (e) {
            e.preventDefault();
            var $img = $(this).parent().parent().find('img');
            $img.attr("src",$img.attr("data-placeholder-src"));
            
            $(this).parent().parent().find('input[type=hidden]').val('');
            $(this).hide();
            return false;
        });

        $("div.vsp_image_select_field").each(function () {
            var $value = $(this).find('input[type=hidden]').val();

            if ($value === '' || $value === undefined) {
                $(this).find('.remove_image_button').hide();
            }
        });
    });

})(jQuery, window, document);