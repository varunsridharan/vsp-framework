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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'VSP_System_Tools' ) ) {
	return;
}

/**
 * Class VSP_System_Tools
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @since 1.0
 */
class VSP_System_Tools extends VSP_Class_Handler implements VSP_Plugin_Settings_Interface {

	/**
	 * Default_options
	 *
	 * @var array
	 */
	protected $default_options = array(
		'system_tools_menu' => true,
		'menu'              => true,
		'system_status'     => true,
		'logging'           => true,
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
		add_filter( $this->slug( 'hook' ) . 'settings_pages', array( &$this, 'add_pages' ), 999 );
		add_filter( $this->slug( 'hook' ) . 'settings_sections', array( &$this, 'add_sections' ), 999 );
	}

	/**
	 * Outputs Logs View.
	 */
	public function output_logs_info() {
		$instance = $this->_instance( 'VSP_System_Logs', false, true, array() );
		$instance::render();
	}

	/**
	 * Adds Pages To Settings.
	 *
	 * @param array $pages
	 *
	 * @return mixed
	 */
	public function add_pages( $pages = array() ) {
		if ( false === $this->option( 'system_tools_menu' ) ) {
			$pages = $this->system_status_menu( $pages, true );
			$pages = $this->system_logs_menu( $pages, true );
		} else {
			$menu                   = $this->menu_data( $this->option( 'menu' ), array(
				'title' => __( 'System Tools', 'vsp-framework' ),
				'icon'  => 'fa fa-gear',
				'name'  => 'system-tools',
			) );
			$pages[ $menu['name'] ] = $menu;
			$this->mp_slug          = $menu['name'];
		}
		return $pages;
	}

	/**
	 * Adds System Status Admin Page & Section Based on the settings.
	 *
	 * @param array $args
	 * @param bool  $is_page
	 *
	 * @return array
	 */
	protected function system_status_menu( $args = array(), $is_page = true ) {
		if ( false !== $this->option( 'system_status' ) ) {
			$menu             = $this->menu_data( $this->option( 'system_status' ), array(
				'title'          => __( 'System Status', 'vsp-framework' ),
				'icon'           => 'fa fa-info-circle',
				'name'           => 'system-status',
				'custom_reports' => array( $this, 'custom_sysinfo_reports' ),
			) );
			$menu['callback'] = 'wponion_sysinfo';

			if ( true === $is_page ) {
				$args[ $menu['name'] ] = $menu;
			} else {
				$args[ $this->mp_slug . '/' . $menu['name'] ] = $menu;
			}
		}
		return $args;
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
	 * @param array $args
	 * @param bool  $is_page
	 *
	 * @return array
	 */
	protected function system_logs_menu( $args = array(), $is_page = true ) {
		if ( false !== $this->option( 'logging' ) ) {
			$menu             = $this->menu_data( $this->option( 'logging' ), array(
				'title' => __( 'System Logs', 'vsp-framework' ),
				'icon'  => 'fa fa-file',
				'name'  => 'system-logs',
			) );
			$menu['callback'] = array( &$this, 'output_logs_info' );

			if ( true === $is_page ) {
				$args[ $menu['name'] ] = $menu;
			} else {
				$args[ $this->mp_slug . '/' . $menu['name'] ] = $menu;
			}
		}
		return $args;
	}

	/**
	 * Custom Hook To Add Custom Sys Info Datas.
	 *
	 * @return mixed
	 */
	public function custom_sysinfo_reports() {
		return apply_filters( 'vsp_system_status_data', array() );
	}

	/**
	 * Adds Sections To Settings.
	 *
	 * @param array $sections
	 *
	 * @return mixed
	 */
	public function add_sections( $sections = array() ) {
		if ( false !== $this->option( 'system_tools_menu' ) && ! is_null( $this->mp_slug ) ) {
			$sections = $this->system_status_menu( $sections, false );
			$sections = $this->system_logs_menu( $sections, false );
		}
		return $sections;
	}

	public function add_fields( $fields = array() ) {
		// TODO: Implement add_fields() method.
	}

}
