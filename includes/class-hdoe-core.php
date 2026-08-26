<?php

namespace htrxuan\hdoe;

if (!defined('ABSPATH')) {
    exit;
}

final class HDOE_Core
{

    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->includes();
        $this->init_hooks();
    }

    private function __clone()
    {
    }

    private function includes()
    {
        // class-hdoe-exporter.php extends \WC_CSV_Exporter, which WooCommerce only loads
        // on-demand (inside WC_Admin_Exporters, not on every request) -- requiring it here
        // unconditionally fatals in lighter bootstrap contexts like wp-cron.php where that
        // WooCommerce class was never loaded. It's required lazily instead, right before
        // use, in HDOE_Admin::handle_export().
        require_once HDOE_PLUGIN_DIR . 'includes/class-hdoe-admin.php';
    }

    private function init_hooks()
    {
        add_action('admin_notices', array($this, 'render_missing_woocommerce_notice'));

        if (!class_exists('WooCommerce')) {
            return;
        }

        if (is_admin()) {
            HDOE_Admin::get_instance();
        }
    }

    public function render_missing_woocommerce_notice()
    {
        $screen = get_current_screen();
        if (!$screen || 'plugins' !== $screen->id) {
            return;
        }

        if (!get_transient('hdoe_wc_missing_notice')) {
            return;
        }
        delete_transient('hdoe_wc_missing_notice');
        ?>
        <div class="notice notice-error is-dismissible">
            <p>
                <?php esc_html_e('HDWebmobile Order Export requires WooCommerce to be installed and active. The plugin has been deactivated.', 'hdwebmobile-order-export'); ?>
            </p>
        </div>
        <?php
    }
}
