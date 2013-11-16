<?php
class Form
{
    protected $_id;
    protected $_action;
    protected $_method = 'post';
    protected $_class = 'form-data';
    protected $_enctype;
    protected $_elementWrapper;
    protected $_elementWrapperClass;
    protected $_elements = array();
    protected $_elementTypes = array(
        'text',
        'textarea',
        'editor',
        'editormini',
        'password',
        'hidden',
        'select',
        'button',
        'submit',
    );

    protected $_validation = array();

    public function __construct()
    {
        $this->_id        = 'form_' . App::getRequest()->getFullActionName();
        $this->_action    = '';
        $this->_tabId     = 'tab_' . App::getRequest()->getFullActionName();
        $this->_formClass = 'tab_' . App::getRequest()->getFullActionName();
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

    public function setElementWrapper($tag, $class = false)
    {
        $this->_elementWrapper      = $tag;
        $this->_elementWrapperClass = $class;
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
            if (!isset($params['label_class'])) $params['label_class'] = 'label-default';
            if (!isset($params['class'])) $params['class'] = 'form-control';
            $this->_elements[$id] = array(
                'type'     => $type,
                'params'   => $params,
                'validate' => ($validation != false)
            );

            if ($validation) {
                $this->_validation[$id] = $validation;
            }
        }
        return $this;
    }

    protected $_hasTab = false;

    public function addTab($id, $title)
    {
        $this->_hasTab        = true;
        $this->_elements[$id] = array('type' => 'tab', 'caption' => $title);
        return $this;
    }

    public function addFieldSet($id, $legend)
    {
        $this->_hasFiledSet = true;
    }

    protected function renderValidationMessages()
    {
        $html    = "";
        $wrapper = "<div class='alert alert-danger alert-dismissable'>
        <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
        %s</div>";
        foreach ($this->_validationErrors as $message) {
            $html .= "<p>$message</p>";
        }
        return ($html) ? sprintf($wrapper, $html) : '';
    }

    protected $_tabOpened;

    protected function renderElements()
    {

        $elements = array();
        $wrapper  = "%s\n%s\n";
        if ($this->_elementWrapper) {
            $wrapper = "<{$this->_elementWrapper} class='{$this->_elementWrapperClass}'>%s\n%s\n%s\n%s</{$this->_elementWrapper}>\n";
        }
        $active = 'active in';
        foreach ($this->_elements as $id => $element) {
            if ($element['type'] == 'tab') {
                if ($this->_tabOpened) {
                    $elements[] = "</{$this->_tabContentItemWrapper}>";
                }
                $elements[]       = "<{$this->_tabContentItemWrapper} class='{$this->_tabContentItemWrapperClass} $active' id='{$this->_tabId}_item_{$id}'>";
                $this->_tabOpened = true;
                $active           = '';
                continue;
            }
            $invoke     = 'render' . ucfirst(strtolower($element['type']));
            $label      = (isset($element['params']['label'])) ? $this->renderLabel($id, $element['params']['label'], $element['params']['label_class']) : '';
            $beforeHtml = (isset($element['params']['before'])) ? $element['params']['before'] : '';
            $afterHtml  = (isset($element['params']['after'])) ? $element['params']['after'] : '';

            if (method_exists($this, $invoke)) {
                $elementHtml = $this->$invoke($id, $element['params']);
            } else {
                $type        = $element['type'];
                $params      = $this->renderParams($element['params']);
                $elementHtml = "<input type='$type' $params />";
            }
            if (isset($element['params']['wrapper'])) {
                $elementHtml = sprintf($element['params']['wrapper'], $elementHtml);
            }
            $elements[] = sprintf($wrapper, $label, $beforeHtml, $elementHtml, $afterHtml);
        }
        if ($this->_tabOpened)
            $elements[] = "</{$this->_tabContentItemWrapper}>";
        $elements = implode("\n", $elements);
        if ($this->_hasTab) $elements = sprintf("<{$this->_tabContentWrapper} id='{$this->_tabId}' class='{$this->_tabContentWrapperClass}'>%s</{$this->_tabContentWrapper} >", $elements);
        return $elements;
    }

    protected $_tabId;
    protected $_tabHeadWrapper = 'ul';
    protected $_tabHeadWrapperClass = 'nav nav-tabs ';
    protected $_tabHeadItemWrapper = 'li';
    protected $_tabHeadItemWrapperClass = '';
    protected $_tabContentWrapper = 'div';
    protected $_tabContentWrapperClass = 'tab-content';
    protected $_tabContentItemWrapper = 'div';
    protected $_tabContentItemWrapperClass = 'tab-pane fade';


    public function renderTabHead()
    {
        if ($this->_hasTab) {
            $html     = "<{$this->_tabHeadWrapper} class='{$this->_tabHeadWrapperClass}'>%s</{$this->_tabHeadWrapper}>";
            $captions = array();
            $active   = "active";
            foreach ($this->_elements as $id => $element) {
                if (isset($element['type']) && $element['type'] == 'tab') {
                    $captions[] = "<{$this->_tabHeadItemWrapper} class='{$this->_tabHeadItemWrapperClass} $active'>
                <a data-toggle='tab' href='#{$this->_tabId}_item_{$id}'>{$element['caption']}</a>
                </{$this->_tabHeadItemWrapper}>";
                }
                $active = "";
            }
            return sprintf($html, implode("\n", $captions));
        }
        return '';
    }

    public function render()
    {
        $html     = "\n<form id='{$this->_id}' class='{$this->_class}' role='form' method='{$this->_method}' action='{$this->_action}' accept-charset='utf-8' {$this->_enctype}>%s\n%s</form>";
        $messages = $this->renderValidationMessages();
        $elements = $this->renderTabHead() . $this->renderElements();
        if ($this->_hasTab) $elements = sprintf("<div id='{$this->_tabId}' class='tabbable'>%s</div>", $elements);
        $html = sprintf($html, $messages, $elements);
        return $html;
    }


    /**__________________________________RENDER_ELEMENTS______________________________**/

    protected function renderLabel($for, $caption, $class = '')
    {
        return "<label for='$for' class='$class'>$caption</label>";
    }

    protected function renderParams($params)
    {
        $html   = "";
        $unSets = array('label', 'options', 'before', 'after', 'label_class', 'wrapper');
        foreach ($unSets as $key) unset($params[$key]);
        foreach ($params as $param => $value) {
            $value = htmlentities($value);
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

    protected function renderHidden($id, $params)
    {
        $params = $this->renderParams($params);
        $html   = "<input type='hidden' $params />";
        return $html;
    }

    protected function renderTextarea($id, $params)
    {
        $value = isset($params['value']) ? $params['value'] : '';
        unset($params['value']);
        $params = $this->renderParams($params);
        $html   = "<textarea type='text' $params >$value</textarea>";
        return $html;
    }

    protected function renderEditor($id, $params)
    {
        $value  = isset($params['value']) ? $params['value'] : '';
        $height = isset($params['height']) ? $params['height'] : 300;
        $html   = "
        <div id='$id'>$value</div>
        <script type='text/javascript'>
            $(document).ready(function () {
                $('#$id').summernote({
                    height: $height,
                    focus: true
                });
            });
        </script>";
        return $html;
    }

    protected function renderEditorMini($id, $params)
    {
        $value  = isset($params['value']) ? $params['value'] : '';
        $height = isset($params['height']) ? $params['height'] : 100;
        $html   = "
        <div id='$id'>$value</div>
        <script type='text/javascript'>
            $(document).ready(function () {
                $('#$id').summernote({
                    height: $height,
                    focus: true,
                    toolbar: [
                        ['style', ['style']],
                        ['style', ['bold', 'italic', 'underline', 'clear']],
                        ['fontsize', ['fontsize']],
                        ['color', ['color']],
                        ['para', ['paragraph']],
                        ['insert', ['link']]
                        //['insert', ['picture', 'link']],
                        //['table', ['table']],
                        //['help', ['help']]
                      ]
                });
            });
        </script>";
        return $html;
    }

    protected function renderButton($id, $params)
    {
        $text   = isset($params['caption']) ? $params['caption'] : '';
        $params = $this->renderParams($params);
        $html   = "<button $params >$text</button>";
        return $html;
    }

    protected function renderSelect($id, $params)
    {
        $options = '';
        if (isset($params['options'])) {
            foreach ($params['options'] as $value => $label) {
                $selected = (isset($params['value']) && $params['value'] == $value) ? "selected='selected'" : '';
                $options .= "<option value='{$value}' $selected>{$label}</option>";
            }
        }
        unset($params['value']);
        $params = $this->renderParams($params);
        $html   = "<select $params>$options</select>";
        return $html;
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

    public function expand()
    {
        App::runObserver('module_form_expand', array('form' => $this));
        return $this;
    }

    public function loadModel(Model $model)
    {
        foreach ($this->_elements as $id => $element) {
            if (in_array($element['type'], $this->_elementTypes)) {
                $name                                    = $element['params']['name'];
                $this->_elements[$id]['params']['value'] = $model->getOrigData($name);
            }
        }
        return $this;
    }
}