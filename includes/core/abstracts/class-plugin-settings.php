<?php

namespace VSP\Core\Abstracts;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Class Plugin_Settings
 *
 * @package VSP\Core\Abstracts
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @since 1.0
 */
abstract class Plugin_Settings implements \VSP\Core\Interfaces\Plugin_Settings {
	/**
	 * Stores Builder.
	 *
	 * @var bool|\WPO\Builder
	 * @access
	 */
	protected $builder = false;

	/**
	 * Plugin_Settings constructor.
	 *
	 * @param string $hook_slug
	 */
	public function __construct( $hook_slug = '' ) {
		add_action( $hook_slug . '_settings_options', array( &$this, 'options' ) );
	}

	/**
	 * @param array|\WPO\Builder $builder
	 *
	 * @return mixed|void
	 */
	public function options( $builder ) {
		$this->builder = $builder;
		$this->fields();
	}

	/**
	 * INITS Settings Fields.
	 */
	protected function fields() {
	}
}
