<?php

namespace VSP\Deprecation;

use WPOnion\Bridge\Deprecated_Hooks;

defined( 'ABSPATH' ) || exit;

/**
 * Handles deprecation notices and triggering of legacy action hooks.
 */
class Actions extends Deprecated_Hooks {
	/**
	 * Array of deprecated hooks we need to handle.
	 * Format of 'new' => 'old'.
	 *
	 * @var array
	 */
	protected $deprecated_hooks = array(
		'vsp/loaded'     => 'vsp_framework_loaded',
		'vsp/log/clear'  => 'vsp_log_clear',
		'vsp/log/remove' => 'vsp_log_remove',
		'vsp/init'       => 'vsp_framework_init',
	);

	/**
	 * Array of versions on each hook has been deprecated.
	 *
	 * @var array
	 */
	protected $deprecated_version = array(
		'vsp_framework_loaded' => '0.9',
		'vsp_log_clear'        => '0.9',
		'vsp_log_remove'       => '0.9',
		'vsp_framework_init'   => '0.9',
	);

	/**
	 * Fire off a legacy hook with it's args.
	 *
	 * @param string $old_hook Old hook name.
	 * @param array  $new_callback_args New callback args.
	 *
	 * @return mixed
	 */
	protected function trigger_hook( $old_hook, $new_callback_args ) {
		do_action_ref_array( $old_hook, $new_callback_args );
	}
}
