<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Shavkat
 * Date: 10/22/13
 * Time: 8:58 PM
 */
class Menu extends Module
{
    protected $_route = 'menu';
    protected $_predefinedFunctions = array(
        'getMenuData'
    );

    protected function _init()
    {
        $translator = new MenuModel();
    }

    /**
     * @param $word
     * @return string
     * suppose we translate it
     */
    public function translate($word)
    {
        return strtoupper($word);
    }

    public function getMenuData($alias = false)
    {
        if ($alias) {
            $menuModel = $this->getModel();
            return $menuModel->getMenuData($alias);
        }
        return array();
    }
}

class MenuModel extends Model
{
    protected $_table = 'menu_item';
    protected $_version = 2;

    public function getMenuData($group)
    {
        $data  = array();
        $query = "SELECT * FROM {$this->_table} WHERE group_id IN (SELECT id FROM menu_group WHERE code='$group') ORDER BY `order`";
        if ($rows = ($this->query($query))) {
            while ($row = mysql_fetch_assoc($rows)) $data[] = $row;
        }
        return $data;
    }

    protected function installVersion1()
    {
        $query = "CREATE TABLE `menu_group` (
        `id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
        `title`  varchar(255) DEFAULT NULL ,
        `code`  varchar(50) NOT NULL ,
        `lang_id`  int(11) UNSIGNED NOT NULL ,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`lang_id`) REFERENCES `languages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
        INDEX `languages` (`lang_id`) USING BTREE
        )ENGINE=InnoDB";
        $this->getConnection()->query($query);
    }

    protected function installVersion2()
    {
        $query = "CREATE TABLE `{$this->_table}` (
        `id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
        `parent_id`  int(11) NOT NULL ,
        `group_id`  int(11) UNSIGNED NOT NULL ,
        `order`  int(2) UNSIGNED NOT NULL ,
        `status`  int(1) NOT NULL DEFAULT 1 ,
        `caption`  varchar(255) NOT NULL ,
        `link`  varchar(255) DEFAULT NULL ,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`group_id`) REFERENCES `menu_group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
        INDEX `menu_group` (`group_id`) USING BTREE
        ) ENGINE=InnoDB";
        $this->getConnection()->query($query);
    }
}
