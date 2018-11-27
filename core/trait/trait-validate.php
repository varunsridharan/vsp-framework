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

/**
 * Trait VSP_Framework_Validate_Trait
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @since 1.0
 */
trait VSP_Validate_Trait {

	/**
	 * Validates If Given Value Is A IP Address.
	 *
	 * @param $ip
	 *
	 * @return bool
	 * @static
	 */
	public static function is_ip( $ip ) {
		return filter_var( $ip, FILTER_VALIDATE_IP ) ? true : false;
	}

	/**
	 * Validates if Given Value Is A Valid URL.
	 *
	 * @param $url
	 *
	 * @return bool
	 * @static
	 */
	public static function is_url( $url ) {
		return filter_var( $url, FILTER_VALIDATE_URL ) ? true : false;
	}

	/**
	 * Validates if Given Value Is A Email ID.
	 *
	 * @param $email
	 *
	 * @return bool
	 * @static
	 */
	public static function is_email( $email ) {
		return filter_var( $email, FILTER_VALIDATE_EMAIL ) ? true : false;
	}

	/**
	 * Validates if Given Value Is A Regex
	 *
	 * @param $regex
	 *
	 * @return bool
	 * @static
	 */
	public static function is_regex( $regex ) {
		return filter_var( $regex, FILTER_VALIDATE_REGEXP ) ? true : false;
	}

	/**
	 * Validates if Given Value Is A Proper MAC ID.
	 *
	 * @param $mac_id
	 *
	 * @return bool
	 * @static
	 */
	public static function is_mac_id( $mac_id ) {
		return filter_var( $mac_id, FILTER_VALIDATE_MAC ) ? true : false;
	}

	/**
	 * Validates if Given Value Is A Proper Domain Name.
	 *
	 * @param $domain
	 *
	 * @return bool
	 * @static
	 */
	public static function is_domain( $domain ) {
		return filter_var( $domain, FILTER_VALIDATE_DOMAIN ) ? true : false;
	}

	/**
	 * Validates if Given url is a proper bool value.
	 * true => true|'true'|1|on|yes
	 * false => ''|false|0|'false'|null|no|off
	 *
	 * @param $value
	 *
	 * @return bool
	 */
	public static function is_bool( $value ) {
		return ! in_array( strtolower( $value ), array( '', 'false', '0', 'no', 'off', null ) );
	}
}
