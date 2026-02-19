<?php

namespace AllInOneSeeder\App\Seeders\FluentCart;

use AllInOneSeeder\App\Seeders\AbstractSeeder;

/**
 * Seeds fct_order_items. Inserts 1–4 line items per order, picking
 * random product variations and converting float prices to cents.
 */
class OrderItemSeeder extends AbstractSeeder
{
    public function __construct()
    {
        parent::__construct();
        $this->table = $this->db->prefix . 'fct_order_items';
    }

    /**
     * $count is unused — items are derived from existing orders.
     */
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
            'order_id', 'product_id', 'variation_id', 'item_name',
            'quantity', 'unit_price', 'item_price',
            'discount_type', 'discount_amount', 'tax_total',
            'fulfillment_type', 'item_status', 'type',
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
                $lineTotal = $unitPrice * $qty;
                $tax       = (int) round($lineTotal * 0.08);

                $type = $variation['fulfillment_type'] === 'physical' ? 'physical' : 'digital';

                $rows[] = [
                    'order_id'         => (int) $order->id,
                    'product_id'       => $productId,
                    'variation_id'     => (int) $variation['id'],
                    'item_name'        => $variation['item_name'],
                    'quantity'         => $qty,
                    'unit_price'       => $unitPrice,
                    'item_price'       => $lineTotal,
                    'discount_type'    => 'none',
                    'discount_amount'  => 0,
                    'tax_total'        => $tax,
                    'fulfillment_type' => $variation['fulfillment_type'],
                    'item_status'      => 'active',
                    'type'             => $type,
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
     * Returns post_id → [ [id, item_price, fulfillment_type, item_name], ... ]
     */
    private function fetchProductMap(): array
    {
        $postsTable = $this->db->posts;
        $varsTable  = $this->db->prefix . 'fct_product_variations';

        $products = $this->db->get_results(
            "SELECT ID, post_title FROM `{$postsTable}` WHERE post_type = 'fluent-products' ORDER BY ID ASC"
        ) ?: [];

        if (empty($products)) {
            return [];
        }

        $nameMap = [];
        foreach ($products as $p) {
            $nameMap[(int) $p->ID] = $p->post_title;
        }

        $variations = $this->db->get_results(
            "SELECT id, post_id, item_price, fulfillment_type FROM `{$varsTable}` ORDER BY post_id ASC"
        ) ?: [];

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
                'item_name'        => $nameMap[$postId] ?? 'Product',
            ];
        }

        return $map;
    }
}
