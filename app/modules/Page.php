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


    protected function _initAdmin()
    {
        $this
            ->addAdminMenu('index', 'Pages', 0)
            ->addAdminMenu('categories', 'Categories', 1);
    }

    protected $_adminConfig = array(
        'pages' => array(
            'label'  => 'Page Management',
            'fields' => array(
                ''
            ),
        ),
    );

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
                return $this->render(array('page' => $page));
            } else {
                return $this->forward('category');
            }
        } else {
            return $this->forward('home');
        }
        $this->_defaultNoRouteAction();
    }

    protected function categoryAction()
    {
        if ($url = App::getRequest()->getDefaultRoute()) {
            $category = new CategoryModel();
            $category->loadCategoryByUrl($url);
            if ($category->getId()) {
                $this->_bodyClassName = $url;
                $this->_title         = $category->getData('meta_title');
                $this->_keywords      = $category->getData('meta_keywords');
                $this->_description   = $category->getData('meta_description');
                return $this->render(array('category' => $category));
            }
        }
        $this->_defaultNoRouteAction();
    }

    protected function homeAction()
    {
        $this->render();
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

    public function loadPageByUrl($url)
    {
        $url   = trim($url, '/');
        $query = "SELECT * FROM {$this->_table} WHERE `url`='$url'";
        return $this->loadOneModel($query);
    }

    protected function installVersion1()
    {
        $query = "CREATE TABLE IF NOT EXISTS `{$this->_table}` (
        `id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
        `url`  varchar(255) NOT NULL ,
        `title`  varchar(255) NULL ,
        `author`  varchar(255) NULL ,
        `content`  text NULL ,
        `category_id`  int(11) UNSIGNED NOT NULL ,
        `lang_id`  int(11) UNSIGNED NOT NULL ,
        `image`  varchar(255) NULL ,
        `meta_keywords`  varchar(255) NULL ,
        `meta_description`  varchar(255) NULL ,
        `created`  datetime NULL ,
        `status`  int(1) UNSIGNED NULL DEFAULT 1 ,
        PRIMARY KEY (`id`),
        INDEX `lang_id` (`lang_id`) USING BTREE,
        INDEX `category_id` (`category_id`) USING BTREE,
        UNIQUE INDEX `url` (`url`) USING BTREE
        )ENGINE=MyISAM;";
        return $this->getConnection()->query($query);
    }

    public function getCreatedDate()
    {
        return $this->getData('created');
    }


    public function getIntro()
    {
        if ($content = $this->getData('intro')) {
            return $content;
        } elseif ($content = $this->getData('content')) {
            $start     = strpos($content, '<p>');
            $end       = strpos($content, '</p>', $start);
            $paragraph = substr($content, $start, $end - $start + 4);
            return $paragraph;
        }
        return "";
    }

}

class CategoryModel extends Model
{
    protected $_table = 'page_categories';
    protected $_version = 1;

    public function loadCategoryByUrl($url)
    {
        $url   = trim($url, '/');
        $query = "SELECT * FROM  `{$this->_table}` WHERE `url`='$url'";
        return $this->loadOneModel($query);
    }

    public function getPosts()
    {
        if ($id = $this->getId()) {
            $query = "SELECT * FROM  `pages` WHERE `category_id`=$id";
            return $this->loadModelCollection($query, 'PageModel');
        }
        return array();
    }

    protected function installVersion1()
    {
        $query = "CREATE TABLE IF NOT EXISTS  `{$this->_table}` (
           `id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
           `title`  varchar(255) NOT NULL ,
           `url`  varchar(255) NOT NULL ,
           `parent_id`  int(11) NULL DEFAULT NULL ,
           `lang_id`  int(11) UNSIGNED NOT NULL ,
           `status`  int(1) NULL DEFAULT 1 ,
           PRIMARY KEY (`id`),
           INDEX `languages` (`lang_id`) USING BTREE,
           UNIQUE INDEX `url` (`url`) USING BTREE
           )ENGINE=MyISAM";
        return $this->getConnection()->query($query);
    }


}
