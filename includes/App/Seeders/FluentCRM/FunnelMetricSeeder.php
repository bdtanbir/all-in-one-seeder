<?php

namespace AllInOneSeeder\App\Seeders\FluentCRM;

use AllInOneSeeder\App\Seeders\AbstractSeeder;

class FunnelMetricSeeder extends AbstractSeeder
{
    public function __construct()
    {
        parent::__construct();
        $this->table = $this->db->prefix . 'fc_funnel_metrics';
    }

    /**
     * Creates one metric per sequence each funnel_subscriber has passed through.
     * $count is not used — derived from existing funnel_subscribers.
     */
    public function seed(int $count): int
    {
        $this->inserted = 0;

        $funnelSubscribers = $this->fetchFunnelSubscribers();

        if (empty($funnelSubscribers)) {
            return 0;
        }

        // Cache sequence IDs per funnel to avoid repeated queries
        $sequenceCache = [];

        $cols = [
            'funnel_id', 'sequence_id', 'subscriber_id',
            'benchmark_value', 'benchmark_currency', 'status',
            'created_at', 'updated_at',
        ];
        $rows = [];

        foreach ($funnelSubscribers as $fs) {
            if ($fs['status'] === 'paused') {
                continue;
            }

            $funnelId = (int) $fs['funnel_id'];

            if (!isset($sequenceCache[$funnelId])) {
                $sequenceCache[$funnelId] = $this->fetchSequenceIds($funnelId);
            }

            $sequenceIds = $sequenceCache[$funnelId];

            if (empty($sequenceIds)) {
                continue;
            }

            // Completed subscribers pass through all steps; active ones pass through some
            $passedCount = ($fs['status'] === 'completed')
                ? count($sequenceIds)
                : rand(1, max(1, count($sequenceIds) - 1));

            for ($i = 0; $i < $passedCount; $i++) {
                $rows[] = [
                    'funnel_id'          => $funnelId,
                    'sequence_id'        => $sequenceIds[$i],
                    'subscriber_id'      => (int) $fs['subscriber_id'],
                    'benchmark_value'    => rand(0, 500),
                    'benchmark_currency' => 'USD',
                    'status'             => 'completed',
                    'created_at'         => $this->randDate('-3 months', 'now'),
                    'updated_at'         => $this->now(),
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

    private function fetchFunnelSubscribers(): array
    {
        $table = $this->db->prefix . 'fc_funnel_subscribers';

        return $this->db->get_results(
            "SELECT funnel_id, subscriber_id, status FROM `{$table}`",
            ARRAY_A
        ) ?: [];
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
