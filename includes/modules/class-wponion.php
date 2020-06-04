<?php

namespace VSP\Modules;

defined( 'ABSPATH' ) || exit;

use VSP\Base;

/**
 * Class VSP_Settings_WPOnion
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 */
class WPOnion extends Base {
	/**
	 * WPOnion constructor.
	 *
	 * @param array $options
	 */
	public function __construct( $options = array() ) {
		$this->set_args( $options );
		$this->wpo_load();
	}

	/**
	 * Filters & Returns Fields.
	 *
	 * @return mixed
	 * @since {NEWVERSION}
	 */
	public function fields() {
		return $this->plugin()->do_action( 'settings_options', wponion_builder() );
	}

	/**
	 * Runs On WPOnion Load.
	 */
	public function wpo_load() {
		$assets   = wponion_cast_array( $this->option( 'assets', array() ) );
		$assets[] = 'vsp_load_core_assets';
		$fields   = ( wponion_is_version( '1.3.6' ) ) ? $this->fields() : array( &$this, 'fields' );
		wponion_settings( $this->option(), $fields );
	}
}
