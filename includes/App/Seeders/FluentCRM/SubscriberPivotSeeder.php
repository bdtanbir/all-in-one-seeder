<?php

namespace AllInOneSeeder\App\Seeders\FluentCRM;

use AllInOneSeeder\App\Seeders\AbstractSeeder;

class SubscriberPivotSeeder extends AbstractSeeder
{
    private const LIST_TYPE = 'FluentCrm\App\Models\Lists';
    private const TAG_TYPE  = 'FluentCrm\App\Models\Tag';

    public function __construct()
    {
        parent::__construct();
        $this->table = $this->db->prefix . 'fc_subscriber_pivot';
    }

    /**
     * Attaches lists and tags to all existing subscribers.
     * $count is not used — relationships are derived from existing records.
     */
    public function seed(int $count): int
    {
        $this->inserted = 0;

        $subscriberIds = $this->fetchIds($this->db->prefix . 'fc_subscribers');
        $listIds       = $this->fetchIds($this->db->prefix . 'fc_lists');
        $tagIds        = $this->fetchIds($this->db->prefix . 'fc_tags');

        if (empty($subscriberIds)) {
            return 0;
        }

        $cols = ['subscriber_id', 'object_id', 'object_type', 'status', 'created_at', 'updated_at'];
        $rows = [];
        $now  = $this->now();

        foreach ($subscriberIds as $subscriberId) {
            // Attach 1–3 random lists
            if (!empty($listIds)) {
                foreach ($this->randomSample($listIds, rand(1, min(3, count($listIds)))) as $listId) {
                    $rows[] = [
                        'subscriber_id' => $subscriberId,
                        'object_id'     => $listId,
                        'object_type'   => self::LIST_TYPE,
                        'status'        => $this->weightedRandom(['subscribed' => 85, 'unsubscribed' => 15]),
                        'created_at'    => $now,
                        'updated_at'    => $now,
                    ];
                }
            }

            // Attach 1–5 random tags
            if (!empty($tagIds)) {
                foreach ($this->randomSample($tagIds, rand(1, min(5, count($tagIds)))) as $tagId) {
                    $rows[] = [
                        'subscriber_id' => $subscriberId,
                        'object_id'     => $tagId,
                        'object_type'   => self::TAG_TYPE,
                        'status'        => 'active',
                        'created_at'    => $now,
                        'updated_at'    => $now,
                    ];
                }
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
}
