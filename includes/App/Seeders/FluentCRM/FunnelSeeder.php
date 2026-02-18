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

        if ($count <= 0) {
            return 0;
        }

        $adminId = $this->adminUserId();

        for ($i = 0; $i < $count; $i++) {
            $this->insert([
                // FluentCRM stores automation funnels as type "funnels" (plural).
                'type'         => 'funnels',
                'title'        => FakeData::funnelTitle() . ' ' . ($i + 1),
                // Keep trigger universally available in core FluentCRM.
                'trigger_name' => 'user_register',
                'status'       => $this->weightedRandom(['published' => 80, 'draft' => 20]),
                'conditions'   => serialize([
                    'update_type' => 'update',
                    'user_roles'  => [],
                ]),
                'settings'     => serialize([
                    'subscription_status' => 'subscribed',
                ]),
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
