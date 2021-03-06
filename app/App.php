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
    public static $_hasTranslator = false;

    /**
     * avtoyuklanuvchi fayllar
     * @var array
     */
    protected $_autoloads = array(
        'db'         => 'Db.php',
        'request'    => 'Request.php',
        'module'     => 'Module.php',
        'object'     => 'Object.php',
        'model'      => 'Model.php',
        'cookie'     => 'Cookie.php',
        'session'    => 'Session.php',
        'form'       => 'Form.php',
        'pagination' => 'Pagination.php',
        'grid'       => 'Grid.php',
    );

    public function __construct()
    {
        set_error_handler(create_function('$severity, $message, $file, $line', 'throw new ErrorException($message." on line ".$line, $severity, $severity, $file, $line);'));

        require_once 'config.php';
        foreach ($this->_autoloads as $file) {
            require_once "lib" . DS . $file;
        }

        if (self::getIsDeveloperMode()) {
            ini_set('display_errors', 'on');
            ini_set('error_reporting', E_ALL);
        } else {
            ini_set('display_errors', 'off');
            ini_set('error_reporting', E_ERROR);
        }
        date_default_timezone_set(APP_DEFAULT_TIMEZONE);
        setlocale(LC_ALL, APP_DEFAULT_LOCALE);
    }

    /**
     * app/Modules direktoriyasi skan qilinadi va u yerdagi har bir php fayl Modul hisoblanadi
     * har bir Modul bitta routga javob beradi, batafsil app/modules/Page.php qarang
     */
    public function loadModules()
    {

        self::$_requestManager = Request::getInstance();
        self::$_dbManager      = Db::getInstance();
        self::$_modelManager   = Model::getInstance();
        ///self::$_sessionManager = Session::getInstance(); //user and admin session separated
        if (self::getIsDeveloperMode()) {
            /*install updates */
            Model::getInstance()->installUpdates();
        }

        $found   = array();
        $modules = scandir(APP_MODULES_DIR);
        foreach ($modules as $moduleFile) {
            if (is_file(APP_MODULES_DIR . $moduleFile)) {
                require_once APP_MODULES_DIR . $moduleFile;
                $class         = substr($moduleFile, 0, strpos($moduleFile, '.php'));
                $found[$class] = new $class();
            }
        }

        self::$_moduleManager = Module::getInstance();


    }

    /**
     * Modullarnui boshqaruvchi class
     * @return Module
     */
    public static function getModuleManager()
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
        return APP_TRANSLATE_INTERFACE && self::$_hasTranslator;
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
        try {
            $this->loadModules();
            $route = $this->getRequest()->getModule();
            /**
             * TODO route urovenida keshlash logikasini qilish kerak
             */
            $module = $this->getModuleManager()->getModuleForRoute($route);
            App::runObserver('module_before_run', array('module' => &$module));
            if ($module) {
                $module->run();
                App::runObserver('module_after_run', array('module' => &$module));
            }
        } catch (Exception $e) {
            if (self::getIsDeveloperMode()) {
                echo "<pre>";
                print_r($e->getMessage());
                echo "\n";
                print_r($e->getTraceAsString());
            } else {
                echo "Something is wrong!  :)"; //Default error page
            }
            self::log($e);
        }
    }

    public static function log($object, $force = false, $logFile = false)
    {
        if (App::getIsDeveloperMode() || $force) {
            $file   = 'system.log';
            $string = "DATE: \t" . date('d-m-Y h:s:i') . "\n";
            if ($object instanceof Exception) {
                $string .= "URL: \t" . $_SERVER['REQUEST_URI'] . "\nMESS: \t" . $object->getMessage() . " in " . $object->getFile() . " on line " . $object->getLine() . "\n";
                $string .= print_r($object->getTraceAsString(), true) . "\n";
                $file = 'exception.log';
            } elseif (is_array($object)) {
                $string .= print_r($object, true);
            } else {
                $string .= $object;
            }
            file_put_contents(APP_LOG_DIR . (($logFile) ? $logFile : $file), "$string\n", FILE_APPEND);
        }
    }

    static protected $_observers = array();

    static public function runObserver($observerName, $params)
    {
        if (is_array($params)) $params = new Object($params);
        if (isset(self::$_observers[$observerName])) {
            foreach (self::$_observers[$observerName] as $module) {
                $instance = self::getModuleManager()->getModule($module);
                if (method_exists($instance, $observerName)) {
                    call_user_func_array(array($instance, $observerName), array($params));
                }
            }
        }
    }

    static public function addObserver($module, $observers)
    {
        if (empty($observers)) return false;
        foreach ($observers as $observerName) {
            if (!isset(self::$_observers[$observerName])) self::$_observers[$observerName] = array();
            self::$_observers[$observerName][] = $module;
        }
        return true;
    }

    static public function isAdmin()
    {
        return self::getRequest()->isAdmin();
    }

    static public function getCurrentTheme()
    {
        return (self::isAdmin()) ? 'admin' : APP_DEFAULT_THEME;
    }

    static public function getBaseTheme()
    {
        return 'default';
    }

    static public function getTemplateDir()
    {
        return APP_VIEW_DIR;
    }

    static public function getMediaDir($dir = false)
    {
        if ($dir) {
            return APP_MEDIA_DIR . $dir . DS;
        }
        return APP_MEDIA_DIR;
    }

    public static function getAdminRoute()
    {
        return APP_ADMIN_ROUTE;
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

    protected static $_singletons = array();

    public static function getSingleton($className)
    {
        $className = ucfirst($className);
        if (!class_exists($className)) throw new Exception("Class [$className] not found");
        if (!isset(self::$_singletons[$className])) {
            self::$_singletons[$className] = new $className();
        }

        return self::$_singletons[$className];
    }

    public static function getUrl($link, $params = array())
    {
        $url = self::getRequest()->getBaseUrl() . trim($link, '/');
        if (count($params)) {
            $params = http_build_query($params);
            $url .= '?' . $params;
        }
        return $url;
    }

    public static function getAdminUrl($link, $params = array())
    {
        return self::getUrl(self::getAdminRoute() . "/" . $link, $params);
    }

    public static function getMediaUrl($link)
    {
        return self::getUrl("media/" . $link);
    }

    public static function formatDate($date)
    {
        return date(CONFIG_DATE_FORMAT, strtotime($date));
    }

    public static function formatDateTime($date)
    {
        return date(CONFIG_DATETIME_FORMAT, strtotime($date));
    }
}
