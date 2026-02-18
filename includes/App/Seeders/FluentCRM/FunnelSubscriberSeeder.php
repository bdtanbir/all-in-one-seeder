<?php

namespace AllInOneSeeder\App\Seeders\FluentCRM;

use AllInOneSeeder\App\Seeders\AbstractSeeder;

class FunnelSubscriberSeeder extends AbstractSeeder
{
    public function __construct()
    {
        parent::__construct();
        $this->table = $this->db->prefix . 'fc_funnel_subscribers';
    }

    /**
     * Enrolls 20–50% of subscribers into each funnel.
     * $count is not used — derived from existing records.
     */
    public function seed(int $count): int
    {
        $this->inserted = 0;

        $funnelIds     = $this->fetchIds($this->db->prefix . 'fc_funnels');
        $subscriberIds = $this->fetchIds($this->db->prefix . 'fc_subscribers');

        if (empty($funnelIds) || empty($subscriberIds)) {
            return 0;
        }

        $cols = [
            'funnel_id', 'subscriber_id',
            'starting_sequence_id', 'last_sequence_id', 'next_sequence_id',
            'last_sequence_status', 'status', 'type',
            'last_executed_time', 'next_execution_time',
            'created_at', 'updated_at',
        ];
        $rows = [];

        foreach ($funnelIds as $funnelId) {
            $sequenceIds = $this->fetchSequenceIds($funnelId);
            $startSeqId  = !empty($sequenceIds) ? $sequenceIds[0] : null;

            // Enroll 20–50% of subscribers
            $enrollCount = max(1, (int) floor(count($subscriberIds) * (rand(20, 50) / 100)));
            $sample      = $this->randomSample($subscriberIds, $enrollCount);

            foreach ($sample as $subscriberId) {
                $status      = $this->weightedRandom(['active' => 60, 'completed' => 30, 'paused' => 10]);
                $lastSeqId   = !empty($sequenceIds) ? $this->randomElement($sequenceIds) : null;
                $nextSeqId   = ($status !== 'completed' && !empty($sequenceIds))
                    ? $this->randomElement($sequenceIds)
                    : null;
                $lastExecTime = $this->randDate('-3 months', 'now');

                $rows[] = [
                    'funnel_id'             => $funnelId,
                    'subscriber_id'         => $subscriberId,
                    'starting_sequence_id'  => $startSeqId,
                    'last_sequence_id'      => $lastSeqId,
                    'next_sequence_id'      => $nextSeqId,
                    'last_sequence_status'  => $this->weightedRandom(['complete' => 70, 'pending' => 30]),
                    'status'                => $status,
                    'type'                  => 'funnel',
                    'last_executed_time'    => $lastExecTime,
                    'next_execution_time'   => ($status === 'active')
                        ? $this->randDate('now', '+7 days')
                        : null,
                    'created_at'            => $lastExecTime,
                    'updated_at'            => $this->now(),
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

    private function fetchSequenceIds(int $funnelId): array
    {
        $table = $this->db->prefix . 'fc_funnel_sequences';

        return array_map(
            'intval',
            $this->db->get_col(
                $this->db->prepare(
                    "SELECT id FROM `{$table}` WHERE funnel_id = %d ORDER BY sequence ASC",
                    $funnelId
                )
            ) ?: []
        );
    }
}
