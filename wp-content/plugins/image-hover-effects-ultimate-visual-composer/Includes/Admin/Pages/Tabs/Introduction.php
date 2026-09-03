<?php

namespace OXI_FLIP_BOX_PLUGINS\Includes\Admin\Pages\Tabs;

class Introduction {

	function render() {
		?>
		<div id="introduction" class="getting-started-content content-introduction setup-complete active" >
			<div class="content-heading heading-overview">
				<h2>
					<?php echo esc_html__( 'Welcome to', 'oxi-flip-box-plugin' ); ?>
					<mark><?php echo esc_html__( 'Flipbox – Awesome Image Overlay', 'oxi-flip-box-plugin' ); ?></mark>
				</h2>
				<p>
					<?php echo esc_html__( 'Create stunning image hover effects, overlays, and flip animations in WordPress with ease.', 'oxi-flip-box-plugin' ); ?>
				</p>
			</div>

			<section class="section-introduction section-full">
				<div class="col-description">
					<p><?php echo esc_html__( 'Flipbox – Awesome Image Overlay allows you to add interactive image effects that enhance user engagement and bring your website to life. Whether it’s product showcases, portfolios, or call-to-action banners, Flipbox makes your visuals unforgettable.', 'oxi-flip-box-plugin' ); ?></p>
					<p><?php echo esc_html__( 'With 50+ hover effects and flexible customization options, you can design stunning, responsive overlays in just a few clicks — fully compatible with today’s WordPress editors and future-ready for page builders.', 'oxi-flip-box-plugin' ); ?></p>
				</div>

				<div class="col-image">
					<iframe src="https://www.youtube.com/embed/OaLL0DNUHWA" title="YouTube demo" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen=""></iframe>
				</div>
			</section>

			<div class="content-heading never-miss-feature">
				<h2>
					<?php echo esc_html__( 'Powerful', 'oxi-flip-box-plugin' ); ?>
					<mark><?php echo esc_html__( 'Flipbox Features', 'oxi-flip-box-plugin' ); ?></mark>
				</h2>
				<p><?php echo esc_html__( 'Design eye-catching hover effects and overlays with complete flexibility', 'oxi-flip-box-plugin' ); ?></p>
			</div>

			<section class="section-full">
				<div class="col-description">
					<h2><?php echo esc_html__( '50+ Hover & Flip Effects', 'oxi-flip-box-plugin' ); ?></h2>
					<p><?php echo esc_html__( 'Choose from a wide range of pre-built hover and flip animations to instantly enhance your website visuals. From subtle fades to bold 3D flips, Flipbox has the perfect effect for every project. All animations are smooth, lightweight, and optimized for performance, so your site stays fast and responsive. You can easily mix and match effects to create unique interactions that grab attention and keep visitors engaged.', 'oxi-flip-box-plugin' ); ?></p>
				</div>
				<div class="col-image">
					<img src="<?php echo OXI_FLIP_BOX_URL . 'image/getting-started/Intruduction/50-hover-effects.png'; ?>" alt=""/>
				</div>
			</section>

			<section class="section-full">
				<div class="col-image">
					<img src="<?php echo OXI_FLIP_BOX_URL . 'image/getting-started/Intruduction/customization.png'; ?>" alt=""/>
				</div>
				<div class="col-description">
					<h2><?php echo esc_html__( 'Full Customization Options', 'oxi-flip-box-plugin' ); ?></h2>
					<p><?php echo esc_html__( 'Easily adjust colors, fonts, overlay content, icons, and buttons. Flipbox gives you full control over design elements to match your brand perfectly. Whether you want a clean minimal look or bold interactive styles, the customization options let you achieve it without touching code. Your flipboxes remain fully responsive, ensuring they look stunning on desktops, tablets, and mobile devices alike.', 'oxi-flip-box-plugin' ); ?></p>
				</div>
			</section>

			<section class="section-full">
				<div class="col-description">
					<h2><?php echo esc_html__( 'Responsive & Mobile Friendly', 'oxi-flip-box-plugin' ); ?><span class="badge"><?php echo esc_html__( 'New', 'oxi-flip-box-plugin' ); ?> ⚡</span></h2>
					<p><?php echo esc_html__( 'Every effect is built with mobile in mind. Flipboxes automatically adapt to all screen sizes, ensuring your site looks great on desktops, tablets, and phones. No extra coding or adjustments are required — everything works out of the box. With fully responsive layouts, you can deliver a seamless user experience that keeps your design consistent across every device.', 'oxi-flip-box-plugin' ); ?></p>
				</div>
				<div class="col-image">
					<img src="<?php echo OXI_FLIP_BOX_URL . 'image/getting-started/Intruduction/responsive.png'; ?>" alt=""/>
				</div>
			</section>

			<div class="content-heading heading-shortcodes">
				<h2>
					<mark><?php echo esc_html__( 'Flexible Shortcodes', 'oxi-flip-box-plugin' ); ?></mark>
					<?php echo esc_html__( 'for Easy Integration', 'oxi-flip-box-plugin' ); ?>
				</h2>
				<p><?php echo esc_html__( 'Insert Flipboxes anywhere using shortcodes or page builders.', 'oxi-flip-box-plugin' ); ?></p>
			</div>

			<section class="section-shortcodes section-full">
				<div class="col-description">
					<h2><?php echo esc_html__( 'Insert Anywhere', 'oxi-flip-box-plugin' ); ?></h2>
					<p><?php echo esc_html__( 'With shortcode support, you can easily place Flipboxes inside posts, pages, widgets, or custom layouts.', 'oxi-flip-box-plugin' ); ?></p>
					<div class="shortcode-examples">
						<p><strong><?php echo esc_html__( 'Example shortcode:', 'oxi-flip-box-plugin' ); ?></strong></p>
						<ul>
							<li><code>[oxi_flipbox id="1"]</code> - <?php echo esc_html__( 'Displays a Flipbox with the selected design', 'oxi-flip-box-plugin' ); ?></li>
						</ul>
					</div>
				</div>
				<div class="col-image">
					<img src="<?php echo OXI_FLIP_BOX_URL . 'image/getting-started/Intruduction/shortcode.png'; ?>" alt=""/>
				</div>
			</section>

			<div class="content-heading heading-integrations">
				<h2>
					<mark><?php echo esc_html__( 'Advanced Features', 'oxi-flip-box-plugin' ); ?></mark>
					<?php echo esc_html__( 'for Designers', 'oxi-flip-box-plugin' ); ?>
				</h2>
				<p><?php echo esc_html__( 'Take your visuals further with these powerful tools', 'oxi-flip-box-plugin' ); ?></p>
			</div>

			<div class="section-wrap">
				<section class="section-private-folders section-half">
					<div class="col-description">
						<h2><?php echo esc_html__( 'Icon & Button Support', 'oxi-flip-box-plugin' ); ?></h2>
						<p><?php echo esc_html__( 'Enhance your Flipboxes with custom icons and powerful call-to-action buttons that engage your audience and drive interaction. Whether you want to highlight key features, link to products, or guide visitors to the next step, Flipbox makes it simple to combine visuals with actionable elements.', 'oxi-flip-box-plugin' ); ?></p>
					</div>
				<div class="col-image">
					<img src="<?php echo OXI_FLIP_BOX_URL . 'image/getting-started/Intruduction/custom-icon.png'; ?>" alt=""/>
				</div>
			</section>

			<section class="section-private-folders section-half">
				<div class="col-description">
					<h2><?php echo esc_html__( 'Custom CSS for Advanced Styling', 'oxi-flip-box-plugin' ); ?></h2>
					<p><?php echo esc_html__( 'Take full control of your Flipboxes with the built-in Custom CSS option. Whether you need small tweaks or advanced styling, you can easily add your own code without touching the plugin’s core files. This gives developers and designers the freedom to create unique, tailored designs that perfectly match any website.', 'oxi-flip-box-plugin' ); ?></p>
				</div>
				<div class="col-image">
					<img src="<?php echo OXI_FLIP_BOX_URL . 'image/getting-started/Intruduction/custom-css.png'; ?>" alt=""/>
				</div>
			</section>
			</div>

			<section class="section-conclusion section-full">
				<div class="col-description">
					<h2><?php echo esc_html__( 'Ready to Add Stunning Flip Effects?', 'oxi-flip-box-plugin' ); ?></h2>
					<p><?php echo esc_html__( 'Flipbox gives you everything you need to create beautiful, interactive image effects that boost engagement and make your site stand out. Start building with Flipbox today!', 'oxi-flip-box-plugin' ); ?></p>
				</div>
				<div>
					<a href="admin.php?page=oxi-flip-box-ultimate-new" class="wpkin-btn btn-primary get-pro-btn">
						<?php echo esc_html__( 'Configure Flipbox Now', 'oxi-flip-box-plugin' ); ?>
					</a>
				</div>
			</section>
		</div>
		<?php
	}
}
