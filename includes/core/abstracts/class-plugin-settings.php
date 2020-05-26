<?php

namespace VSP\Core\Abstracts;

defined( 'ABSPATH' ) || exit;

use VSP\Base;

/**
 * Class Plugin_Settings
 *
 * @package VSP\Core\Abstracts
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 */
abstract class Plugin_Settings extends Base {
	/**
	 * @var \WPO\Builder
	 */
	protected $builder = false;

	/**
	 * Plugin_Settings constructor.
	 */
	public function __construct() {
		$slug = $this->plugin();
		add_action( $slug->slug( 'hook' ) . '_settings_options', array( &$this, 'options' ) );
	}

	/**
	 * @param \WPO\Builder $builder
	 */
	public function options( $builder ) {
		$this->builder = $builder;
		$this->fields();
	}

	/**
	 * Inits Fields
	 */
	abstract protected function fields();
}
