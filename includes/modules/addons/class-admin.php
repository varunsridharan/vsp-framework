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


namespace VSP\Modules\Addons;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'Admin' ) ) {
	/**
	 * Class Admin
	 *
	 * @package VSP\Modules\Addons
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
	 */
	abstract class Admin extends Core {

		/**
		 * Sets Settings Page to show adddons
		 *
		 * @param array|\WPO\Builder $pages
		 */
		public function link_with_wponion( $pages ) {
			$pages->container( $this->option( 'addon_listing_tab_name' ), $this->option( 'addon_listing_tab_title' ), $this->option( 'addon_listing_tab_icon' ) )
				->set_callback( array( &$this, 'render_page' ) );
		}

		/**
		 * Renders Admin Addons Page in settings framework
		 */
		public function render_page() {
			$this->addons       = false;
			$this->in_display   = true;
			$this->addon_cats   = self::$default_addon_cats;
			$this->addon_counts = array_combine( array_keys( $this->addon_cats ), array_fill( 0, count( $this->addon_cats ), 0 ) );
			$this->search_addons();

			$this->addon_counts['all']      = count( $this->addons );
			$this->addon_counts['active']   = count( $this->active_addons );
			$this->addon_counts['inactive'] = count( $this->addons ) - count( $this->active_addons );

			vsp_load_script( 'vsp-framework' );
			vsp_load_style( 'vsp-framework' );
			wp_enqueue_style( 'vsp-fancybox' );
			wp_enqueue_script( 'vsp-fancybox' );

			include VSP_PATH . 'views/addon-page.php';
		}
	}
}
