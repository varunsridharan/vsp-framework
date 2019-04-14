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

namespace VSP\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'WPOnion' ) ) {
	/**
	 * Class VSP_Settings_WPOnion
	 *
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
	 */
	class WPOnion extends \VSP\Base {
		/**
		 * Default_options
		 *
		 * @var array
		 */
		protected $default_options = array(
			'option_name' => false,
			'theme'       => 'wp',
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
		 * @var \WPOnion\Modules\Settings
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
				add_action( 'wponion_loaded', array( &$this, 'init_settings' ) );
				add_action( 'vsp_show_sys_page', array( &$this, 'render_sys_page' ) );
			}
		}

		/**
		 * Adds Settings Data to vsp Syspage
		 *
		 * @param $info
		 *
		 * @return mixed
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
			$settings = get_option( $this->option( 'option_name' ), true );
			if ( ! empty( $settings ) ) {
				$data[ $this->slug( 'slug' ) . '_settings' ] = $settings;
			}
			return $data;
		}

		/**
		 * Renders Settings Page Array
		 */
		private function make_settings_arr() {
			$this->get_settings_config();
			$this->final_array();
		}

		/**
		 * Captures Settings Pages Array
		 */
		public function final_array() {
			$this->final_options = wponion_builder();
			$this->action( 'settings_options', $this->final_options );
		}

		/**
		 * Returns Settings Default Config
		 */
		public function get_settings_config() {
			$this->page_config = $this->options;

			if ( isset( $this->page_config['extra_js'] ) && is_array( $this->page_config['extra_js'] ) ) {
				$this->page_config['extra_js'][] = 'vsp_load_core_assets';
			} elseif ( isset( $this->page_config['extra_js'] ) ) {
				$this->page_config['extra_js'] = array( $this->page_config['extra_js'], 'vsp_load_core_assets' );
			} elseif ( ! isset( $this->page_config['extra_js'] ) ) {
				$this->page_config['extra_js'] = array( 'vsp_load_core_assets' );
			}
		}

		/**
		 * Inits \WPOnion\Modules\Settings Class
		 *
		 * @uses \WPOnion\Modules\Settings
		 */
		public function init_settings() {
			$this->make_settings_arr();
			if ( $this->final_options instanceof \WPO\Builder ) {
				$this->framework = new \WPOnion\Modules\Settings( $this->page_config, $this->final_options );
			}
		}
	}
}
