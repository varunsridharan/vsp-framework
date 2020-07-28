<?php
/**
 * @var \VSP\Modules\Addons $this
 */
$req_title    = __( 'Required Plugin', 'vsp-framework' );
$last_updated = __( 'Last Updated', 'vsp-framework' );
$req_desc     = __( 'Above Mentioned Plugin name with version are Tested Upto', 'vsp-framework' );
$ajax_args    = array(
	'method' => 'post',
	'data'   => array(
		'hook_slug'    => $this->plugin()->slug( 'hook' ),
		'action'       => 'vsp_addon_action',
		'addon_action' => 'activate',
	),
);
?>
<div id="vsp_addons_listing_container" class="wponion-row row">
	<div class="wp-filter col-xs-12">
		<ul class="filter-links addon-category">
			<?php
			foreach ( $this->addon_cats as $slug => $title ) {
				$title = ( true === $this->option( 'show_category_count' ) ) ? $title . ' (' . $this->addon_counts[ $slug ] . ') ' : $title;
				echo '<li class="addon-category ' . $slug . '" data-category="' . $slug . '"><a href="javascript:void(0)">' . $title . '</a></li>';
			}
			?>
		</ul>
	</div>

	<div class="wp-list-table widefat plugin-install col-xs-12">
		<div class="the-list addon_listing">
			<?php
			foreach ( $this->addons as $addon ) {
				$activate   = '<button type="button" class="button button-primary activate actions">' . __( 'Activate', 'vsp-framework' ) . '</button>';
				$deactivate = '<button type="button" class="button button-secondary deactivate actions">' . __( 'De-Activate', 'vsp-framework' ) . '</button>';
				$uid        = $addon['uid'];
				$title      = $addon['name'];
				$author     = $addon['author'];
				$rplugins   = '';
				$is_active  = ( $this->is_active( $addon['uid'] ) ) ? 'active' : 'inactive';
				$desc       = $addon['description']; //@todo add WPKess plugin
				$screens    = '';
				if ( is_array( $addon['required_plugins'] ) && ! empty( $addon['required_plugins'] ) ) {
					$rplugins = '<h3>' . $req_title . '</h3><ul>';
					foreach ( $addon['required_plugins'] as $rp ) {
						$rplugins .= '<li class="required-' . $rp['status'] . ' ' . $rp['status'] . '">
						<a href="' . $rp['url'] . '" title="' . $rp['name'] . '">' . $rp['name'] . '</a> <span class="version">[' . $rp['version'] . ']</span>
						</li>';
					}
					$rplugins .= '</ul>';
				}

				if ( is_array( $addon['screenshots'] ) && ! empty( $addon['screenshots'] ) ) {
					foreach ( $addon['screenshots'] as $ss ) {
						$screens .= "<a data-caption='{$ss['content']}' title='{$ss['content']}' href='{$ss['src']}' data-fancybox='addon-{$addon['uid']}-gallery'>{$ss['content']}</a>";
					}
				}

				$category                          = implode( ' ', $addon['category'] );
				$ajax_args['data']['addon']        = $addon['uid'];
				$ajax_args['data']['addon_action'] = 'activate';
				$ajax_args['success']              = 'function(){var $elm = jQuery("div#addon-' . $uid . ' .addon-actions");$elm.find(".activate").hide();$elm.find(".deactivate").show();}';
				$active                            = wponion_inline_ajax( $ajax_args, $activate );
				$ajax_args['data']['addon_action'] = 'deactivate';
				$ajax_args['success']              = 'function(){var $elm = jQuery("div#addon-' . $uid . ' .addon-actions");$elm.find(".deactivate").hide();$elm.find(".activate").show();}';
				$deactivate                        = wponion_inline_ajax( $ajax_args, $deactivate );

				if ( ! empty( $addon['last_updated'] ) ) {
					$addon['last_updated'] = <<<HTML
<div class="column-updated last-updated"><strong>{$last_updated}</strong> : {$addon['last_updated']}</div>
HTML;

				}

				echo <<<HTML
<div id="addon-{$addon['uid']}" class="plugin-card addon {$category} {$is_active}">
	<div class="plugin-card-top">
		<div class="name column-name">
			<h3><a href="{$addon['url']}" target="_blank" title="{$addon['name']}"> $title <span class="version">[{$addon['version']}]</span></a>
			<a href="{$addon['icon']}" data-fancybox="addon-{$addon['uid']}-gallery" target="_blank" title="{$addon['name']}"><img  class="plugin-icon addon-icon" src="{$addon['icon']}" alt="{$addon['name']}"></a></h3>
		</div>
		<div class="desc column-description"><p>{$desc}</p><p class="authors"> <cite> By <a href="{$addon['author_url']}" title="">{$addon['author']}</a> </cite></p></div>
	</div>
	<div class="plugin-card-top required_plugins">$rplugins</div>
	<div class="hidden" style="visibility: hidden;">$screens</div>
	<div class="plugin-card-bottom">
		{$addon['last_updated']}
		<div class="column-downloaded addon-actions"> {$active} {$deactivate}</div>
	</div>
</div>
HTML;
			}
			?>
		</div>
	</div>
</div>
