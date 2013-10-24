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

    protected static $_instance;


    public function __construct()
    {
        if ($this instanceof Module) self::$_modules[$this->getRoute()] = $this;
        if ($this->_observers) App::addObserver($this->getName(), $this->_observers);
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
     * har bir part (qism html) quyidagi ko'rinishda qidiriladi va yuklanad
     * 1. currentDesign/module/action/part
     * 2. currentDesign/module/part
     * 3. currentDesign/part
     * 4. defaultDesign/module/action/part
     * 5. defaultDesign/module/part
     * 6. defaultDesign/part
     * Kerakli part kamida shu papkalardan birida bo'lishi kerak, birinchi qaysi papkadan
     * topilsa o'sha yuklanadi. (Theme fallback like Magento, but not complicated like it)
     */
    public function getPart($part)
    {
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
                App::runObserver('part_before_include', array('part' => $part, 'file' => &$file));
                if ($file) include $file;
                App::runObserver('part_after_include', array('part' => $part, 'file' => &$file));
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
}
