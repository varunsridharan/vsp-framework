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
				\add_action( 'wp_ajax_nopriv_' . $this->action, array( &$this, 'ajax_request_single' ) );
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
		final protected function ajax_slug( $action ) {
			$action = ( ! empty( $this->action_prefix ) ) ? $this->action_prefix . '_' . $action : $action;
			$action = ( ! empty( $this->action_surfix ) ) ? $action . '_' . $this->action_surfix : $action;
			return $action;
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
		final public function ajax_request_single() {
			$action     = false;
			$action_key = ( true === $this->is_single ) ? $this->action : $this->is_single;

			if ( isset( $_REQUEST[ $action_key . '-action' ] ) && ! empty( $_REQUEST[ $action_key . '-action' ] ) ) {
				$action = $_REQUEST[ $action_key . '-action' ];
			} elseif ( isset( $_REQUEST[ $action_key ] ) && ! empty( $_REQUEST[ $action_key ] ) ) {
				$action = $_REQUEST[ $action_key ];
			}

			$_action = $this->extract_action_slug( $action );

			if ( false !== $action && isset( $this->actions[ $_action ] ) ) {
				if ( false === $this->is_logged_in() && true === $this->actions[ $_action ] ) {
					$this->trigger_ajax_callback( $action );
				} elseif ( $this->is_logged_in() === true ) {
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
		final protected function extract_action_slug( $action ) {
			return trim( trim( str_replace( array(
				$this->action_prefix,
				$this->action_surfix,
			), '', $action ), ' ' ), '_' );
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
			$_function_name   = str_replace( '-', '_', \sanitize_key( $_function_action ) );
			if ( method_exists( $this, $_function_name ) ) {
				\do_action( 'ajax_before_' . $action );
				$this->$_function_name();
				\do_action( 'ajax_after_' . $action );
			} else {
				\do_action( 'ajax_' . $action );
			}
		}

		/**
		 * Handles Multiple Ajax Requests.
		 */
		final public function ajax_request() {
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
		 * Checks / Returns the type of request method for the current request.
		 *
		 * @param string|array $type The type of request you want to check. If an array
		 *                           this method will return true if the request matches any type.
		 *
		 * @return string
		 */
		protected function request_type( $type = null ) {
			$type = ( ! is_array( $type ) ) ? array( $type ) : $type;
			if ( ! is_null( $type ) && is_array( $type ) ) {
				return in_array( $_SERVER['REQUEST_METHOD'], array_map( 'strtoupper', $type ), true );
			}
			return $_SERVER['REQUEST_METHOD'];
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
		 * Checks if Current Request Method Is POST
		 *
		 * @return string|bool|boolean
		 */
		public function is_post() {
			return $this->request_type( 'POST' );
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
						$return = $_GET[ $key ];
						break;
					case 'POST':
						$return = $_POST[ $key ];
						break;
					case 'REQUEST':
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
		protected function has( $key = '', $type = 'GET' ) {
			switch ( $type ) {
				case 'GET':
					$has = ( isset( $_GET[ $key ] ) ) ? $_GET[ $key ] : false;
					break;
				case 'POST':
					$has = ( isset( $_POST[ $key ] ) ) ? $_POST[ $key ] : false;
					break;
				default:
					$has = ( isset( $_REQUEST[ $key ] ) ) ? $_REQUEST[ $key ] : false;
					break;
			}
			return $has;
		}

		/**
		 * @param $key
		 *
		 * @return bool
		 */
		public function has_get( $key ) {
			return $this->has( $key, 'GET' );
		}

		/**
		 * @param $key
		 *
		 * @return bool
		 */
		public function has_post( $key ) {
			return $this->has( $key, 'POST' );
		}

		/**
		 * @param $key
		 *
		 * @return bool
		 */
		public function has_request( $key ) {
			return $this->has( $key, 'REQUEST' );
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
			return $this->get_post_request( $key, $default, 'GET' );
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
			return $this->get_post_request( $key, $default, 'POST' );
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
			return $this->get_post_request( $key, $default, 'REQUEST' );
		}

		/**
		 * @param bool|string $error_title
		 * @param bool|string $error_message
		 *
		 * @return array
		 */
		protected function error_message( $error_title = false, $error_message = false ) {
			return array(
				'title'   => $error_title,
				'message' => $error_message,
			);
		}

		/**
		 * @param bool|string $success_title
		 * @param bool|string $success_message
		 *
		 * @return array
		 */
		protected function success_message( $success_title = false, $success_message = false ) {
			return array(
				'title'   => $success_title,
				'message' => $success_message,
			);
		}

		/**
		 * @param mixed $data
		 * @param null  $status_code
		 */
		public function json_error( $data = null, $status_code = null ) {
			wp_send_json_error( $data, $status_code );
		}

		/**
		 * @param mixed $data
		 * @param null  $status_code
		 */
		public function json_success( $data = null, $status_code = null ) {
			wp_send_json_success( $data, $status_code );
		}

		/**
		 * @param string      $key
		 * @param string|bool $error_title
		 * @param string|bool $error_message
		 * @param string      $type
		 *
		 * @return bool|mixed
		 */
		protected function validate( $key, $error_title = false, $error_message = false, $type = 'GET' ) {
			if ( ( false === $key || false === $this->has( $key, $type ) ) || ( true === $this->has( $key, $type ) && empty( $this->get_post_request( $key, false, $type ) ) ) ) {
				$this->json_error( $this->error_message( $error_title, $error_message ) );
				return false;
			}
			return $this->get_post_request( $key, false, $type );
		}

		/**
		 * Sends WP Error.
		 *
		 * @param string|bool $error_title
		 * @param string|bool $error_message
		 * @param array       $args
		 */
		public function error( $error_title = false, $error_message = false, $args = array() ) {
			$this->json_error( wp_parse_args( $args, $this->error_message( $error_title, $error_message ) ) );
		}

		/**
		 * @param bool|string $success_title
		 * @param bool|string $success_message
		 * @param array       $args
		 */
		public function success( $success_title = false, $success_message = false, $args = array() ) {
			$this->json_success( wp_parse_args( $args, $this->success_message( $success_title, $success_message ) ) );
		}

		/**
		 * @param string      $key
		 * @param string|bool $error_title
		 * @param string|bool $error_message
		 *
		 * @return bool|mixed
		 */
		public function validate_post( $key, $error_title = false, $error_message = false ) {
			return $this->validate( $key, $error_title, $error_message, 'POST' );
		}

		/**
		 * @param string      $key
		 * @param string|bool $error_title
		 * @param string|bool $error_message
		 *
		 * @return bool|mixed
		 */
		public function validate_get( $key, $error_title = false, $error_message = false ) {
			return $this->validate( $key, $error_title, $error_message, 'GET' );
		}

		/**
		 * @param string      $key
		 * @param string|bool $error_title
		 * @param string|bool $error_message
		 *
		 * @return bool|mixed
		 */
		public function validate_request( $key, $error_title = false, $error_message = false ) {
			return $this->validate( $key, $error_title, $error_message, 'REQUEST' );
		}

		/**
		 * Checks if user is logged in.
		 *
		 * @return bool
		 */
		public function is_logged_in() {
			return ( function_exists( 'is_user_logged_in' ) && is_user_logged_in() ) ? true : false;
		}
	}
}
