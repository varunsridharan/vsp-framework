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


trait VSP_IP_Trait {
	/**
	 * Get user's IP.
	 *
	 * @return string|false → user IP
	 */
	public static function get() {
		$possible_keys = array(
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'HTTP_VIA',
			'HTTP_X_COMING_FROM',
			'HTTP_COMING_FROM',
			'HTTP_X_REAL_IP',
			'REMOTE_ADDR',
		);
		foreach ( $possible_keys as $key ) {
			$ip = self::global_vars( $key );
			if ( $ip ) {
				return self::validate_ip( $ip );
			}
		}
		return false;
	}


}
