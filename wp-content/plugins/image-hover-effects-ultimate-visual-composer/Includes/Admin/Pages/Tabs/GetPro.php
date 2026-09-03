<?php

namespace OXI_FLIP_BOX_PLUGINS\Includes\Admin\Pages\Tabs;

class GetPro {

    public function render() {

        // Feature array
        $features = [
            'Design Features' => [
                [
					'title' => '20+ Free Flipbox Styles',
					'free' => true,
					'pro' => true,
				],
                [
					'title' => '70+ Premium Flipbox Styles',
					'free' => false,
					'pro' => true,
				],
                [
					'title' => 'Image & Icon Support',
					'free' => true,
					'pro' => true,
				],
                [
					'title' => 'Custom Background Colors & Gradients',
					'free' => false,
					'pro' => true,
				],
                [
					'title' => 'Advanced Hover Animations',
					'free' => true,
					'pro' => true,
				],
            ],
            'Content Features' => [
                [
					'title' => 'Custom Titles & Descriptions',
					'free' => true,
					'pro' => true,
				],
                [
					'title' => 'Custom Fonts & Typography',
					'free' => false,
					'pro' => true,
				],
                [
					'title' => 'Unlimited Flipboxes',
					'free' => true,
					'pro' => true,
				],
                [
					'title' => 'Custom Links & Call To Action Buttons',
					'free' => true,
					'pro' => true,
				],
            ],
            'Integrations' => [
                [
					'title' => 'Gutenberg Block Support',
					'free' => true,
					'pro' => true,
				],
                [
					'title' => 'Elementor Integration',
					'free' => true,
					'pro' => true,
				],
                [
					'title' => 'WPBakery Page Builder Integration',
					'free' => true,
					'pro' => true,
				],
                [
					'title' => 'Divi Builder Integration',
					'free' => true,
					'pro' => true,
				],
            ],
            'Styling Options' => [
                [
					'title' => 'Custom Border & Shadow Settings',
					'free' => false,
					'pro' => true,
				],
                [
					'title' => 'Flip Direction (Left, Right, Top, Bottom)',
					'free' => true,
					'pro' => true,
				],
                [
					'title' => 'Custom CSS editor',
					'free' => false,
					'pro' => true,
				],
            ],
            'Support & Updates' => [
                [
					'title' => 'Documentation Access',
					'free' => true,
					'pro' => true,
				],
                [
					'title' => 'Community Support (WordPress.org)',
					'free' => true,
					'pro' => true,
				],
                [
					'title' => 'Premium Support',
					'free' => false,
					'pro' => true,
				],
                [
					'title' => 'Regular Updates',
					'free' => true,
					'pro' => true,
				],
                [
					'title' => 'Priority Feature Requests',
					'free' => false,
					'pro' => true,
				],
            ],
        ];
        ?>

        <div id="get-pro" class="getting-started-content content-get-pro active">
            <div class="content-heading">
                <h2>
                    <mark><?php echo __( 'Enhance Your Flipboxes', 'oxi-flip-box-plugin' ); ?></mark>
                    <?php echo __( 'with Flipbox PRO', 'oxi-flip-box-plugin' ); ?>
                </h2>
                <p><?php echo __( 'Unlock premium Flipbox designs, advanced animations, and customization options to create stunning hover effects that engage your visitors.', 'oxi-flip-box-plugin' ); ?></p>
                <a href="https://wpkindemos.com/flipbox/pricing/" class="wpkin-btn btn-primary get-pro-btn" target="_blank" rel="noopener noreferrer">
                    <i class="dashicons dashicons-awards"></i> <?php echo __( 'Get PRO Now', 'oxi-flip-box-plugin' ); ?>
                </a>
            </div>

            <div class="content-heading free-vs-pro">
                <h2><?php echo __( 'Compare Features', 'oxi-flip-box-plugin' ); ?></h2>
                <div class="free-vs-pro-wrap">
                    <span><?php echo __( 'FREE', 'oxi-flip-box-plugin' ); ?></span>
                    <?php echo __( 'vs', 'oxi-flip-box-plugin' ); ?>
                    <span><?php echo __( 'PRO', 'oxi-flip-box-plugin' ); ?></span>
                </div>
                <p><?php echo __( 'The PRO version unlocks advanced Flipbox styles, animations, and integrations to make your designs more attractive and professional.', 'oxi-flip-box-plugin' ); ?></p>
            </div>

            <div class="features-list">
                <div class="list-header">
                    <div class="feature-title"><?php echo __( 'Feature List', 'oxi-flip-box-plugin' ); ?></div>
                    <div class="feature-free"><?php echo __( 'Free', 'oxi-flip-box-plugin' ); ?></div>
                    <div class="feature-pro"><?php echo __( 'Pro', 'oxi-flip-box-plugin' ); ?></div>
                </div>

                <?php foreach ( $features as $section => $items ) : ?>
                    <div class="feature feature-heading">
                        <div class="feature-title"><?php echo esc_html__( $section, 'oxi-flip-box-plugin' ); ?></div>
                        <div class="feature-free"></div>
                        <div class="feature-pro"></div>
                    </div>
                    <?php foreach ( $items as $feature ) : ?>
                        <div class="feature">
                            <div class="feature-title"><?php echo esc_html__( $feature['title'], 'oxi-flip-box-plugin' ); ?></div>
                            <div class="feature-free">
                                <i class="dashicons <?php echo $feature['free'] ? 'dashicons-saved' : 'dashicons-no-alt'; ?>"></i>
                            </div>
                            <div class="feature-pro">
                                <i class="dashicons <?php echo $feature['pro'] ? 'dashicons-saved' : 'dashicons-no-alt'; ?>"></i>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>

            <div class="get-pro-cta">
                <div class="cta-content">
                    <h2><?php echo __( 'Create Stunning Flipboxes with', 'oxi-flip-box-plugin' ); ?> <mark><?php echo __( 'Flipbox PRO', 'oxi-flip-box-plugin' ); ?></mark></h2>
                    <p><?php echo __( 'Upgrade to PRO and unlock 70+ premium styles, advanced customization, and premium support to transform your website visuals.', 'oxi-flip-box-plugin' ); ?></p>
                </div>
                <div class="cta-btn">
                    <a href="https://wpkindemos.com/flipbox/pricing/" class="wpkin-btn btn-primary" target="_blank" rel="noopener noreferrer">
                        <i class="dashicons dashicons-cart"></i> <?php echo __( 'Upgrade Now', 'oxi-flip-box-plugin' ); ?>
                    </a>
                </div>
            </div>
        </div>

        <?php
    }
}
