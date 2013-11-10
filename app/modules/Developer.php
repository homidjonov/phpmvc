<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Shavkat
 * Date: 10/22/13
 * Time: 8:58 PM
 */
class Developer extends Module
{
    protected $_route;
    protected $_defaultAction = 'view';
    protected $_observers = array(
        'module_before_run',
        'part_before_include',
        'part_after_include',
        'part_before_output',
        'part_after_output',
    );

    /**
     * @param $params
     * Dynamic renderer of category posts list content :)
     */
    public function module_before_render($params)
    {
        /**
         * @var $module Module
         */
        $module = $params->getModule();

        if (in_array(App::getRequest()->getAction(), array('category', 'categoryview'))) {
            if ($category = $module->getParam('category')) {
                if ($renderer = $category->getRenderer()) {
                    /*try {
                        $matches = array();
                        preg_match_all('/{%([^%]+)%}/sim', $renderer, $matches);
                        if (isset($matches[0])) {
                            foreach ($matches[0] as $tag) {
                                if ($tag = trim($tag)) {
                                    $renderer = str_replace($tag, $category->getData(trim($tag, '{%%}')), $renderer);
                                }
                            }
                            $module->setPart('content', $renderer);
                        }

                    } catch (Exception $e) {
                        App::log($e, true);
                    }*/
                }
            }
        }
    }

    /**
     * observers
     */

    public function module_before_run($params)
    {


    }


    public function part_before_include($params)
    {
        if (!App::canDebugParts()) return;
        $part  = $params->getPart();
        $alias = $params->getAlias();
        $file  = explode("\\template\\", $params->getFile());
        $file  = $file[1];
        if (!in_array($part, array('head', 'template', 'meta', 'head_part', 'body_before_end')))
            echo "<span class='part_border'><div class='info'>$alias ($file)</div></span>";
        else
            echo "\n<!--DEBUG PART [$alias] in ($file) -->\n";
    }

    public function part_after_include($params)
    {
        if (!App::canDebugParts()) return;
        $alias = $params->getAlias();
        echo "\n<!--END PART [$alias]-->\n";
    }

    public function part_before_output($params)
    {
        if (!App::canDebugParts()) return;
        $part  = $params->getPart();
        $alias = $params->getAlias();
        $file  = $this->getRequest()->getFullActionName();
        if (!in_array($part, array('head', 'template', 'meta', 'head_part', 'body_before_end')))
            echo "<span class='part_border'><div class='info'>$alias ($file)</div></span>";
        else
            echo "\n<!--DEBUG PART [$alias] in ($file) -->\n";
    }

    public function part_after_output($params)
    {
        if (!App::canDebugParts()) return;
        $alias = $params->getAlias();
        echo "\n<!--END PART [$alias]-->\n";
    }

}

