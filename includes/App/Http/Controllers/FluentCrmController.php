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
    /** Default count per field when not supplied in the request body. */
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

    /** Hard ceiling on any single count to prevent runaway inserts. */
    private const MAX_COUNT = 5000;

    /**
     * fc_* table suffixes (without wp_ prefix) used by the stats endpoint.
     */
    private const FC_TABLES = [
        'companies'            => 'fc_companies',
        'lists'                => 'fc_lists',
        'tags'                 => 'fc_tags',
        'subscribers'          => 'fc_subscribers',
        'subscriber_pivot'     => 'fc_subscriber_pivot',
        'subscriber_notes'     => 'fc_subscriber_notes',
        'subscriber_meta'      => 'fc_subscriber_meta',
        'campaigns'            => 'fc_campaigns',
        'campaign_emails'      => 'fc_campaign_emails',
        'url_stores'           => 'fc_url_stores',
        'campaign_url_metrics' => 'fc_campaign_url_metrics',
        'funnels'              => 'fc_funnels',
        'funnel_sequences'     => 'fc_funnel_sequences',
        'funnel_subscribers'   => 'fc_funnel_subscribers',
        'funnel_metrics'       => 'fc_funnel_metrics',
    ];

    // -------------------------------------------------------------------------
    // Public route handlers
    // -------------------------------------------------------------------------

    /**
     * POST /aio-seeder/v1/seed/fluent-crm
     */
    public function seed(WP_REST_Request $request): WP_REST_Response
    {
        @set_time_limit(300);

        if ($guard = $this->guardFluentCrmActive()) {
            return $guard;
        }

        $params = $this->parseParams($request);

        try {
            ['seeded' => $seeded, 'errors' => $errors] = $this->runSeeders($params);
        } catch (\Throwable $e) {
            return new WP_REST_Response([
                'success' => false,
                'seeded'  => [],
                'errors'  => [$e->getMessage()],
            ], 500);
        }

        return new WP_REST_Response([
            'success' => empty($errors),
            'seeded'  => $seeded,
            'errors'  => $errors,
        ], 200);
    }

    /**
     * DELETE /aio-seeder/v1/seed/fluent-crm
     */
    public function truncate(WP_REST_Request $request): WP_REST_Response
    {
        if ($guard = $this->guardFluentCrmActive()) {
            return $guard;
        }

        try {
            ['truncated' => $truncated, 'errors' => $errors] = $this->runTruncate();
        } catch (\Throwable $e) {
            return new WP_REST_Response([
                'success'   => false,
                'truncated' => [],
                'errors'    => [$e->getMessage()],
            ], 500);
        }

        return new WP_REST_Response([
            'success'   => empty($errors),
            'truncated' => $truncated,
            'errors'    => $errors,
        ], 200);
    }

    /**
     * GET /aio-seeder/v1/seed/fluent-crm/stats
     *
     * Returns current row counts for every fc_* table.
     */
    public function stats(WP_REST_Request $request): WP_REST_Response
    {
        if ($guard = $this->guardFluentCrmActive()) {
            return $guard;
        }

        return new WP_REST_Response([
            'success' => true,
            'counts'  => $this->tableCounts(),
        ], 200);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Returns a 422 response if FluentCRM is not active, null otherwise.
     */
    private function guardFluentCrmActive(): ?WP_REST_Response
    {
        if (!defined('FLUENTCRM')) {
            return new WP_REST_Response([
                'success' => false,
                'seeded'  => [],
                'errors'  => ['FluentCRM plugin is not installed or activated.'],
            ], 422);
        }

        return null;
    }

    private function parseParams(WP_REST_Request $request): array
    {
        $body   = $request->get_json_params() ?? [];
        $params = [];

        foreach (self::DEFAULTS as $key => $default) {
            $raw          = $body[$key] ?? $default;
            $params[$key] = min(self::MAX_COUNT, max(0, (int) $raw));
        }

        return $params;
    }

    /**
     * Runs each seeder step with individual try/catch so a single failure
     * does not abort the remaining steps.
     *
     * @param  array<string,int> $params
     * @return array{seeded: array<string,int>, errors: string[]}
     */
    private function runSeeders(array $params): array
    {
        $urlCount = max(5, $params['campaigns'] * 3);

        $steps = [
            'companies'            => fn () => (new CompanySeeder())->seed($params['companies']),
            'lists'                => fn () => (new ListSeeder())->seed($params['lists']),
            'tags'                 => fn () => (new TagSeeder())->seed($params['tags']),
            'subscribers'          => fn () => (new SubscriberSeeder())->seed($params['subscribers']),
            'subscriber_pivot'     => fn () => (new SubscriberPivotSeeder())->seed(0),
            'subscriber_notes'     => fn () => (new SubscriberNoteSeeder())->seed($params['subscriber_notes']),
            'subscriber_meta'      => fn () => (new SubscriberMetaSeeder())->seed($params['subscriber_meta']),
            'campaigns'            => fn () => (new CampaignSeeder())->seed($params['campaigns']),
            'campaign_emails'      => fn () => (new CampaignEmailSeeder())->seed(0),
            'url_stores'           => fn () => (new UrlStoreSeeder())->seed($urlCount),
            'campaign_url_metrics' => fn () => (new CampaignUrlMetricSeeder())->seed(0),
            'funnels'              => fn () => (new FunnelSeeder())->seed($params['funnels']),
            'funnel_sequences'     => fn () => (new FunnelSequenceSeeder())->seed($params['funnel_sequences']),
            'funnel_subscribers'   => fn () => (new FunnelSubscriberSeeder())->seed(0),
            'funnel_metrics'       => fn () => (new FunnelMetricSeeder())->seed(0),
        ];

        $seeded = [];
        $errors = [];

        foreach ($steps as $key => $fn) {
            try {
                $seeded[$key] = $fn();
            } catch (\Throwable $e) {
                $seeded[$key] = 0;
                $errors[]     = "[{$key}] " . $e->getMessage();
            }
        }

        return ['seeded' => $seeded, 'errors' => $errors];
    }

    /**
     * Truncates tables in reverse FK order, isolating each step.
     *
     * @return array{truncated: array<string,bool>, errors: string[]}
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
        $errors    = [];

        foreach ($seeders as $key => $seeder) {
            try {
                $seeder->truncate();
                $truncated[$key] = true;
            } catch (\Throwable $e) {
                $truncated[$key] = false;
                $errors[]        = "[{$key}] " . $e->getMessage();
            }
        }

        return ['truncated' => $truncated, 'errors' => $errors];
    }

    /**
     * Returns a COUNT(*) for every fc_* table, returning 0 for non-existent tables.
     *
     * @return array<string,int>
     */
    private function tableCounts(): array
    {
        global $wpdb;

        $counts = [];

        $wpdb->suppress_errors(true);

        foreach (self::FC_TABLES as $key => $suffix) {
            $table      = $wpdb->prefix . $suffix;
            if ($key === 'campaigns') {
                // Match FluentCRM email campaigns list (exclude funnel/internal campaign rows).
                $sql = "SELECT COUNT(*) FROM `{$table}` WHERE `type` = 'campaign'";
            } elseif ($key === 'funnels') {
                // Match FluentCRM automation funnels list.
                $sql = "SELECT COUNT(*) FROM `{$table}` WHERE `type` = 'funnels'";
            } else {
                $sql = "SELECT COUNT(*) FROM `{$table}`";
            }

            $counts[$key] = (int) ($wpdb->get_var($sql) ?? 0);
        }

        $wpdb->suppress_errors(false);

        return $counts;
    }
}
