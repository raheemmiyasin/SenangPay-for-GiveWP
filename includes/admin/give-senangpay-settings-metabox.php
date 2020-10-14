<?php

class Give_Senangpay_Settings_Metabox
{
    private static $instance;

    private function __construct()
    {

    }

    public static function get_instance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Setup hooks.
     */
    public function setup_hooks()
    {
        if (is_admin()) {
            add_action('admin_enqueue_scripts', array($this, 'enqueue_js'));
            add_filter('give_forms_senangpay_metabox_fields', array($this, 'give_senangpay_add_settings'));
            add_filter('give_metabox_form_data_settings', array($this, 'add_senangpay_setting_tab'), 0, 1);
        }
    }

    public function add_senangpay_setting_tab($settings)
    {
        if (give_is_gateway_active('senangpay')) {
            $settings['senangpay_options'] = apply_filters('give_forms_senangpay_options', array(
                'id' => 'senangpay_options',
                'title' => __('Senangpay', 'give'),
                'icon-html' => '<span class="give-icon give-icon-purse"></span>',
                'fields' => apply_filters('give_forms_senangpay_metabox_fields', array()),
            ));
        }

        return $settings;
    }

    public function give_senangpay_add_settings($settings)
    {

        // Bailout: Do not show offline gateways setting in to metabox if its disabled globally.
        if (in_array('senangpay', (array) give_get_option('gateways'))) {
            return $settings;
        }

        $is_gateway_active = give_is_gateway_active('senangpay');

        //this gateway isn't active
        if (!$is_gateway_active) {
            //return settings and bounce
            return $settings;
        }

        //Fields
        $check_settings = array(

            array(
                'name' => __('Senangpay', 'give-senangpay'),
                'desc' => __('Do you want to customize the donation instructions for this form?', 'give-senangpay'),
                'id' => 'senangpay_customize_senangpay_donations',
                'type' => 'radio_inline',
                'default' => 'global',
                'options' => apply_filters('give_forms_content_options_select', array(
                    'global' => __('Global Option', 'give-senangpay'),
                    'enabled' => __('Customize', 'give-senangpay'),
                    'disabled' => __('Disable', 'give-senangpay'),
                )
                ),
            ),
            array(
                'name' => __('Merchant ID', 'give-senangpay'),
                'desc' => __('Enter your Merchant ID.', 'give-senangpay'),
                'id' => 'senangpay_merchant_id',
                'type' => 'text',
                'row_classes' => 'give-senangpay-key',
            ),
            array(
                'name' => __('API Secret Key', 'give-senangpay'),
                'desc' => __('Enter your API Secret Key, found in your Senangpay Account Settings.', 'give-senangpay'),
                'id' => 'senangpay_api_key',
                'type' => 'text',
                'row_classes' => 'give-senangpay-key',
            ),
            array(
                'name' => __('Bill Description', 'give-senangpay'),
                'desc' => __('Enter description to be included in the bill.', 'give-senangpay'),
                'id' => 'senangpay_description',
                'type' => 'text',
                'row_classes' => 'give-senangpay-key',
            ),
            array(
                'name' => __('Billing Fields', 'give-senangpay'),
                'desc' => __('This option will enable the billing details section for Senangpay which requires the donor\'s address to complete the donation. These fields are not required by Senangpay to process the transaction, but you may have the need to collect the data.', 'give-senangpay'),
                'id' => 'senangpay_collect_billing',
                'row_classes' => 'give-subfield give-hidden',
                'type' => 'radio_inline',
                'default' => 'disabled',
                'options' => array(
                    'enabled' => __('Enabled', 'give-senangpay'),
                    'disabled' => __('Disabled', 'give-senangpay'),
                ),
            ),
        );

        return array_merge($settings, $check_settings);
    }

    public function enqueue_js($hook)
    {
        if ('post.php' === $hook || $hook === 'post-new.php') {
            wp_enqueue_script('give_senangpay_each_form', GIVE_MPAY_PLUGIN_URL . '/includes/js/meta-box.js');
        }
    }

}
Give_Senangpay_Settings_Metabox::get_instance()->setup_hooks();
