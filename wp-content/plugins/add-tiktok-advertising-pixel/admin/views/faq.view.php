<div class="wrap container-fluid ttap-container">

    <?php include 'inc/top.view.php'; ?>
    
    <div class="row">

        <div id="ttap-app" class="col-xs-12 col-main">

            <div class="ttap-segment">

                <h2><?php echo __('How to check if Tiktok pixel is properly installed', 'add-tiktok-advertising-pixel'); ?></h2>

                <p><?php echo sprintf( wp_kses( __( 'Use <a href="%s">TikTok pixel helper</a>: You can use TikTok Pixel Helper to check if your landing page is installed correctly and if the conversion event is triggered.', 'add-tiktok-advertising-pixel' ), array(  'a' => array( 'href' => array() ) ) ), esc_url( 'https://chrome.google.com/webstore/detail/pixel-helper/aelgobmabdmlfmiblddjfnjodalhidnn' ) ); ?></p>

            </div>

            <div class="ttap-segment">

                <h2><?php echo __('Where to find the Tiktok Pixel ID?', 'add-tiktok-advertising-pixel'); ?></h2>

                <p>
                <?php echo __( 'To find your Tiktok PIxel ID, follow the steps below.', 'add-tiktok-advertising-pixel' ); ?>
            </p>

            <ol>
                <li><?php echo __( 'Navigate to your Tiktok ads Plateform.', 'add-tiktok-advertising-pixel' ); ?></li>
                <li><?php echo __( 'Got to Pixel Creation Portal: Library 》Conversion 》Website Pixel.', 'add-tiktok-advertising-pixel' ); ?></li>
                <li><?php echo __( 'Install.', 'add-tiktok-advertising-pixel' ); ?></li>
                <li><?php echo __( 'Click « Create website pixel ».', 'add-tiktok-advertising-pixel' ); ?></li>
                <li><?php echo __( 'Name your pixel. One pixel corresponds to one website with one pixel name', 'add-tiktok-advertising-pixel' ); ?></li>
                <li><?php echo __( 'Click "Generate code".', 'add-tiktok-advertising-pixel' ); ?></li>
                <li><?php echo sprintf( wp_kses( __( 'You will see, in your code, numbers like <a href="%s">this</a> (in red). This is your Tiktok Pixel ID !.', 'add-tiktok-advertising-pixel' ), array(  'a' => array( 'href' => array() ) ) ), esc_url( plugin_dir_url( __FILE__ ) . '../assets/imgs/code.png' ) ); ?></li>
            </ol>

            </div>

        </div>

    </div>

</div>