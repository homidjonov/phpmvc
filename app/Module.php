<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Shavkat
 * Date: 10/22/13
 * Time: 8:58 PM
 * modules/ direktoriyasidagi har bir modul uchun ota klass hisoblanadi.
 * Ixtiyoriy modul yuklanganda ota klass ro'yhatidan o'tadi.
 */
class Module
{
    protected static $_modules;
    protected $_route;
    protected $_defaultAction;
    protected $_name;
    protected $_params;
    protected $_observers;

    /**
     * Page title, keywords, description
     */
    protected $_keywords;
    protected $_title;
    protected $_description;

    protected function getTitle()
    {
        return $this->_title;
    }

    protected function getKeywords()
    {
        return $this->_keywords;
    }

    protected function getDescription()
    {
        return $this->_description;
    }

    protected static $_instance;


    public function __construct()
    {
        if ($this instanceof Module) self::$_modules[$this->getRoute()] = $this;
        if ($this->_observers) App::addObserver($this->getName(), $this->_observers);
        $this->_init();
    }

    protected function _init()
    {

    }

    public function getName()
    {
        if (!$this->_name) {
            $this->_name = strtolower(get_class($this));
        }
        return $this->_name;
    }

    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new Module();
        }
        return self::$_instance;
    }


    public function getModuleForRoute($name)
    {
        if (isset(self::$_modules[$name])) {
            return self::$_modules[$name];
        }
        Request::getInstance()->setModule('page');
        return self::$_modules['page'];
    }

    /**
     * @param $name
     * @return Module
     */

    public function getModule($name)
    {
        if (isset(self::$_modules[$name])) {
            return self::$_modules[$name];
        }
        throw new Exception("Module [$name] not found");
    }

    public function canRoute()
    {
        return $this->_route != null;
    }

    public function getRoute()
    {
        return $this->_route;
    }

    protected function _defaultNoRouteAction()
    {
        echo "404";
    }

    protected function _preDispatch()
    {

    }

    protected function _postDispatch()
    {

    }

    public function run()
    {
        $this->_preDispatch();
        $action = App::getRequest()->getAction();
        if ($action) {
            $action .= 'Action';
            if (method_exists($this, $action)) {
                $this->$action();
            } else {
                $action = 'defaultAction';
                if (method_exists($this, $action)) {
                    Request::getInstance()->setAction('default');
                    $this->$action();
                }
            }
        } else {
            $this->_defaultNoRouteAction();
        }

        $this->_postDispatch();
    }

    protected function render($params = false)
    {
        $this->_params = $params;
        $this->getPart('template');


    }

    public function getParams()
    {
        return $this->_params;
    }

    public function getParam($key)
    {
        if (isset($this->_params[$key])) return $this->_params[$key];
        return false;
    }

    protected static $_parts = array();
    protected static $_partsContent = array();

    /**
     * @return array
     * part ni barcha modullar view papkasidan yuklash
     */
    public static function getParts()
    {
        return self::$_parts;
    }

    /**
     * @param $part
     * @return mixed
     * Tema va shablonlarni birlashtirish, juda ham qiziq, asosiy maqsad
     * view papkada har bir modul va action uchun templatelarni boshqarsak bo'ladi
     * design/module/action ko'rinishida joylashtirlgan
     * har bir part (qism html) quyidagi ko'rinishda qidiriladi va yuklanadi
     * 1. currentDesign/module/action/part
     * 2. currentDesign/module/part
     * 3. currentDesign/part
     * 4. defaultDesign/module/action/part
     * 5. defaultDesign/module/part
     * 6. defaultDesign/part
     * Kerakli part kamida shu papkalardan birida bo'lishi kerak, birinchi qaysi papkadan
     * topilsa o'sha yuklanadi. (Theme fallback like Magento, but not complicated like it)
     * TODO part urovenda keshlash
     */
    public function getPart($part, $alias = false)
    {
        if (!$alias) $alias = $part;
        $part   = str_replace('/', DS, $part);
        $module = $this->getName();
        $action = App::getRequest()->getAction();

        $files = array(
            APP_DEFAULT_DESIGN . DS . $module . DS . $action . DS . $part,
            APP_DEFAULT_DESIGN . DS . $module . DS . $part,
            APP_DEFAULT_DESIGN . DS . 'page' . DS . 'default' . DS . $part,
            APP_DEFAULT_DESIGN . DS . 'page' . DS . $part,
            APP_DEFAULT_DESIGN . DS . $part,
            'default' . DS . $module . DS . $action . DS . $part,
            'default' . DS . $module . DS . $part,
            'default' . DS . 'page' . DS . 'default' . DS . $part,
            'default' . DS . 'page' . DS . $part,
            'default' . DS . $part,
        );
        $files = array_unique($files);
        foreach ($files as $file) {
            $file = APP_VIEW_DIR . $file . '.phtml';
            if (file_exists($file)) {
                self::$_parts[] = $file;
                App::runObserver('part_before_include', array('part' => $part, 'alias' => $alias, 'file' => &$file));
                if ($file) include $file;
                App::runObserver('part_after_include', array('part' => $part, 'alias' => $alias, 'file' => &$file));
                return true;
            }
        }
        $log = array(
            'message' => "Template file [$part] not found",
            'module'  => $module,
            'action'  => $action,
        );

        App::log($log);
        return false;
    }

    public function getWidget($widget)
    {
        $count = 1;
        $this->getPart(str_replace('/', '/widget/', $widget, $count), $widget);
    }

    public function getPartAll($part)
    {
        $designDir = scandir(APP_VIEW_DIR . APP_DEFAULT_DESIGN);
        foreach ($designDir as $moduleDir) {
            $modulePath = APP_VIEW_DIR . APP_DEFAULT_DESIGN . DS . $moduleDir;
            if (is_dir($modulePath) && $moduleDir != '..' && $moduleDir != '.') {
                $file = $modulePath . DS . $part . '.phtml';
                if (file_exists($file)) {
                    include $file;
                }
            }
        }
    }

    protected $_bodyClassName;

    public function getBodyClassName()
    {
        return $this->_bodyClassName;
    }

    /**
     * @param $name
     * @return Module
     * Page renderdan oldin bodyga klass berib ketsak yaxshiroq
     */
    public function setBodyClassName($name)
    {
        $this->_bodyClassName = $name;
        return $this;
    }

    /**
     * @param $word
     * @return mixed
     * Translator
     * TODO translate functionality
     */
    public function __($word)
    {
        $word = $this->getTranslator()->translate($word);
        if (App::canTranslateInterface()) {
            $word = "<span class='translation'>$word</span>";
        }
        return $word;
    }

    /**
     * @return Translator
     */
    protected function getTranslator()
    {
        return self::$_modules['translator'];
    }
}
