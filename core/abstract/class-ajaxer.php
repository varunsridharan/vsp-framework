<?php
/**
 * Project: vsp-framework
 * File: class-ajaxer.php
 * Date: 26-03-2018
 * Time: 12:21 PM
 *
 * @link      : http://github.com/varunsridharan/vsp-framework/
 * @version   : 1.0
 * @since     : 1.0
 *
 * @package   : vsp-framework
 * @subpackage: core/
 * @author    : Varun Sridharan <varunsridharan23@gmail.com>
 * @copyright : 2018 Varun Sridharan
 * @license   : GPLV3 Or Greater
 */

abstract class VSP_Ajaxer extends VSP_Class_Handler {
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
	 * @var array
	 */
	protected $actions = array();

	/**
	 * Set to true if plugin's ajax runs in a single action
	 *
	 * @example Single Ajax Action
	 *          admin-ajax.php?action=plugin-slug&plugin-slug-action=ajax-action&param1=value1&param2=value=2
	 *          Multiple Ajax Actions
	 *          admin-ajax.php?action=plugin-slug-ajax-action1&param1=value1=param2=value2
	 *
	 * @var bool
	 */
	protected $is_single = false;

	/**
	 * Provide a action key if $this->is_single is set to true
	 *
	 * @var string
	 */
	protected $single_ajax_key = '';

	/**
	 * VSP_Ajaxer constructor.
	 *
	 * @param array $options
	 * @param array $defaults
	 */
	public function __construct( $options = array(), $defaults = array() ) {
		parent::__construct( $options, $defaults );
		$this->init();
	}

	/**
	 * Register Hooks With WP.
	 */
	protected function init() {
		if ( true === $this->is_single ) {
			add_action( 'wp_ajax_' . $this->action, array( &$this, 'ajax_request_single' ) );
		} else {
			foreach ( $this->actions as $action => $nopriv ) {
				add_action( 'wp_ajax_' . $this->ajax_slug( $action ), array( &$this, 'ajax_request' ) );
				if ( $nopriv ) {
					add_action( 'wp_ajax_nopriv_' . $this->ajax_slug( $action ), array( &$this, 'ajax_request' ) );
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
		$key = $this->action;

		$action = false;
		if ( isset( $_REQUEST[ $key . '-action' ] ) && ! empty( $_REQUEST[ $key . '-action' ] ) ) {
			$action = $_REQUEST[ $key . '-action' ];
		} elseif ( isset( $_REQUEST[ $this->action . '-action' ] ) && ! empty( $_REQUEST[ $this->action . '-action' ] ) ) {
			$action = $_REQUEST[ $this->action . '-action' ];
		}

		$_action = $this->extract_action_slug( $action );

		if ( false !== $action && isset( $this->actions[ $_action ] ) ) {
			if ( false === is_user_logged_in() && true === $this->actions[ $_action ] ) {
				$this->trigger_ajax_callback( $action );
			} elseif ( is_user_logged_in() === true ) {
				$this->trigger_ajax_callback( $action );
			}
		}

		wp_die( 0 );
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
		return vsp_fix_slug( $action );
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
			$this->action( 'ajax_before_' . $action );
			$this->$function();
			$this->action( 'ajax_after_' . $action );
		} else {
			$this->action( 'ajax_' . $action );
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
		return str_replace( '-', '_', sanitize_key( $action ) );
	}

	/**
	 * Handles Multiple Ajax Requests.
	 */
	public function ajax_request() {
		$action  = ( isset( $_REQUEST['action'] ) && ! empty( $_REQUEST['action'] ) ) ? $_REQUEST['action'] : false;
		$_action = $this->extract_action_slug( $action );

		if ( false !== $action && isset( $this->actions[ $_action ] ) ) {
			$this->trigger_ajax_callback( $_action );
		}

		wp_die( 0 );
	}

	/**
	 * Checks if Current Request Method Is GET
	 *
	 * @return string|bool|boolean
	 */
	public function is_get() {
		return $this->requestType( 'GET' );
	}

	/**
	 * Checks / Returns the type of request method for the current request.
	 *
	 * @param string|array $type The type of request you want to check. If an array
	 *                           this method will return true if the request matches any type.
	 *
	 * @return string
	 */
	public function requestType( $type = null ) {
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
		return $this->requestType( 'POST' );
	}

	/**
	 * Returns give key's value from $_GET
	 *
	 * @param string $key
	 * @param bool   $default
	 *
	 * @return bool|mixed
	 */
	protected function get( $key = '', $default = false ) {
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
		if ( true === $this->has( $key, $type ) ) {
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
	 * Returns give key's value from $_GET
	 *
	 * @param string $key
	 * @param bool   $default
	 *
	 * @return bool|mixed
	 */
	protected function post( $key = '', $default = false ) {
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
	protected function request( $key = '', $default = false ) {
		return $this->get_post_request( $key, $default, 'request' );
	}
}
