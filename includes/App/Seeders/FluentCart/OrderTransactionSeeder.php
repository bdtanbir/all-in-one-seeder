<?php

namespace AllInOneSeeder\App\Seeders\FluentCart;

use AllInOneSeeder\App\Factories\FakeData;
use AllInOneSeeder\App\Seeders\AbstractSeeder;

/**
 * Seeds fct_order_transactions. One transaction row per order,
 * mirroring the order's payment status and amount.
 */
class OrderTransactionSeeder extends AbstractSeeder
{
    public function __construct()
    {
        parent::__construct();
        $this->table = $this->db->prefix . 'fct_order_transactions';
    }

    /**
     * $count is unused — one transaction per existing order.
     */
    public function seed(int $count): int
    {
        $this->inserted = 0;

        $orders = $this->fetchOrders();

        if (empty($orders)) {
            return 0;
        }

        $cols = [
            'order_id', 'payment_method', 'payment_method_title',
            'transaction_type', 'status', 'amount', 'currency',
            'transaction_id', 'created_at', 'updated_at',
        ];
        $rows = [];

        foreach ($orders as $order) {
            $isPaid   = $order->payment_status === 'paid';
            $isOnline = $order->payment_method !== 'offline_payment';

            $rows[] = [
                'order_id'             => (int) $order->id,
                'payment_method'       => $order->payment_method,
                'payment_method_title' => $order->payment_method_title,
                'transaction_type'     => 'payment',
                'status'               => $order->payment_status,
                'amount'               => (int) $order->total_paid,
                'currency'             => 'USD',
                // Only online paid orders have a gateway transaction ID
                'transaction_id'       => ($isPaid && $isOnline) ? FakeData::transactionId() : '',
                'created_at'           => $order->created_at,
                'updated_at'           => $order->created_at,
            ];

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

    private function fetchOrders(): array
    {
        $table = $this->db->prefix . 'fct_orders';

        return $this->db->get_results(
            "SELECT id, payment_method, payment_method_title,
                    payment_status, total_paid, created_at
             FROM `{$table}`
             ORDER BY id ASC"
        ) ?: [];
    }
}
