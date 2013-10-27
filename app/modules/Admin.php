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

        $user                = $this->getSession()->getUser();
        if (empty($user)) {
            //$this->getRequest()->setAction('login');
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
        $this->render();
    }

    /**
     * observers
     */


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

}

