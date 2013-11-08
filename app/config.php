<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Shavkat
 * Date: 10/22/13
 * Time: 8:47 PM
 */

/**
 * TODO bazi konfiguratsiyalarni adminka tugagandan so'ng adminkaga ko'chiriladi
 */
define('DS', DIRECTORY_SEPARATOR);

//App development specific configurations
define('APP_DEVELOPER_MODE', true);
define('APP_DEBUG_PARTS', true);
define('APP_TRANSLATE_INTERFACE', false);
define('APP_BOOTSTRAP_CDN', true);

//folders and paths
define('APP_MODULES_DIR', __DIR__ . DS . 'modules' . DS);
define('APP_VIEW_DIR', __DIR__ . DS . 'template' . DS);
define('APP_THEME_DIR', APP_WWW_FOLDER . DS . 'theme' . DS);
define('APP_TEMP_DIR', APP_WWW_FOLDER . DS . 'temp' . DS);
define('APP_LOG_DIR', APP_TEMP_DIR . 'log' . DS);
define('APP_SESSION_DIR', APP_TEMP_DIR . 'session' . DS);
define('APP_CACHE_DIR', APP_TEMP_DIR . 'cache' . DS);
define('APP_CACHE_PAGE_DIR', APP_CACHE_DIR . 'page' . DS);
define('APP_CACHE_PART_DIR', APP_CACHE_DIR . 'part' . DS);

//defaults
define('APP_DEFAULT_ROUTE', 'page');
define('APP_DEFAULT_THEME', 'responsive');


//db login parollari
define('APP_DB_HOST', 'localhost');
define('APP_DB_USERNAME', 'root');
define('APP_DB_PASSWORD', '');
define('APP_DB_DATABASE', 'blog1');

//administrator configs
define('APP_ADMIN_ROUTE', 'adminbox');
define('APP_CACHE_ENABLED', false);

//modules configs
define('MD_PAGE_POST_LIMIT', 5);
//define('MD_PAGE_DARE_FORMAT', 'Y-m-d');
define('MD_PAGE_DARE_FORMAT', 'd M, Y');