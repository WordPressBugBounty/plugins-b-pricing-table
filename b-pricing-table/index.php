<?php

/**
 * Plugin Name: Pricing Table - Block
 * Description: Display product prices as a table
 * Version: 2.0.0
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
    define( 'BPTB_VERSION', ( isset( $_SERVER['HTTP_HOST'] ) && 'localhost' === $_SERVER['HTTP_HOST'] ? time() : '2.0.0' ) );
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
                'is_premium'          => true,
                'premium_suffix'      => 'Pro',
                'has_premium_version' => true,
                'has_addons'          => false,
                'has_paid_plans'      => true,
                'trial'               => array(
                    'days'               => 7,
                    'is_require_payment' => false,
                ),
                'menu'                => array(
                    'slug'           => 'b-pricing-table',
                    'override_exact' => true,
                    'first-path'     => 'tools.php?page=b-pricing-table',
                    'contact'        => false,
                    'support'        => false,
                    'parent'         => array(
                        'slug' => 'tools.php',
                    ),
                ),
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
        // return false;
    }

    require_once BPTB_DIR_PATH . 'includes/admin/AdminMenu.php';
    if ( !class_exists( "BPTBPlugin" ) ) {
        class BPTBPlugin {
            function __construct() {
                add_action( 'init', [$this, 'onInit'] );
                add_action( 'enqueue_block_editor_assets', [$this, 'bptbEnqueueBlockEditorAssets'] );
            }

            function onInit() {
                register_block_type( __DIR__ . '/build' );
            }

            function bptbEnqueueBlockEditorAssets() {
                wp_add_inline_script( 'bptb-pricing-table-editor-script', 'const bptbpipecheck =  ' . wp_json_encode( bptbIsPremium() ) . ';', 'before' );
            }

        }

        new BPTBPlugin();
    }
}