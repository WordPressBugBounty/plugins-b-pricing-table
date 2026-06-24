<?php
if ( !defined( 'ABSPATH' ) ) { exit; }

/**
 * License Activation Handler
 * Bridges the bPlugins dashboard Activation page to the Freemius SDK via AJAX.
 * Registers bpl_{product_id}_(get_license_status|activate_license|deactivate_license).
 */
if( !class_exists( 'BPTBLicenseActivation' ) ){
	class BPTBLicenseActivation {
		private $fs_callable;
		private $fs;

		function __construct( $fs_callable ) {
			$this->fs_callable = $fs_callable;

			$this->fs = call_user_func( $fs_callable );
			add_action( 'wp_ajax_bpl_'.$this->fs->get_id().'_activate_license', [$this, 'activateLicense'] );
			add_action( 'wp_ajax_bpl_'.$this->fs->get_id().'_get_license_status', [$this, 'getLicenseStatus'] );
			add_action( 'wp_ajax_bpl_'.$this->fs->get_id().'_deactivate_license', [$this, 'deactivateLicense'] );
		}

		/**
		 * Activate Freemius license via AJAX
		 */
		function activateLicense() {
			$this->validate_request( 'activate' );

			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is verified in validate_request() above.
			$license_key = isset( $_POST['license_key'] ) ? sanitize_text_field( wp_unslash( $_POST['license_key'] ) ) : '';

			if ( empty( $license_key ) ) {
				wp_send_json_error( [ 'message' => 'Please enter a license key.' ] );
			}

			try {
				$fs = call_user_func( $this->fs_callable );

				if ( !$fs->is_registered() ) {
					$result = $fs->opt_in(
						false, false, false,
						$license_key,
						false, false, false, false
					);

					if ( is_object( $result ) && isset( $result->error ) ) {
						wp_send_json_error( [ 'message' => $this->getErrorMessage( $result->error ) ] );
						return;
					}
				} else {
					$current_license = $fs->_get_license();

					if ( $current_license && $current_license->secret_key === $license_key && $fs->is_premium() ) {
						wp_send_json_error( [ 'message' => 'This license is already activated.' ] );
						return;
					}

					$result = $fs->opt_in(
						false, false, false,
						$license_key,
						false, false, false, false,
						array(),
						false
					);

					if ( is_object( $result ) && isset( $result->error ) ) {
						wp_send_json_error( [ 'message' => $this->getErrorMessage( $result->error ) ] );
						return;
					}

					$this->element_call( $fs, '_sync_license' );
					$this->element_call( $fs, '_get_license', [true] );
				}

				if ( $fs->is_premium() ) {
					$license = $fs->_get_license();

					if ( $license && $license->secret_key === $license_key ) {
						wp_send_json_success( [
							'message' => 'License activated successfully!',
							'license_key' => $license_key,
							'is_premium' => true
						] );
					} else {
						wp_send_json_error( [ 'message' => 'License verification failed. Please try again.' ] );
					}
				} else {
					wp_send_json_error( [ 'message' => 'Activation failed. Please check your license key and try again.' ] );
				}
			} catch ( \Exception $e ) {
				wp_send_json_error( [ 'message' => 'An error occurred during activation: ' . $e->getMessage() ] );
			}
		}

		/**
		 * Get current license status
		 */
		function getLicenseStatus() {
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
		 * Deactivate Freemius license via AJAX
		 */
		function deactivateLicense() {
			$this->validate_request( 'deactivate' );

			try {
				$fs = call_user_func( $this->fs_callable );

				if ( !$fs->is_registered() || !$fs->is_premium() ) {
					wp_send_json_error( [ 'message' => 'No active license to deactivate.' ] );
					return;
				}

				$license = $fs->_get_license();

				if ( !$license ) {
					wp_send_json_error( [ 'message' => 'License not found.' ] );
					return;
				}

				$reflector = new \ReflectionClass( $fs );
				$method = $reflector->getMethod( 'get_api_site_scope' );
				$method->setAccessible( true );
				$api = $method->invoke( $fs );

				if ( ! is_object( $api ) ) {
					wp_send_json_error( [ 'message' => 'Failed to initialize API connection.' ] );
					return;
				}

				$result = $api->call( "/licenses/{$license->id}.json", 'delete' );

				if ( is_object( $result ) && isset( $result->error ) ) {
					wp_send_json_error( [
						'message' => 'Failed to deactivate license: ' . ( isset( $result->error->message ) ? $result->error->message : 'Unknown error' )
					] );
					return;
				}

				$this->element_call( $fs, '_sync_license' );
				$this->element_call( $fs, '_get_license', [true] );

				wp_send_json_success( [ 'message' => 'License deactivated successfully!' ] );
			} catch ( \Throwable $e ) {
				wp_send_json_error( [ 'message' => 'An error occurred during deactivation: ' . $e->getMessage() ] );
			}
		}

		/**
		 * Helper to call protected/private SDK methods.
		 */
		private function element_call( $object, $method_name, $parameters = [] ) {
			$reflection = new \ReflectionClass( get_class( $object ) );
			$method = $reflection->getMethod( $method_name );
			$method->setAccessible( true );
			return $method->invokeArgs( $object, $parameters );
		}

		/**
		 * Validate request (nonce, permissions, SDK).
		 */
		private function validate_request( $action = '' ) {
			$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

			if ( !wp_verify_nonce( $nonce, 'bPlLicenseActivation' ) ) {
				wp_send_json_error( [ 'message' => 'Invalid security token. Please refresh the page and try again.' ] );
			}

			if ( 'status' !== $action && !current_user_can( 'manage_options' ) ) {
				$action_text = ( 'activate' === $action ) ? 'activate' : 'deactivate';
				wp_send_json_error( [ 'message' => "You do not have permission to {$action_text} licenses." ] );
			}

			if ( !function_exists( $this->fs_callable ) ) {
				wp_send_json_error( [ 'message' => 'Freemius SDK is not available.' ] );
			}
		}

		/**
		 * Get user-friendly error message.
		 */
		private function getErrorMessage( $error ) {
			$messages = [
				'invalid_license_key' => 'Invalid license key. Please check and try again.',
				'expired' => 'This license has expired. Please renew your license.',
				'disabled' => 'This license has been disabled.',
				'no_activations_left' => 'No activations left for this license. Please upgrade or purchase a new license.',
				'license_not_found' => 'License not found. Please check your license key.',
			];

			if ( is_object( $error ) && isset( $error->code ) ) {
				$error_code = $error->code;
				return isset( $messages[$error_code] ) ? $messages[$error_code] : 'Activation failed: ' . $error->message;
			}

			return 'Activation failed. Please check your license key and try again.';
		}
	}
}

// Initialize only if Freemius is available.
if ( function_exists( 'bptb_fs' ) ) {
	new BPTBLicenseActivation( 'bptb_fs' );
}
