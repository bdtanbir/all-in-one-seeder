<?php

namespace AllInOneSeeder\App\Seeders\FluentCRM;

use AllInOneSeeder\App\Seeders\AbstractSeeder;

class CampaignUrlMetricSeeder extends AbstractSeeder
{
    private const COUNTRIES = ['US', 'GB', 'CA', 'AU', 'DE', 'FR', 'ES', 'IT', 'NL', 'SE'];

    public function __construct()
    {
        parent::__construct();
        $this->table = $this->db->prefix . 'fc_campaign_url_metrics';
    }

    /**
     * Creates click/view metrics for sent campaigns.
     * $count is not used — derived from existing records.
     */
    public function seed(int $count): int
    {
        $this->inserted = 0;

        $urlIds        = $this->fetchIds($this->db->prefix . 'fc_url_stores');
        $subscriberIds = $this->fetchIds($this->db->prefix . 'fc_subscribers');
        $campaignIds   = $this->fetchSentCampaignIds();

        if (empty($urlIds) || empty($campaignIds) || empty($subscriberIds)) {
            return 0;
        }

        $cols = [
            'url_id', 'campaign_id', 'subscriber_id', 'type',
            'ip_address', 'country', 'city', 'counter',
            'created_at', 'updated_at',
        ];
        $rows = [];

        foreach ($campaignIds as $campaignId) {
            // 3–8 subscribers interact with each sent campaign
            $sample = $this->randomSample($subscriberIds, rand(3, min(8, count($subscriberIds))));

            foreach ($sample as $subscriberId) {
                $rows[] = [
                    'url_id'        => $this->randomElement($urlIds),
                    'campaign_id'   => $campaignId,
                    'subscriber_id' => $subscriberId,
                    'type'          => $this->weightedRandom(['click' => 70, 'view' => 30]),
                    'ip_address'    => rand(1, 254) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(1, 254),
                    'country'       => $this->randomElement(self::COUNTRIES),
                    'city'          => null,
                    'counter'       => rand(1, 10),
                    'created_at'    => $this->randDate('-3 months', 'now'),
                    'updated_at'    => $this->now(),
                ];
            }

            if (count($rows) >= 500) {
                $this->insertBatch($rows, $cols);
                $rows = [];
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

    private function fetchSentCampaignIds(): array
    {
        $table = $this->db->prefix . 'fc_campaigns';

        return array_map(
            'intval',
            $this->db->get_col("SELECT id FROM `{$table}` WHERE status = 'sent'") ?: []
        );
    }
}
