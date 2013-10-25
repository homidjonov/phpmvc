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
        try {
            $this->loadModules();
            /**
             * @var $module Module
             */
            $route = $this->getRequest()->getModule();
            /**
             * TODO route urovenida keshlash logikasini qilish kerak
             */

            $module = $this->getModuleManager()->getModuleForRoute($route);
            App::runObserver('module_before_run', array('module' => &$module));
            if ($module) {
                $module->run();
                App::runObserver('module_after_run', array('module' => &$module));
                //self::log(self::getModuleManager()->getParts());
            }
        } catch (Exception $e) {
            if (self::getIsDeveloperMode()) {
                echo "<pre>" . $e->getMessage();
            } else {
                echo "Something is wrong!  :)"; //Default error page
            }
            self::log($e);
        }
    }

    public static function log($object, $force = false, $logFile = false)
    {
        if (App::getIsDeveloperMode() || $force) {
            $file = 'system.log';
            if ($object instanceof Exception) {
                $string = $object->getMessage() . " in " . $object->getFile() . " on line " . $object->getLine() . "\n";
                $string .= print_r($object->getTraceAsString(), true) . "\n";
                $string .= "URL: \t" . $_SERVER['REQUEST_URI'] . "\n";
                $file = 'exception.log';
            } elseif (is_array($object)) {
                $string = print_r($object, true);
            } else {
                $string = $object;
            }
            file_put_contents(APP_LOG_DIR . (($logFile) ? $logFile : $file), date('d-m-Y h:s:i') . "\n" . "$string\n", FILE_APPEND);
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

    static public function getCurrentTheme()
    {
        return APP_DEFAULT_THEME;
    }

    static public function getBaseTheme()
    {
        return 'default';
    }

    static public function getTemplateDir()
    {
        return APP_VIEW_DIR;
    }


    static public function getThemeDir()
    {
        return APP_THEME_DIR;
    }

    static public function getCurrentTemplateDir()
    {
        return self::getTemplateDir() . self::getCurrentTheme() . DS;
    }

    static public function getBaseTemplateDir()
    {
        return self::getTemplateDir() . self::getBaseTheme() . DS;
    }

    static public function getCurrentThemeDir()
    {
        return self::getThemeDir() . self::getCurrentTheme() . DS;
    }

    static public function getBaseThemeDir()
    {
        return self::getThemeDir() . self::getBaseTheme() . DS;
    }
}
