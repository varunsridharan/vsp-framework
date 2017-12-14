<?php
if(!defined("ABSPATH")){ exit; }

if(!class_exists("VSP_Addons_Core")){
    class VSP_Addons_Core extends VSP_Addons_Detailed_View {
        protected $addon_cats = array();
        
        public function __construct(){
            $this->default_cats = array(
                'all' => __("All"),
                'active' => __("Active"),
                'inactive' => __("In Active"),
            );
            $this->addon_cats = $this->default_cats;
            
            $this->addons_cats_count = array(
                'all' => 0,
                'active' => 0,
                'inactive' => 0,
            );
            parent::__construct();
        }
        
        public function search_get_addons($single_addon = false){
            $this->addon_metadatas = array();
            $dirs = apply_filters($this->hook_slug.'_addons_dirs',array());
            $internal_addons = $this->search_plugins($this->option("base_path"),$single_addon);
            $internal_addons = $this->get_metadata($internal_addons);
            $external_addons = array();

            if(!empty($dirs)){
                foreach($dirs as $dir){
                    $addons = $this->search_plugins($dir,$single_addon);
                    $addons = $this->get_metadata($addons);
                }
            }
            
            return $this->addon_metadatas;
        }
        
        public function search_get_addon($addon_slug = false,$path_id = ''){
            $addons = $this->search_get_addons($addon_slug);
            $return_data = array();
            if(!empty($addons)){
                foreach($addons as $slug => $data){
                    if($slug == $addon_slug){
                        if($path_id == md5($data['addon_path'])){
                            $return_data = $data;
                            break;
                        }
                    }
                }
            }
            
            return $return_data;
        }
        
        protected function file_file_name($file,$search = '.php',$replace= ''){
            return str_replace($search,$replace,$file);
        }
        
        protected function handle_addon_category($category){
            $category = explode(",",$category);
            $return = array();
            foreach($category as $cat){
                $cat = $this->strip_space($cat,' ');
                $slug = sanitize_title($cat);
                $return[$slug] = $cat;
                
                if(!isset($this->addon_cats[$slug])){
                    $this->addon_cats[$slug] = $cat;
                    $this->addons_cats_count[$slug] = 1;
                } else {
                    $this->addons_cats_count[$slug] = $this->addons_cats_count[$slug] + 1;
                }
            }
            
            $this->addons_cats_count['all'] = $this->addons_cats_count['all'] + 1;
            
            return $return;           
        }
        
        public function search_addon($addon_path){
            $dirs = apply_filters($this->hook_slug.'_addons_dirs',array());
            
            
        }
        
        public function search_plugins($search_path,$single_addon = false,$subpath = ''){
            $search_path = rtrim($search_path,'/');
            $subpath = rtrim($subpath,'/');
            $r = array();
            
            if(!empty($search_path)){
                $_dir = @ opendir($search_path.$subpath);
                
                if($_dir){
                    while(($file = readdir($_dir)) !== false){
                        if(substr($file,0,1) == '.'){
                            continue;
                        }

                        $_ipath = $search_path.'/'.$file;
                        if(is_dir($_ipath)){
                            $r = array_merge($r,$this->search_plugins($search_path,$single_addon,'/'.$file));
                        } else {
                            if($single_addon !== false){
                                $single_addon = ltrim($single_addon,'/');
                                if($search_path.$subpath.'/'.$file !== $search_path.'/'.$single_addon){
                                    continue;
                                }
                            }
                            
                            if(substr($file, -4) == '.php'){
                                $r[] = array(
                                    'full_path' =>  $search_path.$subpath.'/'.$file,
                                    'sub_folder' => $subpath.'/',
                                    'file_name' => $file,
                                );
                            }
                        }
                        
                        
                    }
                    closedir($_dir);
                }
            }
            
            return $r;
        }
        
        protected function get_default_headers(){
            return array(
                'Name' => 'Addon Name',
                'addon_url' => 'Addon URI',
                'icon' => 'Addon icon',
                'Version' => 'Version',
                'Description' => 'Description',
                'Author' => 'Author',
                'AuthorURI' => 'Author URI',
                'last_updated' => 'Last updated',
                'created_on' => 'Created On',
                'category' => 'Category',
            );
        }
        
        protected function fix_addon_metadata($meta){
            $meta = $this->_extract_required_plugins($meta);
            return $meta;
        }
        
        protected function strip_space($string,$char){
            $string = ltrim($string,$char);
            $string = rtrim($string,$char);
            return $string;
        }
        
        protected function check_plugin_status($slug){
            if(!function_exists("validate_plugin")){
                require_once(ABSPATH.'wp-admin/includes/plugin.php');
            }
            $val_plugin = validate_plugin($slug);
            if(is_wp_error($val_plugin)){ return 'notexist'; } 
            else if(is_plugin_active($slug)){ return 'activated'; } 
            else if(is_plugin_inactive($slug)){ return 'exists'; }
            return false;
        }
        
        protected function _extract_required_plugins($meta){
            
            if(empty($meta['rplugins'])){
                $_rplugins = array();
                $_rpc = 1;
                $_apc = 1;
            } else {
                $rplugins = $meta['rplugins'];
                $_rpc = count($rplugins);
                $_apc = 1;
                $r_plugins_a = explode(',',$rplugins);
                $_rplugins = array();
                foreach($r_plugins_a as $r_plugin){
                    $r_plugin = $this->strip_space($r_plugin,' ');
                    $r_plugin = $this->strip_space($r_plugin,']');
                    $r_plugin = $this->strip_space($r_plugin,'[');
                    $r_plugin = $this->strip_space($r_plugin,' ');

                    $r_plugin = explode("|",$r_plugin);
                    if(is_array($r_plugin)){
                        $pd = array();
                        foreach($r_plugin as $data){
                            $data = $this->strip_space($data,' ');
                            $data = explode(":",$data,2);

                            if(count($data) > 1){
                                if(isset($data[0])){
                                    $key = strtolower($this->strip_space($data[0],' '));
                                    $value = $this->strip_space($data[1],' ');
                                    $pd[$key] = $value;
                                }
                            }
                        }

                        if(!empty($pd)){
                            $pd['status'] = $this->check_plugin_status($pd['slug']);
                            if($pd['status'] === 'activated'){
                                $_apc = $_apc + 1;
                            }
                            $_rplugins[$pd['slug']] = $pd;
                        }
                    }
                }
            }
            $meta['rplugins'] = $_rplugins;
            $meta['requirement_fullfiled'] = ($_rpc == $_apc);
            return $meta;
        }
        
        protected function get_plugin_status_label($status = false){
            if($status === 'exists'){
                return __("In Active");
            }
            if($status === 'notexist'){
                return __("Not Exist");
            }
            
            if($status === 'activated'){
                return __("Active");
            }
        }
    }
}