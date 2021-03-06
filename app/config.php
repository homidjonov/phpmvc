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
define('APP_DEVELOPER_MODE', 1);
define('APP_DEBUG_PARTS', true);
define('APP_TRANSLATE_INTERFACE', true);
define('APP_BOOTSTRAP_CDN', true);

//folders and paths
define('APP_MODULES_DIR', __DIR__ . DS . 'modules' . DS);
define('APP_VIEW_DIR', __DIR__ . DS . 'template' . DS);
define('APP_THEME_DIR', APP_WWW_FOLDER . DS . 'theme' . DS);
define('APP_TEMP_DIR', APP_WWW_FOLDER . DS . 'temp' . DS);
define('APP_MEDIA_DIR', APP_WWW_FOLDER . DS . 'media' . DS);
define('APP_LOG_DIR', APP_TEMP_DIR . 'log' . DS);
define('APP_SESSION_DIR', APP_TEMP_DIR . 'session' . DS);
define('APP_CACHE_DIR', APP_TEMP_DIR . 'cache' . DS);
define('APP_CACHE_PAGE_DIR', APP_CACHE_DIR . 'page' . DS);
define('APP_CACHE_PART_DIR', APP_CACHE_DIR . 'part' . DS);


define('APP_DEFAULT_TIMEZONE', 'Asia/Tashkent');
define('APP_DEFAULT_LOCALE', 'en_US');
//App::log(DateTimeZone::listIdentifiers());

//defaults
define('APP_DEFAULT_ROUTE', 'page');
define('APP_DEFAULT_THEME', 'responsive');

//db login parolari
define('APP_DB_HOST', 'localhost');
define('APP_DB_USERNAME', 'root');
define('APP_DB_PASSWORD', '');
define('APP_DB_DATABASE', 'blog');

//administrator configs
define('APP_ADMIN_ROUTE', 'adminbox');
define('APP_CACHE_ENABLED', 1);

//modules configs
define('MD_PAGE_POST_LIMIT', 5);

//define('MD_PAGE_DATE_FORMAT', 'Y-m-d');
define('MD_PAGE_DATE_FORMAT', 'd M, Y');


//Config
define('CONFIG_COOKIE_LIFETIME', 1800);
define('CONFIG_DATE_FORMAT', 'd M, Y');
define('CONFIG_DATETIME_FORMAT', 'd M, Y h:m');