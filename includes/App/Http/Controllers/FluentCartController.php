<?php

namespace AllInOneSeeder\App\Http\Controllers;

use AllInOneSeeder\App\Seeders\FluentCart\AppliedCouponSeeder;
use AllInOneSeeder\App\Seeders\FluentCart\CouponSeeder;
use AllInOneSeeder\App\Seeders\FluentCart\CustomerAddressSeeder;
use AllInOneSeeder\App\Seeders\FluentCart\CustomerSeeder;
use AllInOneSeeder\App\Seeders\FluentCart\OrderAddressSeeder;
use AllInOneSeeder\App\Seeders\FluentCart\OrderItemSeeder;
use AllInOneSeeder\App\Seeders\FluentCart\OrderMetaSeeder;
use AllInOneSeeder\App\Seeders\FluentCart\OrderOperationSeeder;
use AllInOneSeeder\App\Seeders\FluentCart\OrderSeeder;
use AllInOneSeeder\App\Seeders\FluentCart\OrderTransactionSeeder;
use AllInOneSeeder\App\Seeders\FluentCart\ProductSeeder;
use AllInOneSeeder\App\Seeders\FluentCart\SubscriptionSeeder;
use WP_REST_Request;
use WP_REST_Response;

class FluentCartController
{
    /** Default count per field when not supplied in the request body. */
    private const DEFAULTS = [
        'products'  => 10,
        'customers' => 20,
        'coupons'   => 5,
        'orders'    => 30,
    ];

    /** Hard ceiling on any single count to prevent runaway inserts. */
    private const MAX_COUNT = 5000;

    /**
     * fct_* table suffixes used by the stats endpoint.
     * 'products' is handled separately via wp_posts.
     */
    private const FCT_TABLES = [
        'product_variations' => 'fct_product_variations',
        'customers'          => 'fct_customers',
        'customer_addresses' => 'fct_customer_addresses',
        'coupons'            => 'fct_coupons',
        'orders'             => 'fct_orders',
        'order_items'        => 'fct_order_items',
        'order_addresses'    => 'fct_order_addresses',
        'order_transactions' => 'fct_order_transactions',
        'applied_coupons'    => 'fct_applied_coupons',
        'order_meta'         => 'fct_order_meta',
        'order_operations'   => 'fct_order_operations',
        'subscriptions'      => 'fct_subscriptions',
    ];

    // -------------------------------------------------------------------------
    // Public route handlers
    // -------------------------------------------------------------------------

    /**
     * POST /aio-seeder/v1/seed/fluent-cart
     */
    public function seed(WP_REST_Request $request): WP_REST_Response
    {
        @set_time_limit(300);
        @ini_set('memory_limit', '256M');

        if ($guard = $this->guardFluentCartActive()) {
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
     * DELETE /aio-seeder/v1/seed/fluent-cart
     */
    public function truncate(WP_REST_Request $request): WP_REST_Response
    {
        if ($guard = $this->guardFluentCartActive()) {
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
     * GET /aio-seeder/v1/seed/fluent-cart/stats
     */
    public function stats(WP_REST_Request $request): WP_REST_Response
    {
        if ($guard = $this->guardFluentCartActive()) {
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
     * Returns a 422 response if FluentCart is not active, null otherwise.
     */
    private function guardFluentCartActive(): ?WP_REST_Response
    {
        if (!class_exists('FluentCart\App\Models\Order')) {
            return new WP_REST_Response([
                'success' => false,
                'seeded'  => [],
                'errors'  => ['FluentCart plugin is not installed or activated.'],
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
     * Runs each seeder step in FK dependency order. Individual try/catch
     * per step so a single failure does not abort the remaining steps.
     *
     * @param  array<string,int> $params
     * @return array{seeded: array<string,int>, errors: string[]}
     */
    private function runSeeders(array $params): array
    {
        $hasProducts   = $params['products'] > 0;
        $hasCustomers  = $params['customers'] > 0;
        $hasCoupons    = $params['coupons'] > 0;
        $hasOrders     = $params['orders'] > 0;
        $hasPaidOrders = $hasOrders && $hasProducts && $hasCustomers;

        $steps = [
            // Step 1 — Standalone base tables
            'products'           => fn () => (new ProductSeeder())->seed($params['products']),
            'customers'          => fn () => (new CustomerSeeder())->seed($params['customers']),
            'coupons'            => fn () => (new CouponSeeder())->seed($params['coupons']),

            // Step 2 — Depends on customers
            'customer_addresses' => fn () => $hasCustomers ? (new CustomerAddressSeeder())->seed(0) : 0,

            // Step 3 — Depends on customers
            'orders'             => fn () => $hasCustomers ? (new OrderSeeder())->seed($params['orders']) : 0,

            // Step 4 — Depends on orders + products
            'order_items'        => fn () => ($hasOrders && $hasProducts) ? (new OrderItemSeeder())->seed(0) : 0,

            // Step 5 — Depends on orders + customers
            'order_addresses'    => fn () => ($hasOrders && $hasCustomers) ? (new OrderAddressSeeder())->seed(0) : 0,

            // Step 6 — Depends on orders
            'order_transactions' => fn () => $hasOrders ? (new OrderTransactionSeeder())->seed(0) : 0,

            // Step 7 — Depends on orders + coupons
            'applied_coupons'    => fn () => ($hasOrders && $hasCoupons) ? (new AppliedCouponSeeder())->seed(0) : 0,

            // Step 8 — Depends on orders
            'order_meta'         => fn () => $hasOrders ? (new OrderMetaSeeder())->seed(0) : 0,

            // Step 9 — Depends on orders (completed/processing only)
            'order_operations'   => fn () => $hasOrders ? (new OrderOperationSeeder())->seed(0) : 0,

            // Step 10 — Depends on orders + customers + products
            'subscriptions'      => fn () => $hasPaidOrders ? (new SubscriptionSeeder())->seed(0) : 0,
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
     * Truncates all fct_* tables in reverse FK order so no constraint
     * violations occur. Each step is isolated to avoid cascade failures.
     *
     * @return array{truncated: array<string,bool>, errors: string[]}
     */
    private function runTruncate(): array
    {
        // Reverse of seeding order: children first, parents last
        $seeders = [
            'subscriptions'      => new SubscriptionSeeder(),
            'order_operations'   => new OrderOperationSeeder(),
            'order_meta'         => new OrderMetaSeeder(),
            'applied_coupons'    => new AppliedCouponSeeder(),
            'order_transactions' => new OrderTransactionSeeder(),
            'order_addresses'    => new OrderAddressSeeder(),
            'order_items'        => new OrderItemSeeder(),
            'orders'             => new OrderSeeder(),
            'customer_addresses' => new CustomerAddressSeeder(),
            'coupons'            => new CouponSeeder(),
            'customers'          => new CustomerSeeder(),
            // ProductSeeder::truncate() also cleans wp_posts + fct_product_details
            'products'           => new ProductSeeder(),
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
     * Returns a COUNT(*) for every fct_* table plus the wp_posts
     * fluent-products count. Returns 0 for any table that does not exist.
     *
     * @return array<string,int>
     */
    private function tableCounts(): array
    {
        global $wpdb;

        $counts = [];

        $wpdb->suppress_errors(true);

        // Products live in wp_posts with a custom post_type
        $counts['products'] = (int) (
            $wpdb->get_var(
                "SELECT COUNT(*) FROM `{$wpdb->posts}` WHERE post_type = 'fluent-products'"
            ) ?? 0
        );

        // All other fct_* tables are a simple COUNT(*)
        foreach (self::FCT_TABLES as $key => $suffix) {
            $table        = $wpdb->prefix . $suffix;
            $counts[$key] = (int) ($wpdb->get_var("SELECT COUNT(*) FROM `{$table}`") ?? 0);
        }

        $wpdb->suppress_errors(false);

        return $counts;
    }
}
