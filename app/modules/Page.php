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
    protected $_objectData;

    protected $_adminMenu = array(
        'index' => 'Page Management',
        'new'   => 'Add NewPage'
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
                $this->_title         = $page->getData('meta_title');
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

    public function adminNew()
    {
        $this->_title = $this->__('Create New Page');
        $this->render();
    }

    public function adminIndex()
    {
        $this->_title = $this->__('Create New Page');
        $this->render();
    }
}

class PageModel extends Model
{
    protected $_table = 'pages';
    protected $_version = 1;

    protected $_translateable = true;

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
