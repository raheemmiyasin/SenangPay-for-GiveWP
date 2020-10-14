<?php

/**
 * GiveWp Senangpay Gateway Activation
 *
 * @package     Senangpay for GiveWP
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Plugins row action links
 *
 * @since 1.0.0
 *
 * @param array $actions An array of plugin action links.
 *
 * @return array An array of updated action links.
 */
function give_senangpay_plugin_action_links($actions)
{
    $new_actions = array(
        'settings' => sprintf(
            '<a href="%1$s">%2$s</a>', admin_url('edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=senangpay'), esc_html__('Settings', 'give-senangpay')
        ),
    );
    return array_merge($new_actions, $actions);
}
add_filter('plugin_action_links_' . GIVE_MPAY_BASENAME, 'give_senangpay_plugin_action_links');
