<?php
if(!defined("ABSPATH")){ exit; }

if ( !class_exists( 'VSP_WP_Settings_Fields' ) ) {
	class VSP_WP_Settings_Fields {
		public $settings_errors;

        public function __construct( $errors = array() ) {
			$this->settings_errors = (array) $errors;
		}
        
        public function has_method($key){
            if(method_exists($this,$key)){
                return true;
            }
            return false;
        }

        public function create_element($type = '',$args){
            $tag = '';
            $value = esc_attr($this->get_option($args));
            $error = $this->get_setting_error( $args['id'] );
            
            switch($type){
                case 'text':
                    $tag = '<input type="'.$args['text_type'].'" '.$args['attr'].' '.$error.' value="'.$value.'"/>';
                break;                    
                case 'textarea':
                    $tag = '<textarea '.$args['attr'].' '.$error.' >'.$value.'</textarea>';
                break;
                case 'checkbox' :
                    $dfValue = isset($args['bx_value']) ? $args['bx_value'] : 'on';
                    $is_checked = ($dfValue === $value) ? ' checked="checked" ' : "";
                    
                    if(isset($args['is_checked']) && !empty($args['is_checked'])){
                        $is_checked = $args['is_checked'];
                    }
                    
                    $tag = '<input type="'.$type.'" '.$args['attr'].' '.$is_checked.' value="'.$dfValue.'"/>';
                break;
            }
            return $tag;
            
        }
        
        protected function callback_textfield($args,$type = 'text'){
            $args['size'] = ( isset( $args['size'] ) && $args['size'] ) ? $args['size'] : 'regular';
            $args['text_type'] = isset( $args['text_type'] ) ? esc_attr( $args['text_type'] ) : $type;
            $args = $this->get_arguments($args);
            $tag = $this->create_element('text',$args);
            return $args['before'].$tag.$args['after']. $this->description( $args['desc'] );
        }

        public function callback_text($args){echo $this->callback_textfield($args,'text');}
        
        public function callback_search($args){echo $this->callback_textfield($args,'search');}
        
        public function callback_tel($args){echo $this->callback_textfield($args,'tel');}
        
        public function callback_url($args){echo $this->callback_textfield($args,'url');}
        
        public function callback_email($args){echo $this->callback_textfield($args,'email');}
        
        public function callback_password($args){echo $this->callback_textfield($args,'password');}
        
        public function callback_datetime($args){echo $this->callback_textfield($args,'datetime');}
        
        public function callback_date($args){echo $this->callback_textfield($args,'date');}
        
        public function callback_month($args){echo $this->callback_textfield($args,'month');}
        
        public function callback_week($args){echo $this->callback_textfield($args,'week');}
        
        public function callback_time($args){echo $this->callback_textfield($args,'time');}
        
        public function callback_datetime_local($args){echo $this->callback_textfield($args,'datetime_local');}
        
        public function callback_number($args){echo $this->callback_textfield($args,'number');}
        
        public function callback_range($args){echo $this->callback_textfield($args,'range');}
        
        public function callback_color($args){echo $this->callback_textfield($args,'color');}
        
        public function callback_textarea( $args ) {
			$size  = ( isset( $args['size'] ) && $args['size'] ) ? $args['size'] : 'regular';
			$args  = $this->get_arguments( $args ); // escapes all attributes
			$value = (string) esc_textarea( $this->get_option( $args ) );
			$error = $this->get_setting_error( $args['id'] );
			$html  = sprintf( '<textarea id="%1$s_%2$s" name="%1$s[%2$s]"%4$s%5$s>%3$s</textarea>', $args['section'], $args['id'], $value, $args['attr'], $error );
			echo $args['before'] . $html . $args['after'] . $this->description( $args['desc'] );
		}
        
        public function callback_select( $args ) {
            $args  = $this->check_options_type($args);
			$args  = $this->get_arguments( $args ); // escapes all attributes
			$value = array_map( 'esc_attr', array_values( (array) $this->get_option( $args ) ) );
			$multiple = ( preg_match( '/multiple="multiple"/', strtolower( $args['attr'] ) ) ) ? '[]' : '';
			$value = ( '[]' === $multiple ) ? $value : $value[0];
			$html  = sprintf( '<select id="%1$s_%2$s" name="%1$s[%2$s]%4$s"%3$s>', $args['section'], $args['id'], $args['attr'], $multiple );
            $html .= $this->handle_options($args['options'],$value);
			$html .= sprintf( '</select>' );
			echo $args['before'] . $html . $args['after'] . $this->description( $args['desc'] );
		}
        
        public function callback_checkbox( $args ) {
			$args  = $this->get_arguments( $args ); // escapes all attributes
			$value = (string) esc_attr( $this->get_option( $args ) );
			$error = $this->get_setting_error( $args['id'], ' style="border: 1px solid red; padding: 2px 1em 2px 0; "' );
			$input = $this->create_element("checkbox",$args);
            $label = $input.' ';
            $label .= isset($args['multiple_label']) ? $args['multiple_label'] : "";
			$html = sprintf( '<label for="%1$s_%2$s"%5$s>%3$s %4$s</label>', $args['section'], $args['id'], $label, $args['desc'], $error );
			echo $html . '';
		}
        
        public function callback_multicheckbox( $args ) {
            $cArgs = $args;
			$value = array_map( 'esc_attr', array_values( (array) $this->get_option( $args ) ) );
			$count = count( $args['options'] );
			$html  = '<fieldset>';
			$i = 0;
			foreach ( (array) $args['options'] as $opt => $label ) {
                $cArgs['id'] = $args['section'].'_'.$args['id'].'_'.$opt;
                $cArgs['bx_value'] = $opt;
                $cArgs['is_checked'] = ( in_array( $opt , $value ) ) ? ' checked="checked" ' : '';
                $cArgs['multiple_label'] = $label;
                ob_start();
                    $this->callback_checkbox($cArgs);
                $html .= ob_get_clean(); 
                
				$html .= ( isset( $args['row_after'][$opt] ) && $args['row_after'][$opt] ) ? $args['row_after'][$opt] : '';
				$html .= ( ++$i < $count ) ? '<br/>' : '';
			}

			echo $html . '</fieldset>' . $this->description( $args['desc'] );
		}

        public function callback_radio( $args ) {
			$args = $this->get_arguments( $args ); // escapes all attributes
			$value = (string) esc_attr( $this->get_option( $args ) );
			$options = array_keys( (array) $args['options'] );
			// make sure one radio button is checked
			if ( empty( $value ) && ( isset( $options[0] ) && $options[0] ) ) {
				$value = $options[0];
			} elseif ( !empty( $value ) && ( isset( $options[0] ) && $options[0] ) ) {
				if ( !in_array( $value, $options ) )
					$value = $options[0];
			}
			$html = '<fieldset>';
			$i=0;
			$count = count( $args['options'] );
			foreach ( (array) $args['options'] as $opt => $label ) {
				$input = sprintf( '<input type="radio" id="%1$s_%2$s_%3$s" name="%1$s[%2$s]" value="%3$s"%4$s%5$s />', $args['section'], $args['id'], $opt, checked( $value, $opt, false ), $args['attr'] );
				$html .= sprintf( '<label for="%1$s_%2$s_%4$s">%3$s%5$s</label>', $args['section'], $args['id'], $input, $opt, ' <span>'.$label.'</span>' );
				$html .= ( isset( $args['row_after'][$opt] ) && $args['row_after'][$opt] ) ? $args['row_after'][$opt] : '';
				$html .= ( ++$i < $count ) ? '<br/>' : '';
			}

			echo '</fieldset>' . $html . $this->description( $args['desc'] );
		}
        
        public function check_options_type($args){
            if(isset($args['select_type'])){
                $type = $args['select_type'];
                if($type == 'userrole'){
                    $args['options'] = $this->get_user_roles($args);
                }
            }
             
            return $args;
        }
        
        private function handle_options($options,$selected = ''){
            $html = '';
            if(!is_array($selected)){$selected = array($selected);}
            foreach($options as $d => $v){
                if(is_array($v)){
                    $html .= '<optgroup label="'.$d.'">';
                    $html .= $this->handle_options($v,$selected);
                    $html .= '</optgroup>';
                } else {
                    $is_selected = '';
                    
                    if(in_array($d,$selected)){
                        $is_selected =' selected="selected" ';
                    }
                    $html .= '<option value="'.$d.'" '.$is_selected.' >'.$v.'</option>';
                }
            }
            return $html;
        }
        
        private function get_user_roles($args){
            $return = array();
            if(function_exists('wp_roles')){
                $all_roles = wp_roles()->roles; 
                foreach($all_roles as $role=>$roleV){
                    $return[$role] = $roleV['name'];
                }
            }
            return apply_filters("vsp_settings_user_roles",$return,$args);
        }
        
        public function description( $desc = '' ) {
			if ( $desc ) {
				return sprintf( '<p class="description">%s</p>', $desc );
			}
		}

        protected function get_setting_error( $setting_id, $attr = '' ) {
			$display_error = '';
			if ( !empty( $this->settings_errors ) ) {
				foreach ( $this->settings_errors as $error ) {
					if ( isset( $error['setting'] ) && $error['setting'] === $setting_id ) {
                        $display_error = (empty($attr)) ? ' style="border: 1px solid red;"' : $attr;
					}
				}
			}
			return $display_error;
		}
        
        private function cache_options($section){
            if(!isset($this->cached_options)){
                $this->cached_options = array();
            }
            
            if(isset($this->cached_options[$section])){
                return $this->cached_options[$section];
            }
            
            $this->cached_options[$section] = get_option($section);
            return $this->cached_options[$section];
        }
        
        private function get_option( $args ) {
			if ( isset( $args['value'] ) ) {
				return $args['value'];
			}

			$options = $this->cache_options( $args['section'] );
			if ( isset( $options[ $args['id'] ] ) ) {
				return $options[ $args['id'] ];
			}

			return ( isset( $args['default'] ) ) ? $args['default'] : '';
		}
        
        private function get_arguments($args){
            $attr_string = '';
            $defaults = $attr = array();
            
            $args['section'] = esc_attr( $args['section'] );
			$args['id'] = esc_attr( $args['id'] );
            
            if ( isset( $args['options'] ) && $args['options'] ) {
				$options = array();
				foreach ( (array) $args['options'] as $key => $value ) {
					$options[ esc_attr( $key ) ] = $value;
				}
				$args['options'] = $options;
			}
            
            if(isset($args['attr'])){
                if(!empty($args['attr'])){
                    $attr = $args['attr'];
                }
            }
                
            $attr['data-depend-id'] = $args['section'].'_'.$args['id'];
            
            if(!empty($args['dependency'])){
                if(!empty($args['dependency'][0])){
                    $args['dependency'][0] = explode("|",$args['dependency'][0]);
                    foreach($args['dependency'][0] as $i => $val){
                        $args['dependency'][0][$i] = $args['section'].'_'.$val;
                    }
                    $args['dependency'][0] = implode('|',$args['dependency'][0]);
                }
                $attr['data-controller'] = $args['dependency'][0];
                $attr['data-condition'] = $args['dependency'][1];
                $attr['data-value'] = $args['dependency'][2];
            }
            
            if("textarea" === $args['type']){
                $attr = array_merge(array('rows' => 5 , 'cols' => '55'),$attr);
            }
            
            
            $attr['class'] = isset( $attr['class'] ) ? $attr['class'] : array();
            
            if(!is_array($attr['class'])){
                $attr['class'] = explode(" ",$attr['class']);
            }           
            
            if(isset($args['input_class'])){
                if(is_string($args['input_class'])){
                    $attr['class'] = array_merge($attr['class'],explode(" ",$args['input_class']));
                }
            }
            
            if ( isset( $args['size'] ) &&  $args['size'] ) {
                $attr['class'][] = $args['size'].'-'.$args['type'];
            }

            $attr['class'][] = 'vsp-'.$args['type'];
            $attr['class'] = implode(" ",array_unique($attr['class']));
            
            $attr = $this->handle_id_name($args,$attr);
            foreach ( $attr as $key => $arg ) {
				$arg = ( 'class' === $arg ) ? sanitize_html_class( $arg ) : esc_attr( $arg );
				$attr_string .= ' '. trim( $key ) . '="' . trim( $arg ) . '"';
			}

			$args['attr'] = $attr_string;
            return $args;
        }
        
        public function handle_id_name($args,$attr){
            $use_default = true;
            if("checkbox" === $args['type']){
                if(isset($args['bx_value'])){
                    $use_default = false;
                    $attr['id'] = $args['section'].'_'.$args['id'].$args['bx_value'];
                    $attr['name'] = $args['section'].'['.$args['id'].']'.'['.$args['bx_value'].']';
                }
            }
            
            if($use_default === true){
                $attr['id'] = $args['section'].'_'.$args['id'];
                $attr['name'] = $args['section'].'['.$args['id'].']';
            }
            
            return $attr;
        }
    }
}