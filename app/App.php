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
    protected static $_sessionManager;

    /**
     * avtoyuklanuvchi fayllar
     * @var array
     */
    protected $_autoloads = array(
        'config'  => 'config.php',
        'db'      => 'Db.php',
        'request' => 'Request.php',
        'module'  => 'Module.php',
        'model'   => 'Model.php',
        'session' => 'Session.php',
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

        try {
            $this->loadModules();
        } catch (Exception $e) {
            if (self::getIsDeveloperMode()) {
                echo "<pre>" . $e->getTraceAsString();
            }
        }
    }

    /**
     * app/Modules direktoriyasi skan qilinadi va u yerdagi har bir php fayl Modul hisoblanadi
     * har bir Modul bitta routga javob beradi, batafsil app/modules/Page.php qarang
     */
    public function loadModules()
    {

        self::$_sessionManager = Session::getInstance();
        self::$_moduleManager  = Module::getInstance();
        self::$_requestManager = Request::getInstance();
        self::$_dbManager      = Db::getInstance();

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
        foreach ($found as $id => $class) {
            $module = new $class();
            if ($module->canRoute()) {
                $routes[$module->getRoute()] = $class;
            }
        }

        self::$_modelManager = Model::getInstance();
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
     * @return Session
     */
    public static function getSession()
    {
        return self::$_sessionManager;
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

    static public function canDebugParts()
    {
        return APP_DEBUG_PARTS;
    }

    static public function canTranslateInterface()
    {
        return APP_TRANSLATE_INTERFACE;
    }

    /**
     * Route obyektidan kerakli modul routi ni olamiz va shu routga javob beruvchi Modul mavjud
     * bo'lsa boshqaruvni unga uzatamiz.
     */
    public function run()
    {
        /**
         * @var $module Module
         */
        $route = $this->getRequest()->getModule();
        /**
         * TODO route urovenida keshlash logikasini qilish kerak
         */
        try {
            $module = $this->getModuleManager()->getModuleForRoute($route);
            App::runObserver('module_before_run', array('module' => &$module));
            if ($module) {
                $module->run();
                App::runObserver('module_after_run', array('module' => &$module));
            }
        } catch (Exception $e) {
            if (self::getIsDeveloperMode()) {
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

    static protected $_observers = array();

    static public function runObserver($observerName, $params)
    {
        if (isset(self::$_observers[$observerName])) {
            foreach (self::$_observers[$observerName] as $module) {
                $instance = self::getModuleManager()->getModule($module);
                if (method_exists($instance, $observerName)) {
                    $instance->$observerName($params);
                }
            }
        }
    }

    static public function addObserver($module, $observers)
    {
        foreach ($observers as $observerName) {
            if (!isset(self::$_observers[$observerName])) self::$_observers[$observerName] = array();
            self::$_observers[$observerName][] = $module;
        }
    }
}
