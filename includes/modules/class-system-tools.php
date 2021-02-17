<?php

namespace VSP\Modules;

defined( 'ABSPATH' ) || exit;

use VSP\Base;

/**
 * Class VSP_System_Tools
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 */
class System_Tools extends Base {
	/**
	 * @var null
	 */
	private $slug = null;

	/**
	 * Default_options
	 *
	 * @return array
	 */
	protected function defaults() {
		return array(
			'system_tools' => true,
			'menu'         => true,
			'logging'      => true,
		);
	}

	/**
	 * VSP_System_Tools constructor.
	 *
	 * @param array $options
	 */
	public function __construct( $options = array() ) {
		$this->set_args( $options );
		$this->add_action( $this->plugin()->slug( 'hook' ) . '/settings/fields', 'options', 999 );
	}

	/**
	 * Outputs Logs View.
	 */
	public function output_logs_info() {
		$instance = $this->_instance( '\VSP\Modules\System_Logs' );
		$instance->render();
	}

	/**
	 * Adds Pages To Settings.
	 *
	 * @param array|\WPO\Container $builder
	 */
	public function options( $builder ) {
		if ( false === $this->option( 'system_tools' ) ) {
			$this->system_logs_menu( $builder, true );
		} else {
			$menu       = $this->menu_data( $this->option( 'menu' ), array(
				'title' => esc_html__( 'System Tools', 'vsp-framework' ),
				'icon'  => 'fa fa-gear',
				'name'  => 'system-tools',
			) );
			$this->slug = $menu['name'];
			$builder->container( $menu['name'], $menu['title'], $menu['icon'] );
			$this->system_logs_menu( $builder, false );
		}
	}

	/**
	 * @param string|array|bool $given_data
	 * @param array             $default
	 *
	 * @return array
	 */
	private function menu_data( $given_data, $default = array() ) {
		if ( is_bool( $given_data ) ) {
			return $default;
		} elseif ( is_string( $given_data ) ) {
			$default['title'] = $given_data;
			$default['name']  = sanitize_title( $given_data );
		} elseif ( is_array( $given_data ) ) {
			$default = array_merge( $default, $given_data );
		}
		return $default;
	}

	/**
	 * @param array|\WPO\Builder $args
	 * @param bool               $is_page
	 *
	 * @return array
	 */
	protected function system_logs_menu( $args = array(), $is_page = true ) {
		if ( false !== $this->option( 'logging' ) ) {
			$m   = $this->menu_data( $this->option( 'logging' ), array(
				'title' => esc_html__( 'System Logs', 'vsp-framework' ),
				'icon'  => 'fa fa-file',
				'name'  => 'system-logs',
			) );
			$box = ( true === $is_page ) ? $args : $args->container( $this->slug );
			if ( wpo_is_container( $box ) || wpo_is( $box ) ) {
				$box->container( $m['name'], $m['title'], $m['icon'] )->callback( array( $this, 'output_logs_info' ) );
			}
		}
		return $args;
	}
}
