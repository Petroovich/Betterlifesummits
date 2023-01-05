<?php
/**
 * Plugin Name: Custom Modules For Beaver Builder
 * Plugin URI: #
 * Description: A plugin for creating custom builder modules.
 * Version: 1.0
 * Author: Armen Khojoyan
 * Author URI: https://digidez.com
 */
define('FL_BB_CUSTOM_MODULES_DIR', plugin_dir_path(__FILE__));
define('FL_BB_CUSTOM_MODULES_URL', plugins_url('/', __FILE__));

require_once FL_BB_CUSTOM_MODULES_DIR.'classes/BBCMDatasource.php';
require_once FL_BB_CUSTOM_MODULES_DIR.'classes/BBCMLoader.php';