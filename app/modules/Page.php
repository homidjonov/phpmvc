<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Shavkat
 * Date: 10/22/13
 * Time: 8:58 PM
 */
class Page extends Module
{
    //multiple routing
    protected $_route = 'page:category:tag';
    protected $_predefinedFunctions = array('getStaticBlock');


    protected function _initAdmin()
    {
        $this->addAdminMenu('page_index', 'Content', array(
            'page_index'     => 'Pages',
            'page_add'       => 'New Page',
            'category_index' => 'Categories',
            'category_add'   => 'New Category',
            'tag_index'      => 'Tags',
            'tag_add'        => 'New Tag',
        ), 10, 'fa-file');
    }

    protected $_adminConfig = array(
        'pages' => array(
            'label'  => 'Page Management',
            'fields' => array(
                ''
            ),
        ),
    );


    /** --------------FRONTEND ACTIONS---------------------- */

    protected function viewAction()
    {
        if ($id = (int)$this->getRequest()->getParam('id')) {
            $page = new PageModel();
            $page->loadById($id);
            if ($page->getId() && $page->isActive()) {
                return $this->renderPage($page);
            }
        }
        $this->_defaultNoRouteAction();
    }

    protected function defaultAction()
    {
        if ($url = App::getRequest()->getDefaultRoute()) {
            $page = new PageModel();
            $page->loadByUrl($url);
            if ($page->getId() && $page->isActive()) {
                return $this->renderPage($page);
            } else {
                return $this->forward('category');
            }
        } else {
            return $this->forward('home');
        }

        $this->_defaultNoRouteAction();
    }

    protected function categoryViewAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $category = new CategoryModel();
            if ($id = intval($id)) {
                $category->loadById($id);
            } else {
                $category->loadByUrl($this->getRequest()->getParam('id'));
            }

            if ($category->getId()) {
                return $this->renderCategory($category);
            }
        }
        $this->_defaultNoRouteAction();
    }

    protected function categoryAction()
    {
        if ($url = App::getRequest()->getDefaultRoute()) {
            $category = new CategoryModel();
            $category->loadByUrl($url);
            if ($category->getId()) {
                if ($category->getRenderer()) $this->setPart('content', $category->render());
                return $this->renderCategory($category);
            }
        }
        $this->_defaultNoRouteAction();
    }


    protected function renderPage($page)
    {
        $this->_title       = $page->getData('title');
        $this->_keywords    = $page->getData('meta_keywords');
        $this->_description = $page->getData('meta_description');
        $this->setBodyClassName($page->getData('type'));
        $this->setRenderer('content', $page->getData('type'));
        return $this->render(array('page' => $page));
    }

    /**
     * @param $category CategoryModel
     */
    protected function renderCategory($category)
    {
        Pagination::getInstance()->setItemsCount($category->getPostCount());
        $this->_title       = $category->getData('meta_title');
        $this->_keywords    = $category->getData('meta_keywords');
        $this->_description = $category->getData('meta_description');
        $this->setBodyClassName('category_' . $category->getData('url'));
        if ($category->getData('renderer')) $this->setRenderer('content', $category->getData('renderer'));
        return $this->render(array('category' => $category));
    }

    protected function homeAction()
    {
        $this->render();
    }


    protected function tagViewAction()
    {
        $this->render();
    }


    /** --------------ADMIN ACTIONS-------------- */
    public function adminPageIndex()
    {
        $this->_title = 'Content Management';
        $page         = new PageModel();
        $table        = new Grid();
        $table
            ->setPanel('Content Management ')
            ->setModel($page)
            ->addColumn('id', array(
                'title' => '#',
            ))
            ->addColumn('title')
            ->addColumn('created', array(
                'type' => Grid::TYPE_DATETIME
            ))
            ->addColumn('type', array(
                'type'    => Grid::TYPE_OPTION,
                'options' => $page->getTypeOptions(),
            ))
            ->addColumn('edit', array(
                'type'   => Grid::TYPE_ACTION,
                'action' => 'getAdminEditLink'
            ));
        $this->setPart('content', $table);
        $this->render();
    }

    public function adminCategoryIndex()
    {
        $this->_title = 'Content Management';
        $model        = new CategoryModel();
        $table        = new Grid();
        $table
            ->setPanel('Category Management ')
            ->setModel($model)
            ->addColumn('id', array(
                'title' => '#',
            ))
            ->addColumn('title')
            ->addColumn('url')
            ->addColumn('status', array(
                'type'    => Grid::TYPE_OPTION,
                'options' => $model->getStatusOptions(),
            ))
            ->addColumn('edit', array(
                'type'   => Grid::TYPE_ACTION,
                'action' => 'getAdminEditLink'
            ));
        $this->setPart('content', $table);
        $this->render();
    }

    public function adminPageAdd()
    {
        $this->_title = 'Create New Page';
        $this->render();
    }

    public function adminCategoryAdd()
    {
        $this->_title = 'Create New Category';
        $this->render();
    }


    public function adminTagAdd()
    {
        $this->_title = 'Create Tags';
        $this->render();
    }

    public function adminTagIndex()
    {
        $this->_title = 'Tag Management';
        $this->render();
    }

    /** --------------Predefined Functions-------------- */
    public function getStaticBlock($alias = false)
    {
        if ($alias) {
            $page = new PageModel();
            return $page->loadStaticBlock($alias)->getData('content');
        }
        return '';
    }
}

class PageModel extends Model
{
    protected $_table = 'pages';

    const TYPE_POST   = 'post';
    const TYPE_PAGE   = 'page';
    const TYPE_STATIC = 'static';


    static public function getTypeOptions()
    {
        return array(
            self::TYPE_PAGE   => 'Page',
            self::TYPE_POST   => 'Post',
            self::TYPE_STATIC => 'Static',
        );
    }

    public function loadStaticBlock($url)
    {
        return $this->loadOneModel(false, array('url' => $url, 'type' => 'static'));
    }

    public function getCreatedFormatted()
    {
        $date = $this->getData('created');
        return date(MD_PAGE_DATE_FORMAT, strtotime($date));
    }

    public function getImageUrl()
    {
        $url = $this->getData('image');
        if (strpos($url, 'http') === 0) {
            return $url;
        } else {
            return App::getRequest()->getBaseUrl() . "media/images/$url";
        }
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

    public function isPostType()
    {
        return $this->getData('type') == self::TYPE_POST;
    }

    public function isActive()
    {
        return $this->getData('status') == self::STATUS_ENABLED;
    }

    public function getAdminEditLink()
    {
        return sprintf("<a href='%s'>%s</a>", App::getAdminUrl('page_edit', array($this->getIdFieldName() => $this->getId())), 'Edit');
    }

}


class CategoryModel extends Model
{
    protected $_table = 'categories';

    public function getPosts(Pagination $p = null)
    {
        if ($id = $this->getId() && $this->getStatus() == 1) {
            $query = "
            SELECT *, pc.category_id as  category_id FROM  `pages` as p
            LEFT JOIN page_categories as pc ON pc.page_id=p.id and pc.`category_id`=$id
            WHERE p.status=1
            ORDER BY p.created DESC
            ";
            return $this->loadModelCollection($query, 'PageModel', $p);
        }
        return array();
    }

    public function getPostCount()
    {
        if ($id = $this->getId()) {
            $query = "
            SELECT count(1) as `count` FROM  `pages` as p
            LEFT JOIN page_categories as pc ON pc.page_id=p.id and pc.`category_id`=$id
            WHERE p.status=1";
            return $this->getCount($query);
        }
    }

    public function getAdminEditLink()
    {
        return sprintf("<a href='%s'>%s</a>", App::getAdminUrl('category_edit', array($this->getIdFieldName() => $this->getId())), 'Edit');
    }
}

class TagModel extends Model
{
    protected $_table = 'tags';
}


class PageInstaller extends Model
{
    protected $_pages = 'pages';
    protected $_version = 4;

    protected function installVersion1()
    {
        $query = "CREATE TABLE IF NOT EXISTS `pages` (
        `id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
        `url`  varchar(255) NOT NULL ,
        `title`  varchar(255) NULL ,
        `author`  varchar(255) NULL ,
        `content`  text NULL ,
        `lang_id`  int(11) UNSIGNED NOT NULL ,
        `image`  varchar(255) NULL ,
        `meta_keywords`  varchar(255) NULL ,
        `meta_description`  varchar(255) NULL ,
        `status`  int(1) UNSIGNED NULL DEFAULT 1 ,
        `type`  enum('post','page','static') NULL DEFAULT 'post' ,
        `created`  datetime NULL DEFAULT NULL ,
        `views`  int(10) NOT NULL DEFAULT 1 ,
        `downloads`  int(10) NOT NULL DEFAULT 0 ,
        `comments`  int(10) NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`),
        INDEX `lang_id` (`lang_id`) USING BTREE,
        UNIQUE INDEX `url` (`url`) USING BTREE
        )ENGINE=MyISAM;";
        return $this->getConnection()->query($query);
    }

    protected function installVersion2()
    {
        $query = "CREATE TABLE IF NOT EXISTS  `categories` (
              `id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
              `title`  varchar(255) NOT NULL ,
              `url`  varchar(255) NOT NULL ,
              `parent_id`  int(11) NULL DEFAULT NULL ,
              `lang_id`  int(11) UNSIGNED NOT NULL ,
              `status`  int(1) NULL DEFAULT 1 ,
              `renderer`  varchar(255) DEFAULT NULL ,
              PRIMARY KEY (`id`),
              INDEX `languages` (`lang_id`) USING BTREE,
              UNIQUE INDEX `url` (`url`) USING BTREE
              )ENGINE=MyISAM";
        return $this->getConnection()->query($query);
    }

    protected function installVersion3()
    {
        $query = "CREATE TABLE IF NOT EXISTS `page_categories` (
            `id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
            `page_id`   int(11) UNSIGNED NOT NULL ,
            `category_id`   int(11) UNSIGNED NOT NULL ,
            PRIMARY KEY (`id`),
            INDEX `page_id` (`page_id`) USING BTREE,
            INDEX `category_id` (`category_id`) USING BTREE
            )ENGINE=MyISAM;";
        return $this->getConnection()->query($query);
    }

    protected function installVersion4()
    {
        $query = "CREATE TABLE IF NOT EXISTS `page_tags` (
            `id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
            `page_id`   int(11) UNSIGNED NOT NULL ,
            `tag_id`   int(11) UNSIGNED NOT NULL ,
            PRIMARY KEY (`id`),
            INDEX `page_id` (`page_id`) USING BTREE,
            INDEX `tag_id` (`tag_id`) USING BTREE
            )ENGINE=MyISAM;";
        return $this->getConnection()->query($query);
    }


}
