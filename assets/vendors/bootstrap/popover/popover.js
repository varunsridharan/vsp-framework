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