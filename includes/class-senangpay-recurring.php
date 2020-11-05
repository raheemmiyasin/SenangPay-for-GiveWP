<?php
/**
 * Senangpay for Give | Recurring Support
 *
 */

// Bailout, if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

    /* Plugin Debugging */
    if (!function_exists('write_log')) {
        function write_log($log)  {
           if (is_array($log) || is_object($log)) {
              error_log(print_r($log, true));
           } else {
              error_log($log);
           }
        }
     }

class Senangpay_Recurring extends Give_Recurring_Gateway {

	const QUERY_VAR = 'senangpay_givewp_return';
    const LISTENER_PASSPHRASE = 'senangpay_givewp_listener_passphrase';

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
//session key
public function senangpay_get_session_key() {
	return apply_filters( 'givesenangpay_get_session_key', uniqid() );
}

//frequency
public function senangpay_recurring_get_frequency( $frequency, $period ) {

	// Weekly Frequency.
	$senangpay_frequency = 1;

	if (
		( 1 === $frequency && 'month' === $period ) ||
		( 4 === $frequency && 'week' === $period )
	) {
		$senangpay_frequency = 2;
	} elseif (
		( 3 === $frequency && 'month' === $period ) ||
		( 1 === $frequency && 'quarter' === $period )
	) {
		$senangpay_frequency = 3;
	} elseif ( 6 === $frequency && 'month' === $period ) {
		$senangpay_frequency = 4;
	}  elseif (
		( 1 === $frequency && 'year' === $period ) ||
		( 4 === $frequency && 'quarter' === $period ) ||
		( 12 === $frequency && 'month' === $period )
	) {
		// Yearly Frequency.
		$senangpay_frequency = 5;
	}

	return $senangpay_frequency;
}

public function get_listener_url($payment_id)
{
	write_log('get_listener_url');
	$passphrase = get_option(self::LISTENER_PASSPHRASE, false);
	if (!$passphrase) {
		$passphrase = md5(site_url() . time());
		update_option(self::LISTENER_PASSPHRASE, $passphrase);
	}

	$arg = array(
		self::QUERY_VAR => $passphrase,
		'payment-id' => $payment_id,
	);
	return add_query_arg($arg, site_url('/'));
}

	/**
	 * Creates subscription payment profiles and sets the IDs so they can be stored.
	 *
	 * @access      public
	 */
	public function create_payment_profiles() {

		write_log('create_payment_profiles');
		// Record Subscription.
		$this->record_signup();

		$redirect_to_url      = ! empty( $this->purchase_data['post_data']['give-current-url'] ) ? $this->purchase_data['post_data']['give-current-url'] : site_url();
		$session_key          = $this->senangpay_get_session_key();
		$donation_id          = ! empty( $this->payment_id ) ? intval( $this->payment_id ) : false;
		$subscription_details = give_recurring_get_subscription_by( 'payment', $donation_id );
		$subscription_id      = ! empty( $subscription_details->id ) ? intval( $subscription_details->id ) : false;

		// Update session key to database for reference.
		give_update_meta( $donation_id, 'give_senangpay_unique_session_key', $session_key );

		$merchant_id      = give_get_option('senangpay_merchant_id');
		$api_key       = give_get_option('senangpay_api_key');
		$amount             = give_maybe_sanitize_amount( give_get_meta( $donation_id, '_give_payment_total', true ) );
		$form_id            = give_get_payment_form_id( $donation_id );
		$form_name          = give_get_meta( $donation_id, '_give_payment_form_title', true );
		$currency           = give_get_currency( $form_id );
		$donation_details   = give_get_payment_by( 'id', $donation_id );
		$first_name         = $donation_details->first_name;
		$last_name          = $donation_details->last_name;
		$email              = $donation_details->email;

		    // Check the current payment mode
			$url = 'https://api.senangpay.my/recurring/payment/';
			if ( give_is_test_mode() ) {
				// Test mode
				$url = 'https://api.sandbox.senangpay.my/recurring/payment/';
			}

		
	    // Recurring donations.
        $first_payment_date   = date_i18n( 'dmY', strtotime( $subscription_details->created ) );
        $ongoing_payments     = apply_filters( 'senangpay_update_ongoing_payments_count', 99 );
        $number_of_payments   = $subscription_details->bill_times > 0 ? $subscription_details->bill_times : $ongoing_payments;
        $frequency            = $this->senangpay_recurring_get_frequency( $subscription_details->frequency, $subscription_details->period );

		// Generate source and signature.
		$recurring_id = give_get_option('senangpay_monthly_recurringid');

		$hashed_string = hash('sha256', $api_key . $recurring_id . $donation_id . $amount);

		$parameters = array(
            'actionUrl' => $url . $merchant_id,
            'recurring_id' => $recurring_id,
            'amount' => $amount,
            'order_id' => $donation_id, 
            'name' => $first_name . ' ' . $last_name,
            'email' => $email,
            'phone' => '+601000000000', //dummy phone number
            'hashed_string' => $hashed_string,

			'merchant_id' => $merchant_id,
			'secretkey' => $api_key,
            'postURL' => $this->get_listener_url($donation_id),
            //'param'	=> 'GiveWP|' . $payment_id,
        );

		        //send to payment page as params
				$payment_page = site_url() . "/senangpay-payment-pg";
        
				write_log('Senangpay recurring post data ' . print_r($parameters, true));
			 
				$payment_url = add_query_arg( $parameters, $payment_page );
				write_log('Senangpay recurring payment url ' . $payment_url);
			 
				
				$payment_page = '<script type="text/javascript">
					window.onload = function(){
						window.parent.location = "'.$payment_url.'";
					  }
					</script>';
					
				echo $payment_page;

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