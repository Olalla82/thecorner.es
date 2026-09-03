<?php

namespace OXI_FLIP_BOX_PLUGINS\Includes\Admin\Pages\Tabs;

class Changelog {

    public function render() {

        // Full changelog array
        $logs = [
			[
                'version' => '2.10.4',
                'date' => '08-09-2025',
                'sections' => [
                    'fix' => [
                        '*Fixed Visual composer fatal error issue get_row().',
                    ],
                ],
            ],
			[
                'version' => '2.10.3',
                'date' => '07-09-2025',
                'sections' => [
                    'fix' => [
                        'Fixed Visual composer fatal error issue.',
                        'Fixed Widget fatal error issue.',
                    ],
                ],
            ],
            [
                'version' => '2.10.2',
                'date' => '05-09-2025',
                'sections' => [
                    'fix' => [
                        'Fixed 1596 characters of unexpected output during activation issue.',
                    ],
                ],
            ],
            [
                'version' => '2.10.1',
                'date' => '05-09-2025',
                'sections' => [
                    'enhancement' => [
						'Improved plugin structure for better readability and maintainability.',
						'Updated the Getting Started page for clearer instructions.',
					],
                    'fix' => [
                        'Fixed security issue.',
                    ],
                ],
            ],
            [
                'version' => '2.10.0',
                'date' => '15-08-2025',
                'sections' => [
                    'new' => [
                        'Added Getting Started page.',
                        'Added Freemius for license management.',
                    ],
                ],
            ],
            [
                'version' => '2.9.8',
                'date' => '10-07-2025',
                'sections' => [
                    'enhancement' => [
                        'Tested compatibility with WordPress 6.8.',
                    ],
                    'fix' => [
                        'Fixed shortcode list issue.',
                    ],
                ],
            ],
            [
                'version' => '2.9.7',
                'date' => '20-06-2025',
                'sections' => [
                    'enhancement' => [
                        'Tested compatibility with WordPress 6.7.',
                    ],
                    'fix' => [
                        'Fixed data table search issue.',
                    ],
                ],
            ],
            [
                'version' => '2.9.6',
                'date' => '05-05-2025',
                'sections' => [
                    'enhancement' => [
                        'Tested compatibility with WordPress 6.6.2.',
                    ],
                    'fix' => [
                        'Fixed Ajax bugs.',
                    ],
                ],
            ],
            [
                'version' => '2.9.5',
                'date' => '01-04-2025',
                'sections' => [
                    'enhancement' => [
                        'Tested compatibility with WordPress 6.4.3.',
                    ],
                    'fix' => [
                        'Fixed Ajax bugs.',
                    ],
                ],
            ],
            [
                'version' => '2.9.3',
                'date' => '12-02-2025',
                'sections' => [
                    'enhancement' => [
                        'Tested compatibility with WordPress 6.3.0.',
                    ],
                    'fix' => [
                        'Fixed Ajax bugs.',
                    ],
                ],
            ],
            [
                'version' => '2.9.2',
                'date' => '25-01-2025',
                'sections' => [
                    'enhancement' => [
                        'Tested compatibility with WordPress 6.2.2.',
                    ],
                    'fix' => [
                        'Fixed some settings issues.',
                    ],
                ],
            ],
            [
                'version' => '2.9.1',
                'date' => '10-01-2025',
                'sections' => [
                    'enhancement' => [
                        'Tested compatibility with WordPress 6.2.0.',
                    ],
                    'fix' => [
                        'Fixed Ajax bugs.',
                    ],
                ],
            ],
            [
                'version' => '2.9.0',
                'date' => '15-12-2024',
                'sections' => [
                    'enhancement' => [
                        'Tested compatibility with WordPress 6.1.1.',
                    ],
                    'fix' => [
                        'Fixed echo bugs.',
                    ],
                ],
            ],
            [
                'version' => '2.8.5',
                'date' => '01-11-2024',
                'sections' => [
                    'enhancement' => [
                        'Tested compatibility with WordPress 6.0.3.',
                        'Fixed some settings issues.',
                    ],
                ],
            ],
            [
                'version' => '2.8.4',
                'date' => '15-10-2024',
                'sections' => [
                    'enhancement' => [
                        'Tested compatibility with WordPress 6.0.1.',
                        'Fixed some settings issues.',
                    ],
                ],
            ],
            [
                'version' => '2.8.3',
                'date' => '01-10-2024',
                'sections' => [
                    'enhancement' => [
                        'Tested compatibility with WordPress 6.0.0.',
                    ],
                    'new' => [
                        'Added alt tag for images.',
                    ],
                ],
            ],
            [
                'version' => '2.8.2',
                'date' => '15-09-2024',
                'sections' => [
                    'fix' => [
                        'Solved HTML issues.',
                    ],
                ],
            ],
            [
                'version' => '2.8.0',
                'date' => '01-09-2024',
                'sections' => [
                    'new' => [
                        'Added support for HTML tags.',
                    ],
                ],
            ],
            [
                'version' => '2.7.1',
                'date' => '15-08-2024',
                'sections' => [
                    'fix' => [
                        'Fixed new Flipbox issues.',
                    ],
                ],
            ],
            [
                'version' => '2.7.0',
                'date' => '01-08-2024',
                'sections' => [
                    'enhancement' => [
                        'Updated Flipbox modules.',
                    ],
                ],
            ],
        ];
        ?>
        
        <div id="what-new" class="content-what-new">
            <div class="content-heading">
                <h2>
                    <?php echo __( 'Exploring the', 'oxi-flip-box-plugin' ); ?> 
                    <mark><?php echo __( 'Latest Updates', 'oxi-flip-box-plugin' ); ?></mark>
                </h2>
                <p>
                    <?php echo __( 'Dive into the recent changelog for fresh insights about new features and improvements.', 'oxi-flip-box-plugin' ); ?>
                </p>
            </div>

            <?php foreach ( $logs as $log ) : ?>
                <div class="log">
                    <div class="log-header" style="cursor:pointer;">
                        <span class="log-version"><?php echo esc_html( $log['version'] ); ?></span>
                        <span class="log-date">(<?php echo esc_html( $log['date'] ); ?>)</span>
                        <i class="dashicons dashicons-arrow-down-alt2"></i>
                    </div>
                    <div class="log-body" style="display:none;">
                        <?php foreach ( $log['sections'] as $section => $items ) : ?>
                            <div class="log-section <?php echo esc_attr( $section ); ?>">
                                <h3>
                                    <?php
                                        $section_titles = [
                                            'new' => __( 'New Features', 'oxi-flip-box-plugin' ),
                                            'fix' => __( 'Bug Fixes', 'oxi-flip-box-plugin' ),
                                            'enhancement' => __( 'Improvements', 'oxi-flip-box-plugin' ),
                                            'remove' => __( 'Deprecations', 'oxi-flip-box-plugin' ),
                                        ];
                                        echo $section_titles[ $section ];
										?>
                                </h3>
                                <?php foreach ( $items as $item ) : ?>
                                    <div class="log-item log-item-<?php echo esc_attr( $section ); ?>">
                                        <?php
                                            $section_icons = [
                                                'new' => 'dashicons-plus-alt2',
                                                'fix' => 'dashicons-saved',
                                                'enhancement' => 'dashicons-star-filled',
                                                'remove' => 'dashicons-trash',
                                            ];
											?>
                                        <i class="dashicons <?php echo esc_attr( $section_icons[ $section ] ); ?>"></i>
                                        <?php echo esc_html( $item ); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php
    }
}
