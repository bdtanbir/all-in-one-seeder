<?php

namespace AllInOneSeeder\App\Seeders\FluentCRM;

use AllInOneSeeder\App\Factories\FakeData;
use AllInOneSeeder\App\Seeders\AbstractSeeder;

class RecurringCampaignSeeder extends AbstractSeeder
{
    public function __construct()
    {
        parent::__construct();
        $this->table = $this->db->prefix . 'fc_campaigns';
    }

    public function seed(int $count): int
    {
        $this->inserted = 0;

        if ($count <= 0) {
            return 0;
        }

        $adminId = $this->adminUserId();
        $listIds = $this->fetchIds($this->db->prefix . 'fc_lists');
        $tagIds  = $this->fetchIds($this->db->prefix . 'fc_tags');

        for ($i = 0; $i < $count; $i++) {
            $title = 'Recurring ' . FakeData::campaignTitle() . ' ' . ($i + 1);
            $status = $this->weightedRandom([
                'active' => 60,
                'draft'  => 40,
            ]);

            $settings = [
                'mailer_settings'      => [
                    'from_name'      => '',
                    'from_email'     => '',
                    'reply_to_name'  => '',
                    'reply_to_email' => '',
                    'is_custom'      => 'no',
                ],
                'scheduling_settings'  => $this->schedulingSettings(),
                'sending_conditions'   => [],
                'subscribers_settings' => [
                    'subscribers'         => [
                        [
                            'list' => !empty($listIds) ? (string) $this->randomElement($listIds) : 'all',
                            'tag'  => !empty($tagIds) ? (string) $this->randomElement($tagIds) : 'all',
                        ],
                    ],
                    'excludedSubscribers' => [
                        [
                            'list' => null,
                            'tag'  => null,
                        ],
                    ],
                    'sending_filter'      => 'list_tag',
                    'dynamic_segment'     => [
                        'id'   => '',
                        'slug' => '',
                    ],
                    'advanced_filters'    => [[]],
                ],
                'template_config'      => [],
            ];

            $scheduledAt = $status === 'active'
                ? $this->randDate('now', '+14 days')
                : $this->randDate('-30 days', '+14 days');

            $this->insert([
                'parent_id'         => null,
                'type'              => 'recurring_campaign',
                'title'             => $title,
                'slug'              => FakeData::slug($title) . '-' . ($i + 1),
                'status'            => $status,
                'email_subject'     => FakeData::emailSubject(),
                'email_pre_header'  => FakeData::preHeader(),
                'email_body'        => FakeData::loremHtml(),
                'recipients_count'  => rand(10, 500),
                'design_template'   => 'simple',
                'scheduled_at'      => $scheduledAt,
                'settings'          => serialize($settings),
                'created_by'        => $adminId,
                'created_at'        => $this->randDate('-6 months', 'now'),
                'updated_at'        => $this->now(),
            ]);
        }

        return $this->inserted;
    }

    public function truncate(): void
    {
        // Shared table fc_campaigns is truncated by CampaignSeeder.
    }

    private function schedulingSettings(): array
    {
        $types = ['daily', 'weekly', 'monthly'];
        $type  = $this->randomElement($types);

        $settings = [
            'type'               => $type,
            'time'               => sprintf('%02d.00', rand(7, 20)),
            'send_automatically' => $this->weightedRandom(['yes' => 70, 'no' => 30]),
        ];

        if ($type === 'weekly') {
            $settings['day'] = $this->randomElement(['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat']);
        } elseif ($type === 'monthly') {
            $settings['date'] = (string) rand(1, 28);
        }

        return $settings;
    }
}
