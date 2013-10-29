<?php
/**
 * Created by PhpStorm.
 * User: Shavkat
 * Date: 10/25/13
 * Time: 2:40 AM
 */

class Session
{
    protected $_space = 'user';
    protected $_user = false;
    protected $_userId = false;
    protected $_savePath;
    protected $_isLoggedIn;

    public function __construct()
    {
        $this->_init();
        //$this->setUser('blabla');
    }

    public function isLoggedIn()
    {
        return $this->_isLoggedIn;
    }

    public function setIsLoggedIn(Model $model)
    {
        $this->_isLoggedIn  = true;
        $_SESSION['userId'] = $model->getId();
    }


    protected function _init()
    {
        if (!isset($_SESSION)) {
            session_module_name('files');
            if (is_writable($this->getSessionSavePath())) {
                session_save_path($this->getSessionSavePath());
            } else {
                mkdir($this->getSessionSavePath());
            }
            session_name($this->_space);
            session_start();
            if (App::getRequest()->getCookie($this->getSessionName()) == $this->getSessionId()) {
                App::getRequest()->setCookie($this->getSessionName(), $this->getSessionId());
            }
        }
        if (isset($_SESSION['userId'])) {
            $this->_userId     = $_SESSION['userId'];
            $this->_isLoggedIn = true;
        }
    }

    public function getSessionId()
    {
        return session_id();
    }

    public function getSessionName()
    {
        return session_name();
    }

    protected function getSessionSavePath()
    {
        return APP_SESSION_DIR;
    }

    private static $_instance;

    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new Session();
        }
        return self::$_instance;
    }

    public function getUser()
    {
        return $this->_user;
    }

    public function setUser($user)
    {
        $_SESSION['user'] = $user;
        return $this->_user = $user;
    }

    public function renew()
    {
        unset($_COOKIE[$this->getSessionName()]);
        session_regenerate_id(true);

        return $this;
    }

    protected $_messages = array(
        'notice'  => array(),
        'warning' => array(),
        'error'   => array(),
    );

    public function addError($message)
    {
        $this->_messages['error'][] = $message;
    }

} 