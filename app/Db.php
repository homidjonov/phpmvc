<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Shavkat
 * Date: 10/23/13
 * Time: 1:01 AM
 */
class Db
{

    protected static $_instance;

    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new Db();
        }
        return self::$_instance;
    }


    protected $_db;

    public function __construct()
    {
        $this->_db = mysql_connect(APP_DB_HOST, APP_DB_USERNAME, APP_DB_PASSWORD) or die("Can't connect to db");
        mysql_select_db(APP_DB_DATABASE, $this->_db);
    }


    public function query($query)
    {
        if ($result = mysql_query($query, $this->_db)) return $result;
        throw new ErrorException(mysql_error($this->_db));
    }

}
