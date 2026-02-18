<?php

use AllInOneSeeder\App\Plugin;

return function ($file) {
    add_action('plugins_loaded', function () {
        (new Plugin())->boot();
    });
};
