<?php
/**
 * VSP Framework Trait
 *
 * Created by PhpStorm.
 * User: varun
 * Date : 13-10-2018
 * Time : 01:42 PM
 *
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @since     1.0
 * @package   vsp-framework/core/trait
 * @copyright GPL V3 Or greater
 */

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
		$l10n = self::js_args_encode( $l10n );

		$script = 'var ' . $object_name . ' = ' . wp_json_encode( $l10n ) . ';';
		if ( ! empty( $after ) ) {
			$script .= "\n$after;";
		}

		if ( $with_script_tag ) {
			$script = '<script type="text/javascript">' . $script . '</script>';
		}
		return $script;
	}

	/**
	 * Encodes PHP Array in JSString.
	 *
	 * @param $l10n
	 *
	 * @return array|string
	 * @static
	 */
	public static function js_args_encode( $l10n ) {
		if ( is_array( $l10n ) ) {
			foreach ( (array) $l10n as $key => $value ) {
				if ( ! is_scalar( $value ) ) {
					continue;
				}

				$l10n[ $key ] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' );
			}
		} else {
			$l10n = html_entity_decode( (string) $l10n, ENT_QUOTES, 'UTF-8' );
		}
		return $l10n;
	}
}
