<?php

namespace VSP\Core\Interfaces;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Interface VSP_Plugin_Settings_Interface
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @since 1.0
 */
interface Plugin_Settings {
	/**
	 * Settings Args
	 *
	 * @param array|\WPO\Builder $builder .
	 *
	 * @return mixed
	 */
	public function options( $builder );
}
