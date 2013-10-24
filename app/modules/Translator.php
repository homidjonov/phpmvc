<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Shavkat
 * Date: 10/22/13
 * Time: 8:58 PM
 */
class Translator extends Module
{
    protected $_route = 'translator';


    protected function _init()
    {
        //TODO laod translations
        $translator = new TranslatorModel();
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

}

class TranslatorModel extends Model
{
    protected $_table = 'translations';
    protected $_version = 2;


    protected function installVersion1()
    {
        $query = "CREATE TABLE `languages` (
        `id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
        `lang`  varchar(3) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
        `name`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
        PRIMARY KEY (`id`)
        )ENGINE=InnoDB";
        $this->getConnection()->query($query);
    }

    protected function installVersion2()
    {
        $query = "CREATE TABLE `{$this->_table}` (
        `id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
        `word`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
        `lang_id`  int(11) UNSIGNED NOT NULL ,
        `translation`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`lang_id`) REFERENCES `languages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
        INDEX `word` (`word`) USING BTREE ,
        INDEX `lang_id` (`lang_id`) USING BTREE
        )ENGINE=InnoDB";
        $this->getConnection()->query($query);
    }
}
