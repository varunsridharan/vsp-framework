<?php

namespace VSP\Core\Abstracts;

use ReflectionClass;
use VSP\Core\Instance_Handler;
use WPOnion\Traits\Class_Options;
use WPOnion\Traits\Hooks;

defined( 'ABSPATH' ) || exit;

/**
 * Class VSP_Log_Handler
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 */
abstract class Addon extends Instance_Handler {
	use Class_Options;
	use Hooks;

	/**
	 * Stors Addon File Location String.
	 *
	 * @var string
	 */
	private $addon_file = '';

	/**
	 * Stores Hook Priority
	 *
	 * @var int
	 */
	protected $settings_priority = 20;

	/**
	 * Addon constructor.
	 */
	public function __construct() {
		$slug = $this->plugin()->slug( 'hook' );
		$this->add_action( $slug . '_settings_options', 'settings', $this->settings_priority );
		$this->init();
	}

	/**
	 * Triggers Right After construct Call.
	 *
	 * @return mixed
	 */
	abstract protected function init();

	/**
	 * This function should return parent plugins instance.
	 *
	 * @return \VSP\Framework
	 */
	abstract protected function plugin();

	/**
	 * Finds And Returns Valid Addon Path File.
	 *
	 * @return false|string
	 */
	protected function addon_file() {
		if ( empty( $this->addon_file ) ) {
			$reflector        = new ReflectionClass( static::class );
			$this->addon_file = $reflector->getFileName();
		}
		return $this->addon_file;
	}

	/**
	 * Get the plugin url.
	 *
	 * @param string      $ex_path
	 * @param bool|string $addon_file
	 *
	 * @return string
	 */
	public function addon_url( $ex_path = '/', $addon_file = false ) {
		$file = ( false !== $addon_file ) ? $addon_file : $this->addon_file();
		return untrailingslashit( plugins_url( $ex_path, $file ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @param string      $ex_path
	 * @param bool|string $addon_file
	 *
	 * @return string
	 */
	public function addon_path( $ex_path = '', $addon_file = false ) {
		$file = ( false !== $addon_file ) ? $addon_file : $this->addon_file();
		$path = untrailingslashit( plugin_dir_path( $file ) );
		return ( empty( $ex_path ) ) ? $path : $path . '/' . $ex_path;
	}

	/**
	 * @param \WPO\Builder $builder WPOnion's Builder Instance.
	 */
	abstract public function settings( $builder );

}
