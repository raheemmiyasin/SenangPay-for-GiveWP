<?php

/**
 * 
 * 1. Init payment: process_payment function
 *    - create_payment function
 *    - get_senangpay function
 *    - redirect to senangpay-payment-pg page
 * 
 * 2. Verify payment: return_listener function
 *    - verify all the payment & order data
 *    - publish_payment function
 *    - redirect
 * 
 */


if (!defined('ABSPATH')) {
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

class Give_Senangpay_Gateway
{
    private static $instance;

    const QUERY_VAR = 'senangpay_givewp_return';
    const LISTENER_PASSPHRASE = 'senangpay_givewp_listener_passphrase';

    private function __construct()
    {
        add_action('init', array($this, 'return_listener'));
        add_action('give_gateway_senangpay', array($this, 'process_payment'));
        add_action('give_senangpay_cc_form', array($this, 'give_senangpay_cc_form'));
        add_filter('give_enabled_payment_gateways', array($this, 'give_filter_senangpay_gateway'), 10, 2);
        add_filter('give_payment_confirm_senangpay', array($this, 'give_senangpay_success_page_content'));
        add_filter('give_payment_page', array($this, 'give_senangpay_payment_page_content'));
    }

    public static function get_instance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public function give_filter_senangpay_gateway($gateway_list, $form_id)
    {
        if ((false === strpos($_SERVER['REQUEST_URI'], '/wp-admin/post-new.php?post_type=give_forms'))
            && $form_id
            && !give_is_setting_enabled(give_get_meta($form_id, 'senangpay_customize_senangpay_donations', true, 'global'), array('enabled', 'global'))
        ) {
            unset($gateway_list['senangpay']);
        }
        return $gateway_list;
    }

    private function create_payment($purchase_data)
    {

        $form_id = intval($purchase_data['post_data']['give-form-id']);
        $price_id = isset($purchase_data['post_data']['give-price-id']) ? $purchase_data['post_data']['give-price-id'] : '';

        // Collect payment data.
        $insert_payment_data = array(
            'price' => $purchase_data['price'],
            'give_form_title' => $purchase_data['post_data']['give-form-title'],
            'give_form_id' => $form_id,
            'give_price_id' => $price_id,
            'date' => $purchase_data['date'],
            'user_email' => $purchase_data['user_email'],
            'purchase_key' => $purchase_data['purchase_key'],
            'currency' => give_get_currency($form_id, $purchase_data),
            'user_info' => $purchase_data['user_info'],
            'status' => 'pending',
            'gateway' => 'senangpay',
        );

        /**
         * Filter the payment params.
         *
         * @since 3.0.2
         *
         * @param array $insert_payment_data
         */
        $insert_payment_data = apply_filters('give_create_payment', $insert_payment_data);

        // Record the pending payment.
        return give_insert_payment($insert_payment_data);
    }

    private function get_senangpay($purchase_data)
    {
        //ob_start();
        $form_id = intval($purchase_data['post_data']['give-form-id']);

        $custom_donation = give_get_meta($form_id, 'senangpay_customize_senangpay_donations', true, 'global');
        $status = give_is_setting_enabled($custom_donation, 'enabled');

        if ($status) {
            return array(
                'merchant_id' => give_get_meta($form_id, 'senangpay_merchant_id', true),
                'api_key' => give_get_meta($form_id, 'senangpay_api_key', true),
                'description' => give_get_meta($form_id, 'senangpay_description', true, true),
            );
        }
        return array(
            'merchant_id' => give_get_option('senangpay_merchant_id'),
            'api_key' => give_get_option('senangpay_api_key'),
            'description' => give_get_option('senangpay_description', true),
        );
    }

    public static function get_listener_url($payment_id)
    {
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

    public function process_payment($purchase_data)
    {
        // Validate nonce.
        give_validate_nonce($purchase_data['gateway_nonce'], 'give-gateway');


        // Check the current payment mode
        $url = 'https://app.senangpay.my/payment';
        $url2 = 'https://api.senangpay.my/recurring/payment/';
        if ( give_is_test_mode() ) {
            // Test mode
            $url = 'https://sandbox.senangpay.my/payment';
            $url2 = 'https://api.sandbox.senangpay.my/recurring/payment';
        }

        $payment_id = $this->create_payment($purchase_data);

        //get subscription data if recurring
        $subscription_id = ! empty( $get_data['subscription_id'] ) ? intval( $get_data['subscription_id'] ) : false;

        // Check payment.
        if (empty($payment_id)) {
            // Record the error.
            give_record_gateway_error(__('Payment Error', 'give-senangpay'), sprintf( /* translators: %s: payment data */
                __('Payment creation failed before sending donor to Senangpay. Payment data: %s', 'give-senangpay'), json_encode($purchase_data)), $payment_id);
            // Problems? Send back.
            give_send_back_to_checkout();
        }

        $senangpay_key = $this->get_senangpay($purchase_data);

        $name = $purchase_data['user_info']['first_name'] . ' ' . $purchase_data['user_info']['last_name'];
        $amt = $purchase_data['price'];
        $detail = 'This is the transaction of givewp with id of ' . $purchase_data['post_data']['give-form-id'] . ' at ' . $purchase_data['date'] . ' with amount due of RM ' . $amt . '.';
        $order_id = $payment_id; //using give id
        $hashed_string = md5($senangpay_key['api_key'] . $detail . $amt . $order_id);
		
		// Get the success url.
		// $return_url = add_query_arg( array(
		// 	'payment-confirmation' => 'senangpay',
		// 	'payment-id'           => $payment_id,
		// ), get_permalink( give_get_option( 'success_page' ) ) );
 
        $parameter = array(
            'actionUrl' => (!$subscription_id) ? $url . '/' . $senangpay_key['merchant_id'] : $url2 . '/' . $senangpay_key['merchant_id'],
            'recurring_id' => ($subscription_id) ? '160396611172' : false, //baiki balik nanti, letak ni dekat box senangpay settings dkt admin.
            'detail' => $detail,
            'amount' => $amt,
            'order_id' => $order_id, 
            'name' => $name,
            'email' => $purchase_data['user_email'],
            'phone' => '+601000000000', //dummy phone number
            'hashed_string' => $hashed_string,

            'merchant_id' => $senangpay_key['merchant_id'],
            'secretkey' => $senangpay_key['api_key'],

            'postURL' => self::get_listener_url($payment_id),
            //'param'	=> 'GiveWP|' . $payment_id,
        );

        $parameter = apply_filters('give_senangpay_bill_mandatory_param', $parameter, $purchase_data['post_data']);

        //send to payment page as params
        if(!$subscription_id){
            $payment_page = site_url() . "/senangpay-payment-pg";
        }else {
            $payment_page = site_url() . "/senangpay-payment-recurring-pg";
        }
        
		write_log('Senangpay in post data ' . print_r($parameter, true));
     
        $payment_url = add_query_arg( $parameter, $payment_page );
        $payment_page = '<script type="text/javascript">
            window.onload = function(){
                window.parent.location = "'.$payment_url.'";
              }
            </script>';
			
        echo $payment_page;

        exit;
    }

    public function give_senangpay_cc_form($form_id)
    {
        // ob_start();

        $post_senangpay_customize_option = give_get_meta($form_id, 'senangpay_customize_senangpay_donations', true, 'global');

        // Enable Default fields (billing info)
        $post_senangpay_cc_fields = give_get_meta($form_id, 'senangpay_collect_billing', true);
        $global_senangpay_cc_fields = give_get_option('senangpay_collect_billing');

        // Output Address fields if global option is on and user hasn't elected to customize this form's offline donation options
        if (
            (give_is_setting_enabled($post_senangpay_customize_option, 'global') && give_is_setting_enabled($global_senangpay_cc_fields))
            || (give_is_setting_enabled($post_senangpay_customize_option, 'enabled') && give_is_setting_enabled($post_senangpay_cc_fields))
        ) {
            give_default_cc_address_fields($form_id);
            return true;
        }

        return false;
        // echo ob_get_clean();
    }

    private function publish_payment($payment_id, $data)
    {
        if ('publish' !== get_post_status($payment_id)) {
            write_log('senangpay listener success:' . $data['responseDesc']);
			give_set_payment_transaction_id( $payment_id, $data['authCode'] );
            give_update_payment_status($payment_id, 'publish');
            give_insert_payment_note($payment_id, "Payment ID: {$data['invno']}. Authorization Code: {$data['authCode']}");
        }
    }

    public function return_listener()
    {
        // if (!isset($_GET[self::QUERY_VAR])) {
        //     return;
        // }

        $passphrase = get_option(self::LISTENER_PASSPHRASE, false);
        if (!$passphrase) {
            return;
        }

        // if ($_GET[self::QUERY_VAR] != $passphrase) {
        //     return;
        // }
        // if (!isset($_GET['payment-id'])) { //order_id
        //     status_header(403);
        //     exit;
        // }
        if (isset($_GET['status_id']) && isset($_GET['order_id']) && isset($_GET['transaction_id']) && isset($_GET['msg']) && isset($_GET['hash'])) {
            echo 'test-kejayaan. <br />';

            write_log('senangpay listener query'. print_r($_GET, true));

            $payment_id = preg_replace('/\D/', '', $_GET['order_id']);
            $form_id = give_get_payment_form_id($payment_id);

            $payment_data = give_get_payment_meta( $payment_id );
            write_log('senangpay payment meta'. print_r($payment_data, true));

            $custom_donation = give_get_meta($form_id, 'senangpay_customize_senangpay_donations', true, 'global');
            $status = give_is_setting_enabled($custom_donation, 'enabled');

            $payment_amount = give_donation_amount( $payment_id );

            if ($status) {
                $merchant_id = trim(give_get_meta($form_id, 'senangpay_merchant_id', true));
                $hash_key = trim(give_get_meta($form_id, 'senangpay_api_key', true));
            } else {
                $merchant_id = trim(give_get_option('senangpay_merchant_id'));
                $hash_key = trim(give_get_option('senangpay_api_key'));
            }

            // $senangpay_key = $this->get_senangpay(null);

            # verify that the data was not tempered, verify the hash
            $hashed_string = md5($hash_key . urldecode($_GET['status_id']) . urldecode($_GET['order_id']) . urldecode($_GET['transaction_id']) . urldecode($_GET['msg']));

            # if hash is the same then we know the data is valid
            if($hashed_string == urldecode($_GET['hash']))
            {
                $data = array(
                    "responseDesc" => urldecode($_GET['msg']),
                    "authCode" => urldecode($_GET['transaction_id'])
                );

                # this is a simple result page showing either the payment was successful or failed. In real life you will need to process the order made by the customer
                if(urldecode($_GET['status_id']) == '1'){
                    // echo 'Payment was successful with message: '.urldecode($_GET['msg']);
                    $this->publish_payment($payment_id, $data);
                    $return = add_query_arg(array(
                        'payment-confirmation' => 'senangpay',
                        'payment-id' => $payment_id,
                    ), get_permalink(give_get_option('success_page')));
                }
                else {
                    // echo 'Payment failed with message: '.urldecode($_GET['msg']);
                    write_log('senangpay failed:'. $data['responseCode'] . $data['responseDesc']);
                    give_record_gateway_error( __( 'Senangpay Error', 'give' ), sprintf(__( $data['responseDesc'], 'give' ), json_encode( $_REQUEST ) ), $payment_id );
                    give_set_payment_transaction_id( $payment_id, $data['authCode'] );
                    give_update_payment_status( $payment_id, 'failed' );
                    give_insert_payment_note( $payment_id, __( $data['responseCode'] . ':' . $data['responseDesc'], 'give' ) );
                    $failedUrl = give_get_failed_transaction_uri('?payment-id=' . $payment_id);
                    $failedUrl = str_replace("_wpnonce","_wponce",$failedUrl);
                    $return = $failedUrl;
                }
            }
            else
                echo 'Hashed value is not correct';

            wp_redirect($return);
            exit;

        }else {
            return;
        }
    }

    public function give_senangpay_success_page_content($content)
    {
        if ( ! isset( $_GET['payment-id'] ) && ! give_get_purchase_session() ) {
          return $content;
        }

        $payment_id = isset( $_GET['payment-id'] ) ? absint( $_GET['payment-id'] ) : false;

        if ( ! $payment_id ) {
            $session    = give_get_purchase_session();
            $payment_id = give_get_donation_id_by_key( $session['purchase_key'] );
        }
		
        $payment = get_post( $payment_id );
        write_log('Senangpay in success page ' . $payment->post_status);
        if ( $payment && 'pending' === $payment->post_status ) {

            // Payment is still pending so show processing indicator to fix the race condition.
            ob_start();

            give_get_template_part( 'payment', 'processing' );

            $content = ob_get_clean();

        }   

        return $content;
    }
}
Give_Senangpay_Gateway::get_instance();
