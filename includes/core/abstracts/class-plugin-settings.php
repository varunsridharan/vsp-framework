<?php
/**
 * VSP Plugin Settings Abstract Class
 * Used to auto hook with WPOnion Integration
 *
 * Created by PhpStorm.
 * User: varun
 * Date: 27-02-2018
 * Time: 09:11 AM
 *
 * @link    http://github.com/varunsridharan/vsp-framework/
 * @version 1.0
 * @since   1.0
 *
 * @package   vsp-framework/core/abstract
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @copyright 2018 Varun Sridharan
 * @license   GPLV3 Or Greater
 */

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
	 * Plugin_Settings constructor.
	 *
	 * @param string $hook_slug
	 */
	public function __construct( $hook_slug = '' ) {
		add_action( $hook_slug . '_settings_options', array( &$this, 'options' ) );
	}
}
