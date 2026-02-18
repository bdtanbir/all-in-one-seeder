<?php

namespace AllInOneSeeder\App\Seeders\FluentCRM;

use AllInOneSeeder\App\Seeders\AbstractSeeder;

class CampaignEmailSeeder extends AbstractSeeder
{
    public function __construct()
    {
        parent::__construct();
        $this->table = $this->db->prefix . 'fc_campaign_emails';
    }

    /**
     * Creates one email record per campaign × subscriber.
     * $count is not used — derived from existing campaigns and subscribers.
     */
    public function seed(int $count): int
    {
        $this->inserted = 0;

        $campaigns   = $this->fetchCampaigns();
        $subscribers = $this->fetchSubscriberEmails();

        if (empty($campaigns) || empty($subscribers)) {
            return 0;
        }

        $cols = [
            'campaign_id', 'email_type', 'subscriber_id', 'email_address',
            'email_subject', 'is_open', 'click_counter', 'status',
            'email_hash', 'scheduled_at', 'created_at', 'updated_at',
        ];
        $rows = [];

        foreach ($campaigns as $campaign) {
            $baseTs  = (int) strtotime($campaign['scheduled_at'] ?? '-1 month');

            foreach ($subscribers as $sub) {
                $status = $this->emailStatusForCampaign((string) ($campaign['status'] ?? 'draft'));
                $isOpen = (in_array($status, ['sent', 'bounced', 'failed'], true) && rand(1, 100) <= 40) ? 1 : 0;

                $rows[] = [
                    'campaign_id'   => (int) $campaign['id'],
                    'email_type'    => 'campaign',
                    'subscriber_id' => (int) $sub['id'],
                    'email_address' => $sub['email'],
                    'email_subject' => $campaign['email_subject'],
                    'is_open'       => $isOpen,
                    'click_counter' => $isOpen ? rand(0, 5) : 0,
                    'status'        => $status,
                    'email_hash'    => md5($campaign['id'] . '.' . $sub['id'] . '.' . uniqid()),
                    'scheduled_at'  => date('Y-m-d H:i:s', $baseTs + rand(-3600, 3600)),
                    'created_at'    => $campaign['created_at'],
                    'updated_at'    => $this->now(),
                ];

                if (count($rows) >= 500) {
                    $this->insertBatch($rows, $cols);
                    $rows = [];
                }
            }
        }

        if (!empty($rows)) {
            $this->insertBatch($rows, $cols);
        }

        return $this->inserted;
    }

    public function truncate(): void
    {
        $this->truncateTable($this->table);
    }

    private function fetchCampaigns(): array
    {
        $table = $this->db->prefix . 'fc_campaigns';

        return $this->db->get_results(
            "SELECT id, status, email_subject, scheduled_at, created_at FROM `{$table}`",
            ARRAY_A
        ) ?: [];
    }

    private function fetchSubscriberEmails(): array
    {
        $table = $this->db->prefix . 'fc_subscribers';

        return $this->db->get_results(
            "SELECT id, email FROM `{$table}`",
            ARRAY_A
        ) ?: [];
    }

    private function emailStatusForCampaign(string $campaignStatus): string
    {
        switch ($campaignStatus) {
            case 'archived':
                return $this->weightedRandom(['sent' => 82, 'bounced' => 9, 'failed' => 9]);
            case 'working':
                return $this->weightedRandom(['sent' => 45, 'pending' => 25, 'scheduled' => 20, 'processing' => 10]);
            case 'paused':
                return $this->weightedRandom(['paused' => 55, 'scheduled' => 30, 'pending' => 15]);
            case 'scheduled':
                return $this->weightedRandom(['scheduled' => 70, 'pending' => 30]);
            default:
                return 'draft';
        }
    }
}
