<?php

namespace AllInOneSeeder\App\Seeders\FluentCRM;

use AllInOneSeeder\App\Factories\FakeData;
use AllInOneSeeder\App\Seeders\AbstractSeeder;

class FunnelSeeder extends AbstractSeeder
{
    public function __construct()
    {
        parent::__construct();
        $this->table = $this->db->prefix . 'fc_funnels';
    }

    public function seed(int $count): int
    {
        $this->inserted = 0;

        $adminId = $this->adminUserId();

        for ($i = 0; $i < $count; $i++) {
            $this->insert([
                'type'         => 'funnel',
                'title'        => FakeData::funnelTitle() . ' ' . ($i + 1),
                'trigger_name' => FakeData::triggerName(),
                'status'       => $this->weightedRandom(['published' => 70, 'draft' => 30]),
                'conditions'   => serialize([]),
                'settings'     => serialize(['sendingFilter' => 'list_tag']),
                'created_by'   => $adminId,
                'created_at'   => $this->randDate('-1 year', 'now'),
                'updated_at'   => $this->now(),
            ]);
        }

        return $this->inserted;
    }

    public function truncate(): void
    {
        $this->truncateTable($this->table);
    }
}
