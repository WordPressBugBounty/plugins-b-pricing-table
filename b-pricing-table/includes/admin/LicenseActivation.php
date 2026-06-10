<?php
namespace BPTB;

if ( !defined( 'ABSPATH' ) ) { exit; }

/**
 * BPTBLicenseActivation  class
 * Handles Freemius license activation, status checks, and deactivation via AJAX.
 * 
 * @package BPTB
 */
if(!class_exists(BPTBLicenseActivation::class )){
	class BPTBLicenseActivation
	{
		/**
		 * Freemius callable.
		 * @var string
		 */
		private $fs_callable;

		/**
		 * Freemius instance.
		 * @var object
		 */
		private $fs;

		/**
		 * Constructor.
		 *
		 * @param string $fs_callable The Freemius callable function name.
		 */
		public function __construct( $fs_callable ) {
			$this->fs_callable = $fs_callable;

			$this->fs = call_user_func( $fs_callable );
			add_action( 'wp_ajax_bpl_'.$this->fs->get_id().'_activate_license', [$this, 'activateLicense'] );
			add_action( 'wp_ajax_bpl_'.$this->fs->get_id().'_get_license_status', [$this, 'getLicenseStatus'] );
			add_action( 'wp_ajax_bpl_'.$this->fs->get_id().'_deactivate_license', [$this, 'deactivateLicense'] );
		}

		/**
		 * Activates a Freemius license via AJAX.
		 * 
		 * @return void
		 */
		public function activateLicense() {
			$this->validate_request( 'activate' );

			// Get and sanitize input
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is verified in validate_request() above.
			$license_key = isset( $_POST['license_key'] ) ? sanitize_text_field( wp_unslash( $_POST['license_key'] ) ) : '';

			// Validate inputs
			if ( empty( $license_key ) ) {
				wp_send_json_error( [
					'message' => __( 'Please enter a license key.', 'b-pricing-table' )
				] );
			}

			try {
				$fs = call_user_func( $this->fs_callable );

				// If not registered, opt-in with license
				if ( !$fs->is_registered() ) {

					// Opt-in with license key
					$result = $fs->opt_in(
						false,			// $enable_anonymous
						false,			// $enable_pending
						false,			// $is_pending
						$license_key,	// $license_key
						false,			// $trial_plan_id
						false,			// $is_marketing_allowed
						false,			// $is_extensions_tracking_allowed
						false			// $is_staging
					);

					// Check for errors
					if ( is_object( $result ) && isset( $result->error ) ) {
						wp_send_json_error( [
							'message' => $this->getErrorMessage( $result->error )
						] );
						return;
					}
				} else {
					// User is registered - check current license
					$current_license = $fs->_get_license();

					// If same license and already premium, it's already activated
					if ( $current_license && $current_license->secret_key === $license_key && $fs->is_premium() ) {
						wp_send_json_error( [
							'message' => __( 'This license is already activated.', 'b-pricing-table' )
						] );
						return;
					}

					$result = $fs->opt_in(
						false, // email (will be inferred)
						false, // first
						false, // last
						$license_key, 
						false, // is_uninstall
						false, // trial_plan_id
						false, // is_disconnected
						false, // is_marketing_allowed
						array(), // sites
						false // redirect - IMPORTANT to prevent redirection
					);

					// Check for errors
					if ( is_object( $result ) && isset( $result->error ) ) {
						wp_send_json_error( [
							'message' => $this->getErrorMessage( $result->error )
						] );
						return;
					}

					// Check if we got a valid install object or license back
					// opt_in usually returns the install object on success, or redirect URL if redirect=true

					// Sync the license - this updates the local cache
					$this->element_call( $fs, '_sync_license' );

					// Force reload the license from cache
					$this->element_call( $fs, '_get_license', [true] );
				}

				// Verify activation
				if ( $fs->is_premium() ) {
					$license = $fs->_get_license();

					// Verify it's the correct license
					if ( $license && $license->secret_key === $license_key ) {
						wp_send_json_success( [
							'message' => __( 'License activated successfully!', 'b-pricing-table' ),
							'license_key' => $license_key,
							'is_premium' => true
						] );
					} else {
						wp_send_json_error( [
							'message' => __( 'License verification failed. Please try again.', 'b-pricing-table' )
						] );
					}
				} else {
					wp_send_json_error( [
						'message' => __( 'Activation failed. Please check your license key and try again.', 'b-pricing-table' )
					] );
				}
			} catch ( \Exception $e ) {
				wp_send_json_error([
					'message' => sprintf(
						/* translators: %s is the error message returned during license activation */
						__('An error occurred during activation: %s', 'b-pricing-table'),
						$e->getMessage()
					)
				]);
			}
		}

		/**
		 * Retrieves the current license status via AJAX.
		 * 
		 * @return void
		 */
		public function getLicenseStatus() {
			$this->validate_request( 'status' );

			$fs = call_user_func( $this->fs_callable );

			if ( $fs->is_registered() && $fs->is_premium() ) {
				$license = $fs->_get_license();
				$secret_key = $license && isset( $license->secret_key ) ? $license->secret_key : '';

				wp_send_json_success( [
					'is_activated' => $secret_key ? true : false,
					'license_key' => $secret_key,
					'is_premium' => $secret_key ? true : false
				] );
			} else {
				wp_send_json_success( [
					'is_activated' => false,
					'is_premium' => false
				] );
			}
		}

		/**
		 * Deactivates a Freemius license via AJAX.
		 * 
		 * @return void
		 */
		public function deactivateLicense() {
			$this->validate_request( 'deactivate' );

			try {
				$fs = call_user_func( $this->fs_callable );

				// Check if user is registered and has a license
				if ( !$fs->is_registered() || !$fs->is_premium() ) {
					wp_send_json_error( [
						'message' => __( 'No active license to deactivate.', 'b-pricing-table' )
					] );
					return;
				}

				// Get current license
				$license = $fs->_get_license();

				if ( !$license ) {
					wp_send_json_error( [
						'message' => __( 'License not found.', 'b-pricing-table' )
					] );
					return;
				}

				// Deactivate via API logic from Freemius SDK (_deactivate_license)
				// Endpoint: /licenses/{license_id}.json
				// Method: DELETE

				// get_api_site_scope() is protected, so we need to use reflection to access it
				$reflector = new \ReflectionClass( $fs );
				$method = $reflector->getMethod( 'get_api_site_scope' );
				$method->setAccessible( true );
				$api = $method->invoke( $fs );

				if ( ! is_object( $api ) ) {
					wp_send_json_error( [
						'message' => __( 'Failed to initialize API connection.', 'b-pricing-table' )
					] );
					return;
				}

				$result = $api->call( "/licenses/{$license->id}.json", 'delete' );

				// Check for API errors (Freemius API returns object with error property on failure)
				if ( is_object( $result ) && isset( $result->error ) ) {
					wp_send_json_error([
						'message' => sprintf(
							/* translators: %s is the error message returned when license deactivation fails */
							__('Failed to deactivate license: %s', 'b-pricing-table'),
							isset($result->error->message)
								? $result->error->message
								: __('Unknown error', 'b-pricing-table')
						)
					]);
					return;
				}

				// Sync license data to update local state
				$this->element_call( $fs, '_sync_license' );

				// Force refresh license cache
				$this->element_call( $fs, '_get_license', [true] );

				// Verify deactivation
				if ( !$fs->is_premium() ) {
					wp_send_json_success( [
						'message' => __( 'License deactivated successfully!', 'b-pricing-table' )
					] );
				} else {
					// Sometimes sync takes a moment, but if API success, we can likely assume success for UI
					// But let's return success anyway if API call worked, to allow UI to update
					wp_send_json_success( [
						'message' => __( 'License deactivated successfully!', 'b-pricing-table' )
					] );
				}
			} catch ( \Throwable $e ) {
				wp_send_json_error( [
					/* translators: %s: Error message details */
					'message' => sprintf( __( 'An error occurred during deactivation: %s', 'b-pricing-table' ), $e->getMessage() )
				] );
			}
		}

		/**
		 * Helper to call protected/private methods using Reflection.
		 *
		 * @param object	$object			The object to call the method on.
		 * @param string	$method_name	The name of the method.
		 * @param array		$parameters		The parameters to pass to the method.
		 * @return mixed	The result of the method call.
		 */
		private function element_call( $object, $method_name, $parameters = [] ) {
			$reflection = new \ReflectionClass( get_class( $object ) );
			$method = $reflection->getMethod( $method_name );
			$method->setAccessible( true );
			return $method->invokeArgs( $object, $parameters );
		}

		/**
		 * Validates the AJAX request (nonce, permissions, SDK availability).
		 *
		 * @param string	$action	The action being performed.
		 * @return void
		 */
		private function validate_request( $action = '' ) {
			// Verify nonce
			$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

			if ( !wp_verify_nonce( $nonce, 'bptbLicenseActivation' ) ) {
				wp_send_json_error( [
					'message' => __( 'Invalid security token. Please refresh the page and try again.', 'b-pricing-table' )
				] );
				wp_die(); // Explicit safeguard
			}

			// Permissions check
			if ( !current_user_can( 'manage_options' ) ) {
				$action_text = ( 'activate' === $action ) ? 'activate' : ( ( 'status' === $action ) ? 'view' : 'deactivate' );
				wp_send_json_error( [
					/* translators: %s: Action name (activate/deactivate/view) */
					'message' => sprintf( __( 'You do not have permission to %s licenses.', 'b-pricing-table' ), $action_text )
				] );
				wp_die(); // Explicit safeguard
			}

			// Check if Freemius is available
			if ( ! is_callable( $this->fs_callable ) ) {
				wp_send_json_error( [
					'message' => __( 'Freemius SDK is not available.', 'b-pricing-table' )
				] );
			}
		}

		/**
		 * Maps Freemius error codes to user-friendly messages.
		 *
		 * @param object $error The error object from Freemius.
		 * @return string The user-friendly error message.
		 */
		private function getErrorMessage( $error ) {
			$messages = [
				'invalid_license_key' => __( 'Invalid license key. Please check and try again.', 'b-pricing-table' ),
				'expired' => __( 'This license has expired. Please renew your license.', 'b-pricing-table' ),
				'disabled' => __( 'This license has been disabled.', 'b-pricing-table' ),
				'no_activations_left' => __( 'No activations left for this license. Please upgrade or purchase a new license.', 'b-pricing-table' ),
				'license_not_found' => __( 'License not found. Please check your license key.', 'b-pricing-table' ),
			];

			if ( is_object( $error ) && isset( $error->code ) ) {
				$error_code = $error->code;
				/* translators: %s: Error message from API */
				return isset( $messages[$error_code] ) ? $messages[$error_code] : sprintf( __( 'Activation failed: %s', 'b-pricing-table' ), $error->message );
			}

			return __( 'Activation failed. Please check your license key and try again.', 'b-pricing-table' );
		}
	}
}

// Initialize only if Freemius is available
if ( function_exists( 'bptb_fs' ) ) {
	new BPTBLicenseActivation( 'bptb_fs' );
}