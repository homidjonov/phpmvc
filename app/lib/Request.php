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
    protected $_domain;
    protected $_baseUrl;
    protected $_isSecure;
    protected $_moduleRoute;
    protected $_moduleAction;
    protected $_moduleOrigAction;
    protected $_moduleSubAction;
    protected $_query;
    protected $_queryParams;
    protected $_defaultRoute;
    protected $_getParams;
    protected $_beforeAuthUrl;
    protected $_beforeAuthAction;
    protected static $_instance;

    /**
     * Url parser
     * Bilganimdek yozdim, balki kamchiligi bordir hozicha ishlayabdi, lekin
     * optimallashtirish kerak bo'ladi qachonlardir. Regular expression ma'qulroqdir?
     */
    public function __construct()
    {
        $this->sanitizeInput();

        $this->_request = strtolower($_SERVER['REQUEST_URI']);
        if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'])
            $this->_request = substr($this->_request, 0, strpos($this->_request, $_SERVER['QUERY_STRING']) - 1);
        $this->_domain   = $_SERVER['HTTP_HOST'];
        $this->_isSecure = $_SERVER['SERVER_PORT'] == 443;
        $this->_query    = strtolower($_SERVER['QUERY_STRING']);
        $this->_host     = strtolower((($this->_isSecure) ? 'https://' : 'http://') . trim($_SERVER['HTTP_HOST'], '/') . '/');
        $folder          = trim($_SERVER['SCRIPT_NAME'], '/index.php');
        $this->_baseUrl  = strtolower($this->_host . (($folder) ? "$folder/" : ""));
        /**
         * agar module qismi bo'lmasa default routeni olamiz
         * agar action qismi bo'lmasa default actionni olamiz
         */
        $this->_moduleRoute  = App::getDefaultRoute();
        $this->_moduleAction = 'default';

        $params = array();
        if (isset($_SERVER['REDIRECT_URL']) && $_SERVER['REDIRECT_URL']) {
            $this->_defaultRoute = strtolower(trim($_SERVER['REDIRECT_URL'], rtrim($_SERVER['SCRIPT_NAME'], 'index.php')));
            $parts               = explode('/', $this->_defaultRoute);
            if ($parts[0]) $this->_moduleRoute = $parts[0];
            $c = count($parts);

            if ($c > 1) {
                $this->_moduleAction     = $parts[1];
                $this->_moduleOrigAction = $parts[1];
                if ($c > 2) {
                    for ($i = 2; $i < $c; $i += 2) {
                        if (isset($parts[$i]) && isset($parts[$i + 1]) && $parts[$i] && $parts[$i + 1] !== '')
                            $params[$parts[$i]] = $parts[$i + 1];
                    }
                }
            }
        }
        foreach ($_GET as $key => $value) {
            $params[$key] = $value;
        }
        $this->_getParams = $params;
    }

    protected function sanitizeInput()
    {
        foreach ($_POST as $key => $value) {
            $_POST[$key] = mysql_real_escape_string($value);
        }
        foreach ($_GET as $key => $value) {
            $_GET[$key] = mysql_real_escape_string($value);
        }
    }

    public function getParam($key, $default = null)
    {
        if (isset($this->_getParams[$key])) return $this->_getParams[$key];
        return $default;
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

    public function isAdmin()
    {
        return $this->_moduleRoute == App::getAdminRoute();
    }


    public function getHost()
    {
        return $this->_host;
    }

    public function getRemoteAddr()
    {
        return (string)$_SERVER['REMOTE_ADDR'];
    }

    public function getUserAgent()
    {
        return (string)$_SERVER['HTTP_USER_AGENT'];
    }

    public function getQueryString()
    {
        return $this->_query;
    }

    public function isSecure()
    {
        return $this->_isSecure;
    }

    public function getRequestUrl()
    {
        return $this->_request;
    }

    public function getDomain()
    {
        return $this->_domain;
    }

    /**
     * @return mixed
     */
    public function getModule()
    {
        return $this->_moduleRoute;
    }

    public function setModule($module)
    {
        $this->_moduleRoute = $module;
        return $this;
    }

    public function getBeforeAuthUrl()
    {
        return $this->_beforeAuthUrl;
    }

    public function getBeforeAuthAction()
    {
        return $this->_beforeAuthAction;
    }

    public function setBeforeAuthUrl($url)
    {
        $this->_beforeAuthUrl = $url;
        return $this;
    }

    public function setBeforeAuthAction($action)
    {
        $this->_beforeAuthAction = $action;
        return $this;
    }

    public function setAction($action)
    {
        $this->_moduleOrigAction = $this->_moduleAction;
        $this->_moduleAction     = strtolower($action);
        return $this;
    }

    public function getOrigAction()
    {
        return $this->_moduleOrigAction;
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

    public function hasPost()
    {
        return count($_POST) > 0;
    }

    public function getPost($key)
    {
        if (isset($_POST[$key])) return $_POST[$key];
        return false;
    }

    public function redirect($url)
    {
        header('Location: ' . $url);
        die;
    }

    /**
     * @return Cookie
     */
    public static function getCookie()
    {
        return Cookie::getInstance();
    }
}
