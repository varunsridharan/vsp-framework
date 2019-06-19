<?php

namespace VSP\Core\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}


/**
 * Trait VSP_Framework_JS_Trait
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @since 1.0
 */
trait JS {

	/**
	 * Generates Script Tag
	 *
	 * @param string $object_name .
	 * @param array  $l10n .
	 * @param bool   $with_script_tag .
	 *
	 * @return string
	 */
	public static function php_to_js( $object_name, $l10n, $with_script_tag = true ) {
		return wponion_js_vars( $object_name, $l10n, $with_script_tag );
	}
}
