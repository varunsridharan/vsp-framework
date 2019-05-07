<?php
/**
 * Simple Lightweight Ajax Handler For WP Theme/Plugin Developers
 *
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @copyright 2018 Varun Sridharan
 * @license   GPLV3 Or Greater
 */

namespace Varunsridharan\WordPress;

if ( ! class_exists( '\Varunsridharan\WordPress\Ajaxer' ) ) {
	/**
	 * Class Ajaxer
	 *
	 * @package Varunsridharan\WordPress
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
	 */
	abstract class Ajaxer {
		/**
		 * Ajax Action Prefix
		 *
		 * @example for wordpress_show_popup wordpress is the prefix
		 *
		 * @var string
		 */
		protected $action_prefix = '';

		/**
		 * Ajax Action Surfix
		 *
		 * @example for wordpress_show_popup_data data is the surfix
		 *
		 * @var string
		 */
		protected $action_surfix = '';

		/**
		 * Action Name
		 * provide value if all ajax requests runs in a single action key.
		 *
		 * @var string
		 */
		protected $action = '';

		/**
		 * Array of ajax actions
		 *
		 * @example array('ajax_action_1' => true,'ajax_action_2' => false)
		 *          if value set to true then it runs for both loggedout / logged in users
		 *          if value set to false then it runs only for the logged in user
		 *
		 * @example array('ajax_action_1' => array('auth' => false,'callback' => array(CLASSNAME,METHODNAME)))
		 *          if auth value set to true then it runs for both loggedout / logged in users
		 *          if auth value set to false then it runs only for the logged in user
		 *          callback can either be a string,array or a actual dynamic function.
		 *
		 * @var array
		 */
		protected $actions = array();

		/**
		 * Set to true if plugin's ajax runs in a single action
		 * OR
		 * Set a custom key so convert plugin-slug=ajax-action into your-key=ajax-action
		 *
		 * @example Single Ajax Action :
		 *              admin-ajax.php?action=plugin-slug&plugin-slug-action=ajax-action&param1=value1&param2=value=2
		 *          Multiple Ajax Actions :
		 *              admin-ajax.php?action=plugin-slug-ajax-action1&param1=value1=param2=value2
		 *
		 * @example Single Ajax Action :
		 *            admin-ajax.php?action=plugin-slug&custom-key-action=ajax-action&param1=value1&param2=value=2
		 *
		 *          Multiple Ajax Actions:
		 *             admin-ajax.php?action=plugin-slug-ajax-action1&param1=value1=param2=value2
		 *
		 * @var bool
		 */
		protected $is_single = false;

		/**
		 * Ajaxer constructor.
		 */
		public function __construct() {
			if ( false !== $this->is_single ) {
				\add_action( 'wp_ajax_' . $this->action, array( &$this, 'ajax_request_single' ) );
			} else {
				foreach ( $this->actions as $action => $nopriv ) {
					\add_action( 'wp_ajax_' . $this->ajax_slug( $action ), array( &$this, 'ajax_request' ) );
					if ( ( ! is_array( $nopriv ) && true === $nopriv ) || ( is_array( $nopriv ) && isset( $nopriv['auth'] ) && true === $nopriv['auth'] ) ) {
						\add_action( 'wp_ajax_nopriv_' . $this->ajax_slug( $action ), array( &$this, 'ajax_request' ) );
					}
				}
			}
		}

		/**
		 * Returns Modified Ajax Action Slug
		 *
		 * @param $action
		 *
		 * @return string
		 */
		protected function ajax_slug( $action ) {
			$slug = $action;
			if ( ! empty( $this->action_prefix ) ) {
				$slug = $this->action_prefix . '_' . $slug;
			}
			if ( ! empty( $this->action_surfix ) ) {
				$slug .= '_' . $this->action_surfix;
			}
			return $slug;
		}

		/**
		 * Triggers A Callback to a ajax request
		 *
		 * @hook ajax_before_{ajax_action}
		 * @hook ajax_after_{ajax_action}
		 *
		 * if method not exists then below hook fires
		 * @hook ajax_{ajax_action}
		 * ajax_action will be replaced with {$this->action}-action from url
		 */
		public function ajax_request_single() {
			$action     = false;
			$action_key = ( true === $this->is_single ) ? $this->action : $this->is_single;

			if ( isset( $_REQUEST[ $action_key . '-action' ] ) && ! empty( $_REQUEST[ $action_key . '-action' ] ) ) {
				$action = $_REQUEST[ $action_key . '-action' ];
			} elseif ( isset( $_REQUEST[ $action_key ] ) && ! empty( $_REQUEST[ $action_key ] ) ) {
				$action = $_REQUEST[ $action_key ];
			}

			$_action = $this->extract_action_slug( $action );

			if ( false !== $action && isset( $this->actions[ $_action ] ) ) {
				if ( false === \is_user_logged_in() && true === $this->actions[ $_action ] ) {
					$this->trigger_ajax_callback( $action );
				} elseif ( \is_user_logged_in() === true ) {
					$this->trigger_ajax_callback( $action );
				}
			}

			\wp_die( 0 );
		}

		/**
		 * Extracts Action Without Prefix / Surfix ($this->action_prefix | $this->action_surfix)
		 *
		 * @param $action
		 *
		 * @return string
		 */
		protected function extract_action_slug( $action ) {
			$action = str_replace( $this->action_prefix, '', $action );
			$action = str_replace( $this->action_surfix, '', $action );
			$action = ltrim( $action, ' ' );
			$action = ltrim( $action, '_' );
			$action = rtrim( $action, ' ' );
			$action = rtrim( $action, '_' );
			return $action;
		}

		/**
		 * Triggers A AjaxCallback
		 *
		 * @hook ajax_before_{ajax_action}
		 * @hook ajax_after_{ajax_action}
		 * if method not exists then below hook fires
		 * @hook ajax_{ajax_action}
		 *
		 * @param $action
		 */
		protected function trigger_ajax_callback( $action ) {
			$_function_action = $this->extract_action_slug( $action );
			if ( method_exists( $this, $this->function_name( $_function_action ) ) ) {
				$function = $this->function_name( $_function_action );
				\do_action( 'ajax_before_' . $action );
				$this->$function();
				\do_action( 'ajax_after_' . $action );
			} else {
				\do_action( 'ajax_' . $action );
			}
		}

		/**
		 * Converts Normal String into php function name
		 *
		 * @param $action
		 *
		 * @return mixed
		 */
		protected function function_name( $action ) {
			return str_replace( '-', '_', \sanitize_key( $action ) );
		}

		/**
		 * Handles Multiple Ajax Requests.
		 */
		public function ajax_request() {
			$action  = ( isset( $_REQUEST['action'] ) && ! empty( $_REQUEST['action'] ) ) ? $_REQUEST['action'] : false;
			$_action = $this->extract_action_slug( $action );

			if ( false !== $action && isset( $this->actions[ $_action ] ) ) {
				if ( is_array( $this->actions[ $_action ] ) && isset( $this->actions[ $_action ]['callback'] ) ) {
					call_user_func( $this->actions[ $_action ]['callback'] );
					\wp_die();
				} else {
					$this->trigger_ajax_callback( $_action );
				}
			}
			\wp_die( 0 );
		}

		/**
		 * Checks if Current Request Method Is GET
		 *
		 * @return string|bool|boolean
		 */
		public function is_get() {
			return $this->request_type( 'GET' );
		}

		/**
		 * Checks / Returns the type of request method for the current request.
		 *
		 * @param string|array $type The type of request you want to check. If an array
		 *                           this method will return true if the request matches any type.
		 *
		 * @return string
		 */
		public function request_type( $type = null ) {
			if ( ! is_null( $type ) ) {
				if ( is_array( $type ) ) {
					return in_array( $_SERVER['REQUEST_METHOD'], array_map( 'strtoupper', $type ) );
				}
				return ( strtoupper( $type ) === $_SERVER['REQUEST_METHOD'] );
			}
			return $_SERVER['REQUEST_METHOD'];
		}

		/**
		 * Checks if Current Request Method Is POST
		 *
		 * @return string|bool|boolean
		 */
		public function is_post() {
			return $this->request_type( 'POST' );
		}

		/**
		 * Returns give key's value from $_GET
		 *
		 * @param string $key
		 * @param bool   $default
		 *
		 * @return bool|mixed
		 */
		public function get( $key = '', $default = false ) {
			return $this->get_post_request( $key, $default, 'get' );
		}

		/**
		 * Checks for the given key in the given method and returns it.
		 *
		 * @param $key
		 * @param $default
		 * @param $type
		 *
		 * @return mixed
		 */
		private function get_post_request( $key, $default, $type ) {
			$return = $default;
			if ( false !== $this->has( $key, $type ) ) {
				switch ( $type ) {
					case 'GET':
					case 'get':
						$return = $_GET[ $key ];
						break;

					case 'POST':
					case 'post':
						$return = $_POST[ $key ];
						break;

					case 'REQUEST':
					case 'request':
						$return = $_REQUEST[ $key ];
						break;
					default:
						$return = $default;
						break;
				}
			}
			return $return;
		}

		/**
		 * Checks if given key exists in given request global array ($_GET/$_POST/$_REQUEST)
		 *
		 * @param string $key
		 * @param string $type
		 *
		 * @return bool
		 */
		public function has( $key = '', $type = 'GET' ) {
			switch ( $type ) {
				case 'GET':
				case 'get':
					$has = ( isset( $_GET[ $key ] ) ) ? $_GET[ $key ] : false;
					break;
				case 'POST':
				case 'post':
					$has = ( isset( $_POST[ $key ] ) ) ? $_POST[ $key ] : false;
					break;
				default:
					$has = ( isset( $_REQUEST[ $key ] ) ) ? $_REQUEST[ $key ] : false;
					break;
			}
			return $has;
		}

		/**
		 * Returns give key's value from $_GET
		 *
		 * @param string $key
		 * @param bool   $default
		 *
		 * @return bool|mixed
		 */
		public function post( $key = '', $default = false ) {
			return $this->get_post_request( $key, $default, 'post' );
		}

		/**
		 * Returns give key's value from $_REQUEST
		 *
		 * @param string $key
		 * @param bool   $default
		 *
		 * @return bool|mixed
		 */
		public function request( $key = '', $default = false ) {
			return $this->get_post_request( $key, $default, 'request' );
		}
	}
}
