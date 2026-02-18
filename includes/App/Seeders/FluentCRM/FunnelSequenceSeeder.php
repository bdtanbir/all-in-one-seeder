<?php

namespace AllInOneSeeder\App\Seeders\FluentCRM;

use AllInOneSeeder\App\Seeders\AbstractSeeder;

class FunnelSequenceSeeder extends AbstractSeeder
{
    /**
     * Core FluentCRM action names that exist in free installs.
     * We avoid integration-specific actions to keep seeded data portable.
     */
    private const ACTIONS = [
        'fluentcrm_wait_times',
        'add_contact_to_tag',
        'detach_contact_from_tag',
        'add_contact_to_list',
        'detach_contact_from_list',
    ];

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
        $listIds = $this->fetchIds($this->db->prefix . 'fc_lists');
        $tagIds  = $this->fetchIds($this->db->prefix . 'fc_tags');

        foreach ($funnelIds as $funnelId) {
            for ($seq = 1; $seq <= $count; $seq++) {
                $action = $this->randomElement(self::ACTIONS);

                $this->insert([
                    'funnel_id'   => $funnelId,
                    'parent_id'   => 0,
                    'action_name' => $action,
                    'type'        => 'action',
                    'title'       => ucwords(str_replace('_', ' ', $action)) . ' - Step ' . $seq,
                    'status'      => 'published',
                    'conditions'  => serialize([]),
                    'settings'    => serialize($this->actionSettings($action, $listIds, $tagIds)),
                    'delay'       => $action === 'fluentcrm_wait_times' ? rand(3600, 604800) : 0,
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

    private function actionSettings(string $action, array $listIds, array $tagIds): array
    {
        switch ($action) {
            case 'fluentcrm_wait_times':
                return [
                    'wait_type'         => 'unit_wait',
                    'wait_time_amount'  => rand(1, 7),
                    'wait_time_unit'    => $this->randomElement(['days', 'hours']),
                    'is_timestamp_wait' => '',
                    'wait_date_time'    => '',
                    'to_day'            => [],
                    'to_day_time'       => '',
                ];
            case 'add_contact_to_tag':
            case 'detach_contact_from_tag':
                return ['tags' => !empty($tagIds) ? [$this->randomElement($tagIds)] : []];
            case 'add_contact_to_list':
            case 'detach_contact_from_list':
                return ['lists' => !empty($listIds) ? [$this->randomElement($listIds)] : []];
            default:
                return [];
        }
    }
}
