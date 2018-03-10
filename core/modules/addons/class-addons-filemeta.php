<?php
if( ! class_exists("VPS_Addons_FileMeta") ) {
    /**
     * Class VSP_Addons_FileMeta
     */
    class VSP_Addons_FileMeta extends VSP_Class_Handler {

        public function __construct() {
            $this->filedata = NULL;
            parent::__construct();
        }

        /**
         * @param $addon_file
         */
        private function __addon_duplicate_msg($addon_file) {
            $data = json_encode($addon_file);
            $msg  = sprintf(__("Duplicate Addon Found. Unable to load Please contact the developer with below information %s", 'vsp-framework'), date('h:i:s'));
            $msg  .= '<br/> <pre><code>' . $data . '</code></pre>';
            vsp_notice_error($msg, 1, '', array( $this->settings_pagehook ));
        }

        /**
         * @param $addon_file
         * @return bool|mixed
         */
        private function get_file_metadata($addon_file) {
            $meta_data = $this->get_plugin_meta($addon_file['full_path']);

            if( empty ($meta_data['Name']) ) {
                return FALSE;
            }

            $meta_data['addon_path']      = rtrim(dirname($addon_file['full_path']), '/') . '/';
            $meta_data['addon_path_md5']  = md5($meta_data['addon_path']);
            $meta_data['addon_subfolder'] = $addon_file['sub_folder'];
            $meta_data['addon_file']      = $addon_file['file_name'];
            $meta_data['addon_slug']      = empty($meta_data['addon_subfolder']) ? sanitize_title($meta_data['addon_file']) : sanitize_title($meta_data['addon_subfolder']);
            $meta_data['addon_url']       = $this->option("base_url") . $addon_file['sub_folder'];

            if( empty($meta_data['icon']) ) {
                $meta_data['icon'] = $this->search_addon_icon($meta_data);
            }

            $meta_data['screenshots']     = $this->search_addon_screenshots($meta_data);
            $meta_data['is_active']       = FALSE;
            $meta_data                    = $this->extract_registered_shortcodes($meta_data);
            $meta_data['category']        = $this->handle_addon_category($meta_data['category']);
            $meta_data['category-slug']   = array_keys($meta_data['category']);
            $meta_data['addon_file_slug'] = $meta_data['addon_subfolder'] . $meta_data['addon_file'];
            return $meta_data;
        }

        /**
         * @param $addons_files
         * @return bool
         */
        public function get_metadata($addons_files) {
            if( ! is_array($addons_files) ) {
                return FALSE;
            }

            foreach( $addons_files as $addon_file ) {

                if( isset($this->addon_metadatas[$addon_file['sub_folder'] . $addon_file['file_name']]) ) {
                    $this->__addon_duplicate_msg($addon_file);
                    continue;
                }

                if( ! is_readable($addon_file['full_path']) ) {
                    continue;
                }

                $meta_data                                            = $this->get_file_metadata($addon_file);
                $this->addon_metadatas[$meta_data['addon_file_slug']] = $meta_data;
            }

            return $this->addon_metadatas;
        }

        /**
         * @param      $file
         * @param bool $markup
         * @param bool $translate
         * @return mixed
         */
        private function get_plugin_meta($file, $markup = TRUE, $translate = TRUE) {
            $headers = $this->parse_args($this->option("file_headers"), $this->get_default_headers());
            $data    = $this->render_file_headers($file, $headers);

            if( empty($data['TextDomain']) ) {
                $data['TextDomain'] = '';
            }
            if( empty($data['DomainPath']) ) {
                $data['DomainPath'] = FALSE;
            }
            if( empty($data['category']) ) {
                $data['category'] = array( 'general' );
            }
            $data['category-slug'] = sanitize_key($data['category']);
            $data['PluginURI']     = $data['addon_url'];

            if( $markup || $translate ) {
                $data = vsp_addon_data_markup($file, $data, $markup, $translate);
            }

            return $data;
        }

        /**
         * @param $file
         * @return bool|string
         */
        protected function read_file_data($file) {
            if( file_exists($file) ) {
                $fp        = fopen($file, 'r');
                $file_data = fread($fp, 8192);
                fclose($fp);
                $this->filedata = $file_data;
                return $file_data;
            }
            return $file;
        }

        /**
         * @param        $file
         * @param        $default_headers
         * @param string $context
         * @return mixed
         */
        protected function render_file_headers($file, $default_headers, $context = '') {
            $file_data   = $this->read_file_data($file);
            $file_data   = str_replace("\r", "\n", $file_data);
            $all_headers = $default_headers;
            foreach( $all_headers as $field => $regex ) {
                if( preg_match('/^[ \t\/*#@]*' . preg_quote($regex, '/') . ':(.*)$/mi', $file_data, $match) && $match[1] )
                    $all_headers[$field] = _cleanup_header_comment($match[1]); else
                    $all_headers[$field] = '';
            }

            return $all_headers;
        }

        /**
         * @param $meta_data
         * @return mixed
         */
        protected function extract_registered_shortcodes($meta_data) {
            $codes          = array(
                'REQUIRED_PLUGINS' => __("Required Plugins", 'vsp-framework'),
                'SCREENSHOTS'      => __("Screenshots", 'vsp-framework'),
                'FAQ'              => __("FAQ", 'vsp-framework'),
            );
            $reg_shortcodes = vsp_addons_extract_tags($this->filedata);

            if( ! empty($reg_shortcodes[1]) ) {
                foreach( $reg_shortcodes[1] as $sc ) {
                    if( isset($codes[$sc]) ) {
                        $ctn      = vsp_addons_extract_tags_pattern($sc, $this->filedata);
                        $callback = 'extract_' . strtolower($sc);
                        if( method_exists($this, $callback) ) {
                            $meta_data[strtolower($sc)] = $this->$callback($ctn, $meta_data);
                        } else {
                            var_dump($callback);
                        }
                    }
                }
            }

            return $meta_data;
        }

        /**
         * @param $content
         * @param $meta
         * @return array
         */
        private function extract_screenshots($content, $meta) {
            $screens = $this->search_addon_screenshots($meta);
            $text    = str_replace(array( "\r\n", "\r" ), "\n", $content[5]);
            $text    = trim($text, "\n");
            $lines   = explode("\n", $text);
            $return  = array();

            foreach( $lines as $line ) {
                $line = trim($line, ' * ');
                $line = trim($line, ' ');
                if( ! empty($line) ) {
                    preg_match('/(\[((?:[^][]++|(?R))*+)]) ((?:[^][]++|(?R))*+)/', $line, $info);
                    if( ! empty($info[2]) ) {

                        if( filter_var($info[2], FILTER_VALIDATE_URL) === FALSE ) {
                            if( isset($screens[$info[2]]) ) {
                                $return[$info[2]] = array(
                                    'title' => isset($info[3]) ? $info[3] : '',
                                    'url'   => ( isset($screens[$info[2]]) ) ? $screens[$info[2]] : "",
                                );
                            }

                        } else {
                            $return[basename($info[2])] = array(
                                'title' => isset($info[3]) ? $info[3] : '',
                                'url'   => $info[2],
                            );
                        }
                    }
                }
            }

            return array_merge($screens, $return);
        }

        /**
         * @param $meta
         * @return array
         */
        private function search_addon_screenshots($meta) {
            $file_formats = array( 'screenshot', 'addon-screenshot' );
            $file_exts    = array( 'jpg', 'png', 'gif' );
            $return       = array();
            foreach( $file_formats as $name ) {
                foreach( $file_exts as $ext ) {
                    $files = vsp_get_file_paths($meta['addon_path'] . '*' . $name . '*.' . $ext);
                    if( ! empty($files) ) {
                        foreach( $files as $file ) {
                            $f          = basename($file);
                            $return[$f] = $meta['addon_url'] . $f;
                        }
                    }
                }
            }

            return $return;
        }

        /**
         * @param $meta
         * @return bool|string
         */
        protected function search_addon_icon($meta) {
            $name_formats = array(
                'icon',
                $meta['addon_slug'],
                $meta['addon_slug'] . '-icon',
                'icon-' . $meta['addon_slug'],
            );
            $file_formats = array( 'jpg', 'png', 'gif' );
            $return       = FALSE;

            foreach( $name_formats as $name ) {
                foreach( $file_formats as $fom ) {
                    $ff = $name . '.' . $fom;
                    if( file_exists($meta['addon_path'] . $ff) ) {
                        $return = $meta['addon_url'] . $ff;
                        break;
                    }
                }
            }

            if( $return === FALSE ) {
                $return = vsp_img("noimage.png");
            }

            return $return;
        }

        /**
         * @param $content
         * @param $meta
         * @return array
         */
        protected function extract_required_plugins($content, $meta) {
            //preg_match_all( '@\[([^<>&\[\]\x00-\x20=]++)@',$content[5], $reg_shortcodes );
            $reg_shortcodes = vsp_addons_extract_tags($content[5], TRUE);
            $return_array   = array();
            $total_active   = 0;
            if( ! empty($reg_shortcodes[1]) ) {
                foreach( $reg_shortcodes[1] as $sc ) {
                    $data = vsp_addons_extract_tags_pattern($sc, $content[5], TRUE);
                    if( ! empty($data[5]) ) {
                        $info = $this->render_file_headers($data[5], array(
                            'name'             => 'Name',
                            'author'           => 'Author',
                            'required_version' => 'Required Version',
                            'url'              => 'URL',
                            'slug'             => "Slug",
                        ));

                        if( empty($info['slug']) ) {
                            $info['slug'] = $data[2];
                        }

                        $info['status'] = $this->check_plugin_status($info['slug']);
                        if( $info['status'] == 'activated' ) {
                            $total_active++;
                        }
                        $return_array[$info['slug']] = $info;
                    }
                }
            }

            $total_req    = count($return_array);
            $is_fullfiled = ( $total_req == $total_active );
            return array( "plugins" => $return_array, 'fulfilled' => $is_fullfiled );
        }
    }
}