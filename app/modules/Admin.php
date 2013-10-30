<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Shavkat
 * Date: 10/22/13
 * Time: 8:58 PM
 */
class Admin extends Module
{
    protected $_route = APP_ADMIN_ROUTE;
    protected $_observers = array(
        'module_before_run',
        'module_after_run',
        'page_before_cache',
    );

    public function page_before_cache($params)
    {
        $params['can_cache'] &= !$this->getRequest()->isAdmin();
    }

    protected function getSession()
    {
        return AdminSession::getInstance();
    }

    protected function _preDispatch()
    {
        $user = $this->getSession()->getUser();
        if (!$this->getSession()->isLoggedIn()) {
            $this->getRequest()->setBeforeAuthUrl($this->getRequest()->getRequestUrl());
            $this->getRequest()->setAction('login');
        }
    }

    public function defaultAction()
    {
        $action = explode('_', $this->getRequest()->getOrigAction());
        if (count($action) == 2) {
            $module = false;
            try {
                $module = $this->getModule($action[0]);
            } catch (Exception $e) {
                //module not found
            }
            if ($module) {
                $invoke = 'admin' . ucfirst($action[1]);
                if (method_exists($module, $invoke)) {
                    $this->getRequest()->setAction($action[1]);
                    $module->$invoke();
                    return;
                }
            }
            $this->_defaultNoRouteAction();
        }
        $this->forward('index');
    }

    public function loginAction()
    {
        if ($this->getSession()->isLoggedIn()) {
            $this->getRequest()->redirect($this->getAdminUrl('index'));
        }

        $form = $this->getLoginForm();

        if ($this->getRequest()->hasPost()) {
            $form->init();
            $email    = $this->getRequest()->getPost('email');
            $password = $this->getRequest()->getPost('password');
            try {
                if ($this->getSession()->authentificate($email, $password)) {
                    $this->getRequest()->redirect($this->getRequest()->getBeforeAuthUrl());
                }
            } catch (Exception $e) {
                $form->addValidationError($e->getMessage());
            }
        }
        $this->setPart('login_form', $form);

        $this->render();
    }

    public function indexAction()
    {
        $this->render();
    }

    /**
     * @return Form
     */
    protected function getLoginForm()
    {
        $form = new Form();
        $form->setElementWrapper('p');

        $form->addElement('text', 'email', array(
            'name'  => 'email',
            'label' => $this->__('Email'),
            'class' => 'some_class valid-required',
        ));

        $form->addElement('password', 'password', array(
            'label'        => $this->__('Password'),
            'autocomplite' => 'off',
            'class'        => 'some_class valid-required',
        ));

        $form->addElement('select', 'role', array(
            'label'   => $this->__('Test SelectBox'),
            'value'   => 'admin',
            'options' => array(
                ''          => 'Select',
                'admin'     => 'Admin',
                'user'      => 'User',
                'moderator' => 'Moderator',
            )
        ));

        $form->addElement('submit', 'submit', array(
            'value' => 'Login',
            'class' => 'some_class valid-required',
        ));

        return $form;
    }
}

class UserModel extends Model
{
    protected $_username;
    protected $_password;
    protected $_table = 'users';
    protected $_version = 1;

    public function loadByEmail($email)
    {
        $query = "SELECT * FROM {$this->_table} WHERE `email`='$email'";
        if ($result = mysql_fetch_assoc($this->query($query))) {
            $this->_username = $result['username'];
            $this->_password = $result['password'];
            $this->_id       = (int)$result['id'];
        }
        return $this;
    }

    public function validatePassword($password)
    {
        if ($this->_password) {
            $salt = substr($this->_password, 0, 10);
            return $this->_password == $this->encryptPassword($password, $salt);
        }
        return $this->_password == $password;
    }

    protected function encryptPassword($password, $salt = false)
    {
        if (!$salt) $salt = substr(md5(time()), 0, 10);
        return $salt . hash('sha256', $password . $salt);
    }

    protected function installVersion1()
    {
        $query = "CREATE TABLE `{$this->_table}` (
        `id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
        `username`  varchar(20) DEFAULT NULL ,
        `email`  varchar(50) NOT NULL ,
        `password`  varchar(255) NOT NULL ,
        `role`  enum('user','admin') DEFAULT 'user' ,
        `status`  int(1) NULL DEFAULT 1 ,
        PRIMARY KEY (`id`),
        UNIQUE INDEX `email` (`email`) USING BTREE
        )ENGINE=MyISAM";
        return $this->getConnection()->query($query);
    }
}

class AdminSession extends Session
{
    protected $_space = 'admin';
    private static $_instance;

    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new  AdminSession();
        }
        return self::$_instance;
    }

    public function authentificate($email, $password)
    {
        $admin = new UserModel();
        $admin->loadByEmail($email);

        if ($admin->validatePassword($password)) {
            $this->renew()->setIsLoggedIn($admin);
            return true;
        }
        throw new Exception('Invalid Username or Password');
    }


}

