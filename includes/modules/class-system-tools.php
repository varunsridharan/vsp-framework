<?php
/**
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @version 1.0
 * @since 1.0
 *
 * Date 17-04-2018
 * Time 10:23 AM
 *
 * @package   vsp-framework/core/modules/system-tools
 * @link http://github.com/varunsridharan/vsp-framework
 * @copyright 2018 Varun Sridharan
 * @license GPLV3 Or Greater (https://www.gnu.org/licenses/gpl-3.0.txt)
 */

namespace VSP\Modules;

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
class System_Tools extends \VSP\Base implements \VSP\Core\Interfaces\Plugin_Settings {
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
	 * @param array $defaults
	 */
	public function __construct( $options = array(), $defaults = array() ) {
		parent::__construct( $options, $defaults );
		add_action( $this->slug( 'hook' ) . '_settings_options', array( &$this, 'options' ), 999 );
	}

	/**
	 * Outputs Logs View.
	 */
	public function output_logs_info() {
		$instance = $this->_instance( '\VSP\Modules\System_Logs', false, true, array() );
		$instance::render();
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
	 * @param       $given_data
	 * @param array $default
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
			if ( true === $is_page ) {
				$args->container( $menu['name'], $menu['title'], $menu['icon'] )
					->set_callback( array( &$this, 'output_logs_info' ) );
			} else {
				$base = $args->container( $this->mp_slug );
				if ( $base instanceof \WPO\Container ) {
					$base->container( $menu['name'], $menu['title'], $menu['icon'] )
						->set_callback( array( &$this, 'output_logs_info' ) );
				}
			}
		}
		return $args;
	}
}
