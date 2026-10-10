<?php
/**
 * Plugin Name: Pricing Table - Block
 * Description: Display product prices as a table
 * Version: 2.1.5
 * Author: bPlugins
 * Author URI: https://bplugins.com
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: b-pricing-table
 * @fs_premium_only /vendor/freemius, /includes/admin/LicenseActivation.php, build/blocks/pricing-comparison, build/blocks/price-list, build/blocks/pricing-slider, build/blocks/coupon-box, build/blocks/saas-pricing, build/blocks/hosting-pricing, build/blocks/gym-pricing, build/blocks/course-pricing, build/blocks/agency-pricing, build/blocks/salon-pricing, build/blocks/wellness-pricing, build/blocks/restaurant-pricing, build/blocks/photography-pricing, build/blocks/coworking-pricing
 * @fs_free_only /vendor/freemius-lite
 */
 
// ABS PATH
if ( !defined( 'ABSPATH' ) ) { exit; }

if ( function_exists( 'bptb_fs' ) ) {
  bptb_fs()->set_basename( true, __FILE__ );
} else {
	// Define Constant
	define( 'BPTB_VERSION', isset( $_SERVER['HTTP_HOST'] ) && 'localhost' === $_SERVER['HTTP_HOST'] ? time() : '2.1.5' );
	define( 'BPTB_DIR_URL', plugin_dir_url(__FILE__));
	define( 'BPTB_DIR_PATH', plugin_dir_path(__FILE__));

    function bptb_fs() {
      global $bptb_fs;

      if ( ! isset( $bptb_fs ) ) {
				
					require_once dirname(__FILE__) . '/vendor/freemius-lite/start.php';
				

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
									'trial'               =>[
											'days'               => 7,
											'is_require_payment' => false
									],
									'menu' =>[
												'slug'        => 'b-pricing-table',
												'first-path'  =>  'admin.php?page=b-pricing-table#/welcome',
												'support'     => false,
												'contact'        => false,
									]
				];

				 fs_lite_dynamic_init( $bptbConfig );
		  }
			return $bptb_fs;
		}

		bptb_fs();
		do_action( 'bptb_fs_loaded' );


	    require_once BPTB_DIR_PATH . 'includes/admin/ShortCode.php';
	
		require_once BPTB_DIR_PATH . 'includes/admin/AdminMenu.php';
	

	if(!class_exists("BPTBPlugin")) {
		class BPTBPlugin{
			function __construct(){
				add_action( 'init', [$this, 'onInit'] );
			}

			function onInit() {
				// Free base block — always registered. Its Pro features are gated
				// inside the block by bptbpipecheck/bptbIsPremium(), so they appear
				// locked in the free version (like the business-card block).
				register_block_type( __DIR__ . '/build/blocks/pricing-table' );

				// Pro-only blocks (added later) — registered only with an active
				// premium license, and only when not disabled on the dashboard
				// Blocks page. Add entries as 'build-dir' => 'bptb/block-name'.
				
			}

			
		}
		new BPTBPlugin();
	}
}