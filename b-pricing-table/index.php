<?php
/**
 * Plugin Name: Pricing Table - Block
 * Description: Display product prices as a table
 * Version: 1.0.7
 * Author: bPlugins
 * Author URI: https://bplugins.com
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: b-pricing-table
 */

// ABS PATH
if ( !defined( 'ABSPATH' ) ) { exit; }

// Constant
define( 'BPTB_VERSION', isset( $_SERVER['HTTP_HOST'] ) && 'localhost' === $_SERVER['HTTP_HOST'] ? time() : '1.0.7' );

if( !class_exists( 'BPTBPlugin' ) ){
	class BPTBPlugin{
		function __construct(){
			add_action( 'init', [$this, 'onInit'] );
		}

		function onInit() {
			register_block_type( __DIR__ . '/build' );
		}
	}
	new BPTBPlugin;
}