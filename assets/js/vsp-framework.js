(function ($, window, document) {

    $.VSPFRAMEWORK = $.VSPFRAMEWORK || {};

    $.VSPFRAMEWORK.is_framework_exists = function (type) {
        var $is_error = true;
        var $message = 'Lib Dose Not Exists';

        if (type === 'select2') {
            try {
                jQuery().select2();
                $is_error = false;
            } catch (err) {}
        } else if (type === 'icheck') {
            try {
                jQuery().iCheck();
                $is_error = false;
            } catch (err) {}
        } else if (type === 'Switchery') {
            try {
                new Switchery('');
                $is_error = false;
            } catch (err) {}
        } else if (type === 'vsptooltip') {
            try {
                jQuery().vsptooltip();
                $is_error = false;
            } catch (err) {
                console.log(err);
            }
        } else if (type === 'vsppopover') {
            try {
                jQuery().vsppopover();
                $is_error = false;
            } catch (err) {
                console.log(err);
            }
        }

        if ($is_error === true) {
            console.log(type + " " + $message);
            return false;
        }

        return true;
    }

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
            tab: $.VSPFRAMEWORK.URL_PARAM('tab'),
            ctab: clicked_tab
        }
        var $data = $.param($data);
        var url = window.location.protocol + '//' + window.location.hostname + window.location.pathname + '?' + $data;
        $('input[name=_wp_http_referer').val(url);
        return url;
    }

    $.VSPFRAMEWORK.show_settings_tab = function (id) {
        $("div.vsp_settings_content").hide();
        id = id.replace('#', '#settings_');
        $(id).show();
        id = id.replace('#settings_', '#');
        $('body').trigger("vsp_settings_tab_updated", [id]);
        return $.VSPFRAMEWORK.UPDATE_SETTINGS_PAGE_URL(id);
    }

    $.VSPFRAMEWORK.init_settings_tab = function () {
        var id = $.VSPFRAMEWORK.URL_PARAM("ctab"); //window.location.hash;
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

    $.VSPFRAMEWORK.render_faqs = function ($tab) {
        if (typeof vspFramework_Settings_Faqs == undefined) {
            return;
        }
        var $replace = '#' + vspFramework_Settings_Faqs['prefix_sec_id'] + '_';
        var $id = $tab.replace($replace, '');
        var $faqs = vspFramework_Settings_Faqs['faqs'][$id];

        var $html = '<ul class="vsp-faq-list">';
        $.each($faqs, function ($a, $c) {
            $html += '<li><a data-tab-id="' + $id + '" class="vsp-faq-single" data-trigger="click" data-toggle-popover="true" data-stitle="' + $c['question'] + '"  href="javascript:void(0);" id="' + $a + '">' + $c['question'] + '</a></li>';
        });

        $html += '</ul>';
        $("#vsp-settings-faq .inside").html($html).find('a').VSPFRAMEWORK_FAQ_TOOLTIP();
    }

    $.fn.VSPFRAMEWORK_SETTINGS_TAB = function (el) {
        el.preventDefault();
        var clicked_tab = this.attr('href');
        $(".vsp_settings_subtab a").removeClass("current");
        this.addClass("current");

        var url = $.VSPFRAMEWORK.show_settings_tab(clicked_tab);
        //location.href = url;
    }

    $.fn.VSPFRAMEWORK_SELECT2 = function () {
        if (!$.VSPFRAMEWORK.is_framework_exists('select2')) {
            return;
        }
        return this.each(function () {
            try {
                $(this).select2();
            } catch (err) {

            }
        });
    }

    $.fn.VSPFRAMEWORK_ICHECK = function () {
        if (!$.VSPFRAMEWORK.is_framework_exists('icheck')) {
            return;
        }
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
        if (!$.VSPFRAMEWORK.is_framework_exists('Switchery')) {
            return;
        }
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

    $.fn.VSPFRAMEWORK_FAQ_TOOLTIP = function () {
        return this.each(function () {
            var $this = $(this);
            var $tab_id = $this.data('tab-id');

            var $options = {
                'animation': true,
                'container': 'body',
                'delay': 0,
                'html': true,
                'placement': 'left',
                'title': $this.attr("data-stitle") + '<button data-target="a#'+$this.attr('id')+'" type="button" class="close vsp-close-popover" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>',
                'content': function ($e) {
                    var $faqs = vspFramework_Settings_Faqs['faqs'][$tab_id];
                    var $faq = $faqs[$this.attr('id')];
                    $faq = $($.parseHTML('<div>' + $faq['answer'] + '</div>'));
                    $faq.find('a').attr("target", '_blank');

                    return $faq.html();
                },
            };
            var $options = $.VSPFRAMEWORK.get_element_args($(this), $options);
            $(this).vsppopover($options);
        })

    }

    $.fn.VSPFRAMEWORK_TOOLTIP = function () {
        return this.each(function () {

            var $is_popover = false;
            var $ArrayKey = 'tooltip';

            if ($(this).attr("data-toggle-popover") == 'true') {
                $is_popover = true;
                $ArrayKey = 'popover';
            }

            if ($is_popover === false) {
                if (!$.VSPFRAMEWORK.is_framework_exists('vsptooltip')) {
                    return;
                }
                var $options = {
                    'animation': true,
                    'container': 'body',
                    'delay': 0,
                    'html': true,
                    'placement': 'bottom',
                    'title': '',
                };
            } else {
                if (!$.VSPFRAMEWORK.is_framework_exists('vsppopover')) {
                    return;
                }
                var $options = {
                    'animation': true,
                    'container': 'body',
                    'delay': 0,
                    'html': true,
                    'placement': 'bottom',
                    'title': '',
                    'content': '',
                    'trigger': 'click',
                };
            }
            var $options = $.VSPFRAMEWORK.get_element_args($(this), $options);
            var $ID = $(this).attr("id");

            if (vspFrameWork_Settings[$ArrayKey][$ID] !== undefined) {
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


            }

            if ($is_popover === true) {
                $(this).vsppopover($options);
            } else {
                $(this).vsptooltip($options);
            }

        });
    };

    $(document).ready(function () {

        if ($("#vsp-settings-faq").length > 0) {
            $("body").on("vsp_settings_tab_updated", function ($event, $settings) {
                $.VSPFRAMEWORK.render_faqs($settings);
            });
            $("#vsp-settings-faq > button.handlediv , #vsp-settings-faq > h2").click(function () {
                $(this).parent().toggleClass("closed");
            })
        }

        $('.vsp_settings_content , .vsp_inputs').VSPFRAMEWORK_DEPENDENCY();

        $("body").on("click", '.vsp-close-popover', function (e) {
            e.preventDefault();
            var $target = $(this).attr('data-target');
            $($target).vsppopover('hide');
        });

        $('[data-toggle-tooltip="true"] , [data-toggle-popover="true"]').VSPFRAMEWORK_TOOLTIP();

        if ($('.vsp_settings_subtab').length > 0) {
            $.VSPFRAMEWORK.init_settings_tab();
            jQuery("body").on("click", '.vsp_settings_subtab a', function (e) {
                jQuery(this).VSPFRAMEWORK_SETTINGS_TAB(e);
            });
        }

        if ($(".vsp-select2").length > 0) {
            $(".vsp-select2").VSPFRAMEWORK_SELECT2();
        }

        if ($(".vsp-icheck").length > 0) {
            $(".vsp-icheck").VSPFRAMEWORK_ICHECK();
        }

        if ($(".vsp-switch").length > 0) {
            $(".vsp-switch").VSPFRAMEWORK_SWITCHERY();
        }

        if ($("#vsp-sys-status-report-text-btn").length > 0) {
            $("#vsp-sys-status-report-text-btn").click(function (e) {
                e.preventDefault();
                var $textarea = $(this).parent().parent().find("textarea");
                $textarea.slideDown("slow");
                $(this).remove();
            });
        }

        if ($('.vsp-adds-slider').length > 0) {
            $('.vsp-adds-slider').owlCarousel({
                items: 1,
                nav: true,
                autoplay: true,
                autoHeight: true,
                navText: ['< prev', 'next >'],
                dots: false,

            });
        }
    });

})(jQuery, window, document);