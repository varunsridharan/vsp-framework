<?php

namespace VSP;

defined( 'ABSPATH' ) || exit;

/**
 * Class Framework_Admin
 *
 * @package VSP
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 */
abstract class Framework_Admin extends Framework_Base {
	use Core\Traits\Framework;

	/**
	 * Registers Admin hook
	 *
	 * @see \VSP_Framework::__register_hooks
	 */
	public function _register_admin_hooks() {
		/** @uses admin_assets */
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ), 1 );
		/** @uses wp_admin_init */
		add_action( 'admin_init', array( $this, 'wp_admin_init' ) );
	}
}

