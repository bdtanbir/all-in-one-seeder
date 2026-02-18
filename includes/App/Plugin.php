<?php

namespace AllInOneSeeder\App;

use AllInOneSeeder\App\Hooks\AdminMenu;

class Plugin
{
    public function boot(): void
    {
        (new AdminMenu())->register();
    }
}
