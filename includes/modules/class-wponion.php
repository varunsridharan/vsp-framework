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
	 * @since 0.8.9
	 */
	public function fields() {
		$builder = wponion_builder();
		$this->do_deprecated_action( 'settings_options', array( $builder ), '0.9', 'settings/fields' );
		$this->do_action( 'settings/fields', $builder );
		return $builder;
	}

	/**
	 * Runs On WPOnion Load.
	 */
	public function wpo_load() {
		$assets   = wponion_cast_array( $this->option( 'assets', array() ) );
		$assets[] = 'vsp_load_core_assets';
		$fields   = ( wponion_is_version( '1.4.6', '>' ) ) ? array( &$this, 'fields' ) : $this->fields();
		wponion_settings( $this->option(), $fields );
	}
}
