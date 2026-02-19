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

    public function seed(int $count): int
    {
        $this->inserted = 0;

        $subscriberIds = $this->fetchSubscriberIds($count);
        $listIds       = $this->fetchIds($this->db->prefix . 'fc_lists');
        $tagIds        = $this->fetchIds($this->db->prefix . 'fc_tags');

        if (empty($subscriberIds)) {
            return 0;
        }

        // Normalize any existing duplicate relations first across the table.
        $this->purgeDuplicateRows();
        $existing = $this->fetchExistingMap($subscriberIds);

        $cols = ['subscriber_id', 'object_id', 'object_type', 'status', 'created_at', 'updated_at'];
        $rows = [];
        $now  = $this->now();

        foreach ($subscriberIds as $subscriberId) {
            $subExisting = $existing[$subscriberId] ?? ['lists' => [], 'tags' => []];

            // Attach 1–3 random lists
            if (!empty($listIds)) {
                $availableListIds = array_values(array_diff($listIds, $subExisting['lists']));
                if (!empty($availableListIds)) {
                    $pickCount = rand(1, min(3, count($availableListIds)));
                    foreach ($this->randomSample($availableListIds, $pickCount) as $listId) {
                        $listId = (int) $listId;
                        $existing[$subscriberId]['lists'][] = $listId;
                        $existing[$subscriberId]['lists']   = array_values(array_unique($existing[$subscriberId]['lists']));

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
            }

            // Attach 1–5 random tags
            if (!empty($tagIds)) {
                $availableTagIds = array_values(array_diff($tagIds, $subExisting['tags']));
                if (!empty($availableTagIds)) {
                    $pickCount = rand(1, min(5, count($availableTagIds)));
                    foreach ($this->randomSample($availableTagIds, $pickCount) as $tagId) {
                        $tagId = (int) $tagId;
                        $existing[$subscriberId]['tags'][] = $tagId;
                        $existing[$subscriberId]['tags']   = array_values(array_unique($existing[$subscriberId]['tags']));

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

    /**
     * Returns latest subscriber IDs when $limit > 0, otherwise all.
     *
     * @return int[]
     */
    private function fetchSubscriberIds(int $limit = 0): array
    {
        $table = $this->db->prefix . 'fc_subscribers';
        $sql   = "SELECT id FROM `{$table}` ORDER BY id DESC";
        if ($limit > 0) {
            $sql .= $this->db->prepare(' LIMIT %d', $limit);
        }
        $ids = $this->db->get_col($sql) ?: [];

        if ($limit > 0) {
            $ids = array_reverse($ids);
        }

        return array_map('intval', $ids);
    }

    /**
     * Removes duplicate rows by (subscriber_id, object_id, object_type),
     * keeping the earliest row id.
     *
     */
    private function purgeDuplicateRows(array $subscriberIds = []): void
    {
        $table  = $this->table;
        $sql    = "DELETE p1
                   FROM `{$table}` p1
                   INNER JOIN `{$table}` p2
                       ON p1.subscriber_id = p2.subscriber_id
                      AND p1.object_id     = p2.object_id
                      AND p1.object_type   = p2.object_type
                      AND p1.id > p2.id";

        if (!empty($subscriberIds)) {
            $idsSql = implode(',', array_map('intval', $subscriberIds));
            $sql   .= " WHERE p1.subscriber_id IN ({$idsSql})";
        }

        $this->db->query($sql);
    }

    /**
     * Returns existing list/tag relations for each subscriber.
     *
     * @param int[] $subscriberIds
     * @return array<int,array{lists:int[],tags:int[]}>
     */
    private function fetchExistingMap(array $subscriberIds): array
    {
        $map = [];
        foreach ($subscriberIds as $sid) {
            $map[(int) $sid] = ['lists' => [], 'tags' => []];
        }

        if (empty($subscriberIds)) {
            return $map;
        }

        $idsSql = implode(',', array_map('intval', $subscriberIds));
        $rows   = $this->db->get_results(
            "SELECT subscriber_id, object_id, object_type
             FROM `{$this->table}`
             WHERE subscriber_id IN ({$idsSql})"
        ) ?: [];

        foreach ($rows as $row) {
            $subscriberId = (int) $row->subscriber_id;
            $objectId     = (int) $row->object_id;
            $bucket       = ($row->object_type === self::LIST_TYPE) ? 'lists' : (($row->object_type === self::TAG_TYPE) ? 'tags' : null);

            if (!$bucket || !isset($map[$subscriberId])) {
                continue;
            }

            $map[$subscriberId][$bucket][] = $objectId;
        }

        foreach ($map as $sid => $data) {
            $map[$sid]['lists'] = array_values(array_unique($data['lists']));
            $map[$sid]['tags']  = array_values(array_unique($data['tags']));
        }

        return $map;
    }
}
