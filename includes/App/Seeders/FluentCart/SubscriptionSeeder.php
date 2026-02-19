<?php

namespace AllInOneSeeder\App\Seeders\FluentCart;

use AllInOneSeeder\App\Seeders\AbstractSeeder;

/**
 * Seeds fct_subscriptions. Targets ~25% of paid orders and creates
 * one subscription per selected order.
 */
class SubscriptionSeeder extends AbstractSeeder
{
    public function __construct()
    {
        parent::__construct();
        $this->table = $this->db->prefix . 'fct_subscriptions';
    }

    /**
     * $count is unused — subscriptions are derived from paid orders.
     */
    public function seed(int $count): int
    {
        $this->inserted = 0;

        $paidOrders = $this->fetchPaidOrders();
        $products   = $this->fetchProductVariations();

        if (empty($paidOrders) || empty($products)) {
            return 0;
        }

        $targetCount  = max(1, (int) round(count($paidOrders) * 0.25));
        $targetOrders = $this->randomSample($paidOrders, $targetCount);

        $cols = [
            'uuid', 'customer_id', 'parent_order_id',
            'product_id', 'item_name', 'quantity', 'variation_id',
            'billing_interval', 'signup_fee',
            'initial_tax_total', 'recurring_amount',
            'recurring_tax_total', 'recurring_total',
            'bill_times', 'bill_count', 'status',
            'collection_method', 'next_billing_date',
            'trial_days', 'vendor_customer_id', 'vendor_plan_id',
            'vendor_subscription_id', 'original_plan', 'vendor_response',
            'current_payment_method', 'config',
            'created_at', 'updated_at',
        ];
        $rows = [];

        foreach ($targetOrders as $order) {
            $product  = $this->randomElement($products);
            $interval = $this->weightedRandom([
                'weekly'  => 10,
                'monthly' => 60,
                'yearly'  => 30,
            ]);

            $recurring = rand(500, 10000); // cents
            $tax       = (int) round($recurring * 0.08);
            $total     = $recurring + $tax;

            $status = $this->weightedRandom([
                'active'   => 60,
                'canceled' => 25,
                'expired'  => 15,
            ]);

            // Only active subscriptions have a future billing date
            $nextBilling = null;
            if ($status === 'active') {
                if ($interval === 'weekly') {
                    $nextBilling = date('Y-m-d H:i:s', strtotime('+7 days'));
                } elseif ($interval === 'yearly') {
                    $nextBilling = date('Y-m-d H:i:s', strtotime('+1 year'));
                } else {
                    $nextBilling = date('Y-m-d H:i:s', strtotime('+1 month'));
                }
            }

            $rows[] = [
                'uuid'                   => wp_generate_uuid4(),
                'customer_id'            => (int) $order->customer_id,
                'parent_order_id'        => (int) $order->id,
                'product_id'             => (int) $product['post_id'],
                'item_name'              => $product['item_name'],
                'quantity'               => 1,
                'variation_id'           => (int) $product['variation_id'],
                'billing_interval'       => $interval,
                'signup_fee'             => 0,
                'initial_tax_total'      => $tax,
                'recurring_amount'       => $recurring,
                'recurring_tax_total'    => $tax,
                'recurring_total'        => $total,
                'bill_times'             => 0,
                'bill_count'             => rand(1, 24),
                'status'                 => $status,
                'collection_method'      => 'automatic',
                'next_billing_date'      => $nextBilling,
                'trial_days'             => 0,
                'vendor_customer_id'     => '',
                'vendor_plan_id'         => '',
                'vendor_subscription_id' => '',
                'original_plan'          => '{}',
                'vendor_response'        => '',
                'current_payment_method' => $order->payment_method,
                'config'                 => '{}',
                'created_at'             => $order->created_at,
                'updated_at'             => $order->created_at,
            ];

            if (count($rows) >= 100) {
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

    private function fetchPaidOrders(): array
    {
        $table = $this->db->prefix . 'fct_orders';

        return $this->db->get_results(
            "SELECT id, customer_id, payment_method, created_at
             FROM `{$table}`
             WHERE payment_status = 'paid'
             ORDER BY id ASC"
        ) ?: [];
    }

    /**
     * Returns a flat list of [ variation_id, post_id, item_name ] maps
     * by joining fct_product_variations to wp_posts.
     */
    private function fetchProductVariations(): array
    {
        $postsTable = $this->db->posts;
        $varsTable  = $this->db->prefix . 'fct_product_variations';

        $results = $this->db->get_results(
            "SELECT v.id AS variation_id, v.post_id, p.post_title AS item_name
             FROM `{$varsTable}` v
             INNER JOIN `{$postsTable}` p ON p.ID = v.post_id
             ORDER BY v.id ASC"
        ) ?: [];

        $list = [];
        foreach ($results as $r) {
            $list[] = [
                'variation_id' => (int) $r->variation_id,
                'post_id'      => (int) $r->post_id,
                'item_name'    => $r->item_name,
            ];
        }

        return $list;
    }
}
