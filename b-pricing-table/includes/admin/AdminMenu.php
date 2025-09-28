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
				'tools.php', //parent slug
				__('Dashboard - Pricing Table Block by bPlugins', 'b-pricing-table'), //page title
				__('Pricing Table', 'b-pricing-table'), //menu title
				'manage_options', //capability
				'b-pricing-table', //menu slug
				[$this, 'renderDashboardPage'] //function to display the page content
			);
		}
	
		function renderDashboardPage(){ ?>
			<div
				id='bptbDashboard'
				data-info='<?php echo esc_attr( wp_json_encode( [
					'version' => BPTB_VERSION,
					'isPremium' => bptbIsPremium(),
					'hasPro' => BPTB_HAS_PRO,
					'adminUrl' => admin_url()
				] ) ); ?>'
			></div>
		<?php }
	
		function bptbAdminEnqueueScripts( $hook ) {
			if( 'tools_page_b-pricing-table' === $hook ){
				wp_enqueue_style( 'bptb-admin-dashboard', BPTB_DIR_URL . 'build/admin/dashboard.css', [], BPTB_VERSION );
				wp_enqueue_script( 'bptb-admin-dashboard', BPTB_DIR_URL . 'build/admin/dashboard.js', [ 'react', 'react-dom', "wp-components" ], BPTB_VERSION, true );
				wp_set_script_translations( 'bptb-admin-dashboard', 'b-pricing-table', BPTB_DIR_PATH . 'languages' );
			}
		}
	}
	new BPTBAdminMenu();
}
