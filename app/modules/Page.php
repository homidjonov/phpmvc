<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Shavkat
 * Date: 10/22/13
 * Time: 8:58 PM
 */
class Page extends Module
{
    protected $_route = 'page';
    protected $_defaultAction = 'view';
    protected $_observers = array(
        'module_before_run',
        'module_after_run',
        'part_before_include',
        'part_after_include',
    );

    public function viewAction()
    {
        //echo App::getRequest()->getParam('k');
    }


    protected function defaultAction()
    {
        if ($url = App::getRequest()->getDefaultRoute()) {
            $page = new PageModel();
            $page->loadPageByUrl($url);
            if ($page->getId()) {
                $this->_bodyClassName = $url;
                $this->_title         = $page->getData('meta)title');
                $this->_keywords      = $page->getData('meta_keywords');
                $this->_description   = $page->getData('meta_description');
                $this->render(array('page' => $page));
                return;
            }
        } else {
            Request::getInstance()->setAction('home');
            $this->render();
            return;
        }
        $this->_defaultNoRouteAction();
    }

    /**
     * observers
     */

    public function module_before_run($params)
    {
        $module = $params['module'];
        //echo $module->getName() . " module is running and setted to null<br>";
    }

    public function module_after_run($params)
    {
        echo "I am module_after_run observer!";
    }

    public function part_before_include($params)
    {
        if (!App::canDebugParts()) return;
        $part = $params['part'];
        $file = explode('\view\\', $params['file']);
        $file = $file[1];
        if (!in_array($part, array('head', 'template', 'meta')))
            echo "<span class='part_border'><div class='info'>$part ($file)</div>";
    }

    public function part_after_include($params)
    {
        if (!App::canDebugParts()) return;
        $part = $params['part'];
        if (!in_array($part, array('head', 'template', 'meta')))
            echo "</span>";
    }

}

class PageModel extends Model
{
    protected $_table = 'pages';
    protected $_version = 1;

    protected $_columns = array(
        'page_id', 'title', 'url', 'created', 'content',
    );

    public function loadPageByUrl($url)
    {
        $query = "SELECT * FROM {$this->_table} WHERE `url`='$url'";
        return $this->loadOneModel($query);
    }

    protected function installVersion1()
    {
        $query = "CREATE TABLE IF NOT EXISTS `{$this->_table}` (
        `id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
        `url`  varchar(255) NOT NULL ,
        `title`  varchar(255) NULL ,
        `content`  text NULL ,
        `meta_keywords`  varchar(255) NULL ,
        `meta_description`  varchar(255) NULL ,
        `created`  datetime NULL ,
        `status`  int(1) UNSIGNED NULL DEFAULT 1 ,
        PRIMARY KEY (`page_id`),
        UNIQUE INDEX `url` (`url`) USING BTREE
        );";
        $this->getConnection()->query($query);
    }

    public function getCreatedDate()
    {
        return $this->getData('created');
    }
}
