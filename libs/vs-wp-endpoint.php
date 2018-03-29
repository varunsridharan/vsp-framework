<?php
/**
 * VS WP Endpoint Handler.
 *
 * @name: VS WP Endpoint Handler.
 * @version: 1.0
 * @created_date : 29-03-2018
 * @created_time : 07:54 AM
 * @package      : vs-wp-endpoint
 * @since        : 1.0
 * @github       : https://github.com/varunsridharan/vs-wp-endpoint
 * @author       : Varun Sridharan <varunsridharan23@gmail.com>
 * @copyright    : 2018 Varun Sridharan
 * @license      : GPLV3 Or Greater (https://www.gnu.org/licenses/gpl-3.0.txt)
 */

/**
 * Class VS_WP_Router
 */
class VS_WP_Endpoint {

	/**
	 * Version
	 *
	 * @var string
	 */
	public $version = '1.0';

	/**
	 * Rewrite_endpoint
	 *
	 * @var array
	 */
	protected $rewrite_endpoint = array();

	/**
	 * Rewrite_rule
	 *
	 * @var array
	 */
	protected $rewrite_rule = array();

	/**
	 * Rewrite_tag
	 *
	 * @var array
	 */
	protected $rewrite_tag = array();

	/**
	 * @var string
	 */
	protected $rewrite_prefix = 'wp_router';

	/**
	 * @var string
	 */
	protected $parameter_pattern = '/{([\w\d]+)}/';

	/**
	 * @var string
	 */
	protected $value_pattern = '(?P<$1>[^/]+)';

	/**
	 * @var string
	 */
	protected $value_pattern_replace = '([^\/]+)';

	/**
	 * VSP_WP_Router constructor.
	 *
	 * @param string $prefix Used to set prefeix for query args.
	 */
	public function __construct( $prefix = '' ) {
		$this->prefix( $prefix );
		add_action( 'init', array( &$this, 'on_wp_init' ) );
		add_action( 'parse_request', array( $this, 'parse_request' ) );
		add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );
		add_action( 'wp_loaded', array( $this, 'flush' ) );
	}

	/**
	 * Sets Prefix.
	 * Used to set prefeix for query args.
	 *
	 * @param string $prefix
	 */
	public function prefix( $prefix = '' ) {
		$this->rewrite_prefix = $prefix;
	}

	/**
	 * Parses Request And Triggers Callback / Action.
	 * Based On the $callback Arg when using add_endpoint()
	 *
	 * @uses \call_user_func_array()
	 * @uses \call_user_func()
	 *
	 * @param $wp
	 */
	public function parse_request( $wp ) {
		if ( ! empty( $wp->query_vars ) ) {
			foreach ( $wp->query_vars as $key => $value ) {
				if ( isset( $this->rewrite_endpoint[ $key ] ) ) {
					if ( isset( $this->rewrite_endpoint[ $key ]['callback'] ) && is_callable( $this->rewrite_endpoint[ $key ]['callback'] ) ) {
						$is_arr   = ( is_array( $this->rewrite_endpoint[ $key ]['callback'] ) ) ? true : false;
						$callback = ( true === $is_arr ) ? 'call_user_func_array' : 'call_user_func';
						$param    = ( true === $is_arr ) ? array( $wp ) : $wp;
						$callback( $this->rewrite_endpoint[ $key ]['callback'], $param );
					} else {
						do_action( $this->rewrite_endpoint[ $key ]['callback'], $wp );
					}
				}
			}
		}
	}

	/**
	 * Flushes WordPress's rewrite rules.
	 *
	 * @return void
	 */
	public function flush() {
		flush_rewrite_rules();
	}

	/**
	 * On WP Init.
	 */
	public function on_wp_init() {
		$this->register_rewrite_rules();
		$this->register_rewrite_tags();
		$this->register_rewrite_endpoints();
	}

	/**
	 * Registers Rewrite Rules With WordPress.
	 *
	 * @static
	 */
	protected function register_rewrite_rules() {
		if ( ! empty( $this->rewrite_rule ) ) {
			foreach ( $this->rewrite_rule as $value ) {
				add_rewrite_rule( $value['regex'], $value['replace'], $value['type'] );
			}
		}
	}

	/**
	 * Registers Rewrite Tag With WordPress.
	 */
	protected function register_rewrite_tags() {
		if ( ! empty( $this->rewrite_tag ) ) {
			foreach ( $this->rewrite_tag as $param => $value ) {
				add_rewrite_tag( $param, $value );
			}
		}
	}

	/**
	 * Registers Rewrite Endpoints With WordPress.
	 */
	protected function register_rewrite_endpoints() {
		if ( ! empty( $this->rewrite_endpoint ) ) {
			foreach ( $this->rewrite_endpoint as $slug => $arr ) {
				add_rewrite_endpoint( $slug, $arr['type'] );
			}
		}
	}

	/**
	 * Adds Custom Endpoints To Endpoints Array.
	 *
	 * @param string       $endpoint
	 * @param int          $endpoint_type
	 * @param array|string $callback
	 *
	 * @example add_endpoint('hello/',EP_PAGES,'my_page_calback')
	 * @example add_endpoint('world/',EP_PAGES,array(&$this,'page_callback'))
	 *
	 * @return $this
	 */
	public function add_endpoint( $endpoint = '', $endpoint_type = EP_ROOT, $callback = array() ) {
		if ( ! isset( $this->rewrite_endpoint[ $endpoint ] ) ) {
			$this->rewrite_endpoint[ $endpoint ] = array(
				'type'     => $endpoint_type,
				'callback' => $callback,
			);
		}
		return $this;
	}

	/**
	 * Adds Custom Rewrite Rules.
	 *
	 * @param string $path
	 * @param string $after
	 *
	 * @return $this
	 */
	public function add_rewrite_rule( $path = '', $after = 'top' ) {
		$uri     = '^' . preg_replace( $this->parameter_pattern, $this->value_pattern_replace, $path );
		$url     = 'index.php?';
		$_url    = array();
		$matches = [];

		if ( preg_match_all( $this->parameter_pattern, $path, $matches ) ) {
			foreach ( $matches[1] as $id => $param ) {
				$param  = ( empty( $this->rewrite_prefix ) ) ? $param : $this->rewrite_prefix . '_' . $param;
				$_url[] = "{$param}=\$matches[" . ( $id + 1 ) . ']';
				$this->add_tag( '%' . $param . '%', '(.+)' );
			}
		}

		$this->rewrite_rule[] = array(
			'regex'   => $uri . '/?',
			'replace' => $url . '' . implode( '&', $_url ),
			'type'    => $after,
		);
		return $this;
	}

	/**
	 * Adds Rewrite Tag.
	 *
	 * @param string  $tag
	 * @param string  $regex
	 * @param boolean $force
	 *
	 * @return $this
	 */
	public function add_tag( $tag = '', $regex = '', $force = false ) {
		if ( ! isset( $this->rewrite_tag[ $tag ] ) || isset( $this->rewrite_tag[ $tag ] ) && true === $force ) {
			$this->rewrite_tag[ $tag ] = $regex;
		}
		return $this;
	}

	/**
	 * Adds Custom Query Vars TO WordPress.
	 *
	 * @param array $vars
	 *
	 * @return array
	 */
	public function add_query_vars( $vars = array() ) {
		if ( ! empty( $this->rewrite_endpoint ) ) {
			$keys = array_keys( $this->rewrite_endpoint );
			return array_merge( $vars, $keys );
		}
		return $vars;
	}
}
