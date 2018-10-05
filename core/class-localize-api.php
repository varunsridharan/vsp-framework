<?php
/**
 *
 * Project : wcisms
 * Date : 01-10-2018
 * Time : 02:49 PM
 * File : class-localize-api.php
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @version 1.0
 * @package wcisms
 * @copyright 2018 Varun Sridharan
 * @license GPLV3 Or Greater (https://www.gnu.org/licenses/gpl-3.0.txt)
 */


if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'VSP_Localize_API' ) ) {
	/**
	 * Class VSP_Localize_API
	 *
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
	 */
	class VSP_Localize_API {
		/**
		 * slug
		 *
		 * @var null|string
		 */
		private $slug = null;

		/**
		 * js_args
		 *
		 * @var array
		 */
		private $js_args = array();

		/**
		 * translations
		 *
		 * @var array
		 */
		private $translations = array();

		/**
		 * scripts_check
		 *
		 * @var array
		 */
		private $scripts_check = array();

		/**
		 * VSP_Localize_API constructor.
		 *
		 * @param string $slug
		 * @param array  $scripts_check
		 * @param bool   $frontend
		 * @param bool   $print_functions
		 */
		public function __construct( $slug = '', $scripts_check = array(), $frontend = false, $print_functions = true ) {
			$this->slug          = $slug;
			$this->scripts_check = $scripts_check;
			add_action( 'admin_footer', array( &$this, 'render_js_args' ) );
			add_action( 'customize_controls_print_footer_scripts', array( &$this, 'render_js_args' ), 9999999999999 );

			if ( true === $frontend ) {
				add_action( 'wp_footer', array( &$this, 'render_js_args' ) );
			}

			if ( true === $print_functions ) {
				add_action( 'admin_footer', array( &$this, 'print_functions' ) );
				add_action( 'customize_controls_print_footer_scripts', array( &$this, 'print_functions' ) );
				if ( true === $frontend ) {
					add_action( 'wp_footer', array( &$this, 'print_functions' ) );
				}
			}
		}

		/**
		 * Merges given array
		 *
		 * @param array $new .
		 * @param array $defaults .
		 *
		 * @return array
		 */
		protected function parse_args( $new = array(), $defaults = array() ) {
			if ( ! is_array( $new ) ) {
				$new = array();
			}
			return wp_parse_args( $new, $defaults );
		}

		/**
		 * Adds a given object to array based on the ID.
		 *
		 * @param string $object_id
		 * @param array  $args
		 * @param bool   $merge
		 *
		 * @return bool
		 */
		public function add( $object_id = '', $args = array(), $merge = true ) {
			$args = self::handle_js_function( $args );
			if ( true === $merge && isset( $this->js_args[ $object_id ] ) ) {
				$this->js_args[ $object_id ] = $this->parse_args( $args, $this->js_args[ $object_id ] );
			} else {
				$this->js_args[ $object_id ] = $args;
			}
			return true;
		}

		/**
		 * Converts Javascript Function into array.
		 *
		 * @param $args
		 *
		 * @return mixed
		 */
		public static function handle_js_function( $args ) {
			foreach ( $args as $i => $ar ) {
				if ( is_array( $ar ) ) {
					$args[ $i ] = self::handle_js_function( $ar );
				} elseif ( is_string( $ar ) ) {
					$re = '/\bfunction[ ]{0,1}(\(((?>[^()]+|(?-2))*)\))(\{((?>[^{}]+|(?-2))*)\})/';
					preg_match_all( $re, $ar, $matches, PREG_SET_ORDER, 0 );

					if ( is_array( $matches ) && ! empty( array_filter( $matches ) ) ) {
						$args[ $i ] = array(
							'js_args'     => false,
							'js_contents' => false,
						);

						if ( isset( $matches[0][2] ) ) {
							$args[ $i ]['js_args'] = ( empty( $matches[0][2] ) ) ? false : $matches[0][2];
						}

						if ( isset( $matches[0][4] ) ) {
							$args[ $i ]['js_contents'] = ( empty( $matches[0][4] ) ) ? false : $matches[0][4];
						}
					}
				}
			}
			return $args;
		}


		/**
		 * Custom Text which will be used in JS.
		 *
		 * @param string $key
		 * @param string $value
		 *
		 * @return $this
		 */
		public function text( $key = '', $value = '' ) {
			$this->translations[ $key ] = $value;
			return $this;
		}

		/**
		 * Renders JS Args.
		 */
		public function render_js_args() {
			do_action( $this->slug . '_before_render_js_args' );
			if ( ! empty( $this->scripts_check ) ) {
				foreach ( $this->scripts_check as $script ) {
					if ( true === wp_script_is( $script ) && false === wp_script_is( $script, 'done' ) ) {
						return $this->localize_script( $script );
					}
				}
			}

			return $this->print_js_data();
		}

		/**
		 * Uses WP-Script-API To Localize script.
		 *
		 * @param string $handle
		 *
		 * @return bool
		 */
		private function localize_script( $handle = '' ) {
			wp_localize_script( $handle, $this->slug, $this->js_args );
			wp_localize_script( $handle, $this->slug . '_i18n', $this->translations );
			/*foreach ( $this->js_args as $key => $value ) {
				$key = str_replace( '-', '_', $key );
				wp_localize_script( $handle, $key, $value );
			}*/
			return true;
		}

		/**
		 * Clears JS Args.
		 *
		 * @return $this
		 */
		public function clear() {
			$this->js_args = array();
			return $this;
		}

		/**
		 * Outputs Raw HTML of js info.
		 */
		private function print_js_data() {
			$h = "<script type='text/javascript' id='" . $this->slug . "_field_js_vars'>\n"; // CDATA and type='text/javascript' is not needed for HTML 5

			$h .= "/* <![CDATA[ */\n";
			/*foreach ( $this->js_args as $key => $value ) {
				$h .= vsp_js_vars( $key, $value, false );
			}*/
			$h .= vsp_js_vars( $this->slug, $this->js_args, false );
			$h .= vsp_js_vars( $this->slug . '_i18n', $this->translations, false );
			$h .= "/* ]]> */\n";
			$h .= "</script>\n";
			echo $h;
			return true;
		}

		/**
		 * Prints Few JS Functions.
		 */
		public function print_functions() {
			$h = "<script type='text/javascript' id='" . $this->slug . "_functions'>\n"; // CDATA and type='text/javascript' is not needed for HTML 5

			$h .= ' 
			function ' . $this->slug . '_option($var_id,$default){
				$default = $default || {};
				if ( $var_id ) {
					if ( typeof window["' . $this->slug . '"][$var_id] === "undefined" || window["' . $this->slug . '"][ $var_id ] === undefined ) {
						return $default;
					}
					return JSON.parse( JSON.stringify(window["' . $this->slug . '"][$var_id] ) );
				}
				
				return $default;
			}
			
			function ' . $this->slug . '_text($key,$default){
				$default = $default || "string_default_not_found";
				return ( window["' . $this->slug . '_i18n' . '"][ $key ] !== undefined ) ?  window["' . $this->slug . '_i18n' . '"][ $key ] : $default;
			}';


			$h .= "</script>\n";
			echo $h;
		}
	}
}
