<?php

namespace AllInOneSeeder\App\Seeders\FluentCart;

use AllInOneSeeder\App\Factories\FakeData;
use AllInOneSeeder\App\Seeders\AbstractSeeder;

/**
 * Seeds fct_order_meta. Inserts 2–4 meta rows per order.
 */
class OrderMetaSeeder extends AbstractSeeder
{
    public function __construct()
    {
        parent::__construct();
        $this->table = $this->db->prefix . 'fct_order_meta';
    }

    public function seed(int $count): int
    {
        $this->inserted = 0;

        $orderIds = $this->fetchRecentOrderIds($count);

        if (empty($orderIds)) {
            return 0;
        }

        $allKeys = ['_source', '_checkout_page_id', '_customer_note', '_fulfillment_note'];

        // Use only the guaranteed core columns; fct_order_meta follows the
        // standard WP meta-table pattern (no created_at / updated_at columns).
        $cols = ['order_id', 'meta_key', 'meta_value'];
        $rows = [];

        foreach ($orderIds as $orderId) {
            $metaCount    = rand(2, 4);
            $selectedKeys = $this->randomSample($allKeys, min($metaCount, count($allKeys)));

            foreach ($selectedKeys as $key) {
                if ($key === '_source') {
                    $value = $this->randomElement(['web', 'api', 'import', 'mobile', 'pos']);
                } elseif ($key === '_checkout_page_id') {
                    $value = (string) rand(1, 100);
                } elseif ($key === '_customer_note') {
                    $value = rand(1, 10) <= 4 ? FakeData::sentence() : '';
                } else {
                    $value = rand(1, 10) <= 3 ? FakeData::sentence() : '';
                }

                $rows[] = [
                    'order_id'   => (int) $orderId,
                    'meta_key'   => $key,
                    'meta_value' => $value,
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

    /**
     * Returns latest order IDs when $limit > 0, otherwise all order IDs.
     *
     * @return int[]
     */
    private function fetchRecentOrderIds(int $limit = 0): array
    {
        $table = $this->db->prefix . 'fct_orders';
        $sql   = "SELECT id FROM `{$table}` ORDER BY id DESC";
        if ($limit > 0) {
            $sql .= $this->db->prepare(' LIMIT %d', $limit);
        }
        $ids = $this->db->get_col($sql) ?: [];

        if ($limit > 0) {
            $ids = array_reverse($ids);
        }

        return array_map('intval', $ids);
    }
}
