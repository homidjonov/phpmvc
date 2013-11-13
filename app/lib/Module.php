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
    protected $_route;
    protected $_name;
    protected $_params;
    protected $_renderers;
    protected $_observers;
    protected $_adminMenu;

    protected $_adminActions = array();

    protected $_predefinedFunctions = array();

    protected static $_modules;
    protected static $_currentTheme;
    protected static $_predefinedFunctionsArray;

    private static $_routes = array();
    protected static $_adminMenuItems = array();

    /**
     * Page title, keywords, description
     */
    protected $_keywords;
    protected $_title;
    protected $_description;


    public function __construct()
    {
        if (!$this->_name) {
            $this->_name = strtolower(get_class($this));
        }
        $this->_params    = new Object();
        $this->_renderers = new Object();
        if ($this->getName() != 'module') {
            self::$_modules[$this->getName()] = $this;
            if ($this->canRoute()) {
                $routes = explode(':', $this->_route);
                foreach ($routes as $route) {
                    self::$_routes[$route] = $this->getName();
                }
            }
        }

        App::addObserver($this->getName(), $this->_observers);

        foreach ($this->_predefinedFunctions as $function) {
            self::$_predefinedFunctionsArray[$function] = $this->getName();
        }


        if (App::getIsDeveloperMode()) {
            $installer = get_class($this) . 'Installer';
            if (class_exists($installer)) {
                $installer = new $installer();
                $installer->installUpdates();
            }
        }

        if (App::isAdmin()) $this->_initAdmin();
        $this->_init();
    }

    protected function getAdminMenu()
    {
        $order = array();
        foreach (self::$_adminMenuItems as $module => $item) {
            $order[$module] = $item['order'];
        }
        array_multisort($order, SORT_ASC, self::$_adminMenuItems);
        return self::$_adminMenuItems;
    }

    protected function addAdminMenu($action, $title, $child = array(), $order = 100,$iconClass)
    {
        self::$_adminMenuItems[$this->getName()] = array('action' => $action, 'title' => $title, 'order' => $order, 'child' => $child,'icon'=>$iconClass);
        return $this;
    }

    protected function _init()
    {

    }

    protected function _initAdmin()
    {
        //App::log(get_class_methods($this));
    }

    protected function getTitle()
    {
        $data = strip_tags($this->_title);
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
        }
        return $params['data'];
    }

    protected static $_instance;


    public function getName()
    {

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

    public function getMessages()
    {
        return $this->getSession()->getMessages();
    }

    protected function getRequest()
    {
        return App::getRequest();
    }

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

    public function isMultipleRoute()
    {
        return strpos($this->_route, ':');
    }

    public function getRoute()
    {
        return $this->_route;
    }


    protected function _preDispatch()
    {
        if ($this->isMultipleRoute() && App::getRequest()->getModule() != $this->getName()) {
            $action = App::getRequest()->getModule() . ucfirst(App::getRequest()->getAction());
            App::getRequest()->setAction($action);
        }
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
        $this->getRequest()->setAction('404');
        $this->render();
    }


    protected function render($params = false)
    {
        if (is_array($params)) $this->_params->addData($params);
        App::runObserver('module_before_render', array('module' => $this));
        ob_start();
        $this->setBodyClassName(App::getRequest()->getFullActionName());
        $this->getPart('template');
        $content = ob_get_contents();
        ob_end_clean();
        App::runObserver('module_after_render', array('module' => $this));
        echo $content;
    }

    public function getParams()
    {
        return $this->_params;
    }

    public function getParam($key, $default = null)
    {
        return $this->_params->getData($key, $default);
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

    public function getRenderer($alias)
    {
        if ($template = $this->_renderers->getData($alias)) {
            return $template;
        }
        return false;
    }

    public function setRenderer($alias, $file)
    {
        $this->_renderers->setData($alias, $file);
        return $this;
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
        // App::log($module);
        //       App::log($action);
        if (isset(self::$_partsContent[$part])) {
            $data = self::$_partsContent[$part];
            App::runObserver('part_before_output', array('part' => $part, 'alias' => $alias, 'data' => &$data));
            echo $data;
            App::runObserver('part_after_output', array('part' => $part, 'alias' => $alias, 'data' => &$data));
            return;
        }
        $default = $this->getRenderer($alias);
        $files   = array(
            ($default) ? $this->getCurrentTemplateDir() . $module . DS . $action . DS . $part . DS . $default : $this->getCurrentTemplateDir() . $module . DS . $action . DS . $part,
            $this->getCurrentTemplateDir() . $module . DS . $action . DS . $part,
            $this->getCurrentTemplateDir() . $module . DS . 'default' . DS . $part,
            $this->getCurrentTemplateDir() . $module . DS . $part,
            $this->getCurrentTemplateDir() . 'page' . DS . $part,
            $this->getCurrentTemplateDir() . $part,
        );

        if (!App::isAdmin()) {
            $files[] = App::getBaseTemplateDir() . $module . DS . $action . DS . $part;
            $files[] = App::getBaseTemplateDir() . $module . DS . 'default' . DS . $part;
            $files[] = App::getBaseTemplateDir() . $module . DS . $part;
            $files[] = App::getBaseTemplateDir() . 'page' . DS . $part;
            $files[] = App::getBaseTemplateDir() . $part;
        }

        $files = array_unique($files);
        //App::log($files);
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

    protected static $_partContents = array();

    public function setPart($part, $content, $isFile = false)
    {
        if (is_object($content)) $content = $content->render();
        self::$_partsContent[$part] = $content;
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

    public function getCss($file)
    {
        $file = $this->getLink('theme/' . $this->getCurrentTheme() . '/' . $file);
        return "<link rel='stylesheet' href='$file'>";
    }

    public function getJs($file)
    {
        $file = $this->getLink('theme/' . $this->getCurrentTheme() . '/' . $file);
        return "<script type='text/javascript' src='$file'></script>";
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
        return $this->getRequest()->getBaseUrl() . trim($link, '/');
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
        $this->_bodyClassName .= "$name ";
        return $this;
    }

    /**
     * @param $word
     * @return mixed
     * Translator
     * TODO translate functionality
     */
    public static function __($word)
    {
        if (App::canTranslateInterface()) {
            $word = self::getTranslator()->translate($word);
            //$word = "<span class='translation'>$word</span>";
        }
        return $word;
    }

    /**
     * @return Translator
     */
    protected static function getTranslator()
    {
        return self::$_modules['translator'];
    }

    protected function forward($action)
    {
        $this->getRequest()->setAction($action);
        $action .= 'Action';
        $this->$action();
    }

    /**
     * @param $method
     * @param $args
     * @return mixed
     * Predefined callable functions
     */
    public function __call($method, $args)
    {
        if (isset(self::$_predefinedFunctionsArray[$method])) {
            $module = self::$_modules[self::$_predefinedFunctionsArray[$method]];
            if (method_exists($module, $method)) return call_user_func_array(array($module, $method), $args);
        }
    }

    /**
     * @param bool $modelName
     * @return Model
     * @throws Exception
     */
    protected function getModel($modelName = false)
    {
        if ($modelName && class_exists($modelName)) {
            return new $modelName();
        }
        $modelName = ucfirst($this->getName()) . 'Model';
        if ($modelName && class_exists($modelName)) {
            return new $modelName();
        }
        throw new Exception("Model class [$modelName] not found.");
    }

}
