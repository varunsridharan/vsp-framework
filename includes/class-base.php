<?php

namespace VSP;

use WPOnion\Traits\Class_Options;
use WPOnion\Traits\Hooks;

defined( 'ABSPATH' ) || exit;

/**
 * Class VSP_Class_Handler
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 */
class Base extends Core\Instance_Handler {
	use Class_Options;
	use Hooks;

	/**
	 * Class Clone.
	 */
	public function __clone() {
		vsp_doing_it_wrong( __FUNCTION__, __( 'Cloning instances of the class is forbidden.', 'vsp-framework' ), $this->plugin()
			->version() );
	}

	/**
	 * Class Wakeup.
	 */
	public function __wakeup() {
		vsp_doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of the class is forbidden.', 'vsp-framework' ), $this->plugin()
			->version() );
	}

	/**
	 * Returns Plugin's Instance.
	 *
	 * @return \VSP\Framework
	 */
	public function plugin() {
		return ( $this instanceof Framework ) ? $this : $this->get_instance( self::$framework_instance[ static::class ] );
	}

	/**
	 * Get the plugin url.
	 *
	 * @param string $ex_path
	 *
	 * @return string
	 */
	public function plugin_url( $ex_path = '/' ) {
		return untrailingslashit( plugins_url( $ex_path, $this->plugin()->file() ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @param string $ex_path
	 *
	 * @return string
	 */
	public function plugin_path( $ex_path = '' ) {
		$path = untrailingslashit( plugin_dir_path( $this->plugin()->file() ) );
		return ( empty( $ex_path ) ) ? $path : $path . '/' . $ex_path;
	}

	/**
	 * Loads A Required File.
	 *
	 * @param string $file
	 * @param bool   $is_internal
	 */
	public function load_file( $file = '', $is_internal = true ) {
		$file = ( $is_internal ) ? $this->plugin_path( $file ) : $file;
		vsp_load_file( $file );
	}
}
