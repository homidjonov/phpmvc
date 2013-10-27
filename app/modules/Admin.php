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
        'part_before_include',
        'part_after_include',
    );

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
                return $this->_defaultNoRouteAction();
            }
            if ($module) {
                $invoke = 'admin' . ucfirst($action[1]);
                if (method_exists($module, $invoke)) {
                    $this->getRequest()->setAction($action[1]);
                    $module->$invoke();
                    return;
                }
            }
        }
        $this->_defaultNoRouteAction();
    }

    public function loginAction()
    {
        if ($this->getSession()->isLoggedIn()) {
            $this->getRequest()->redirect($this->getAdminUrl('index'));
        }
        $form = new Form();
        $form->setElementWrapper('p');

        $form->addElement('text', 'login', array(
            'name'  => 'login',
            'label' => $this->__('Login'),
            'class' => 'some_class valid-required',

        ));
        $form->addElement('password', 'password', array(
            'label'        => $this->__('Password'),
            'autocomplite' => 'off',
            'class'        => 'some_class valid-required',
        ));
        $form->addElement('submit', 'submit', array(
            'value' => $this->__('Login'),
            'class' => 'some_class valid-required',
        ));

        if ($this->getRequest()->getIsPost()) {
            $form->init();
            $login    = $this->getRequest()->getPost('login');
            $password = $this->getRequest()->getPost('password');
            try {
                if ($this->getSession()->authentificate($login, $password)) {
                    $this->getRequest()->redirect($this->getRequest()->getBeforeAuthUrl());
                }
            } catch (Exception $e) {
                $form->addValidationError($e->getMessage());
            }
        }
        $this->setPart('login_form', $form->render());

        $this->render();
    }

    public function indexAction()
    {
        $this->render();
    }
}

class AdminModel extends Model
{
    protected $_username;
    protected $_password;

    public function loadByUsername($login)
    {
        $this->_username = "shavkat";
        $this->_password = "123";
        $this->_id       = "1";
        return $this;
    }

    public function validatePassword($password)
    {
        return $this->_password == $password;
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

    public function authentificate($login, $password)
    {
        $admin = new AdminModel();
        $admin->loadByUsername($login);
        if ($admin->getId()) {
            if ($admin->validatePassword($password)) {
                $this->renew()->setIsLoggedIn($admin);
                return true;
            }
        }
        throw new Exception('Invalid Username or Password');
    }


}

