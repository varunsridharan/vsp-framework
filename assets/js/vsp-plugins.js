//@codekit-append ../vendors/blockui/jquery.blockui.js
//@codekit-append ../vendors/vspajax/jquery.vsp-ajax.js
//@codekit-append ../vendors/sweatalert/sweatalert.js
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
( function () {
    /*jshint eqeqeq:false curly:false latedef:false */
    "use strict";

    function setup($) {
        $.fn._fadeIn = $.fn.fadeIn;

        var noOp = $.noop || function () {
        };

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
            if ( title ) $m.append('<h1>' + title + '</h1>');
            if ( message ) $m.append('<h2>' + message + '</h2>');
            if ( timeout === undefined ) timeout = 3000;

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
            if ( this[0] === window ) {
                $.blockUI(opts);
                return this;
            }
            var fullOpts = $.extend({}, $.blockUI.defaults, opts || {});
            this.each(function () {
                var $el = $(this);
                if ( fullOpts.ignoreIfBlocked && $el.data('blockUI.isBlocked') )
                    return;
                $el.unblock({
                    fadeOut: 0
                });
            });

            return this.each(function () {
                if ( $.css(this, 'position') == 'static' ) {
                    this.style.position = 'relative';
                    $(this).data('blockUI.static', true);
                }
                this.style.zoom = 1; // force 'hasLayout' in ie
                install(this, opts);
            });
        };

        // plugin method for unblocking element content
        $.fn.unblock = function (opts) {
            if ( this[0] === window ) {
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
            var full = ( el == window );
            var msg = ( opts && opts.message !== undefined ? opts.message : undefined );
            opts = $.extend({}, $.blockUI.defaults, opts || {});

            if ( opts.ignoreIfBlocked && $(el).data('blockUI.isBlocked') )
                return;

            opts.overlayCSS = $.extend({}, $.blockUI.defaults.overlayCSS, opts.overlayCSS || {});
            css = $.extend({}, $.blockUI.defaults.css, opts.css || {});
            if ( opts.onOverlayClick )
                opts.overlayCSS.cursor = 'pointer';

            themedCSS = $.extend({}, $.blockUI.defaults.themedCSS, opts.themedCSS || {});
            msg = msg === undefined ? opts.message : msg;

            // remove the current block (if there is one)
            if ( full && pageBlock )
                remove(window, {
                    fadeOut: 0
                });

            // if an existing element is being used as the blocking content then we capture
            // its current place in the DOM (and current display style) so we can restore
            // it when we unblock
            if ( msg && typeof msg != 'string' && ( msg.parentNode || msg.jquery ) ) {
                var node = msg.jquery ? msg[0] : msg;
                var data = {};
                $(el).data('blockUI.history', data);
                data.el = node;
                data.parent = node.parentNode;
                data.display = node.style.display;
                data.position = node.style.position;
                if ( data.parent )
                    data.parent.removeChild(node);
            }

            $(el).data('blockUI.onUnblock', opts.onUnblock);
            var z = opts.baseZ;

            // blockUI uses 3 layers for blocking, for simplicity they are all used on every platform;
            // layer1 is the iframe layer which is used to supress bleed through of underlying content
            // layer2 is the overlay layer which has opacity and a wait cursor (by default)
            // layer3 is the message content that is displayed while blocking
            var lyr1, lyr2, lyr3, s;
            if ( msie || opts.forceIframe )
                lyr1 = $('<iframe class="blockUI" style="z-index:' + ( z++ ) + ';display:none;border:none;margin:0;padding:0;position:absolute;width:100%;height:100%;top:0;left:0" src="' + opts.iframeSrc + '"></iframe>');
            else
                lyr1 = $('<div class="blockUI" style="display:none"></div>');

            if ( opts.theme )
                lyr2 = $('<div class="blockUI blockOverlay ui-widget-overlay" style="z-index:' + ( z++ ) + ';display:none"></div>');
            else
                lyr2 = $('<div class="blockUI blockOverlay" style="z-index:' + ( z++ ) + ';display:none;border:none;margin:0;padding:0;width:100%;height:100%;top:0;left:0"></div>');

            if ( opts.theme && full ) {
                s = '<div class="blockUI ' + opts.blockMsgClass + ' blockPage ui-dialog ui-widget ui-corner-all" style="z-index:' + ( z + 10 ) + ';display:none;position:fixed">';
                if ( opts.title ) {
                    s += '<div class="ui-widget-header ui-dialog-titlebar ui-corner-all blockTitle">' + ( opts.title || '&nbsp;' ) + '</div>';
                }
                s += '<div class="ui-widget-content ui-dialog-content"></div>';
                s += '</div>';
            } else if ( opts.theme ) {
                s = '<div class="blockUI ' + opts.blockMsgClass + ' blockElement ui-dialog ui-widget ui-corner-all" style="z-index:' + ( z + 10 ) + ';display:none;position:absolute">';
                if ( opts.title ) {
                    s += '<div class="ui-widget-header ui-dialog-titlebar ui-corner-all blockTitle">' + ( opts.title || '&nbsp;' ) + '</div>';
                }
                s += '<div class="ui-widget-content ui-dialog-content"></div>';
                s += '</div>';
            } else if ( full ) {
                s = '<div class="blockUI ' + opts.blockMsgClass + ' blockPage" style="z-index:' + ( z + 10 ) + ';display:none;position:fixed"></div>';
            } else {
                s = '<div class="blockUI ' + opts.blockMsgClass + ' blockElement" style="z-index:' + ( z + 10 ) + ';display:none;position:absolute"></div>';
            }
            lyr3 = $(s);

            // if we have a message, style it
            if ( msg ) {
                if ( opts.theme ) {
                    lyr3.css(themedCSS);
                    lyr3.addClass('ui-widget-content');
                } else
                    lyr3.css(css);
            }

            // style the overlay
            if ( !opts.theme /*&& (!opts.applyPlatformOpacityRules)*/ )
                lyr2.css(opts.overlayCSS);
            lyr2.css('position', full ? 'fixed' : 'absolute');

            // make iframe layer transparent in IE
            if ( msie || opts.forceIframe )
                lyr1.css('opacity', 0.0);

            //$([lyr1[0],lyr2[0],lyr3[0]]).appendTo(full ? 'body' : el);
            var layers = [lyr1, lyr2, lyr3],
                $par = full ? $('body') : $(el);
            $.each(layers, function () {
                this.appendTo($par);
            });

            if ( opts.theme && opts.draggable && $.fn.draggable ) {
                lyr3.draggable({
                    handle: '.ui-dialog-titlebar',
                    cancel: 'li'
                });
            }

            // ie7 must use absolute positioning in quirks mode and to account for activex issues (when scrolling)
            var expr = setExpr && ( !$.support.boxModel || $('object,embed', full ? null : el).length > 0 );
            if ( ie6 || expr ) {
                // give body 100% height
                if ( full && opts.allowBodyStretch && $.support.boxModel )
                    $('html,body').css('height', '100%');

                // fix ie6 issue when blocked element has a border width
                if ( ( ie6 || !$.support.boxModel ) && !full ) {
                    var t = sz(el, 'borderTopWidth'),
                        l = sz(el, 'borderLeftWidth');
                    var fixT = t ? '(0 - ' + t + ')' : 0;
                    var fixL = l ? '(0 - ' + l + ')' : 0;
                }

                // simulate fixed position
                $.each(layers, function (i, o) {
                    var s = o[0].style;
                    s.position = 'absolute';
                    if ( i < 2 ) {
                        if ( full )
                            s.setExpression('height', 'Math.max(document.body.scrollHeight, document.body.offsetHeight) - (jQuery.support.boxModel?0:' + opts.quirksmodeOffsetHack + ') + "px"');
                        else
                            s.setExpression('height', 'this.parentNode.offsetHeight + "px"');
                        if ( full )
                            s.setExpression('width', 'jQuery.support.boxModel && document.documentElement.clientWidth || document.body.clientWidth + "px"');
                        else
                            s.setExpression('width', 'this.parentNode.offsetWidth + "px"');
                        if ( fixL ) s.setExpression('left', fixL);
                        if ( fixT ) s.setExpression('top', fixT);
                    } else if ( opts.centerY ) {
                        if ( full ) s.setExpression('top', '(document.documentElement.clientHeight || document.body.clientHeight) / 2 - (this.offsetHeight / 2) + (blah = document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop) + "px"');
                        s.marginTop = 0;
                    } else if ( !opts.centerY && full ) {
                        var top = ( opts.css && opts.css.top ) ? parseInt(opts.css.top, 10) : 0;
                        var expression = '((document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop) + ' + top + ') + "px"';
                        s.setExpression('top', expression);
                    }
                });
            }

            // show the message
            if ( msg ) {
                if ( opts.theme )
                    lyr3.find('.ui-widget-content').append(msg);
                else
                    lyr3.append(msg);
                if ( msg.jquery || msg.nodeType )
                    $(msg).show();
            }

            if ( ( msie || opts.forceIframe ) && opts.showOverlay )
                lyr1.show(); // opacity is zero
            if ( opts.fadeIn ) {
                var cb = opts.onBlock ? opts.onBlock : noOp;
                var cb1 = ( opts.showOverlay && !msg ) ? cb : noOp;
                var cb2 = msg ? cb : noOp;
                if ( opts.showOverlay )
                    lyr2._fadeIn(opts.fadeIn, cb1);
                if ( msg )
                    lyr3._fadeIn(opts.fadeIn, cb2);
            } else {
                if ( opts.showOverlay )
                    lyr2.show();
                if ( msg )
                    lyr3.show();
                if ( opts.onBlock )
                    opts.onBlock.bind(lyr3)();
            }

            // bind key and mouse events
            bind(1, el, opts);

            if ( full ) {
                pageBlock = lyr3[0];
                pageBlockEls = $(opts.focusableElements, pageBlock);
                if ( opts.focusInput )
                    setTimeout(focus, 20);
            } else
                center(lyr3[0], opts.centerX, opts.centerY);

            if ( opts.timeout ) {
                // auto-unblock
                var to = setTimeout(function () {
                    if ( full )
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
            var full = ( el == window );
            var $el = $(el);
            var data = $el.data('blockUI.history');
            var to = $el.data('blockUI.timeout');
            if ( to ) {
                clearTimeout(to);
                $el.removeData('blockUI.timeout');
            }
            opts = $.extend({}, $.blockUI.defaults, opts || {});
            bind(0, el, opts); // unbind events

            if ( opts.onUnblock === null ) {
                opts.onUnblock = $el.data('blockUI.onUnblock');
                $el.removeData('blockUI.onUnblock');
            }

            var els;
            if ( full ) // crazy selector to handle odd field errors in ie6/7
                els = $('body').children().filter('.blockUI').add('body > .blockUI');
            else
                els = $el.find('>.blockUI');

            // fix cursor issue
            if ( opts.cursorReset ) {
                if ( els.length > 1 )
                    els[1].style.cursor = opts.cursorReset;
                if ( els.length > 2 )
                    els[2].style.cursor = opts.cursorReset;
            }

            if ( full )
                pageBlock = pageBlockEls = null;

            if ( opts.fadeOut ) {
                count = els.length;
                els.stop().fadeOut(opts.fadeOut, function () {
                    if ( --count === 0 )
                        reset(els, data, opts, el);
                });
            } else
                reset(els, data, opts, el);
        }

        // move blocking element back into the DOM where it started
        function reset(els, data, opts, el) {
            var $el = $(el);
            if ( $el.data('blockUI.isBlocked') )
                return;

            els.each(function (i, o) {
                // remove via DOM calls so we don't lose event handlers
                if ( this.parentNode )
                    this.parentNode.removeChild(this);
            });

            if ( data && data.el ) {
                data.el.style.display = data.display;
                data.el.style.position = data.position;
                data.el.style.cursor = 'default'; // #59
                if ( data.parent )
                    data.parent.appendChild(data.el);
                $el.removeData('blockUI.history');
            }

            if ( $el.data('blockUI.static') ) {
                $el.css('position', 'static'); // #22
            }

            if ( typeof opts.onUnblock == 'function' )
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
            if ( !b && ( full && !pageBlock || !full && !$el.data('blockUI.isBlocked') ) )
                return;

            $el.data('blockUI.isBlocked', b);

            // don't bind events when overlay is not in use or if bindEvents is false
            if ( !full || !opts.bindEvents || ( b && !opts.showOverlay ) )
                return;

            // bind anchors and inputs for mouse and key events
            var events = 'mousedown mouseup keydown keypress keyup touchstart touchend touchmove';
            if ( b )
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
            if ( e.type === 'keydown' && e.keyCode && e.keyCode == 9 ) {
                if ( pageBlock && e.data.constrainTabKey ) {
                    var els = pageBlockEls;
                    var fwd = !e.shiftKey && e.target === els[els.length - 1];
                    var back = e.shiftKey && e.target === els[0];
                    if ( fwd || back ) {
                        setTimeout(function () {
                            focus(back);
                        }, 10);
                        return false;
                    }
                }
            }
            var opts = e.data;
            var target = $(e.target);
            if ( target.hasClass('blockOverlay') && opts.onOverlayClick )
                opts.onOverlayClick(e);

            // allow events within the message content
            if ( target.parents('div.' + opts.blockMsgClass).length > 0 )
                return true;

            // allow events for content that is not being blocked
            return target.parents().children().filter('div.blockUI').length === 0;
        }

        function focus(back) {
            if ( !pageBlockEls )
                return;
            var e = pageBlockEls[back === true ? pageBlockEls.length - 1 : 0];
            if ( e )
                e.focus();
        }

        function center(el, x, y) {
            var p = el.parentNode,
                s = el.style;
            var l = ( ( p.offsetWidth - el.offsetWidth ) / 2 ) - sz(p, 'borderLeftWidth');
            var t = ( ( p.offsetHeight - el.offsetHeight ) / 2 ) - sz(p, 'borderTopWidth');
            if ( x ) s.left = l > 0 ? ( l + 'px' ) : '0';
            if ( y ) s.top = t > 0 ? ( t + 'px' ) : '0';
        }

        function sz(el, p) {
            return parseInt($.css(el, p), 10) || 0;
        }

    }


    /*global define:true */
    if ( typeof define === 'function' && define.amd && define.amd.jQuery ) {
        define(['jquery'], setup);
    } else {
        setup(jQuery);
    }

} )();

( function (window) {
    'use strict';

    function define_VSPAjax() {
        var VSPAjax = function (AjaxOptions, Options) {
            this.ajax(AjaxOptions, Options);
        };
        return VSPAjax;
    }

    function define_VSPAjaxQ() {
        var VSPAjaxQ = function () {
            this.init();
            return this;
        }
        return VSPAjaxQ;
    }

    function vsp_Ajax_Elem_Access($element, stats) {
        if ( stats == 'block' ) {
            $element.vspbutton('loading');
        } else {
            $element.vspbutton("reset");
        }
    }

    if ( typeof ( Library ) === 'undefined' ) {
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
            for ( i = 0; i < argl; i++ ) {
                if ( toStr.call(args[i]) !== '[object Array]' ) {
                    retArr = false
                    break;
                }
            }
            if ( retArr ) {
                retArr = [];
                for ( i = 0; i < argl; i++ ) {
                    retArr = retArr.concat(args[i]);
                }
                return retArr;
            }
            for ( i = 0, ct = 0; i < argl; i++ ) {
                arg = args[i];
                if ( toStr.call(arg) === '[object Array]' ) {
                    for ( j = 0, argil = arg.length; j < argil; j++ ) {
                        retObj[ct++] = arg[j];
                    }
                } else {
                    for ( k in arg ) {
                        if ( arg.hasOwnProperty(k) ) {
                            if ( parseInt(k, 10) + '' === k ) {
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
            if ( $Options !== undefined ) {
                this._Options = $Options;
            }
        },

        _set_ajax_options: function ($Options) {
            if ( $Options !== undefined ) {
                this._AjaxOptions = this._mergeArray(this._default_ajax_options, $Options);
            }
        },

        _BodyTrigger: function ($status) {
            if ( this._Options.trigger_code !== undefined ) {
                jQuery("body").trigger(this._Options.trigger_code, [$status, Options.element, this._AjaxOptions]);
            }
        },

        _FuncTrigger: function ($status, $args) {
            if ( $status == 'before' && this._Options.before === 'function' ) {
                this._Options.before($args);
            } else if ( $status == 'after' && this._Options.after === 'function' ) {
                this._Options.after($args);
            } else if ( $status == 'onSuccess' && this._AjaxOptions.OnSuccess === 'function' ) {
                this._AjaxOptions.OnSuccess($args);
            } else if ( $status == 'onError' && this._AjaxOptions.onError === 'function' ) {
                this._AjaxOptions.onError($args);
            } else if ( $status == 'OnAlways' && this._AjaxOptions.OnAlways === 'function' ) {
                this._AjaxOptions.OnAlways($args);
            } else if ( $status == 'AjaxQ' ) {
                jQuery("body").trigger('vsp-ajaxq');
            }
        },

        _elementLock: function (stats) {
            if ( this._Options.element_lock ) {
                vsp_Ajax_Elem_Access(this._Options.element, stats);
            }
        },

        _handle_response: function (res) {
            if ( res.data !== undefined ) {
                if ( res.data.msg !== undefined ) {
                    this._Options.response_element.html(res.data.msg);
                    return false;
                }
            }
            return true;
        },

        _string_function_callback: function (callback) {
            try {
                window[callback]();
            } catch ( err ) {
                console.log(err);
            }
        },

        _array_function_callback: function (callback) {
            jQuery.each(callback, function (key, value) {
                this._string_function_callback(value);
            })
        },

        _handle_callback: function (res) {
            var $this = this;
            if ( res.data !== undefined ) {
                if ( res.data.callback !== undefined ) {
                    if ( typeof res.data.callback == 'string' ) {
                        $this._string_function_callback(res.data.callback);
                    } else if ( typeof res.data.callback == 'object' || typeof res.data.callback == 'array' ) {

                        jQuery.each(res.data.callback, function (key, value) {
                            if ( key == parseInt(key) ) {
                                if ( typeof value == 'string' ) {
                                    $this._string_function_callback(value);
                                } else if ( typeof value == 'object' || typeof value == 'array' ) {
                                    $this._array_function_callback(value);
                                }
                            } else {
                                try {
                                    var CB = new Function(key, value);
                                    CB();
                                } catch ( arr ) {
                                    console.log(arr);
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

            if ( AjaxOptions.ajax !== undefined ) {
                this._set_ajax_options(AjaxOptions.ajax);
            }

            if ( AjaxOptions.options !== undefined ) {
                this._set_Options(AjaxOptions.options);
            }


            this._FuncTrigger("before");

            this._BodyTrigger("before");

            this._elementLock('block');

            if ( this._Options.response_element === undefined ) {
                this._Options.response_element = jQuery(".inline-ajax-response");
            }

            this._Ajax = jQuery.ajax(this._AjaxOptions);

            var $self = this;

            this._Ajax.done(function (res) {
                $self._elementLock('unblock');

                $self._FuncTrigger("onSuccess", res);

                $self._BodyTrigger("success");

                $self._handle_response(res);

                $self._handle_callback(res);


            });

            this._Ajax.fail(function (res) {
                $self._elementLock('unblock');

                $self._handle_response(res.responseJSON.data);

                $self._handle_callback(res);

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
                if ( $self.ajax_queue[0] !== undefined ) {
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

            if ( this.ajax_queue[0] !== undefined ) {
                new VSPAjax(this.ajax_queue[0].data)
            } else {
                this.is_ajax_ongoing = false;
            }
        },

        add: function ($ele, $data) {
            if ( !$ele.hasClass("vspajaxq-in-queue") ) {
                this.ajax_queue.push({
                    elem: $ele,
                    data: $data
                });
                $ele.addClass("vspajaxq-in-queue");
                vsp_Ajax_Elem_Access($ele, 'block');
                if ( this.is_ajax_ongoing === false ) {
                    this.kick_start_ajax();
                }
            }
        },

    };
} )(window);

/*!
* sweetalert2 v7.18.0
* Released under the MIT License.
*/
!function (t, e) {
    "object" == typeof exports && "object" == typeof module ? module.exports = e() : "function" == typeof define && define.amd ? define([], e) : "object" == typeof exports ? exports.swal = e() : t.swal = e()
}(this, function () {
    return function (t) {
        function e(o) {
            if ( n[o] ) return n[o].exports;
            var r = n[o] = {i: o, l: !1, exports: {}};
            return t[o].call(r.exports, r, r.exports, e), r.l = !0, r.exports
        }

        var n = {};
        return e.m = t, e.c = n, e.d = function (t, n, o) {
            e.o(t, n) || Object.defineProperty(t, n, {configurable: !1, enumerable: !0, get: o})
        }, e.n = function (t) {
            var n = t && t.__esModule ? function () {
                return t.default
            } : function () {
                return t
            };
            return e.d(n, "a", n), n
        }, e.o = function (t, e) {
            return Object.prototype.hasOwnProperty.call(t, e)
        }, e.p = "", e(e.s = 8)
    }([function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", {value: !0});
        var o = "swal-button";
        e.CLASS_NAMES = {
            MODAL: "swal-modal",
            OVERLAY: "swal-overlay",
            SHOW_MODAL: "swal-overlay--show-modal",
            MODAL_TITLE: "swal-title",
            MODAL_TEXT: "swal-text",
            ICON: "swal-icon",
            ICON_CUSTOM: "swal-icon--custom",
            CONTENT: "swal-content",
            FOOTER: "swal-footer",
            BUTTON_CONTAINER: "swal-button-container",
            BUTTON: o,
            CONFIRM_BUTTON: o + "--confirm",
            CANCEL_BUTTON: o + "--cancel",
            DANGER_BUTTON: o + "--danger",
            BUTTON_LOADING: o + "--loading",
            BUTTON_LOADER: o + "__loader"
        }, e.default = e.CLASS_NAMES
    }, function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", {value: !0}), e.getNode = function (t) {
            var e = "." + t;
            return document.querySelector(e)
        }, e.stringToNode = function (t) {
            var e = document.createElement("div");
            return e.innerHTML = t.trim(), e.firstChild
        }, e.insertAfter = function (t, e) {
            var n = e.nextSibling;
            e.parentNode.insertBefore(t, n)
        }, e.removeNode = function (t) {
            t.parentElement.removeChild(t)
        }, e.throwErr = function (t) {
            throw t = t.replace(/ +(?= )/g, ""), "SweetAlert: " + ( t = t.trim() )
        }, e.isPlainObject = function (t) {
            if ( "[object Object]" !== Object.prototype.toString.call(t) ) return !1;
            var e = Object.getPrototypeOf(t);
            return null === e || e === Object.prototype
        }, e.ordinalSuffixOf = function (t) {
            var e = t % 10, n = t % 100;
            return 1 === e && 11 !== n ? t + "st" : 2 === e && 12 !== n ? t + "nd" : 3 === e && 13 !== n ? t + "rd" : t + "th"
        }
    }, function (t, e, n) {
        "use strict";

        function o(t) {
            for ( var n in t ) e.hasOwnProperty(n) || ( e[n] = t[n] )
        }

        Object.defineProperty(e, "__esModule", {value: !0}), o(n(25));
        var r = n(26);
        e.overlayMarkup = r.default, o(n(27)), o(n(28)), o(n(29));
        var i = n(0), a = i.default.MODAL_TITLE, s = i.default.MODAL_TEXT, c = i.default.ICON, l = i.default.FOOTER;
        e.iconMarkup = '\n  <div class="' + c + '"></div>', e.titleMarkup = '\n  <div class="' + a + '"></div>\n', e.textMarkup = '\n  <div class="' + s + '"></div>', e.footerMarkup = '\n  <div class="' + l + '"></div>\n'
    }, function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", {value: !0});
        var o = n(1);
        e.CONFIRM_KEY = "confirm", e.CANCEL_KEY = "cancel";
        var r = {visible: !0, text: null, value: null, className: "", closeModal: !0},
            i = Object.assign({}, r, {visible: !1, text: "Cancel", value: null}),
            a = Object.assign({}, r, {text: "OK", value: !0});
        e.defaultButtonList = {cancel: i, confirm: a};
        var s = function (t) {
            switch ( t ) {
                case e.CONFIRM_KEY:
                    return a;
                case e.CANCEL_KEY:
                    return i;
                default:
                    var n = t.charAt(0).toUpperCase() + t.slice(1);
                    return Object.assign({}, r, {text: n, value: t})
            }
        }, c = function (t, e) {
            var n = s(t);
            return !0 === e ? Object.assign({}, n, {visible: !0}) : "string" == typeof e ? Object.assign({}, n, {
                visible: !0,
                text: e
            }) : o.isPlainObject(e) ? Object.assign({visible: !0}, n, e) : Object.assign({}, n, {visible: !1})
        }, l = function (t) {
            for ( var e = {}, n = 0, o = Object.keys(t); n < o.length; n++ ) {
                var r = o[n], a = t[r], s = c(r, a);
                e[r] = s
            }
            return e.cancel || ( e.cancel = i ), e
        }, u = function (t) {
            var n = {};
            switch ( t.length ) {
                case 1:
                    n[e.CANCEL_KEY] = Object.assign({}, i, {visible: !1});
                    break;
                case 2:
                    n[e.CANCEL_KEY] = c(e.CANCEL_KEY, t[0]), n[e.CONFIRM_KEY] = c(e.CONFIRM_KEY, t[1]);
                    break;
                default:
                    o.throwErr("Invalid number of 'buttons' in array (" + t.length + ").\n      If you want more than 2 buttons, you need to use an object!")
            }
            return n
        };
        e.getButtonListOpts = function (t) {
            var n = e.defaultButtonList;
            return "string" == typeof t ? n[e.CONFIRM_KEY] = c(e.CONFIRM_KEY, t) : Array.isArray(t) ? n = u(t) : o.isPlainObject(t) ? n = l(t) : !0 === t ? n = u([!0, !0]) : !1 === t ? n = u([!1, !1]) : void 0 === t && ( n = e.defaultButtonList ), n
        }
    }, function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", {value: !0});
        var o = n(1), r = n(2), i = n(0), a = i.default.MODAL, s = i.default.OVERLAY, c = n(30), l = n(31), u = n(32),
            f = n(33);
        e.injectElIntoModal = function (t) {
            var e = o.getNode(a), n = o.stringToNode(t);
            return e.appendChild(n), n
        };
        var d = function (t) {
            t.className = a, t.textContent = ""
        }, p = function (t, e) {
            d(t);
            var n = e.className;
            n && t.classList.add(n)
        };
        e.initModalContent = function (t) {
            var e = o.getNode(a);
            p(e, t), c.default(t.icon), l.initTitle(t.title), l.initText(t.text), f.default(t.content), u.default(t.buttons, t.dangerMode)
        };
        var m = function () {
            var t = o.getNode(s), e = o.stringToNode(r.modalMarkup);
            t.appendChild(e)
        };
        e.default = m
    }, function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", {value: !0});
        var o = n(3), r = {isOpen: !1, promise: null, actions: {}, timer: null}, i = Object.assign({}, r);
        e.resetState = function () {
            i = Object.assign({}, r)
        }, e.setActionValue = function (t) {
            if ( "string" == typeof t ) return a(o.CONFIRM_KEY, t);
            for ( var e in t ) a(e, t[e])
        };
        var a = function (t, e) {
            i.actions[t] || ( i.actions[t] = {} ), Object.assign(i.actions[t], {value: e})
        };
        e.setActionOptionsFor = function (t, e) {
            var n = ( void 0 === e ? {} : e ).closeModal, o = void 0 === n || n;
            Object.assign(i.actions[t], {closeModal: o})
        }, e.default = i
    }, function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", {value: !0});
        var o = n(1), r = n(3), i = n(0), a = i.default.OVERLAY, s = i.default.SHOW_MODAL, c = i.default.BUTTON,
            l = i.default.BUTTON_LOADING, u = n(5);
        e.openModal = function () {
            o.getNode(a).classList.add(s), u.default.isOpen = !0
        };
        var f = function () {
            o.getNode(a).classList.remove(s), u.default.isOpen = !1
        };
        e.onAction = function (t) {
            void 0 === t && ( t = r.CANCEL_KEY );
            var e = u.default.actions[t], n = e.value;
            if ( !1 === e.closeModal ) {
                var i = c + "--" + t;
                o.getNode(i).classList.add(l)
            } else f();
            u.default.promise.resolve(n)
        }, e.getState = function () {
            var t = Object.assign({}, u.default);
            return delete t.promise, delete t.timer, t
        }, e.stopLoading = function () {
            for ( var t = document.querySelectorAll("." + c), e = 0; e < t.length; e++ ) {
                t[e].classList.remove(l)
            }
        }
    }, function (t, e) {
        var n;
        n = function () {
            return this
        }();
        try {
            n = n || Function("return this")() || ( 0, eval )("this")
        } catch ( t ) {
            "object" == typeof window && ( n = window )
        }
        t.exports = n
    }, function (t, e, n) {
        ( function (e) {
            t.exports = e.sweetAlert = n(9)
        } ).call(e, n(7))
    }, function (t, e, n) {
        ( function (e) {
            t.exports = e.swal = n(10)
        } ).call(e, n(7))
    }, function (t, e, n) {
        "undefined" != typeof window && n(11), n(16);
        var o = n(23).default;
        t.exports = o
    }, function (t, e, n) {
        var o = n(12);
        "string" == typeof o && ( o = [[t.i, o, ""]] );
        var r = {insertAt: "top"};
        r.transform = void 0;
        n(14)(o, r);
        o.locals && ( t.exports = o.locals )
    }, function (t, e, n) {
        e = t.exports = n(13)(void 0), e.push([t.i, '.swal-icon--error{border-color:#f27474;-webkit-animation:animateErrorIcon .5s;animation:animateErrorIcon .5s}.swal-icon--error__x-mark{position:relative;display:block;-webkit-animation:animateXMark .5s;animation:animateXMark .5s}.swal-icon--error__line{position:absolute;height:5px;width:47px;background-color:#f27474;display:block;top:37px;border-radius:2px}.swal-icon--error__line--left{-webkit-transform:rotate(45deg);transform:rotate(45deg);left:17px}.swal-icon--error__line--right{-webkit-transform:rotate(-45deg);transform:rotate(-45deg);right:16px}@-webkit-keyframes animateErrorIcon{0%{-webkit-transform:rotateX(100deg);transform:rotateX(100deg);opacity:0}to{-webkit-transform:rotateX(0deg);transform:rotateX(0deg);opacity:1}}@keyframes animateErrorIcon{0%{-webkit-transform:rotateX(100deg);transform:rotateX(100deg);opacity:0}to{-webkit-transform:rotateX(0deg);transform:rotateX(0deg);opacity:1}}@-webkit-keyframes animateXMark{0%{-webkit-transform:scale(.4);transform:scale(.4);margin-top:26px;opacity:0}50%{-webkit-transform:scale(.4);transform:scale(.4);margin-top:26px;opacity:0}80%{-webkit-transform:scale(1.15);transform:scale(1.15);margin-top:-6px}to{-webkit-transform:scale(1);transform:scale(1);margin-top:0;opacity:1}}@keyframes animateXMark{0%{-webkit-transform:scale(.4);transform:scale(.4);margin-top:26px;opacity:0}50%{-webkit-transform:scale(.4);transform:scale(.4);margin-top:26px;opacity:0}80%{-webkit-transform:scale(1.15);transform:scale(1.15);margin-top:-6px}to{-webkit-transform:scale(1);transform:scale(1);margin-top:0;opacity:1}}.swal-icon--warning{border-color:#f8bb86;-webkit-animation:pulseWarning .75s infinite alternate;animation:pulseWarning .75s infinite alternate}.swal-icon--warning__body{width:5px;height:47px;top:10px;border-radius:2px;margin-left:-2px}.swal-icon--warning__body,.swal-icon--warning__dot{position:absolute;left:50%;background-color:#f8bb86}.swal-icon--warning__dot{width:7px;height:7px;border-radius:50%;margin-left:-4px;bottom:-11px}@-webkit-keyframes pulseWarning{0%{border-color:#f8d486}to{border-color:#f8bb86}}@keyframes pulseWarning{0%{border-color:#f8d486}to{border-color:#f8bb86}}.swal-icon--success{border-color:#a5dc86}.swal-icon--success:after,.swal-icon--success:before{content:"";border-radius:50%;position:absolute;width:60px;height:120px;background:#fff;-webkit-transform:rotate(45deg);transform:rotate(45deg)}.swal-icon--success:before{border-radius:120px 0 0 120px;top:-7px;left:-33px;-webkit-transform:rotate(-45deg);transform:rotate(-45deg);-webkit-transform-origin:60px 60px;transform-origin:60px 60px}.swal-icon--success:after{border-radius:0 120px 120px 0;top:-11px;left:30px;-webkit-transform:rotate(-45deg);transform:rotate(-45deg);-webkit-transform-origin:0 60px;transform-origin:0 60px;-webkit-animation:rotatePlaceholder 4.25s ease-in;animation:rotatePlaceholder 4.25s ease-in}.swal-icon--success__ring{width:80px;height:80px;border:4px solid hsla(98,55%,69%,.2);border-radius:50%;box-sizing:content-box;position:absolute;left:-4px;top:-4px;z-index:2}.swal-icon--success__hide-corners{width:5px;height:90px;background-color:#fff;padding:1px;position:absolute;left:28px;top:8px;z-index:1;-webkit-transform:rotate(-45deg);transform:rotate(-45deg)}.swal-icon--success__line{height:5px;background-color:#a5dc86;display:block;border-radius:2px;position:absolute;z-index:2}.swal-icon--success__line--tip{width:25px;left:14px;top:46px;-webkit-transform:rotate(45deg);transform:rotate(45deg);-webkit-animation:animateSuccessTip .75s;animation:animateSuccessTip .75s}.swal-icon--success__line--long{width:47px;right:8px;top:38px;-webkit-transform:rotate(-45deg);transform:rotate(-45deg);-webkit-animation:animateSuccessLong .75s;animation:animateSuccessLong .75s}@-webkit-keyframes rotatePlaceholder{0%{-webkit-transform:rotate(-45deg);transform:rotate(-45deg)}5%{-webkit-transform:rotate(-45deg);transform:rotate(-45deg)}12%{-webkit-transform:rotate(-405deg);transform:rotate(-405deg)}to{-webkit-transform:rotate(-405deg);transform:rotate(-405deg)}}@keyframes rotatePlaceholder{0%{-webkit-transform:rotate(-45deg);transform:rotate(-45deg)}5%{-webkit-transform:rotate(-45deg);transform:rotate(-45deg)}12%{-webkit-transform:rotate(-405deg);transform:rotate(-405deg)}to{-webkit-transform:rotate(-405deg);transform:rotate(-405deg)}}@-webkit-keyframes animateSuccessTip{0%{width:0;left:1px;top:19px}54%{width:0;left:1px;top:19px}70%{width:50px;left:-8px;top:37px}84%{width:17px;left:21px;top:48px}to{width:25px;left:14px;top:45px}}@keyframes animateSuccessTip{0%{width:0;left:1px;top:19px}54%{width:0;left:1px;top:19px}70%{width:50px;left:-8px;top:37px}84%{width:17px;left:21px;top:48px}to{width:25px;left:14px;top:45px}}@-webkit-keyframes animateSuccessLong{0%{width:0;right:46px;top:54px}65%{width:0;right:46px;top:54px}84%{width:55px;right:0;top:35px}to{width:47px;right:8px;top:38px}}@keyframes animateSuccessLong{0%{width:0;right:46px;top:54px}65%{width:0;right:46px;top:54px}84%{width:55px;right:0;top:35px}to{width:47px;right:8px;top:38px}}.swal-icon--info{border-color:#c9dae1}.swal-icon--info:before{width:5px;height:29px;bottom:17px;border-radius:2px;margin-left:-2px}.swal-icon--info:after,.swal-icon--info:before{content:"";position:absolute;left:50%;background-color:#c9dae1}.swal-icon--info:after{width:7px;height:7px;border-radius:50%;margin-left:-3px;top:19px}.swal-icon{width:80px;height:80px;border-width:4px;border-style:solid;border-radius:50%;padding:0;position:relative;box-sizing:content-box;margin:20px auto}.swal-icon:first-child{margin-top:32px}.swal-icon--custom{width:auto;height:auto;max-width:100%;border:none;border-radius:0}.swal-icon img{max-width:100%;max-height:100%}.swal-title{color:rgba(0,0,0,.65);font-weight:600;text-transform:none;position:relative;display:block;padding:13px 16px;font-size:27px;line-height:normal;text-align:center;margin-bottom:0}.swal-title:first-child{margin-top:26px}.swal-title:not(:first-child){padding-bottom:0}.swal-title:not(:last-child){margin-bottom:13px}.swal-text{font-size:16px;position:relative;float:none;line-height:normal;vertical-align:top;text-align:left;display:inline-block;margin:0;padding:0 10px;font-weight:400;color:rgba(0,0,0,.64);max-width:calc(100% - 20px);overflow-wrap:break-word;box-sizing:border-box}.swal-text:first-child{margin-top:45px}.swal-text:last-child{margin-bottom:45px}.swal-footer{text-align:right;padding-top:13px;margin-top:13px;padding:13px 16px;border-radius:inherit;border-top-left-radius:0;border-top-right-radius:0}.swal-button-container{margin:5px;display:inline-block;position:relative}.swal-button{background-color:#7cd1f9;color:#fff;border:none;box-shadow:none;border-radius:5px;font-weight:600;font-size:14px;padding:10px 24px;margin:0;cursor:pointer}.swal-button[not:disabled]:hover{background-color:#78cbf2}.swal-button:active{background-color:#70bce0}.swal-button:focus{outline:none;box-shadow:0 0 0 1px #fff,0 0 0 3px rgba(43,114,165,.29)}.swal-button[disabled]{opacity:.5;cursor:default}.swal-button::-moz-focus-inner{border:0}.swal-button--cancel{color:#555;background-color:#efefef}.swal-button--cancel[not:disabled]:hover{background-color:#e8e8e8}.swal-button--cancel:active{background-color:#d7d7d7}.swal-button--cancel:focus{box-shadow:0 0 0 1px #fff,0 0 0 3px rgba(116,136,150,.29)}.swal-button--danger{background-color:#e64942}.swal-button--danger[not:disabled]:hover{background-color:#df4740}.swal-button--danger:active{background-color:#cf423b}.swal-button--danger:focus{box-shadow:0 0 0 1px #fff,0 0 0 3px rgba(165,43,43,.29)}.swal-content{padding:0 20px;margin-top:20px;font-size:medium}.swal-content:last-child{margin-bottom:20px}.swal-content__input,.swal-content__textarea{-webkit-appearance:none;background-color:#fff;border:none;font-size:14px;display:block;box-sizing:border-box;width:100%;border:1px solid rgba(0,0,0,.14);padding:10px 13px;border-radius:2px;transition:border-color .2s}.swal-content__input:focus,.swal-content__textarea:focus{outline:none;border-color:#6db8ff}.swal-content__textarea{resize:vertical}.swal-button--loading{color:transparent}.swal-button--loading~.swal-button__loader{opacity:1}.swal-button__loader{position:absolute;height:auto;width:43px;z-index:2;left:50%;top:50%;-webkit-transform:translateX(-50%) translateY(-50%);transform:translateX(-50%) translateY(-50%);text-align:center;pointer-events:none;opacity:0}.swal-button__loader div{display:inline-block;float:none;vertical-align:baseline;width:9px;height:9px;padding:0;border:none;margin:2px;opacity:.4;border-radius:7px;background-color:hsla(0,0%,100%,.9);transition:background .2s;-webkit-animation:swal-loading-anim 1s infinite;animation:swal-loading-anim 1s infinite}.swal-button__loader div:nth-child(3n+2){-webkit-animation-delay:.15s;animation-delay:.15s}.swal-button__loader div:nth-child(3n+3){-webkit-animation-delay:.3s;animation-delay:.3s}@-webkit-keyframes swal-loading-anim{0%{opacity:.4}20%{opacity:.4}50%{opacity:1}to{opacity:.4}}@keyframes swal-loading-anim{0%{opacity:.4}20%{opacity:.4}50%{opacity:1}to{opacity:.4}}.swal-overlay{position:fixed;top:0;bottom:0;left:0;right:0;text-align:center;font-size:0;overflow-y:auto;background-color:rgba(0,0,0,.4);z-index:10000;pointer-events:none;opacity:0;transition:opacity .3s}.swal-overlay:before{content:" ";display:inline-block;vertical-align:middle;height:100%}.swal-overlay--show-modal{opacity:1;pointer-events:auto}.swal-overlay--show-modal .swal-modal{opacity:1;pointer-events:auto;box-sizing:border-box;-webkit-animation:showSweetAlert .3s;animation:showSweetAlert .3s;will-change:transform}.swal-modal{width:478px;opacity:0;pointer-events:none;background-color:#fff;text-align:center;border-radius:5px;position:static;margin:20px auto;display:inline-block;vertical-align:middle;-webkit-transform:scale(1);transform:scale(1);-webkit-transform-origin:50% 50%;transform-origin:50% 50%;z-index:10001;transition:opacity .2s,-webkit-transform .3s;transition:transform .3s,opacity .2s;transition:transform .3s,opacity .2s,-webkit-transform .3s}@media (max-width:500px){.swal-modal{width:calc(100% - 20px)}}@-webkit-keyframes showSweetAlert{0%{-webkit-transform:scale(1);transform:scale(1)}1%{-webkit-transform:scale(.5);transform:scale(.5)}45%{-webkit-transform:scale(1.05);transform:scale(1.05)}80%{-webkit-transform:scale(.95);transform:scale(.95)}to{-webkit-transform:scale(1);transform:scale(1)}}@keyframes showSweetAlert{0%{-webkit-transform:scale(1);transform:scale(1)}1%{-webkit-transform:scale(.5);transform:scale(.5)}45%{-webkit-transform:scale(1.05);transform:scale(1.05)}80%{-webkit-transform:scale(.95);transform:scale(.95)}to{-webkit-transform:scale(1);transform:scale(1)}}', ""])
    }, function (t, e) {
        function n(t, e) {
            var n = t[1] || "", r = t[3];
            if ( !r ) return n;
            if ( e && "function" == typeof btoa ) {
                var i = o(r);
                return [n].concat(r.sources.map(function (t) {
                    return "/*# sourceURL=" + r.sourceRoot + t + " */"
                })).concat([i]).join("\n")
            }
            return [n].join("\n")
        }

        function o(t) {

        }

        t.exports = function (t) {
            var e = [];
            return e.toString = function () {
                return this.map(function (e) {
                    var o = n(e, t);
                    return e[2] ? "@media " + e[2] + "{" + o + "}" : o
                }).join("")
            }, e.i = function (t, n) {
                "string" == typeof t && ( t = [[null, t, ""]] );
                for ( var o = {}, r = 0; r < this.length; r++ ) {
                    var i = this[r][0];
                    "number" == typeof i && ( o[i] = !0 )
                }
                for ( r = 0; r < t.length; r++ ) {
                    var a = t[r];
                    "number" == typeof a[0] && o[a[0]] || ( n && !a[2] ? a[2] = n : n && ( a[2] = "(" + a[2] + ") and (" + n + ")" ), e.push(a) )
                }
            }, e
        }
    }, function (t, e, n) {
        function o(t, e) {
            for ( var n = 0; n < t.length; n++ ) {
                var o = t[n], r = m[o.id];
                if ( r ) {
                    r.refs++;
                    for ( var i = 0; i < r.parts.length; i++ ) r.parts[i](o.parts[i]);
                    for ( ; i < o.parts.length; i++ ) r.parts.push(u(o.parts[i], e))
                } else {
                    for ( var a = [], i = 0; i < o.parts.length; i++ ) a.push(u(o.parts[i], e));
                    m[o.id] = {id: o.id, refs: 1, parts: a}
                }
            }
        }

        function r(t, e) {
            for ( var n = [], o = {}, r = 0; r < t.length; r++ ) {
                var i = t[r], a = e.base ? i[0] + e.base : i[0], s = i[1], c = i[2], l = i[3],
                    u = {css: s, media: c, sourceMap: l};
                o[a] ? o[a].parts.push(u) : n.push(o[a] = {id: a, parts: [u]})
            }
            return n
        }

        function i(t, e) {
            var n = v(t.insertInto);
            if ( !n ) throw new Error("Couldn't find a style target. This probably means that the value for the 'insertInto' parameter is invalid.");
            var o = w[w.length - 1];
            if ( "top" === t.insertAt ) o ? o.nextSibling ? n.insertBefore(e, o.nextSibling) : n.appendChild(e) : n.insertBefore(e, n.firstChild), w.push(e); else {
                if ( "bottom" !== t.insertAt ) throw new Error("Invalid value for parameter 'insertAt'. Must be 'top' or 'bottom'.");
                n.appendChild(e)
            }
        }

        function a(t) {
            if ( null === t.parentNode ) return !1;
            t.parentNode.removeChild(t);
            var e = w.indexOf(t);
            e >= 0 && w.splice(e, 1)
        }

        function s(t) {
            var e = document.createElement("style");
            return t.attrs.type = "text/css", l(e, t.attrs), i(t, e), e
        }

        function c(t) {
            var e = document.createElement("link");
            return t.attrs.type = "text/css", t.attrs.rel = "stylesheet", l(e, t.attrs), i(t, e), e
        }

        function l(t, e) {
            Object.keys(e).forEach(function (n) {
                t.setAttribute(n, e[n])
            })
        }

        function u(t, e) {
            var n, o, r, i;
            if ( e.transform && t.css ) {
                if ( !( i = e.transform(t.css) ) ) return function () {
                };
                t.css = i
            }
            if ( e.singleton ) {
                var l = h++;
                n = g || ( g = s(e) ), o = f.bind(null, n, l, !1), r = f.bind(null, n, l, !0)
            } else t.sourceMap && "function" == typeof URL && "function" == typeof URL.createObjectURL && "function" == typeof URL.revokeObjectURL && "function" == typeof Blob && "function" == typeof btoa ? ( n = c(e), o = p.bind(null, n, e), r = function () {
                a(n), n.href && URL.revokeObjectURL(n.href)
            } ) : ( n = s(e), o = d.bind(null, n), r = function () {
                a(n)
            } );
            return o(t), function (e) {
                if ( e ) {
                    if ( e.css === t.css && e.media === t.media && e.sourceMap === t.sourceMap ) return;
                    o(t = e)
                } else r()
            }
        }

        function f(t, e, n, o) {
            var r = n ? "" : o.css;
            if ( t.styleSheet ) t.styleSheet.cssText = x(e, r); else {
                var i = document.createTextNode(r), a = t.childNodes;
                a[e] && t.removeChild(a[e]), a.length ? t.insertBefore(i, a[e]) : t.appendChild(i)
            }
        }

        function d(t, e) {
            var n = e.css, o = e.media;
            if ( o && t.setAttribute("media", o), t.styleSheet ) t.styleSheet.cssText = n; else {
                for ( ; t.firstChild; ) t.removeChild(t.firstChild);
                t.appendChild(document.createTextNode(n))
            }
        }

        function p(t, e, n) {
            var o = n.css, r = n.sourceMap, i = void 0 === e.convertToAbsoluteUrls && r;

            var a = new Blob([o], {type: "text/css"}), s = t.href;
            t.href = URL.createObjectURL(a), s && URL.revokeObjectURL(s)
        }

        var m = {}, b = function (t) {
            var e;
            return function () {
                return void 0 === e && ( e = t.apply(this, arguments) ), e
            }
        }(function () {
            return window && document && document.all && !window.atob
        }), v = function (t) {
            var e = {};
            return function (n) {
                return void 0 === e[n] && ( e[n] = t.call(this, n) ), e[n]
            }
        }(function (t) {
            return document.querySelector(t)
        }), g = null, h = 0, w = [], y = n(15);
        t.exports = function (t, e) {
            if ( "undefined" != typeof DEBUG && DEBUG && "object" != typeof document ) throw new Error("The style-loader cannot be used in a non-browser environment");
            e = e || {}, e.attrs = "object" == typeof e.attrs ? e.attrs : {}, e.singleton || ( e.singleton = b() ), e.insertInto || ( e.insertInto = "head" ), e.insertAt || ( e.insertAt = "bottom" );
            var n = r(t, e);
            return o(n, e), function (t) {
                for ( var i = [], a = 0; a < n.length; a++ ) {
                    var s = n[a], c = m[s.id];
                    c.refs--, i.push(c)
                }
                if ( t ) {
                    o(r(t, e), e)
                }
                for ( var a = 0; a < i.length; a++ ) {
                    var c = i[a];
                    if ( 0 === c.refs ) {
                        for ( var l = 0; l < c.parts.length; l++ ) c.parts[l]();
                        delete m[c.id]
                    }
                }
            }
        };
        var x = function () {
            var t = [];
            return function (e, n) {
                return t[e] = n, t.filter(Boolean).join("\n")
            }
        }()
    }, function (t, e) {
        t.exports = function (t) {
            var e = "undefined" != typeof window && window.location;
            if ( !e ) throw new Error("fixUrls requires window.location");
            if ( !t || "string" != typeof t ) return t;
            var n = e.protocol + "//" + e.host, o = n + e.pathname.replace(/\/[^\/]*$/, "/");
            return t.replace(/url\s*\(((?:[^)(]|\((?:[^)(]+|\([^)(]*\))*\))*)\)/gi, function (t, e) {
                var r = e.trim().replace(/^"(.*)"$/, function (t, e) {
                    return e
                }).replace(/^'(.*)'$/, function (t, e) {
                    return e
                });
                if ( /^(#|data:|http:\/\/|https:\/\/|file:\/\/\/)/i.test(r) ) return t;
                var i;
                return i = 0 === r.indexOf("//") ? r : 0 === r.indexOf("/") ? n + r : o + r.replace(/^\.\//, ""), "url(" + JSON.stringify(i) + ")"
            })
        }
    }, function (t, e, n) {
        var o = n(17);
        "undefined" == typeof window || window.Promise || ( window.Promise = o ), n(21), String.prototype.includes || ( String.prototype.includes = function (t, e) {
            "use strict";
            return "number" != typeof e && ( e = 0 ), !( e + t.length > this.length ) && -1 !== this.indexOf(t, e)
        } ), Array.prototype.includes || Object.defineProperty(Array.prototype, "includes", {
            value: function (t, e) {
                if ( null == this ) throw new TypeError('"this" is null or not defined');
                var n = Object(this), o = n.length >>> 0;
                if ( 0 === o ) return !1;
                for ( var r = 0 | e, i = Math.max(r >= 0 ? r : o - Math.abs(r), 0); i < o; ) {
                    if ( function (t, e) {
                            return t === e || "number" == typeof t && "number" == typeof e && isNaN(t) && isNaN(e)
                        }(n[i], t) ) return !0;
                    i++
                }
                return !1
            }
        }), "undefined" != typeof window && function (t) {
            t.forEach(function (t) {
                t.hasOwnProperty("remove") || Object.defineProperty(t, "remove", {
                    configurable: !0,
                    enumerable: !0,
                    writable: !0,
                    value: function () {
                        this.parentNode.removeChild(this)
                    }
                })
            })
        }([Element.prototype, CharacterData.prototype, DocumentType.prototype])
    }, function (t, e, n) {
        ( function (e) {
            !function (n) {
                function o() {
                }

                function r(t, e) {
                    return function () {
                        t.apply(e, arguments)
                    }
                }

                function i(t) {
                    if ( "object" != typeof this ) throw new TypeError("Promises must be constructed via new");
                    if ( "function" != typeof t ) throw new TypeError("not a function");
                    this._state = 0, this._handled = !1, this._value = void 0, this._deferreds = [], f(t, this)
                }

                function a(t, e) {
                    for ( ; 3 === t._state; ) t = t._value;
                    if ( 0 === t._state ) return void t._deferreds.push(e);
                    t._handled = !0, i._immediateFn(function () {
                        var n = 1 === t._state ? e.onFulfilled : e.onRejected;
                        if ( null === n ) return void( 1 === t._state ? s : c )(e.promise, t._value);
                        var o;
                        try {
                            o = n(t._value)
                        } catch ( t ) {
                            return void c(e.promise, t)
                        }
                        s(e.promise, o)
                    })
                }

                function s(t, e) {
                    try {
                        if ( e === t ) throw new TypeError("A promise cannot be resolved with itself.");
                        if ( e && ( "object" == typeof e || "function" == typeof e ) ) {
                            var n = e.then;
                            if ( e instanceof i ) return t._state = 3, t._value = e, void l(t);
                            if ( "function" == typeof n ) return void f(r(n, e), t)
                        }
                        t._state = 1, t._value = e, l(t)
                    } catch ( e ) {
                        c(t, e)
                    }
                }

                function c(t, e) {
                    t._state = 2, t._value = e, l(t)
                }

                function l(t) {
                    2 === t._state && 0 === t._deferreds.length && i._immediateFn(function () {
                        t._handled || i._unhandledRejectionFn(t._value)
                    });
                    for ( var e = 0, n = t._deferreds.length; e < n; e++ ) a(t, t._deferreds[e]);
                    t._deferreds = null
                }

                function u(t, e, n) {
                    this.onFulfilled = "function" == typeof t ? t : null, this.onRejected = "function" == typeof e ? e : null, this.promise = n
                }

                function f(t, e) {
                    var n = !1;
                    try {
                        t(function (t) {
                            n || ( n = !0, s(e, t) )
                        }, function (t) {
                            n || ( n = !0, c(e, t) )
                        })
                    } catch ( t ) {
                        if ( n ) return;
                        n = !0, c(e, t)
                    }
                }

                var d = setTimeout;
                i.prototype.catch = function (t) {
                    return this.then(null, t)
                }, i.prototype.then = function (t, e) {
                    var n = new this.constructor(o);
                    return a(this, new u(t, e, n)), n
                }, i.all = function (t) {
                    var e = Array.prototype.slice.call(t);
                    return new i(function (t, n) {
                        function o(i, a) {
                            try {
                                if ( a && ( "object" == typeof a || "function" == typeof a ) ) {
                                    var s = a.then;
                                    if ( "function" == typeof s ) return void s.call(a, function (t) {
                                        o(i, t)
                                    }, n)
                                }
                                e[i] = a, 0 == --r && t(e)
                            } catch ( t ) {
                                n(t)
                            }
                        }

                        if ( 0 === e.length ) return t([]);
                        for ( var r = e.length, i = 0; i < e.length; i++ ) o(i, e[i])
                    })
                }, i.resolve = function (t) {
                    return t && "object" == typeof t && t.constructor === i ? t : new i(function (e) {
                        e(t)
                    })
                }, i.reject = function (t) {
                    return new i(function (e, n) {
                        n(t)
                    })
                }, i.race = function (t) {
                    return new i(function (e, n) {
                        for ( var o = 0, r = t.length; o < r; o++ ) t[o].then(e, n)
                    })
                }, i._immediateFn = "function" == typeof e && function (t) {
                    e(t)
                } || function (t) {
                    d(t, 0)
                }, i._unhandledRejectionFn = function (t) {
                    "undefined" != typeof console && console && console.warn("Possible Unhandled Promise Rejection:", t)
                }, i._setImmediateFn = function (t) {
                    i._immediateFn = t
                }, i._setUnhandledRejectionFn = function (t) {
                    i._unhandledRejectionFn = t
                }, void 0 !== t && t.exports ? t.exports = i : n.Promise || ( n.Promise = i )
            }(this)
        } ).call(e, n(18).setImmediate)
    }, function (t, e, n) {
        function o(t, e) {
            this._id = t, this._clearFn = e
        }

        var r = Function.prototype.apply;
        e.setTimeout = function () {
            return new o(r.call(setTimeout, window, arguments), clearTimeout)
        }, e.setInterval = function () {
            return new o(r.call(setInterval, window, arguments), clearInterval)
        }, e.clearTimeout = e.clearInterval = function (t) {
            t && t.close()
        }, o.prototype.unref = o.prototype.ref = function () {
        }, o.prototype.close = function () {
            this._clearFn.call(window, this._id)
        }, e.enroll = function (t, e) {
            clearTimeout(t._idleTimeoutId), t._idleTimeout = e
        }, e.unenroll = function (t) {
            clearTimeout(t._idleTimeoutId), t._idleTimeout = -1
        }, e._unrefActive = e.active = function (t) {
            clearTimeout(t._idleTimeoutId);
            var e = t._idleTimeout;
            e >= 0 && ( t._idleTimeoutId = setTimeout(function () {
                t._onTimeout && t._onTimeout()
            }, e) )
        }, n(19), e.setImmediate = setImmediate, e.clearImmediate = clearImmediate
    }, function (t, e, n) {
        ( function (t, e) {
            !function (t, n) {
                "use strict";

                function o(t) {
                    "function" != typeof t && ( t = new Function("" + t) );
                    for ( var e = new Array(arguments.length - 1), n = 0; n < e.length; n++ ) e[n] = arguments[n + 1];
                    var o = {callback: t, args: e};
                    return l[c] = o, s(c), c++
                }

                function r(t) {
                    delete l[t]
                }

                function i(t) {
                    var e = t.callback, o = t.args;
                    switch ( o.length ) {
                        case 0:
                            e();
                            break;
                        case 1:
                            e(o[0]);
                            break;
                        case 2:
                            e(o[0], o[1]);
                            break;
                        case 3:
                            e(o[0], o[1], o[2]);
                            break;
                        default:
                            e.apply(n, o)
                    }
                }

                function a(t) {
                    if ( u ) setTimeout(a, 0, t); else {
                        var e = l[t];
                        if ( e ) {
                            u = !0;
                            try {
                                i(e)
                            } finally {
                                r(t), u = !1
                            }
                        }
                    }
                }

                if ( !t.setImmediate ) {
                    var s, c = 1, l = {}, u = !1, f = t.document, d = Object.getPrototypeOf && Object.getPrototypeOf(t);
                    d = d && d.setTimeout ? d : t, "[object process]" === {}.toString.call(t.process) ? function () {
                        s = function (t) {
                            e.nextTick(function () {
                                a(t)
                            })
                        }
                    }() : function () {
                        if ( t.postMessage && !t.importScripts ) {
                            var e = !0, n = t.onmessage;
                            return t.onmessage = function () {
                                e = !1
                            }, t.postMessage("", "*"), t.onmessage = n, e
                        }
                    }() ? function () {
                        var e = "setImmediate$" + Math.random() + "$", n = function (n) {
                            n.source === t && "string" == typeof n.data && 0 === n.data.indexOf(e) && a(+n.data.slice(e.length))
                        };
                        t.addEventListener ? t.addEventListener("message", n, !1) : t.attachEvent("onmessage", n), s = function (n) {
                            t.postMessage(e + n, "*")
                        }
                    }() : t.MessageChannel ? function () {
                        var t = new MessageChannel;
                        t.port1.onmessage = function (t) {
                            a(t.data)
                        }, s = function (e) {
                            t.port2.postMessage(e)
                        }
                    }() : f && "onreadystatechange" in f.createElement("script") ? function () {
                        var t = f.documentElement;
                        s = function (e) {
                            var n = f.createElement("script");
                            n.onreadystatechange = function () {
                                a(e), n.onreadystatechange = null, t.removeChild(n), n = null
                            }, t.appendChild(n)
                        }
                    }() : function () {
                        s = function (t) {
                            setTimeout(a, 0, t)
                        }
                    }(), d.setImmediate = o, d.clearImmediate = r
                }
            }("undefined" == typeof self ? void 0 === t ? this : t : self)
        } ).call(e, n(7), n(20))
    }, function (t, e) {
        function n() {
            throw new Error("setTimeout has not been defined")
        }

        function o() {
            throw new Error("clearTimeout has not been defined")
        }

        function r(t) {
            if ( u === setTimeout ) return setTimeout(t, 0);
            if ( ( u === n || !u ) && setTimeout ) return u = setTimeout, setTimeout(t, 0);
            try {
                return u(t, 0)
            } catch ( e ) {
                try {
                    return u.call(null, t, 0)
                } catch ( e ) {
                    return u.call(this, t, 0)
                }
            }
        }

        function i(t) {
            if ( f === clearTimeout ) return clearTimeout(t);
            if ( ( f === o || !f ) && clearTimeout ) return f = clearTimeout, clearTimeout(t);
            try {
                return f(t)
            } catch ( e ) {
                try {
                    return f.call(null, t)
                } catch ( e ) {
                    return f.call(this, t)
                }
            }
        }

        function a() {
            b && p && ( b = !1, p.length ? m = p.concat(m) : v = -1, m.length && s() )
        }

        function s() {
            if ( !b ) {
                var t = r(a);
                b = !0;
                for ( var e = m.length; e; ) {
                    for ( p = m, m = []; ++v < e; ) p && p[v].run();
                    v = -1, e = m.length
                }
                p = null, b = !1, i(t)
            }
        }

        function c(t, e) {
            this.fun = t, this.array = e
        }

        function l() {
        }

        var u, f, d = t.exports = {};
        !function () {
            try {
                u = "function" == typeof setTimeout ? setTimeout : n
            } catch ( t ) {
                u = n
            }
            try {
                f = "function" == typeof clearTimeout ? clearTimeout : o
            } catch ( t ) {
                f = o
            }
        }();
        var p, m = [], b = !1, v = -1;
        d.nextTick = function (t) {
            var e = new Array(arguments.length - 1);
            if ( arguments.length > 1 ) for ( var n = 1; n < arguments.length; n++ ) e[n - 1] = arguments[n];
            m.push(new c(t, e)), 1 !== m.length || b || r(s)
        }, c.prototype.run = function () {
            this.fun.apply(null, this.array)
        }, d.title = "browser", d.browser = !0, d.env = {}, d.argv = [], d.version = "", d.versions = {}, d.on = l, d.addListener = l, d.once = l, d.off = l, d.removeListener = l, d.removeAllListeners = l, d.emit = l, d.prependListener = l, d.prependOnceListener = l, d.listeners = function (t) {
            return []
        }, d.binding = function (t) {
            throw new Error("process.binding is not supported")
        }, d.cwd = function () {
            return "/"
        }, d.chdir = function (t) {
            throw new Error("process.chdir is not supported")
        }, d.umask = function () {
            return 0
        }
    }, function (t, e, n) {
        "use strict";
        n(22).polyfill()
    }, function (t, e, n) {
        "use strict";

        function o(t, e) {
            if ( void 0 === t || null === t ) throw new TypeError("Cannot convert first argument to object");
            for ( var n = Object(t), o = 1; o < arguments.length; o++ ) {
                var r = arguments[o];
                if ( void 0 !== r && null !== r ) for ( var i = Object.keys(Object(r)), a = 0, s = i.length; a < s; a++ ) {
                    var c = i[a], l = Object.getOwnPropertyDescriptor(r, c);
                    void 0 !== l && l.enumerable && ( n[c] = r[c] )
                }
            }
            return n
        }

        function r() {
            Object.assign || Object.defineProperty(Object, "assign", {
                enumerable: !1,
                configurable: !0,
                writable: !0,
                value: o
            })
        }

        t.exports = {assign: o, polyfill: r}
    }, function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", {value: !0});
        var o = n(24), r = n(6), i = n(5), a = n(36), s = function () {
            for ( var t = [], e = 0; e < arguments.length; e++ ) t[e] = arguments[e];
            if ( "undefined" != typeof window ) {
                var n = a.getOpts.apply(void 0, t);
                return new Promise(function (t, e) {
                    i.default.promise = {resolve: t, reject: e}, o.default(n), setTimeout(function () {
                        r.openModal()
                    })
                })
            }
        };
        s.close = r.onAction, s.getState = r.getState, s.setActionValue = i.setActionValue, s.stopLoading = r.stopLoading, s.setDefaults = a.setDefaults, e.default = s
    }, function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", {value: !0});
        var o = n(1), r = n(0), i = r.default.MODAL, a = n(4), s = n(34), c = n(35), l = n(1);
        e.init = function (t) {
            o.getNode(i) || ( document.body || l.throwErr("You can only use SweetAlert AFTER the DOM has loaded!"), s.default(), a.default() ), a.initModalContent(t), c.default(t)
        }, e.default = e.init
    }, function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", {value: !0});
        var o = n(0), r = o.default.MODAL;
        e.modalMarkup = '\n  <div class="' + r + '" role="dialog" aria-modal="true"></div>', e.default = e.modalMarkup
    }, function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", {value: !0});
        var o = n(0), r = o.default.OVERLAY, i = '<div \n    class="' + r + '"\n    tabIndex="-1">\n  </div>';
        e.default = i
    }, function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", {value: !0});
        var o = n(0), r = o.default.ICON;
        e.errorIconMarkup = function () {
            var t = r + "--error", e = t + "__line";
            return '\n    <div class="' + t + '__x-mark">\n      <span class="' + e + " " + e + '--left"></span>\n      <span class="' + e + " " + e + '--right"></span>\n    </div>\n  '
        }, e.warningIconMarkup = function () {
            var t = r + "--warning";
            return '\n    <span class="' + t + '__body">\n      <span class="' + t + '__dot"></span>\n    </span>\n  '
        }, e.successIconMarkup = function () {
            var t = r + "--success";
            return '\n    <span class="' + t + "__line " + t + '__line--long"></span>\n    <span class="' + t + "__line " + t + '__line--tip"></span>\n\n    <div class="' + t + '__ring"></div>\n    <div class="' + t + '__hide-corners"></div>\n  '
        }
    }, function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", {value: !0});
        var o = n(0), r = o.default.CONTENT;
        e.contentMarkup = '\n  <div class="' + r + '">\n\n  </div>\n'
    }, function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", {value: !0});
        var o = n(0), r = o.default.BUTTON_CONTAINER, i = o.default.BUTTON, a = o.default.BUTTON_LOADER;
        e.buttonMarkup = '\n  <div class="' + r + '">\n\n    <button\n      class="' + i + '"\n    ></button>\n\n    <div class="' + a + '">\n      <div></div>\n      <div></div>\n      <div></div>\n    </div>\n\n  </div>\n'
    }, function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", {value: !0});
        var o = n(4), r = n(2), i = n(0), a = i.default.ICON, s = i.default.ICON_CUSTOM,
            c = ["error", "warning", "success", "info"],
            l = {error: r.errorIconMarkup(), warning: r.warningIconMarkup(), success: r.successIconMarkup()},
            u = function (t, e) {
                var n = a + "--" + t;
                e.classList.add(n);
                var o = l[t];
                o && ( e.innerHTML = o )
            }, f = function (t, e) {
                e.classList.add(s);
                var n = document.createElement("img");
                n.src = t, e.appendChild(n)
            }, d = function (t) {
                if ( t ) {
                    var e = o.injectElIntoModal(r.iconMarkup);
                    c.includes(t) ? u(t, e) : f(t, e)
                }
            };
        e.default = d
    }, function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", {value: !0});
        var o = n(2), r = n(4), i = function (t) {
            navigator.userAgent.includes("AppleWebKit") && ( t.style.display = "none", t.offsetHeight, t.style.display = "" )
        };
        e.initTitle = function (t) {
            if ( t ) {
                var e = r.injectElIntoModal(o.titleMarkup);
                e.textContent = t, i(e)
            }
        }, e.initText = function (t) {
            if ( t ) {
                var e = document.createDocumentFragment();
                t.split("\n").forEach(function (t, n, o) {
                    e.appendChild(document.createTextNode(t)), n < o.length - 1 && e.appendChild(document.createElement("br"))
                });
                var n = r.injectElIntoModal(o.textMarkup);
                n.appendChild(e), i(n)
            }
        }
    }, function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", {value: !0});
        var o = n(1), r = n(4), i = n(0), a = i.default.BUTTON, s = i.default.DANGER_BUTTON, c = n(3), l = n(2),
            u = n(6), f = n(5), d = function (t, e, n) {
                var r = e.text, i = e.value, d = e.className, p = e.closeModal, m = o.stringToNode(l.buttonMarkup),
                    b = m.querySelector("." + a), v = a + "--" + t;
                if ( b.classList.add(v), d ) {
                    ( Array.isArray(d) ? d : d.split(" ") ).filter(function (t) {
                        return t.length > 0
                    }).forEach(function (t) {
                        b.classList.add(t)
                    })
                }
                n && t === c.CONFIRM_KEY && b.classList.add(s), b.textContent = r;
                var g = {};
                return g[t] = i, f.setActionValue(g), f.setActionOptionsFor(t, {closeModal: p}), b.addEventListener("click", function () {
                    return u.onAction(t)
                }), m
            }, p = function (t, e) {
                var n = r.injectElIntoModal(l.footerMarkup);
                for ( var o in t ) {
                    var i = t[o], a = d(o, i, e);
                    i.visible && n.appendChild(a)
                }
                0 === n.children.length && n.remove()
            };
        e.default = p
    }, function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", {value: !0});
        var o = n(3), r = n(4), i = n(2), a = n(5), s = n(6), c = n(0), l = c.default.CONTENT, u = function (t) {
            t.addEventListener("input", function (t) {
                var e = t.target, n = e.value;
                a.setActionValue(n)
            }), t.addEventListener("keyup", function (t) {
                if ( "Enter" === t.key ) return s.onAction(o.CONFIRM_KEY)
            }), setTimeout(function () {
                t.focus(), a.setActionValue("")
            }, 0)
        }, f = function (t, e, n) {
            var o = document.createElement(e), r = l + "__" + e;
            o.classList.add(r);
            for ( var i in n ) {
                var a = n[i];
                o[i] = a
            }
            "input" === e && u(o), t.appendChild(o)
        }, d = function (t) {
            if ( t ) {
                var e = r.injectElIntoModal(i.contentMarkup), n = t.element, o = t.attributes;
                "string" == typeof n ? f(e, n, o) : e.appendChild(n)
            }
        };
        e.default = d
    }, function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", {value: !0});
        var o = n(1), r = n(2), i = function () {
            var t = o.stringToNode(r.overlayMarkup);
            document.body.appendChild(t)
        };
        e.default = i
    }, function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", {value: !0});
        var o = n(5), r = n(6), i = n(1), a = n(3), s = n(0), c = s.default.MODAL, l = s.default.BUTTON,
            u = s.default.OVERLAY, f = function (t) {
                t.preventDefault(), v()
            }, d = function (t) {
                t.preventDefault(), g()
            }, p = function (t) {
                if ( o.default.isOpen ) switch ( t.key ) {
                    case"Escape":
                        return r.onAction(a.CANCEL_KEY)
                }
            }, m = function (t) {
                if ( o.default.isOpen ) switch ( t.key ) {
                    case"Tab":
                        return f(t)
                }
            }, b = function (t) {
                if ( o.default.isOpen ) return "Tab" === t.key && t.shiftKey ? d(t) : void 0
            }, v = function () {
                var t = i.getNode(l);
                t && ( t.tabIndex = 0, t.focus() )
            }, g = function () {
                var t = i.getNode(c), e = t.querySelectorAll("." + l), n = e.length - 1, o = e[n];
                o && o.focus()
            }, h = function (t) {
                t[t.length - 1].addEventListener("keydown", m)
            }, w = function (t) {
                t[0].addEventListener("keydown", b)
            }, y = function () {
                var t = i.getNode(c), e = t.querySelectorAll("." + l);
                e.length && ( h(e), w(e) )
            }, x = function (t) {
                if ( i.getNode(u) === t.target ) return r.onAction(a.CANCEL_KEY)
            }, _ = function (t) {
                var e = i.getNode(u);
                e.removeEventListener("click", x), t && e.addEventListener("click", x)
            }, k = function (t) {
                o.default.timer && clearTimeout(o.default.timer), t && ( o.default.timer = window.setTimeout(function () {
                    return r.onAction(a.CANCEL_KEY)
                }, t) )
            }, O = function (t) {
                t.closeOnEsc ? document.addEventListener("keyup", p) : document.removeEventListener("keyup", p), t.dangerMode ? v() : g(), y(), _(t.closeOnClickOutside), k(t.timer)
            };
        e.default = O
    }, function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", {value: !0});
        var o = n(1), r = n(3), i = n(37), a = n(38), s = {
            title: null,
            text: null,
            icon: null,
            buttons: r.defaultButtonList,
            content: null,
            className: null,
            closeOnClickOutside: !0,
            closeOnEsc: !0,
            dangerMode: !1,
            timer: null
        }, c = Object.assign({}, s);
        e.setDefaults = function (t) {
            c = Object.assign({}, s, t)
        };
        var l = function (t) {
            var e = t && t.button, n = t && t.buttons;
            return void 0 !== e && void 0 !== n && o.throwErr("Cannot set both 'button' and 'buttons' options!"), void 0 !== e ? {confirm: e} : n
        }, u = function (t) {
            return o.ordinalSuffixOf(t + 1)
        }, f = function (t, e) {
            o.throwErr(u(e) + " argument ('" + t + "') is invalid")
        }, d = function (t, e) {
            var n = t + 1, r = e[n];
            o.isPlainObject(r) || void 0 === r || o.throwErr("Expected " + u(n) + " argument ('" + r + "') to be a plain object")
        }, p = function (t, e) {
            var n = t + 1, r = e[n];
            void 0 !== r && o.throwErr("Unexpected " + u(n) + " argument (" + r + ")")
        }, m = function (t, e, n, r) {
            var i = typeof e, a = "string" === i, s = e instanceof Element;
            if ( a ) {
                if ( 0 === n ) return {text: e};
                if ( 1 === n ) return {text: e, title: r[0]};
                if ( 2 === n ) return d(n, r), {icon: e};
                f(e, n)
            } else {
                if ( s && 0 === n ) return d(n, r), {content: e};
                if ( o.isPlainObject(e) ) return p(n, r), e;
                f(e, n)
            }
        };
        e.getOpts = function () {
            for ( var t = [], e = 0; e < arguments.length; e++ ) t[e] = arguments[e];
            var n = {};
            t.forEach(function (e, o) {
                var r = m(0, e, o, t);
                Object.assign(n, r)
            });
            var o = l(n);
            n.buttons = r.getButtonListOpts(o), delete n.button, n.content = i.getContentOpts(n.content);
            var u = Object.assign({}, s, c, n);
            return Object.keys(u).forEach(function (t) {
                a.DEPRECATED_OPTS[t] && a.logDeprecation(t)
            }), u
        }
    }, function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", {value: !0});
        var o = n(1), r = {element: "input", attributes: {placeholder: ""}};
        e.getContentOpts = function (t) {
            var e = {};
            return o.isPlainObject(t) ? Object.assign(e, t) : t instanceof Element ? {element: t} : "input" === t ? r : null
        }
    }, function (t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", {value: !0}), e.logDeprecation = function (t) {
            var n = e.DEPRECATED_OPTS[t], o = n.onlyRename, r = n.replacement, i = n.subOption, a = n.link,
                s = o ? "renamed" : "deprecated", c = 'SweetAlert warning: "' + t + '" option has been ' + s + ".";
            if ( r ) {
                c += " Please use" + ( i ? ' "' + i + '" in ' : " " ) + '"' + r + '" instead.'
            }
            var l = "https://sweetalert.js.org";
            c += a ? " More details: " + l + a : " More details: " + l + "/guides/#upgrading-from-1x", console.warn(c)
        }, e.DEPRECATED_OPTS = {
            type: {replacement: "icon", link: "/docs/#icon"},
            imageUrl: {replacement: "icon", link: "/docs/#icon"},
            customClass: {replacement: "className", onlyRename: !0, link: "/docs/#classname"},
            imageSize: {},
            showCancelButton: {replacement: "buttons", link: "/docs/#buttons"},
            showConfirmButton: {replacement: "button", link: "/docs/#button"},
            confirmButtonText: {replacement: "button", link: "/docs/#button"},
            confirmButtonColor: {},
            cancelButtonText: {replacement: "buttons", link: "/docs/#buttons"},
            closeOnConfirm: {replacement: "button", subOption: "closeModal", link: "/docs/#button"},
            closeOnCancel: {replacement: "buttons", subOption: "closeModal", link: "/docs/#buttons"},
            showLoaderOnConfirm: {replacement: "buttons"},
            animation: {},
            inputType: {replacement: "content", link: "/docs/#content"},
            inputValue: {replacement: "content", link: "/docs/#content"},
            inputPlaceholder: {replacement: "content", link: "/docs/#content"},
            html: {replacement: "content", link: "/docs/#content"},
            allowEscapeKey: {replacement: "closeOnEsc", onlyRename: !0, link: "/docs/#closeonesc"},
            allowClickOutside: {replacement: "closeOnClickOutside", onlyRename: !0, link: "/docs/#closeonclickoutside"}
        }
    }])
});
