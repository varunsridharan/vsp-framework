<?php
if(!defined("ABSPATH")){ die; }

if(!class_exists("VSP_Settings_Handler")){
    class VSP_Settings_Handler extends VSP_Class_Handler {
        
        protected $default_options = array(
            'hook_slug' => '',
            'settings_page_slug' => '',
            'db_slug' => '',
        );
        
        protected static $_instance = null;
        
        public static function get_instance() {
            if ( null == self::$_instance ) {
                self::$_instance = new self;
            }
            return self::$_instance;
        }
        
        public function __construct(){
            parent::__construct();
            $this->settings_tabs = array();
            $this->pages = array();
            $this->register_settings = array();
            $this->page_slugs = array();
            $this->page_ids = array();
            $this->fields = array();
            $this->debug = '';
            $this->js_data = array();
            $this->set_option("current_page",'');
            $this->set_option("multiple_forms",false);
            $this->set_option("label_for",array('text','select','textarea'));
        }
        
        private function debug($msg){
            $this->debug .= $msg.'<br/>';
        }
        
        private function init_fields_class(){
            return $this->fields = VSP_Settings_Fields::get_instance();
        }
        
        public function init($pages = '',$options = array()){
            if(!empty($pages)){
                $this->pages = (array) $pages;
            }
            
            $options = $this->parse_args($options,$this->default_options);
            $options = array_map("vsp_fix_slug",$options);
            $this->set_option("hook_slug",$options['hook_slug']);
            $this->set_option("settings_page_slug",$options['settings_page_slug']);
            $this->set_option("db_slug",$options['db_slug']);
            $this->set_option("current_page",$this->get_current_page());
            $cpage = $this->option('current_page');
            
            if(isset($cpage['multiform']) && $cpage['multiform']){
                $is_yes = ( count($cpage['sections'] )  > 1 ) ? true : false;
                $this->option("multiple_forms",$is_yes);
            }
            
            $this->init_fields_class();
            $label_for = $this->filter("label_for",$this->option("label_for"));
            $this->set_option("label_for",$label_for);
            $this->add_settings_sections();
            $this->register_settings();
        }
        
        public function add_settings_sections(){
            $page = $this->option("current_page");
            if(isset($page['sections']) && is_array($page['sections'])){
                foreach($page['sections'] as $sec){
                    $section_desc = '__return_false';
                    
                    if(isset($sec['desc']) && $sec['desc']){
                        $section_desc = array($this,'render_section_description');
                    }

                    $title = (isset($sec['title'])) ? $sec['title'] : "";
                    $page_id = ($this->option("multiple_forms")) ?  $sec['id'] : $page['id'];
                    $page_id = $this->option('settings_page_slug').'_'.$page_id;
                    $section_id = $this->option("db_slug").'_'.$sec['id'];
                    $this->debug("Database Option(s) created for this page");
                    $this->debug("database Option : ".$section_id);

                    add_settings_section($section_id,$title,$section_desc,$page_id);

                    if(isset($sec['fields']) && ! empty($sec['fields'])){
                        $this->add_settings_fields($section_id,$sec['fields'],$page_id);
                    }
                }
                return true;
            }
            return false;
        }
        
        public function add_settings_fields($sec_id,$fields,$page_id){
            $defaults = array(
                'tooltip'      => false,
                'popover'      => false,
                'popover_title' => '',
                'popover_content' => '',
                'tooltip_content' => '',
                'section'      => $sec_id,
                'id'           => '',
                'type'         => '',
                'label'        => '',
                'desc'         => '',
                'size'         => false,
                'options'      => '',
                'default'      => '',
                'content'      => '',
                'attr'         => array(),
                'before'       => '',
                'after'        => '',
                'multiple'     => false,
                '_type'        => '',
                'data'         => array(),
                'input_class' => array(),
            );
            $opt_defaults = array();
            //$use_defaults = (false === get_option($sec_id)) ? true : false;
            
            $use_defaults = (false === $this->fields->cache_options($sec_id)) ? true : false;
            
            foreach($fields as $field){
                $args = $this->parse_args($field,$defaults);
                $args = $this->fix_attributes($args);
                $args['default'] = ($use_defaults) ? $args['default'] : "";
                $opt_defaults[$args['id']] = $args['default'];
                
                if(in_array($args['type'],$this->option("label_for"))){
                    $args['label_for'] = $sec_id.'_'.$args['id'];
                }
                
                if(!isset($args['attr']['id'])){
                    $args['attr']['id'] = $sec_id.'_'.$args['id'];
                }
                
                $old_name = isset($args['attr']['name']) ? $args['attr']['name'] : "";
                $args['attr']['name'] = $sec_id.'['.$args['id'].']'.$old_name;
                
                if(!$this->fields->has_method('callback_'.$field['type'])){
                    $args['callback'] = $field['type'];
                    $args['page_hook'] = $this->db_slug();
                    $field['type'] = 'extra_field';
                }
                
                if($this->fields->has_method('callback_'.$field['type'])){
                    $label = isset($args['label']) ? $args['label'] : "";
                    $callback = array($this->fields,'callback_'.$field['type']);
                    add_settings_field( $this->db_slug().'['.$field['id'].']', $label,$callback, $page_id, $sec_id, $args );
                }
            }
            
            if($use_defaults === true){
                add_option($sec_id,$opt_defaults);
            }
        }
        
        public function register_settings(){
            foreach($this->register_settings as $data){
                $data[0] = $this->db_slug().'_'.$data[0];
                $data[1] = $this->db_slug().'_'.$data[1];
                register_setting($data[0],$data[1],$data[2]);
            }
        }
        
        protected function _fix_tooltip_popover_data($type = '',$args){
            if($args[$type] !== false){
                $content = '';
                if($args[$type] === true && empty($args[$type.'_content'])){
                    $args[$type.'_content'] = $args['desc'];
                    $args['desc'] = '';
                } else {
                    $args[$type.'_contnt'] = $args[$type];
                }
                
                $args['attr']['data-toggle-'.$type] = 'true';
            }
            return $args;
        }
        
        protected function fix_attributes($args = array()){
            if($args['multiple'] === true && $args['type'] === 'select'){
                $args['attr']['multiple'] = true;
            } else if($args['multiple'] === true && $args['type'] === 'checkbox'){
                $args['type'] = 'multicheckbox';
            }
            
            if(is_array($args['data']) && ! empty($args['data'])){
                foreach($args['data'] as $i => $v){
                    $args['attr']['data-'.$i] = $v;
                }
                unset($args['data']);
            }
            
            if(!empty($args['input_class'])){
                $args['attr']['class'] = (is_array($args['input_class'])) ? implode(" ",$args['input_class']) : $args['input_class'];
                unset($args['input_class']);
            }
            
            if(isset($args['style'])){
                $args['attr']['style'] = $args['style'];
            }
            
            $args = $this->_fix_tooltip_popover_data("tooltip",$args);
            $args = $this->_fix_tooltip_popover_data("popover",$args);
            
            return $args;
        }
        
        public function get_current_page() {
            $current_page = $cpage = null;
            if(isset($_GET['tab']) && $_GET['tab']){
                $tab = $_GET['tab'];
                if(isset($this->page_slugs[$tab])){
                    $cpage = $this->page_slugs[$tab];
                } else if(isset($this->page_ids[$tab])){
                    $cpage = $this->page_ids[$tab];
                }
                $current_page = isset($this->pages[$cpage]) ? $this->pages[$cpage] : current($this->pages);
            } else{
                $current_page = current($this->pages);
            }
            return $current_page;
		}
        
        public function add_page( $page ) {
            $rand = time();
            $this->pages[$page['id'].$rand] = $page;
            $this->page_slugs[$page['slug']] = $page['id'].$rand;
            $this->page_ids[$page['id']] = $page['id'].$rand;
            $this->settings_tabs[$page['slug']] = array('id' => $page['id'],'title' => $page['title'],'array_id' => $page['id'].$rand);
			return $this->pages;
		}

        public function add_pages( $pages ) {
			foreach ( $pages as $page ) {
				$this->add_page( $page );
			}
			return $this->pages;
		}
        
		public function add_section( $page, $section ) {
			foreach ( $this->pages as $key => $_page ) {
				if ( $page !== $_page['id'] ) {
					continue;
				}

				if ( isset( $this->pages[ $key ][ $page ]['sections'] ) ) {
					$this->pages[ $key ]['sections'] = array();
				}

				$this->pages[ $key ]['sections'][] = $section;
                
                if(vsp_is_request('admin') || vsp_is_request('ajax')){
                    $pgid = $_page['id'];
                    $valid_callback = null;

                    if(isset($_page['multiforms']) && $_page['multiforms']){
                        $pgid = (count($_page['sections']) > 1) ? $section['id'] : $_page['id'];
                    }

                    if(isset($section['callback']) && $section['callback']){
                        $valid_callback = $section['callback'];
                    }

                    $this->register_settings[$_page['id'].'_'.$section['id']] = array($pgid,$section['id'],$valid_callback);
                }
			}

			return $this->pages;
		}

        public function add_sections( $page, $sections ) {
			foreach ( $sections as $section ) {
				$this->pages = $this->add_section( $page, $section );
			}
			return $this->pages;
		}

        public function add_field( $page, $section, $field ) {
			foreach ( $this->pages as $key => $_page ) {
				if ( $page !== $_page['id'] ) {
					continue;
				}

				if ( !isset( $this->pages[ $key ]['sections'] ) ) {
					continue;
				}

				$_sections = $this->pages[ $key ]['sections'];

				foreach ( $_sections as $_key => $_section ) {
					if ( $section !== $_section['id'] ) {
						continue;
					}

					if ( !isset( $this->pages[ $key ]['sections'][ $_key ]['fields'] ) ) {
						$this->pages[ $key ]['sections'][ $_key ]['fields'] = array();
					}

					$this->pages[ $key ]['sections'][ $_key ]['fields'][] = $field;
				}
			}

			return $this->pages;
		}
        
		public function add_fields( $page, $section, $fields ) {
			foreach ( $fields as $field ) {
				$this->pages = $this->add_field( $page, $section, $field );
			}
			return $this->pages;
		}
        
        public function render_header($page_title = '', $tab_id = false){
            if(!empty($page_title)){
                echo get_screen_icon().'<h2>'.$page_title.'</h2>';
            }
            
            $html = '<h2 class="nav-tab-wrapper">';
            
            $current_page = $this->option("current_page");
            $current_page = $current_page['id'];
            
            $tab_url = remove_query_arg(array('tab','settings-updated'));
            foreach($this->settings_tabs as $slug => $page){
                $ltburl = add_query_arg('tab',$slug,$tab_url);
                $html .= sprintf('<a href="%1$s" class="nav-tab %2$s" id="%3$-tab">%4$s</a>',
                                esc_url($ltburl),
                                 ($current_page === $page['id']) ? 'nav-tab-active' : '',
                                 esc_attr($page['id']),
                                 $page['title']
                                );
            }
            
            $html .= '</h2>';
            echo $html;
        }
        
        public function render_form(){
            $page = $this->option("current_page");
            
            if(!empty($page)){
                $section_ids = '';
                $forms = array($page);
                
                if(isset($page['sections'])){
                    $ids = wp_list_pluck($page['sections'],'id');
                    $forms = ($this->option("multiple_forms")) ? $page['sections'] : array($page);
                    
                    foreach($ids as $id){
                        $section_ids .= sprintf('<input type="hidden" name="%1$s_%2$s[section_id]" value="%2$s" id="%1$s_%2$s_section_id"/>',$this->settings_page_slug(),$id);
                    }
                }
                
                foreach($forms as $form){
                    $this->output('<form method="post" action="options.php">');
                    $this->output($section_ids);
                    
                    $filter_output = $this->filter("form_fields",'',$form['id'],$form);
                    
                    if(!empty($filter_output)){
                        $this->output($filter_output);
                    } else {
                        $this->cache_output('start');
                            settings_fields($this->db_slug().'_'.$form['id']);
                        $this->output($this->cache_output('end'));
                        
                        $this->do_settings_sections($this->settings_page_slug().'_'.$form['id']);
                    }
                    
                    $submit = (isset($form['submit']) && $form['submit']) ? $form['submit'] : '';
                    
                    if(empty($submit) && isset($page['submit']) && $page['submit']){
                        $submit = $page['submit'];
                    }
                    
                    $defaults = array('text' => null,'type' => 'primary', 'name' => 'submit');
                    $sarg = $this->parse_args($submit,$defaults);
                    
                    $this->output(get_submit_button($sarg['text'],$sarg['type'],$sarg['name'],true,array('id' => $form['id'])));
                    $this->output('</form>');
                }
                
                $this->output(vsp_js_vars('vspFrameWork_Settings',$this->js_data));
               // $this->output('<script type="text/javascript">');
               // $this->output('/* <![CDATA[*/'.$this->localize('vspFrameWork_Settings',$this->js_data));
               // $this->output('/*]]>*/ </script>');
                
                $ulli = $this->_echo_output('sub_tabs',true);
                
                return array("form" => $this->_echo_output(),'sub_tabs' => $ulli);
            }
        }
        
        public function render_section_description( $section ) {
			$current_page = $this->option("current_page");
			foreach ( $current_page['sections'] as $setting ) {
				if ( $this->db_slug() .'_'. $setting['id'] === $section['id'] )
					echo $setting['desc'];
			}
		}
        
        protected function do_settings_sections($page){
            global $wp_settings_sections,$wp_settings_fields;
            
            if(!isset($wp_settings_sections[$page])){
                return;
            }
            
            $this->output("<ul class='subsubsub vsp_settings_subtab'>",'sub_tabs');
            
            foreach((array) $wp_settings_sections[$page] as $section){
                $this->output('<li><a href="#'.$section['id'].'">'.$section['title'].'</a> | </li>','sub_tabs');
                
                $this->output('<div id="settings_'.$section['id'].'" class="hidden vsp_settings_content">','settings_section');
                
                if($section['title']){
                    $this->output('<h2>'.$section['title'].'</h2>','settings_section');
                }
                
                $this->cache_output('start');
                
                if($section['callback']){
                    call_user_func($section['callback'],$section);
                }
                $this->output($this->cache_output('end'),'settings_section');
                
                if (!isset($wp_settings_fields) || !isset($wp_settings_fields[$page]) || !isset($wp_settings_fields[$page][$section['id']])){
                    $this->output('</div>','settings_section');
                    continue;
                }
                
                $this->output('<table class="form-table">','settings_section');
                $this->cache_output('start');
                $this->do_settings_fields($page,$section['id']);
                $this->output($this->cache_output('end'),'settings_section');
                $this->output('</table>','settings_section');
                $this->output('</div>','settings_section');
            }
            
            $this->output("</ul>",'sub_tabs');

            if(count($wp_settings_sections[$page]) > 1){
               //$ulli = $this->_echo_output('sub_tabs',true).'<br/>';
               //$this->output($ulli);
            }
            $this->output($this->_echo_output('settings_section',true));
        }
        
        protected function do_settings_fields($page,$section){
            global $wp_settings_fields;
            if(!isset($wp_settings_fields[$page][$section])){
                return;
            }
            
            foreach($wp_settings_fields[$page][$section] as $f){
                $class = '';
                $this->get_js_variable_infos($f);
                if(!empty($f['args']['class'])){
                    $class = ' class="'.esc_attr($f['args']['class']).'" ';
                }
            
                echo "<tr {$class}>";
                $label = $f['title'];
                if(!empty($f['args']['label_for'])){
                    $label = '<label for="'.esc_attr($f['args']['label_for']).'">'.$f['title'].'</label>';
                }
                
                echo '<th scope="row">'.$label.'</th>';
                echo '<td>';
                call_user_func($f['callback'],$f['args']);
                echo '</td>';
                echo '</tr>';
            }
        }
        
        private function get_js_variable_infos($f){
            $is_tooltip = ($f['args']['tooltip'] !== false ) ? true : false;
                
            if($is_tooltip){
                $this->js_data['tooltip'][$f['args']['section'].'_'.$f['args']['id']] = $f['args']['tooltip_content'];
            }

            $is_popover = ($f['args']['popover'] !== false ) ? true : false;

            if($is_popover){
                $this->js_data['popover'][$f['args']['section'].'_'.$f['args']['id']]['content'] = $f['args']['popover_content'];
                $this->js_data['popover'][$f['args']['section'].'_'.$f['args']['id']]['title'] = $f['args']['popover_title'];
            }
        }
    }
}