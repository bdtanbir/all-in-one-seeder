<?php

namespace AllInOneSeeder\App\Seeders\FluentCRM;

use AllInOneSeeder\App\Factories\FakeData;
use AllInOneSeeder\App\Seeders\AbstractSeeder;

class CampaignSeeder extends AbstractSeeder
{
    public function __construct()
    {
        parent::__construct();
        $this->table = $this->db->prefix . 'fc_campaigns';
    }

    public function seed(int $count): int
    {
        $this->inserted = 0;

        $adminId = $this->adminUserId();
        $listIds = $this->fetchIds($this->db->prefix . 'fc_lists');
        $tagIds  = $this->fetchIds($this->db->prefix . 'fc_tags');

        for ($i = 0; $i < $count; $i++) {
            $title  = FakeData::campaignTitle();
            // FluentCRM campaign statuses (campaigns are not marked as "sent").
            $status = $this->weightedRandom([
                'draft'     => 40,
                'scheduled' => 25,
                'working'   => 15,
                'paused'    => 10,
                'archived'  => 10,
            ]);

            $settings = [
                'subscribe_list' => !empty($listIds) ? [$this->randomElement($listIds)] : [],
                'subscribe_tag'  => !empty($tagIds)  ? [$this->randomElement($tagIds)]  : [],
                'sending_filter' => 'list_tag',
            ];

            $scheduledAt = in_array($status, ['scheduled', 'paused'], true)
                ? $this->randDate('now', '+30 days')
                : $this->randDate('-6 months', 'now');

            $this->insert([
                'type'             => 'campaign',
                'title'            => $title,
                'slug'             => FakeData::slug($title) . '-' . ($i + 1),
                'status'           => $status,
                'email_subject'    => FakeData::emailSubject(),
                'email_pre_header' => FakeData::preHeader(),
                'email_body'       => FakeData::loremHtml(),
                'recipients_count' => rand(10, 500),
                'design_template'  => 'simple',
                'scheduled_at'     => $scheduledAt,
                'settings'         => serialize($settings),
                'created_by'       => $adminId,
                'created_at'       => $this->randDate('-6 months', 'now'),
                'updated_at'       => $this->now(),
            ]);
        }

        return $this->inserted;
    }

    public function truncate(): void
    {
        $this->truncateTable($this->table);
    }
}
