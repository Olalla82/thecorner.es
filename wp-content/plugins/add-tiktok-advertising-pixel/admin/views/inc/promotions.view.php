<div class="ttap-segment">

    <div class="row" style="margin-bottom: 15px;">

        <div class="col-xs-3">
            <label class="ttap-label" for="boost-robot">
                <strong>
                    <?php echo __('Boost your ranking on Search engines', 'add-tiktok-advertising-pixel'); ?>
                </strong>
            </label>
        </div>

        <div class="col-xs-9 promotion-container">

            <label class="ttap-toggle"><input id="boost-robot" type="checkbox" name="boost-robot" value="allow" <?php if ( $options::check('boost-robot') ) { echo  'checked' ;  } ?> /><span class='ttap-toggle-slider ttap-toggle-round'></span></label>

            &nbsp; <span class="ttap-comment"><?php echo  __( 'Optimize site\'s crawlability with an optimized robots.txt', 'add-tiktok-advertising-pixel' ) ;?></span>

            <div class="promotion"
                <?php if ($options::check('boost-robot')) { echo 'style="display: inline;"'; } else { echo 'style="display: none;"';} ?>>

                <div class="ttap-alert ttap-success" style="margin-top: 10px;">
                    <?php echo sprintf( wp_kses( __( 'Click <a href="%s" target="_blank">HERE</a> to Install <a href="%2s" target="_blank">Better Robots.txt plugin</a> to boost your robots.txt', 'add-tiktok-advertising-pixel' ), array( 
                        'a' => array( 
                            'href' => array(), 
                            'target' => array(), 
                        ), 
                        'a' => array( 
                            'href' => array(), 
                            'target' => array(), 
                        ),
                    ) ), esc_url( "https://wordpress.org/plugins/better-robots-txt/" ), esc_url( "https://wordpress.org/plugins/better-robots-txt/" ) ); ?>
                </div>
            </div>
        </div>

    </div>

    <div class="row" style="margin-bottom: 15px;">

        <div class="col-xs-3">
            <label class="ttap-label" for="boost-robot">
                <strong>
                    <?php echo __('Boost your Alt texts', 'add-tiktok-advertising-pixel'); ?>
                </strong>
            </label>
        </div>

        <div class="col-xs-9 promotion-container">

            <label class="ttap-toggle"><input id="boost-alt" type="checkbox" name="boost-alt" value="allow" <?php if ( $options::check('boost-alt') ) { echo  'checked' ;  }?> />
            <span class='ttap-toggle-slider ttap-toggle-round'></span></label>

            &nbsp; <span class="ttap-comment"><?php echo  __( 'Boost your ranking with optimized Alt tags', 'add-tiktok-advertising-pixel' ) ;?></span>

            <div class="promotion"
                <?php if ($options::check('boost-alt')) { echo 'style="display: inline;"'; } else { echo 'style="display: none;"';} ?>>
                <div class="ttap-alert ttap-success" style="margin-top: 10px;">
                    <?php echo sprintf( wp_kses( __( 'Click <a href="%s" target="_blank">HERE</a> to Install <a href="%2s" target="_blank">BIALTY Wordpress plugin</a> & auto-optimize all your alt texts for FREE', 'add-tiktok-advertising-pixel' ), array( 
                        'a' => array( 
                            'href' => array(), 
                            'target' => array(), 
                        ), 
                        'a' => array( 
                            'href' => array(), 
                            'target' => array(), 
                        ),
                    ) ), esc_url( "https://wordpress.org/plugins/bulk-image-alt-text-with-yoast/" ), esc_url( "https://wordpress.org/plugins/bulk-image-alt-text-with-yoast/" ) ); ?>
                </div>
            </div>
        </div>

    </div>

    <div class="row" style="margin-bottom: 15px;">

        <div class="col-xs-3">
            <label class="ttap-label" for="ttap-bigta">
                <strong>
                    <?php echo __('Boost your image title attribute', 'add-tiktok-advertising-pixel'); ?>
                </strong>
            </label>
        </div>

        <div class="col-xs-9 promotion-container">

            <label class="ttap-toggle"><input id="ttap-bigta" type="checkbox" name="ttap-bigta" value="allow" <?php if ( $options::check('ttap-bigta') ) { echo  'checked' ;  } ?> /><span class='ttap-toggle-slider ttap-toggle-round'></span></label>

            &nbsp; <span class="ttap-comment"><?php echo  __( 'Optimize all your image title attributes for UX & search engines performance', 'add-tiktok-advertising-pixel' ) ;?></span>

            <div class="promotion"
                <?php if ($options::check('ttap-bigta')) { echo 'style="display: inline;"'; } else { echo 'style="display: none;"';} ?>>

                <div class="ttap-alert ttap-success" style="margin-top: 10px;"><?php echo sprintf( wp_kses( __( 'Click <a href="%s" target="_blank">HERE</a> to Install <a href="%2s" target="_blank">BIGTA</a> Wordpress plugin & auto-optimize all your image title attributes for FREE', 'add-tiktok-advertising-pixel' ), array( 
                        'a' => array( 
                            'href' => array(), 
                            'target' => array(), 
                        ), 
                        'a' => array( 
                            'href' => array(), 
                            'target' => array(), 
                        ),
                    ) ), esc_url( "https://wordpress.org/plugins/bulk-image-title-attribute/" ), esc_url( "https://wordpress.org/plugins/bulk-image-title-attribute/" ) ); ?>
                </div>
            </div>
        </div>

    </div>

    <div class="row" style="margin-bottom: 15px;">

        <div class="col-xs-3">
            <label class="ttap-label" for="ttap-vidseo">
                <strong>
                    <?php echo __('Looking for FREE unlimited content for SEO?', 'add-tiktok-advertising-pixel'); ?>
                </strong>
            </label>
        </div>

        <div class="col-xs-9 promotion-container">

            <label class="ttap-toggle"><input id="ttap-vidseo" type="checkbox" name="ttap-vidseo" value="allow" <?php if ( $options::check('ttap-vidseo') ) { echo  'checked' ;  } ?> /><span class='ttap-toggle-slider ttap-toggle-round'></span></label>

            &nbsp; <span class="ttap-comment"><?php echo  __( 'Get access to billions of non-indexed content with Video transcripts (Youtube)', 'add-tiktok-advertising-pixel' ) ;?></span>

            <div class="promotion"
                <?php if ($options::check('ttap-vidseo')) { echo 'style="display: inline;"'; } else { echo 'style="display: none;"';} ?>>

                <div class="ttap-alert ttap-success" style="margin-top: 10px;"><?php echo sprintf( wp_kses( __( 'Click <a href="%s" target="_blank">HERE</a> to learn more about <a href="%2s" target="_blank">VidSEO</a> Wordpress plugin & how to skyrocket your SEO', 'add-tiktok-advertising-pixel' ), array( 
                        'a' => array( 
                            'href' => array(), 
                            'target' => array(), 
                        ), 
                        'a' => array( 
                            'href' => array(), 
                            'target' => array(), 
                        ),
                    ) ), esc_url( "https://wordpress.org/plugins/vidseo/" ), esc_url( "https://wordpress.org/plugins/vidseo/" ) ); ?>
                </div>  
            </div>
        </div>

    </div>

</div>