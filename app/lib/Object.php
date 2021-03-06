<?php
/**
 * Created by PhpStorm.
 * User: Shavkat
 * Date: 11/10/13
 * Time: 12:19 PM
 */

class Object
{
    protected $_data = array();
    protected $_origData = array();
    protected $_idFieldName = 'id';

    public function __construct($data = array())
    {
        $this->_data = $data;
    }

    public function getIdFieldName()
    {
        return $this->_idFieldName;
    }

    public function getId()
    {
        return $this->getData($this->getIdFieldName());
    }

    public function getAllData()
    {
        return $this->_data;
    }

    public function getAllOrigData()
    {
        return $this->_origData;
    }

    public function getOrigData($key = false, $default = null)
    {
        if (isset($this->_origData[$key])) return $this->_origData[$key];
        return $default;
    }

    public function getData($key, $default = null)
    {
        if (isset($this->_data[$key])) return $this->_data[$key];
        return $default;
    }

    public function setData($key, $value)
    {
        $this->_data[$key] = $value;
        return $this;
    }

    protected function assignData($data)
    {
        if (is_array($data)) {
            $this->_data     = $data;
            $this->_origData = $data;
        }
        return $this;
    }

    public function addData(array $arr)
    {
        foreach ($arr as $index => $value) {
            $this->setData($index, $value);
        }
        return $this;
    }

    public function unsetData($key = null)
    {
        if (is_null($key)) {
            $this->_data = array();
        } else {
            unset($this->_data[$key]);
        }
        return $this;
    }

    /**
     * @param $method
     * @param $args
     * @return mixed
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

    protected function getChangedFields()
    {
        $fields = array();
        foreach ($this->_origData as $field => $value) {
            if ($this->getOrigData($field) != $this->getData($field)) {
                $fields[$field] = $this->getData($field);
            }
        }
        return $fields;
    }
}