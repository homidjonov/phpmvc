<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Shavkat
 * Date: 10/22/13
 * Time: 8:58 PM
 */
class Debug extends Module
{
    protected $_route;
    protected $_defaultAction = 'view';
    protected $_observers = array(
        'module_before_run',
        'module_after_run',
        'part_before_include',
        'part_after_include',
        'part_before_output',
        'part_after_output',
    );


    /**
     * observers
     */

    public function module_before_run($params)
    {
        $module = $params['module'];
        //echo $module->getName() . " module is running and setted to null<br>";
    }

    public function module_after_run($params)
    {

    }

    public function part_before_include($params)
    {
        if (!App::canDebugParts()) return;
        $part  = $params['part'];
        $alias = $params['alias'];
        $file  = explode("\\template\\", $params['file']);
        $file  = $file[1];
        if (!in_array($part, array('head', 'template', 'meta', 'head_part', 'body_before_end')))
            echo "<span class='part_border'><div class='info'>$alias ($file)</div></span>";
        else
            echo "\n<!--DEBUG PART [$alias] in ($file) -->\n";
    }

    public function part_after_include($params)
    {
        if (!App::canDebugParts()) return;
        $part  = $params['part'];
        $alias = $params['alias'];
        echo "\n<!--END PART [$alias]-->\n";
    }

    public function part_before_output($params)
    {
        if (!App::canDebugParts()) return;
        $part  = $params['part'];
        $alias = $params['alias'];
        $file  = $this->getRequest()->getFullActionName();
        if (!in_array($part, array('head', 'template', 'meta', 'head_part', 'body_before_end')))
            echo "<span class='part_border'><div class='info'>$alias ($file)</div></span>";
        else
            echo "\n<!--DEBUG PART [$alias] in ($file) -->\n";
    }

    public function part_after_output($params)
    {
        if (!App::canDebugParts()) return;
        $part  = $params['part'];
        $alias = $params['alias'];
        echo "\n<!--END PART [$alias]-->\n";
    }

}

