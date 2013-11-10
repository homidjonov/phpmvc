<?php
class Cookie
{
    protected $_lifetime;
    protected $_domain;
    protected static $_instance;

    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new Cookie();
        }
        return self::$_instance;
    }

    public function __construct()
    {
        $this->_lifetime = CONFIG_COOKIE_LIFETIME;
        $this->_domain   = trim($this->getRequest()->getDomain(), '/');
        if (strpos($this->_domain, 'www.') === 0) {
            $this->_domain = ltrim($this->_domain, 'www.');
        }
    }

    protected function getRequest()
    {
        return App::getRequest();
    }

    public function getDomain()
    {
        return $this->_domain;
    }

    public function getPath()
    {

        return App::isAdmin() ? '/' . APP_ADMIN_ROUTE : '/';
    }

    public function getLifetime()
    {
        if ($this->_lifetime && is_numeric($this->_lifetime)) {
            return $this->_lifetime;
        }
        return 3600;
    }


    public function setLifetime($lifetime)
    {
        $this->_lifetime = (int)$lifetime;
        return $this;
    }

    public function getHttpOnly()
    {
        return true;
    }

    public function isSecure()
    {
        if (App::isAdmin()) {
            return $this->getRequest()->isSecure();
        }
        return false;
    }

    public function set($name, $value, $period = null, $path = null, $domain = null, $secure = null, $httponly = null)
    {
        if ($period === null) {
            $period = $this->getLifetime();
        }

        if ($period == null) {
            $expire = 0;
        } else {
            $expire = time() + $period;
        }


        if (is_null($path)) {
            $path = $this->getPath();
        }

        if (is_null($domain)) {
            $domain = $this->getDomain();
        }

        if (is_null($secure)) {
            $secure = $this->isSecure();
        }
        if (is_null($httponly)) {
            $httponly = $this->getHttpOnly();
        }
        //App::log($domain);
        setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);

        return $this;
    }

    public function renew($name, $period = null, $path = null, $domain = null, $secure = null, $httponly = null)
    {
        $value = $this->getCookie($name, false);
        if ($value !== false) {
            $this->set($name, $value, $period, $path, $domain, $secure, $httponly);
        }
        return $this;
    }

    public function get($name = null)
    {
        return $this->getCookie($name, false);
    }

    public function getCookie($key = null, $default = null)
    {
        if (null === $key) {
            return $_COOKIE;
        }

        return (isset($_COOKIE[$key])) ? $_COOKIE[$key] : $default;
    }

    public function delete($name, $path = null, $domain = null, $secure = null, $httponly = null)
    {
        if (is_null($path)) {
            $path = $this->getPath();
        }
        if (is_null($domain)) {
            $domain = $this->getDomain();
        }
        if (is_null($secure)) {
            $secure = $this->isSecure();
        }
        if (is_null($httponly)) {
            $httponly = $this->getHttponly();
        }

        setcookie($name, null, null, $path, $domain, $secure, $httponly);
        return $this;
    }
}
