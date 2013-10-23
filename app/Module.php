<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Shavkat
 * Date: 10/22/13
 * Time: 8:58 PM
 *
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

    protected static $_instance;


    public function __construct()
    {
        if ($this instanceof Module) self::$_modules[$this->getRoute()] = $this;
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

    public function getModule($name)
    {
        if (isset(self::$_modules[$name])) {
            return self::$_modules[$name];
        }
        Request::getInstance()->setModule('page');
        return self::$_modules['page'];
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

    public function dispatch()
    {
        $action = App::getRequest()->getAction();
        if ($action) {
            $action .= 'Action';
            if (method_exists($this, $action)) {
                return $this->$action();
            }
            $action = 'defaultAction';
            if (method_exists($this, $action)) {
                Request::getInstance()->setAction('default');
                return $this->$action();
            }
        }
        $this->_defaultNoRouteAction();
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

    public static function getParts()
    {
        return self::$_parts;
    }

    public function getPart($part)
    {
        //$part   = str_replace('/', DS, $part);
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
                return include $file;
            }
        }
        $log = array(
            'message'=> "Template file [$part] not found",
            'module' => $module,
            'action' => $action,
        );
    }

    protected $_bodyClassName;

    public function getBodyClassName()
    {
        return $this->_bodyClassName;
    }

    public function setBodyClassName($name)
    {
        $this->_bodyClassName = $name;
        return $this;
    }
}
