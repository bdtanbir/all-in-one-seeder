<?php

namespace AllInOneSeeder\App\Seeders\FluentCart;

use AllInOneSeeder\App\Seeders\AbstractSeeder;

/**
 * Seeds fct_order_items using FluentCart's actual schema.
 * Inserts 1–4 line items per order from existing product variations.
 */
class OrderItemSeeder extends AbstractSeeder
{
    public function __construct()
    {
        parent::__construct();
        $this->table = $this->db->prefix . 'fct_order_items';
    }

    public function seed(int $count): int
    {
        $this->inserted = 0;

        $orders     = $this->fetchOrders($count);
        $productMap = $this->fetchProductMap();

        if (empty($orders) || empty($productMap)) {
            return 0;
        }

        $productIds = array_keys($productMap);

        $cols = [
            'order_id', 'post_id', 'object_id',
            'fulfillment_type', 'payment_type',
            'post_title', 'title', 'cart_index',
            'quantity', 'unit_price', 'cost', 'subtotal',
            'tax_amount', 'shipping_charge', 'discount_total',
            'line_total', 'refund_total', 'rate',
            'other_info', 'line_meta',
            'created_at', 'updated_at',
        ];
        $rows = [];

        foreach ($orders as $order) {
            // 1 item (50%), 2 (30%), 3 (15%), 4 (5%)
            $itemCount = (int) $this->weightedRandom([1 => 50, 2 => 30, 3 => 15, 4 => 5]);

            for ($j = 0; $j < $itemCount; $j++) {
                $productId = $this->randomElement($productIds);
                $variation = $this->randomElement($productMap[$productId]);
                $qty       = rand(1, 5);

                // Variation prices are stored as floats; order items use cents (integer)
                $unitPrice = (int) round($variation['item_price'] * 100);
                $subtotal  = $unitPrice * $qty;
                $discount  = 0;
                $tax       = (int) round($subtotal * 0.08);
                $lineTotal = $subtotal + $tax - $discount;

                $rows[] = [
                    'order_id'         => (int) $order->id,
                    'post_id'          => $productId,
                    'object_id'        => (int) $variation['id'],
                    'fulfillment_type' => $variation['fulfillment_type'],
                    'payment_type'     => $variation['payment_type'] ?: 'onetime',
                    'post_title'       => $variation['post_title'],
                    'title'            => $variation['variation_title'],
                    'cart_index'       => $j + 1,
                    'quantity'         => $qty,
                    'unit_price'       => $unitPrice,
                    'cost'             => 0,
                    'subtotal'         => $subtotal,
                    'tax_amount'       => $tax,
                    'shipping_charge'  => 0,
                    'discount_total'   => $discount,
                    'line_total'       => $lineTotal,
                    'refund_total'     => 0,
                    'rate'             => 1,
                    'other_info'       => '{}',
                    'line_meta'        => '{}',
                    'created_at'       => $order->created_at,
                    'updated_at'       => $order->created_at,
                ];
            }

            if (count($rows) >= 200) {
                $this->insertBatch($rows, $cols);
                $rows = [];
            }
        }

        if (!empty($rows)) {
            $this->insertBatch($rows, $cols);
        }

        return $this->inserted;
    }

    public function truncate(): void
    {
        $this->truncateTable($this->table);
    }

    private function fetchOrders(int $limit = 0): array
    {
        $table = $this->db->prefix . 'fct_orders';
        $sql   = "SELECT id, created_at FROM `{$table}` ORDER BY id DESC";
        if ($limit > 0) {
            $sql .= $this->db->prepare(' LIMIT %d', $limit);
        }
        $orders = $this->db->get_results($sql) ?: [];

        if ($limit > 0) {
            $orders = array_reverse($orders);
        }

        return $orders;
    }

    /**
     * Returns post_id => list of variation payloads.
     */
    private function fetchProductMap(): array
    {
        $postsTable = $this->db->posts;
        $varsTable  = $this->db->prefix . 'fct_product_variations';

        $variations = $this->db->get_results(
            "SELECT v.id, v.post_id, v.item_price, v.fulfillment_type, v.payment_type, v.variation_title, p.post_title
             FROM `{$varsTable}` v
             INNER JOIN `{$postsTable}` p ON p.ID = v.post_id
             WHERE p.post_type = 'fluent-products'
             ORDER BY v.post_id ASC, v.id ASC"
        ) ?: [];

        if (empty($variations)) {
            return [];
        }

        $map = [];
        foreach ($variations as $var) {
            $postId = (int) $var->post_id;
            if (!isset($map[$postId])) {
                $map[$postId] = [];
            }
            $map[$postId][] = [
                'id'               => (int) $var->id,
                'item_price'       => (float) $var->item_price,
                'fulfillment_type' => $var->fulfillment_type,
                'payment_type'     => $var->payment_type,
                'variation_title'  => $var->variation_title ?: 'Default',
                'post_title'       => $var->post_title ?: 'Product',
            ];
        }

        return $map;
    }
}
