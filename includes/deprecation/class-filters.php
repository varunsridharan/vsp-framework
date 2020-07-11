<?php

namespace VSP\Deprecation;

use WPOnion\Bridge\Deprecated_Hooks;

defined( 'ABSPATH' ) || exit;

/**
 * Handles deprecation notices and triggering of legacy filter hooks
 */
class Filters extends Deprecated_Hooks {
	/**
	 * Array of deprecated hooks we need to handle.
	 * Format of 'new' => 'old'.
	 *
	 * @var array
	 */
	protected $deprecated_hooks = array(
		'vsp/wc/payment_gateway/label'           => 'vsp_wc_payment_gateway_label',
		'vsp/wc/shipping_methods/label'          => 'vsp_wc_shipping_methods_label',
		'vsp/wc/shipping_methods/instance/label' => 'vsp_wc_shipping_methods_by_instance_label',
		'vsp/placeholder_img'                    => 'vsp_placeholder_img',
		'vsp/date/format'                        => 'vsp_date_format',
		'vsp/time/format'                        => 'vsp_time_format',
		'vsp/log/class'                          => 'vsp_logging_class',
		'vsp/log/format/entry'                   => 'vsp_format_log_entry',
		'vsp/log/register/handlers'              => 'vsp_register_log_handlers',
		'vsp/log/message'                        => 'vsp_logger_log_message',
		'vsp/log/add/message'                    => 'vsp_logger_add_message',
	);

	/**
	 * Array of versions on each hook has been deprecated.
	 *
	 * @var array
	 */
	protected $deprecated_version = array(
		'vsp_wc_payment_gateway_label'              => '0.9',
		'vsp_wc_shipping_methods_label'             => '0.9',
		'vsp_wc_shipping_methods_by_instance_label' => '0.9',
		'vsp_placeholder_img'                       => '0.9',
		'vsp_logging_class'                         => '0.9',
		'vsp_date_format'                           => '0.9',
		'vsp_time_format'                           => '0.9',
		'vsp_format_log_entry'                      => '0.9',
		'vsp_register_log_handlers'                 => '0.9',
		'vsp_logger_log_message'                    => '0.9',
		'vsp_logger_add_message'                    => '0.9',
	);

	/**
	 * Which Type of Hook is this
	 * action / filter
	 *
	 * @var string
	 */
	protected $type = 'filter';

	/**
	 * Fire off a legacy hook with it's args.
	 *
	 * @param string $old_hook Old hook name.
	 * @param array  $new_callback_args New callback args.
	 *
	 * @return mixed
	 */
	protected function trigger_hook( $old_hook, $new_callback_args ) {
		return apply_filters_ref_array( $old_hook, $new_callback_args );
	}
}
