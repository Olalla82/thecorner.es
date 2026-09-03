<div class="misc-pub-section misc-pub-section-last ttap-container" style="border: 0"><span id="timestamp">
    
    <p class="post-attributes-label-wrapper"><label class="post-attributes-label" for="atp_custom_pixel"><?php echo  esc_html__( 'Add Event-Specific Tiktok Pixel', 'add-tiktok-advertising-pixel' ); ?></label></p>

    <label class="ttap-toggle"><input id="atp_custom_pixel" type="checkbox" name="ttap__event" value="ttap__event_yes" <?php if ( isset( $ttap__event) && !empty( $ttap__event ) ) echo 'checked="checked"'; ?> />
    <span class='ttap-toggle-slider ttap-toggle-round'></span></label>

    <p class="post-attributes-label-wrapper"><label class="post-attributes-label" for="atp_custom_pixel_code"><?php echo  esc_html__( 'Custom Code', 'add-tiktok-advertising-pixel' ) ;?></label></p>

    <textarea id="ttap__eventarea" name="ttap__eventarea" class="ttap-textarea"><?php if ( isset($ttap__eventarea) && !empty( $ttap__eventarea ) ) { echo $ttap__eventarea; } ?></textarea>

    <p><?php echo  esc_html__( 'Note: Make sure to check Add Event-Specific Tiktok Pixel and then copy-paste code in textarea. If you plan to use a Tiktok Advertising Pixel event on this page. Please read more details about Tiktok Advertising Pixel in FAQ', 'add-tiktok-advertising-pixel' ); ?></p>

</div>