<?php
if ( !defined( 'ABSPATH' ) ) { exit; }

if( !class_exists( 'BPTBAdminMenu' ) ) {
	class BPTBAdminMenu {
		function __construct() {
			add_action( 'admin_menu', [ $this, 'bptbAdminMenu' ] );
			add_action( 'admin_enqueue_scripts', [$this, 'bptbAdminEnqueueScripts'] );
		}

		function bptbAdminMenu() {
				add_submenu_page(
				'tools.php',
				__('Pricing Table - bPlugins', 'b-pricing-table'),
				__('Pricing Table', 'b-pricing-table'),
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
					'upgradeUrl' => function_exists('fs_get_upgrade_url') ? fs_get_upgrade_url() : ''
				] ) ); ?>'
			></div>
		<?php }

		function bptbAdminEnqueueScripts( $hook ) {
			if( strpos( $hook, 'b-pricing-table' ) ){
				wp_enqueue_style( 'bptb-admin-dashboard', BPTB_DIR_URL . 'build/admin/dashboard.css', [], BPTB_VERSION );
				wp_enqueue_script( 'bptb-admin-dashboard', BPTB_DIR_URL . 'build/admin/dashboard.js', [ 'react', 'react-dom', "wp-components" ], BPTB_VERSION, true );
				wp_set_script_translations( 'bptb-admin-dashboard', 'b-pricing-table', BPTB_DIR_PATH . 'languages' );
			}
		}
	}
	new BPTBAdminMenu();
}


