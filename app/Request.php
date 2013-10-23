<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Shavkat
 * Date: 10/22/13
 * Time: 9:49 PM
 */
class Request
{
    protected $_request;
    protected $_host;
    protected $_baseUrl;
    protected $_isSecure;
    protected $_moduleRoute;
    protected $_moduleAction;
    protected $_query;
    protected $_queryParams;
    protected $_defaultRoute;
    protected $_getParams;
    protected static $_instance;

    /**
     * Url parser
     * Bilganimdek yozdim, balki kamchiligi bordir hozicha ishlayabdi, agar
     * optimallashtirish kerak bo'ladi qachonlardir. Regular expression ma'qulroqdir?
     */
    public function __construct()
    {
        $this->_request  = strtolower($_SERVER['REQUEST_URI']);
        $this->_isSecure = $_SERVER['SERVER_PORT'] == 443;
        $this->_query    = strtolower($_SERVER['QUERY_STRING']);
        $this->_host     = strtolower((($this->_isSecure) ? 'https://' : 'http://') . trim($_SERVER['HTTP_HOST'], '/') . '/');
        $this->_baseUrl  = strtolower($this->_host . trim(trim($_SERVER['SCRIPT_NAME'], 'index.php'), '/') . '/');

        $this->_moduleRoute  = App::getDefaultRoute();
        $this->_moduleAction = 'default';

        $params = array();
        if (isset($_SERVER['REDIRECT_URL']) && $_SERVER['REDIRECT_URL']) {
            $this->_defaultRoute = strtolower(trim($_SERVER['REDIRECT_URL'], rtrim($_SERVER['SCRIPT_NAME'], 'index.php')));
            $parts               = explode('/', $this->_defaultRoute);
            if ($parts[0]) $this->_moduleRoute = $parts[0];
            $c = count($parts);

            if ($c > 1) {
                if ($parts[1]) $this->_moduleAction = $parts[1];
                if ($c > 2) {
                    for ($i = 2; $i < $c; $i += 2) {
                        if (isset($parts[$i]) && isset($parts[$i + 1]) && $parts[$i] && $parts[$i + 1] !== '')
                            $params[$parts[$i]] = $parts[$i + 1];
                    }
                }
            }
        }
        foreach ($_GET as $key=> $value) {
            $params[$key] = mysql_real_escape_string($value);
        }
        $this->_getParams = $params;
    }

    public function getParam($key)
    {
        if (isset($this->_getParams[$key])) return $this->_getParams[$key];
        return null;
    }

    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new Request();
        }
        return self::$_instance;
    }

    public function getBaseUrl()
    {
        return $this->_baseUrl;
    }

    public function getHost()
    {
        return $this->_host;
    }

    public function isSecure()
    {
        return $this->_isSecure;
    }

    public function getUrl()
    {
        return $this->_request;
    }

    public function getModule()
    {
        return $this->_moduleRoute;
    }

    public function setModule($module)
    {
        $this->_moduleRoute = $module;
        return $this;
    }

    public function setAction($action)
    {
        $this->_moduleAction = $action;
        return $this;
    }

    public function getAction()
    {
        return $this->_moduleAction;
    }

    public function getDefaultRoute()
    {
        return $this->_defaultRoute;
    }

    public function getFullActionName()
    {
        return $this->_moduleRoute . '_' . $this->_moduleAction;
    }
}
