<?php

namespace AllInOneSeeder\App\Seeders\FluentCRM;

use AllInOneSeeder\App\Factories\FakeData;
use AllInOneSeeder\App\Seeders\AbstractSeeder;

class CompanySeeder extends AbstractSeeder
{
    public function __construct()
    {
        parent::__construct();
        $this->table = $this->db->prefix . 'fc_companies';
    }

    public function seed(int $count): int
    {
        $this->inserted = 0;

        $cols = [
            'hash', 'name', 'industry', 'email', 'phone',
            'address_line_1', 'city', 'state', 'country', 'postal_code',
            'employees_number', 'type', 'website', 'description',
            'created_at', 'updated_at',
        ];
        $rows = [];

        for ($i = 0; $i < $count; $i++) {
            $email  = FakeData::email(FakeData::slug(FakeData::companyName()));
            $rows[] = [
                'hash'             => md5($email),
                'name'             => FakeData::companyName(),
                'industry'         => FakeData::industry(),
                'email'            => $email,
                'phone'            => FakeData::phone(),
                'address_line_1'   => FakeData::addressLine(),
                'city'             => FakeData::city(),
                'state'            => FakeData::state(),
                'country'          => FakeData::country(),
                'postal_code'      => FakeData::postalCode(),
                'employees_number' => rand(5, 5000),
                'type'             => FakeData::companyType(),
                'website'          => FakeData::website(),
                'description'      => FakeData::paragraph(2),
                'created_at'       => $this->randDate('-2 years', 'now'),
                'updated_at'       => $this->now(),
            ];
        }

        return $this->insertBatch($rows, $cols);
    }

    public function truncate(): void
    {
        $this->truncateTable($this->table);
    }
}
