<?php

namespace AllInOneSeeder\App\Seeders\FluentCart;

use AllInOneSeeder\App\Factories\FakeData;
use AllInOneSeeder\App\Seeders\AbstractSeeder;

class CustomerSeeder extends AbstractSeeder
{
    public function __construct()
    {
        parent::__construct();
        $this->table = $this->db->prefix . 'fct_customers';
    }

    public function seed(int $count): int
    {
        $this->inserted = 0;

        $cols = [
            'email', 'first_name', 'last_name',
            'status', 'notes', 'country', 'city', 'state', 'postcode',
            'uuid', 'purchase_count', 'ltv', 'aov',
            'created_at', 'updated_at',
        ];

        // Weighted country distribution matching real e-commerce traffic
        $countryWeights = [
            'US' => 30, 'GB' => 15, 'CA' => 10, 'AU' => 10,
            'DE' => 8,  'FR' => 8,  'NL' => 4,  'SE' => 3,
            'JP' => 3,  'BR' => 3,  'IN' => 3,  'SG' => 3,
        ];

        $rows = [];

        for ($i = 0; $i < $count; $i++) {
            $first     = FakeData::firstName();
            $last      = FakeData::lastName();
            $email     = FakeData::email(strtolower($first . '.' . $last));
            $createdAt = $this->randDate('-700 days', 'now');

            $rows[] = [
                'email'          => $email,
                'first_name'     => $first,
                'last_name'      => $last,
                'status'         => 'active',
                'notes'          => rand(1, 10) <= 3 ? FakeData::sentence() : '',
                'country'        => $this->weightedRandom($countryWeights),
                'city'           => FakeData::city(),
                'state'          => FakeData::state(),
                'postcode'       => FakeData::postalCode(),
                'uuid'           => wp_generate_uuid4(),
                'purchase_count' => 0,
                'ltv'            => 0,
                'aov'            => 0,
                'created_at'     => $createdAt,
                'updated_at'     => $createdAt,
            ];

            if (count($rows) === 200) {
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
}
