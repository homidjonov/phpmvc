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
            if ($result = $this->getConnection()->query($query)) {
                $row = $result->fetch();
                return (int)$row['version'];
            }
        } catch (Exception $e) {
        }
        return 0;
    }

    /**
     * @return PDO
     */
    protected function getConnection()
    {
        return App::getDb()->getConnection();
    }

    protected function query($query)
    {
        return $this->getConnection()->query($query);
    }


    protected function getCount($query)
    {
        $result = $this->getConnection()->query($query);
        if ($row = $result->fetch()) {
            return (int)$row[0];
        }
        return 0;
    }


    public function fetchAll($query)
    {
        $data = array();
        if ($result = $this->query($query)) {
            $data = $result->fetchAll(PDO::FETCH_ASSOC);
        }
        return $data;
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
        while ($row = $result->fetch()) {
            $model = new $model();
            $model->assignData($row);
            $collection[] = $model;
        }
        return $collection;
    }

    public function whereQuery($query, array $where)
    {
        $binds = array();
        foreach ($where as $field => $value) {
            $binds [] = sprintf("`%s`=:%s", $field, $field);
        }
        $query  = sprintf("$query WHERE %s", implode(' AND ', $binds));
        $result = $this->getConnection()->prepare($query);
        $result->execute($where);
        return $result;
    }

    protected function loadOneModel($query = false, $where = null)
    {
        if (!$query) {
            $query = "SELECT * FROM {$this->_table} ";
        }
        if (is_array($where)) {
            $result = $this->whereQuery($query, $where);
        } else {
            $result = $this->query($query);
        }

        if ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $this->_id = (int)$row['id'];
            $this->assignData($row);
        }
        return $this;
    }

    public function loadByUrl($url)
    {
        $url = trim($url, '/');
        return $this->loadOneModel(false, array('url' => $url));
    }


    public function loadById($id)
    {
        return $this->loadOneModel(false, array($this->_idFieldName => $id));
    }

    public function __($word)
    {
        return Module::__($word);
    }

}
