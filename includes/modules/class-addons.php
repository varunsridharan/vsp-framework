<?php

namespace VSP\Modules;

defined( 'ABSPATH' ) || exit;

/**
 * Class VSP_Addons
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 */
class Addons extends Addons\Admin {
	/**
	 * @return array
	 */
	protected function defaults() {
		return array(
			'base_path'               => '',
			'base_url'                => '',
			'addon_listing_tab_name'  => 'addons',
			'addon_listing_tab_title' => 'Addons',
			'addon_listing_tab_icon'  => 'fa fa-plus',
			'headers'                 => array(),
			'show_category_count'     => true,
			'hook_priority'           => 99,
		);
	}

	/**
	 * VSP_Addons constructor.
	 *
	 * @param array $options .
	 */
	public function __construct( $options = array() ) {
		$this->set_args( $options );
		self::$default_addon_cats      = array(
			'all'      => esc_html__( 'All', 'vsp-framework' ),
			'active'   => esc_html__( 'Active', 'vsp-framework' ),
			'inactive' => esc_html__( 'Inactive', 'vsp-framework' ),
			'general'  => esc_html__( 'General', 'vsp-framework' ),
		);
		self::$required_plugins_status = array(
			'notexists' => esc_html__( 'Not Installed', 'vsp-framework' ),
			'exists'    => esc_html__( 'In Active', 'vsp-framework' ),
			'active'    => esc_html__( 'Active', 'vsp-framework' ),
		);
		$this->headers                 = $this->parse_args( $this->option( 'headers' ), $this->default_headers );
		$slug                          = $this->plugin()->slug( 'hook' );
		$hook                          = $this->option( 'hook_priority' );
		$this->active_addons();
		$this->load_active_addons();

		if ( vsp_is_admin() ) {
			$this->add_action( $slug . '/settings/fields', 'link_with_wponion', $hook );
		}

		if ( vsp_is_ajax() ) {
			add_action( $slug . '/addon/ajax/handle/request', array( $this, 'handle_ajax_request' ) );
		}
	}

	/**
	 * @param \VSP\Ajax $ajax
	 */
	public function handle_ajax_request( $ajax ) {
		$addon  = $ajax->post( 'addon' );
		$action = $ajax->request( 'addon_action' );

		if ( empty( $addon ) ) {
			$ajax->error( esc_html__( 'Invalid Addon', 'vsp-framework' ) );
		}

		if ( ! in_array( $action, array( 'activate', 'deactivate' ), true ) ) {
			$ajax->error( esc_html__( 'Invalid Addon Action', 'vsp-framework' ) );
		}

		switch ( $action ) {
			case 'activate':
				if ( ! $this->is_active( $addon ) ) {
					$data = $this->search_addon( $addon );

					if ( ! is_array( $data ) ) {
						vsp_send_callback_error( swal2_error( esc_html__( 'Addon Not Found', 'vsp-framework' ), esc_html__( 'Selected Addon Not Found. Please Contact The Developer', 'vsp-framework' ) ) );
					}

					if ( isset( $data['required_plugins'] ) && is_array( $data['required_plugins'] ) && ! empty( $data['required_plugins'] ) && true !== $data['required_plugins_fulfilled'] ) {
						$msg = swal2_error( esc_html__( 'Activation Failed', 'vsp-framework' ), esc_html__( 'Addon\'s Requried Plugins Not Active / Installed', 'vsp-framework' ) );
						vsp_send_callback_error( $msg );
					}

					if ( $this->activate_addon( $addon ) ) {
						vsp_send_callback_success( swal2_success( esc_html__( 'Addon Activated', 'vsp-framework' ) ) );
					}
				}
				vsp_send_callback_error( swal2_warning( esc_html__( 'Addon Already Active', 'vsp-framework' ) ) );
				break;
			case 'deactivate':
				if ( $this->is_active( $addon ) && $this->deactivate_addon( $addon ) ) {
					vsp_send_callback_success( swal2_warning( esc_html__( 'Addon De-Activated', 'vsp-framework' ) ) );
				}
				vsp_send_callback_error( swal2_warning( esc_html__( 'Addon Is Not Active', 'vsp-framework' ) ) );
				break;
		}
	}

	/**
	 * Loads Active Addons.
	 */
	public function load_active_addons() {
		$active_addons       = $this->active_addons();
		$msg                 = esc_html__( 'Following addons are deactivated because some of its required plugins are deactivated / uninstalled', 'vsp-framework' );
		$deactivated_plugins = '';
		if ( ! empty( $active_addons ) ) {
			foreach ( $active_addons as $pathid ) {
				$is_active = $this->is_active( $pathid );
				if ( false !== $is_active ) {
					$addon_data = $this->search_addon( $pathid );
					if ( empty( $addon_data ) ) {
						$deactivated_plugins .= '<li>' . vsp_slashit( basename( $addon_data['addon_path'] ) ) . $addon_data['file'] . '</li>';
						$this->deactivate_addon( $pathid );
						continue;
					}
					if ( isset( $addon_data['required_plugins'] ) && is_array( $addon_data['required_plugins'] ) && ! empty( $addon_data['required_plugins'] ) && true !== $addon_data['required_plugins_fulfilled'] ) {
						$deactivated_plugins .= '<li>' . $addon_data['name'] . '</li>';
						$this->deactivate_addon( $pathid );
						continue;
					}
					$full_path = vsp_slashit( $addon_data['addon_path'] ) . $addon_data['file'];
					vsp_load_file( $full_path );
				}
			}
		}
		if ( ! empty( $deactivated_plugins ) ) {
			$title = '<strong>' . $this->plugin()
					->name() . '</strong>' . esc_html__( ' Has Deactivated Some of its addons', 'vsp-framework' );
			$msg   = $msg . '<ul>' . $deactivated_plugins . '</ul>';
			$msg   .= '<p><button class="button button-secondary wpo-stick-dismiss">' . esc_html__( 'I Understand. Will Fix It', 'vsp-framework' ) . '</button></p>';

			wponion_error_admin_notice( $msg, $title, array( 'large' => true ) )->set_sticky( true );
		}
	}

	/**
	 * Returns All Active Addons For current plugin
	 *
	 * @return array|false
	 */
	public function active_addons() {
		if ( false === $this->active_addons ) {
			$this->active_addons = wponion_cast_array( get_option( $this->plugin()->slug( 'db' ) . '_active_addons' ) );
		}
		return $this->active_addons;
	}

	/**
	 * Handles Ajax Request Params For Addons
	 *
	 * @param string $request .
	 * @param string $msg .
	 *
	 * @return bool
	 */
	public function handle_ajax_params( $request, $msg ) {
		if ( isset( $_POST[ $request ] ) ) {
			return $_POST[ $request ];
		}
		wp_send_json_error( $msg );
		return false;
	}

	/**
	 * Updates Active Addons in db
	 *
	 * @param array $addons .
	 *
	 * @return array
	 */
	public function update_active_addons( $addons ) {
		update_option( $this->plugin()->slug( 'db' ) . '_active_addons', $addons );
		$this->active_addons = $addons;
		return $this->active_addons;
	}

	/**
	 * Activates Selected Addon.
	 *
	 * @param string $addon_uid Addons MD5 Unique ID
	 *
	 * @return bool
	 */
	public function activate_addon( $addon_uid = '' ) {
		$active_addons = $this->active_addons();
		if ( ! in_array( $addon_uid, $active_addons, true ) ) {
			$active_addons[] = $addon_uid;
			$this->update_active_addons( $active_addons );
			return true;
		}
		return false;
	}

	/**
	 * Deactivates Selected Addon
	 *
	 * @param string $addon_uid Addons MD5 Unique ID
	 *
	 * @return bool
	 */
	public function deactivate_addon( $addon_uid = '' ) {
		$active_addons = $this->active_addons();
		if ( in_array( $addon_uid, $active_addons, true ) ) {
			foreach ( $active_addons as $k => $id ) {
				if ( $id === $addon_uid ) {
					unset( $active_addons[ $k ] );
				}
			}
			$this->update_active_addons( $active_addons );
			return true;
		}
		return false;
	}

	/**
	 * Checks if given addon is active
	 *
	 * @param string $addon_uid Addons MD5 Unique ID
	 *
	 * @return bool|mixed
	 */
	public function is_active( $addon_uid = '' ) {
		$addons = $this->active_addons();
		return ( in_array( $addon_uid, $addons, true ) );
	}
}
