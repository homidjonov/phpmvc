<?php
/**
 * Class Grid
 * @property Model $_model
 */
class Grid
{
    protected static $_instance;

    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new Grid();
        }
        return self::$_instance;
    }

    public function __construct()
    {
        if ($filer = App::getRequest()->getParam('filter')) {
            $this->_filter = $filer;
        }
    }


    protected $_model;
    protected $_filter;
    protected $_pageLimit = 10;
    protected $_columns = array();
    protected $_collection;
    protected $_header = true;
    protected $_pagination;
    protected $_panel;
    protected $_footer = false;
    protected $_class = 'table-striped table-hover';

    public function setModel(Model $model)
    {
        $this->_model = $model;
        return $this;
    }

    public function setCollection(array $collection)
    {
        $this->_collection = $collection;
        return $this;
    }

    public function setPagination(Pagination $p)
    {
        $this->_pagination = $p;
        return $this;
    }


    public function setPanel($p)
    {
        $this->_panel = $p;
        return $this;
    }

    /**
     * @return Pagination
     */
    public function getPagination()
    {
        return $this->_pagination;
    }

    public function getCollection()
    {
        return $this->_collection;
    }


    public function addColumn($field, $params = array())
    {
        if (!isset($params['type'])) $params['type'] = self::TYPE_DEFAULT;
        if (!isset($params['title'])) $params['title'] = ucfirst($field);
        $params['field']        = $field;
        $this->_columns[$field] = $params;
        return $this;
    }

    protected function initCollection()
    {
        if (!$this->_collection) {
            if (!$this->_model) {
                throw new Exception('Grid Model not defined');
            }
            if (!$this->_pagination) {
                $pagination        = new Pagination();
                $this->_pagination = $pagination->setPageLimit(App::getRequest()->getParam('limit', $this->_pageLimit))->setItemsCount($this->_model->getCount());
            }
            $order = null;
            foreach ($this->_columns as $field => $column) {
                if (isset($column['order'])) $order[$field] = $column['order'];
            }
            $this->_collection = $this->_model->getCollection($this->_pagination, false, $order);
        }
        return $this;
    }

    public function getIdentifier()
    {
        if ($this->_model) {
            return 'grid_' . $this->_model->getName();
        }
        if (!empty($this->_collection)) {
            return 'grid_' . $this->_collection[0]->getName();
        }
    }

    public function render()
    {
        $this->initCollection();
        $id   = $this->getIdentifier();
        $html = "<table class='table {$this->_class}' id='{$id}'>%s\n%s\n%s</table>";
        $html = sprintf($html, $this->renderHeader(), $this->renderRows(), $this->renderFooter());
        if ($this->_panel) {
            $pager = $this->getPagination()->render();
            $html  = "<div class='panel panel-default'>
                    <div class='panel-heading'><h3 class='panel-title'>{$this->_panel}</h3></div>
                    <div class='panel-body'>$html</div>
                    <div class='panel-footer'>$pager</div>
                </div>";
        }
        return $html;
    }

    protected function renderHeader()
    {
        if ($this->_header) {
            $html = "<thead><tr>\n%s\n</tr></thead>";
            $cols = array();
            foreach ($this->_columns as $column) {
                $css    = $this->getColStyle($column);
                $cols[] = "<th $css>{$column['title']}</th>";
            }
            return sprintf($html, implode("\n", $cols));
        }
        return '';
    }

    protected function getColStyle($column)
    {
        $styles = array('width', 'height');
        $css    = "";
        foreach ($styles as $style) {
            if (isset($column[$style])) $css .= "$style:$column[$style];";
        }
        return " style='$css'";
    }

    protected function renderRows()
    {
        $rows = array();
        foreach ($this->_collection as $item) {
            $cols = array();
            foreach ($this->_columns as $field => $column) {
                $cols[] = $this->renderColumn($item, $column);
            }
            $rows[] = sprintf("<tr>%s</tr>", implode("\n", $cols));
        }
        return sprintf("<tbody>\n%s\n</tbody>", implode("\n", $rows));
    }

    protected function renderColumn(Model $item, array $column)
    {
        $value = $item->getData($column['field']);
        $type  = $column['type'];
        switch ($type) {
            case self::TYPE_ACTION:
                $action = $column['action'];
                $value  = $item->$action();
                break;
            case self::TYPE_DATE:
                $value = App::formatDate($value);
                break;
            case self::TYPE_DATETIME:
                $value = App::formatDateTime($value);
                break;
            case self::TYPE_NUMBER:
                $value = intval($value);
                break;
            case self::TYPE_OPTION:
                if (isset($column['options'][$value])) $value = $column['options'][$value];
                break;
        }

        return "<td>$value</td>";
    }

    protected function renderFooter()
    {
        return "";
    }

    const TYPE_DEFAULT  = 'default';
    const TYPE_ACTION   = 'action';
    const TYPE_NUMBER   = 'number';
    const TYPE_OPTION   = 'option';
    const TYPE_DATE     = 'date';
    const TYPE_DATETIME = 'datetime';

}