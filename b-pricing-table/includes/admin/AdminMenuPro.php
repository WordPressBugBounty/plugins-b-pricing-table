<?php
if ( !defined( 'ABSPATH' ) ) { exit; }

if( !class_exists( 'BPTBAdminMenu' ) ) {
	class BPTBAdminMenu {
		function __construct() {
			add_action( 'admin_menu', [ $this, 'bptbAdminMenu' ] );
			add_action( 'admin_enqueue_scripts', [$this, 'bptbAdminEnqueueScripts'] );
		}

		function bptbAdminMenu() {
			$icon = " <svg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 34 34'>
            <path fill='white' transform='translate(-1094.000000, -2938.000000)' d='M1118.5,2938.0001 C1119.881,2938.0001 1121,2939.1191 1121,2940.5001 L1121,2969.5001 C1121,2970.8801 1119.881,2972.0001 1118.5,2972.0001 L1103.5,2972.0001 C1102.12,2972.0001 1101,2970.8801 1101,2969.5001 L1101,2940.5001 C1101,2939.1191 1102.12,2938.0001 1103.5,2938.0001 Z M1118.5,2939.0001 L1103.5,2939.0001 C1102.673,2939.0001 1102,2939.6731 1102,2940.5001 L1102,2969.5001 C1102,2970.3271 1102.673,2971.0001 1103.5,2971.0001 L1118.5,2971.0001 C1119.327,2971.0001 1120,2970.3271 1120,2969.5001 L1120,2940.5001 C1120,2939.6731 1119.327,2939.0001 1118.5,2939.0001 Z M1125.23077,2942 C1126.75754,2942 1128,2943.29615 1128,2944.88889 L1128,2965.11111 C1128,2966.70385 1126.75754,2968 1125.23077,2968 L1123,2968 L1123,2967.03704 L1125.23077,2967.03704 C1126.24892,2967.03704 1127.07692,2966.17326 1127.07692,2965.11111 L1127.07692,2944.88889 C1127.07692,2943.82674 1126.24892,2942.96296 1125.23077,2942.96296 L1123,2942.96296 L1123,2942 Z M1099,2942 L1099,2942.96296 L1096.76923,2942.96296 C1095.75108,2942.96296 1094.92308,2943.82674 1094.92308,2944.88889 L1094.92308,2965.11111 C1094.92308,2966.17326 1095.75108,2967.03704 1096.76923,2967.03704 L1099,2967.03704 L1099,2968 L1096.76923,2968 C1095.24246,2968 1094,2966.70385 1094,2965.11111 L1094,2944.88889 C1094,2943.29615 1095.24246,2942 1096.76923,2942 L1099,2942 Z M1116.6436,2962.4107 C1117.4726,2962.4107 1118.1436,2963.0817 1118.1436,2963.9107 C1118.1436,2964.7397 1117.4726,2965.4107 1116.6436,2965.4107 L1106.0266,2965.4107 C1105.1976,2965.4107 1104.5266,2964.7397 1104.5266,2963.9107 C1104.5266,2963.0817 1105.1976,2962.4107 1106.0266,2962.4107 Z M1115.6434,2955.9249 C1115.9204,2955.9249 1116.1434,2956.1489 1116.1434,2956.4249 C1116.1434,2956.7009 1115.9204,2956.9249 1115.6434,2956.9249 L1105.0264,2956.9249 C1104.7504,2956.9249 1104.5264,2956.7009 1104.5264,2956.4249 C1104.5264,2956.1489 1104.7504,2955.9249 1105.0264,2955.9249 Z M1110.3354,2952.2164 C1110.6114,2952.2164 1110.8354,2952.4404 1110.8354,2952.7164 C1110.8354,2952.9924 1110.6114,2953.2164 1110.3354,2953.2164 L1105.0264,2953.2164 C1104.7504,2953.2164 1104.5264,2952.9924 1104.5264,2952.7164 C1104.5264,2952.4404 1104.7504,2952.2164 1105.0264,2952.2164 Z M1114.7064,2948.2579 C1114.9824,2948.2579 1115.2064,2948.4819 1115.2064,2948.7579 C1115.2064,2949.0339 1114.9824,2949.2579 1114.7064,2949.2579 L1105.0264,2949.2579 C1104.7504,2949.2579 1104.5264,2949.0339 1104.5264,2948.7579 C1104.5264,2948.4819 1104.7504,2948.2579 1105.0264,2948.2579 Z M1116.8594,2944.2022 C1117.1354,2944.2022 1117.3594,2944.4262 1117.3594,2944.7022 C1117.3594,2944.9782 1117.1354,2945.2022 1116.8594,2945.2022 L1105.0264,2945.2022 C1104.7504,2945.2022 1104.5264,2944.9782 1104.5264,2944.7022 C1104.5264,2944.4262 1104.7504,2944.2022 1105.0264,2944.2022 Z' />
            </svg>";

				// 	add_submenu_page(
				// 		'tools.php', //parent slug
				// 		__('Dashboard - Pricing Table Block by bPlugins', 'b-pricing-table'), //page title
				// 		__('Pricing Table', 'b-pricing-table'), //menu title
				// 		'manage_options', //capability
				// 		'b-pricing-table', //menu slug
				// 		[$this, 'renderDashboardPage'] //function to display the page content
				// 	);

			add_menu_page(
				__('Pricing Table - bPlugins', 'b-pricing-table'),
				__('Pricing Table', 'b-pricing-table'),
				'manage_options',
				'b-pricing-table',
				[ $this, 'renderDashboardPage' ],
				'data:image/svg+xml;base64,' . base64_encode($icon),
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
					'adminUrl' => admin_url()
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
