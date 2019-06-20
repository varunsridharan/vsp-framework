<?php

namespace VSP;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( '\VSP\Framework_Admin' ) ) {
	/**
	 * Class Framework_Admin
	 *
	 * @package VSP
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
	 */
	abstract class Framework_Admin extends Framework_Base implements Core\Interfaces\Framework_Interface {
		use Core\Traits\Framework;

		/**
		 * Registers Admin hook
		 *
		 * @see \VSP_Framework::__register_hooks
		 */
		public function _register_admin_hooks() {
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ), 1 );
			add_action( 'admin_init', array( $this, 'wp_admin_init' ) );
		}
	}
}
