<?php

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
				->callback( array( &$this, 'render_page' ) );
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
			include VSP_PATH . 'views/addon-page.php';
		}
	}
}
