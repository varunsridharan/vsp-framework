<?php

/**
 * Class WordPressSticky
 *
 * @author  Panagiotis Vagenas <pan.vagenas@gmail.com>
 * @since   TODO ${VERSION}
 */
class VSP_WordPress_Sticky_Notice implements VSP_WP_Admin_Notice_Interface {
	/**
	 * @param VSP_WP_Notice $notice
	 *
	 * @return string
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since  TODO ${VERSION}
	 */
	public function formatOutput( VSP_WP_Notice $notice ) {
		$unqId = uniqid( preg_replace( '/[^a-z0-9A-Z]/', '', $notice->getId() ) );
		$out   = "
		<div style=\"position: relative;\" class=\"{$notice->getType()}\">
			<h4 style=\"margin-top: 4px; margin-bottom: 0;\">{$notice->getTitle()}</h4>
			<p>
				{$notice->getContent()}
				<a id=\"{$unqId}\" href=\"#\" style=\"font-size: 150%; position: absolute; right: 5px; top: -5px; text-decoration: none;\">×</a>
			</p>
		</div>
		";

		$out .= $this->dismissibleScript( $notice, $unqId );

		return $out;
	}

	/**
	 * @param VSP_WP_Notice $notice
	 * @param string        $unqId
	 *
	 * @return string
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since  TODO ${VERSION}
	 */
	protected function dismissibleScript( VSP_WP_Notice $notice, $unqId ) {
		return '
		<script type="text/javascript">
			jQuery(document).ready(function ($) {
				var data = {
					"action": "' . VSP_WP_Admin_Notices::KILL_STICKY_NTC_AJAX_ACTION . '",
					"' . VSP_WP_Admin_Notices::KILL_STICKY_NTC_AJAX_NTC_ID_VAR . '": "' . $notice->getId() . '",
					"' . VSP_WP_Admin_Notices::KILL_STICKY_NTC_AJAX_NONCE_VAR . '": "' . wp_create_nonce( VSP_WP_Admin_Notices::KILL_STICKY_NTC_AJAX_ACTION ) . '"
				};

				var $notice = $("#' . $unqId . '").parent().parent();
				$("#' . $unqId . '").click(function(){
					jQuery.post(ajaxurl, data,
						function(){
							$notice.slideUp();
						}
					);
				});
			});
		</script>
		';
	}
}