<?php

namespace VSP\Modules;

use VSP\Base;
use VSP\Core\Interfaces\Plugin_Settings;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( class_exists( '\VSP\Modules\System_Tools' ) ) {
	return;
}

/**
 * Class VSP_System_Tools
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @since 1.0
 */
class System_Tools extends Base {
	/**
	 * Default_options
	 *
	 * @var array
	 */
	protected $default_options = array(
		'system_tools' => true,
		'menu'         => true,
		'logging'      => true,
	);

	/**
	 * mp_slug
	 *
	 * @var null
	 */
	private $mp_slug = null;

	/**
	 * VSP_System_Tools constructor.
	 *
	 * @param array $options
	 */
	public function __construct( $options = array() ) {
		$this->set_args( $options );
		$slug = $this->plugin()
			->slug( 'hook' );
		add_action( $slug . '_settings_options', array( &$this, 'options' ), 999 );
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
			$menu = $this->menu_data( $this->option( 'menu' ), array(
				'title' => __( 'System Tools', 'vsp-framework' ),
				'icon'  => 'fa fa-gear',
				'name'  => 'system-tools',
			) );
			$builder->container( $menu['name'], $menu['title'], $menu['icon'] );
			$this->mp_slug = $menu['name'];
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
			$menu = $this->menu_data( $this->option( 'logging' ), array(
				'title' => __( 'System Logs', 'vsp-framework' ),
				'icon'  => 'fa fa-file',
				'name'  => 'system-logs',
			) );

			$container = ( true === $is_page ) ? $args : $args->container( $this->mp_slug );
			if ( wpo_is_container( $container ) || wpo_is( $container ) ) {
				$container->container( $menu['name'], $menu['title'], $menu['icon'] )
					->callback( array( &$this, 'output_logs_info' ) );
			}
		}
		return $args;
	}
}
