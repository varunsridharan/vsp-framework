<?php

namespace VSP\Modules\Addons;

defined( 'ABSPATH' ) || exit;

/**
 * Class Admin
 *
 * @package VSP\Modules\Addons
 * @author Varun Sridharan <varunsridharan23@gmail.com>
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
		$this->addon_counts['all']      = ( is_array( $this->addons ) ) ? count( $this->addons ) : 0;
		$this->addon_counts['active']   = ( is_array( $this->active_addons ) ) ? count( $this->active_addons ) : 0;
		$this->addon_counts['inactive'] = ( is_array( $this->addons ) && is_array( $this->active_addons ) ) ? count( $this->addons ) - count( $this->active_addons ) : 0;
		vsp_load_core_assets();
		include VSP_PATH . 'views/addon-page.php';
	}
}

