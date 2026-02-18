<?php defined('ABSPATH') || exit;

/**
 * Plugin Name:  All-in-One Seeder
 * Plugin URI:   https://tanbirahmod.com
 * Description:  Admin tools and seeders with a Vue 3 powered panel.
 * Version:      1.3.10
 * Author:       
 * Author URI:   https://tanbirahmod.com
 * License:      GPLv2 or later
 * Text Domain:  all-in-one-seeder
 * Domain Path:  /language
 */

if (defined('ALL_IN_ONE_SEEDER')) {
    return;
}

define('ALL_IN_ONE_SEEDER', 'all-in-one-seeder');
define('AIOS_PLUGIN_FILE', __FILE__);
define('AIOS_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('AIOS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('AIOS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AIOS_PLUGIN_VERSION', '1.3.10');

require_once AIOS_PLUGIN_PATH . 'includes/Autoloader.php';

AllInOneSeeder\Autoloader::register();

call_user_func(function ($bootstrap) {
    $bootstrap(__FILE__);
}, require AIOS_PLUGIN_PATH . 'boot/app.php');
