<?php
/**
 * VSP/WPOnion Settings Handler.
 *
 * Created by PhpStorm.
 * User: varun
 * Date: 27-02-2018
 * Time: 09:11 AM
 *
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @since     1.0
 * @package   vsp-framework/core/modules/settings
 * @copyright GPL V3 Or greater
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'VSP_Settings_WPOnion' ) ) {
	/**
	 * Class VSP_Settings_WPOnion
	 *
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
	 */
	class VSP_Settings_WPOnion extends VSP_Class_Handler {
		/**
		 * Default_options
		 *
		 * @var array
		 */
		protected $default_options = array(
			'extra_css'      => array(),
			'extra_js'       => array(),
			'option_name'    => false,
			'plugin_id'      => null,
			'is_single_page' => false,
			'menu'           => array(),
			'theme'          => 'wp',
		);

		/**
		 * Final_options
		 *
		 * @var array
		 */
		private $final_options = array();

		/**
		 * Pages
		 *
		 * @var array
		 */
		public $pages = array();

		/**
		 * Fields
		 *
		 * @var array
		 */
		public $fields = array();

		/**
		 * Sections
		 *
		 * @var array
		 */
		public $sections = array();

		/**
		 * Page_config
		 *
		 * @var array
		 */
		protected $page_config = array();

		/**
		 * Framework
		 *
		 * @var null
		 * @uses \WPOnion\Modules\Settings
		 */
		protected $framework = null;

		/**
		 * VSP_Settings_WPOnion constructor.
		 *
		 * @param array $options .
		 */
		public function __construct( $options = array() ) {
			parent::__construct( $options );

			if ( vsp_is_admin() || vsp_is_ajax() ) {
				$this->pages    = array();
				$this->fields   = array();
				$this->sections = array();
				add_filter( 'vsp_system_status_headers_vsp_plugins', array( &$this, 'set_sysinfo_headers' ) );
				add_filter( 'vsp_system_status_data', array( &$this, 'set_sysinfo_data' ) );
				add_action( 'vsp_sys_status_before_render', array( $this, 'add_settings_data' ) );
				add_action( 'wponion_loaded', array( &$this, 'init_settings' ), 40 );
				add_action( 'vsp_show_sys_page', array( &$this, 'render_sys_page' ) );
			}
		}

		/**
		 * Adds Settings Data to vsp Syspage
		 *
		 * @uses \WPOnion\Modules\Settings
		 */
		public function set_sysinfo_headers( $info ) {
			if ( ! isset( $info[ $this->slug( 'slug' ) ] ) ) {
				$info[ $this->slug( 'slug' ) ] = array(
					'name'   => $this->plugin_name(),
					'childs' => array( $this->slug( 'slug' ) . '_settings' => __( 'Settings' ) ),
				);
			} elseif ( is_array( $info[ $this->slug( 'slug' ) ] ) ) {
				$info[ $this->slug( 'slug' ) ]['childs'][ $this->slug( 'slug' ) . '_settings' ] = __( 'Settings' );
			}
			return $info;
		}

		/**
		 * @param $data
		 *
		 * @return mixed
		 */
		public function set_sysinfo_data( $data ) {
			$settings = $this->framework->get_db_options();
			if ( $settings ) {
				$data[ $this->slug( 'slug' ) . '_settings' ] = $this->framework->get_db_options();
			}
			return $data;
		}

		/**
		 * @param $class
		 */
		public function add_settings_data( $class ) {
			if ( ! isset( $class->vsp_settings ) ) {
				$class->vsp_settings = array();
			}
			$class->vsp_settings[ $this->slug() ] = $this->framework->get_db_options();
		}

		/**
		 * Renders Settings Page Array
		 */
		private function make_settings_arr() {
			$this->get_settings_config();
			$this->settings_pages();
			$this->settings_sections();
			$this->settings_fields();
			$this->final_array();
		}

		/**
		 * Captures Settings Pages Array
		 */
		public function settings_pages() {
			$this->pages = $this->filter( 'settings_pages', $this->pages );
		}

		/**
		 * Captures Settings Sections Array
		 */
		public function settings_sections() {
			$this->sections = $this->filter( 'settings_sections', $this->sections );
		}

		/**
		 * Captures Settings Fields Array
		 */
		public function settings_fields() {
			$this->fields = $this->filter( 'settings_fields', $this->fields );
		}

		/**
		 * Returns Settings Default Config
		 */
		public function get_settings_config() {
			$defaults = array_keys( $this->default_options );

			$this->page_config = array();
			foreach ( $defaults as $op ) {
				$this->page_config[ $op ] = $this->option( $op, '' );
			}

			if ( empty( $this->page_config['buttons'] ) ) {
				unset( $this->page_config['buttons'] );
			}

			if ( isset( $this->page_config['extra_js'] ) && is_array( $this->page_config['extra_js'] ) ) {
				$this->page_config['extra_js'][] = 'vsp_load_core_assets';
			} else {
				$this->page_config['extra_js'] = array( $this->page_config['extra_js'], 'vsp_load_core_assets' );
			}
		}

		/**
		 * Returns All Final Array
		 */
		public function final_array() {
			$pages = $this->pages;
			foreach ( $this->sections as $i => $v ) {
				list( $page, $section ) = explode( '/', $i );
				if ( isset( $pages[ $page ] ) ) {
					if ( ! isset( $pages[ $page ]['sections'] ) ) {
						$pages[ $page ]['sections'] = array();
					}

					$pages[ $page ]['sections'][ $section ] = $v;
				}
			}

			foreach ( $this->fields as $id => $fields ) {
				$page    = explode( '/', $id );
				$section = isset( $page[1] ) ? $page[1] : null;
				$page    = $page[0];

				if ( null === $section ) {
					if ( isset( $pages[ $page ] ) && ! isset( $pages['section'] ) ) {
						if ( ! isset( $pages[ $page ]['fields'] ) ) {
							$pages[ $page ]['fields'] = array();
						}

						$pages[ $page ]['fields'] = array_merge( $pages[ $page ]['fields'], $fields );
					}
				} else {
					if ( isset( $pages[ $page ] ) && ! isset( $pages['fields'] ) ) {
						if ( ! isset( $pages[ $page ]['sections'][ $section ]['fields'] ) ) {
							$pages[ $page ]['sections'][ $section ]['fields'] = array();
						}

						$pages[ $page ]['sections'][ $section ]['fields'] = array_merge( $pages[ $page ]['sections'][ $section ]['fields'], $fields );
					}
				}
			}

			$this->final_options = $pages;
		}

		/**
		 * Inits \WPOnion\Modules\Settings Class
		 *
		 * @uses \WPOnion\Modules\Settings
		 */
		public function init_settings() {
			$this->make_settings_arr();
			$this->framework = new \WPOnion\Modules\Settings( $this->page_config, $this->final_options );
		}
	}
}
