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
        'module_before_run',
        'page_before_cache',
        'module_after_render',
        'pagemodel_after_save',
    );

    public function pagemodel_after_save($params)
    {
        if ($model = $params->getData('model')) {
            if ($url = $model->getUrl()) {
                $fileName = APP_CACHE_PAGE_DIR . md5($this->getUrl($url)) . '.cache';
                if (file_exists($fileName)) {
                    unlink($fileName);
                }
            }
        }
    }

    public function page_before_cache($params)
    {
        $canCache = $params->getData('can_cache');
        $canCache &= APP_CACHE_ENABLED & !$this->getRequest()->hasPost() & $this->getRequest()->getAction() != '404';
        $params->setData('can_cache', $canCache);
    }

    public function module_before_run($params)
    {
        if ($this->canCacheThisRequest()) {
            $cache = $this->getFileNameForRequest();
            if (file_exists($cache)) {
                try {
                    $params->setData('module', false);
                    include_once $cache;
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
                $content = $params->getData('content');
                file_put_contents($this->getFileNameForRequest(), $content);
            } catch (Exception $e) {
                if ($e->getCode() == 2) {
                    $this->fixCacheDirs();
                }
            }
        }
    }

    protected function canCacheThisRequest()
    {
        $canCacheThisPage = true;
        App::runObserver('page_before_cache', array('can_cache' => &$canCacheThisPage));
        return $canCacheThisPage;
    }

    protected function getFileNameForRequest()
    {
        $fileName = md5($this->getUrl(trim($this->getRequest()->getOrigRequestUrl(), '/?'))) . '.cache';
        return APP_CACHE_PAGE_DIR . $fileName;
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



