<div class="wrap container-fluid ttap-container">

    <?php 
include 'inc/top.view.php';
?>

    <div class="row">

        <div id="ttap-app" class="col-xs-8 col-main">

            <form method="post" class="ttap-form">

                <?php 
wp_nonce_field( 'ttap__settings', 'ttap__nonce' );
?>

                <div class="ttap-segment">

                    <h2><?php 
echo esc_html__( 'About TikTok Pixel', 'add-tiktok-advertising-pixel' );
?></h2>
                    <p><?php 
echo sprintf( wp_kses( __( 'Tiktok advertising pixel plugin allows you to easily create custom events on specific pages with a few clicks. Don\'t forget to check on your pages to find META BOX feature under WordPress post editor. If you have any doubt, please refer to <a href="%s" target="_blank">Tiktok documentation</a>. Enjoy.', 'add-tiktok-advertising-pixel' ), array(
    'a' => array(
        'href'   => array(),
        'target' => array(),
    ),
) ), esc_url( 'https://ads.tiktok.com/help/article?aid=6669727593823993861' ) );
?>
                    </p>

                </div>

                <div class="ttap-segment">

                    <h2 style="margin-bottom: 2em;">
                        <?php 
echo __( 'TikTok Advertising Pixel', 'add-tiktok-advertising-pixel' );
?></h2>

                    <div class="row" style="margin-bottom: 2em;">

                        <div class="col-xs-3">
                            <label class="ttap-label" for="ttap__enable">
                                <strong>
                                    <?php 
echo __( 'Enable Tiktok Pixel', 'add-tiktok-advertising-pixel' );
?>
                                </strong>
                            </label>
                        </div>

                        <div class="col-xs-9">

                            <label class="ttap-toggle"><input id="ttap__enable" type="checkbox" name="ttap__enable" value="allow" <?php 
if ( $options::check( 'ttap__enable' ) ) {
    echo 'checked';
}
?> />
                                <span class='ttap-toggle-slider ttap-toggle-round'></span></label>
                            &nbsp;
                            <span
                                class="ttap-comment"><?php 
echo __( 'This feature will add the Tiktok Advertising Pixel to all pages', 'add-tiktok-advertising-pixel' );
?></span>

                        </div>

                    </div>

                    <div class="row">
                        <div class="col-xs-3">
                            <label class="ttap-label" for="ttap__id">
                                <strong>
                                    <?php 
echo __( 'Tiktok Pixel ID', 'add-tiktok-advertising-pixel' );
?>
                                </strong>
                            </label>
                        </div>

                        <div class="col-xs-9">
                            <input type="text" id="ttap__id" class="ttap-input" name="ttap__id"
                                value="<?php 
if ( $options::check( 'ttap__id' ) ) {
    echo $options::get( 'ttap__id' );
}
?>"
                                placeholder="Enter Your Tiktok Tracking ID" />

                            <p class="ttap-comment">
                                <?php 
echo sprintf( wp_kses( __( 'Please Refer <a href="%s">FAQ</a> to check your pixel.', 'add-tiktok-advertising-pixel' ), array(
    'a' => array(
        'href' => array(),
    ),
) ), esc_url( 'options-general.php?page=ttap&tab=ttap-faq' ) );
?>
                            </p>

                            <?php 
if ( isset( $ttap__options['ttap__enable'] ) && !empty( $ttap__options['ttap__enable'] ) && empty( $ttap__options['ttap__id'] ) ) {
    ?>

                            <div class="ttap-alert ttap-warning"><span
                                    class="closebtn">&times;</span><?php 
    echo __( 'It seems you enabled Tiktok Advertising Pixel above but forgot to enter TAG ID. Please make sure to enter TAG ID otherwise it won\'t work.', 'add-tiktok-advertising-pixel' );
    ?>
                            </div>

                            <?php 
}
?>

                        </div>

                    </div>

                </div>

                <div class="ttap-segment">

                    <h2 style="margin-bottom: 2em;">
                        <?php 
echo __( 'Tiktok Advertising Pixel on WooCommerce &lt;/Pro&gt;', 'add-tiktok-advertising-pixel' );
?>
                    </h2>

                    <div class="row">

                        <div class="col-xs-3">
                            <label class="ttap-label" for="ttap__woo">
                                <strong>
                                    <?php 
echo __( 'Enable Tiktok Pixel on your Online store', 'add-tiktok-advertising-pixel' );
?>
                                </strong>
                            </label>
                        </div>

                        <div class="col-xs-9">

                            <?php 
?>

                            <label class="ttap-toggle"><input type="checkbox" disabled />
                            <span class='ttap-toggle-slider ttap-toggle-round'></span></label>

                            <?php 
?>

                            <?php 
?>

                            <div class="ttap-alert ttap-info">
                                <span class="closebtn">&times;</span>
                                <?php 
echo $get_pro . " " . __( 'on Woocommerce product pages.', 'add-tiktok-advertising-pixel' );
?>
                            </div>

                            <?php 
?>
                        </div>

                    </div>

                </div>

                <div class="ttap-alert ttap-note">
                    <?php 
echo sprintf( wp_kses( __( 'Please note that, if you have activated this feature in order to track Add-to-cart, Checkout, ... events, you also need to create these events within your Tiktok Dashboard / Events Manager (otherwise it won\'t work). &nbsp;<a href="%s" target="_blank">More info</a>', 'pctag' ), array(
    'a' => array(
        'href'   => array(),
        'target' => array(),
    ),
) ), esc_url( 'https://ads.tiktok.com/help/article?aid=10025' ) );
?>
                </div>

                <div class="ttap-segment">

                    <h2 style="margin-bottom: 2em;">
                        <?php 
echo __( 'Developer Mode &lt;/Pro&gt;', 'add-tiktok-advertising-pixel' );
?></h2>

                    <div class="row">

                        <div class="col-xs-3">
                            <label class="ttap-label" for="ttap__enable">
                                <strong>
                                    <?php 
echo __( 'Custom Code', 'add-tiktok-advertising-pixel' );
?>
                                </strong>
                            </label>
                        </div>

                        <div class="col-xs-9">

                            <?php 
?>

                            <textarea class="ttap-textarea ttap-disabled" disabled></textarea>

                            <br />

                            <?php 
?>

                            <div class="ttap-alert ttap-info">
                                <span class="closebtn">&times;</span>
                                <?php 
echo $get_pro . " " . __( 'Developer mode.', 'add-tiktok-advertising-pixel' );
?>
                            </div>

                            <?php 
?>

                            <?php 
?>

                        </div>

                    </div>

                </div>

                <?php 
// free only
?>

                <div class="ttap-alert ttap-info"><span
                        class="closebtn">&times;</span><?php 
echo sprintf( wp_kses( __( '<a href="%s">Get Pro version</a> to enable', 'add-tiktok-advertising-pixel' ), array(
    'a' => array(
        'href'   => array(),
        'target' => array(),
    ),
) ), esc_url( "options-general.php?page=ttap-pricing" ) ) . " " . sprintf( wp_kses( __( '"specific event pixels" on your Posts, Pages are managed with a &nbsp;<a href="%s" target="_blank">META BOX feature</a>', 'pctag' ), array(
    'a' => array(
        'href'   => array(),
        'target' => array(),
    ),
) ), esc_url( plugin_dir_url( __FILE__ ) . '../assets/metabox.png' ) );
?>
                </div>

                <?php 
?>

                <?php 
include 'inc/promotions.view.php';
?>

                <div class="ttap-segment">

                    <div class="row">

                        <div class="col-xs-3">
                            <label class="ttap-label" for="remove_settings">
                                <strong>
                                    <?php 
echo __( 'Remove Settings', 'add-tiktok-advertising-pixel' );
?>
                                </strong>
                            </label>
                        </div>

                        <div class="col-xs-2">
                            <label class="ttap-toggle"><input id="remove_settings" type="checkbox"
                                    name="remove_settings" value="allow" <?php 
if ( $options::check( 'remove_settings' ) ) {
    echo 'checked';
}
?> />
                                <span class='ttap-toggle-slider ttap-toggle-round'></span></label>
                        </div>

                        <div class="col-xs-7 field">
                            <input type="submit" name="update" class="ttap-submit" value="<?php 
echo esc_html__( 'Save Changes', 'add-tiktok-advertising-pixel' );
?>" />
                        </div>

                    </div>

                </div>

                <div class="ttap-alert ttap-note">
                    <?php 
echo __( "<strong>Note:</strong> once the codes are added, make sure to clear your cache.", 'add-tiktok-advertising-pixel' );
?>
                </div>

            </form>

            <div class="ttap-segment">

                    <h2 style="margin-bottom: 2em;">
                        <?php 
echo __( 'Tiktok Video Booster', 'add-tiktok-advertising-pixel' );
?></h2>

                    <div class="row">

                        <div class="col-xs-8">
                            <p class="ttap-label" style="display: inline; margin: 0; background-color: #fff996;">
                                <strong>
                                    <?php 
echo __( 'Looking for ways to boost the number of views of your Tiktok videos (and get more traffic on your Tiktok account)? You MUST try this Add-on.', 'add-tiktok-advertising-pixel' );
?>
                                </strong>
                            </p>
                        </div>

                        <div class="col-xs-4">
                        <?php 
if ( $addon ) {
    ?>
                            <a href="<?php 
    echo esc_url( 'admin.php?page=' . TTAP_NAME . '&tab=booster' );
    ?>" class="ttap-btn" style="font-size: 16px;">Tiktok Booster Add-on</a>
                        <?php 
} else {
    ?>
                            <a href="<?php 
    echo esc_url( 'admin.php?page=add-tiktok-advertising-pixel-addons' );
    ?>" class="ttap-btn" style="font-size: 16px;">Tiktok Booster Add-on</a>
                        <?php 
}
?>
                            

                        </div>

                    </div>

                </div>

            <div class="ttap-segment" style="padding: 0; line-height: 0;">
                <a href="https://wordpress.org/plugins/floating-tiktok-button/" target="_blank"><img src="<?php 
echo esc_url( plugin_dir_url( __FILE__ ) . '../assets/floating-btn.jpg' );
?>" style="width: 100%;"></a>
            </div>

            <div class="ttap-tiktokicon">
                <a href="https://wordpress.org/plugins/floating-tiktok-button/" target="_blank"><img src="<?php 
echo esc_url( plugin_dir_url( __FILE__ ) . '../assets/floating-btn.gif' );
?>"></a>
            </div>

        </div>

        <div class="col-xs-4 ttap-side">

            <?php 
Pagup\TiktokPixel\Core\Plugin::view( 'inc/side' );
?>

        </div>