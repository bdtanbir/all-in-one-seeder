<?php

namespace AllInOneSeeder\App\Seeders\FluentCRM;

use AllInOneSeeder\App\Factories\FakeData;
use AllInOneSeeder\App\Seeders\AbstractSeeder;

class ListSeeder extends AbstractSeeder
{
    public function __construct()
    {
        parent::__construct();
        $this->table = $this->db->prefix . 'fc_lists';
    }

    public function seed(int $count): int
    {
        $this->inserted = 0;

        $cols = ['title', 'slug', 'description', 'is_public', 'created_at', 'updated_at'];
        $rows = [];

        for ($i = 0; $i < $count; $i++) {
            $title  = FakeData::listName();
            $suffix = $i + 1;
            $rows[] = [
                'title'       => $title . ' ' . $suffix,
                'slug'        => FakeData::slug($title) . '-' . $suffix,
                'description' => FakeData::sentence(),
                'is_public'   => rand(0, 1),
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
