( function ($, window, document) {

    $.VSPFRAMEWORK = $.VSPFRAMEWORK || {};

    $.VSP_HELPER = $.VSP_HELPER || {};

    $.VSP_HELPER.string_function_callback = function (callback) {
        try {
            window[callback]();
        } catch ( err ) {
            console.log(err);
        }
    };

    $.VSP_HELPER.array_function_callback = function (callback) {
        $.each(callback, function (key, value) {
            ecollab_string_function_callback(value);
        })
    };

    $.VSP_HELPER.function_callback = function (key, value) {
        var $return = false;
        try {
            var CB = new Function(key, value);
            CB();
        } catch ( err ) {
            console.log(err);
        }

        return $return;
    };

    $.VSP_HELPER.ajax_callback = function (res) {
        if ( res.callback !== undefined ) {
            if ( typeof res.callback === 'string' ) {
                var callback = res.callback;
                $.VSP_HELPER.string_function_callback(callback);
            } else if ( typeof res.callback === 'object' || typeof res.callback === 'array' ) {
                $.each(res.callback, function (key, value) {
                    if ( key === parseInt(key) ) {
                        if ( typeof value === 'string' ) {
                            $.VSP_HELPER.string_function_callback(value);
                        } else if ( typeof value === 'object' || typeof value === 'array' ) {
                            $.VSP_HELPER.array_function_callback(value);
                        }
                    } else {
                        $.VSP_HELPER.function_callback(key, value);
                    }
                })

            }
        }
    };


    $.VSPFRAMEWORK.init_frameworks = function () {
        if ( $.VSPFRAMEWORK.is_framework_exists('bspopover') ) {
            function vsp_extended_popover(option) {
                return this.each(function () {
                    var $element = $(this);
                    $(this).on('hidden.bs.popover', function () {
                        $element.removeClass("popover-open");
                    });

                    $(this).on('shown.bs.popover', function () {
                        $element.addClass("popover-open");
                    });

                    return $element.popover(option);
                })
            }

            var old = $.fn.vsppopover

            $.fn.vsppopover = vsp_extended_popover
            $.fn.vsppopover.noConflict = function () {
                $.fn.vsppopover = old
                return this
            }
        }

        if ( $.VSPFRAMEWORK.is_framework_exists('bstooltip') ) {
            function vsp_extended_tooltip(option) {
                return this.each(function () {
                    var $element = $(this);
                    $(this).on('hidden.bs.tooltip', function () {
                        $element.removeClass("tooltip-open");
                    });

                    $(this).on('shown.bs.tooltip', function () {
                        $element.addClass("tooltip-open");
                    });

                    return $element.tooltip(option);
                })
            }

            var old = $.fn.vsptooltip;

            $.fn.vsptooltip = vsp_extended_tooltip
            $.fn.vsptooltip.noConflict = function () {
                $.fn.vsptooltip = old
                return this
            }
        }
    };

    $.VSPFRAMEWORK.is_framework_exists = function (type) {
        var $is_error = true;
        var $message = 'Lib Dose Not Exists';

        if ( type === 'select2' ) {
            try {
                jQuery().select2();
                $is_error = false;
            } catch ( err ) {
            }
        } else if ( type === 'Switchery' ) {
            try {
                new Switchery('');
                $is_error = false;
            } catch ( err ) {
            }
        } else if ( type === 'vsptooltip' ) {
            try {
                jQuery().vsptooltip();
                $is_error = false;
            } catch ( err ) {
            }
        } else if ( type === 'vsppopover' ) {
            try {
                jQuery().vsppopover();
                $is_error = false;
            } catch ( err ) {
            }
        } else if ( type === 'bstooltip' ) {
            try {
                jQuery().tooltip();
                $is_error = false;
            } catch ( err ) {
            }
        } else if ( type === 'bspopover' ) {
            try {
                jQuery().popover();
                $is_error = false;
            } catch ( err ) {
            }
        }

        if ( $is_error === true ) {
            //console.log(type + " " + $message);
            return false;
        }

        return true;
    };

    $.VSPFRAMEWORK.get_element_args = function (elem, $options) {
        var $final_data = {};

        $.each($options, function (key, defaults) {
            var $data = elem.data(key);
            if ( $data === undefined ) {
                $final_data[key] = defaults;
            } else {
                $final_data[key] = $data;
            }
        });

        return $final_data;
    };

    $.VSPFRAMEWORK.render_faq_ul = function ($faqs, $page, $section, $ulClass) {
        var $html = '<ul class="' + $ulClass + '">';
        $.each($faqs, function ($a, $c) {
            $html += '<li><a data-page="' + $page + '" data-section="' + $section + '" class="vsp-faq-single" data-trigger="click" data-stitle="' + $c['question'] + '"  href="javascript:void(0);" id="' + $a + '">' + $c['question'] + '</a></li>';
        });

        $html += '</ul>';
        return $html;
    };

    $.VSPFRAMEWORK.render_faqs = function ($parent, $subnav, $el) {
        $(".popover-open , .tooltip-open").click();
        if ( typeof vspFramework_Settings_Faqs == undefined ) {
            return;
        }

        var $vspsf = vspFramework_Settings_Faqs;
        var $page = $parent;
        var $section = ( $subnav === undefined ) ? false : $subnav;
        var $elem = $el;
        var $theme = $('.wpsf-framework').data('theme');
        var $spage = $('.wpsf-framework').data('single-page');
        var $faqs = null;
        var $html = '';

        if ( $theme === 'modern' && $page === undefined ) {
            return;
        }

        if ( $vspsf['faqs'][$page] !== undefined ) {
            if ( $section === false ) {
                if ( $theme === 'simple' && $spage === 'no' ) {
                    return;
                }
                $faqs = $vspsf['faqs'][$page];
            } else {
                $faqs = $vspsf['faqs'][$page][$section];
            }
        }


        if ( $vspsf['faqs']['global'] !== undefined ) {
            $html = $html + $.VSPFRAMEWORK.render_faq_ul($vspsf['faqs']['global'], 'global', '', ' vsp-faq-list vsp-faq-global');
        }

        $html = $html + $.VSPFRAMEWORK.render_faq_ul($faqs, $page, $section, ' vsp-faq-list');

        $("#vsp-settings-faq .inside").html($html).find('a').VSPFRAMEWORK_FAQ_TOOLTIP();

        $("#vsp-settings-faq .inside").slimScroll({
            height: '250px',
            allowPageScroll: false,
            alwaysVisible: true,
        });
    };

    $.VSPFRAMEWORK.init_inline_ajax = function (e) {
        e.preventDefault();

        if ( $(this).hasClass("disabled") || $(this).hasClass("in-process") ) {
            return;
        }

        var $this = $(this),
            url = $this.attr("href"),
            method = $this.attr("data-method"),
            trigger_code = $this.attr("data-triggername");

        if ( method == undefined ) {
            method = 'GET';
        }

        new VSPAjax({
            url: url,
            method: method,
        }, {
            trigger_code: trigger_code,
            element: $this,
            element_lock: true,
        });
    };

    $.fn.VSPFRAMEWORK_SELECT2 = function () {
        if ( !$.VSPFRAMEWORK.is_framework_exists('select2') ) {
            return;
        }
        return this.each(function () {
            try {
                $(this).select2();
            } catch ( err ) {

            }
        });
    };

    $.fn.VSPFRAMEWORK_FAQ_TOOLTIP = function () {
        return this.each(function () {
            var $this = $(this);
            var $page = $this.data('page');
            var $section = $this.data('section');
            var $fqs = null;

            if ( $section === undefined || $section === '' ) {
                $fqs = vspFramework_Settings_Faqs['faqs'][$page];
            } else {
                $fqs = vspFramework_Settings_Faqs['faqs'][$page][$section];
            }

            var $options = {
                'animation': true,
                'container': 'body',
                'delay': 0,
                'template': '<div class="popover vsp-faq-popover" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
                'html': true,
                'placement': 'left',
                'title': $this.attr("data-stitle") + '<button data-target="a#' + $this.attr('id') + '" type="button" class="close vsp-close-popover" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>',
                'content': function ($e) {
                    var $faqs = $fqs;
                    var $faq = $faqs[$this.attr('id')];
                    $faq = $($.parseHTML('<div>' + $faq['answer'] + '</div>'));
                    $faq.find('a').attr("target", '_blank');

                    return $faq.html();
                },
            };
            var $options = $.VSPFRAMEWORK.get_element_args($(this), $options);
            $(this).vsppopover($options);
        });

    };

    $.fn.VSPFRAMEWORK_TOOLTIP = function () {
        return this.each(function () {

            var $is_popover = false;
            var $ArrayKey = 'tooltip';

            if ( $(this).attr("data-toggle-popover") == 'true' ) {
                $is_popover = true;
                $ArrayKey = 'popover';
            }

            if ( $is_popover === false ) {
                if ( !$.VSPFRAMEWORK.is_framework_exists('vsptooltip') ) {
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
                if ( !$.VSPFRAMEWORK.is_framework_exists('vsppopover') ) {
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

            if ( vspFrameWork_Settings[$ArrayKey][$ID] !== undefined ) {
                if ( $is_popover ) {
                    if ( $options['title'] === '' ) {
                        $options['title'] = vspFrameWork_Settings[$ArrayKey][$ID]['title'];
                        $options['content'] = vspFrameWork_Settings[$ArrayKey][$ID]['content'];
                    }
                } else {
                    if ( $options['title'] === '' ) {
                        var $ID = $(this).attr("id");
                        $options['title'] = vspFrameWork_Settings[$ArrayKey][$ID];
                    }
                }


            }

            if ( $is_popover === true ) {
                $(this).vsppopover($options);
            } else {
                $(this).vsptooltip($options);
            }

        });
    };

    $(document).ready(function () {
        $.VSPFRAMEWORK.init_frameworks();

        if ( $("#vsp-settings-faq").length > 0 ) {
            if ( $('.wpsf-framework').data('theme') === 'modern' ) {
                var $active = $('.wpsf-section-active');
                $.VSPFRAMEWORK.render_faqs($active.data('parent-section'), $active.data('section'), $active);
            } else if ( $('.wpsf-framework').data('theme') === 'simple' ) {
                var $active = $('.nav-tab-wrapper .nav-tab-active').data('section');
                var $active = $("#wpsf-tab-" + $active).find('a.current');
                $.VSPFRAMEWORK.render_faqs($active.data('parent-section'), $active.data('section'), $active);
            }

            $("body").on("wpsf_settings_nav_updated", function ($event, $parent, $subnav, $el) {
                $.VSPFRAMEWORK.render_faqs($parent, $subnav, $el);
            });
            $("#vsp-settings-faq > button.handlediv , #vsp-settings-faq > h2").click(function () {
                $(this).parent().toggleClass("closed");
            })
        }

        $("body").on("click", '.vsp-close-popover', function (e) {
            e.preventDefault();
            var $target = $(this).attr('data-target');
            $($target).click();
        });

        $('[data-toggle-tooltip="true"] , [data-toggle-popover="true"]').VSPFRAMEWORK_TOOLTIP();

        if ( $(".vsp-select2").length > 0 ) {
            $(".vsp-select2").VSPFRAMEWORK_SELECT2();
        }

        if ( $("#vsp-sys-status-report-text-btn").length > 0 ) {
            $("#vsp-sys-status-report-text-btn").click(function (e) {
                e.preventDefault();
                var $textarea = $(this).parent().parent().find("textarea");
                $textarea.slideDown("slow");
                $(this).remove();
            });
        }

        if ( $('.vsp-adds-slider').length > 0 ) {
            var $owl = $('.vsp-adds-slider').owlCarousel({
                items: 1,
                nav: true,
                autoplay: true,
                autoHeight: true,
                navText: ['< prev', 'next >'],
                callbacks: true,
                dots: false,
                onInitialize: function () {
                },
                onInitialized: function () {
                    $('.vsp-adds-slider button').attr("type", 'button');
                }
            });
        }

        $("body").on("click", 'a.vsp-inline-ajax, button.vsp-inline-ajax', $.VSPFRAMEWORK.init_inline_ajax);
    });

} )(jQuery, window, document);
