<?php

namespace AllInOneSeeder\App\Seeders\FluentCart;

use AllInOneSeeder\App\Seeders\AbstractSeeder;

/**
 * Seeds fct_order_operations. Inserts 1–2 operation records per
 * completed or processing order to simulate a realistic audit trail.
 */
class OrderOperationSeeder extends AbstractSeeder
{
    public function __construct()
    {
        parent::__construct();
        $this->table = $this->db->prefix . 'fct_order_operations';
    }

    /**
     * $count is unused — operations are derived from eligible orders.
     */
    public function seed(int $count): int
    {
        $this->inserted = 0;

        $adminId = $this->adminUserId();
        $orders  = $this->fetchEligibleOrders();

        if (empty($orders)) {
            return 0;
        }

        $operationPool = [
            'status_changed'    => 'Order status changed to %s.',
            'payment_confirmed' => 'Payment confirmed via %s.',
            'note_added'        => 'A note was added to this order.',
        ];
        $allOpTypes = array_keys($operationPool);

        $cols = ['order_id', 'operation_type', 'description', 'created_by', 'created_at'];
        $rows = [];

        foreach ($orders as $order) {
            $opCount      = rand(1, 2);
            $selectedOps  = $this->randomSample($allOpTypes, min($opCount, count($allOpTypes)));
            $orderCreated = $order->created_at;

            foreach ($selectedOps as $opType) {
                if ($opType === 'status_changed') {
                    $description = sprintf($operationPool[$opType], $order->status);
                } elseif ($opType === 'payment_confirmed') {
                    $description = sprintf($operationPool[$opType], $order->payment_method_title);
                } else {
                    $description = $operationPool[$opType];
                }

                $rows[] = [
                    'order_id'       => (int) $order->id,
                    'operation_type' => $opType,
                    'description'    => $description,
                    'created_by'     => $adminId,
                    // Operation happens some time after order creation
                    'created_at'     => $this->randDate($orderCreated, 'now'),
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

    private function fetchEligibleOrders(): array
    {
        $table = $this->db->prefix . 'fct_orders';

        return $this->db->get_results(
            "SELECT id, status, payment_method_title, created_at
             FROM `{$table}`
             WHERE status IN ('completed', 'processing')
             ORDER BY id ASC"
        ) ?: [];
    }
}
