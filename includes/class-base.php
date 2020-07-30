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
	 * Stores Plugin's Base Class Name.
	 *
	 * @var string
	 * @since {NEWVERSION}
	 */
	protected $plugin_class = '';

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
		return ( $this instanceof Framework ) ? $this : $this->get_instance( $this->plugin_class );
	}

	/**
	 * Get the plugin url.
	 *
	 * @param string      $ex_path
	 * @param bool|string $plugin_file
	 *
	 * @return string
	 */
	public function plugin_url( $ex_path = '/', $plugin_file = false ) {
		$file = ( false !== $plugin_file ) ? $plugin_file : $this->plugin()->file();
		return untrailingslashit( plugins_url( $ex_path, $file ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @param string      $ex_path
	 * @param bool|string $plugin_file
	 *
	 * @return string
	 */
	public function plugin_path( $ex_path = '', $plugin_file = false ) {
		$file = ( false !== $plugin_file ) ? $plugin_file : $this->plugin()->file();
		$path = untrailingslashit( plugin_dir_path( $file ) );
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

	/**
	 * Triggers Given function
	 *
	 * @param string $type
	 * @param array  $args
	 *
	 * @return mixed
	 */
	private function hooker( $type = '', $args = array() ) {
		$args[0] = $this->plugin()->slug( 'hook' ) . '/' . $args[0];
		return call_user_func_array( $type, $args );
	}

	/**
	 * Triggers apply_filters
	 *
	 * @return mixed
	 * @uses \apply_filters()
	 */
	public function apply_filter() {
		return $this->hooker( 'apply_filters', func_get_args() );
	}

	/**
	 * Triggers do_action
	 *
	 * @return mixed
	 * @uses \do_action()
	 */
	public function do_action() {
		return $this->hooker( 'do_action', func_get_args() );
	}

	/**
	 * Triggers deprecated apply_filters
	 *
	 * @param string      $tag
	 * @param mixed       $args
	 * @param string      $version
	 * @param string|null $replacement
	 * @param string|null $message
	 *
	 * @return mixed
	 * @since {NEWVERSION}
	 */
	public function do_deprecated_filter( $tag, $args, $version, $replacement = null, $message = null ) {
		$tag         = $this->plugin()->slug( 'hook' ) . '_' . $tag;
		$replacement = $this->plugin()->slug( 'hook' ) . '/' . $replacement;
		return wponion_apply_deprecated_filters( $tag, $args, $version, $replacement, $message );
	}

	/**
	 * Triggers deprecated do_action
	 *
	 * @param string      $tag
	 * @param mixed       $args
	 * @param string      $version
	 * @param string|null $replacement
	 * @param string|null $message
	 *
	 * @since {NEWVERSION}
	 */
	public function do_deprecated_action( $tag, $args, $version, $replacement = null, $message = null ) {
		$tag         = $this->plugin()->slug( 'hook' ) . '_' . $tag;
		$replacement = $this->plugin()->slug( 'hook' ) . '/' . $replacement;
		return wponion_do_deprecated_action( $tag, $args, $version, $replacement, $message );
	}
}
