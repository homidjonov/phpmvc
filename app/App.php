<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Shavkat
 * Date: 10/22/13
 * Time: 8:48 PM
 */
class App
{
    protected static $_moduleManager;
    protected static $_requestManager;
    protected static $_dbManager;
    protected static $_modelManager;

    /**
     * avtoyuklanuvchi fayllar
     * @var array
     */
    protected $_autoloads = array(
        'config' => 'config.php',
        'db'     => 'Db.php',
        'request'=> 'Request.php',
        'module' => 'Module.php',
        'model'  => 'Model.php',
    );

    public function __construct()
    {

        foreach ($this->_autoloads as $file) {
            require_once $file;
        }

        if (APP_DEVELOPER_MODE) {
            ini_set('display_errors', 'on');
            ini_set('error_reporting', E_ALL);
        }
        $this->loadModules();
    }

    /**
     * app/Modules direktoriyasi skan qilinadi va u yerdagi har bir php fayl Modul hisoblanadi
     * har bir Modul bitta routga javob beradi, batafsil app/modules/Page.php qarang
     */
    public function loadModules()
    {
        $found   = array();
        $modules = scandir(APP_MODULES_DIR);
        foreach ($modules as $moduleFile) {
            if (is_file(APP_MODULES_DIR . $moduleFile)) {
                require_once APP_MODULES_DIR . $moduleFile;
                $class         = substr($moduleFile, 0, strpos($moduleFile, '.php'));
                $found[$class] = $class;
            }
        }
        /**
         * @var $module Module
         */
        $routes = array();
        foreach ($found as $id=> $class) {
            $module = new $class();
            if ($module->canRoute()) {
                $routes[$module->getRoute()] = $class;
            }
        }
        self::$_moduleManager  = Module::getInstance();
        self::$_requestManager = Request::getInstance();
        self::$_dbManager      = Db::getInstance();
        self::$_modelManager   = Model::getInstance();
    }

    /**
     * Modullarnui boshqaruvchi class
     * @return Module
     */
    public function getModuleManager()
    {
        return self::$_moduleManager;
    }

    /**
     * @return Request
     */
    public static function getRequest()
    {
        return self::$_requestManager;
    }

    /**
     * @return Db
     */
    public static function getDb()
    {
        return self::$_dbManager;
    }

    static public function getDefaultRoute()
    {
        return APP_DEFAULT_ROUTE;
    }

    static public function getIsDeveloperMode()
    {
        return APP_DEVELOPER_MODE;
    }

    /**
     * Route obyektidan kerakli modul routi ni olamiz va shu routga javob beruvchi Modul mavjud
     * bo'lsa boshqaruvni unga uzatamiz.
     */
    public function run()
    {
        $route  = $this->getRequest()->getModule();
        $module = $this->getModuleManager()->getModule($route);
        /**
         * TODO route urovenida keshlash logikasini qilish kerak
         */
        try {
            $module->dispatch();
        } catch (Exception $e) {
            if (APP_DEVELOPER_MODE) {
                echo "<pre>" . $e->getTraceAsString();
            }
        }
        self::log(self::getModuleManager()->getParts());
    }

    public static function log($object)
    {
        if (self::getRequest()->getParam('log') == 1) {

            $file = 'system.log';
            if ($object instanceof Exception) {
                $string = $object->getTraceAsString();
                $file   = 'exception.log';
            } elseif (is_array($object)) {
                $string = print_r($object, true);
            } else {
                $string = $object;
            }
            file_put_contents(APP_LOG_DIR . $file, date('d-m-Y h:s:i') . "\n", FILE_APPEND);
            file_put_contents(APP_LOG_DIR . $file, $string, FILE_APPEND);
        }
    }
}
