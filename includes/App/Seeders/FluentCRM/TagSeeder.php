<?php

namespace AllInOneSeeder\App\Seeders\FluentCRM;

use AllInOneSeeder\App\Factories\FakeData;
use AllInOneSeeder\App\Seeders\AbstractSeeder;

class TagSeeder extends AbstractSeeder
{
    public function __construct()
    {
        parent::__construct();
        $this->table = $this->db->prefix . 'fc_tags';
    }

    public function seed(int $count): int
    {
        $this->inserted = 0;

        $cols = ['title', 'slug', 'description', 'created_at', 'updated_at'];
        $rows = [];

        for ($i = 0; $i < $count; $i++) {
            $name   = FakeData::tagName() . '-' . ($i + 1);
            $rows[] = [
                'title'       => $name,
                'slug'        => $name,
                'description' => FakeData::sentence(),
                'created_at'  => $this->randDate('-1 year', 'now'),
                'updated_at'  => $this->now(),
            ];
        }

        return $this->insertBatch($rows, $cols);
    }

    public function truncate(): void
    {
        $this->truncateTable($this->table);
    }
}
