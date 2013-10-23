<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Shavkat
 * Date: 10/22/13
 * Time: 7:48 PM
 */


/**
 * Yukovchi fayl, barcha zapros shu yerga kelib tushadi (.htaccess).
 * Asosiy bootstrap fayl bu app/App.php, u modullarni yuklaydi va
 * kerakli modulga boshqaruvni uzatadi.
 *
 * har bir url www.website.com/module/action/param1/value1/param2/value2
 * sifatida qaraladi.
 *
 * ushbu MVC PHP OOP asosida yaratilishi kerak, deyarli barcha modul va
 * yordamchi klasslar singleton patterni asosida yuklanadi.
 */
require_once 'app/App.php';

define('APP_WWW_FOLDER', __DIR__);

$application = new App();
$application->run();