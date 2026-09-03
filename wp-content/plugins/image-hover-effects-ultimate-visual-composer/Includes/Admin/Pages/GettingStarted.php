<?php

namespace OXI_FLIP_BOX_PLUGINS\Includes\Admin\Pages;

/**
 * Description of GettingStarted
 *
 * @author Richard
 */
class GettingStarted {

	public function __construct() {
		$this->Public_Render();
	}

	public function Public_Render() {
		?>
		<div id="wpkin-flipbox-getting-started">
			<div class="wpkin-flipbox-plugin-container">
				<div class="getting-started-header">
					<img src="<?php echo esc_attr( OXI_FLIP_BOX_URL . 'image/sa-logo.png' ); ?>" alt="Flipbox">
					<p class="wpkin-flipbox-plugin-description">
						<?php echo esc_html__( "Thank you for choosing Flipbox - Awesomes Flip Boxes Image Overlay - the most friendly WordPress Flip Box Or Image Overlay Plugins. Here's how to get started.", 'oxi-flip-box-plugin' ); ?>
					</p>
				</div>
				<div class="getting-started-menu">
					<div class="menu-item active" data-target="introduction">
						<img src="<?php echo OXI_FLIP_BOX_URL . 'image/getting-started/menu/introduction.svg'; ?>">
						<span>Introduction</span>
					</div>
					<div class="menu-item" data-target="basic-usage">
						<img src="<?php echo OXI_FLIP_BOX_URL . 'image/getting-started/menu/basic-usage.svg'; ?>">
						<span>Basic Usage</span>
					</div>
					<div class="menu-item" data-target="help">
						<img src="<?php echo OXI_FLIP_BOX_URL . 'image/getting-started/menu/help.svg'; ?>">
						<span>Help</span>
					</div>
					<div class="menu-item" data-target="what-new">
						<img src="<?php echo OXI_FLIP_BOX_URL . 'image/getting-started/menu/what-new.svg'; ?>">
						<span>Changelog</span>
					</div>
					<div class="menu-item" data-target="get-pro">
						<img src="<?php echo OXI_FLIP_BOX_URL . 'image/getting-started/menu/get-pro.svg'; ?>">
						<span>Get PRO</span>
					</div>
				</div>

				<div class="introduction" data="introduction">
					<?php
                    $int = new Tabs\Introduction();
					$int->render();
                    ?>
				</div>
				<div class="basic-usage" data="basic-usage">
					<?php
                    $int = new Tabs\BasicUses();
					$int->render();
                    ?>
				</div>
				<div class="help" data="help">
					<?php
                    $int = new Tabs\Help();
					$int->render();
                    ?>
				</div>
				<div class="what-new" data="what-new">
					<?php
                    $int = new Tabs\Changelog();
					$int->render();
                    ?>
				</div>
				<div class="get-pro" data="get-pro">
					<?php
                    $int = new Tabs\GetPro();
					$int->render();
                    ?>
				</div>
			</div>
		</div>
		<?php
	}
}
