<?php

namespace AllInOneSeeder\App\Seeders\FluentCart;

use AllInOneSeeder\App\Factories\FakeData;
use AllInOneSeeder\App\Seeders\AbstractSeeder;

class CouponSeeder extends AbstractSeeder
{
    public function __construct()
    {
        parent::__construct();
        $this->table = $this->db->prefix . 'fct_coupons';
    }

    public function seed(int $count): int
    {
        $this->inserted = 0;
        $usedCodes      = [];

        $cols = [
            'title', 'code', 'priority', 'type', 'amount',
            'conditions', 'use_count', 'status', 'stackable',
            'show_on_checkout', 'notes', 'start_date', 'end_date',
            'created_at', 'updated_at',
        ];
        $rows = [];

        for ($i = 0; $i < $count; $i++) {
            // Ensure unique codes within this run
            $code    = FakeData::couponCode();
            $attempt = 0;
            while (in_array($code, $usedCodes, true) && $attempt < 30) {
                $code = FakeData::couponCode() . ($i + 1);
                $attempt++;
            }
            $usedCodes[] = $code;

            $type      = $this->weightedRandom(['percentage' => 60, 'fixed' => 40]);
            $amount    = $type === 'percentage' ? rand(5, 50) : rand(5, 100);
            $status    = $this->weightedRandom(['active' => 75, 'expired' => 25]);
            $startDate = $this->randDate('-1 year', 'now');
            $endDate   = $status === 'expired'
                ? $this->randDate($startDate, 'now')
                : $this->randDate('now', '+1 year');

            $conditions = json_encode([
                'min_purchase'         => rand(0, 100),
                'max_discount'         => $type === 'percentage' ? rand(20, 200) : null,
                'max_use_per_customer' => rand(1, 10),
            ]);

            // Derive a human-readable title from the code
            $title = ucwords(strtolower(str_replace(['-', '_', '0123456789'], ' ', $code)));

            $rows[] = [
                'title'            => trim($title),
                'code'             => $code,
                'priority'         => $i + 1,
                'type'             => $type,
                'amount'           => $amount,
                'conditions'       => $conditions,
                'use_count'        => rand(0, 500),
                'status'           => $status,
                'stackable'        => $this->weightedRandom(['no' => 80, 'yes' => 20]),
                'show_on_checkout' => $this->weightedRandom(['yes' => 60, 'no' => 40]),
                'notes'            => '',
                'start_date'       => $startDate,
                'end_date'         => $endDate,
                'created_at'       => $startDate,
                'updated_at'       => $startDate,
            ];
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
}
