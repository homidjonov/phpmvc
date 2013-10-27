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
    protected static $_currentTheme;

    private static $_routes = array();

    /**
     * Page title, keywords, description
     */
    protected $_keywords;
    protected $_title;
    protected $_description;

    protected function getTitle()
    {
        $data = $this->_title;
        return $this->getMetaData($data, 'title');
    }

    protected function getKeywords()
    {
        $data = $this->_keywords;
        return $this->getMetaData($data, 'keywords');
    }

    protected function getDescription()
    {
        $data = $this->_description;
        return $this->getMetaData($data, 'description');
    }

    protected function getMetaData($data, $type = false)
    {
        $params = array('data' => &$data, 'type' => $type);
        if ($type) {
            //Is it necessary to add event observer to only meta tags? I don't now really.
            //App::runObserver('load_meta_' . $type, $params);
        }
        return $params['data'];
    }

    protected static $_instance;


    public function __construct()
    {
        if ($this->getName() != 'module') {
            self::$_modules[$this->getName()] = $this;
            if ($this->canRoute()) {
                self::$_routes[$this->_route] = $this->getName();
            }
        }
        if (!empty($this->_observers)) App::addObserver($this->getName(), $this->_observers);
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
            self::$_instance     = new Module();
            self::$_currentTheme = App::getCurrentTheme();
        }
        return self::$_instance;
    }


    public function getModuleForRoute($name)
    {
        if (isset(self::$_routes[$name])) {
            return self::$_modules[self::$_routes[$name]];
        }
        Request::getInstance()->setModule('page');
        return self::$_modules['page'];
    }

    protected function getSession()
    {
        return Session::getInstance();
    }

    protected function getRequest()
    {
        return App::getRequest();
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
                    $this->$action();
                } else {
                    $this->_defaultNoRouteAction();
                }
            }
        }

        $this->_postDispatch();
    }

    protected function _defaultNoRouteAction()
    {
        $this->getPart('404');
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
            $this->getCurrentTemplateDir() . $module . DS . $action . DS . $part,
            $this->getCurrentTemplateDir() . $module . DS . $part,
            $this->getCurrentTemplateDir() . 'page' . DS . $part,
            $this->getCurrentTemplateDir() . $part,
            App::getBaseTemplateDir() . $module . DS . $action . DS . $part,
            App::getBaseTemplateDir() . $module . DS . $part,
            App::getBaseTemplateDir() . 'page' . DS . $part,
            App::getBaseTemplateDir() . $part,
        );
        $files = array_unique($files);
        App::log($files);
        foreach ($files as $file) {
            $file = $file . '.phtml';
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

    public function getPartAll($part, $alias = false)
    {
        if (!$alias) $alias = $part;
        $modules    = array_keys(self::$_modules);
        $designDirs = array();
        foreach ($modules as $module) {
            $designDirs[] = App::getCurrentTemplateDir() . $module . DS . $part . '.phtml';
            $designDirs[] = App::getBaseTemplateDir() . $module . DS . $part . '.phtml';
        }
        for ($i = 0; $i < count($designDirs); $i++) {
            $file = $designDirs[$i];
            if (file_exists($file)) {
                App::runObserver('part_before_include', array('part' => $part, 'alias' => $alias, 'file' => &$file));
                if ($file) include $file;
                App::runObserver('part_after_include', array('part' => $part, 'alias' => $alias, 'file' => &$file));
                if ($i % 2 == 0) $i++;
            }
        }

    }

    public function getThemeFileLink($fileLink)
    {
        $file     = trim(str_replace('/', DS, $fileLink), DS);
        $fileLink = trim(str_replace(DS, '/', $fileLink), '/');
        $files    = array(
            $this->getCurrentThemeDir() . $file => $this->getLink('theme/' . $this->getCurrentTheme() . '/' . $fileLink),
            App::getBaseThemeDir() . $file      => $this->getLink('theme/' . App::getBaseTheme() . '/' . $fileLink),
        );
        foreach ($files as $file => $link) {
            if (file_exists($file)) return $link;
        }
        return $link;
    }

    protected function getCurrentTheme()
    {
        return self::$_currentTheme;
    }

    protected function getCurrentThemeDir()
    {
        return App::getThemeDir() . $this->getCurrentTheme() . DS;
    }

    protected function getCurrentTemplateDir()
    {
        return App::getTemplateDir() . $this->getCurrentTheme() . DS;
    }

    protected function getLink($part)
    {
        return App::getRequest()->getBaseUrl() . $part;
    }

    public function getUrl($link)
    {
        return $this->getRequest()->getBaseUrl() . $link . '/';
    }

    public function getAdminUrl($link)
    {
        return $this->getUrl(APP_ADMIN_ROUTE . "/" . $link);
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
