!function(s,t,e){s.VSPFRAMEWORK=s.VSPFRAMEWORK||{},s.VSPFRAMEWORK.init_inline_ajax=function(t){if(t.preventDefault(),!s(this).hasClass("disabled")&&!s(this).hasClass("in-process")){var e=s(this),n=e.attr("href"),a=e.attr("data-method"),i=e.attr("data-triggername");void 0===a&&(a="GET"),new VSPAjax({url:n,method:a},{trigger_code:i,element:e,element_lock:!0})}},s(e).ready(function(){0<s("#vsp-sys-status-report-text-btn").length&&s("#vsp-sys-status-report-text-btn").click(function(t){t.preventDefault(),s(this).parent().parent().find("textarea").slideDown("slow"),s(this).remove()}),s("body").on("click","a.vsp-inline-ajax, button.vsp-inline-ajax",s.VSPFRAMEWORK.init_inline_ajax)})}(jQuery,window,document);