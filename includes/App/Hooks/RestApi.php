<?php

namespace AllInOneSeeder\App\Hooks;

use AllInOneSeeder\App\Http\Controllers\FluentCrmController;
use AllInOneSeeder\App\Http\Controllers\PluginsController;

class RestApi
{
    private const NAMESPACE = 'aio-seeder/v1';

    public function register(): void
    {
        add_action('rest_api_init', [$this, 'registerRoutes']);
    }

    public function registerRoutes(): void
    {
        register_rest_route(self::NAMESPACE, '/plugins', [
            'methods'             => 'GET',
            'callback'            => [new PluginsController(), 'index'],
            'permission_callback' => [$this, 'requireAdminPermission'],
        ]);

        register_rest_route(self::NAMESPACE, '/seed/fluent-crm', [
            [
                'methods'             => 'POST',
                'callback'            => [new FluentCrmController(), 'seed'],
                'permission_callback' => [$this, 'requireAdminPermission'],
                'args'                => $this->seedArgs(),
            ],
            [
                'methods'             => 'DELETE',
                'callback'            => [new FluentCrmController(), 'truncate'],
                'permission_callback' => [$this, 'requireAdminPermission'],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/seed/fluent-crm/stats', [
            'methods'             => 'GET',
            'callback'            => [new FluentCrmController(), 'stats'],
            'permission_callback' => [$this, 'requireAdminPermission'],
        ]);
    }

    public function requireAdminPermission(): bool
    {
        return current_user_can('manage_options');
    }

    private function seedArgs(): array
    {
        $intArg = [
            'type'     => 'integer',
            'minimum'  => 0,
            'maximum'  => 5000,
            'required' => false,
        ];

        return [
            'companies'          => $intArg,
            'lists'              => $intArg,
            'tags'               => $intArg,
            'subscribers'        => $intArg,
            'subscriber_notes'   => $intArg,
            'subscriber_meta'    => $intArg,
            'campaigns'          => $intArg,
            'recurring_campaigns' => $intArg,
            'email_sequences'    => $intArg,
            'funnels'            => $intArg,
            'funnel_sequences'   => $intArg,
        ];
    }
}
