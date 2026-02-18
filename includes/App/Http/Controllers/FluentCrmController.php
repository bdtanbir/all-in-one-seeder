<?php

namespace AllInOneSeeder\App\Http\Controllers;

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
     * Seeder classes are added here as they are implemented in Phase 3.
     *
     * @param  array<string,int> $params
     * @return array<string,int>
     */
    private function runSeeders(array $params): array
    {
        // Phase 3 will populate this method with seeder calls, e.g.:
        //
        //   $companySeeder = new \AllInOneSeeder\App\Seeders\FluentCRM\CompanySeeder();
        //   $seeded['companies'] = $companySeeder->seed($params['companies']);
        //   ...
        //
        // Returning an empty array until seeders are implemented.

        return [];
    }

    /**
     * Truncates all fc_* tables in reverse dependency order.
     * Implemented in Phase 3.
     *
     * @return array<string,bool>
     */
    private function runTruncate(): array
    {
        // Phase 3 will populate this method.
        return [];
    }
}
