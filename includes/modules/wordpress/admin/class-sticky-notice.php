<?php

namespace VSP\Modules\WordPress\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Class WordPressSticky
 *
 * @author    Panagiotis Vagenas <pan.vagenas@gmail.com>
 * @package   vsp-framework/core/modules/admin-notices
 * @copyright 2018 Varun Sridharan
 * @license   GPLV3 Or Greater
 */
class Sticky_Notice implements \VSP\Core\Interfaces\WP_Admin_Notice {

	/**
	 * @param \VSP\Modules\WordPress\WP_Notice $notice
	 *
	 * @return string
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since  TODO ${VERSION}
	 */
	public function formatOutput( \VSP\Modules\WordPress\WP_Notice $notice ) {
		$ui_id = uniqid( preg_replace( '/[^a-z0-9A-Z]/', '', $notice->getId() ) );
		$out   = '<div style="position: relative;" class="' . $notice->getType() . '">';

		$out .= '<h4 style="margin-top: 4px; margin-bottom: 0;">' . $notice->getTitle() . '</h4>';
		$out .= '<p>';
		$out .= $notice->getContent();
		$out .= '</p>';
		$out .= '<hr/><p >';
		$out .= '<a id="' . $ui_id . '" href="' . $this->get_url( $notice ) . '" class="button button-secondary text-right">' . __( 'Hide Notice', 'vsp-framework' ) . '</a>';
		$out .= '</p>';
		$out .= '</div>';
		$out .= $this->dismissibleScript( $notice, $ui_id );
		return $out;
	}

	protected function get_url( \VSP\Modules\WordPress\WP_Notice $notice ) {
		return vsp_ajax_url( array(
			'action'                                                        => \VSP\Modules\WordPress\Admin\Notices::KILL_STICKY_NTC_AJAX_ACTION,
			\VSP\Modules\WordPress\Admin\Notices::KILL_STICKY_NTC_AJAX_NTC_ID_VAR => $notice->getId(),
			\VSP\Modules\WordPress\Admin\Notices::KILL_STICKY_NTC_AJAX_NONCE_VAR  => wp_create_nonce( \VSP\Modules\WordPress\Admin\Notices::KILL_STICKY_NTC_AJAX_ACTION ),
		) );
	}

	/**
	 * @param \VSP\Modules\WordPress\WP_Notice $notice
	 * @param string                           $ui_id
	 *
	 * @return string
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since  TODO ${VERSION}
	 */
	protected function dismissibleScript( \VSP\Modules\WordPress\WP_Notice $notice, $ui_id ) {
		return '<script type="text/javascript">
		jQuery(document).on("ready",function(){
			jQuery("#' . $ui_id . '").click(function(e){
				e.preventDefault();				
				var $notice = jQuery("#' . $ui_id . '").parent().parent();
				jQuery.post(jQuery(this).attr("href"),"",function(){$notice.slideUp();});
			})
		})
		</script>';

	}
}