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
    protected $_observers;
    protected $_adminMenu;

    protected static $_renderers;
    protected static $_params;
    protected static $_partsContent;

    protected $_adminActions = array();

    protected $_predefinedFunctions = array();

    protected static $_modules;
    protected static $_currentTheme;
    protected static $_predefinedFunctionsArray;

    private static $_routes = array();
    protected static $_adminMenuItems = array();
    protected $_bodyClassName;

    /**
     * Page title, keywords, description
     */
    protected $_keywords;
    protected $_title;
    protected $_description;

    protected static $_instance;

    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance     = new Module();
            self::$_currentTheme = App::getCurrentTheme();
        }
        return self::$_instance;
    }

    public function __construct()
    {
        $this->_name = strtolower(get_class($this));

        self::$_params       = new Object();
        self::$_renderers    = new Object();
        self::$_partsContent = new Object();

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

    protected function _init()
    {
    }

    protected function _initAdmin()
    {
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

    public function getActions()
    {
        if (isset(self::$_adminMenuItems[$this->getName()])) {
            return self::$_adminMenuItems[$this->getName()];
        }
        return false;
    }

    protected function addAdminMenu($action, $title, $child = array(), $order = 100, $iconClass)
    {
        self::$_adminMenuItems[$this->getName()] = array('action' => $action, 'title' => $title, 'order' => $order, 'child' => $child, 'icon' => $iconClass);
        return $this;
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

    /**
     * @param $name
     * @return Module | Page | Admin
     */
    public function getModuleForRoute($name)
    {
        if (isset(self::$_routes[$name])) {
            return self::$_modules[self::$_routes[$name]];
        }
        Request::getInstance()->setModule('page');
        return self::$_modules['page'];
    }

    /**
     * @param $name
     * @return Module
     * @throws Exception
     */
    public function getModule($name)
    {
        if (isset(self::$_modules[$name])) {
            return self::$_modules[$name];
        }
        throw new Exception("Module [$name] not found");
    }

    /**
     * @return AdminSession|Session
     */
    protected function getSession()
    {
        if (App::isAdmin()) {
            return AdminSession::getInstance();
        }
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

    public function getName()
    {
        return $this->_name;
    }

    public function getBodyClassName()
    {
        return $this->_bodyClassName;
    }

    public function setBodyClassName($name)
    {
        $this->_bodyClassName .= "$name ";
        return $this;
    }

    protected function _preDispatch()
    {
        if ($this->isMultipleRoute() && App::getRequest()->getModule() != $this->getName()) {
            $action = App::getRequest()->getModule() . ucfirst(App::getRequest()->getAction());
            App::getRequest()->setAction($action);
        }
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

    protected function _postDispatch()
    {

    }

    protected function _defaultNoRouteAction()
    {
        $this->getRequest()->setAction('404');
        $this->render();
    }

    protected function render($params = array())
    {
        self::$_params->addData($params);
        App::runObserver('module_before_render', array('module' => $this));
        ob_start();
        $this->setBodyClassName(App::getRequest()->getFullActionName());
        $this->getPart('template');
        $content = ob_get_contents();
        ob_end_clean();
        App::runObserver('module_after_render', array('module' => $this, 'content' => &$content));
        echo $content;
    }

    public function getParams()
    {
        return self::$_params;
    }

    public function getParam($key, $default = null)
    {
        return self::$_params->getData($key, $default);
    }


    public function setRenderer($alias, $file)
    {
        self::$_renderers->setData($alias, $file);
        return $this;
    }

    public function setPart($part, $content)
    {
        if (is_object($content)) $content = $content->render();
        self::$_partsContent->setData($part, $content);
    }


    /**
     * @param $part
     * @return mixed
     * Tema va shablonlarni birlashtirish, juda ham qiziq, asosiy maqsad
     * view papkada har bir modul va action uchun templatelarni boshqarsak bo'ladi
     * design/module/action ko'rinishida joylashtirlgan
     * har bir part (qism html) quyidagi ko'rinishda qidiriladi va yuklanadi
     * 1. currentDesign/module/action/renderer/part     if has some renderer
     * 2. currentDesign/module/action/part
     * 3. currentDesign/module/part
     * 4. currentDesign/page/part
     * 5. currentDesign/part
     * 6. defaultDesign/module/action/renderer/part     if has some renderer
     * 7. defaultDesign/module/action/part
     * 8. defaultDesign/module/part
     * 9. defaultDesign/page/part
     * 10. defaultDesign/part
     * Kerakli part kamida shu papkalardan birida bo'lishi kerak, birinchi qaysi papkadan
     * topilsa o'sha yuklanadi. (Theme fallback like Magento, but not complicated like it)
     * TODO part urovenda keshlash, balki ortiqcha ish kerakmasdir, chunki butun sahifani keshlash borku
     */
    public function getPart($part, $alias = false)
    {
        if ($alias == false) $alias = $part;
        $part = str_replace('/', DS, $part);
        if ($this->canIncludeAlreadyDefinedParts($part, $alias)) return true;
        $files = $this->getTemplateFileList($part, $alias);
        foreach ($files as $file) {
            $file = $file . '.phtml';
            if (file_exists($file)) {
                App::runObserver('part_before_include', array('part' => $part, 'alias' => $alias, 'file' => &$file));
                if ($file) include $file;
                App::runObserver('part_after_include', array('part' => $part, 'alias' => $alias, 'file' => &$file));
                return true;
            }
        }
        if (App::getIsDeveloperMode()) {
            $module = $this->getName();
            $action = App::getRequest()->getAction();
            throw new Exception("Template file [$part] not found (module: [$module]\t action: [$action]).");
        }
        return false;
    }

    protected function canIncludeAlreadyDefinedParts($part, $alias = false)
    {
        if ($content = self::$_partsContent->getData($part)) {
            App::runObserver('part_before_output', array('part' => $part, 'alias' => $alias, 'data' => &$content));
            echo $content;
            App::runObserver('part_after_output', array('part' => $part, 'alias' => $alias, 'data' => &$content));
            return true;
        }
        return false;
    }

    protected function getTemplateFileList($part, $alias = false)
    {
        $module          = $this->getName();
        $action          = App::getRequest()->getAction();
        $dynamicRenderer = self::$_renderers->getData($alias);
        $currentTemplate = $this->getCurrentTemplateDir();

        $files = array(
            ($dynamicRenderer) ? $currentTemplate . $module . DS . $action . DS . $part . DS . $dynamicRenderer : $currentTemplate . $module . DS . $action . DS . $part,
            $currentTemplate . $module . DS . $action . DS . $part,
            $currentTemplate . $module . DS . $part,
            $currentTemplate . 'page' . DS . $part,
            $currentTemplate . $part,
        );

        if (!App::isAdmin()) {
            $baseTemplate = App::getBaseTemplateDir();
            if ($dynamicRenderer) $files[] = $baseTemplate . $module . DS . $action . DS . $part . DS . $dynamicRenderer;
            $files[] = $baseTemplate . $module . DS . $action . DS . $part;
            $files[] = $baseTemplate . $module . DS . $part;
            $files[] = $baseTemplate . 'page' . DS . $part;
            $files[] = $baseTemplate . $part;
        }
        return $files;
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

    public function getUrl($link, $params = array())
    {
        return App::getUrl($link, $params);
    }

    public function getAdminUrl($link, $params = array())
    {
        return App::getAdminUrl($link, $params);
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

    protected function redirect($url)
    {
        $this->getRequest()->redirect($url);
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
        if (!$modelName) $modelName = $this->getName() . 'Model';

        if (class_exists($modelName)) {
            return new $modelName();
        }
        throw new Exception("Model class [$modelName] not found.");
    }

}
