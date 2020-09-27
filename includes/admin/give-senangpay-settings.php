<?php

/**
 * Class Give_Mpay_Settings
 *
 * @since 1.0.0
 */
class Give_Mpay_Settings
{

    /**
     * @access private
     * @var Give_Mpay_Settings $instance
     */
    private static $instance;

    /**
     * @access private
     * @var string $section_id
     */
    private $section_id;

    /**
     * @access private
     *
     * @var string $section_label
     */
    private $section_label;

    /**
     * Give_Mpay_Settings constructor.
     */
    private function __construct()
    {

    }

    /**
     * get class object.
     *
     * @return Give_Mpay_Settings
     */
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

        $this->section_id = 'mpay';
        $this->section_label = __('Mpay', 'give-mpay');

        if (is_admin()) {
            // Add settings.
            add_filter('give_get_settings_gateways', array($this, 'add_settings'), 99);
            add_filter('give_get_sections_gateways', array($this, 'add_sections'), 99);
        }
    }

    /**
     * Add setting section.
     *
     * @param array $sections Array of section.
     *
     * @return array
     */
    public function add_sections($sections)
    {
        $sections[$this->section_id] = $this->section_label;

        return $sections;
    }

    /**
     * Add plugin settings.
     *
     * @param array $settings Array of setting fields.
     *
     * @return array
     */
    public function add_settings($settings)
    {
        $current_section = give_get_current_setting_section();

        if ($current_section != 'mpay') {
            return $settings;
        }

        $give_mpay_settings = array(
            array(
                'name' => __('Mpay Settings', 'give-mpay'),
                'id' => 'give_title_gateway_mpay',
                'type' => 'title',
            ),
            array(
                'name' => __('Merchant ID', 'give-mpay'),
                'desc' => __('Enter your Merchant ID.', 'give-mpay'),
                'id' => 'mpay_merchant_id',
                'type' => 'text',
                'row_classes' => 'give-mpay-key',
            ),
            array(
                'name' => __('Secret Key', 'give-mpay'),
                'desc' => __('Enter your API Secret Key, found in your Mpay Account Settings.', 'give-mpay'),
                'id' => 'mpay_api_key',
                'type' => 'text',
                'row_classes' => 'give-mpay-key',
            ),
            array(
                'name' => __('Bill Description', 'give-mpay'),
                'desc' => __('Enter description to be included in the bill.', 'give-mpay'),
                'id' => 'mpay_description',
                'type' => 'text',
                'row_classes' => 'give-mpay-key',
            ),
            array(
                'name' => __('Billing Fields', 'give-mpay'),
                'desc' => __('This option will enable the billing details section for Mpay which requires the donor\'s address to complete the donation. These fields are not required by Mpay to process the transaction, but you may have the need to collect the data.', 'give-mpay'),
                'id' => 'mpay_collect_billing',
                'type' => 'radio_inline',
                'default' => 'disabled',
                'options' => array(
                    'enabled' => __('Enabled', 'give-mpay'),
                    'disabled' => __('Disabled', 'give-mpay'),
                ),
            ),
            array(
                'type' => 'sectionend',
                'id' => 'give_title_gateway_mpay',
            ),
        );

        return array_merge($settings, $give_mpay_settings);
    }
}

Give_Mpay_Settings::get_instance()->setup_hooks();
