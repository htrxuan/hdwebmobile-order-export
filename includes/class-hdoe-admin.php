<?php

namespace htrxuan\hdoe;

if (!defined('ABSPATH')) {
    exit;
}

class HDOE_Admin
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
        require_once HDOE_PLUGIN_DIR . 'includes/class-hdoe-hub.php';
        add_filter('hdwebmobile_hub_tabs', array($this, 'register_hub_tabs'));
        add_action('admin_post_hdoe_export_orders', array($this, 'handle_export'));
    }

    public function register_hub_tabs($tabs)
    {
        $tabs['order-export'] = array(
            'label'  => __('Order Export', 'hdwebmobile-order-export'),
            'order'  => 85,
            'render' => array($this, 'render_page'),
        );
        return $tabs;
    }

    public function render_page()
    {
        ?>
        <p><?php esc_html_e('Export orders to CSV, filtered by date range and status. Every query runs through WooCommerce\'s own order-query API and every exported cell is escaped using WooCommerce\'s own CSV exporter -- nothing here writes raw SQL or a raw CSV cell.', 'hdwebmobile-order-export'); ?></p>
        <form method="get" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="hdoe_export_orders" />
            <?php wp_nonce_field('hdoe_export_orders', 'hdoe_export_nonce'); ?>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><label for="hdoe_date_from"><?php esc_html_e('From date', 'hdwebmobile-order-export'); ?></label></th>
                        <td><input type="date" id="hdoe_date_from" name="date_from" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="hdoe_date_to"><?php esc_html_e('To date', 'hdwebmobile-order-export'); ?></label></th>
                        <td><input type="date" id="hdoe_date_to" name="date_to" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Order status', 'hdwebmobile-order-export'); ?></th>
                        <td>
                            <?php foreach (wc_get_order_statuses() as $key => $label) : ?>
                                <label style="display:block;">
                                    <input type="checkbox" name="status[]" value="<?php echo esc_attr($key); ?>" />
                                    <?php echo esc_html($label); ?>
                                </label>
                            <?php endforeach; ?>
                            <p class="description"><?php esc_html_e('Leave all unchecked to export every status.', 'hdwebmobile-order-export'); ?></p>
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php submit_button(__('Download CSV', 'hdwebmobile-order-export')); ?>
        </form>
        <?php
    }

    public function handle_export()
    {
        if (
            !isset($_GET['hdoe_export_nonce'])
            || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['hdoe_export_nonce'])), 'hdoe_export_orders')
        ) {
            wp_die(esc_html__('Invalid request.', 'hdwebmobile-order-export'));
        }

        if (!current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('You do not have permission to do this.', 'hdwebmobile-order-export'));
        }

        $date_from = $this->sanitize_date(isset($_GET['date_from']) ? sanitize_text_field(wp_unslash($_GET['date_from'])) : '');
        $date_to   = $this->sanitize_date(isset($_GET['date_to']) ? sanitize_text_field(wp_unslash($_GET['date_to'])) : '');

        $submitted_statuses = isset($_GET['status']) && is_array($_GET['status'])
            ? array_map('sanitize_text_field', wp_unslash($_GET['status']))
            : array();
        $statuses = array_values(array_intersect($submitted_statuses, array_keys(wc_get_order_statuses())));

        $this->ensure_exporter_class_loaded();
        $exporter = new HDOE_Exporter();
        $exporter->set_filters($date_from, $date_to, $statuses);
        $exporter->set_filename('orders-export-' . gmdate('Y-m-d') . '.csv');
        $exporter->export();
    }

    private function sanitize_date($raw)
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw) ? $raw : '';
    }

    /**
     * WooCommerce only loads WC_CSV_Exporter on-demand (inside WC_Admin_Exporters), not on
     * every request, so it may not exist yet even though WooCommerce itself is active --
     * same class of issue as WC_Email needing WC()->mailer() forced first. Requiring it
     * explicitly here guarantees the parent class exists before class-hdoe-exporter.php,
     * which extends it, is parsed.
     */
    private function ensure_exporter_class_loaded()
    {
        require_once WC_ABSPATH . 'includes/export/abstract-wc-csv-exporter.php';
        require_once HDOE_PLUGIN_DIR . 'includes/class-hdoe-exporter.php';
    }
}
