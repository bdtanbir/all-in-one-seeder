<?php

namespace AllInOneSeeder\App\Seeders\FluentCart;

use AllInOneSeeder\App\Seeders\AbstractSeeder;

/**
 * Seeds fct_applied_coupons. Applies a coupon to ~40% of existing orders.
 */
class AppliedCouponSeeder extends AbstractSeeder
{
    public function __construct()
    {
        parent::__construct();
        $this->table = $this->db->prefix . 'fct_applied_coupons';
    }

    /**
     * $count is unused — derived as ~40% of existing orders.
     */
    public function seed(int $count): int
    {
        $this->inserted = 0;

        $orderMap = $this->fetchOrderMap();
        $coupons  = $this->fetchCoupons();

        if (empty($orderMap) || empty($coupons)) {
            return 0;
        }

        $orderIds     = array_keys($orderMap);
        $targetOrders = $this->randomSample($orderIds, (int) ceil(count($orderIds) * 0.4));

        $cols = [
            'order_id', 'coupon_id', 'coupon_code', 'coupon_title',
            'type', 'discount_amount', 'created_at',
        ];
        $rows = [];

        foreach ($targetOrders as $orderId) {
            $coupon   = $this->randomElement($coupons);
            $subtotal = $orderMap[$orderId]['subtotal'];
            $createdAt = $orderMap[$orderId]['created_at'];

            // Calculate discount in cents matching the order's currency scale
            if ($coupon['type'] === 'percentage') {
                $discountAmount = (int) round($subtotal * ($coupon['amount'] / 100));
            } else {
                // Fixed coupon amount is stored as a dollar value; convert to cents
                $discountAmount = (int) round($coupon['amount'] * 100);
            }

            $rows[] = [
                'order_id'        => (int) $orderId,
                'coupon_id'       => (int) $coupon['id'],
                'coupon_code'     => $coupon['code'],
                'coupon_title'    => $coupon['title'],
                'type'            => $coupon['type'],
                'discount_amount' => $discountAmount,
                'created_at'      => $createdAt,
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

    /** Returns order_id → ['subtotal' => int, 'created_at' => string] */
    private function fetchOrderMap(): array
    {
        $table   = $this->db->prefix . 'fct_orders';
        $results = $this->db->get_results(
            "SELECT id, subtotal, created_at FROM `{$table}` ORDER BY id ASC"
        ) ?: [];

        $map = [];
        foreach ($results as $r) {
            $map[(int) $r->id] = [
                'subtotal'   => (int) $r->subtotal,
                'created_at' => $r->created_at,
            ];
        }

        return $map;
    }

    private function fetchCoupons(): array
    {
        $table   = $this->db->prefix . 'fct_coupons';
        $results = $this->db->get_results(
            "SELECT id, code, title, type, amount FROM `{$table}`"
        ) ?: [];

        $coupons = [];
        foreach ($results as $r) {
            $coupons[] = [
                'id'     => (int) $r->id,
                'code'   => $r->code,
                'title'  => $r->title,
                'type'   => $r->type,
                'amount' => (float) $r->amount,
            ];
        }

        return $coupons;
    }
}
