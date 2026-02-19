<?php

namespace AllInOneSeeder\App\Seeders\FluentCart;

use AllInOneSeeder\App\Factories\FakeData;
use AllInOneSeeder\App\Seeders\AbstractSeeder;

/**
 * Seeds fct_order_addresses. Every order gets a billing address;
 * physical orders also get a shipping address.
 */
class OrderAddressSeeder extends AbstractSeeder
{
    public function __construct()
    {
        parent::__construct();
        $this->table = $this->db->prefix . 'fct_order_addresses';
    }

    /**
     * $count is unused — addresses are derived from existing orders.
     */
    public function seed(int $count): int
    {
        $this->inserted = 0;

        $orders      = $this->fetchOrders();
        $customerMap = $this->fetchCustomerMap();

        if (empty($orders)) {
            return 0;
        }

        $cols = [
            'order_id', 'type', 'first_name', 'last_name',
            'email', 'phone', 'address', 'city', 'state',
            'postcode', 'country', 'created_at', 'updated_at',
        ];
        $rows = [];

        foreach ($orders as $order) {
            $customer  = $customerMap[(int) $order->customer_id] ?? null;

            $firstName = $customer ? $customer['first_name'] : FakeData::firstName();
            $lastName  = $customer ? $customer['last_name']  : FakeData::lastName();
            $email     = $customer ? $customer['email']      : FakeData::email();
            $country   = $customer ? $customer['country']    : FakeData::country();
            $city      = $customer ? $customer['city']       : FakeData::city();
            $state     = $customer ? $customer['state']      : FakeData::state();
            $postcode  = $customer ? $customer['postcode']   : FakeData::postalCode();

            // Billing address (always)
            $rows[] = [
                'order_id'   => (int) $order->id,
                'type'       => 'billing',
                'first_name' => $firstName,
                'last_name'  => $lastName,
                'email'      => $email,
                'phone'      => FakeData::phone(),
                'address'    => FakeData::addressLine(),
                'city'       => $city,
                'state'      => $state,
                'postcode'   => $postcode,
                'country'    => $country,
                'created_at' => $order->created_at,
                'updated_at' => $order->created_at,
            ];

            // Shipping address for physical orders
            if ($order->fulfillment_type === 'physical') {
                $rows[] = [
                    'order_id'   => (int) $order->id,
                    'type'       => 'shipping',
                    'first_name' => $firstName,
                    'last_name'  => $lastName,
                    'email'      => $email,
                    'phone'      => FakeData::phone(),
                    'address'    => FakeData::addressLine(),
                    'city'       => $city,
                    'state'      => $state,
                    'postcode'   => $postcode,
                    'country'    => $country,
                    'created_at' => $order->created_at,
                    'updated_at' => $order->created_at,
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

    private function fetchOrders(): array
    {
        $table = $this->db->prefix . 'fct_orders';

        return $this->db->get_results(
            "SELECT id, customer_id, fulfillment_type, created_at FROM `{$table}` ORDER BY id ASC"
        ) ?: [];
    }

    private function fetchCustomerMap(): array
    {
        $table   = $this->db->prefix . 'fct_customers';
        $results = $this->db->get_results(
            "SELECT id, first_name, last_name, email, country, city, state, postcode FROM `{$table}`"
        ) ?: [];

        $map = [];
        foreach ($results as $r) {
            $map[(int) $r->id] = [
                'first_name' => $r->first_name,
                'last_name'  => $r->last_name,
                'email'      => $r->email,
                'country'    => $r->country,
                'city'       => $r->city,
                'state'      => $r->state,
                'postcode'   => $r->postcode,
            ];
        }

        return $map;
    }
}
