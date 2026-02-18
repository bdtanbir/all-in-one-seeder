<?php

namespace AllInOneSeeder\App;

use AllInOneSeeder\App\Hooks\AdminMenu;
use AllInOneSeeder\App\Hooks\RestApi;

class Plugin
{
    public function boot(): void
    {
        (new AdminMenu())->register();
        (new RestApi())->register();
    }
}
