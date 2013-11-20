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
    protected $_route = 'page:category:tag:content:post';
    protected $_predefinedFunctions = array('getStaticBlock');


    protected function _initAdmin()
    {
        $this->addAdminMenu('page_index', 'Content', array(
            'content_index'  => 'Contents',
            'content_add'    => 'New Content',
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
                $this->renderPage($page);
            } else {
                $this->forward('category');
            }
        } else {
            $this->forward('home');
        }
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
                return $this->renderCategory($category);
            }
        }
        $this->_defaultNoRouteAction();
    }

    /**
     * @param $page PageModel
     */
    protected function renderPage($page)
    {
        $this->_title       = $page->getData('title');
        $this->_keywords    = $page->getData('meta_keywords');
        $this->_description = $page->getData('meta_description');
        $this->setBodyClassName('content_' . $page->getData('type'));
        $this->setRenderer('content', $page->getData('type'));
        $this->render(array('page' => $page));
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
        $this->setRenderer('content', $category->getData('renderer'));
        $this->render(array('category' => $category));
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
    public function adminContentIndex()
    {
        $this->_title = 'Category Management';
        $this->setPart('content', $this->getContentGrid());
        $this->render();
    }

    public function adminContentAdd()
    {
        $this->_title = 'Create New Page';
        $this->setPart('form', $this->getPostEditForm());
        $this->render();
    }

    protected function _loadPageModel()
    {
        $model = new PageModel();
        if ($id = intval($this->getRequest()->getParam('id'))) {
            $model->loadById($id);
        }
        return $model;
    }

    public function adminPostEdit()
    {
        $this->adminPageEdit();
    }

    public function adminPageEditorUpload()
    {
        $result = array('success' => 1, 'url' => '');
        try {
            $ext         = explode('.', $_FILES['file']['name']);
            $filename    = time() . '.' . $ext[1];
            $destination = App::getMediaDir('content') . $filename;
            $location    = $_FILES["file"]["tmp_name"];
            move_uploaded_file($location, $destination);
            $result['url']   = App::getMediaUrl('content/' . $filename);
            $result['image'] = $filename;
        } catch (Exception $e) {
            App::log($e);
            $result['message'] = $e->getMessage();
            $result['success'] = 0;
        }
        $this->returnJson($result);
    }

    public function adminPageEdit()
    {
        $model = $this->_loadPageModel();
        if ($this->getRequest()->hasPost()) {
            try {
                $data = new Object($_POST);
                $model
                    ->setData('title', $data->getTitle())
                    ->setData('content', $data->getContent())
                    ->setData('intro', $data->getIntro())
                    ->setData('meta_keywords', $data->getMetaKeywords())
                    ->setData('meta_description', $data->getMetaDescription())
                    ->setData('author', $data->getAuthor())
                    ->setData('url', $data->getUrl())
                    ->setData('image', $data->getImage())
                    ->setData('status', $data->getStatus())
                    ->save();

                $this->getSession()->addSuccess("Page updated successfully");
                if ($model->getId() && $this->getRequest()->getParam('save_back')) {
                    $this->redirect($model->getAdminEditLink());
                }
                $this->redirect($this->getAdminUrl('content_index'));
            } catch (Exception $e) {
                $this->getSession()->addError($e->getMessage());
            }
        }
        if ($model->getId() || $this->getRequest()->hasPost()) {
            $form         = $this->getPostEditForm()->loadModel($model);
            $this->_title = sprintf('Edit Page "%s"', $model->getTitle());
            $this->setPart('form', $form);
            $this->render();
        } else {
            $this->getSession()->addError('Content not found');
            $this->redirect($this->getAdminUrl('content_index'));
        }
    }

    protected function getContentGrid()
    {
        $page  = new PageModel();
        $table = new Grid();
        $table
            ->setPanel('Content Management')
            ->setModel($page)
            ->addColumn('id', array(
                'title' => 'ID',
                'width' => '40px'
            ))
            ->addColumn('title')
            ->addColumn('views')
            ->addColumn('status', array(
                'type'    => Grid::TYPE_OPTION,
                'options' => $page->getStatusOptions(),
            ))
            ->addColumn('type', array(
                'type'    => Grid::TYPE_OPTION,
                'options' => $page->getTypeOptions(),
            ))
            ->addColumn('created', array(
                'type'  => Grid::TYPE_DATETIME,
                'order' => 'DESC'
            ))
            ->addColumn('edit', array(
                'type'   => Grid::TYPE_ACTION,
                'action' => 'getAdminEditLink'
            ));
        return $table;
    }

    protected function getPostEditForm()
    {
        $form = new Form();
        $form
            ->setClass('form-horizontal')

            ->setElementWrapper('div', 'form-group')
            ->addTab('tab_2', 'Content')
            ->addElement('text', 'title', array(
                'placeholder' => $this->__('Title'),
                'wrapper'     => '<div class="col-sm-12">%s</div>',
            ))
            ->addElement('editor', 'content', array(
                'wrapper' => '<div class="col-sm-12">%s</div>',
            ))
            ->addTab('tab_3', 'Meta Data')
            ->addElement('editormini', 'intro', array(
                'label'       => $this->__('Introduction'),
                'label_class' => 'col-sm-2',
                'wrapper'     => '<div class="col-sm-10">%s</div>',
            ))
            ->addElement('text', 'meta_keywords', array(
                'label'       => $this->__('Meta Keywords'),
                'label_class' => 'col-sm-2',
                'wrapper'     => '<div class="col-sm-10">%s</div>',
            ))
            ->addElement('textarea', 'meta_description', array(
                'label'       => $this->__('Meta Description'),
                'label_class' => 'col-sm-2',
                'rows'        => '6',
                'wrapper'     => '<div class="col-sm-10">%s</div>',
            ))
            ->addTab('tab_4', 'Configuration')
            ->addElement('text', 'url', array(
                'label'       => $this->__('Content Link'),
                'placeholder' => $this->__('type-some-unique-url-identifier-of-the-page-here'),
                'label_class' => 'col-sm-2',
                'wrapper'     => '<div class="col-sm-10">%s</div>',
            ))
            ->addElement('image', 'image', array(
                'label'       => $this->__('Thumbnail'),
                'label_class' => 'col-sm-2',
                'ajax_upload' => App::getAdminUrl('page_editorUpload'),
                'placeholder' => $this->__('Select image or paste image url here'),
                'wrapper'     => '<div class="col-sm-10">%s</div>',
            ))
            ->addElement('text', 'author', array(
                'label'       => $this->__('Author or Source'),
                'label_class' => 'col-sm-2',
                'wrapper'     => '<div class="col-sm-5">%s</div>',
            ))
            ->addElement('select', 'status', array(
                'label'       => $this->__('Status'),
                'label_class' => 'col-sm-2',
                'wrapper'     => '<div class="col-sm-5">%s</div>',
                'options'     => PageModel::getStatusOptions()
            ));

        return $form->expand();
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

    static public function getStatusOptions()
    {
        return array(
            self::STATUS_ENABLED  => 'Published',
            self::STATUS_DISABLED => 'Draft',
        );
    }

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
        return $this->loadOneModel(false, array('url' => $url, 'type' => 'static', 'status' => 1));
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
            return App::getMediaUrl("content/$url");
        }
    }

    protected $_mediaC = 0;

    public function dataToImage($match)
    {
        list(, $img, $type, $base64, $end) = $match;
        $bin  = base64_decode($base64);
        $name = time() . "{$this->_mediaC}." . $type;
        $file = App::getMediaDir('content') . $name;
        file_exists($file) or file_put_contents($file, $bin);
        $url = App::getMediaUrl("content/$name");
        $this->_mediaC++;
        return "$img$url$end";
    }


    protected function _beforeSave()
    {
        //load from url
        if (strpos($this->getData('image'), 'http') === 0) {
            try {
                $content = file_get_contents($this->getData('image'));
                $ext     = 'jpg';
                $exts    = explode('.', $this->getData('image'));
                $exts    = strtolower($exts[count($exts) - 1]);
                if (in_array($exts, array('jpg', 'jpeg', 'bmp', 'png', 'gif', 'tiff'))) {
                    $ext = $exts;
                }
                $name        = time() . '.' . $ext;
                $destination = App::getMediaDir('content') . $name;
                file_put_contents($destination, $content);
                $this->setData('image', $name);
                AdminSession::getInstance()->addSuccess('Image successfully loaded from url.');
            } catch (Exception $e) {
                AdminSession::getInstance()->addError('Failed load image from url');
            }
        }

        //delete old thumbnail
        $oldImage = App::getMediaDir('content') . $this->getOrigData('image');
        if ($this->getOrigData('image') && $this->getOrigData('image') != $this->getData('image') && file_exists($oldImage)) {
            unlink($oldImage);
        }

        //upload editor images
        if ($content = $this->getContent()) {
            $content = preg_replace_callback('#(<img\s(?>(?!src=)[^>])*?src=")data:image/(gif|png|jpeg);base64,([\w=+/]++)("[^>]*>)#', array('PageModel', 'dataToImage'), $content);
            $this->setData('content', $content);
        }

        //fix created and updated date
        $this->setData('updated', date('Y-m-d H:i:s'));
        if (!$this->getId()) {
            $this->setData('created', date('Y-m-d H:i:s'));
        }

        //fix content url
        if (strlen(trim($this->getData('url'))) == 0 && $this->getData('title')) {
            $this->setData('url', $this->convertToUrl($this->getData('title')));
        }
        parent::_beforeSave();
    }

    protected function _afterSave()
    {
        parent::_afterSave();
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
        return App::getAdminUrl($this->getData('type') . '_edit', array($this->getIdFieldName() => $this->getId()));
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
        `intro`  mediumtext DEFAULT NULL ,
        `content`  text DEFAULT NULL ,
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
