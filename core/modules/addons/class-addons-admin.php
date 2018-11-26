<?php
/**
 * VSP Plugin Addon Admin Class.
 *
 * Created by PhpStorm.
 * User: varun
 * Date: 27-02-2018
 * Time: 09:11 AM
 *
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @since     1.0
 * @package   vsp-framework/core/modules/addons
 * @copyright GPL V3 Or greater
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'VSP_Addons_Admin' ) ) {
	/**
	 * Class VSP_Addons_Admin
	 *
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
	 */
	class VSP_Addons_Admin extends VSP_Addons_Core {

		/**
		 * VSP_Addons_Admin constructor.
		 */
		public function __construct() {
			parent::__construct();
			$this->addons_list       = array();
			$this->settings_pagehook = '';
		}

		/**
		 * Sets Settings Page to show adddons
		 *
		 * @param array $pages .
		 *
		 * @return mixed
		 */
		public function set_settings_page( $pages ) {
			$pages[ $this->option( 'addon_listing_tab_name' ) ] = array(
				'name'     => $this->option( 'addon_listing_tab_name' ),
				'title'    => $this->option( 'addon_listing_tab_title' ),
				'icon'     => $this->option( 'addon_listing_tab_icon' ),
				'callback' => array( &$this, 'render_addons_page' ),
			);

			return $pages;
		}

		/**
		 * Renders Admin Addons Page in settings framework
		 */
		public function render_addons_page() {
			$this->addons_list = $this->search_get_addons();

			foreach ( $this->addons_list as $id => $data ) {
				$this->addons_list[ $id ]['is_active'] = ( $this->is_active( $id, $data['addon_path_md5'] ) === false ) ? false : true;
				unset( $this->addons_list[ $id ]['addon_path'] );
			}

			vsp_load_script( 'vsp-addons' );
			vsp_load_style( 'vsp-addons' );
			wp_enqueue_style( 'vsp-fancybox' );
			wp_enqueue_script( 'vsp-fancybox' );
			wp_enqueue_script( 'plugin-install' );

			add_thickbox();

			wp_localize_script( 'vsp-addons', 'vsp_addons_settings', array(
				'hook_slug'     => $this->slug( 'hook' ),
				'save_slug'     => $this->slug( 'db' ),
				'plugin_data'   => $this->addons_list,
				'default_cats'  => $this->default_cats,
				'texts'         => array(
					'required_plugin' => __( 'Required Plugin', 'vsp-framework' ),
					'required_desc'   => __( 'Above Mentioned Plugin name with version are Tested Upto', 'vsp-framework' ),
					'activate_btn'    => __( 'Activate', 'vsp-framework' ),
					'deactivate_btn'  => __( 'De Activate', 'vsp-framework' ),
					'admin_url'       => admin_url(),
					'plugin_view_url' => admin_url( 'plugin-install.php?&isvspaddon=true&tab=plugin-information&plugin={{slug}}&pathid={{addon.addon_path_md5}}&TB_iframe=true&width=600&height=800' ),
				),
				'plugin_status' => array(
					'exists'    => __( 'In Active', 'vsp-framework' ),
					'notexist'  => __( 'Not Exist', 'vsp-framework' ),
					'activated' => __( 'Active', 'vsp-framework' ),
				),
			) );

			include __DIR__ . '/page-template.html';
		}
	}
}
