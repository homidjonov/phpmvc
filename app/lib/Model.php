<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Shavkat
 * Date: 10/23/13
 * Time: 1:01 AM
 */
class Model
{

    protected $_table = 'models';
    protected $_version = 1;
    protected static $_instance;
    protected $_id;
    protected $_data;

    protected $_translateable;

    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new Model();
        }
        return self::$_instance;
    }

    public function __construct()
    {
        if (App::getIsDeveloperMode()) {
            $currentVersion   = $this->_version;
            $installedVersion = $this->getInstalledVersion();
            if ($installedVersion < $currentVersion) {
                $success = $installedVersion;
                $name    = get_class($this);
                for ($i = $installedVersion; $i <= $currentVersion; $i++) {
                    $method = "installVersion$i";
                    if (method_exists($this, $method)) {
                        $result = false;
                        if ($result = $this->$method()) {
                            $success = $i;
                        }
                    }
                }
                if ($success > $installedVersion) {
                    if ($installedVersion == 0) {
                        $query = "INSERT INTO `models`(`version`,`name`) VALUES ($success,'$name')";
                    } else {
                        $query = "UPDATE `models` SET `version`=$success WHERE `name`='$name'";
                    }
                    $this->getConnection()->query($query);
                }
            }
        }
    }

    protected function installVersion1()
    {
        $query = "CREATE TABLE IF NOT EXISTS `{$this->_table}` (
        `model_id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
        `version`  int(3) NOT NULL DEFAULT 0 ,
        `name`  varchar(30) NOT NULL ,
        PRIMARY KEY (`model_id`),
        UNIQUE INDEX `name` (`name`) USING BTREE
        );";
        $this->getConnection()->query($query);
    }

    protected function getInstalledVersion()
    {
        $name  = get_class($this);
        $query = "SELECT version FROM `models` WHERE `name`='$name'";

        try {
            $result = $this->getConnection()->query($query);
            if ($row = mysql_fetch_assoc($result)) {
                return (int)$row['version'];
            }
        } catch (Exception $e) {
        }
        return 0;
    }

    /**
     * @return Db
     */
    protected function getConnection()
    {
        return App::getDb();
    }

    protected function query($query)
    {
        return $this->getConnection()->query($query);
    }

    public function getId()
    {
        return $this->_id;
    }

    public function getData($key)
    {
        if (isset($this->_data[$key])) return $this->_data[$key];
        return false;
    }

    protected function assignData($data)
    {
        if (is_array($data)) {
            $this->_data = $data;
        }
        return $this;
    }

    protected function loadModelCollection($query, $model = false)
    {
        $collection = array();
        if (!$model) {
            $model = get_class($this);
        }
        $result = $this->getConnection()->query($query);
        while ($row = mysql_fetch_assoc($result)) {
            $model = new $model();
            $model->assignData($row);
            $collection[] = $model;
        }
        return $collection;
    }

    protected function loadOneModel($query)
    {
        $result = $this->getConnection()->query($query);
        if ($row = mysql_fetch_assoc($result)) {
            $this->_id   = (int)$row['id'];
            $this->_data = $row;
        }
        return $this;
    }

    /**
     * @param $method
     * @param $args
     * @return mixed
     * Predefined callable functions
     */
    public function __call($method, $args)
    {
        if (strpos($method, 'get') === 0) {
            $key = $this->_underscore(substr($method, 3));
            return $this->getData($key);
        }
    }

    protected function _underscore($name)
    {
        $result = strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $name));
        return $result;
    }

}
