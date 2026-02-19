<?php

namespace AllInOneSeeder\App\Http\Controllers;

use WP_REST_Request;
use WP_REST_Response;

class FluentCartController
{
    public function seed(WP_REST_Request $request): WP_REST_Response
    {
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Not implemented yet.',
        ], 501);
    }

    public function truncate(WP_REST_Request $request): WP_REST_Response
    {
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Not implemented yet.',
        ], 501);
    }

    public function stats(WP_REST_Request $request): WP_REST_Response
    {
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Not implemented yet.',
        ], 501);
    }
}
