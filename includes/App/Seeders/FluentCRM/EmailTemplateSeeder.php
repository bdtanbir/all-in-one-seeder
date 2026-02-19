<?php

namespace AllInOneSeeder\App\Seeders\FluentCRM;

use AllInOneSeeder\App\Factories\FakeData;
use AllInOneSeeder\App\Seeders\AbstractSeeder;

class EmailTemplateSeeder extends AbstractSeeder
{
    public function __construct()
    {
        parent::__construct();
        $this->table = $this->db->posts;
    }

    public function seed(int $count): int
    {
        $this->inserted = 0;

        if ($count <= 0) {
            return 0;
        }

        for ($i = 0; $i < $count; $i++) {
            $title = 'Template ' . FakeData::campaignTitle() . ' ' . ($i + 1);

            $templateId = wp_insert_post([
                'post_title'        => $title,
                'post_content'      => FakeData::loremHtml(),
                'post_excerpt'      => FakeData::preHeader(),
                'post_status'       => $this->weightedRandom(['publish' => 75, 'draft' => 25]),
                'post_type'         => 'fc_template',
                'post_author'       => $this->adminUserId(),
                'post_date'         => $this->randDate('-6 months', 'now'),
                'post_date_gmt'     => gmdate('Y-m-d H:i:s'),
                'post_modified'     => $this->now(),
                'post_modified_gmt' => gmdate('Y-m-d H:i:s'),
            ], true);

            if (is_wp_error($templateId) || !$templateId) {
                continue;
            }

            update_post_meta($templateId, '_email_subject', FakeData::emailSubject());
            update_post_meta($templateId, '_edit_type', 'html');
            update_post_meta($templateId, '_design_template', 'simple');
            update_post_meta($templateId, '_template_config', []);
            update_post_meta($templateId, '_footer_settings', [
                'custom_footer' => 'no',
                'footer_content' => '',
            ]);

            $this->inserted++;
        }

        return $this->inserted;
    }

    public function truncate(): void
    {
        $ids = get_posts([
            'post_type'      => 'fc_template',
            'post_status'    => ['publish', 'draft', 'pending', 'private', 'future', 'trash'],
            'numberposts'    => -1,
            'fields'         => 'ids',
            'suppress_filters' => true,
        ]);

        foreach ($ids as $id) {
            wp_delete_post((int) $id, true);
        }
    }
}
