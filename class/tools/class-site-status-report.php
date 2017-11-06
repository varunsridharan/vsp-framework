<?php
if(!defined("ABSPATH")){ exit; }

if(!class_exists("VSP_Site_Status_Report")){
    class VSP_Site_Status_Report {
        
        public function __construct(){
            global $wpdb;
            $this->wp_env = array();
            $this->active_theme = array();
            $this->must_use_plugins = array();
            $this->active_plugins = array();
            $this->msite_plugins = array();
            $this->server_info = array();
            $this->php_info = array();
            $this->php_exts = array();
            $this->session_config = array();
            $this->set_wp_env_data();
            $this->set_active_theme_data();
            $this->set_plugins_data();
            $this->set_php_data();
            $this->output_html();
        }
         
        private function _bool($check,$success = 'Enabled',$fail = 'Disabled'){
            if($check === true){
                return $success;
            }
            return $fail;
        }

        private function get_theme_information($theme,$prefix = ''){
            $return = array();
            $return[$prefix.__("Theme Name")] = $theme->name;
            $return[$prefix.__("Theme Version")] = $theme->version;
            $return[$prefix.__("Theme URI")] = $theme->get('ThemeURI');
            $return[$prefix.__("Theme Description")] = $theme->description;
            $return[$prefix.__("Theme Author")] = $theme->get('Author');
            $return[$prefix.__("Theme AuthorURI")] = $theme->get('AuthorURI');
            $return[$prefix.__("Parent Theme")] = $theme->parent_theme;
            return $return;
        }
        
        private function get_plugin_information($plugin,$prefix = ''){
            $return = array();
            $return[$prefix.__("Name")] = $plugin['Name'];
            $return[$prefix.__("PluginURI")] = $plugin['PluginURI'];
            $return[$prefix.__("Version")] = $plugin['Version'];
            $return[$prefix.__("Author")] = $plugin['Author'];
            $return[$prefix.__("AuthorURI")] = $plugin['AuthorURI'];
            return $return;
        }

        private function get_host() {
            $host = false;
            if( defined( 'WPE_APIKEY' ) ) {
                $host = 'WP Engine';
            } elseif( defined( 'PAGELYBIN' ) ) {
                $host = 'Pagely';
            } elseif( DB_HOST == 'localhost:/tmp/mysql5.sock' ) {
                $host = 'ICDSoft';
            } elseif( DB_HOST == 'mysqlv5' ) {
                $host = 'NetworkSolutions';
            } elseif( strpos( DB_HOST, 'ipagemysql.com' ) !== false ) {
                $host = 'iPage';
            } elseif( strpos( DB_HOST, 'ipowermysql.com' ) !== false ) {
                $host = 'IPower';
            } elseif( strpos( DB_HOST, '.gridserver.com' ) !== false ) {
                $host = 'MediaTemple Grid';
            } elseif( strpos( DB_HOST, '.pair.com' ) !== false ) {
                $host = 'pair Networks';
            } elseif( strpos( DB_HOST, '.stabletransit.com' ) !== false ) {
                $host = 'Rackspace Cloud';
            } elseif( strpos( DB_HOST, '.sysfix.eu' ) !== false ) {
                $host = 'SysFix.eu Power Hosting';
            } elseif( strpos( $_SERVER['SERVER_NAME'], 'Flywheel' ) !== false ) {
                $host = 'Flywheel';
            } else {
                $host = 'DBH: ' . DB_HOST . ', SRV: ' . $_SERVER['SERVER_NAME'];
            }
            return $host;
        }
        
        public function set_wp_env_data(){
            global $wpdb,$WP_REMOTE_POST;
            $front_page_id = get_option( 'page_on_front' );
            $blog_page_id = get_option( 'page_for_posts' ); 
            $this->wp_env[__("Home URL")] = home_url(); 
            $this->wp_env[__("Site URL")] = site_url(); 
            $this->wp_env[__("WP Version")] = get_bloginfo( 'version' );
            $this->wp_env[__("WP Language")] = (defined( 'WPLANG' ) && WPLANG ? WPLANG : 'en_US' );
            $this->wp_env[__("Multisite")] = $this->_bool(is_multisite());
            $this->wp_env[__("WP Memory Limit")] = WP_MEMORY_LIMIT."MB";
            $this->wp_env[__("WP Table Prefix")] = $wpdb->prefix;
            $this->wp_env[__("WP Timezone")] = get_option('timezone_string') . ', GMT: ' . get_option('gmt_offset'); 
            $this->wp_env[__("WP Remote Post")] = ($WP_REMOTE_POST) ? __("Enabled") : __("Disabled");
            $this->wp_env[__("Permalink Structure")] = get_option( 'permalink_structure' );
            $this->wp_env[__("Registered Post Stati")] = implode(" , ",get_post_stati());
            $this->wp_env[__("Show On Front")] = get_option( 'show_on_front' );
            $this->wp_env[__("Page On Front")] = ( $front_page_id != 0 ? get_the_title( $front_page_id ) . ' (#' . $front_page_id . ')' : 'Unset' );
            $this->wp_env[__("Show On Front")] = ( $blog_page_id != 0 ? get_the_title( $blog_page_id ) . ' (#' . $blog_page_id . ')' : 'Unset' );
            $this->server_info[__("Server Info")] = $_SERVER['SERVER_SOFTWARE'];
            $this->server_info[__("Host")] = $this->get_host();
            $this->server_info[__("Default Timezone")] = date_default_timezone_get();
              
            if ( $wpdb->use_mysqli ) {
                $this->server_info[__("MySql Version")] = @mysqli_get_server_info( $wpdb->dbh );
            } else { 
                $this->server_info[__("MySql Version")] = @mysql_get_server_info(); 
            }
        }
        
        public function set_active_theme_data(){
            $active_theme = wp_get_theme(); 
            $active_theme = $this->get_theme_information($active_theme);
            $active_theme[__("Is Child Theme")] = is_child_theme() ? __("Yes") :  __("No");
            
            if( is_child_theme() ) {
                $parent_theme = wp_get_theme( $active_theme->Template );
                $active_theme = array_merge($active_theme,$this->get_theme_information($parent_theme,__("Parent Theme")));
            }
            
            $this->active_theme = $active_theme;
        }
        
        public function set_plugins_data(){
            $muplugins = wp_get_mu_plugins();
            
            if(!empty($muplugins)){
                foreach($muplugins as $plugin => $plugin_data){
                    $this->must_use_plugins[$plugin['Name']] = $this->get_plugin_information($plugin_data);
                }
            }
            
            if ( ! function_exists( 'get_plugins' ) ) { require_once ABSPATH . 'wp-admin/includes/plugin.php'; }
            
            $plugins = get_plugins();
            $active_plugins = get_option( 'active_plugins', array() );

            foreach( $plugins as $plugin_path => $plugin ) {
                if( in_array( $plugin_path, $active_plugins ) ){
                    continue;
                }
                $this->active_plugins[$plugin_path] = $this->get_plugin_information($plugin);
            }
            
            if(is_multisite()){
                $plugins = wp_get_active_network_plugins();
                $active_plugins = get_site_option( 'active_sitewide_plugins', array() );
                
                foreach( $plugins as $plugin_path ) {
                    $plugin_base = plugin_basename( $plugin_path );
                    if( !array_key_exists( $plugin_base, $active_plugins )){
                        continue;
                    }
                    $this->msite_plugins[$plugin_path] = $this->get_plugin_information(get_plugin_data( $plugin ));
                }
            }
        }
        
        public function set_php_data(){
            $this->php_info[__("PHP Version")] = PHP_VERSION;
            $this->php_info[__("PHP Post Max Size")] = ini_get( 'post_max_size' );
            $this->php_info[__("PHP Time Limit")] = ini_get( 'max_execution_time' );
            $this->php_info[__("PHP Max Input Vars")] = ini_get( 'max_input_vars' );
            $this->php_info[__("PHP Safe Mode")] = $this->_bool(ini_get( 'safe_mode' ));
            $this->php_info[__("PHP Memory Limit")] = ini_get( 'memory_limit' );
            $this->php_info[__("PHP Upload Max Size")] = ini_get( 'upload_max_filesize' );
            $this->php_info[__("PHP Arg Separator")] = ini_get( 'arg_separator.output' );
            $this->php_info[__("PHP Allow URL File Open")] = $this->_bool(ini_get( 'allow_url_fopen' ));
            $this->php_exts[__("Display Errors")] = $this->_bool(ini_get( 'display_errors' ));
            $this->php_exts[__("FSOCKOPEN")] = $this->_bool(function_exists( 'fsockopen' ));
            $this->php_exts[__("cURL")] = $this->_bool(function_exists( 'curl_init' ));
            $this->php_exts[__("SOAP Client")] = $this->_bool(function_exists( 'SoapClient' ));
            $this->php_exts[__("SUHOSIN")] = $this->_bool(function_exists( 'suhosin' ));
            $this->session_config[__("Session")] = $this->_bool(isset( $_SESSION ));
            $this->session_config[__("Session Name")] = esc_html( ini_get( 'session.name' ) );
            $this->session_config[__("Cookie Path")] = esc_html( ini_get( 'session.cookie_path' ) );
            $this->session_config[__("Save Path")] = esc_html( ini_get( 'session.save_path' ) );
            $this->session_config[__("Use Cookies")] = $this->_bool(ini_get( 'session.use_cookies' ),'Yes','No');
            $this->session_config[__("Use Only Cookies")] = $this->_bool(ini_get( 'session.use_only_cookies' ),'Yes','No');
        }
        
        public function get_final_array(){
            return array(
                __("WordPress Environment") => $this->wp_env,
                __("Server Environment") =>  $this->server_info,
                __("PHP Environment") => $this->php_info,
                __("WordPress Theme") => $this->active_theme,
                __("WordPress Plugins") => $this->plugins,
                __("WordPress Must Use Plugins") => $this->must_use_plugins,
                __("WordPress MultiSite Plugins") => $this->msite_plugins,
                __("PHP Extenstions") => $this->php_exts,
                __("Session Configs") => $this->session_config,
            );
        }
    
        public function headings(){
            return array(
                'wp_env' => __("WordPress Environment"),
                'server_info' => __("Server Environment"),
                'php_info' => __("PHP Environment"),
                'php_exts' => __("PHP Extenstions"),
                'session_config' => __("Session Config"),
                'active_theme' => __("Active Theme"),
                'active_plugins' => __("Active Plugins"),
                'msite_plugins' => __("Muilti Site Plugins"),
                'must_use_plugins' => __("WordPress Must Use Plugins"),
            );
        }
        
        public function output_html(){
            $heads = $this->headings();
            $deep_looper = array("msite_plugins","must_use_plugins",'active_plugins');
            $html_output = '';
            $text = '';
            foreach($heads as $var => $name){
                $is_exists = (isset($this->{$var})) ? true : false;
                if(!$is_exists){
                    continue;
                }
                
                $datas = array_filter($this->{$var});
                if(empty($datas)){
                    continue;
                }
                
                $text .= '## '.$name.' ##'.PHP_EOL;
                $html_output .= '<table  class="widefat striped fixed">';
                $html_output .= '<thead><tr><th colspan="2"><b>'.$name.'</b></th></tr></thead>';
                if(in_array($var,$deep_looper)){
                    foreach($datas as $i => $infos){
                        $i_ap = $i.' =>  { '.PHP_EOL;
                        $i_op = '<table class="widefat striped fixed">';
                        foreach($infos as $c => $v){
                            $i_ap .= $c.' : '.$v.PHP_EOL;
                            $i_op .= '<tr><th>'.$c.'</th><td>'.$v.'</td></tr>';
                        }           
                        $i_op .= '</table>';
                        $html_output .=  '<tr> <th>'.$i.'</th> <td>'.$i_op.'</td></tr>';
                        $text .= $i_ap.' } '.PHP_EOL.PHP_EOL;
                    }
                } else {
                    foreach($datas as $id => $val){
                        if(is_array($val)){
                            $val = json_encode($val);
                        }
                        $html_output .= '<tr> <th>'.$id.'</th> <td>'.$val.'</td></tr>';
                        $text .= $id.' : '.$val.PHP_EOL;
                    }
                }
                
                $text .= PHP_EOL;
                $html_output .= '</table> <br/>';
            }
            $this->text_output = $text;
            $this->html_output = $html_output;
        }
        
        public function get_output(){
            $title = __( 'To copy the System Status, click below then press Ctrl + C (PC) or Cmd + C (Mac)');
            $html = '<div class="updated woocommerce-message inline">';
            $html .= '<p>'.__("Please copy and paste this information in your ticket when contacting support: ").'</p>';
            $html .= '<textarea style="display:none; min-height:250px; width:100%;" readonly="readonly" onclick="this.focus();this.select()" id="wcqdssstextarea" title="'.$title.'">'.$this->text_output.'</textarea>';
            $html .= '<p class="submit">';
                $html .= '<a href="#" class="button-primary debug-report" id="vsp-sys-status-report-text-btn">'.__("Get system report").'</a>';
            $html .= '</p>';
            $html .= '</div>';
            $html .= $this->html_output;
            return $html;
        }
    }   
}