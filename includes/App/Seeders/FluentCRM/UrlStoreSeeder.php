<?php

namespace AllInOneSeeder\App\Seeders\FluentCRM;

use AllInOneSeeder\App\Factories\FakeData;
use AllInOneSeeder\App\Seeders\AbstractSeeder;

class UrlStoreSeeder extends AbstractSeeder
{
    private const CHARS = 'abcdefghijklmnopqrstuvwxyz0123456789';

    public function __construct()
    {
        parent::__construct();
        $this->table = $this->db->prefix . 'fc_url_stores';
    }

    public function seed(int $count): int
    {
        $this->inserted = 0;

        $cols = ['url', 'short', 'created_at', 'updated_at'];
        $rows = [];

        for ($i = 0; $i < $count; $i++) {
            $rows[] = [
                'url'        => FakeData::url(),
                'short'      => $this->shortCode(),
                'created_at' => $this->randDate('-6 months', 'now'),
                'updated_at' => $this->now(),
            ];
        }

        return $this->insertBatch($rows, $cols);
    }

    public function truncate(): void
    {
        $this->truncateTable($this->table);
    }

    private function shortCode(int $length = 8): string
    {
        $chars  = self::CHARS;
        $result = '';

        for ($i = 0; $i < $length; $i++) {
            $result .= $chars[rand(0, strlen($chars) - 1)];
        }

        return $result;
    }
}
