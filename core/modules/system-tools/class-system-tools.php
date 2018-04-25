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
	 * Handles Current View.
	 *
	 * @var null
	 */
	private $current_view = null;

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
		add_filter( $this->slug( 'hook' ) . 'settings_fields', array( &$this, 'add_fields' ), 999 );
		add_action( 'vsp_system_status_output', array( $this, 'output_sys_info' ) );
		add_action( 'vsp_system_logs_output', array( &$this, 'output_logs_info' ) );
	}

	/**
	 * Outputs Sys Info Page.
	 *
	 * @static
	 */
	public function output_sys_info() {
		if ( $this->is_view( 'sysinfo' ) ) {
			self::output_css( false );
			echo '<style>#wpsf-tab-system-tools .inside {
			margin-top: 0px;
			}
			#wpsf-tab-system-tools .postbox{
				border:0;
				box-shadow: none;
			}
			
			#wpsf-tab-system-tools-system-status > .log_wrap {
			padding-right:20px;
			}
			
			#wpsf-tab-system-tools-system-status > .wpsf-framework-box > .wpsf-row {
			padding:0;
			}
			</style>';


			echo '<div class="wpsf-framework-box">';

			$active_url = vsp_ajax_url( array(
				'action'         => 'vsp_sysinfo_remote',
				'sysinfo_action' => 'generate',
			) );

			$inactive_url = vsp_ajax_url( array(
				'action'         => 'vsp_sysinfo_remote',
				'sysinfo_action' => 'disable',
			) );

			$currenturl = vsp_get_cache( 'vsp-sysinfo-url' );

			$extra = '<strong>' . __( 'Current URL : ', 'vsp-framework' ) . '</strong>';
			$output = vsp_ajax_url( array(
				'action'  => 'vsp_sys_info',
				'vsp-key' => $currenturl,
			) );
			$extra .= '<a id="vspsysinfocurl" href="' . $output . '">' . $output . '</a>';

			echo wpsf_add_element( array(
				'id'              => 'report',
				'type'            => 'accordion',
				'open'            => true,
				'accordion_title' => __( 'Copy / Send System Report', 'vsp-framework' ),
				'fields'          => array(
					array(
						'type'    => 'content',
						'content' => __( 'Users with this URL can view a plain-text version of your System Status.  
				This link can be handy in support forums, as access to this information can be removed after you receive the help you need. <br/>
				 Generating a new URL will safely void access to all who have the existing URL.', 'vsp-framework' ),
						'after'   => '<br/> <br/> 
<button href="' . $active_url . '" class="button button-primary vsp-inline-ajax" type="button">' . __( 'Generate New URL', 'vsp-framework' ) . '</button>
<button href="' . $inactive_url . '" type="button"  class="button button-secondary vsp-inline-ajax" >' . __( 'Disable', 'vsp-framework' ) . '</button> <br/> <br/> ' . $extra,
					),

					array(
						'id'     => 'report',
						'before' => __( 'Please copy and paste this information in your ticket when contacting support:', 'vsp-framework' ),
						'type'   => 'textarea',
					),
				),
			), array( 'report' => VSP_System_Status_Report::text_output() ) );

			echo '</div>';

			echo '<div class="log_wrap">';

			echo VSP_System_Status_Report::output();
			echo '</div>';
		}
	}

	/**
	 * Checks if current view is same as the given view.
	 *
	 * @param $is
	 *
	 * @return bool
	 */
	public function is_view( $is ) {
		if ( $is === $this->current_view ) {
			return true;
		}
		return false;
	}

	/**
	 * Outputs Custom CSS Required For Settings Page.
	 */
	public static function output_css( $force = true ) {
		echo '<style> 
			div#post-body.metabox-holder.columns-2{width:100%;} 
			#postbox-container-1,.wpsf-simple-footer{display:none;}
			
		</style>';
		if ( $force ) {
			echo '<style>#wpsf-tab-system-tools .inside {
				background: #f1f1f1;
				padding: 15px 0px;
				margin-top: 0px;
				border-top: 1px solid #e4e4e4;
			}
			#wpsf-tab-system-tools .postbox{
				border:0;
				box-shadow: none;
			}</style>';
		}
	}

	public function output_logs_info() {
		if ( $this->is_view( 'logs' ) ) {
			self::output_css();
			$instance = $this->_instance( 'VSP_System_Logs', false, true, array() );
			$instance::render();
		}
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
			$menu                  = $this->menu_data( $this->option( 'system_status' ), array(
				'title' => __( 'System Status', 'vsp-framework' ),
				'icon'  => 'fa fa-info-circle',
				'name'  => 'system-status',
			) );
			$menu['callback_hook'] = 'vsp_system_status_output';

			if ( true === $is_page ) {
				$args[ $menu['name'] ] = $menu;
			} else {
				$menu['query_args']                           = $this->menu_query_args( $menu, array( 'vsp-view' => 'sysinfo' ) );
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
	 * @param array $menu
	 * @param array $extra_args
	 *
	 * @return array
	 */
	protected function menu_query_args( $menu = array(), $extra_args = array() ) {
		return array_merge( array(
			'wpsf-parent-id'  => $this->mp_slug,
			'wpsf-section-id' => $menu['name'],
		), $extra_args );
	}

	/**
	 * @param array $args
	 * @param bool  $is_page
	 *
	 * @return array
	 */
	protected function system_logs_menu( $args = array(), $is_page = true ) {
		if ( false !== $this->option( 'logging' ) ) {
			$menu                  = $this->menu_data( $this->option( 'logging' ), array(
				'title' => __( 'System Logs', 'vsp-framework' ),
				'icon'  => 'fa fa-file',
				'name'  => 'system-logs',
			) );
			$menu['callback_hook'] = 'vsp_system_logs_output';

			if ( true === $is_page ) {
				$args[ $menu['name'] ] = $menu;
			} else {
				$menu['query_args']                           = $this->menu_query_args( $menu, array( 'vsp-view' => 'logs' ) );
				$args[ $this->mp_slug . '/' . $menu['name'] ] = $menu;
			}
		}
		return $args;
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

	/**
	 * Adds Fields To Settings.
	 *
	 * @param array $fields
	 *
	 * @return mixed
	 */
	public function add_fields( $fields = array() ) {
		if ( isset( $_REQUEST['vsp-view'] ) && ! empty( $_REQUEST['vsp-view'] ) ) {
			$this->current_view = $_REQUEST['vsp-view'];
		} else {
			$this->current_view = 'sysinfo';
		}
		return $fields;
	}

}
