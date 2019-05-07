<?php
/**
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @copyright 2018 Varun Sridharan
 * @license   GPLV3 Or Greater
 */

namespace Varunsridharan\WordPress;

if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( '\Varunsridharan\WordPress\Localizer' ) ) {
	/**
	 * Class Localizer
	 *
	 * @package Varunsridharan\WordPress
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
	 */
	class Localizer {
		/**
		 * Stores All Instance.
		 *
		 * @var array
		 * @access
		 * @static
		 */
		protected static $instances = array();

		/**
		 * @var null
		 * @access
		 */
		protected $slug = null;

		/**
		 * @var bool
		 * @access
		 */
		protected $script_tocheck = false;

		/**
		 * js_args
		 *
		 * @var array
		 */
		protected $js_args = array();

		/**
		 * translations
		 *
		 * @var array
		 */
		protected $translations = array();

		/**
		 * @var bool
		 * @access
		 */
		private $frontend = false;

		/**
		 * @var bool
		 * @access
		 */
		private $functions = false;

		/**
		 * Localizer constructor.
		 *
		 * @param array $args
		 */
		public function __construct( $args = array() ) {
			$args                 = wp_parse_args( $args, array(
				'id'        => false,
				'frontend'  => false,
				'functions' => false,
				'scripts'   => array(),
			) );
			$this->slug           = $args['id'];
			$this->script_tocheck = $args['scripts'];
			$this->frontend       = $args['frontend'];
			$this->functions      = $args['functions'];

			add_action( 'admin_footer', array( &$this, 'backend_hook' ) );
			add_action( 'customize_controls_print_footer_scripts', array( &$this, 'backend_hook' ), 9999999999999 );
			add_action( 'wp_footer', array( &$this, 'frontend_hook' ) );
		}

		/**
		 * Returns A New Instance.
		 *
		 * @param array|string $args
		 *
		 * @static
		 * @return bool|mixed|$this|\Varunsridharan\WordPress\Localizer
		 */
		public static function instance( $args = array() ) {
			if ( isset( $args['id'] ) ) {
				if ( ! isset( self::$instances[ $args['id'] ] ) ) {
					self::$instances[ $args['id'] ] = new static( $args );
				}
				return self::$instances[ $args['id'] ];
			}
			return ( is_string( $args ) && isset( self::$instances[ $args ] ) ) ? self::$instances[ $args ] : false;
		}

		/**
		 * Back End Hook.
		 */
		public function backend_hook() {
			echo $this->render_js_args();
			if ( true === $this->functions ) {
				echo $this->print_js_function();
			}
		}

		/**
		 * Frontend Hook.
		 */
		public function frontend_hook() {
			if ( $this->frontend ) {
				echo $this->render_js_args();
				if ( $this->functions ) {
					echo $this->print_js_function();
				}
			}
		}

		/**
		 * Adds a given object to array based on the ID.
		 *
		 * @param string $object_id
		 * @param array  $args
		 * @param bool   $merge
		 * @param bool   $convert_js_funcion
		 *
		 * @return self|$this
		 */
		public function add( $object_id = '', $args = array(), $merge = true, $convert_js_funcion = true ) {
			if ( true === $convert_js_funcion ) {
				$args = $this->handle_js_function( $args );
			}
			if ( true === $merge && isset( $this->js_args[ $object_id ] ) ) {
				$this->js_args[ $object_id ] = wp_parse_args( $args, $this->js_args[ $object_id ] );
			} else {
				$this->js_args[ $object_id ] = $args;
			}
			return $this;
		}

		/**
		 * Converts Javascript Function into array.
		 *
		 * @param $args
		 *
		 * @return mixed
		 */
		protected function handle_js_function( $args ) {
			if ( empty( $args ) || false === is_array( $args ) || true === is_object( $args ) ) {
				return $args;
			}
			foreach ( $args as $i => $ar ) {
				if ( is_array( $ar ) ) {
					$args[ $i ] = $this->handle_js_function( $ar );
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
		 * @return mixed
		 */
		public function render_js_args() {
			do_action( $this->slug . '_before_render_js_args' );

			if ( defined( 'DOING_AJAX' ) && true === DOING_AJAX ) {
				return $this->print_js_data();
			}

			if ( ! empty( $this->script_tocheck ) ) {
				foreach ( $this->script_tocheck as $script ) {
					if ( true === wp_script_is( $script ) && false === wp_script_is( $script, 'done' ) ) {
						$this->localize_script( $script );
						return '';
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
		 * @return boolean
		 */
		protected function localize_script( $handle = '' ) {
			foreach ( $this->js_args as $key => $value ) {
				$key = str_replace( '-', '_', $key );
				wp_localize_script( $handle, $key, $value );
			}

			wp_localize_script( $handle, $this->slug . '_il8n', $this->translations );
			return true;
		}

		/**
		 * @param $object_name
		 * @param $l10n
		 *
		 * @static
		 * @return string
		 */
		public static function php_to_js( $object_name, $l10n ) {
			return 'var ' . $object_name . ' = ' . wp_json_encode( self::js_args_encode( $l10n ) ) . ';';
		}

		/**
		 * Encodes PHP Array in JSString.
		 *
		 * @param $l10n
		 *
		 * @return array|string
		 * @static
		 */
		public static function js_args_encode( $l10n ) {
			if ( is_array( $l10n ) ) {
				foreach ( (array) $l10n as $key => $value ) {
					if ( is_scalar( $value ) ) {
						$l10n[ $key ] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' );
					}
				}
			} else {
				$l10n = html_entity_decode( (string) $l10n, ENT_QUOTES, 'UTF-8' );
			}
			return $l10n;
		}

		/**
		 * Outputs Raw HTML of js info.
		 */
		protected function print_js_data() {
			$slug = $this->slug . '_field_js_vars';
			$args = self::php_to_js( $this->slug, $this->js_args );
			$txt  = self::php_to_js( $this->slug . '_il8n', $this->translations );
			return '<script type="text/javascript" id="' . $slug . '">/* <![CDATA[ */ ' . $args . ' ' . $txt . '/* ]]> */ </script>';
		}

		/**
		 * Prints Custom Javascript Functions.
		 *
		 * @return string
		 */
		private function print_js_function() {
			$script = '<script type="text/javascript" id="' . $this->slug . '_functions"> ';

			$script .= '
function ' . $this->slug . '_option ($key,$default){ 
	$default = $default || false; 
	if($key && "undefined" !== typeof window.' . $this->slug . '[$key] || undefined !== window.' . $this->slug . '[$key]){
		return JSON.parse( JSON.stringify( window.' . $this->slug . '[$key] ) ); 
	}
return $default; 
}';
			$script .= '
function ' . $this->slug . '_text($key,$default){ 
	$default = $default || "string_not_found"; 
	if ($key && "undefined" !== typeof window["' . $this->slug . '_il8n"][$key]||  window["' . $this->slug . '_il8n"][$key] !== undefined ) { 
		return window["' . $this->slug . '_il8n"][$key]; 
	} 
return $default; 
}';
			$script .= '</script>';
			return $script;
		}
	}
}
