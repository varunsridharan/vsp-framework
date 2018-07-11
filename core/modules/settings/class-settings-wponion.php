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
			'show_faqs'         => true,
			'menu_parent'       => false,
			'menu_title'        => false,
			'menu_type'         => false,
			'menu_slug'         => false,
			'menu_icon'         => false,
			'menu_position'     => false,
			'menu_capability'   => false,
			'ajax_save'         => false,
			'show_reset_all'    => false,
			'framework_title'   => false,
			'option_name'       => false,
			'style'             => 'modern',
			'override_location' => VSP_PATH . 'views/settings/',
			'is_single_page'    => false,
			'is_sticky_header'  => false,
			'extra_css'         => array( 'vsp-plugins', 'vsp-framework' ),
			'extra_js'          => array( 'vsp-plugins', 'vsp-framework' ),
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
				add_action( 'vsp_wp_settings_simple_footer', array( &$this, 'render_settings_metaboxes' ) );
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
					'childs' => array(
						$this->slug( 'slug' ) . '_settings' => __( 'Settings' ),
					),
				);
			} elseif ( is_array( $info[ $this->slug( 'slug' ) ] ) ) {
				$info[ $this->slug( 'slug' ) ]['childs'][ $this->slug( 'slug' ) . '_settings' ] = __( 'Settings' );
			}
			return $info;
		}

		public function set_sysinfo_data( $data ) {
			$data[ $this->slug( 'slug' ) . '_settings' ] = $this->framework->get_db_options();
			return $data;
		}


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
			$defaults = array(
				'menu_parent',
				'menu_title',
				'menu_type',
				'menu_slug',
				'menu_icon',
				'menu_position',
				'menu_capability',
				'ajax_save',
				'show_reset_all',
				'framework_title',
				'option_name',
				'style',
				'is_single_page',
				'is_sticky_header',
				'extra_css',
				'extra_js',
				'buttons',
			);

			$this->page_config = array();
			foreach ( $defaults as $op ) {
				$this->page_config[ $op ] = $this->option( $op, '' );
			}

			if ( empty( $this->page_config['buttons'] ) ) {
				unset( $this->page_config['buttons'] );
			}

			if ( ! isset( $this->page_config['override_location'] ) || empty( $this->page_config['override_location'] ) ) {
				$this->page_config['override_location'] = VSP_PATH . 'views/';
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
				$section = null;
				$page    = explode( '/', $id );

				if ( isset( $page[1] ) ) {
					$section = $page[1];
				}

				$page = $page[0];

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
			#$this->framework = new WPSFramework_Settings( $this->page_config, $this->final_options );
			$this->framework = new \WPOnion\Modules\Settings( $this->page_config, $this->final_options );
		}

		/**
		 * Outputs Settings Metabox
		 *
		 * @uses VSP_Settings_Metabox
		 */
		public function render_settings_metaboxes() {
			$args = $this->get_common_args( array(
				'show_faqs' => $this->option( 'show_faqs' ),
			) );
			$adds = new VSP_Settings_Metabox( $args );
			$adds->render_metaboxes();
		}
	}
}
