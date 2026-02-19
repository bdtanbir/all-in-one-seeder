<?php

namespace AllInOneSeeder\App\Seeders\FluentCart;

use AllInOneSeeder\App\Factories\FakeData;
use AllInOneSeeder\App\Seeders\AbstractSeeder;

class CustomerAddressSeeder extends AbstractSeeder
{
    public function __construct()
    {
        parent::__construct();
        $this->table = $this->db->prefix . 'fct_customer_addresses';
    }

    public function seed(int $count): int
    {
        $this->inserted = 0;

        $customers = $this->fetchCustomers($count);

        if (empty($customers)) {
            return 0;
        }

        $cols = [
            'customer_id', 'type', 'first_name', 'last_name',
            'address', 'city', 'state', 'postcode', 'country',
            'is_default', 'created_at', 'updated_at',
        ];
        $rows = [];

        foreach ($customers as $customer) {
            $createdAt = $customer->created_at ?? $this->now();

            // Always insert one billing address as default
            $rows[] = [
                'customer_id' => (int) $customer->id,
                'type'        => 'billing',
                'first_name'  => $customer->first_name,
                'last_name'   => $customer->last_name,
                'address'     => FakeData::addressLine(),
                'city'        => $customer->city,
                'state'       => $customer->state,
                'postcode'    => $customer->postcode,
                'country'     => $customer->country,
                'is_default'  => 1,
                'created_at'  => $createdAt,
                'updated_at'  => $createdAt,
            ];

            // 50% chance of a saved shipping address
            if (rand(1, 2) === 1) {
                $rows[] = [
                    'customer_id' => (int) $customer->id,
                    'type'        => 'shipping',
                    'first_name'  => $customer->first_name,
                    'last_name'   => $customer->last_name,
                    'address'     => FakeData::addressLine(),
                    'city'        => $customer->city,
                    'state'       => $customer->state,
                    'postcode'    => $customer->postcode,
                    'country'     => $customer->country,
                    'is_default'  => 0,
                    'created_at'  => $createdAt,
                    'updated_at'  => $createdAt,
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

    private function fetchCustomers(int $limit = 0): array
    {
        $table = $this->db->prefix . 'fct_customers';
        $sql   = "SELECT id, first_name, last_name, city, state, postcode, country, created_at
             FROM `{$table}`
             ORDER BY id DESC";
        if ($limit > 0) {
            $sql .= $this->db->prepare(' LIMIT %d', $limit);
        }
        $customers = $this->db->get_results($sql) ?: [];

        if ($limit > 0) {
            $customers = array_reverse($customers);
        }

        return $customers;
    }
}
