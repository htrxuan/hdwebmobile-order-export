<?php

namespace htrxuan\hdoe;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Extends WooCommerce core's own WC_CSV_Exporter rather than writing a custom CSV writer --
 * escape_data() (inherited, unmodified) already implements the OWASP formula-injection
 * mitigation, and export() streams directly to the HTTP response and dies without ever
 * writing a file to disk. Orders are fetched exclusively through wc_get_orders(), which
 * parameterizes every argument internally; this class never builds or concatenates SQL.
 */
class HDOE_Exporter extends \WC_CSV_Exporter
{

    protected $export_type = 'hdoe_orders';

    private $date_from = '';
    private $date_to = '';
    private $statuses = array();

    public function set_filters($date_from, $date_to, $statuses)
    {
        $this->date_from = $date_from;
        $this->date_to   = $date_to;
        $this->statuses  = $statuses;
    }

    public function get_default_column_names()
    {
        return array(
            'order_id'       => __('Order ID', 'hdwebmobile-order-export'),
            'order_number'   => __('Order Number', 'hdwebmobile-order-export'),
            'date_created'   => __('Date', 'hdwebmobile-order-export'),
            'status'         => __('Status', 'hdwebmobile-order-export'),
            'customer_name'  => __('Customer Name', 'hdwebmobile-order-export'),
            'email'          => __('Email', 'hdwebmobile-order-export'),
            'phone'          => __('Phone', 'hdwebmobile-order-export'),
            'billing_address'  => __('Billing Address', 'hdwebmobile-order-export'),
            'shipping_address' => __('Shipping Address', 'hdwebmobile-order-export'),
            'payment_method' => __('Payment Method', 'hdwebmobile-order-export'),
            'items'          => __('Items', 'hdwebmobile-order-export'),
            'item_count'     => __('Item Count', 'hdwebmobile-order-export'),
            'subtotal'       => __('Subtotal', 'hdwebmobile-order-export'),
            'shipping_total' => __('Shipping', 'hdwebmobile-order-export'),
            'tax_total'      => __('Tax', 'hdwebmobile-order-export'),
            'discount_total' => __('Discount', 'hdwebmobile-order-export'),
            'order_total'    => __('Order Total', 'hdwebmobile-order-export'),
            'coupon_codes'   => __('Coupon(s)', 'hdwebmobile-order-export'),
        );
    }

    public function prepare_data_to_export()
    {
        $this->column_names = $this->get_default_column_names();

        $args = array(
            'limit'   => -1,
            'orderby' => 'date',
            'order'   => 'DESC',
        );

        if (!empty($this->statuses)) {
            $args['status'] = $this->statuses;
        }
        if ('' !== $this->date_from) {
            $args['date_after'] = $this->date_from;
        }
        if ('' !== $this->date_to) {
            $args['date_before'] = $this->date_to;
        }

        $orders = wc_get_orders($args);

        $this->row_data = array();
        foreach ($orders as $order) {
            $this->row_data[] = $this->generate_row_data($order);
        }
        $this->total_rows = count($this->row_data);
    }

    private function generate_row_data($order)
    {
        $statuses     = wc_get_order_statuses();
        $status_label = $statuses['wc-' . $order->get_status()] ?? $order->get_status();

        $items = array();
        foreach ($order->get_items() as $item) {
            $items[] = $item->get_name() . ' x ' . $item->get_quantity();
        }

        return array(
            'order_id'         => $order->get_id(),
            'order_number'     => $order->get_order_number(),
            'date_created'     => $order->get_date_created() ? $order->get_date_created()->date('Y-m-d H:i:s') : '',
            'status'           => $status_label,
            'customer_name'    => trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()),
            'email'            => $order->get_billing_email(),
            'phone'            => $order->get_billing_phone(),
            'billing_address'  => $this->format_address($order, 'billing'),
            'shipping_address' => $this->format_address($order, 'shipping'),
            'payment_method'   => $order->get_payment_method_title(),
            'items'            => implode('; ', $items),
            'item_count'       => $order->get_item_count(),
            'subtotal'         => $order->get_subtotal(),
            'shipping_total'   => $order->get_shipping_total(),
            'tax_total'        => $order->get_total_tax(),
            'discount_total'   => $order->get_discount_total(),
            'order_total'      => $order->get_total(),
            'coupon_codes'     => implode(', ', $order->get_coupon_codes()),
        );
    }

    private function format_address($order, $type)
    {
        $getter = 'get_' . $type . '_';
        $parts  = array(
            $order->{$getter . 'address_1'}(),
            $order->{$getter . 'address_2'}(),
            $order->{$getter . 'city'}(),
            $order->{$getter . 'state'}(),
            $order->{$getter . 'postcode'}(),
            $order->{$getter . 'country'}(),
        );

        return implode(', ', array_filter($parts, function ($part) {
            return '' !== $part;
        }));
    }
}
