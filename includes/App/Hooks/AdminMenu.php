<?php

namespace AllInOneSeeder\App\Hooks;

class AdminMenu
{
    private const MENU_SLUG = 'all-in-one-seeder';

    public function register(): void
    {
        add_action('admin_menu', [$this, 'addMenuPage']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    public function addMenuPage(): void
    {
        add_menu_page(
            __('All-in-One Seeder', 'all-in-one-seeder'),
            __('All-in-One Seeder', 'all-in-one-seeder'),
            'manage_options',
            self::MENU_SLUG,
            [$this, 'renderPage'],
            'dashicons-database-import',
            58
        );
    }

    public function renderPage(): void
    {
        echo '<div class="wrap"><div id="aio-seeder-admin-app"></div></div>';
    }

    public function enqueueAssets(string $hookSuffix): void
    {
        if ($hookSuffix !== 'toplevel_page_' . self::MENU_SLUG) {
            return;
        }

        (new AssetLoader())->enqueue();
    }
}
