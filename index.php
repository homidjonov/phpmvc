<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Shavkat
 * Date: 10/22/13
 * Time: 7:48 PM
 */

require_once 'app/App.php';

define('APP_WWW_FOLDER', __DIR__);

$application = new App();
$application->run();