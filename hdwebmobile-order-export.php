<?php

/**
 * Plugin Name: HDWebmobile Order Export
 * Plugin URI: https://hdwebmobile.com/plugins/hdwebmobile-order-export/
 * Description: Export WooCommerce orders to CSV, filtered by date range and status. Every query goes through wc_get_orders() (never raw SQL) and CSV output extends WooCommerce's own WC_CSV_Exporter, so formula-injection-safe escaping comes from WooCommerce core itself.
 * Version: 1.0.0
 * Author: htrxuan - Han Tran
 * Author URI: https://hdwebmobile.com/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: hdwebmobile-order-export
 * Domain Path: /languages
 * Requires Plugins: woocommerce
 * Requires PHP: 7.4
 * Requires at least: 6.9
 */

namespace htrxuan\hdoe;

if (!defined('ABSPATH')) {
    exit;
}

define('HDOE_VERSION', '1.0.0');
define('HDOE_PLUGIN_FILE', __FILE__);
define('HDOE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HDOE_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once HDOE_PLUGIN_DIR . 'includes/class-hdoe-activator.php';

register_activation_hook(__FILE__, array(HDOE_Activator::class, 'activate'));

add_action('before_woocommerce_init', function () {
    if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', HDOE_PLUGIN_FILE, true);
    }
});

add_action('plugins_loaded', function () {
    require_once HDOE_PLUGIN_DIR . 'includes/class-hdoe-core.php';
    HDOE_Core::get_instance();
});

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($links) {
    $donate_link = '<a href="https://paypal.me/htrxuan/20" target="_blank" rel="noopener noreferrer">' . esc_html__('Donate', 'hdwebmobile-order-export') . '</a>';
    array_unshift($links, $donate_link);
    return $links;
});
