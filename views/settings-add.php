<div class="item">
    
    <div class="vsp-single-add">
        
        <div class="vsp-single-add-header">
            <?php $isrc = isset($r['logo']) ?  $r['logo'] : vsp_img('noimage.png'); ?>
            <img  src="<?php echo $isrc; ?>"/>
            <h3><?php echo $r['name']; ?></h3>
            <div class="vsp-add-meta">
                <span title="<?php _e("Downloads / Installs"); ?>"><span class="dashicons dashicons-chart-bar"></span> <?php echo $r['downloaded']; ?></span>
                <span title="<?php _e("Price"); ?>"><span class="dashicons dashicons-money"></span> <?php  if(isset($r['cost'])) {echo intval($r['cost']).' $ '; } else {echo __("Free"); } ?></span>

            </div>

        </div>
        <div class="vsp-single-add-content"> <?php echo $r['desc']; ?></div>
        <div class="vsp-single-add-footer">
            <a href="<?php echo $r['link']; ?>" class="button <?php if($r['type'] == 'paid'){echo 'button-secondary';} else {echo 'button-primary';} ?>" target="_blank">
            <?php if($r['type'] == 'paid'){_e("Purchase Plugin");}else {_e("View Plugin Page");} ?>
            </a>
            
        </div>
    </div>
    
</div>