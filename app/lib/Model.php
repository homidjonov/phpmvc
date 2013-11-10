<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Shavkat
 * Date: 10/23/13
 * Time: 1:01 AM
 */
class Model extends Object
{
    protected $_table = 'models';
    protected $_version = 1;
    protected static $_instance;
    protected $_id;

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
            $this->installUpdates();
        }
        parent::__construct();
    }

    public function installUpdates()
    {
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

    protected function installVersion1()
    {
        $query = "CREATE TABLE IF NOT EXISTS `{$this->_table}` (
        `model_id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
        `version`  int(3) NOT NULL DEFAULT 0 ,
        `name`  varchar(30) NOT NULL ,
        PRIMARY KEY (`model_id`),
        UNIQUE INDEX `name` (`name`) USING BTREE
        );";
        return $this->getConnection()->query($query);
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

    protected function getCount($query)
    {
        $result = $this->getConnection()->query($query);
        if ($row = mysql_fetch_row($result)) {
            return (int)$row[0];
        }
        return 0;
    }

    protected function loadModelCollection($query, $model = false, Pagination $p = null)
    {
        $collection = array();
        if (!$model) {
            $model = get_class($this);
        }
        if ($p instanceof Pagination) {
            $query .= sprintf(" LIMIT %s, %s;", $p->getCurrentPage() - 1, $p->getPageLimit());
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
            $this->_id = (int)$row['id'];
            $this->assignData($row);
        }
        return $this;
    }

    public function loadById($id)
    {
        $query = "SELECT * FROM {$this->_table} WHERE `{$this->_idFieldName}`='$id'";
        return $this->loadOneModel($query);
    }


}
