<?php

namespace AllInOneSeeder\App\Seeders\FluentCRM;

use AllInOneSeeder\App\Factories\FakeData;
use AllInOneSeeder\App\Seeders\AbstractSeeder;

class SubscriberNoteSeeder extends AbstractSeeder
{
    public function __construct()
    {
        parent::__construct();
        $this->table = $this->db->prefix . 'fc_subscriber_notes';
    }

    /**
     * $count = notes per subscriber.
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
        $cols    = [
            'subscriber_id', 'created_by', 'status', 'type',
            'is_private', 'title', 'description', 'created_at', 'updated_at',
        ];
        $rows = [];

        foreach ($subscriberIds as $subscriberId) {
            for ($i = 0; $i < $count; $i++) {
                $rows[] = [
                    'subscriber_id' => $subscriberId,
                    'created_by'    => $adminId,
                    'status'        => $this->weightedRandom(['open' => 60, 'closed' => 40]),
                    'type'          => $this->randomElement(['note', 'task', 'activity']),
                    'is_private'    => rand(0, 1),
                    'title'         => FakeData::noteTitle(),
                    'description'   => FakeData::paragraph(2),
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
}
