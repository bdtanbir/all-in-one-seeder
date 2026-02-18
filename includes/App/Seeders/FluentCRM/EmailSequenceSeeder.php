<?php

namespace AllInOneSeeder\App\Seeders\FluentCRM;

use AllInOneSeeder\App\Factories\FakeData;
use AllInOneSeeder\App\Seeders\AbstractSeeder;

class EmailSequenceSeeder extends AbstractSeeder
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

        for ($i = 0; $i < $count; $i++) {
            $title = 'Sequence ' . FakeData::campaignTitle() . ' ' . ($i + 1);

            $created = $this->insert([
                'parent_id'         => null,
                'type'              => 'email_sequence',
                'title'             => $title,
                'slug'              => FakeData::slug($title) . '-' . ($i + 1),
                'status'            => $this->weightedRandom(['published' => 70, 'draft' => 30]),
                'email_subject'     => null,
                'email_pre_header'  => null,
                'email_body'        => '',
                'recipients_count'  => 0,
                'design_template'   => 'simple',
                'scheduled_at'      => null,
                'settings'          => serialize([
                    'mailer_settings' => [
                        'from_name'      => '',
                        'from_email'     => '',
                        'reply_to_name'  => '',
                        'reply_to_email' => '',
                        'is_custom'      => 'no',
                    ],
                ]),
                'created_by'        => $adminId,
                'created_at'        => $this->randDate('-6 months', 'now'),
                'updated_at'        => $this->now(),
            ]);

            if (!$created) {
                continue;
            }

            $sequenceId      = $this->lastInsertId();
            $emailsPerSeq    = rand(2, 5);
            $cumulativeDelay = 0;

            for ($step = 1; $step <= $emailsPerSeq; $step++) {
                $delayUnit   = $this->randomElement(['hours', 'days']);
                $delayAmount = $delayUnit === 'hours' ? rand(4, 24) : rand(1, 5);
                $stepDelay   = $delayUnit === 'hours' ? $delayAmount * 3600 : $delayAmount * 86400;
                $cumulativeDelay += $stepDelay;

                $subject = FakeData::emailSubject();

                $this->insert([
                    'parent_id'         => $sequenceId,
                    'type'              => 'sequence_mail',
                    'title'             => $subject,
                    'slug'              => FakeData::slug($subject) . '-' . $sequenceId . '-' . $step,
                    'status'            => $this->weightedRandom(['published' => 85, 'draft' => 15]),
                    'email_subject'     => $subject,
                    'email_pre_header'  => FakeData::preHeader(),
                    'email_body'        => FakeData::loremHtml(),
                    'delay'             => $cumulativeDelay,
                    'recipients_count'  => 0,
                    'design_template'   => 'raw',
                    'scheduled_at'      => null,
                    'settings'          => serialize([
                        'action_triggers' => [],
                        'timings'         => [
                            'delay_unit'   => $delayUnit,
                            'delay'        => (string) $delayAmount,
                            'is_anytime'   => 'yes',
                            'sending_time' => ['', ''],
                        ],
                        'template_config' => [],
                        'mailer_settings' => [
                            'from_name'      => '',
                            'from_email'     => '',
                            'reply_to_name'  => '',
                            'reply_to_email' => '',
                            'is_custom'      => 'no',
                        ],
                    ]),
                    'created_by'        => $adminId,
                    'created_at'        => $this->randDate('-6 months', 'now'),
                    'updated_at'        => $this->now(),
                ]);
            }
        }

        return $this->inserted;
    }

    public function truncate(): void
    {
        // Shared table fc_campaigns is truncated by CampaignSeeder.
    }
}
