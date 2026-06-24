<?php
if ( !defined( 'ABSPATH' ) ) { exit; }

if( !class_exists( 'BPTBAdminMenu' ) ) {
	class BPTBAdminMenu {
		function __construct() {
			add_action( 'admin_menu', [ $this, 'bptbAdminMenu' ] );
			add_action( 'admin_enqueue_scripts', [$this, 'bptbAdminEnqueueScripts'] );
			add_action( 'wp_ajax_bptb_disabled_blocks', [$this, 'handleDisabledBlocks'] );
		}

		/**
		 * Persist the set of blocks turned off from the dashboard Blocks page.
		 * Stores an array of block names in the `bptbDisabledBlocks` option.
		 */
		function handleDisabledBlocks() {
			$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : null;
			if ( ! wp_verify_nonce( $nonce, 'bptb_disabled_blocks' ) || ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( 'Invalid Request' );
			}

			$data = isset( $_POST['data'] ) ? json_decode( wp_unslash( $_POST['data'] ), true ) : null;

			if ( is_array( $data ) ) {
				$data = array_values( array_map( 'sanitize_text_field', $data ) );
				update_option( 'bptbDisabledBlocks', $data );
				wp_send_json_success( $data );
			}

			wp_send_json_success( get_option( 'bptbDisabledBlocks', [] ) );
		}

		function bptbAdminMenu() {
			// Top-level "Pricing Table" menu in the WordPress dashboard (not under
			// Tools). The "ShortCodes" CPT attaches here via show_in_menu.
			add_menu_page(
				__('Pricing Table - bPlugins', 'b-pricing-table'),
				__('Pricing Table', 'b-pricing-table'),
				'manage_options',
				'b-pricing-table',
				[$this, 'renderDashboardPage'],
				'dashicons-editor-table',
				20
			);

			add_submenu_page(
				'b-pricing-table',
				__('Help - bPlugins', 'b-pricing-table'),
				__('Help And Demo', 'b-pricing-table'),
				'manage_options',
				'b-pricing-table',
				[$this, 'renderDashboardPage']
			);
		}

		function renderDashboardPage(){ ?>
			<div
				id='bptbDashboard'
				data-info='<?php echo esc_attr( wp_json_encode( [
					'version' => BPTB_VERSION,
					'isPremium' => bptbIsPremium(),
					'hasPro' => BPTB_HAS_PRO,
					'adminUrl' => admin_url(),
					'upgradeUrl' => function_exists('fs_get_upgrade_url') ? fs_get_upgrade_url() : '',
					'licenseActiveNonce' => wp_create_nonce( 'bPlLicenseActivation' ),
					'disabledBlocksNonce' => wp_create_nonce( 'bptb_disabled_blocks' ),
					'bptbDisabledBlocks' => get_option( 'bptbDisabledBlocks', [] )
				] ) ); ?>'
			></div>
		<?php }

		function bptbAdminEnqueueScripts( $hook ) {
			if( strpos( $hook, 'b-pricing-table' ) ){
				wp_enqueue_style( 'bptb-admin-dashboard', BPTB_DIR_URL . 'build/admin/dashboard.css', [], BPTB_VERSION );
				$asset = require BPTB_DIR_PATH . 'build/admin/dashboard.asset.php';
				wp_enqueue_script( 'bptb-admin-dashboard', BPTB_DIR_URL . 'build/admin/dashboard.js', array_merge( $asset['dependencies'], [ 'wp-util' ] ), $asset['version'], true );
				wp_set_script_translations( 'bptb-admin-dashboard', 'b-pricing-table', BPTB_DIR_PATH . 'languages' );
			}
		}
	}
	new BPTBAdminMenu();
}


