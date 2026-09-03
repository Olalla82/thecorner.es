<div class="wrap container-fluid ttap-container">

    <?php include 'inc/top.view.php'; ?>

    <div class="row">

        <div id="ttap-app" class="col-xs-8 col-main">

        <?php do_action( 'tiktok_booster' ); ?>

        </div>

        <div class="col-xs-4 ttap-side">

            <?php Pagup\TiktokPixel\Core\Plugin::view('inc/side'); ?>

        </div>

    </div>

</div>