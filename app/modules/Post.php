<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Shavkat
 * Date: 10/22/13
 * Time: 8:58 PM
 */
class Post extends Module
{
    protected $_route = 'post';

    public function viewAction()
    {
        //echo App::getRequest()->getParam('k');
        $this->render();
    }

    protected function defaultAction()
    {

        $this->_defaultNoRouteAction();
    }

}

class PostModel extends Model
{
    protected $_table = 'posts';
    protected $_version = 1;

    public function loadPostByUrl($url)
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
        `image`  varchar(255) NULL ,
        `description`  text NULL ,
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
}
