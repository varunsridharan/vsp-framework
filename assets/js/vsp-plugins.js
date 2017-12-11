//@codekit-append ../vendors/interdependencies/jquery.interdependencies.js
//@codekit-append ../vendors/blockui/jquery.blockui.js
//@codekit-append ../vendors/bootstrap/bootstrap-transition.js
//@codekit-append ../vendors/bootstrap/button/button.js
//@codekit-append ../vendors/bootstrap/tooltip/tooltip.js
//@codekit-append ../vendors/bootstrap/popover/popover.js
//@codekit-append ../vendors/switchery/switchery.js
//@codekit-append ../vendors/vspajax/jquery.vsp-ajax.js  
/*** jQuery Interdependencies library** http://miohtama.github.com/jquery-interdependencies/** Copyright 2012-2013 Mikko Ohtamaa, others*/ /*global console, window*/
(function ($) {
    "use strict";

    function log(msg) {
        if (window.console && window.console.log) {
            console.log(msg);
        }
    }

    function safeFind(context, selector) {
        if (selector[0] == "#") {
            if (selector.indexOf(" ") < 0) {
                return $(selector);
            }
        }
        return context.find(selector);
    }
    var configExample = {
        show: null,
        hide: null,
        log: false,
        checkTargets: true
    };

    function Rule(controller, condition, value) {
        this.init(controller, condition, value);
    }
    $.extend(Rule.prototype, {
        init: function (controller, condition, value) {
            this.controller = controller;
            this.condition = condition;
            this.value = value;
            this.rules = [];
            this.controls = [];
        },
        evalCondition: function (context, control, condition, val1, val2) {
            if (condition == "==" || condition == "OR") {
                return this.checkBoolean(val1) == this.checkBoolean(val2);
            } else if (condition == "!=") {
                return this.checkBoolean(val1) != this.checkBoolean(val2);
            } else if (condition == ">=") {
                return Number(val2) >= Number(val1);
            } else if (condition == "<=") {
                return Number(val2) <= Number(val1);
            } else if (condition == ">") {
                return Number(val2) > Number(val1);
            } else if (condition == "<") {
                return Number(val2) < Number(val1);
            } else if (condition == "()") {
                return window[val1](context, control, val2);
            } else if (condition == "any") {
                return $.inArray(val2, val1.split(',')) > -1;
            } else if (condition == "not-any") {
                return $.inArray(val2, val1.split(',')) == -1;
            } else {
                throw new Error("Unknown condition:" + condition);
            }
        },
        checkCondition: function (context, cfg) {
            if (!this.condition) {
                return true;
            }
            var control = context.find(this.controller);
            if (control.size() === 0 && cfg.log) {
                log("Evaling condition: Could not find controller input " + this.controller);
            }
            var val = this.getControlValue(context, control);
            if (cfg.log && val === undefined) {
                log("Evaling condition: Could not exctract value from input " + this.controller);
            }
            if (val === undefined) {
                return false;
            }
            val = this.normalizeValue(control, this.value, val);
            return this.evalCondition(context, control, this.condition, this.value, val);
        },
        normalizeValue: function (control, baseValue, val) {
            if (typeof baseValue == "number") {
                return parseFloat(val);
            }
            return val;
        },
        getControlValue: function (context, control) {
            if ((control.attr("type") == "radio" || control.attr("type") == "checkbox") && control.size() > 1) {
                return control.filter(":checked").val();
            }
            if (control.attr("type") == "checkbox" || control.attr("type") == "radio") {
                return control.is(":checked");
            }
            return control.val();
        },
        createRule: function (controller, condition, value) {
            var rule = new Rule(controller, condition, value);
            this.rules.push(rule);
            return rule;
        },
        include: function (input) {
            if (!input) {
                throw new Error("Must give an input selector");
            }
            this.controls.push(input);
        },
        applyRule: function (context, cfg, enforced) {
            var result;
            if (enforced === undefined) {
                result = this.checkCondition(context, cfg);
            } else {
                result = enforced;
            }
            if (cfg.log) {
                log("Applying rule on " + this.controller + "==" + this.value + " enforced:" + enforced + " result:" + result);
            }
            if (cfg.log && !this.controls.length) {
                log("Zero length controls slipped through");
            }
            var show = cfg.show || function (control) {
                control.show();
            };
            var hide = cfg.hide || function (control) {
                control.hide();
            };
            var controls = $.map(this.controls, function (elem, idx) {
                var control = context.find(elem);
                if (cfg.log && control.size() === 0) {
                    log("Could not find element:" + elem);
                }
                return control;
            });
            if (result) {
                $(controls).each(function () {
                    if (cfg.log && $(this).size() === 0) {
                        log("Control selection is empty when showing");
                        log(this);
                    }
                    show(this);
                });
                $(this.rules).each(function () {
                    this.applyRule(context, cfg);
                });
            } else {
                $(controls).each(function () {
                    if (cfg.log && $(this).size() === 0) {
                        log("Control selection is empty when hiding:");
                        log(this);
                    }
                    hide(this);
                });
                $(this.rules).each(function () {
                    this.applyRule(context, cfg, false);
                });
            }
        },
        checkBoolean: function (value) {
            switch (value) {
                case true:
                case 'true':
                case 1:
                case '1':
                    value = true;
                    break;
                case false:
                case 'false':
                case 0:
                case '0':
                    value = false;
                    break;
            }
            return value;
        },
    });

    function Ruleset() {
        this.rules = [];
    }
    $.extend(Ruleset.prototype, {
        createRule: function (controller, condition, value) {
            var rule = new Rule(controller, condition, value);
            this.rules.push(rule);
            return rule;
        },
        applyRules: function (context, cfg) {
            var i;
            cfg = cfg || {};
            if (cfg.log) {
                log("Starting evaluation ruleset of " + this.rules.length + " rules");
            }
            for (i = 0; i < this.rules.length; i++) {
                this.rules[i].applyRule(context, cfg);
            }
        },
        walk: function () {
            var rules = [];

            function descent(rule) {
                rules.push(rule);
                $(rule.children).each(function () {
                    descent(this);
                });
            }
            $(this.rules).each(function () {
                descent(this);
            });
            return rules;
        },
        checkTargets: function (context, cfg) {
            var controls = 0;
            var rules = this.walk();
            $(rules).each(function () {
                if (context.find(this.controller).size() === 0) {
                    throw new Error("Rule's controller does not exist:" + this.controller);
                }
                if (this.controls.length === 0) {
                    throw new Error("Rule has no controls:" + this);
                }
                $(this.controls).each(function () {
                    if (safeFind(context, this) === 0) {
                        throw new Error("Rule's target control " + this + " does not exist in context " + context.get(0));
                    }
                    controls++;
                });
            });
            if (cfg.log) {
                log("Controller check ok, rules count " + rules.length + " controls count " + controls);
            }
        },
        install: function (cfg) {
            $.deps.enable($(document.body), this, cfg);
        }
    });
    var deps = {
        createRuleset: function () {
            return new Ruleset();
        },
        enable: function (selection, ruleset, cfg) {
            cfg = cfg || {};
            if (cfg.checkTargets || cfg.checkTargets === undefined) {
                ruleset.checkTargets(selection, cfg);
            }
            var self = this;
            if (cfg.log) {
                log("Enabling dependency change monitoring on " + selection.get(0));
            }
            var handler = function () {
                ruleset.applyRules(selection, cfg);
            };
            var val = selection.on ? selection.on("change.deps", null, null, handler) : selection.live("change.deps", handler);
            ruleset.applyRules(selection, cfg);
            return val;
        }
    };
    $.deps = deps;
})(jQuery);

/*!
 * jQuery blockUI plugin
 * Version 2.70.0-2014.11.23
 * Requires jQuery v1.7 or later
 *
 * Examples at: http://malsup.com/jquery/block/
 * Copyright (c) 2007-2013 M. Alsup
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 * Thanks to Amir-Hossein Sobhi for some excellent contributions!
 */

;
(function () {
    /*jshint eqeqeq:false curly:false latedef:false */
    "use strict";

    function setup($) {
        $.fn._fadeIn = $.fn.fadeIn;

        var noOp = $.noop || function () {};

        // this bit is to ensure we don't call setExpression when we shouldn't (with extra muscle to handle
        // confusing userAgent strings on Vista)
        var msie = /MSIE/.test(navigator.userAgent);
        var ie6 = /MSIE 6.0/.test(navigator.userAgent) && !/MSIE 8.0/.test(navigator.userAgent);
        var mode = document.documentMode || 0;
        var setExpr = $.isFunction(document.createElement('div').style.setExpression);

        // global $ methods for blocking/unblocking the entire page
        $.blockUI = function (opts) {
            install(window, opts);
        };
        $.unblockUI = function (opts) {
            remove(window, opts);
        };

        // convenience method for quick growl-like notifications  (http://www.google.com/search?q=growl)
        $.growlUI = function (title, message, timeout, onClose) {
            var $m = $('<div class="growlUI"></div>');
            if (title) $m.append('<h1>' + title + '</h1>');
            if (message) $m.append('<h2>' + message + '</h2>');
            if (timeout === undefined) timeout = 3000;

            // Added by konapun: Set timeout to 30 seconds if this growl is moused over, like normal toast notifications
            var callBlock = function (opts) {
                opts = opts || {};

                $.blockUI({
                    message: $m,
                    fadeIn: typeof opts.fadeIn !== 'undefined' ? opts.fadeIn : 700,
                    fadeOut: typeof opts.fadeOut !== 'undefined' ? opts.fadeOut : 1000,
                    timeout: typeof opts.timeout !== 'undefined' ? opts.timeout : timeout,
                    centerY: false,
                    showOverlay: false,
                    onUnblock: onClose,
                    css: $.blockUI.defaults.growlCSS
                });
            };

            callBlock();
            var nonmousedOpacity = $m.css('opacity');
            $m.mouseover(function () {
                callBlock({
                    fadeIn: 0,
                    timeout: 30000
                });

                var displayBlock = $('.blockMsg');
                displayBlock.stop(); // cancel fadeout if it has started
                displayBlock.fadeTo(300, 1); // make it easier to read the message by removing transparency
            }).mouseout(function () {
                $('.blockMsg').fadeOut(1000);
            });
            // End konapun additions
        };

        // plugin method for blocking element content
        $.fn.block = function (opts) {
            if (this[0] === window) {
                $.blockUI(opts);
                return this;
            }
            var fullOpts = $.extend({}, $.blockUI.defaults, opts || {});
            this.each(function () {
                var $el = $(this);
                if (fullOpts.ignoreIfBlocked && $el.data('blockUI.isBlocked'))
                    return;
                $el.unblock({
                    fadeOut: 0
                });
            });

            return this.each(function () {
                if ($.css(this, 'position') == 'static') {
                    this.style.position = 'relative';
                    $(this).data('blockUI.static', true);
                }
                this.style.zoom = 1; // force 'hasLayout' in ie
                install(this, opts);
            });
        };

        // plugin method for unblocking element content
        $.fn.unblock = function (opts) {
            if (this[0] === window) {
                $.unblockUI(opts);
                return this;
            }
            return this.each(function () {
                remove(this, opts);
            });
        };

        $.blockUI.version = 2.70; // 2nd generation blocking at no extra cost!

        // override these in your code to change the default behavior and style
        $.blockUI.defaults = {
            // message displayed when blocking (use null for no message)
            message: '<h1>Please wait...</h1>',

            title: null, // title string; only used when theme == true
            draggable: true, // only used when theme == true (requires jquery-ui.js to be loaded)

            theme: false, // set to true to use with jQuery UI themes

            // styles for the message when blocking; if you wish to disable
            // these and use an external stylesheet then do this in your code:
            // $.blockUI.defaults.css = {};
            css: {
                padding: 0,
                margin: 0,
                width: '30%',
                top: '40%',
                left: '35%',
                textAlign: 'center',
                color: '#000',
                border: '3px solid #aaa',
                backgroundColor: '#fff',
                cursor: 'wait'
            },

            // minimal style set used when themes are used
            themedCSS: {
                width: '30%',
                top: '40%',
                left: '35%'
            },

            // styles for the overlay
            overlayCSS: {
                backgroundColor: '#000',
                opacity: 0.6,
                cursor: 'wait'
            },

            // style to replace wait cursor before unblocking to correct issue
            // of lingering wait cursor
            cursorReset: 'default',

            // styles applied when using $.growlUI
            growlCSS: {
                width: '350px',
                top: '10px',
                left: '',
                right: '10px',
                border: 'none',
                padding: '5px',
                opacity: 0.6,
                cursor: 'default',
                color: '#fff',
                backgroundColor: '#000',
                '-webkit-border-radius': '10px',
                '-moz-border-radius': '10px',
                'border-radius': '10px'
            },

            // IE issues: 'about:blank' fails on HTTPS and javascript:false is s-l-o-w
            // (hat tip to Jorge H. N. de Vasconcelos)
            /*jshint scripturl:true */
            iframeSrc: /^https/i.test(window.location.href || '') ? 'javascript:false' : 'about:blank',

            // force usage of iframe in non-IE browsers (handy for blocking applets)
            forceIframe: false,

            // z-index for the blocking overlay
            baseZ: 1000,

            // set these to true to have the message automatically centered
            centerX: true, // <-- only effects element blocking (page block controlled via css above)
            centerY: true,

            // allow body element to be stetched in ie6; this makes blocking look better
            // on "short" pages.  disable if you wish to prevent changes to the body height
            allowBodyStretch: true,

            // enable if you want key and mouse events to be disabled for content that is blocked
            bindEvents: true,

            // be default blockUI will supress tab navigation from leaving blocking content
            // (if bindEvents is true)
            constrainTabKey: true,

            // fadeIn time in millis; set to 0 to disable fadeIn on block
            fadeIn: 200,

            // fadeOut time in millis; set to 0 to disable fadeOut on unblock
            fadeOut: 400,

            // time in millis to wait before auto-unblocking; set to 0 to disable auto-unblock
            timeout: 0,

            // disable if you don't want to show the overlay
            showOverlay: true,

            // if true, focus will be placed in the first available input field when
            // page blocking
            focusInput: true,

            // elements that can receive focus
            focusableElements: ':input:enabled:visible',

            // suppresses the use of overlay styles on FF/Linux (due to performance issues with opacity)
            // no longer needed in 2012
            // applyPlatformOpacityRules: true,

            // callback method invoked when fadeIn has completed and blocking message is visible
            onBlock: null,

            // callback method invoked when unblocking has completed; the callback is
            // passed the element that has been unblocked (which is the window object for page
            // blocks) and the options that were passed to the unblock call:
            //	onUnblock(element, options)
            onUnblock: null,

            // callback method invoked when the overlay area is clicked.
            // setting this will turn the cursor to a pointer, otherwise cursor defined in overlayCss will be used.
            onOverlayClick: null,

            // don't ask; if you really must know: http://groups.google.com/group/jquery-en/browse_thread/thread/36640a8730503595/2f6a79a77a78e493#2f6a79a77a78e493
            quirksmodeOffsetHack: 4,

            // class name of the message block
            blockMsgClass: 'blockMsg',

            // if it is already blocked, then ignore it (don't unblock and reblock)
            ignoreIfBlocked: false
        };

        // private data and functions follow...

        var pageBlock = null;
        var pageBlockEls = [];

        function install(el, opts) {
            var css, themedCSS;
            var full = (el == window);
            var msg = (opts && opts.message !== undefined ? opts.message : undefined);
            opts = $.extend({}, $.blockUI.defaults, opts || {});

            if (opts.ignoreIfBlocked && $(el).data('blockUI.isBlocked'))
                return;

            opts.overlayCSS = $.extend({}, $.blockUI.defaults.overlayCSS, opts.overlayCSS || {});
            css = $.extend({}, $.blockUI.defaults.css, opts.css || {});
            if (opts.onOverlayClick)
                opts.overlayCSS.cursor = 'pointer';

            themedCSS = $.extend({}, $.blockUI.defaults.themedCSS, opts.themedCSS || {});
            msg = msg === undefined ? opts.message : msg;

            // remove the current block (if there is one)
            if (full && pageBlock)
                remove(window, {
                    fadeOut: 0
                });

            // if an existing element is being used as the blocking content then we capture
            // its current place in the DOM (and current display style) so we can restore
            // it when we unblock
            if (msg && typeof msg != 'string' && (msg.parentNode || msg.jquery)) {
                var node = msg.jquery ? msg[0] : msg;
                var data = {};
                $(el).data('blockUI.history', data);
                data.el = node;
                data.parent = node.parentNode;
                data.display = node.style.display;
                data.position = node.style.position;
                if (data.parent)
                    data.parent.removeChild(node);
            }

            $(el).data('blockUI.onUnblock', opts.onUnblock);
            var z = opts.baseZ;

            // blockUI uses 3 layers for blocking, for simplicity they are all used on every platform;
            // layer1 is the iframe layer which is used to supress bleed through of underlying content
            // layer2 is the overlay layer which has opacity and a wait cursor (by default)
            // layer3 is the message content that is displayed while blocking
            var lyr1, lyr2, lyr3, s;
            if (msie || opts.forceIframe)
                lyr1 = $('<iframe class="blockUI" style="z-index:' + (z++) + ';display:none;border:none;margin:0;padding:0;position:absolute;width:100%;height:100%;top:0;left:0" src="' + opts.iframeSrc + '"></iframe>');
            else
                lyr1 = $('<div class="blockUI" style="display:none"></div>');

            if (opts.theme)
                lyr2 = $('<div class="blockUI blockOverlay ui-widget-overlay" style="z-index:' + (z++) + ';display:none"></div>');
            else
                lyr2 = $('<div class="blockUI blockOverlay" style="z-index:' + (z++) + ';display:none;border:none;margin:0;padding:0;width:100%;height:100%;top:0;left:0"></div>');

            if (opts.theme && full) {
                s = '<div class="blockUI ' + opts.blockMsgClass + ' blockPage ui-dialog ui-widget ui-corner-all" style="z-index:' + (z + 10) + ';display:none;position:fixed">';
                if (opts.title) {
                    s += '<div class="ui-widget-header ui-dialog-titlebar ui-corner-all blockTitle">' + (opts.title || '&nbsp;') + '</div>';
                }
                s += '<div class="ui-widget-content ui-dialog-content"></div>';
                s += '</div>';
            } else if (opts.theme) {
                s = '<div class="blockUI ' + opts.blockMsgClass + ' blockElement ui-dialog ui-widget ui-corner-all" style="z-index:' + (z + 10) + ';display:none;position:absolute">';
                if (opts.title) {
                    s += '<div class="ui-widget-header ui-dialog-titlebar ui-corner-all blockTitle">' + (opts.title || '&nbsp;') + '</div>';
                }
                s += '<div class="ui-widget-content ui-dialog-content"></div>';
                s += '</div>';
            } else if (full) {
                s = '<div class="blockUI ' + opts.blockMsgClass + ' blockPage" style="z-index:' + (z + 10) + ';display:none;position:fixed"></div>';
            } else {
                s = '<div class="blockUI ' + opts.blockMsgClass + ' blockElement" style="z-index:' + (z + 10) + ';display:none;position:absolute"></div>';
            }
            lyr3 = $(s);

            // if we have a message, style it
            if (msg) {
                if (opts.theme) {
                    lyr3.css(themedCSS);
                    lyr3.addClass('ui-widget-content');
                } else
                    lyr3.css(css);
            }

            // style the overlay
            if (!opts.theme /*&& (!opts.applyPlatformOpacityRules)*/ )
                lyr2.css(opts.overlayCSS);
            lyr2.css('position', full ? 'fixed' : 'absolute');

            // make iframe layer transparent in IE
            if (msie || opts.forceIframe)
                lyr1.css('opacity', 0.0);

            //$([lyr1[0],lyr2[0],lyr3[0]]).appendTo(full ? 'body' : el);
            var layers = [lyr1, lyr2, lyr3],
                $par = full ? $('body') : $(el);
            $.each(layers, function () {
                this.appendTo($par);
            });

            if (opts.theme && opts.draggable && $.fn.draggable) {
                lyr3.draggable({
                    handle: '.ui-dialog-titlebar',
                    cancel: 'li'
                });
            }

            // ie7 must use absolute positioning in quirks mode and to account for activex issues (when scrolling)
            var expr = setExpr && (!$.support.boxModel || $('object,embed', full ? null : el).length > 0);
            if (ie6 || expr) {
                // give body 100% height
                if (full && opts.allowBodyStretch && $.support.boxModel)
                    $('html,body').css('height', '100%');

                // fix ie6 issue when blocked element has a border width
                if ((ie6 || !$.support.boxModel) && !full) {
                    var t = sz(el, 'borderTopWidth'),
                        l = sz(el, 'borderLeftWidth');
                    var fixT = t ? '(0 - ' + t + ')' : 0;
                    var fixL = l ? '(0 - ' + l + ')' : 0;
                }

                // simulate fixed position
                $.each(layers, function (i, o) {
                    var s = o[0].style;
                    s.position = 'absolute';
                    if (i < 2) {
                        if (full)
                            s.setExpression('height', 'Math.max(document.body.scrollHeight, document.body.offsetHeight) - (jQuery.support.boxModel?0:' + opts.quirksmodeOffsetHack + ') + "px"');
                        else
                            s.setExpression('height', 'this.parentNode.offsetHeight + "px"');
                        if (full)
                            s.setExpression('width', 'jQuery.support.boxModel && document.documentElement.clientWidth || document.body.clientWidth + "px"');
                        else
                            s.setExpression('width', 'this.parentNode.offsetWidth + "px"');
                        if (fixL) s.setExpression('left', fixL);
                        if (fixT) s.setExpression('top', fixT);
                    } else if (opts.centerY) {
                        if (full) s.setExpression('top', '(document.documentElement.clientHeight || document.body.clientHeight) / 2 - (this.offsetHeight / 2) + (blah = document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop) + "px"');
                        s.marginTop = 0;
                    } else if (!opts.centerY && full) {
                        var top = (opts.css && opts.css.top) ? parseInt(opts.css.top, 10) : 0;
                        var expression = '((document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop) + ' + top + ') + "px"';
                        s.setExpression('top', expression);
                    }
                });
            }

            // show the message
            if (msg) {
                if (opts.theme)
                    lyr3.find('.ui-widget-content').append(msg);
                else
                    lyr3.append(msg);
                if (msg.jquery || msg.nodeType)
                    $(msg).show();
            }

            if ((msie || opts.forceIframe) && opts.showOverlay)
                lyr1.show(); // opacity is zero
            if (opts.fadeIn) {
                var cb = opts.onBlock ? opts.onBlock : noOp;
                var cb1 = (opts.showOverlay && !msg) ? cb : noOp;
                var cb2 = msg ? cb : noOp;
                if (opts.showOverlay)
                    lyr2._fadeIn(opts.fadeIn, cb1);
                if (msg)
                    lyr3._fadeIn(opts.fadeIn, cb2);
            } else {
                if (opts.showOverlay)
                    lyr2.show();
                if (msg)
                    lyr3.show();
                if (opts.onBlock)
                    opts.onBlock.bind(lyr3)();
            }

            // bind key and mouse events
            bind(1, el, opts);

            if (full) {
                pageBlock = lyr3[0];
                pageBlockEls = $(opts.focusableElements, pageBlock);
                if (opts.focusInput)
                    setTimeout(focus, 20);
            } else
                center(lyr3[0], opts.centerX, opts.centerY);

            if (opts.timeout) {
                // auto-unblock
                var to = setTimeout(function () {
                    if (full)
                        $.unblockUI(opts);
                    else
                        $(el).unblock(opts);
                }, opts.timeout);
                $(el).data('blockUI.timeout', to);
            }
        }

        // remove the block
        function remove(el, opts) {
            var count;
            var full = (el == window);
            var $el = $(el);
            var data = $el.data('blockUI.history');
            var to = $el.data('blockUI.timeout');
            if (to) {
                clearTimeout(to);
                $el.removeData('blockUI.timeout');
            }
            opts = $.extend({}, $.blockUI.defaults, opts || {});
            bind(0, el, opts); // unbind events

            if (opts.onUnblock === null) {
                opts.onUnblock = $el.data('blockUI.onUnblock');
                $el.removeData('blockUI.onUnblock');
            }

            var els;
            if (full) // crazy selector to handle odd field errors in ie6/7
                els = $('body').children().filter('.blockUI').add('body > .blockUI');
            else
                els = $el.find('>.blockUI');

            // fix cursor issue
            if (opts.cursorReset) {
                if (els.length > 1)
                    els[1].style.cursor = opts.cursorReset;
                if (els.length > 2)
                    els[2].style.cursor = opts.cursorReset;
            }

            if (full)
                pageBlock = pageBlockEls = null;

            if (opts.fadeOut) {
                count = els.length;
                els.stop().fadeOut(opts.fadeOut, function () {
                    if (--count === 0)
                        reset(els, data, opts, el);
                });
            } else
                reset(els, data, opts, el);
        }

        // move blocking element back into the DOM where it started
        function reset(els, data, opts, el) {
            var $el = $(el);
            if ($el.data('blockUI.isBlocked'))
                return;

            els.each(function (i, o) {
                // remove via DOM calls so we don't lose event handlers
                if (this.parentNode)
                    this.parentNode.removeChild(this);
            });

            if (data && data.el) {
                data.el.style.display = data.display;
                data.el.style.position = data.position;
                data.el.style.cursor = 'default'; // #59
                if (data.parent)
                    data.parent.appendChild(data.el);
                $el.removeData('blockUI.history');
            }

            if ($el.data('blockUI.static')) {
                $el.css('position', 'static'); // #22
            }

            if (typeof opts.onUnblock == 'function')
                opts.onUnblock(el, opts);

            // fix issue in Safari 6 where block artifacts remain until reflow
            var body = $(document.body),
                w = body.width(),
                cssW = body[0].style.width;
            body.width(w - 1).width(w);
            body[0].style.width = cssW;
        }

        // bind/unbind the handler
        function bind(b, el, opts) {
            var full = el == window,
                $el = $(el);

            // don't bother unbinding if there is nothing to unbind
            if (!b && (full && !pageBlock || !full && !$el.data('blockUI.isBlocked')))
                return;

            $el.data('blockUI.isBlocked', b);

            // don't bind events when overlay is not in use or if bindEvents is false
            if (!full || !opts.bindEvents || (b && !opts.showOverlay))
                return;

            // bind anchors and inputs for mouse and key events
            var events = 'mousedown mouseup keydown keypress keyup touchstart touchend touchmove';
            if (b)
                $(document).bind(events, opts, handler);
            else
                $(document).unbind(events, handler);

            // former impl...
            //		var $e = $('a,:input');
            //		b ? $e.bind(events, opts, handler) : $e.unbind(events, handler);
        }

        // event handler to suppress keyboard/mouse events when blocking
        function handler(e) {
            // allow tab navigation (conditionally)
            if (e.type === 'keydown' && e.keyCode && e.keyCode == 9) {
                if (pageBlock && e.data.constrainTabKey) {
                    var els = pageBlockEls;
                    var fwd = !e.shiftKey && e.target === els[els.length - 1];
                    var back = e.shiftKey && e.target === els[0];
                    if (fwd || back) {
                        setTimeout(function () {
                            focus(back);
                        }, 10);
                        return false;
                    }
                }
            }
            var opts = e.data;
            var target = $(e.target);
            if (target.hasClass('blockOverlay') && opts.onOverlayClick)
                opts.onOverlayClick(e);

            // allow events within the message content
            if (target.parents('div.' + opts.blockMsgClass).length > 0)
                return true;

            // allow events for content that is not being blocked
            return target.parents().children().filter('div.blockUI').length === 0;
        }

        function focus(back) {
            if (!pageBlockEls)
                return;
            var e = pageBlockEls[back === true ? pageBlockEls.length - 1 : 0];
            if (e)
                e.focus();
        }

        function center(el, x, y) {
            var p = el.parentNode,
                s = el.style;
            var l = ((p.offsetWidth - el.offsetWidth) / 2) - sz(p, 'borderLeftWidth');
            var t = ((p.offsetHeight - el.offsetHeight) / 2) - sz(p, 'borderTopWidth');
            if (x) s.left = l > 0 ? (l + 'px') : '0';
            if (y) s.top = t > 0 ? (t + 'px') : '0';
        }

        function sz(el, p) {
            return parseInt($.css(el, p), 10) || 0;
        }

    }


    /*global define:true */
    if (typeof define === 'function' && define.amd && define.amd.jQuery) {
        define(['jquery'], setup);
    } else {
        setup(jQuery);
    }

})();

/*!
 * Bootstrap v3.3.7 (http://getbootstrap.com)
 * Copyright 2011-2017 Twitter, Inc.
 */

+
function ($) {
    'use strict';

    // CSS TRANSITION SUPPORT (Shoutout: http://www.modernizr.com/)
    // ============================================================

    function transitionEnd() {
        var el = document.createElement('bootstrap')

        var transEndEventNames = {
            WebkitTransition: 'webkitTransitionEnd',
            MozTransition: 'transitionend',
            OTransition: 'oTransitionEnd otransitionend',
            transition: 'transitionend'
        }

        for (var name in transEndEventNames) {
            if (el.style[name] !== undefined) {
                return {
                    end: transEndEventNames[name]
                }
            }
        }

        return false // explicit for ie8 (  ._.)
    }

    // http://blog.alexmaccaw.com/css-transitions
    $.fn.emulateTransitionEnd = function (duration) {
        var called = false
        var $el = this
        $(this).one('bsTransitionEnd', function () {
            called = true
        })
        var callback = function () {
            if (!called) $($el).trigger($.support.transition.end)
        }
        setTimeout(callback, duration)
        return this
    }

    $(function () {
        $.support.transition = transitionEnd()

        if (!$.support.transition) return

        $.event.special.bsTransitionEnd = {
            bindType: $.support.transition.end,
            delegateType: $.support.transition.end,
            handle: function (e) {
                if ($(e.target).is(this)) return e.handleObj.handler.apply(this, arguments)
            }
        }
    })

}(jQuery);

/*!
 * Bootstrap v3.3.7 (http://getbootstrap.com)
 * Copyright 2011-2017 Twitter, Inc.
 */

+
function ($) {
    'use strict';
    var VSPButton = function (element, options) {
        this.$element = $(element)
        this.options = $.extend({}, VSPButton.DEFAULTS, options)
        this.isLoading = false
    }

    VSPButton.VERSION = '3.3.7'

    VSPButton.DEFAULTS = {
        loadingText: 'loading...'
    }

    VSPButton.prototype.setState = function (state) {
        var d = 'disabled'
        var $el = this.$element
        var val = $el.is('input') ? 'val' : 'html'
        var data = $el.data()
        state += 'Text'
        if (data.resetText == null) $el.data('resetText', $el[val]())

        setTimeout($.proxy(function () {
            $el[val](data[state] == null ? this.options[state] : data[state])
            if (state == 'loadingText') {
                this.isLoading = true
                $el.addClass(d).attr(d, d).prop(d, true)
            } else if (this.isLoading) {
                this.isLoading = false
                $el.removeClass(d).removeAttr(d).prop(d, false)
            }
        }, this), 0)
    }

    VSPButton.prototype.toggle = function () {
        var changed = true
        var $parent = this.$element.closest('[data-toggle="buttons"]')

        if ($parent.length) {
            var $input = this.$element.find('input')
            if ($input.prop('type') == 'radio') {
                if ($input.prop('checked')) changed = false
                $parent.find('.active').removeClass('active')
                this.$element.addClass('active')
            } else if ($input.prop('type') == 'checkbox') {
                if (($input.prop('checked')) !== this.$element.hasClass('active')) changed = false
                this.$element.toggleClass('active')
            }
            $input.prop('checked', this.$element.hasClass('active'))
            if (changed) $input.trigger('change')
        } else {
            this.$element.attr('aria-pressed', !this.$element.hasClass('active'))
            this.$element.toggleClass('active')
        }
    }

    function vspbutton_plugin(option) {
        return this.each(function () {
            var $this = $(this)
            var data = $this.data('vsp.button')
            var options = typeof option == 'object' && option
            if (!data) $this.data('vsp.button', (data = new VSPButton(this, options)))
            if (option == 'toggle') data.toggle()
            else if (option) data.setState(option)
        })
    }

    var old = $.fn.vspbutton
    $.fn.vspbutton = vspbutton_plugin
    $.fn.vspbutton.Constructor = VSPButton
    $.fn.vspbutton.noConflict = function () {
        $.fn.vspbutton = old;
        return this
    }
}(jQuery);

/*!
 * Bootstrap v3.3.7 (http://getbootstrap.com)
 * Copyright 2011-2017 Twitter, Inc.
 */
+
function ($) {
    'use strict';

    // TOOLTIP PUBLIC CLASS DEFINITION
    // ===============================

    var VSPTooltip = function (element, options) {
        this.type = null
        this.options = null
        this.enabled = null
        this.timeout = null
        this.hoverState = null
        this.$element = null
        this.inState = null

        this.init('tooltip', element, options)
    }

    VSPTooltip.VERSION = '3.3.7'

    VSPTooltip.TRANSITION_DURATION = 150

    VSPTooltip.DEFAULTS = {
        animation: true,
        placement: 'top',
        selector: false,
        template: '<div class="tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',
        trigger: 'hover focus',
        title: '',
        delay: 0,
        html: false,
        container: false,
        viewport: {
            selector: 'body',
            padding: 0
        }
    }

    VSPTooltip.prototype.init = function (type, element, options) {
        this.enabled = true
        this.type = type
        this.$element = $(element)
        this.options = this.getOptions(options)
        this.$viewport = this.options.viewport && $($.isFunction(this.options.viewport) ? this.options.viewport.call(this, this.$element) : (this.options.viewport.selector || this.options.viewport))
        this.inState = {
            click: false,
            hover: false,
            focus: false
        }

        if (this.$element[0] instanceof document.constructor && !this.options.selector) {
            throw new Error('`selector` option must be specified when initializing ' + this.type + ' on the window.document object!')
        }

        var triggers = this.options.trigger.split(' ')

        for (var i = triggers.length; i--;) {
            var trigger = triggers[i]

            if (trigger == 'click') {
                this.$element.on('click.' + this.type, this.options.selector, $.proxy(this.toggle, this))
            } else if (trigger != 'manual') {
                var eventIn = trigger == 'hover' ? 'mouseenter' : 'focusin'
                var eventOut = trigger == 'hover' ? 'mouseleave' : 'focusout'

                this.$element.on(eventIn + '.' + this.type, this.options.selector, $.proxy(this.enter, this))
                this.$element.on(eventOut + '.' + this.type, this.options.selector, $.proxy(this.leave, this))
            }
        }

        this.options.selector ?
            (this._options = $.extend({}, this.options, {
                trigger: 'manual',
                selector: ''
            })) :
            this.fixTitle()
    }

    VSPTooltip.prototype.getDefaults = function () {
        return VSPTooltip.DEFAULTS
    }

    VSPTooltip.prototype.getOptions = function (options) {
        options = $.extend({}, this.getDefaults(), this.$element.data(), options)

        if (options.delay && typeof options.delay == 'number') {
            options.delay = {
                show: options.delay,
                hide: options.delay
            }
        }

        return options
    }

    VSPTooltip.prototype.getDelegateOptions = function () {
        var options = {}
        var defaults = this.getDefaults()

        this._options && $.each(this._options, function (key, value) {
            if (defaults[key] != value) options[key] = value
        })

        return options
    }

    VSPTooltip.prototype.enter = function (obj) {
        var self = obj instanceof this.constructor ?
            obj : $(obj.currentTarget).data('bs.' + this.type)

        if (!self) {
            self = new this.constructor(obj.currentTarget, this.getDelegateOptions())
            $(obj.currentTarget).data('bs.' + this.type, self)
        }

        if (obj instanceof $.Event) {
            self.inState[obj.type == 'focusin' ? 'focus' : 'hover'] = true
        }

        if (self.tip().hasClass('in') || self.hoverState == 'in') {
            self.hoverState = 'in'
            return
        }

        clearTimeout(self.timeout)

        self.hoverState = 'in'

        if (!self.options.delay || !self.options.delay.show) return self.show()

        self.timeout = setTimeout(function () {
            if (self.hoverState == 'in') self.show()
        }, self.options.delay.show)
    }

    VSPTooltip.prototype.isInStateTrue = function () {
        for (var key in this.inState) {
            if (this.inState[key]) return true
        }

        return false
    }

    VSPTooltip.prototype.leave = function (obj) {
        var self = obj instanceof this.constructor ?
            obj : $(obj.currentTarget).data('bs.' + this.type)

        if (!self) {
            self = new this.constructor(obj.currentTarget, this.getDelegateOptions())
            $(obj.currentTarget).data('bs.' + this.type, self)
        }

        if (obj instanceof $.Event) {
            self.inState[obj.type == 'focusout' ? 'focus' : 'hover'] = false
        }

        if (self.isInStateTrue()) return

        clearTimeout(self.timeout)

        self.hoverState = 'out'

        if (!self.options.delay || !self.options.delay.hide) return self.hide()

        self.timeout = setTimeout(function () {
            if (self.hoverState == 'out') self.hide()
        }, self.options.delay.hide)
    }

    VSPTooltip.prototype.show = function () {
        var e = $.Event('show.bs.' + this.type)

        if (this.hasContent() && this.enabled) {
            this.$element.trigger(e)

            var inDom = $.contains(this.$element[0].ownerDocument.documentElement, this.$element[0])
            if (e.isDefaultPrevented() || !inDom) return
            var that = this

            var $tip = this.tip()

            var tipId = this.getUID(this.type)

            this.setContent()
            $tip.attr('id', tipId)
            this.$element.attr('aria-describedby', tipId)

            if (this.options.animation) $tip.addClass('fade')

            var placement = typeof this.options.placement == 'function' ?
                this.options.placement.call(this, $tip[0], this.$element[0]) :
                this.options.placement

            var autoToken = /\s?auto?\s?/i
            var autoPlace = autoToken.test(placement)
            if (autoPlace) placement = placement.replace(autoToken, '') || 'top'

            $tip
                .detach()
                .css({
                    top: 0,
                    left: 0,
                    display: 'block'
                })
                .addClass(placement)
                .data('bs.' + this.type, this)

            this.options.container ? $tip.appendTo(this.options.container) : $tip.insertAfter(this.$element)
            this.$element.trigger('inserted.bs.' + this.type)

            var pos = this.getPosition()
            var actualWidth = $tip[0].offsetWidth
            var actualHeight = $tip[0].offsetHeight

            if (autoPlace) {
                var orgPlacement = placement
                var viewportDim = this.getPosition(this.$viewport)

                placement = placement == 'bottom' && pos.bottom + actualHeight > viewportDim.bottom ? 'top' :
                    placement == 'top' && pos.top - actualHeight < viewportDim.top ? 'bottom' :
                    placement == 'right' && pos.right + actualWidth > viewportDim.width ? 'left' :
                    placement == 'left' && pos.left - actualWidth < viewportDim.left ? 'right' :
                    placement

                $tip
                    .removeClass(orgPlacement)
                    .addClass(placement)
            }

            var calculatedOffset = this.getCalculatedOffset(placement, pos, actualWidth, actualHeight)

            this.applyPlacement(calculatedOffset, placement)

            var complete = function () {
                var prevHoverState = that.hoverState
                that.$element.trigger('shown.bs.' + that.type)
                that.hoverState = null

                if (prevHoverState == 'out') that.leave(that)
            }

            $.support.transition && this.$tip.hasClass('fade') ?
                $tip
                .one('bsTransitionEnd', complete)
                .emulateTransitionEnd(VSPTooltip.TRANSITION_DURATION) :
                complete()
        }
    }

    VSPTooltip.prototype.applyPlacement = function (offset, placement) {
        var $tip = this.tip()
        var width = $tip[0].offsetWidth
        var height = $tip[0].offsetHeight

        // manually read margins because getBoundingClientRect includes difference
        var marginTop = parseInt($tip.css('margin-top'), 10)
        var marginLeft = parseInt($tip.css('margin-left'), 10)

        // we must check for NaN for ie 8/9
        if (isNaN(marginTop)) marginTop = 0
        if (isNaN(marginLeft)) marginLeft = 0

        offset.top += marginTop
        offset.left += marginLeft

        // $.fn.offset doesn't round pixel values
        // so we use setOffset directly with our own function B-0
        $.offset.setOffset($tip[0], $.extend({
            using: function (props) {
                $tip.css({
                    top: Math.round(props.top),
                    left: Math.round(props.left)
                })
            }
        }, offset), 0)

        $tip.addClass('in')

        // check to see if placing tip in new offset caused the tip to resize itself
        var actualWidth = $tip[0].offsetWidth
        var actualHeight = $tip[0].offsetHeight

        if (placement == 'top' && actualHeight != height) {
            offset.top = offset.top + height - actualHeight
        }

        var delta = this.getViewportAdjustedDelta(placement, offset, actualWidth, actualHeight)

        if (delta.left) offset.left += delta.left
        else offset.top += delta.top

        var isVertical = /top|bottom/.test(placement)
        var arrowDelta = isVertical ? delta.left * 2 - width + actualWidth : delta.top * 2 - height + actualHeight
        var arrowOffsetPosition = isVertical ? 'offsetWidth' : 'offsetHeight'

        $tip.offset(offset)
        this.replaceArrow(arrowDelta, $tip[0][arrowOffsetPosition], isVertical)
    }

    VSPTooltip.prototype.replaceArrow = function (delta, dimension, isVertical) {
        this.arrow()
            .css(isVertical ? 'left' : 'top', 50 * (1 - delta / dimension) + '%')
            .css(isVertical ? 'top' : 'left', '')
    }

    VSPTooltip.prototype.setContent = function () {
        var $tip = this.tip()
        var title = this.getTitle()

        $tip.find('.tooltip-inner')[this.options.html ? 'html' : 'text'](title)
        $tip.removeClass('fade in top bottom left right')
    }

    VSPTooltip.prototype.hide = function (callback) {
        var that = this
        var $tip = $(this.$tip)
        var e = $.Event('hide.bs.' + this.type)

        function complete() {
            if (that.hoverState != 'in') $tip.detach()
            if (that.$element) { // TODO: Check whether guarding this code with this `if` is really necessary.
                that.$element
                    .removeAttr('aria-describedby')
                    .trigger('hidden.bs.' + that.type)
            }
            callback && callback()
        }

        this.$element.trigger(e)

        if (e.isDefaultPrevented()) return

        $tip.removeClass('in')

        $.support.transition && $tip.hasClass('fade') ?
            $tip
            .one('bsTransitionEnd', complete)
            .emulateTransitionEnd(VSPTooltip.TRANSITION_DURATION) :
            complete()

        this.hoverState = null

        return this
    }

    VSPTooltip.prototype.fixTitle = function () {
        var $e = this.$element
        if ($e.attr('title') || typeof $e.attr('data-original-title') != 'string') {
            $e.attr('data-original-title', $e.attr('title') || '').attr('title', '')
        }
    }

    VSPTooltip.prototype.hasContent = function () {
        return this.getTitle()
    }

    VSPTooltip.prototype.getPosition = function ($element) {
        $element = $element || this.$element

        var el = $element[0]
        var isBody = el.tagName == 'BODY'

        var elRect = el.getBoundingClientRect()
        if (elRect.width == null) {
            // width and height are missing in IE8, so compute them manually; see https://github.com/twbs/bootstrap/issues/14093
            elRect = $.extend({}, elRect, {
                width: elRect.right - elRect.left,
                height: elRect.bottom - elRect.top
            })
        }
        var isSvg = window.SVGElement && el instanceof window.SVGElement
        // Avoid using $.offset() on SVGs since it gives incorrect results in jQuery 3.
        // See https://github.com/twbs/bootstrap/issues/20280
        var elOffset = isBody ? {
            top: 0,
            left: 0
        } : (isSvg ? null : $element.offset())
        var scroll = {
            scroll: isBody ? document.documentElement.scrollTop || document.body.scrollTop : $element.scrollTop()
        }
        var outerDims = isBody ? {
            width: $(window).width(),
            height: $(window).height()
        } : null

        return $.extend({}, elRect, scroll, outerDims, elOffset)
    }

    VSPTooltip.prototype.getCalculatedOffset = function (placement, pos, actualWidth, actualHeight) {
        return placement == 'bottom' ? {
                top: pos.top + pos.height,
                left: pos.left + pos.width / 2 - actualWidth / 2
            } :
            placement == 'top' ? {
                top: pos.top - actualHeight,
                left: pos.left + pos.width / 2 - actualWidth / 2
            } :
            placement == 'left' ? {
                top: pos.top + pos.height / 2 - actualHeight / 2,
                left: pos.left - actualWidth
            } :
            /* placement == 'right' */
            {
                top: pos.top + pos.height / 2 - actualHeight / 2,
                left: pos.left + pos.width
            }

    }

    VSPTooltip.prototype.getViewportAdjustedDelta = function (placement, pos, actualWidth, actualHeight) {
        var delta = {
            top: 0,
            left: 0
        }
        if (!this.$viewport) return delta

        var viewportPadding = this.options.viewport && this.options.viewport.padding || 0
        var viewportDimensions = this.getPosition(this.$viewport)

        if (/right|left/.test(placement)) {
            var topEdgeOffset = pos.top - viewportPadding - viewportDimensions.scroll
            var bottomEdgeOffset = pos.top + viewportPadding - viewportDimensions.scroll + actualHeight
            if (topEdgeOffset < viewportDimensions.top) { // top overflow
                delta.top = viewportDimensions.top - topEdgeOffset
            } else if (bottomEdgeOffset > viewportDimensions.top + viewportDimensions.height) { // bottom overflow
                delta.top = viewportDimensions.top + viewportDimensions.height - bottomEdgeOffset
            }
        } else {
            var leftEdgeOffset = pos.left - viewportPadding
            var rightEdgeOffset = pos.left + viewportPadding + actualWidth
            if (leftEdgeOffset < viewportDimensions.left) { // left overflow
                delta.left = viewportDimensions.left - leftEdgeOffset
            } else if (rightEdgeOffset > viewportDimensions.right) { // right overflow
                delta.left = viewportDimensions.left + viewportDimensions.width - rightEdgeOffset
            }
        }

        return delta
    }

    VSPTooltip.prototype.getTitle = function () {
        var title
        var $e = this.$element
        var o = this.options

        title = $e.attr('data-original-title') ||
            (typeof o.title == 'function' ? o.title.call($e[0]) : o.title)

        return title
    }

    VSPTooltip.prototype.getUID = function (prefix) {
        do prefix += ~~(Math.random() * 1000000)
        while (document.getElementById(prefix))
        return prefix
    }

    VSPTooltip.prototype.tip = function () {
        if (!this.$tip) {
            this.$tip = $(this.options.template)
            if (this.$tip.length != 1) {
                throw new Error(this.type + ' `template` option must consist of exactly 1 top-level element!')
            }
        }
        return this.$tip
    }

    VSPTooltip.prototype.arrow = function () {
        return (this.$arrow = this.$arrow || this.tip().find('.tooltip-arrow'))
    }

    VSPTooltip.prototype.enable = function () {
        this.enabled = true
    }

    VSPTooltip.prototype.disable = function () {
        this.enabled = false
    }

    VSPTooltip.prototype.toggleEnabled = function () {
        this.enabled = !this.enabled
    }

    VSPTooltip.prototype.toggle = function (e) {
        var self = this
        if (e) {
            self = $(e.currentTarget).data('bs.' + this.type)
            if (!self) {
                self = new this.constructor(e.currentTarget, this.getDelegateOptions())
                $(e.currentTarget).data('bs.' + this.type, self)
            }
        }

        if (e) {
            self.inState.click = !self.inState.click
            if (self.isInStateTrue()) self.enter(self)
            else self.leave(self)
        } else {
            self.tip().hasClass('in') ? self.leave(self) : self.enter(self)
        }
    }

    VSPTooltip.prototype.destroy = function () {
        var that = this
        clearTimeout(this.timeout)
        this.hide(function () {
            that.$element.off('.' + that.type).removeData('bs.' + that.type)
            if (that.$tip) {
                that.$tip.detach()
            }
            that.$tip = null
            that.$arrow = null
            that.$viewport = null
            that.$element = null
        })
    }


    // TOOLTIP PLUGIN DEFINITION
    // =========================

    function Plugin(option) {
        return this.each(function () {
            var $this = $(this)
            var data = $this.data('bs.tooltip')
            var options = typeof option == 'object' && option

            if (!data && /destroy|hide/.test(option)) return
            if (!data) $this.data('bs.tooltip', (data = new VSPTooltip(this, options)))
            if (typeof option == 'string') data[option]()
        })
    }

    var old = $.fn.vsptooltip

    $.fn.vsptooltip = Plugin
    $.fn.vsptooltip.Constructor = VSPTooltip


    // TOOLTIP NO CONFLICT
    // ===================

    $.fn.vsptooltip.noConflict = function () {
        $.fn.vsptooltip = old
        return this
    }

}(jQuery);
/*!
 * Bootstrap v3.3.7 (http://getbootstrap.com)
 * Copyright 2011-2017 Twitter, Inc.
 */

+
function ($) {
    'use strict';

    // POPOVER PUBLIC CLASS DEFINITION
    // ===============================

    var VSPPopover = function (element, options) {
        this.init('popover', element, options)
    }

    if (!$.fn.vsptooltip) throw new Error('VSPPopover requires tooltip.js')

    VSPPopover.VERSION = '3.3.7'

    VSPPopover.DEFAULTS = $.extend({}, $.fn.vsptooltip.Constructor.DEFAULTS, {
        placement: 'right',
        trigger: 'click',
        content: '',
        template: '<div class="popover" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'
    })

    VSPPopover.prototype = $.extend({}, $.fn.vsptooltip.Constructor.prototype)

    VSPPopover.prototype.constructor = VSPPopover

    VSPPopover.prototype.getDefaults = function () {
        return VSPPopover.DEFAULTS
    }

    VSPPopover.prototype.setContent = function () {
        var $tip = this.tip()
        var title = this.getTitle()
        var content = this.getContent()

        $tip.find('.popover-title')[this.options.html ? 'html' : 'text'](title)
        $tip.find('.popover-content').children().detach().end()[ // we use append for html objects to maintain js events
            this.options.html ? (typeof content == 'string' ? 'html' : 'append') : 'text'
            ](content)

        $tip.removeClass('fade top bottom left right in')

        // IE8 doesn't accept hiding via the `:empty` pseudo selector, we have to do
        // this manually by checking the contents.
        if (!$tip.find('.popover-title').html()) $tip.find('.popover-title').hide()
    }

    VSPPopover.prototype.hasContent = function () {
        return this.getTitle() || this.getContent()
    }

    VSPPopover.prototype.getContent = function () {
        var $e = this.$element
        var o = this.options

        return $e.attr('data-content') ||
            (typeof o.content == 'function' ?
                o.content.call($e[0]) :
                o.content)
    }

    VSPPopover.prototype.arrow = function () {
        return (this.$arrow = this.$arrow || this.tip().find('.arrow'))
    }


    // POPOVER PLUGIN DEFINITION
    // =========================

    function Plugin(option) {
        return this.each(function () {
            var $this = $(this)
            var data = $this.data('bs.popover')
            var options = typeof option == 'object' && option

            if (!data && /destroy|hide/.test(option)) return
            if (!data) $this.data('bs.popover', (data = new VSPPopover(this, options)))
            if (typeof option == 'string') data[option]()
        })
    }

    var old = $.fn.vsppopover

    $.fn.vsppopover = Plugin
    $.fn.vsppopover.Constructor = VSPPopover


    // POPOVER NO CONFLICT
    // ===================

    $.fn.vsppopover.noConflict = function () {
        $.fn.vsppopover = old
        return this
    }

}(jQuery);
;
(function () {

    /**
     * Require the module at `name`.
     *
     * @param {String} name
     * @return {Object} exports
     * @api public
     */

    function require(name) {
        var module = require.modules[name];
        if (!module) throw new Error('failed to require "' + name + '"');

        if (!('exports' in module) && typeof module.definition === 'function') {
            module.client = module.component = true;
            module.definition.call(this, module.exports = {}, module);
            delete module.definition;
        }

        return module.exports;
    }

    /**
     * Meta info, accessible in the global scope unless you use AMD option.
     */

    require.loader = 'component';

    /**
     * Internal helper object, contains a sorting function for semantiv versioning
     */
    require.helper = {};
    require.helper.semVerSort = function (a, b) {
        var aArray = a.version.split('.');
        var bArray = b.version.split('.');
        for (var i = 0; i < aArray.length; ++i) {
            var aInt = parseInt(aArray[i], 10);
            var bInt = parseInt(bArray[i], 10);
            if (aInt === bInt) {
                var aLex = aArray[i].substr(("" + aInt).length);
                var bLex = bArray[i].substr(("" + bInt).length);
                if (aLex === '' && bLex !== '') return 1;
                if (aLex !== '' && bLex === '') return -1;
                if (aLex !== '' && bLex !== '') return aLex > bLex ? 1 : -1;
                continue;
            } else if (aInt > bInt) {
                return 1;
            } else {
                return -1;
            }
        }
        return 0;
    }

    /**
     * Find and require a module which name starts with the provided name.
     * If multiple modules exists, the highest semver is used.
     * This function can only be used for remote dependencies.

     * @param {String} name - module name: `user~repo`
     * @param {Boolean} returnPath - returns the canonical require path if true,
     *                               otherwise it returns the epxorted module
     */
    require.latest = function (name, returnPath) {
        function showError(name) {
            throw new Error('failed to find latest module of "' + name + '"');
        }
        // only remotes with semvers, ignore local files conataining a '/'
        var versionRegexp = /(.*)~(.*)@v?(\d+\.\d+\.\d+[^\/]*)$/;
        var remoteRegexp = /(.*)~(.*)/;
        if (!remoteRegexp.test(name)) showError(name);
        var moduleNames = Object.keys(require.modules);
        var semVerCandidates = [];
        var otherCandidates = []; // for instance: name of the git branch
        for (var i = 0; i < moduleNames.length; i++) {
            var moduleName = moduleNames[i];
            if (new RegExp(name + '@').test(moduleName)) {
                var version = moduleName.substr(name.length + 1);
                var semVerMatch = versionRegexp.exec(moduleName);
                if (semVerMatch != null) {
                    semVerCandidates.push({
                        version: version,
                        name: moduleName
                    });
                } else {
                    otherCandidates.push({
                        version: version,
                        name: moduleName
                    });
                }
            }
        }
        if (semVerCandidates.concat(otherCandidates).length === 0) {
            showError(name);
        }
        if (semVerCandidates.length > 0) {
            var module = semVerCandidates.sort(require.helper.semVerSort).pop().name;
            if (returnPath === true) {
                return module;
            }
            return require(module);
        }
        // if the build contains more than one branch of the same module
        // you should not use this funciton
        var module = otherCandidates.sort(function (a, b) {
            return a.name > b.name
        })[0].name;
        if (returnPath === true) {
            return module;
        }
        return require(module);
    }

    /**
     * Registered modules.
     */

    require.modules = {};

    /**
     * Register module at `name` with callback `definition`.
     *
     * @param {String} name
     * @param {Function} definition
     * @api private
     */

    require.register = function (name, definition) {
        require.modules[name] = {
            definition: definition
        };
    };

    /**
     * Define a module's exports immediately with `exports`.
     *
     * @param {String} name
     * @param {Generic} exports
     * @api private
     */

    require.define = function (name, exports) {
        require.modules[name] = {
            exports: exports
        };
    };
    require.register("abpetkov~transitionize@0.0.3", function (exports, module) {

        /**
         * Transitionize 0.0.2
         * https://github.com/abpetkov/transitionize
         *
         * Authored by Alexander Petkov
         * https://github.com/abpetkov
         *
         * Copyright 2013, Alexander Petkov
         * License: The MIT License (MIT)
         * http://opensource.org/licenses/MIT
         *
         */

        /**
         * Expose `Transitionize`.
         */

        module.exports = Transitionize;

        /**
         * Initialize new Transitionize.
         *
         * @param {Object} element
         * @param {Object} props
         * @api public
         */

        function Transitionize(element, props) {
            if (!(this instanceof Transitionize)) return new Transitionize(element, props);

            this.element = element;
            this.props = props || {};
            this.init();
        }

        /**
         * Detect if Safari.
         *
         * @returns {Boolean}
         * @api private
         */

        Transitionize.prototype.isSafari = function () {
            return (/Safari/).test(navigator.userAgent) && (/Apple Computer/).test(navigator.vendor);
        };

        /**
         * Loop though the object and push the keys and values in an array.
         * Apply the CSS3 transition to the element and prefix with -webkit- for Safari.
         *
         * @api private
         */

        Transitionize.prototype.init = function () {
            var transitions = [];

            for (var key in this.props) {
                transitions.push(key + ' ' + this.props[key]);
            }

            this.element.style.transition = transitions.join(', ');
            if (this.isSafari()) this.element.style.webkitTransition = transitions.join(', ');
        };
    });

    require.register("ftlabs~fastclick@v0.6.11", function (exports, module) {
        /**
         * @preserve FastClick: polyfill to remove click delays on browsers with touch UIs.
         *
         * @version 0.6.11
         * @codingstandard ftlabs-jsv2
         * @copyright The Financial Times Limited [All Rights Reserved]
         * @license MIT License (see LICENSE.txt)
         */

        /*jslint browser:true, node:true*/
        /*global define, Event, Node*/


        /**
         * Instantiate fast-clicking listeners on the specificed layer.
         *
         * @constructor
         * @param {Element} layer The layer to listen on
         */
        function FastClick(layer) {
            'use strict';
            var oldOnClick, self = this;


            /**
             * Whether a click is currently being tracked.
             *
             * @type boolean
             */
            this.trackingClick = false;


            /**
             * Timestamp for when when click tracking started.
             *
             * @type number
             */
            this.trackingClickStart = 0;


            /**
             * The element being tracked for a click.
             *
             * @type EventTarget
             */
            this.targetElement = null;


            /**
             * X-coordinate of touch start event.
             *
             * @type number
             */
            this.touchStartX = 0;


            /**
             * Y-coordinate of touch start event.
             *
             * @type number
             */
            this.touchStartY = 0;


            /**
             * ID of the last touch, retrieved from Touch.identifier.
             *
             * @type number
             */
            this.lastTouchIdentifier = 0;


            /**
             * Touchmove boundary, beyond which a click will be cancelled.
             *
             * @type number
             */
            this.touchBoundary = 10;


            /**
             * The FastClick layer.
             *
             * @type Element
             */
            this.layer = layer;

            if (!layer || !layer.nodeType) {
                throw new TypeError('Layer must be a document node');
            }

            /** @type function() */
            this.onClick = function () {
                return FastClick.prototype.onClick.apply(self, arguments);
            };

            /** @type function() */
            this.onMouse = function () {
                return FastClick.prototype.onMouse.apply(self, arguments);
            };

            /** @type function() */
            this.onTouchStart = function () {
                return FastClick.prototype.onTouchStart.apply(self, arguments);
            };

            /** @type function() */
            this.onTouchMove = function () {
                return FastClick.prototype.onTouchMove.apply(self, arguments);
            };

            /** @type function() */
            this.onTouchEnd = function () {
                return FastClick.prototype.onTouchEnd.apply(self, arguments);
            };

            /** @type function() */
            this.onTouchCancel = function () {
                return FastClick.prototype.onTouchCancel.apply(self, arguments);
            };

            if (FastClick.notNeeded(layer)) {
                return;
            }

            // Set up event handlers as required
            if (this.deviceIsAndroid) {
                layer.addEventListener('mouseover', this.onMouse, true);
                layer.addEventListener('mousedown', this.onMouse, true);
                layer.addEventListener('mouseup', this.onMouse, true);
            }

            layer.addEventListener('click', this.onClick, true);
            layer.addEventListener('touchstart', this.onTouchStart, false);
            layer.addEventListener('touchmove', this.onTouchMove, false);
            layer.addEventListener('touchend', this.onTouchEnd, false);
            layer.addEventListener('touchcancel', this.onTouchCancel, false);

            // Hack is required for browsers that don't support Event#stopImmediatePropagation (e.g. Android 2)
            // which is how FastClick normally stops click events bubbling to callbacks registered on the FastClick
            // layer when they are cancelled.
            if (!Event.prototype.stopImmediatePropagation) {
                layer.removeEventListener = function (type, callback, capture) {
                    var rmv = Node.prototype.removeEventListener;
                    if (type === 'click') {
                        rmv.call(layer, type, callback.hijacked || callback, capture);
                    } else {
                        rmv.call(layer, type, callback, capture);
                    }
                };

                layer.addEventListener = function (type, callback, capture) {
                    var adv = Node.prototype.addEventListener;
                    if (type === 'click') {
                        adv.call(layer, type, callback.hijacked || (callback.hijacked = function (event) {
                            if (!event.propagationStopped) {
                                callback(event);
                            }
                        }), capture);
                    } else {
                        adv.call(layer, type, callback, capture);
                    }
                };
            }

            // If a handler is already declared in the element's onclick attribute, it will be fired before
            // FastClick's onClick handler. Fix this by pulling out the user-defined handler function and
            // adding it as listener.
            if (typeof layer.onclick === 'function') {

                // Android browser on at least 3.2 requires a new reference to the function in layer.onclick
                // - the old one won't work if passed to addEventListener directly.
                oldOnClick = layer.onclick;
                layer.addEventListener('click', function (event) {
                    oldOnClick(event);
                }, false);
                layer.onclick = null;
            }
        }


        /**
         * Android requires exceptions.
         *
         * @type boolean
         */
        FastClick.prototype.deviceIsAndroid = navigator.userAgent.indexOf('Android') > 0;


        /**
         * iOS requires exceptions.
         *
         * @type boolean
         */
        FastClick.prototype.deviceIsIOS = /iP(ad|hone|od)/.test(navigator.userAgent);


        /**
         * iOS 4 requires an exception for select elements.
         *
         * @type boolean
         */
        FastClick.prototype.deviceIsIOS4 = FastClick.prototype.deviceIsIOS && (/OS 4_\d(_\d)?/).test(navigator.userAgent);


        /**
         * iOS 6.0(+?) requires the target element to be manually derived
         *
         * @type boolean
         */
        FastClick.prototype.deviceIsIOSWithBadTarget = FastClick.prototype.deviceIsIOS && (/OS ([6-9]|\d{2})_\d/).test(navigator.userAgent);


        /**
         * Determine whether a given element requires a native click.
         *
         * @param {EventTarget|Element} target Target DOM element
         * @returns {boolean} Returns true if the element needs a native click
         */
        FastClick.prototype.needsClick = function (target) {
            'use strict';
            switch (target.nodeName.toLowerCase()) {

                // Don't send a synthetic click to disabled inputs (issue #62)
                case 'button':
                case 'select':
                case 'textarea':
                    if (target.disabled) {
                        return true;
                    }

                    break;
                case 'input':

                    // File inputs need real clicks on iOS 6 due to a browser bug (issue #68)
                    if ((this.deviceIsIOS && target.type === 'file') || target.disabled) {
                        return true;
                    }

                    break;
                case 'label':
                case 'video':
                    return true;
            }

            return (/\bneedsclick\b/).test(target.className);
        };


        /**
         * Determine whether a given element requires a call to focus to simulate click into element.
         *
         * @param {EventTarget|Element} target Target DOM element
         * @returns {boolean} Returns true if the element requires a call to focus to simulate native click.
         */
        FastClick.prototype.needsFocus = function (target) {
            'use strict';
            switch (target.nodeName.toLowerCase()) {
                case 'textarea':
                    return true;
                case 'select':
                    return !this.deviceIsAndroid;
                case 'input':
                    switch (target.type) {
                        case 'button':
                        case 'checkbox':
                        case 'file':
                        case 'image':
                        case 'radio':
                        case 'submit':
                            return false;
                    }

                    // No point in attempting to focus disabled inputs
                    return !target.disabled && !target.readOnly;
                default:
                    return (/\bneedsfocus\b/).test(target.className);
            }
        };


        /**
         * Send a click event to the specified element.
         *
         * @param {EventTarget|Element} targetElement
         * @param {Event} event
         */
        FastClick.prototype.sendClick = function (targetElement, event) {
            'use strict';
            var clickEvent, touch;

            // On some Android devices activeElement needs to be blurred otherwise the synthetic click will have no effect (#24)
            if (document.activeElement && document.activeElement !== targetElement) {
                document.activeElement.blur();
            }

            touch = event.changedTouches[0];

            // Synthesise a click event, with an extra attribute so it can be tracked
            clickEvent = document.createEvent('MouseEvents');
            clickEvent.initMouseEvent(this.determineEventType(targetElement), true, true, window, 1, touch.screenX, touch.screenY, touch.clientX, touch.clientY, false, false, false, false, 0, null);
            clickEvent.forwardedTouchEvent = true;
            targetElement.dispatchEvent(clickEvent);
        };

        FastClick.prototype.determineEventType = function (targetElement) {
            'use strict';

            //Issue #159: Android Chrome Select Box does not open with a synthetic click event
            if (this.deviceIsAndroid && targetElement.tagName.toLowerCase() === 'select') {
                return 'mousedown';
            }

            return 'click';
        };


        /**
         * @param {EventTarget|Element} targetElement
         */
        FastClick.prototype.focus = function (targetElement) {
            'use strict';
            var length;

            // Issue #160: on iOS 7, some input elements (e.g. date datetime) throw a vague TypeError on setSelectionRange. These elements don't have an integer value for the selectionStart and selectionEnd properties, but unfortunately that can't be used for detection because accessing the properties also throws a TypeError. Just check the type instead. Filed as Apple bug #15122724.
            if (this.deviceIsIOS && targetElement.setSelectionRange && targetElement.type.indexOf('date') !== 0 && targetElement.type !== 'time') {
                length = targetElement.value.length;
                targetElement.setSelectionRange(length, length);
            } else {
                targetElement.focus();
            }
        };


        /**
         * Check whether the given target element is a child of a scrollable layer and if so, set a flag on it.
         *
         * @param {EventTarget|Element} targetElement
         */
        FastClick.prototype.updateScrollParent = function (targetElement) {
            'use strict';
            var scrollParent, parentElement;

            scrollParent = targetElement.fastClickScrollParent;

            // Attempt to discover whether the target element is contained within a scrollable layer. Re-check if the
            // target element was moved to another parent.
            if (!scrollParent || !scrollParent.contains(targetElement)) {
                parentElement = targetElement;
                do {
                    if (parentElement.scrollHeight > parentElement.offsetHeight) {
                        scrollParent = parentElement;
                        targetElement.fastClickScrollParent = parentElement;
                        break;
                    }

                    parentElement = parentElement.parentElement;
                } while (parentElement);
            }

            // Always update the scroll top tracker if possible.
            if (scrollParent) {
                scrollParent.fastClickLastScrollTop = scrollParent.scrollTop;
            }
        };


        /**
         * @param {EventTarget} targetElement
         * @returns {Element|EventTarget}
         */
        FastClick.prototype.getTargetElementFromEventTarget = function (eventTarget) {
            'use strict';

            // On some older browsers (notably Safari on iOS 4.1 - see issue #56) the event target may be a text node.
            if (eventTarget.nodeType === Node.TEXT_NODE) {
                return eventTarget.parentNode;
            }

            return eventTarget;
        };


        /**
         * On touch start, record the position and scroll offset.
         *
         * @param {Event} event
         * @returns {boolean}
         */
        FastClick.prototype.onTouchStart = function (event) {
            'use strict';
            var targetElement, touch, selection;

            // Ignore multiple touches, otherwise pinch-to-zoom is prevented if both fingers are on the FastClick element (issue #111).
            if (event.targetTouches.length > 1) {
                return true;
            }

            targetElement = this.getTargetElementFromEventTarget(event.target);
            touch = event.targetTouches[0];

            if (this.deviceIsIOS) {

                // Only trusted events will deselect text on iOS (issue #49)
                selection = window.getSelection();
                if (selection.rangeCount && !selection.isCollapsed) {
                    return true;
                }

                if (!this.deviceIsIOS4) {

                    // Weird things happen on iOS when an alert or confirm dialog is opened from a click event callback (issue #23):
                    // when the user next taps anywhere else on the page, new touchstart and touchend events are dispatched
                    // with the same identifier as the touch event that previously triggered the click that triggered the alert.
                    // Sadly, there is an issue on iOS 4 that causes some normal touch events to have the same identifier as an
                    // immediately preceeding touch event (issue #52), so this fix is unavailable on that platform.
                    if (touch.identifier === this.lastTouchIdentifier) {
                        event.preventDefault();
                        return false;
                    }

                    this.lastTouchIdentifier = touch.identifier;

                    // If the target element is a child of a scrollable layer (using -webkit-overflow-scrolling: touch) and:
                    // 1) the user does a fling scroll on the scrollable layer
                    // 2) the user stops the fling scroll with another tap
                    // then the event.target of the last 'touchend' event will be the element that was under the user's finger
                    // when the fling scroll was started, causing FastClick to send a click event to that layer - unless a check
                    // is made to ensure that a parent layer was not scrolled before sending a synthetic click (issue #42).
                    this.updateScrollParent(targetElement);
                }
            }

            this.trackingClick = true;
            this.trackingClickStart = event.timeStamp;
            this.targetElement = targetElement;

            this.touchStartX = touch.pageX;
            this.touchStartY = touch.pageY;

            // Prevent phantom clicks on fast double-tap (issue #36)
            if ((event.timeStamp - this.lastClickTime) < 200) {
                event.preventDefault();
            }

            return true;
        };


        /**
         * Based on a touchmove event object, check whether the touch has moved past a boundary since it started.
         *
         * @param {Event} event
         * @returns {boolean}
         */
        FastClick.prototype.touchHasMoved = function (event) {
            'use strict';
            var touch = event.changedTouches[0],
                boundary = this.touchBoundary;

            if (Math.abs(touch.pageX - this.touchStartX) > boundary || Math.abs(touch.pageY - this.touchStartY) > boundary) {
                return true;
            }

            return false;
        };


        /**
         * Update the last position.
         *
         * @param {Event} event
         * @returns {boolean}
         */
        FastClick.prototype.onTouchMove = function (event) {
            'use strict';
            if (!this.trackingClick) {
                return true;
            }

            // If the touch has moved, cancel the click tracking
            if (this.targetElement !== this.getTargetElementFromEventTarget(event.target) || this.touchHasMoved(event)) {
                this.trackingClick = false;
                this.targetElement = null;
            }

            return true;
        };


        /**
         * Attempt to find the labelled control for the given label element.
         *
         * @param {EventTarget|HTMLLabelElement} labelElement
         * @returns {Element|null}
         */
        FastClick.prototype.findControl = function (labelElement) {
            'use strict';

            // Fast path for newer browsers supporting the HTML5 control attribute
            if (labelElement.control !== undefined) {
                return labelElement.control;
            }

            // All browsers under test that support touch events also support the HTML5 htmlFor attribute
            if (labelElement.htmlFor) {
                return document.getElementById(labelElement.htmlFor);
            }

            // If no for attribute exists, attempt to retrieve the first labellable descendant element
            // the list of which is defined here: http://www.w3.org/TR/html5/forms.html#category-label
            return labelElement.querySelector('button, input:not([type=hidden]), keygen, meter, output, progress, select, textarea');
        };


        /**
         * On touch end, determine whether to send a click event at once.
         *
         * @param {Event} event
         * @returns {boolean}
         */
        FastClick.prototype.onTouchEnd = function (event) {
            'use strict';
            var forElement, trackingClickStart, targetTagName, scrollParent, touch, targetElement = this.targetElement;

            if (!this.trackingClick) {
                return true;
            }

            // Prevent phantom clicks on fast double-tap (issue #36)
            if ((event.timeStamp - this.lastClickTime) < 200) {
                this.cancelNextClick = true;
                return true;
            }

            // Reset to prevent wrong click cancel on input (issue #156).
            this.cancelNextClick = false;

            this.lastClickTime = event.timeStamp;

            trackingClickStart = this.trackingClickStart;
            this.trackingClick = false;
            this.trackingClickStart = 0;

            // On some iOS devices, the targetElement supplied with the event is invalid if the layer
            // is performing a transition or scroll, and has to be re-detected manually. Note that
            // for this to function correctly, it must be called *after* the event target is checked!
            // See issue #57; also filed as rdar://13048589 .
            if (this.deviceIsIOSWithBadTarget) {
                touch = event.changedTouches[0];

                // In certain cases arguments of elementFromPoint can be negative, so prevent setting targetElement to null
                targetElement = document.elementFromPoint(touch.pageX - window.pageXOffset, touch.pageY - window.pageYOffset) || targetElement;
                targetElement.fastClickScrollParent = this.targetElement.fastClickScrollParent;
            }

            targetTagName = targetElement.tagName.toLowerCase();
            if (targetTagName === 'label') {
                forElement = this.findControl(targetElement);
                if (forElement) {
                    this.focus(targetElement);
                    if (this.deviceIsAndroid) {
                        return false;
                    }

                    targetElement = forElement;
                }
            } else if (this.needsFocus(targetElement)) {

                // Case 1: If the touch started a while ago (best guess is 100ms based on tests for issue #36) then focus will be triggered anyway. Return early and unset the target element reference so that the subsequent click will be allowed through.
                // Case 2: Without this exception for input elements tapped when the document is contained in an iframe, then any inputted text won't be visible even though the value attribute is updated as the user types (issue #37).
                if ((event.timeStamp - trackingClickStart) > 100 || (this.deviceIsIOS && window.top !== window && targetTagName === 'input')) {
                    this.targetElement = null;
                    return false;
                }

                this.focus(targetElement);

                // Select elements need the event to go through on iOS 4, otherwise the selector menu won't open.
                if (!this.deviceIsIOS4 || targetTagName !== 'select') {
                    this.targetElement = null;
                    event.preventDefault();
                }

                return false;
            }

            if (this.deviceIsIOS && !this.deviceIsIOS4) {

                // Don't send a synthetic click event if the target element is contained within a parent layer that was scrolled
                // and this tap is being used to stop the scrolling (usually initiated by a fling - issue #42).
                scrollParent = targetElement.fastClickScrollParent;
                if (scrollParent && scrollParent.fastClickLastScrollTop !== scrollParent.scrollTop) {
                    return true;
                }
            }

            // Prevent the actual click from going though - unless the target node is marked as requiring
            // real clicks or if it is in the whitelist in which case only non-programmatic clicks are permitted.
            if (!this.needsClick(targetElement)) {
                event.preventDefault();
                this.sendClick(targetElement, event);
            }

            return false;
        };


        /**
         * On touch cancel, stop tracking the click.
         *
         * @returns {void}
         */
        FastClick.prototype.onTouchCancel = function () {
            'use strict';
            this.trackingClick = false;
            this.targetElement = null;
        };


        /**
         * Determine mouse events which should be permitted.
         *
         * @param {Event} event
         * @returns {boolean}
         */
        FastClick.prototype.onMouse = function (event) {
            'use strict';

            // If a target element was never set (because a touch event was never fired) allow the event
            if (!this.targetElement) {
                return true;
            }

            if (event.forwardedTouchEvent) {
                return true;
            }

            // Programmatically generated events targeting a specific element should be permitted
            if (!event.cancelable) {
                return true;
            }

            // Derive and check the target element to see whether the mouse event needs to be permitted;
            // unless explicitly enabled, prevent non-touch click events from triggering actions,
            // to prevent ghost/doubleclicks.
            if (!this.needsClick(this.targetElement) || this.cancelNextClick) {

                // Prevent any user-added listeners declared on FastClick element from being fired.
                if (event.stopImmediatePropagation) {
                    event.stopImmediatePropagation();
                } else {

                    // Part of the hack for browsers that don't support Event#stopImmediatePropagation (e.g. Android 2)
                    event.propagationStopped = true;
                }

                // Cancel the event
                event.stopPropagation();
                event.preventDefault();

                return false;
            }

            // If the mouse event is permitted, return true for the action to go through.
            return true;
        };


        /**
         * On actual clicks, determine whether this is a touch-generated click, a click action occurring
         * naturally after a delay after a touch (which needs to be cancelled to avoid duplication), or
         * an actual click which should be permitted.
         *
         * @param {Event} event
         * @returns {boolean}
         */
        FastClick.prototype.onClick = function (event) {
            'use strict';
            var permitted;

            // It's possible for another FastClick-like library delivered with third-party code to fire a click event before FastClick does (issue #44). In that case, set the click-tracking flag back to false and return early. This will cause onTouchEnd to return early.
            if (this.trackingClick) {
                this.targetElement = null;
                this.trackingClick = false;
                return true;
            }

            // Very odd behaviour on iOS (issue #18): if a submit element is present inside a form and the user hits enter in the iOS simulator or clicks the Go button on the pop-up OS keyboard the a kind of 'fake' click event will be triggered with the submit-type input element as the target.
            if (event.target.type === 'submit' && event.detail === 0) {
                return true;
            }

            permitted = this.onMouse(event);

            // Only unset targetElement if the click is not permitted. This will ensure that the check for !targetElement in onMouse fails and the browser's click doesn't go through.
            if (!permitted) {
                this.targetElement = null;
            }

            // If clicks are permitted, return true for the action to go through.
            return permitted;
        };


        /**
         * Remove all FastClick's event listeners.
         *
         * @returns {void}
         */
        FastClick.prototype.destroy = function () {
            'use strict';
            var layer = this.layer;

            if (this.deviceIsAndroid) {
                layer.removeEventListener('mouseover', this.onMouse, true);
                layer.removeEventListener('mousedown', this.onMouse, true);
                layer.removeEventListener('mouseup', this.onMouse, true);
            }

            layer.removeEventListener('click', this.onClick, true);
            layer.removeEventListener('touchstart', this.onTouchStart, false);
            layer.removeEventListener('touchmove', this.onTouchMove, false);
            layer.removeEventListener('touchend', this.onTouchEnd, false);
            layer.removeEventListener('touchcancel', this.onTouchCancel, false);
        };


        /**
         * Check whether FastClick is needed.
         *
         * @param {Element} layer The layer to listen on
         */
        FastClick.notNeeded = function (layer) {
            'use strict';
            var metaViewport;
            var chromeVersion;

            // Devices that don't support touch don't need FastClick
            if (typeof window.ontouchstart === 'undefined') {
                return true;
            }

            // Chrome version - zero for other browsers
            chromeVersion = +(/Chrome\/([0-9]+)/.exec(navigator.userAgent) || [, 0])[1];

            if (chromeVersion) {

                if (FastClick.prototype.deviceIsAndroid) {
                    metaViewport = document.querySelector('meta[name=viewport]');

                    if (metaViewport) {
                        // Chrome on Android with user-scalable="no" doesn't need FastClick (issue #89)
                        if (metaViewport.content.indexOf('user-scalable=no') !== -1) {
                            return true;
                        }
                        // Chrome 32 and above with width=device-width or less don't need FastClick
                        if (chromeVersion > 31 && window.innerWidth <= window.screen.width) {
                            return true;
                        }
                    }

                    // Chrome desktop doesn't need FastClick (issue #15)
                } else {
                    return true;
                }
            }

            // IE10 with -ms-touch-action: none, which disables double-tap-to-zoom (issue #97)
            if (layer.style.msTouchAction === 'none') {
                return true;
            }

            return false;
        };


        /**
         * Factory method for creating a FastClick object
         *
         * @param {Element} layer The layer to listen on
         */
        FastClick.attach = function (layer) {
            'use strict';
            return new FastClick(layer);
        };


        if (typeof define !== 'undefined' && define.amd) {

            // AMD. Register as an anonymous module.
            define(function () {
                'use strict';
                return FastClick;
            });
        } else if (typeof module !== 'undefined' && module.exports) {
            module.exports = FastClick.attach;
            module.exports.FastClick = FastClick;
        } else {
            window.FastClick = FastClick;
        }

    });

    require.register("component~indexof@0.0.3", function (exports, module) {
        module.exports = function (arr, obj) {
            if (arr.indexOf) return arr.indexOf(obj);
            for (var i = 0; i < arr.length; ++i) {
                if (arr[i] === obj) return i;
            }
            return -1;
        };
    });

    require.register("component~classes@1.2.1", function (exports, module) {
        /**
         * Module dependencies.
         */

        var index = require('component~indexof@0.0.3');

        /**
         * Whitespace regexp.
         */

        var re = /\s+/;

        /**
         * toString reference.
         */

        var toString = Object.prototype.toString;

        /**
         * Wrap `el` in a `ClassList`.
         *
         * @param {Element} el
         * @return {ClassList}
         * @api public
         */

        module.exports = function (el) {
            return new ClassList(el);
        };

        /**
         * Initialize a new ClassList for `el`.
         *
         * @param {Element} el
         * @api private
         */

        function ClassList(el) {
            if (!el) throw new Error('A DOM element reference is required');
            this.el = el;
            this.list = el.classList;
        }

        /**
         * Add class `name` if not already present.
         *
         * @param {String} name
         * @return {ClassList}
         * @api public
         */

        ClassList.prototype.add = function (name) {
            // classList
            if (this.list) {
                this.list.add(name);
                return this;
            }

            // fallback
            var arr = this.array();
            var i = index(arr, name);
            if (!~i) arr.push(name);
            this.el.className = arr.join(' ');
            return this;
        };

        /**
         * Remove class `name` when present, or
         * pass a regular expression to remove
         * any which match.
         *
         * @param {String|RegExp} name
         * @return {ClassList}
         * @api public
         */

        ClassList.prototype.remove = function (name) {
            if ('[object RegExp]' == toString.call(name)) {
                return this.removeMatching(name);
            }

            // classList
            if (this.list) {
                this.list.remove(name);
                return this;
            }

            // fallback
            var arr = this.array();
            var i = index(arr, name);
            if (~i) arr.splice(i, 1);
            this.el.className = arr.join(' ');
            return this;
        };

        /**
         * Remove all classes matching `re`.
         *
         * @param {RegExp} re
         * @return {ClassList}
         * @api private
         */

        ClassList.prototype.removeMatching = function (re) {
            var arr = this.array();
            for (var i = 0; i < arr.length; i++) {
                if (re.test(arr[i])) {
                    this.remove(arr[i]);
                }
            }
            return this;
        };

        /**
         * Toggle class `name`, can force state via `force`.
         *
         * For browsers that support classList, but do not support `force` yet,
         * the mistake will be detected and corrected.
         *
         * @param {String} name
         * @param {Boolean} force
         * @return {ClassList}
         * @api public
         */

        ClassList.prototype.toggle = function (name, force) {
            // classList
            if (this.list) {
                if ("undefined" !== typeof force) {
                    if (force !== this.list.toggle(name, force)) {
                        this.list.toggle(name); // toggle again to correct
                    }
                } else {
                    this.list.toggle(name);
                }
                return this;
            }

            // fallback
            if ("undefined" !== typeof force) {
                if (!force) {
                    this.remove(name);
                } else {
                    this.add(name);
                }
            } else {
                if (this.has(name)) {
                    this.remove(name);
                } else {
                    this.add(name);
                }
            }

            return this;
        };

        /**
         * Return an array of classes.
         *
         * @return {Array}
         * @api public
         */

        ClassList.prototype.array = function () {
            var str = this.el.className.replace(/^\s+|\s+$/g, '');
            var arr = str.split(re);
            if ('' === arr[0]) arr.shift();
            return arr;
        };

        /**
         * Check if class `name` is present.
         *
         * @param {String} name
         * @return {ClassList}
         * @api public
         */

        ClassList.prototype.has =
            ClassList.prototype.contains = function (name) {
                return this.list ?
                    this.list.contains(name) :
                    !!~index(this.array(), name);
            };

    });

    require.register("component~event@0.1.4", function (exports, module) {
        var bind = window.addEventListener ? 'addEventListener' : 'attachEvent',
            unbind = window.removeEventListener ? 'removeEventListener' : 'detachEvent',
            prefix = bind !== 'addEventListener' ? 'on' : '';

        /**
         * Bind `el` event `type` to `fn`.
         *
         * @param {Element} el
         * @param {String} type
         * @param {Function} fn
         * @param {Boolean} capture
         * @return {Function}
         * @api public
         */

        exports.bind = function (el, type, fn, capture) {
            el[bind](prefix + type, fn, capture || false);
            return fn;
        };

        /**
         * Unbind `el` event `type`'s callback `fn`.
         *
         * @param {Element} el
         * @param {String} type
         * @param {Function} fn
         * @param {Boolean} capture
         * @return {Function}
         * @api public
         */

        exports.unbind = function (el, type, fn, capture) {
            el[unbind](prefix + type, fn, capture || false);
            return fn;
        };
    });

    require.register("component~query@0.0.3", function (exports, module) {
        function one(selector, el) {
            return el.querySelector(selector);
        }

        exports = module.exports = function (selector, el) {
            el = el || document;
            return one(selector, el);
        };

        exports.all = function (selector, el) {
            el = el || document;
            return el.querySelectorAll(selector);
        };

        exports.engine = function (obj) {
            if (!obj.one) throw new Error('.one callback required');
            if (!obj.all) throw new Error('.all callback required');
            one = obj.one;
            exports.all = obj.all;
            return exports;
        };

    });

    require.register("component~matches-selector@0.1.5", function (exports, module) {
        /**
         * Module dependencies.
         */

        var query = require('component~query@0.0.3');

        /**
         * Element prototype.
         */

        var proto = Element.prototype;

        /**
         * Vendor function.
         */

        var vendor = proto.matches ||
            proto.webkitMatchesSelector ||
            proto.mozMatchesSelector ||
            proto.msMatchesSelector ||
            proto.oMatchesSelector;

        /**
         * Expose `match()`.
         */

        module.exports = match;

        /**
         * Match `el` to `selector`.
         *
         * @param {Element} el
         * @param {String} selector
         * @return {Boolean}
         * @api public
         */

        function match(el, selector) {
            if (!el || el.nodeType !== 1) return false;
            if (vendor) return vendor.call(el, selector);
            var nodes = query.all(selector, el.parentNode);
            for (var i = 0; i < nodes.length; ++i) {
                if (nodes[i] == el) return true;
            }
            return false;
        }

    });

    require.register("component~closest@0.1.4", function (exports, module) {
        var matches = require('component~matches-selector@0.1.5')

        module.exports = function (element, selector, checkYoSelf, root) {
            element = checkYoSelf ? {
                parentNode: element
            } : element

            root = root || document

            // Make sure `element !== document` and `element != null`
            // otherwise we get an illegal invocation
            while ((element = element.parentNode) && element !== document) {
                if (matches(element, selector))
                    return element
                // After `matches` on the edge case that
                // the selector matches the root
                // (when the root is not the document)
                if (element === root)
                    return
            }
        }

    });

    require.register("component~delegate@0.2.3", function (exports, module) {
        /**
         * Module dependencies.
         */

        var closest = require('component~closest@0.1.4'),
            event = require('component~event@0.1.4');

        /**
         * Delegate event `type` to `selector`
         * and invoke `fn(e)`. A callback function
         * is returned which may be passed to `.unbind()`.
         *
         * @param {Element} el
         * @param {String} selector
         * @param {String} type
         * @param {Function} fn
         * @param {Boolean} capture
         * @return {Function}
         * @api public
         */

        exports.bind = function (el, selector, type, fn, capture) {
            return event.bind(el, type, function (e) {
                var target = e.target || e.srcElement;
                e.delegateTarget = closest(target, selector, true, el);
                if (e.delegateTarget) fn.call(el, e);
            }, capture);
        };

        /**
         * Unbind event `type`'s callback `fn`.
         *
         * @param {Element} el
         * @param {String} type
         * @param {Function} fn
         * @param {Boolean} capture
         * @api public
         */

        exports.unbind = function (el, type, fn, capture) {
            event.unbind(el, type, fn, capture);
        };

    });

    require.register("component~events@1.0.9", function (exports, module) {

        /**
         * Module dependencies.
         */

        var events = require('component~event@0.1.4');
        var delegate = require('component~delegate@0.2.3');

        /**
         * Expose `Events`.
         */

        module.exports = Events;

        /**
         * Initialize an `Events` with the given
         * `el` object which events will be bound to,
         * and the `obj` which will receive method calls.
         *
         * @param {Object} el
         * @param {Object} obj
         * @api public
         */

        function Events(el, obj) {
            if (!(this instanceof Events)) return new Events(el, obj);
            if (!el) throw new Error('element required');
            if (!obj) throw new Error('object required');
            this.el = el;
            this.obj = obj;
            this._events = {};
        }

        /**
         * Subscription helper.
         */

        Events.prototype.sub = function (event, method, cb) {
            this._events[event] = this._events[event] || {};
            this._events[event][method] = cb;
        };

        /**
         * Bind to `event` with optional `method` name.
         * When `method` is undefined it becomes `event`
         * with the "on" prefix.
         *
         * Examples:
         *
         *  Direct event handling:
         *
         *    events.bind('click') // implies "onclick"
         *    events.bind('click', 'remove')
         *    events.bind('click', 'sort', 'asc')
         *
         *  Delegated event handling:
         *
         *    events.bind('click li > a')
         *    events.bind('click li > a', 'remove')
         *    events.bind('click a.sort-ascending', 'sort', 'asc')
         *    events.bind('click a.sort-descending', 'sort', 'desc')
         *
         * @param {String} event
         * @param {String|function} [method]
         * @return {Function} callback
         * @api public
         */

        Events.prototype.bind = function (event, method) {
            var e = parse(event);
            var el = this.el;
            var obj = this.obj;
            var name = e.name;
            var method = method || 'on' + name;
            var args = [].slice.call(arguments, 2);

            // callback
            function cb() {
                var a = [].slice.call(arguments).concat(args);
                obj[method].apply(obj, a);
            }

            // bind
            if (e.selector) {
                cb = delegate.bind(el, e.selector, name, cb);
            } else {
                events.bind(el, name, cb);
            }

            // subscription for unbinding
            this.sub(name, method, cb);

            return cb;
        };

        /**
         * Unbind a single binding, all bindings for `event`,
         * or all bindings within the manager.
         *
         * Examples:
         *
         *  Unbind direct handlers:
         *
         *     events.unbind('click', 'remove')
         *     events.unbind('click')
         *     events.unbind()
         *
         * Unbind delegate handlers:
         *
         *     events.unbind('click', 'remove')
         *     events.unbind('click')
         *     events.unbind()
         *
         * @param {String|Function} [event]
         * @param {String|Function} [method]
         * @api public
         */

        Events.prototype.unbind = function (event, method) {
            if (0 == arguments.length) return this.unbindAll();
            if (1 == arguments.length) return this.unbindAllOf(event);

            // no bindings for this event
            var bindings = this._events[event];
            if (!bindings) return;

            // no bindings for this method
            var cb = bindings[method];
            if (!cb) return;

            events.unbind(this.el, event, cb);
        };

        /**
         * Unbind all events.
         *
         * @api private
         */

        Events.prototype.unbindAll = function () {
            for (var event in this._events) {
                this.unbindAllOf(event);
            }
        };

        /**
         * Unbind all events for `event`.
         *
         * @param {String} event
         * @api private
         */

        Events.prototype.unbindAllOf = function (event) {
            var bindings = this._events[event];
            if (!bindings) return;

            for (var method in bindings) {
                this.unbind(event, method);
            }
        };

        /**
         * Parse `event`.
         *
         * @param {String} event
         * @return {Object}
         * @api private
         */

        function parse(event) {
            var parts = event.split(/ +/);
            return {
                name: parts.shift(),
                selector: parts.join(' ')
            }
        }

    });

    require.register("switchery", function (exports, module) {
        /**
         * Switchery 0.8.1
         * http://abpetkov.github.io/switchery/
         *
         * Authored by Alexander Petkov
         * https://github.com/abpetkov
         *
         * Copyright 2013-2015, Alexander Petkov
         * License: The MIT License (MIT)
         * http://opensource.org/licenses/MIT
         *
         */

        /**
         * External dependencies.
         */

        var transitionize = require('abpetkov~transitionize@0.0.3'),
            fastclick = require('ftlabs~fastclick@v0.6.11'),
            classes = require('component~classes@1.2.1'),
            events = require('component~events@1.0.9');

        /**
         * Expose `Switchery`.
         */

        module.exports = Switchery;

        /**
         * Set Switchery default values.
         *
         * @api public
         */

        var defaults = {
            color: '#64bd63',
            secondaryColor: '#dfdfdf',
            jackColor: '#fff',
            jackSecondaryColor: null,
            className: 'switchery',
            disabled: false,
            disabledOpacity: 0.5,
            speed: '0.4s',
            size: 'default'
        };

        /**
         * Create Switchery object.
         *
         * @param {Object} element
         * @param {Object} options
         * @api public
         */

        function Switchery(element, options) {
            if (!(this instanceof Switchery)) return new Switchery(element, options);

            this.element = element;
            this.options = options || {};

            for (var i in defaults) {
                if (this.options[i] == null) {
                    this.options[i] = defaults[i];
                }
            }

            if (this.element != null && this.element.type == 'checkbox') this.init();
            if (this.isDisabled() === true) this.disable();
        }

        /**
         * Hide the target element.
         *
         * @api private
         */

        Switchery.prototype.hide = function () {
            this.element.style.display = 'none';
        };

        /**
         * Show custom switch after the target element.
         *
         * @api private
         */

        Switchery.prototype.show = function () {
            var switcher = this.create();
            this.insertAfter(this.element, switcher);
        };

        /**
         * Create custom switch.
         *
         * @returns {Object} this.switcher
         * @api private
         */

        Switchery.prototype.create = function () {
            this.switcher = document.createElement('span');
            this.jack = document.createElement('small');
            this.switcher.appendChild(this.jack);
            this.switcher.className = this.options.className;
            this.events = events(this.switcher, this);

            return this.switcher;
        };

        /**
         * Insert after element after another element.
         *
         * @param {Object} reference
         * @param {Object} target
         * @api private
         */

        Switchery.prototype.insertAfter = function (reference, target) {
            reference.parentNode.insertBefore(target, reference.nextSibling);
        };

        /**
         * Set switch jack proper position.
         *
         * @param {Boolean} clicked - we need this in order to uncheck the input when the switch is clicked
         * @api private
         */

        Switchery.prototype.setPosition = function (clicked) {
            var checked = this.isChecked(),
                switcher = this.switcher,
                jack = this.jack;

            if (clicked && checked) checked = false;
            else if (clicked && !checked) checked = true;

            if (checked === true) {
                this.element.checked = true;

                if (window.getComputedStyle) jack.style.left = parseInt(window.getComputedStyle(switcher).width) - parseInt(window.getComputedStyle(jack).width) + 'px';
                else jack.style.left = parseInt(switcher.currentStyle['width']) - parseInt(jack.currentStyle['width']) + 'px';

                if (this.options.color) this.colorize();
                this.setSpeed();
            } else {
                jack.style.left = 0;
                this.element.checked = false;
                this.switcher.style.boxShadow = 'inset 0 0 0 0 ' + this.options.secondaryColor;
                this.switcher.style.borderColor = this.options.secondaryColor;
                this.switcher.style.backgroundColor = (this.options.secondaryColor !== defaults.secondaryColor) ? this.options.secondaryColor : '#fff';
                this.jack.style.backgroundColor = (this.options.jackSecondaryColor !== this.options.jackColor) ? this.options.jackSecondaryColor : this.options.jackColor;
                this.setSpeed();
            }
        };

        /**
         * Set speed.
         *
         * @api private
         */

        Switchery.prototype.setSpeed = function () {
            var switcherProp = {},
                jackProp = {
                    'background-color': this.options.speed,
                    'left': this.options.speed.replace(/[a-z]/, '') / 2 + 's'
                };

            if (this.isChecked()) {
                switcherProp = {
                    'border': this.options.speed,
                    'box-shadow': this.options.speed,
                    'background-color': this.options.speed.replace(/[a-z]/, '') * 3 + 's'
                };
            } else {
                switcherProp = {
                    'border': this.options.speed,
                    'box-shadow': this.options.speed
                };
            }

            transitionize(this.switcher, switcherProp);
            transitionize(this.jack, jackProp);
        };

        /**
         * Set switch size.
         *
         * @api private
         */

        Switchery.prototype.setSize = function () {
            var small = 'switchery-small',
                normal = 'switchery-default',
                large = 'switchery-large';

            switch (this.options.size) {
                case 'small':
                    classes(this.switcher).add(small)
                    break;
                case 'large':
                    classes(this.switcher).add(large)
                    break;
                default:
                    classes(this.switcher).add(normal)
                    break;
            }
        };

        /**
         * Set switch color.
         *
         * @api private
         */

        Switchery.prototype.colorize = function () {
            var switcherHeight = this.switcher.offsetHeight / 2;

            this.switcher.style.backgroundColor = this.options.color;
            this.switcher.style.borderColor = this.options.color;
            this.switcher.style.boxShadow = 'inset 0 0 0 ' + switcherHeight + 'px ' + this.options.color;
            this.jack.style.backgroundColor = this.options.jackColor;
        };

        /**
         * Handle the onchange event.
         *
         * @param {Boolean} state
         * @api private
         */

        Switchery.prototype.handleOnchange = function (state) {
            if (document.dispatchEvent) {
                var event = document.createEvent('HTMLEvents');
                event.initEvent('change', true, true);
                this.element.dispatchEvent(event);
            } else {
                this.element.fireEvent('onchange');
            }
        };

        /**
         * Handle the native input element state change.
         * A `change` event must be fired in order to detect the change.
         *
         * @api private
         */

        Switchery.prototype.handleChange = function () {
            var self = this,
                el = this.element;

            if (el.addEventListener) {
                el.addEventListener('change', function () {
                    self.setPosition();
                });
            } else {
                el.attachEvent('onchange', function () {
                    self.setPosition();
                });
            }
        };

        /**
         * Handle the switch click event.
         *
         * @api private
         */

        Switchery.prototype.handleClick = function () {
            var switcher = this.switcher;

            fastclick(switcher);
            this.events.bind('click', 'bindClick');
        };

        /**
         * Attach all methods that need to happen on switcher click.
         *
         * @api private
         */

        Switchery.prototype.bindClick = function () {
            var parent = this.element.parentNode.tagName.toLowerCase(),
                labelParent = (parent === 'label') ? false : true;

            this.setPosition(labelParent);
            this.handleOnchange(this.element.checked);
        };

        /**
         * Mark an individual switch as already handled.
         *
         * @api private
         */

        Switchery.prototype.markAsSwitched = function () {
            this.element.setAttribute('data-switchery', true);
        };

        /**
         * Check if an individual switch is already handled.
         *
         * @api private
         */

        Switchery.prototype.markedAsSwitched = function () {
            return this.element.getAttribute('data-switchery');
        };

        /**
         * Initialize Switchery.
         *
         * @api private
         */

        Switchery.prototype.init = function () {
            this.hide();
            this.show();
            this.setSize();
            this.setPosition();
            this.markAsSwitched();
            this.handleChange();
            this.handleClick();
        };

        /**
         * See if input is checked.
         *
         * @returns {Boolean}
         * @api public
         */

        Switchery.prototype.isChecked = function () {
            return this.element.checked;
        };

        /**
         * See if switcher should be disabled.
         *
         * @returns {Boolean}
         * @api public
         */

        Switchery.prototype.isDisabled = function () {
            return this.options.disabled || this.element.disabled || this.element.readOnly;
        };

        /**
         * Destroy all event handlers attached to the switch.
         *
         * @api public
         */

        Switchery.prototype.destroy = function () {
            this.events.unbind();
        };

        /**
         * Enable disabled switch element.
         *
         * @api public
         */

        Switchery.prototype.enable = function () {
            if (!this.options.disabled) return;
            if (this.options.disabled) this.options.disabled = false;
            if (this.element.disabled) this.element.disabled = false;
            if (this.element.readOnly) this.element.readOnly = false;
            this.switcher.style.opacity = 1;
            this.events.bind('click', 'bindClick');
        };

        /**
         * Disable switch element.
         *
         * @api public
         */

        Switchery.prototype.disable = function () {
            if (this.options.disabled) return;
            if (!this.options.disabled) this.options.disabled = true;
            if (!this.element.disabled) this.element.disabled = true;
            if (!this.element.readOnly) this.element.readOnly = true;
            this.switcher.style.opacity = this.options.disabledOpacity;
            this.destroy();
        };

    });

    if (typeof exports == "object") {
        module.exports = require("switchery");
    } else if (typeof define == "function" && define.amd) {
        define("Switchery", [], function () {
            return require("switchery");
        });
    } else {
        (this || window)["Switchery"] = require("switchery");
    }
})();

(function (window) {
    'use strict';

    function define_VSPAjax() {
        var VSPAjax = function (AjaxOptions, Options) {
            this.ajax(AjaxOptions, Options);
            return this;
        }
        return VSPAjax;
    }

    function define_VSPAjaxQ() {
        var VSPAjaxQ = function () {
            this.init();
            return this;
        }
        return VSPAjaxQ;
    }

    function vsp_Ajax_Elem_Access($element,stats){
        if (stats == 'block') {
            $element.vspbutton('loading');
        } else {
            $element.vspbutton("reset");
        }
    }
    
    if (typeof (Library) === 'undefined') {
        window.VSPAjax = define_VSPAjax();
        window.VSPAjaxQ = define_VSPAjaxQ();
    }

    VSPAjax.prototype = {
        VERSION: '1.0',
        _Ajax: '',
        _default_ajax_options: {
            method: "GET",
            data: '',
            url: ajaxurl,
        },
        _AjaxOptions: {},
        _Options: {},

        _mergeArray: function () {
            var args = Array.prototype.slice.call(arguments);
            var argl = args.length;
            var arg;
            var retObj = {};
            var k = '';
            var argil = 0;
            var j = 0;
            var i = 0;
            var ct = 0;
            var toStr = Object.prototype.toString;
            var retArr = true;
            for (i = 0; i < argl; i++) {
                if (toStr.call(args[i]) !== '[object Array]') {
                    retArr = false
                    break;
                }
            }
            if (retArr) {
                retArr = [];
                for (i = 0; i < argl; i++) {
                    retArr = retArr.concat(args[i]);
                }
                return retArr;
            }
            for (i = 0, ct = 0; i < argl; i++) {
                arg = args[i];
                if (toStr.call(arg) === '[object Array]') {
                    for (j = 0, argil = arg.length; j < argil; j++) {
                        retObj[ct++] = arg[j];
                    }
                } else {
                    for (k in arg) {
                        if (arg.hasOwnProperty(k)) {
                            if (parseInt(k, 10) + '' === k) {
                                retObj[ct++] = arg[k];
                            } else {
                                retObj[k] = arg[k];
                            }
                        }
                    }
                }
            }
            return retObj;
        },

        _set_Options: function ($Options) {
            if ($Options !== undefined) {
                this._Options = $Options;
            }
        },

        _set_ajax_options: function ($Options) {
            if ($Options !== undefined) {
                this._AjaxOptions = this._mergeArray(this._default_ajax_options,$Options);
            }
        },

        _BodyTrigger: function ($status) {
            if (this._Options.trigger_code !== undefined) {
                jQuery("body").trigger(this._Options.trigger_code, [$status, Options.element, this._AjaxOptions]);
            }
        },

        _FuncTrigger: function ($status, $args) {
            if ($status == 'before' && this._Options.before === 'function') {
                this._Options.before($args);
            } else if ($status == 'after' && this._Options.after === 'function') {
                this._Options.after($args);
            } else if ($status == 'onSuccess' && this._AjaxOptions.OnSuccess === 'function') {
                this._AjaxOptions.OnSuccess($args);
            } else if ($status == 'onError' && this._AjaxOptions.onError === 'function') {
                this._AjaxOptions.onError($args);
            } else if ($status == 'OnAlways' && this._AjaxOptions.OnAlways === 'function') {
                this._AjaxOptions.OnAlways($args);
            } else if ($status == 'AjaxQ') {
                jQuery("body").trigger('vsp-ajaxq');
            }
        },

        _elementLock: function (stats) {
            if (this._Options.element_lock) {
                vsp_Ajax_Elem_Access(this._Options.element, stats);
            }
        },

        _handle_response: function (res) {
            if (res.data !== undefined) {
                if (res.data.msg !== undefined) {
                    this._Options.response_element.html(res.data.msg);
                    return false;
                }
            }
            return true;
        },

        _string_function_callback: function (callback) {
            try {
                window[callback]();
            } catch (err) {
                console.log(err);
            }
        },

        _array_function_callback: function (callback) {
            jQuery.each(callback, function (key, value) {
                this._string_function_callback(value);
            })
        },

        _handle_callback: function (res) {
            if (res.data !== undefined) {
                if (res.data.callback !== undefined) {
                    if (typeof res.data.callback == 'string') {
                        this._string_function_callback(res.data.callback);
                    } else if (typeof res.data.callback == 'object' || typeof res.data.callback == 'array') {

                        jQuery.each(res.data.callback, function (key, value) {
                            if (key == parseInt(key)) {
                                if (typeof value == 'string') {
                                    this._string_function_callback(value);
                                } else if (typeof value == 'object' || typeof value == 'array') {
                                    this._array_function_callback(value);
                                }
                            } else {
                                try {
                                    var CB = new Function(key, value);
                                    CB();
                                } catch (arr) {
                                    console.log(err);
                                }
                            }
                        })

                    }
                }
            }
        },

        ajax: function (AjaxOptions, Options) {
            this._set_ajax_options(AjaxOptions);
            this._set_Options(Options);
            
            if (AjaxOptions.ajax !== undefined) {
                this._set_ajax_options(AjaxOptions.ajax);
            }

            if (AjaxOptions.options !== undefined) {
                this._set_Options(AjaxOptions.options);
            }
            

            this._FuncTrigger("before");

            this._BodyTrigger("before");

            this._elementLock('block');

            if (this._Options.response_element === undefined) {
                this._Options.response_element = jQuery(".inline-ajax-response");
            }

            this._Ajax = jQuery.ajax(this._AjaxOptions);

            var $self = this;

            this._Ajax.done(function (res) {
                $self._elementLock('unblock');

                $self._FuncTrigger("onSuccess", res);

                $self._BodyTrigger("success");

                $self._handle_response(res);
            });

            this._Ajax.fail(function (res) {
                $self._elementLock('unblock');

                $self._handle_response(res.responseJSON.data);

                $self._FuncTrigger("onError", res);

                $self._BodyTrigger("failed");

            });

            this._Ajax.always(function (res) {
                $self._elementLock('unblock');

                $self._FuncTrigger("onAlways", res);

                $self._FuncTrigger("AjaxQ");

                $self._BodyTrigger("always");

            });

            this._FuncTrigger("after");

            this._BodyTrigger("after");
        }

    };

    VSPAjaxQ.prototype = {
        ajax_queue: [],

        is_ajax_ongoing: false,

        init: function () {
            var $self = this;
            jQuery("body").on("vsp-ajaxq", function () {
                if($self.ajax_queue[0] !== undefined){
                    var $elem = $self.ajax_queue[0].elem;
                    $elem.removeClass("vspajaxq-in-queue");
                    $self.ajax_queue.shift();
                    $self.kick_start_ajax();
                } else {
                    this.is_ajax_ongoing = false;
                }
            });
        },
        
        kick_start_ajax: function () {
            this.is_ajax_ongoing = true;

            if (this.ajax_queue[0] !== undefined) {
                new VSPAjax(this.ajax_queue[0].data)
            } else {
                this.is_ajax_ongoing = false;
            }
        },

        add: function ($ele, $data) {
            if (!$ele.hasClass("vspajaxq-in-queue")) {
                this.ajax_queue.push({
                    elem: $ele,
                    data: $data
                });
                $ele.addClass("vspajaxq-in-queue");
                vsp_Ajax_Elem_Access($ele,'block');
                if (this.is_ajax_ongoing === false) {
                    this.kick_start_ajax();
                }
            }
        },

    };
})(window);

