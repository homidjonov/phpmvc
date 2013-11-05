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

    protected $_allowedActions = array('login', 'restore');

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
        if (!$this->getSession()->isLoggedIn() && !in_array($this->getRequest()->getAction(), $this->_allowedActions)) {
            $this->getRequest()->setBeforeAuthUrl($this->getRequest()->getRequestUrl());
            $this->getRequest()->setAction('login');
        }

        if ($this->getSession()->isLoggedIn() && in_array($this->getRequest()->getAction(), $this->_allowedActions)) {
            $this->getRequest()->redirect($this->getAdminUrl('index'));
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

    public function restoreAction()
    {
        $form = $this->getRestoreForm();
        if ($this->getRequest()->hasPost()) {
            $email = $this->getRequest()->getPost('email');
            try {
                /*if ($this->getSession()->authentificate($email, $password)) {
                    $this->getRequest()->redirect($this->getRequest()->getBeforeAuthUrl());
                }*/
            } catch (Exception $e) {
                $form->addValidationError($e->getMessage());
            }
            $this->getSession()->addNotice($this->__("Restore link has been send to provided e-mail."));
         }
        $this->setPart('restore_form', $form);
        $this->render();
    }

    public function loginAction()
    {
        $this->_title = $this->__("Authentication");
        $form         = $this->getLoginForm();

        if ($this->getRequest()->hasPost()) {
            $form->init();
            $email    = $this->getRequest()->getPost('email');
            $password = $this->getRequest()->getPost('password');
            try {
                if ($this->getSession()->authentificate($email, $password)) {
                    $this->getRequest()->redirect($this->getRequest()->getBeforeAuthUrl());
                }
            } catch (Exception $e) {
                $this->getSession()->addError($this->__($e->getMessage()));
            }
        }
        $this->setPart('login_form', $form);

        $this->render();
    }

    public function indexAction()
    {
        $this->render();
    }

    public function logoutAction()
    {
        $this->getSession()->renew();
        $this->getRequest()->redirect($this->getAdminUrl('login'));
    }

    /**
     * @return Form
     */
    protected function getRestoreForm()
    {
        $form = new Form();
        $form->setElementWrapper('div', 'form-group input-group');

        $form->addElement('text', 'email', array(
            'name'        => 'email',
            'placeholder' => $this->__('Email'),
            'class'       => 'form-control',
            'before'      => '<span class="input-group-addon"><i class="fa fa-envelope-o fa-fw"></i></span>'
        ));

        $login = $this->getAdminUrl('login');
        $form->addElement('button', 'submit', array(
            'caption' => 'Restore',
            'type'    => 'submit',
            'class'   => 'btn btn-primary',
            'style'   => 'margin-right:10px',
            'before'  => '<div>',
            'after'   => "<a href='$login' class='text-muted' style='margin-left: 10px'>Back to Login <i class='fa fa-key'></i></a> <a href='/' class='text-info' style='margin-left: 10px'>Back to Home <i class='fa fa-home'></i></a></div>",
        ));


        return $form;
    }

    protected function getLoginForm()
    {
        $form = new Form();
        $form->setElementWrapper('div', 'form-group input-group');

        $form->addElement('text', 'email', array(
            'name'        => 'email',
            'placeholder' => $this->__('Email'),
            'class'       => 'form-control',
            'before'      => '<span class="input-group-addon"><i class="fa fa-envelope-o fa-fw"></i></span>'
        ));

        $form->addElement('password', 'password', array(
            'placeholder'  => $this->__('Password'),
            'autocomplite' => 'off',
            'class'        => 'form-control',
            'before'       => '<span class="input-group-addon"><i class="fa fa-key fa-fw"></i></span>'
        ));

        $forgot = $this->getAdminUrl('restore');
        $form->addElement('button', 'submit', array(
            'caption' => 'Login',
            'type'    => 'submit',
            'class'   => 'btn btn-primary',
            'style'   => 'margin-right:10px',
            'before'  => '<div>',
            'after'   => "<a href='$forgot' class='text-muted' style='margin-left: 10px'>Forgot Password <i class='fa fa-question-circle'></i></a> <a href='/' class='text-info' style='margin-left: 10px'>Back to Home <i class='fa fa-home'></i></a></div>",
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
        $query = "CREATE TABLE IF NOT EXISTS `{$this->_table}` (
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
            $this
                //->renew()
                ->setIsLoggedIn($admin);
            return true;
        }
        throw new Exception('Invalid Username or Password');
    }


}

