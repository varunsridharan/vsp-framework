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
