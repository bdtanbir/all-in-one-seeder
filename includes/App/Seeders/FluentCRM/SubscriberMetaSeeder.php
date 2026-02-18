<?php

namespace AllInOneSeeder\App\Seeders\FluentCRM;

use AllInOneSeeder\App\Factories\FakeData;
use AllInOneSeeder\App\Seeders\AbstractSeeder;

class SubscriberMetaSeeder extends AbstractSeeder
{
    private const META_KEYS = [
        '_source_form_id',
        '_last_campaign',
        '_custom_score',
        '_signup_page',
        '_referral_source',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->table = $this->db->prefix . 'fc_subscriber_meta';
    }

    /**
     * $count = meta rows per subscriber (capped at 5 distinct keys).
     */
    public function seed(int $count): int
    {
        $this->inserted = 0;

        if ($count <= 0) {
            return 0;
        }

        $subscriberIds = $this->fetchIds($this->db->prefix . 'fc_subscribers');

        if (empty($subscriberIds)) {
            return 0;
        }

        $adminId = $this->adminUserId();
        $keys    = array_slice(self::META_KEYS, 0, min($count, count(self::META_KEYS)));
        $cols    = [
            'subscriber_id', 'created_by', 'object_type', 'key', 'value',
            'created_at', 'updated_at',
        ];
        $rows = [];

        foreach ($subscriberIds as $subscriberId) {
            $selected = $keys;
            shuffle($selected);

            foreach ($selected as $key) {
                $rows[] = [
                    'subscriber_id' => $subscriberId,
                    'created_by'    => $adminId,
                    'object_type'   => 'option',
                    'key'           => $key,
                    'value'         => $this->metaValue($key),
                    'created_at'    => $this->randDate('-1 year', 'now'),
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

    private function metaValue(string $key): string
    {
        switch ($key) {
            case '_source_form_id':
                return (string) rand(1, 50);
            case '_last_campaign':
                return (string) rand(1, 20);
            case '_custom_score':
                return (string) rand(0, 100);
            case '_signup_page':
                return FakeData::url();
            case '_referral_source':
                return FakeData::source();
            default:
                return FakeData::sentence(3);
        }
    }
}
