<?php
/**
 * Senangpay for Give | Recurring Support
 *
 */

// Bailout, if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Senangpay_Recurring extends Give_Recurring_Gateway {

	/**
	 * Setup gateway ID and possibly load API libraries.
	 *
	 * @access      public
	 * @return      void
	 */
	public function init() {
		$this->id = 'senangpay';

		// Complete recurring donation via backend response by senangpay.
		$this->offsite = true;
	}

	/**
	 * Creates subscription payment profiles and sets the IDs so they can be stored.
	 *
	 * @access      public
	 */
	public function create_payment_profiles() {

		// Record Subscription.
		$this->record_signup();

		$redirect_to_url      = ! empty( $this->purchase_data['post_data']['give-current-url'] ) ? $this->purchase_data['post_data']['give-current-url'] : site_url();
		$session_key          = mggi_get_session_key();
		$donation_id          = ! empty( $this->payment_id ) ? intval( $this->payment_id ) : false;
		$subscription_details = give_recurring_get_subscription_by( 'payment', $donation_id );
		$subscription_id      = ! empty( $subscription_details->id ) ? intval( $subscription_details->id ) : false;

		// Update session key to database for reference.
		give_update_meta( $donation_id, '_mg_give_senangpay_unique_session_key', $session_key );

		// Redirect to show loading area to trigger redirectToCheckout client side.
		wp_safe_redirect(
			add_query_arg(
				array(
					'action'          => 'process_senangpay',
					'donation_id'     => $donation_id,
					'subscription_id' => $subscription_id,
					'session'         => $session_key,
				),
				$redirect_to_url
			)
		);

		give_die();
	}

	/**
	 * Gets interval length and interval unit for Authorize.net based on Give subscription period.
	 *
	 * @param  string $period
	 * @param  int    $frequency
	 *
	 * @access public
	 *
	 * @return string
	 */
	public static function get_interval( $period, $frequency ) {

		$interval = $period;

		switch ( $period ) {

			case 'quarter':
				$interval = 'month';
				break;
		}

		return $interval;
	}

	/**
	 * Gets interval length and interval unit for Authorize.net based on Give subscription period.
	 *
	 * @param  string $period
	 * @param  int    $frequency
	 *
	 * @access public
	 *
	 * @return string
	 */
	public static function get_interval_count( $period, $frequency ) {

		$interval_count = $frequency;

		switch ( $period ) {

			case 'quarter':
				$interval_count = 3 * $frequency;
				break;
		}

		return $interval_count;
	}

}

new Senangpay_Recurring();