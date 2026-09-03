<?php

namespace OXI_FLIP_BOX_PLUGINS\Includes\Admin\Pages\Tabs;

class Help {

	public function render() {
		?>
		<div id="help" class="wpkin-help getting-started-content active">
			<div class="content-heading heading-questions">
				<h2>
					<?php _e( 'Frequently Asked', 'oxi-flip-box-plugin' ); ?>
					<mark><?php _e( 'Questions', 'oxi-flip-box-plugin' ); ?></mark>
				</h2>
			</div>

			<section class="section-faq">

				<!-- FAQ Item 1 -->
				<div class="faq-item">
					<div class="faq-header" style="cursor:pointer">
						<i class="dashicons dashicons-arrow-down-alt2"></i>
						<h3>
							<?php _e( 'I have a pre-sale question. How can I get support?', 'oxi-flip-box-plugin' ); ?>
						</h3>
					</div>
					<div class="faq-body" style="display:none;">
						<p>
							<?php _e( 'For any pre-sale inquiries, please contact us directly by submitting a form here:', 'oxi-flip-box-plugin' ); ?>
							<a href="https://wpkin.com/contact-us/" target="_blank" rel="noopener noreferrer">
								<?php _e( 'Contact Us', 'oxi-flip-box-plugin' ); ?>
							</a>
						</p>
					</div>
				</div>

				<!-- FAQ Item 2 -->
				<div class="faq-item">
					<div class="faq-header" style="cursor:pointer">
						<i class="dashicons dashicons-arrow-down-alt2"></i>
						<h3>
							<?php _e( 'How do I install the Flipbox plugin?', 'oxi-flip-box-plugin' ); ?>
						</h3>
					</div>
					<div class="faq-body" style="display:none;">
						<p>
							<?php _e( "Go to Plugins → Add New → Upload Plugin, choose the Flipbox .zip file, install and activate. You can also install it directly from the WordPress plugin directory by searching for 'Flipbox – Awesomes Image Overlay'.", 'oxi-flip-box-plugin' ); ?>
							<a href="https://wpkindemos.com/flipbox/docs/installations/how-to-install-the-plugin/" target="_blank" rel="noopener noreferrer">
								<?php _e( 'Read Installation Guide', 'oxi-flip-box-plugin' ); ?>
							</a>
						</p>
					</div>
				</div>

				<!-- FAQ Item 3 -->
				<div class="faq-item">
					<div class="faq-header" style="cursor:pointer">
						<i class="dashicons dashicons-arrow-down-alt2"></i>
						<h3>
							<?php _e( 'I purchased the PRO version, but it still shows the free plan. What should I do?', 'oxi-flip-box-plugin' ); ?>
						</h3>
					</div>
					<div class="faq-body" style="display:none;">
						<p>
							<?php _e( "After purchase, go to the Plugins menu in your WordPress dashboard. Scroll down to the Flipbox - Awesome Flip Boxes Image Overlay plugin and click on 'Activate License.' A modal will appear where you can enter and submit your license key.", 'oxi-flip-box-plugin' ); ?>
						</p>
						<img src="<?php echo OXI_FLIP_BOX_URL . 'image/getting-started/help/license-activate.png'; ?>">
					</div>
				</div>
				<!-- FAQ Item 4 -->
				<div class="faq-item">
					<div class="faq-header" style="cursor:pointer">
						<i class="dashicons dashicons-arrow-down-alt2"></i>
						<h3>
							<?php _e( 'Where can I find the PRO license key?', 'oxi-flip-box-plugin' ); ?>
						</h3>
					</div>
					<div class="faq-body" style="display:none;">
						<p>
							<?php _e( 'After purchase, you should receive a confirmation email containing the license key. If you did not receive it due to email delivery issues, you can access your license key from the', 'oxi-flip-box-plugin' ); ?>
							<a href="https://users.freemius.com/store/4896/" target="_blank" rel="noopener noreferrer">Freemius Customer Portal.</a>
						</p>
					</div>
				</div>

				<!-- FAQ Item 5 -->
				<div class="faq-item">
					<div class="faq-header" style="cursor:pointer">
						<i class="dashicons dashicons-arrow-down-alt2"></i>
						<h3>
							<?php _e( 'How do I display a Flipbox on my website?', 'oxi-flip-box-plugin' ); ?>
						</h3>
					</div>
					<div class="faq-body" style="display:none;">
						<p>
							<?php _e( 'Each Flipbox you create generates a shortcode. Copy this shortcode and paste it inside any post, page, or widget area where you want the Flipbox to appear. Works with Gutenberg, Elementor, WPBakery, Divi, and other builders.', 'oxi-flip-box-plugin' ); ?>
						</p>
						<img src="<?php echo OXI_FLIP_BOX_URL . 'image/getting-started/basic-uses/shortcode.png'; ?>">
					</div>
				</div>

				<!-- FAQ Item 6 -->
				<div class="faq-item">
					<div class="faq-header" style="cursor:pointer">
						<i class="dashicons dashicons-arrow-down-alt2"></i>
						<h3>
							<?php _e( 'Where can I get support if I face an issue?', 'oxi-flip-box-plugin' ); ?>
						</h3>
					</div>
					<div class="faq-body" style="display:none;">
						<p>
							<?php _e( 'For free users, you can ask questions in the WordPress.org support forum. For Pro users, premium email support is available. You can also contact us directly:', 'oxi-flip-box-plugin' ); ?>
							<a href="https://wpkin.com/contact-us/" target="_blank" rel="noopener noreferrer">
								<?php _e( 'Contact Support', 'oxi-flip-box-plugin' ); ?>
							</a>
						</p>
					</div>
				</div>

			</section>

			<div class="content-heading heading-help">
				<h2>
					<?php _e( 'Need', 'oxi-flip-box-plugin' ); ?>
					<mark><?php _e( 'Help?', 'oxi-flip-box-plugin' ); ?></mark>
				</h2>
				<p>
					<?php _e( 'Read our documentation or contact us directly for support.', 'oxi-flip-box-plugin' ); ?>
				</p>
			</div>

			<div class="facebook-cta">
				<div class="cta-content">
					<h2><?php _e( 'Support', 'oxi-flip-box-plugin' ); ?></h2>
					<p>
						<?php _e( 'Join our community and get help from other Flipbox users. Share your ideas, solve problems, and learn tips to get the most out of Flipbox.', 'oxi-flip-box-plugin' ); ?>
					</p>
				</div>
				<div class="cta-btn">
					<a href="https://wordpress.org/support/plugin/image-hover-effects-ultimate-visual-composer/" class="wpkin-btn btn-primary" target="_blank" rel="noopener noreferrer">
						<i class="dashicons dashicons-sos"></i>
						<?php _e( 'Get Support', 'oxi-flip-box-plugin' ); ?>
					</a>
				</div>
			</div>
		</div>

		<?php
	}
}
