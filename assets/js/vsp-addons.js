"use strict";function _typeof(t){return(_typeof="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t})(t)}!function(a,s,t){a("div.wponion-framework.wponion-framework-bootstrap").removeClass("wponion-framework-bootstrap"),a("div.wponion-form-actions").remove(),a.VSP_ADDONS=a.VSP_ADDONS||{},new Vue({el:"#vspAddonListing",template:"#VSPAddonsListingTemplate",methods:{getPluginStatusLabel:function(t){if(void 0!==this.status[t])return this.status[t]},change_category:function(t){this.current_category=t},is_show_cateogry:function(t){return"all"===this.current_category||("inactive"===this.current_category&&!1===t.is_active||("active"===this.current_category&&!0===t.is_active||void 0!==t.category&&void 0!==t.category[this.current_category]))},pluginViewUrl:function(t,o){var e=this.text.plugin_view_url;return(e=e.replace("{{slug}}",o)).replace("{{addon.addon_path_md5}}",t.addon_path_md5)},addonHandleButton:function(t,o,e){var n=this;a.VSP_ADDONS.blockUI(t.addon_path_md5),new s.wponion.ajaxer({method:"POST",data:{hook_slug:vsp_addons_settings.hook_slug,addon_slug:o,addon_action:e,addon_pathid:t.addon_path_md5,action:"vsp_addon_action"},always:function(){a.VSP_ADDONS.unblock(t.addon_path_md5)},success:function(){n.pdata[o].is_active="activate"===e}})}},computed:{cats:function(){if(void 0!==this.categoires)return this.categoires;var t=this.pdata,o=this.default_cats,e="";for(e in t)if(void 0!==_typeof(t[e].category)){var n="";for(n in t[e].category)void 0===o[n]&&(o[n]=t[e].category[n])}return this.categoires=o,this.category_slugs=Object.keys(o),this.current_category=this.category_slugs[0],o}},data:{pdata:vsp_addons_settings.plugin_data,status:vsp_addons_settings.plugin_status,default_cats:vsp_addons_settings.default_cats,categories:null,category_slugs:null,current_category:null,text:vsp_addons_settings.texts}}),a.VSP_ADDONS.blockUI=function(t){a("div#"+t).toggleClass("vsp-requested").block({message:null,overlayCSS:{background:"#fff",opacity:.6}})},a.VSP_ADDONS.unblock=function(t){a("div#"+t).toggleClass("vsp-requested").unblock()}}(jQuery,window,document);