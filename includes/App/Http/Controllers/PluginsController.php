<?php

namespace AllInOneSeeder\App\Http\Controllers;

use WP_REST_Request;
use WP_REST_Response;

class PluginsController
{
    public function index(WP_REST_Request $request): WP_REST_Response
    {
        return new WP_REST_Response([
            'plugins' => [
                [
                    'id'          => 'fluent-crm',
                    'name'        => 'FluentCRM',
                    'description' => 'Seed contacts, lists, tags, campaigns, automations and more.',
                    'active'      => defined('FLUENTCRM'),
                    'route'       => '/seed/fluent-crm',
                    'sections'    => [
                        [
                            'label'  => 'Core Data',
                            'fields' => [
                                ['key' => 'companies', 'label' => 'Companies',      'default' => 10],
                                ['key' => 'lists',     'label' => 'Lists',          'default' => 5],
                                ['key' => 'tags',      'label' => 'Tags',           'default' => 10],
                            ],
                        ],
                        [
                            'label'  => 'Contacts',
                            'fields' => [
                                ['key' => 'subscribers',      'label' => 'Subscribers',          'default' => 50],
                                ['key' => 'subscriber_notes', 'label' => 'Notes per Subscriber', 'default' => 2],
                                ['key' => 'subscriber_meta',  'label' => 'Meta per Subscriber',  'default' => 3],
                            ],
                        ],
                        [
                            'label'  => 'Email Campaigns',
                            'fields' => [
                                ['key' => 'campaigns',           'label' => 'Campaigns',           'default' => 5],
                                ['key' => 'recurring_campaigns', 'label' => 'Recurring Campaigns', 'default' => 3],
                                ['key' => 'email_sequences',     'label' => 'Email Sequences',     'default' => 3],
                                ['key' => 'email_templates',     'label' => 'Email Templates',     'default' => 5],
                            ],
                        ],
                        [
                            'label'  => 'Automations',
                            'fields' => [
                                ['key' => 'funnels',          'label' => 'Funnels',          'default' => 3],
                                ['key' => 'funnel_sequences', 'label' => 'Steps per Funnel', 'default' => 5],
                            ],
                        ],
                    ],
                ],
            ],
        ], 200);
    }
}
