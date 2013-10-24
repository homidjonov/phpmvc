<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Shavkat
 * Date: 10/22/13
 * Time: 8:47 PM
 */

define('DS', DIRECTORY_SEPARATOR);
define('APP_DEVELOPER_MODE', true);
define('APP_DEBUG_PARTS', true);
define('APP_TRANSLATE_INTERFACE', true);
define('APP_MODULES_DIR', __DIR__ . DS . 'modules' . DS);
define('APP_CONFIG_DIR', __DIR__ . DS . 'config' . DS);
define('APP_VIEW_DIR', __DIR__ . DS . 'view' . DS);
define('APP_LOG_DIR', __DIR__ . DS . 'log' . DS);
define('APP_DEFAULT_ROUTE', 'page');
define('APP_DEFAULT_DESIGN', 'default'); //default dizayn, huddi shu papkani copy/paste qilib umuman boshqa dizay qursak bo'ladi.
//db login parollari
define('APP_DB_HOST', 'localhost');
define('APP_DB_USERNAME', 'root');
define('APP_DB_PASSWORD', '');
define('APP_DB_DATABASE', 'blog');