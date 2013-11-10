<?php
/**
 * Created by PhpStorm.
 * User: Shavkat
 * Date: 10/25/13
 * Time: 2:40 AM
 */

class Session extends Object
{
    protected $_nameSpace = 'user';
    protected $_user = false;
    protected $_userId = false;
    protected $_savePath;
    protected $_isLoggedIn;
    protected $_cookie;

    const VALIDATION_KEY            = '_session_validation_key';
    const VALIDATION_REMOTE_ADD_KEY = 'remote_address';
    const VALIDATION_HTTP_AGENT_KEY = 'http_user_agent';

    public function __construct()
    {
        $this->start();
    }


    protected function start()
    {
        if (!isset($_SESSION)) {
            session_module_name('files');
            if (is_writable($this->getSessionSavePath())) {
                session_save_path($this->getSessionSavePath());
            } else {
                mkdir($this->getSessionSavePath());
            }
            $this->setSessionName($this->_nameSpace);

            $cookie       = $this->getCookie();
            $cookieParams = array(
                'lifetime' => $cookie->getLifetime(),
                'path'     => $cookie->getPath(),
                'domain'   => $cookie->getDomain(),
                'secure'   => $cookie->isSecure(),
                'httponly' => $cookie->getHttponly()
            );
            call_user_func_array('session_set_cookie_params', $cookieParams);
            session_start();
            if ($cookie->get($this->getSessionName()) == $this->getSessionId()) {
                $cookie->renew(session_name());
            }
        }
        $this->_init();
    }

    protected function _init()
    {
        if (!isset($_SESSION[$this->_nameSpace])) {
            $_SESSION[$this->_nameSpace] = array();
        }
        $this->_data = & $_SESSION[$this->_nameSpace];

        if (isset($this->_data['user_id'])) {
            $this->_isLoggedIn = true;
            $this->_userId     = $this->_data['user_id'];
        }
        if (!isset($this->_data['messages'])) $this->_clearMessages(true);
        $this->validate();
    }

    public function validate()
    {
        if (!isset($this->_data[self::VALIDATION_KEY])) {
            $this->_data[self::VALIDATION_KEY] = $this->getValidationData();
        } else {
            $valid          = true;
            $sessionData    = $this->_data[self::VALIDATION_KEY];
            $validationData = $this->getValidationData();
            foreach ($validationData as $key => $value) {
                $valid &= $sessionData[$key] == $validationData[$key];
            }
            if (!$valid) {
                throw new Exception('Fake session');
            }
        }
        return $this;
    }


    public function getValidationData()
    {
        $data = array(
            self::VALIDATION_REMOTE_ADD_KEY => App::getRequest()->getRemoteAddr(),
            self::VALIDATION_HTTP_AGENT_KEY => App::getRequest()->getUserAgent(),
        );
        return $data;
    }

    /**
     * @return Cookie
     */
    protected function getCookie()
    {
        return App::getRequest()->getCookie();
    }

    public function setSessionName($name)
    {
        session_name($name);
        return $this;
    }

    public function clear()
    {
        return $this->unsetData();
    }

    public function getSessionId()
    {
        return session_id();
    }

    public function getSessionName()
    {
        return session_name();
    }


    public function isLoggedIn()
    {
        return $this->_isLoggedIn;
    }

    public function setIsLoggedIn(Model $model)
    {

        $this->_isLoggedIn = true;
        $this->setData('user_id', $model->getId());
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
        $this->getCookie()->delete($this->getSessionName());
        session_regenerate_id(true);
        return $this;
    }

    protected function _clearMessages()
    {
        $this->_data['messages'] = array(
            'success' => array(),
            'info'    => array(),
            'warning' => array(),
            'danger'  => array(),
        );
        return $this;
    }

    protected $_messages;

    public function addMessage($message, $severity = 'notice')
    {
        $this->_data['messages'][$severity][] = $message;
        return $this;
    }

    public function addError($message)
    {
        return $this->addMessage($message, 'danger');
    }

    public function addSuccess($message)
    {
        return $this->addMessage($message, 'success');
    }

    public function addNotice($message)
    {
        return $this->addMessage($message, 'info');
    }

    public function addWarning($message)
    {
        return $this->addMessage($message, 'warning');
    }

    public function getMessages()
    {
        $messages = $this->getData('messages');
        $this->_clearMessages();
        return $messages;
    }

} 