<?php

namespace AllInOneSeeder\App\Seeders\FluentCRM;

use AllInOneSeeder\App\Factories\FakeData;
use AllInOneSeeder\App\Seeders\AbstractSeeder;

class FunnelSequenceSeeder extends AbstractSeeder
{
    /** Delay options in seconds: 0, 1h, 1d, 2d, 3d, 7d */
    private const DELAYS = [0, 3600, 86400, 172800, 259200, 604800];

    public function __construct()
    {
        parent::__construct();
        $this->table = $this->db->prefix . 'fc_funnel_sequences';
    }

    /**
     * $count = steps per funnel.
     */
    public function seed(int $count): int
    {
        $this->inserted = 0;

        if ($count <= 0) {
            return 0;
        }

        $funnelIds = $this->fetchIds($this->db->prefix . 'fc_funnels');

        if (empty($funnelIds)) {
            return 0;
        }

        $adminId = $this->adminUserId();

        foreach ($funnelIds as $funnelId) {
            for ($seq = 1; $seq <= $count; $seq++) {
                $action = FakeData::actionName();

                $this->insert([
                    'funnel_id'   => $funnelId,
                    'parent_id'   => 0,
                    'action_name' => $action,
                    'type'        => 'sequence',
                    'title'       => ucwords(str_replace('_', ' ', $action)) . ' — Step ' . $seq,
                    'status'      => 'published',
                    'conditions'  => serialize([]),
                    'settings'    => serialize($this->actionSettings($action)),
                    'delay'       => $this->randomElement(self::DELAYS),
                    'sequence'    => $seq,
                    'created_by'  => $adminId,
                    'created_at'  => $this->randDate('-1 year', 'now'),
                    'updated_at'  => $this->now(),
                ]);
            }
        }

        return $this->inserted;
    }

    public function truncate(): void
    {
        $this->truncateTable($this->table);
    }

    private function actionSettings(string $action): array
    {
        switch ($action) {
            case 'send_email':
                return ['email_subject' => FakeData::emailSubject(), 'email_body' => ''];
            case 'wait':
                return ['wait_type' => 'delay'];
            case 'add_tag':
            case 'remove_tag':
                return ['tag_ids' => []];
            case 'add_to_list':
            case 'remove_from_list':
                return ['list_ids' => []];
            case 'update_contact_property':
                return ['property' => 'status', 'value' => 'subscribed'];
            default:
                return [];
        }
    }
}
