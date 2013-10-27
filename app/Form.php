<?php
class Form
{
    protected $_id;
    protected $_action;
    protected $_method = 'post';
    protected $_class = 'form-data';
    protected $_enctype;
    protected $_elementWrapper;
    protected $_elements = array();
    protected $_elementTypes = array(
        'text',
        'password',
        'select',
        'radio',
        'checkbox',
        'button',
        'html',
        'fieldset',
        'submit',
    );

    protected $_validation = array();

    public function __construct()
    {
        $this->_id     = 'form_' . App::getRequest()->getFullActionName();
        $this->_action = App::getRequest()->getRequestUrl();
    }

    public function setAction($action)
    {
        $this->_action = $action;
        return $this;
    }

    public function setMultiPartFormData()
    {
        $this->_enctype = "enctype='multipart/form-data'";
        return $this;
    }

    public function setMethod($method)
    {
        $this->_method = $method;
        return $this;
    }

    public function setClass($class)
    {
        $this->_class = $class;
        return $this;
    }

    public function setElementWrapper($tag)
    {
        $this->_elementWrapper = $tag;
        return $this;
    }

    public function addLabel($for, $caption = true)
    {
        $this->_elements[] = "<label for='$for'>$caption</label>";
    }

    protected $_validationErrors = array();

    public function addValidationError($msg)
    {
        $this->_validationErrors[] = $msg;
    }

    /***
     * @param      $id
     * @param      $type "text" | "select"
     * @param      $params
     * @param bool $validation
     */
    public function addElement($type, $id, $params, $validation = false)
    {
        if (in_array($type, $this->_elementTypes)) {
            if (!isset($params['name'])) $params['name'] = $id;
            $this->_elements[$id] = array(
                'type'     => $type,
                'params'   => $params,
                'validate' => ($validation != false)
            );

            if ($validation) {
                $this->_validation[$id] = $validation;
            }
        }
    }

    protected $_hasFiledSet = false;

    public function addFieldSet($id, $legend)
    {
        $this->_hasFiledSet = true;
    }

    protected function renderValidationMessages()
    {
        $html    = "";
        $wrapper = "<div class='validation-messages'>%s</div>";
        foreach ($this->_validationErrors as $message) {
            $html .= "<p>$message</p>";
        }
        return sprintf($wrapper, $html);
    }

    protected function renderElements()
    {
        $elements = array();
        $wrapper  = "%s\n%s\n";
        if ($this->_elementWrapper) {
            $wrapper = "<{$this->_elementWrapper}>%s\n%s</{$this->_elementWrapper}>\n";
        }
        foreach ($this->_elements as $id => $element) {
            $invoke = 'render' . ucfirst(strtolower($element['type']));
            $label  = (isset($element['params']['label'])) ? $this->renderLabel($id, $element['params']['label']) : '';
            if (method_exists($this, $invoke)) {
                $elementHtml = $this->$invoke($id, $element['params']);
            } else {
                $type        = $element['type'];
                $params      = $this->renderParams($element['params']);
                $elementHtml = "<input type='$type' $params />";
            }
            $elements[] = sprintf($wrapper, $label, $elementHtml);
        }
        return implode("\n", $elements);
    }

    public function render()
    {
        $html = "\n<form id='{$this->_id}' method='{$this->_method}' action='{$this->_action}' accept-charset='utf-8' {$this->_enctype}>\n";
        $html .= $this->renderValidationMessages();
        $html .= $this->renderElements();
        $html .= "</form>";
        return $html;
    }


    /**__________________________________RENDER_ELEMENTS______________________________**/


    protected function renderLabel($for, $caption)
    {
        return "<label for='$for'>$caption</label>";
    }

    protected function renderParams($params)
    {
        $html = "";
        unset($params['label']);
        unset($params['options']);
        foreach ($params as $param => $value) {
            $value = addcslashes($value, '\',"');
            $html .= " $param='$value'";
        }
        return $html;
    }

    protected function renderText($id, $params)
    {
        $params = $this->renderParams($params);
        $html   = "<input type='text' $params />";
        return $html;
    }


    protected $_values;

    public function getValue($elementName)
    {

    }


    public function init()
    {
        foreach ($this->_elements as $id => &$element) {
            if (in_array($element['type'], array('password', 'submit'))) continue;
            $name                       = $element['params']['name'];
            $value                      = App::getRequest()->getPost($name);
            $element['params']['value'] = $value;
        }
    }
}