<h2 class="ttap-logo">Tiktok Advertising Pixel Settings</h2>

<div class="nav-tab-wrapper">

    <a href="<?php echo esc_url( 'admin.php?page='.TTAP_NAME.'&tab=settings' ); ?>" class="ttap-tab nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>">Settings</a>

    <?php if ( $addon ) { ?>
        <a href="<?php echo esc_url( 'admin.php?page='.TTAP_NAME.'&tab=booster' ); ?>" class="ttap-tab nav-tab <?php echo $active_tab == 'booster' ? 'nav-tab-active' : ''; ?>">Tiktok Booster</a>
    <?php } ?>
    
    <a href="<?php echo esc_url( 'admin.php?page='.TTAP_NAME.'&tab=faq' ); ?>" class="ttap-tab nav-tab <?php echo $active_tab == 'faq' ? 'nav-tab-active' : ''; ?>">FAQ</a>

    <a href="<?php echo esc_url( 'admin.php?page='.TTAP_NAME.'&tab=growth-tools' ); ?>" class="ttap-tab nav-tab <?php echo $active_tab == 'growth-tools' ? 'nav-tab-active' : ''; ?>">150+ Growth Hacking Tools</a>

</div>