<?php

namespace AllInOneSeeder\App\Seeders\FluentCRM;

use AllInOneSeeder\App\Factories\FakeData;
use AllInOneSeeder\App\Seeders\AbstractSeeder;

class SubscriberSeeder extends AbstractSeeder
{
    public function __construct()
    {
        parent::__construct();
        $this->table = $this->db->prefix . 'fc_subscribers';
    }

    public function seed(int $count): int
    {
        $this->inserted = 0;

        $companyIds = $this->fetchIds($this->db->prefix . 'fc_companies');

        $cols = [
            'hash', 'prefix', 'first_name', 'last_name', 'email', 'phone',
            'address_line_1', 'city', 'state', 'country', 'postal_code',
            'status', 'contact_type', 'source',
            'total_points', 'life_time_value',
            'company_id', 'date_of_birth',
            'created_at', 'last_activity', 'updated_at',
        ];
        $rows = [];

        for ($i = 0; $i < $count; $i++) {
            $first     = FakeData::firstName();
            $last      = FakeData::lastName();
            $email     = FakeData::email(strtolower($first . '.' . $last));
            $createdAt = $this->randDate('-2 years', 'now');

            // 40% chance of belonging to a company
            $companyId = (!empty($companyIds) && rand(1, 100) <= 40)
                ? $this->randomElement($companyIds)
                : null;

            $rows[] = [
                'hash'            => md5($email),
                'prefix'          => rand(0, 1) ? FakeData::prefix() : null,
                'first_name'      => $first,
                'last_name'       => $last,
                'email'           => $email,
                'phone'           => rand(0, 1) ? FakeData::phone() : null,
                'address_line_1'  => FakeData::addressLine(),
                'city'            => FakeData::city(),
                'state'           => FakeData::state(),
                'country'         => FakeData::country(),
                'postal_code'     => FakeData::postalCode(),
                'status'          => $this->weightedRandom([
                    'subscribed'   => 70,
                    'unsubscribed' => 15,
                    'pending'      => 10,
                    'bounced'      => 5,
                ]),
                'contact_type'    => $this->weightedRandom(['lead' => 60, 'customer' => 40]),
                'source'          => FakeData::source(),
                'total_points'    => rand(0, 1000),
                'life_time_value' => rand(0, 10000),
                'company_id'      => $companyId,
                'date_of_birth'   => $this->randDateOnly('-60 years', '-18 years'),
                'created_at'      => $createdAt,
                'last_activity'   => $this->randDate($createdAt, 'now'),
                'updated_at'      => $this->now(),
            ];

            // Flush every 200 rows
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
