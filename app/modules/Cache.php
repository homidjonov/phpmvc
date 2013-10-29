<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Shavkat
 * Date: 10/22/13
 * Time: 8:58 PM
 */
class Cache extends Module
{
    protected $_observers = array(
        'module_after_render',
        'module_before_run',
        'page_before_cache',
    );

    public function page_before_cache($params)
    {
        $params['can_cache'] &= APP_CACHE_ENABLED;
    }

    public function module_before_run($params)
    {
        $module = $params['module'];

        if ($this->canCacheThisRequest()) {
            $cache = $this->getFileNameForRequest();
            if (file_exists($cache)) {
                try {
                    $params['module'] = false;
                    include_once $cache;
                    //$content = file_get_contents($cache);
                    //echo $content;
                } catch (Exception $e) {
                    App::log($e);
                }
            }
        }
    }

    public function module_after_render($params)
    {
        if ($this->canCacheThisRequest()) {
            try {
                $content = $params['content'];
                file_put_contents($this->getFileNameForRequest(), $content);
            } catch (Exception $e) {
                if ($e->getCode() == 2) {
                    $this->fixCacheDirs();
                }
                App::log($e);
            }

        }
    }


    protected function canCacheThisRequest()
    {
        $canCacheThisPage = true;
        App::runObserver('page_before_cache', array('can_cache' => & $canCacheThisPage));
        return $canCacheThisPage;
    }

    protected function getFileNameForRequest()
    {
        $fileName = md5($this->getRequest()->getRequestUrl()) . '.cache';
        return APP_CACHE_PAGE_DIR . $fileName;
    }

    public function _defaultAction()
    {
        $action = explode('_', $this->getRequest()->getOrigAction());

        $this->forward('index');
    }

    protected function fixCacheDirs()
    {
        if (!file_exists(APP_CACHE_DIR)) {
            mkdir(APP_CACHE_DIR);
        }
        if (!file_exists(APP_CACHE_PAGE_DIR)) {
            mkdir(APP_CACHE_PAGE_DIR);
        }
        if (!file_exists(APP_CACHE_PART_DIR)) {
            mkdir(APP_CACHE_PART_DIR);
        }
    }

}



