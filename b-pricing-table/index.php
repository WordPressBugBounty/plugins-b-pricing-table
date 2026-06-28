<?php

/**
 * Plugin Name: Pricing Table - Block
 * Description: Display product prices as a table
 * Version: 2.1.2
 * Author: bPlugins
 * Author URI: https://bplugins.com
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: b-pricing-table
 */
// ABS PATH
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( function_exists( 'bptb_fs' ) ) {
    bptb_fs()->set_basename( false, __FILE__ );
} else {
    // Define Constant
    define( 'BPTB_VERSION', ( isset( $_SERVER['HTTP_HOST'] ) && 'localhost' === $_SERVER['HTTP_HOST'] ? time() : '2.1.2' ) );
    define( 'BPTB_DIR_URL', plugin_dir_url( __FILE__ ) );
    define( 'BPTB_DIR_PATH', plugin_dir_path( __FILE__ ) );
    define( 'BPTB_HAS_PRO', file_exists( dirname( __FILE__ ) . '/vendor/freemius/start.php' ) );
    function bptb_fs() {
        global $bptb_fs;
        if ( !isset( $bptb_fs ) ) {
            if ( BPTB_HAS_PRO ) {
                require_once dirname( __FILE__ ) . '/vendor/freemius/start.php';
            } else {
                require_once dirname( __FILE__ ) . '/vendor/freemius-lite/start.php';
            }
            $bptbConfig = [
                'id'                  => '19836',
                'slug'                => 'b-pricing-table',
                'premium_slug'        => 'b-pricing-table-pro',
                'type'                => 'plugin',
                'public_key'          => 'pk_b226dd725f9d04a9927e37d9cc77f',
                'is_premium'          => false,
                'premium_suffix'      => 'Pro',
                'has_premium_version' => true,
                'has_addons'          => false,
                'has_paid_plans'      => true,
                'trial'               => [
                    'days'               => 7,
                    'is_require_payment' => false,
                ],
                'menu'                => [
                    'slug'       => 'b-pricing-table',
                    'first-path' => 'admin.php?page=b-pricing-table#/welcome',
                    'support'    => false,
                    'contact'    => false,
                ],
            ];
            $bptb_fs = ( BPTB_HAS_PRO ? fs_dynamic_init( $bptbConfig ) : fs_lite_dynamic_init( $bptbConfig ) );
        }
        return $bptb_fs;
    }

    bptb_fs();
    do_action( 'bptb_fs_loaded' );
    // ... Your plugin's main file logic ...
    function bptbIsPremium() {
        return ( BPTB_HAS_PRO ? bptb_fs()->can_use_premium_code() : false );
    }

    // Shortcode + the "ShortCodes" CPT are available in BOTH the free and pro
    // versions (the [bptb id=X] workflow), so this loads unconditionally.
    require_once BPTB_DIR_PATH . 'includes/admin/ShortCode.php';
    // License activation AJAX bridge for the dashboard Activation page. Loaded
    // whenever the Pro build is present so license status reads correctly and
    // keys can be activated/deactivated even before a license is active.
    if ( BPTB_HAS_PRO ) {
        require_once BPTB_DIR_PATH . 'includes/admin/LicenseActivation.php';
    }
    // Both menus register a top-level "Pricing Table" item; the licensed build
    // uses the Pro menu, everything else uses the free menu.
    if ( BPTB_HAS_PRO && bptbIsPremium() ) {
        require_once BPTB_DIR_PATH . 'includes/admin/AdminMenuPro.php';
    } else {
        require_once BPTB_DIR_PATH . 'includes/admin/AdminMenu.php';
    }
    if ( !class_exists( "BPTBPlugin" ) ) {
        class BPTBPlugin {
            function __construct() {
                add_action( 'init', [$this, 'onInit'] );
                add_action( 'enqueue_block_editor_assets', [$this, 'bptbEnqueueBlockEditorAssets'] );
            }

            function onInit() {
                // Free base block — always registered. Its Pro features are gated
                // inside the block by bptbpipecheck/bptbIsPremium(), so they appear
                // locked in the free version (like the business-card block).
                register_block_type( __DIR__ . '/build/blocks/pricing-table' );
                // Pro-only blocks (added later) — registered only with an active
                // premium license, and only when not disabled on the dashboard
                // Blocks page. Add entries as 'build-dir' => 'bptb/block-name'.
                if ( BPTB_HAS_PRO && bptbIsPremium() ) {
                    $disabled = get_option( 'bptbDisabledBlocks', [] );
                    if ( !is_array( $disabled ) ) {
                        $disabled = [];
                    }
                    $pro_blocks = [
                        'pricing-comparison'  => 'bptb/pricing-comparison',
                        'price-list'          => 'bptb/price-list',
                        'pricing-slider'      => 'bptb/pricing-slider',
                        'coupon-box'          => 'bptb/coupon-box',
                        'saas-pricing'        => 'bptb/saas-pricing',
                        'hosting-pricing'     => 'bptb/hosting-pricing',
                        'gym-pricing'         => 'bptb/gym-pricing',
                        'course-pricing'      => 'bptb/course-pricing',
                        'agency-pricing'      => 'bptb/agency-pricing',
                        'salon-pricing'       => 'bptb/salon-pricing',
                        'wellness-pricing'    => 'bptb/wellness-pricing',
                        'restaurant-pricing'  => 'bptb/restaurant-pricing',
                        'photography-pricing' => 'bptb/photography-pricing',
                        'coworking-pricing'   => 'bptb/coworking-pricing',
                    ];
                    foreach ( $pro_blocks as $dir => $block_name ) {
                        if ( in_array( $block_name, $disabled, true ) ) {
                            continue;
                        }
                        register_block_type( __DIR__ . '/build/blocks/' . $dir );
                    }
                }
            }

            function bptbEnqueueBlockEditorAssets() {
                wp_add_inline_script( 'bptb-pricing-table-editor-script', 'const bptbpipecheck =  ' . wp_json_encode( bptbIsPremium() ) . ';', 'before' );
            }

        }

        new BPTBPlugin();
    }
}