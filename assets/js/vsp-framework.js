;
(function ($, window, document, undefined) {
    'use strict';

    $.VSPFRAMEWORK = $.VSPFRAMEWORK || {};

    $.VSPFRAMEWORK.DEPENDENCY = function (el, param) {
        var base = this;

        base.$el = $(el);

        base.el = el;

        base.init = function () {
            base.ruleset = $.deps.createRuleset();
            var cfg = {
                show: function (el) {
                    el.parent().parent().show();
                    el.removeClass('hidden');
                },
                hide: function (el) {
                    el.parent().parent().hide();
                    el.addClass('hidden');
                },
                log: false,
                checkTargets: false
            };

            if (param !== undefined) {
                base.depSub();
            } else {
                base.depRoot();
            }
            $.deps.enable(base.$el, base.ruleset, cfg);
        };

        base.depRoot = function () {
            base.$el.each(function () {
                $(this).find('[data-controller]').each(function () {
                    var $this = $(this),
                        _controller = $this.data('controller').split('|'),
                        _condition = $this.data('condition').split('|'),
                        _value = $this.data('value').toString().split('|'),
                        _rules = base.ruleset;
                    $.each(_controller, function (index, element) {
                        var value = _value[index] || '',
                            condition = _condition[index] || _condition[0];
                        _rules = _rules.createRule('[data-depend-id="' + element + '"]', condition, value);
                        _rules.include($this);
                    });
                });
            });
        };

        base.depSub = function () {
            base.$el.each(function () {
                $(this).find('[data-sub-controller]').each(function () {
                    var $this = $(this),
                        _controller = $this.data('sub-controller').split('|'),
                        _condition = $this.data('sub-condition').split('|'),
                        _value = $this.data('sub-value').toString().split('|'),
                        _rules = base.ruleset;
                    $.each(_controller, function (index, element) {
                        var value = _value[index] || '',
                            condition = _condition[index] || _condition[0];
                        _rules = _rules.createRule('[data-sub-depend-id="' + element + '"]', condition, value);
                        _rules.include($this);
                    });
                });
            });
        };

        base.init();
    };

    $.fn.VSPFRAMEWORK_DEPENDENCY = function (param) {
        return this.each(function () {
            new $.VSPFRAMEWORK.DEPENDENCY(this, param);
        });
    };

    $.VSPFRAMEWORK.URL_PARAM = function (name, url) {
        if (!url) url = window.location.href;
        name = name.replace(/[\[\]]/g, "\\$&");
        var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
            results = regex.exec(url);
        if (!results) return null;
        if (!results[2]) return '';
        return decodeURIComponent(results[2].replace(/\+/g, " "));
    }

    $.VSPFRAMEWORK.UPDATE_SETTINGS_PAGE_URL = function (clicked_tab, el) {
        var $location = window.location;
        clicked_tab = clicked_tab.replace("#", '');
        var $data = {
            page: $.VSPFRAMEWORK.URL_PARAM('page'),
            tab: clicked_tab
        }
        var $data = $.param($data);
        var url = window.location.protocol + '//' + window.location.hostname + window.location.pathname + '?' + $data;
        $('input[name=_wp_http_referer').val(url);
    }

    $.VSPFRAMEWORK.show_settings_tab = function (id) {
        $("div.vsp_settings_content").hide();
        id = id.replace('#', '#settings_');
        $(id).show();
        id = id.replace('#settings_', '#');
        $.VSPFRAMEWORK.UPDATE_SETTINGS_PAGE_URL(id);
    }

    $.VSPFRAMEWORK.init_settings_tab = function () {
        var id = $.VSPFRAMEWORK.URL_PARAM("tab"); //window.location.hash;
        if (id !== null) {
            id = '#' + id;
        }

        if (id == '' || id == null) {
            $('.vsp_settings_subtab a:first').addClass('current');
            id = $('.vsp_settings_subtab a:first').attr('href');
        } else {
            $('.vsp_settings_subtab a').removeClass('current');
            $('.vsp_settings_subtab a[href="' + id + '" ]').addClass('current');
        }

        $.VSPFRAMEWORK.show_settings_tab(id);
    }

    $.VSPFRAMEWORK.get_element_args = function (elem, $options) {
        var $final_data = {};

        $.each($options, function (key, defaults) {
            var $data = elem.data(key);
            if ($data === undefined) {
                $final_data[key] = defaults;
            } else {
                $final_data[key] = $data;
            }
        });

        return $final_data;
    }

    $.fn.VSPFRAMEWORK_SETTINGS_TAB = function (el) {
        el.preventDefault();
        var clicked_tab = this.attr('href');
        $(".vsp_settings_subtab a").removeClass("current");
        this.addClass("current");

        $.VSPFRAMEWORK.show_settings_tab(clicked_tab);
    }

    $.fn.VSPFRAMEWORK_SELECT2 = function () {
        return this.each(function () {
            $(this).select2();
        });
    }

    $.fn.VSPFRAMEWORK_ICHECK = function () {
        this.each(function () {
            var $this = $(this);
            var $options = {
                increaseArea: '',
                cursor: false,
                inheritClass: false,
                inheritID: false,
                aria: false,
                checkboxClass: 'icheckbox_minimal-green',
                radioClass: 'iradio_minimal-green',
            };

            var $final_data = $.VSPFRAMEWORK.get_element_args($this, $options);
            var $theme = $this.data('theme');
            if ($theme !== undefined) {
                $final_data['checkboxClass'] = 'icheckbox_' + $theme;
                $final_data['radioClass'] = 'iradio_' + $theme;
            }
            $this.iCheck($final_data);
        })
    }

    $.fn.VSPFRAMEWORK_SWITCHERY = function ($element) {
        return this.each(function () {
            var $this = $(this);
            var $options = {
                color: '#64bd63',
                secondaryColor: '#aaa',
                jackColor: '#fff',
                jackSecondaryColor: null,
                className: 'switchery',
                disabled: false,
                disabledOpacity: 0.5,
                speed: '0.1s',
                size: 'small'
            };

            var $final_data = $.VSPFRAMEWORK.get_element_args($this, $options);

            new Switchery($this.get(0), $final_data);

        });
    };

    $.fn.VSPFRAMEWORK_TOOLTIP = function () {
        return this.each(function () {

            var $is_popover = false;
            var $ArrayKey = 'tooltip';
            if ($(this).attr("data-toggle-popover") == 'true') {
                $is_popover = true;
                $ArrayKey = 'popover';
            }

            if ($is_popover === false) {
                var $options = {
                    'animation': true,
                    'container': 'body',
                    'delay': 0,
                    'html': true,
                    'placement': 'bottom',
                    'title': '',
                };
            } else {
                var $options = {
                    'animation': true,
                    'container': 'body',
                    'delay': 0,
                    'html': true,
                    'placement': 'bottom',
                    'title': '',
                    'content': '',
                };
            }
            var $options = $.VSPFRAMEWORK.get_element_args($(this), $options);
            var $ID = $(this).attr("id");
            
            if(vspFrameWork_Settings[$ArrayKey][$ID] !== undefined){
                if ($is_popover) {
                    if ($options['title'] === '') {
                        $options['title'] = vspFrameWork_Settings[$ArrayKey][$ID]['title'];
                        $options['content'] = vspFrameWork_Settings[$ArrayKey][$ID]['content'];
                    }
                } else {
                    if ($options['title'] === '') {
                        var $ID = $(this).attr("id");
                        $options['title'] = vspFrameWork_Settings[$ArrayKey][$ID];
                    }
                }

                if ($(this).attr("data-toggle-popover") == 'true') {
                    $(this).vsppopover($options);
                } else {
                    $(this).vsptooltip($options);
                }
            }


        });
    };


    $(document).ready(function () {

        $('.vsp_settings_content , .vsp_inputs').VSPFRAMEWORK_DEPENDENCY();

        if (jQuery('.vsp_settings_subtab').size() > 0) {
            $.VSPFRAMEWORK.init_settings_tab();

            jQuery("body").on("click", '.vsp_settings_subtab a', function (e) {
                jQuery(this).VSPFRAMEWORK_SETTINGS_TAB(e);
            });
        }

        if ($(".vsp-select2").size() > 0) {
            $(".vsp-select2").VSPFRAMEWORK_SELECT2();
        }

        if ($(".vsp-icheck").size() > 0) {
            $(".vsp-icheck").VSPFRAMEWORK_ICHECK();
        }

        if ($(".vsp-switch").size() > 0) {
            $(".vsp-switch").VSPFRAMEWORK_SWITCHERY();
        }

        jQuery("body").on("click", '.vsp-close-popover', function (e) {
            e.preventDefault();
            var $target = $(this).attr('data-target');
            $($target).vsppopover('hide');
        });


        $('[data-toggle-tooltip="true"] , [data-toggle-popover="true"]').VSPFRAMEWORK_TOOLTIP();

        if ($("#vsp-sys-status-report-text-btn").size() > 0) {
            $("#vsp-sys-status-report-text-btn").click(function (e) {
                e.preventDefault();
                var $textarea = $(this).parent().parent().find("textarea");
                $textarea.slideDown("slow");
                $(this).remove();
            });
        }
    });

})(jQuery, window, document);
