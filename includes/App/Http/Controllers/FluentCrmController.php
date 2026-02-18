<?php

namespace AllInOneSeeder\App\Http\Controllers;

use AllInOneSeeder\App\Seeders\FluentCRM\CampaignEmailSeeder;
use AllInOneSeeder\App\Seeders\FluentCRM\CampaignSeeder;
use AllInOneSeeder\App\Seeders\FluentCRM\CampaignUrlMetricSeeder;
use AllInOneSeeder\App\Seeders\FluentCRM\CompanySeeder;
use AllInOneSeeder\App\Seeders\FluentCRM\FunnelMetricSeeder;
use AllInOneSeeder\App\Seeders\FluentCRM\FunnelSeeder;
use AllInOneSeeder\App\Seeders\FluentCRM\FunnelSequenceSeeder;
use AllInOneSeeder\App\Seeders\FluentCRM\FunnelSubscriberSeeder;
use AllInOneSeeder\App\Seeders\FluentCRM\ListSeeder;
use AllInOneSeeder\App\Seeders\FluentCRM\SubscriberMetaSeeder;
use AllInOneSeeder\App\Seeders\FluentCRM\SubscriberNoteSeeder;
use AllInOneSeeder\App\Seeders\FluentCRM\SubscriberPivotSeeder;
use AllInOneSeeder\App\Seeders\FluentCRM\SubscriberSeeder;
use AllInOneSeeder\App\Seeders\FluentCRM\TagSeeder;
use AllInOneSeeder\App\Seeders\FluentCRM\UrlStoreSeeder;
use WP_REST_Request;
use WP_REST_Response;

class FluentCrmController
{
    private const DEFAULTS = [
        'companies'        => 10,
        'lists'            => 5,
        'tags'             => 10,
        'subscribers'      => 50,
        'subscriber_notes' => 2,
        'subscriber_meta'  => 3,
        'campaigns'        => 5,
        'funnels'          => 3,
        'funnel_sequences' => 5,
    ];

    /**
     * POST /aio-seeder/v1/seed/fluent-crm
     *
     * Runs all FluentCRM seeders in dependency order.
     * Seeder classes are wired in Phase 3; this controller
     * already owns request parsing and the response contract.
     */
    public function seed(WP_REST_Request $request): WP_REST_Response
    {
        @set_time_limit(300);

        $params = $this->parseParams($request);
        $seeded = [];
        $errors = [];

        try {
            $seeded = $this->runSeeders($params);
        } catch (\Throwable $e) {
            $errors[] = $e->getMessage();
        }

        return new WP_REST_Response([
            'success' => empty($errors),
            'seeded'  => $seeded,
            'errors'  => $errors,
        ], empty($errors) ? 200 : 500);
    }

    /**
     * DELETE /aio-seeder/v1/seed/fluent-crm
     *
     * Truncates all fc_* tables. Implemented in Phase 3.
     */
    public function truncate(WP_REST_Request $request): WP_REST_Response
    {
        $truncated = [];
        $errors    = [];

        try {
            $truncated = $this->runTruncate();
        } catch (\Throwable $e) {
            $errors[] = $e->getMessage();
        }

        return new WP_REST_Response([
            'success'   => empty($errors),
            'truncated' => $truncated,
            'errors'    => $errors,
        ], empty($errors) ? 200 : 500);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function parseParams(WP_REST_Request $request): array
    {
        $body   = $request->get_json_params() ?? [];
        $params = [];

        foreach (self::DEFAULTS as $key => $default) {
            $raw          = $body[$key] ?? $default;
            $params[$key] = max(0, (int) $raw);
        }

        return $params;
    }

    /**
     * Runs each seeder in FK-dependency order and collects inserted counts.
     *
     * @param  array<string,int> $params
     * @return array<string,int>
     */
    private function runSeeders(array $params): array
    {
        $seeded = [];

        // --- Standalone tables (no FK deps) ---
        $seeded['companies'] = (new CompanySeeder())->seed($params['companies']);
        $seeded['lists']     = (new ListSeeder())->seed($params['lists']);
        $seeded['tags']      = (new TagSeeder())->seed($params['tags']);

        // --- Subscribers (depends on companies) ---
        $seeded['subscribers']      = (new SubscriberSeeder())->seed($params['subscribers']);
        $seeded['subscriber_pivot'] = (new SubscriberPivotSeeder())->seed(0);
        $seeded['subscriber_notes'] = (new SubscriberNoteSeeder())->seed($params['subscriber_notes']);
        $seeded['subscriber_meta']  = (new SubscriberMetaSeeder())->seed($params['subscriber_meta']);

        // --- Campaigns (depends on lists, tags, subscribers) ---
        $seeded['campaigns']      = (new CampaignSeeder())->seed($params['campaigns']);
        $seeded['campaign_emails'] = (new CampaignEmailSeeder())->seed(0);

        // --- URL tracking (depends on campaigns + subscribers) ---
        $urlCount                         = max(5, $params['campaigns'] * 3);
        $seeded['url_stores']             = (new UrlStoreSeeder())->seed($urlCount);
        $seeded['campaign_url_metrics']   = (new CampaignUrlMetricSeeder())->seed(0);

        // --- Funnels (depends on subscribers) ---
        $seeded['funnels']            = (new FunnelSeeder())->seed($params['funnels']);
        $seeded['funnel_sequences']   = (new FunnelSequenceSeeder())->seed($params['funnel_sequences']);
        $seeded['funnel_subscribers'] = (new FunnelSubscriberSeeder())->seed(0);
        $seeded['funnel_metrics']     = (new FunnelMetricSeeder())->seed(0);

        return $seeded;
    }

    /**
     * Truncates all fc_* tables in reverse FK-dependency order.
     *
     * @return array<string,bool>
     */
    private function runTruncate(): array
    {
        $seeders = [
            'funnel_metrics'        => new FunnelMetricSeeder(),
            'funnel_subscribers'    => new FunnelSubscriberSeeder(),
            'funnel_sequences'      => new FunnelSequenceSeeder(),
            'funnels'               => new FunnelSeeder(),
            'campaign_url_metrics'  => new CampaignUrlMetricSeeder(),
            'url_stores'            => new UrlStoreSeeder(),
            'campaign_emails'       => new CampaignEmailSeeder(),
            'campaigns'             => new CampaignSeeder(),
            'subscriber_meta'       => new SubscriberMetaSeeder(),
            'subscriber_notes'      => new SubscriberNoteSeeder(),
            'subscriber_pivot'      => new SubscriberPivotSeeder(),
            'subscribers'           => new SubscriberSeeder(),
            'tags'                  => new TagSeeder(),
            'lists'                 => new ListSeeder(),
            'companies'             => new CompanySeeder(),
        ];

        $truncated = [];

        foreach ($seeders as $key => $seeder) {
            $seeder->truncate();
            $truncated[$key] = true;
        }

        return $truncated;
    }
}
