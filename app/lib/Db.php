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
    protected $_pdo;

    public function __construct()
    {
        /*$this->_db = mysql_connect(APP_DB_HOST, APP_DB_USERNAME, APP_DB_PASSWORD) or die("Can't connect to db");
        if (!mysql_select_db(APP_DB_DATABASE, $this->_db)) {
            $err = mysql_errno($this->_db);
            if ($err == 1049) {
                //TODO default blog install script;
            }
            throw new Exception(mysql_error($this->_db));
        };*/


        $this->_pdo = new PDO('mysql:host=localhost;dbname=' . APP_DB_DATABASE, APP_DB_USERNAME, APP_DB_PASSWORD);

    }

    public function getConnection()
    {
        return $this->_pdo;
    }

    public function bindAndExecute($sql, $bindParams)
    {
        $this->_pdo->prepare($sql);
        foreach ($bindParams as $key => $value) {
            //$this->_pdo->setAttribute()
        }
    }


    public function query($query)
    {
        $res = $this->_pdo->query($query);
        if (!$res) {
            throw new Exception("Error in query: $query", $this->_pdo->errorCode());
        }
        return $res;
    }


}
