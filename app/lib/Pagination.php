<?php
class Pagination
{
    protected $_start;
    protected $_page;
    protected $_pages;
    protected $_limit;
    protected $_maxItem = 8;
    protected $_itemsCount = 0;
    protected $_url;

    protected static $_instance;

    protected $_itemRender = 'a';
    protected $_itemWrapper = 'li';
    protected $_itemWrapperActiveClass = 'active';
    protected $_itemWrapperDisableClass = 'disabled';
    protected $_groupWrapper = 'ul';
    protected $_groupWrapperClass = 'pagination';

    /**
     * @return Pagination
     */
    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new Pagination();
        }
        return self::$_instance;
    }

    public function setItemsCount($count)
    {
        if ($count === null) {
            throw new Exception("Unknown count");
        }
        $this->_itemsCount = $count;
        $this->_pages      = ceil($this->_itemsCount / $this->_limit);
        if ($this->_page > $this->_pages || $this->_page < 1) {
            $this->_page  = 1;
            $this->_pages = 0;
            $this->_limit = 0;
            /** I hate from face requests */
            App::getRequest()->redirect('/' . App::getRequest()->getDefaultRoute());
        }
        return $this;
    }

    public function setUrl($url)
    {
        $this->_url = $url;
        return $this;
    }

    public function __construct()
    {
        $this->_limit = MD_PAGE_POST_LIMIT;
        $this->_page  = (int)App::getRequest()->getParam('page', 1);
        $this->_url   = App::getRequest()->getRequestUrl();
    }

    public function getCurrentPage()
    {
        return $this->_page;
    }

    public function getPageLimit()
    {
        return $this->_limit;
    }

    public function setPageLimit($limit)
    {
        $this->_limit = $limit;
        return $this;
    }

    public function render()
    {
        $pages = $this->_pages;
        if ($pages > 1) {
            $wrapper = "<{$this->_groupWrapper} class='{$this->_groupWrapperClass}'>%s</{$this->_groupWrapper}>";
            $items   = array();
            $curPage = $this->_page;
            if ($curPage > $pages) $curPage = $pages;
            $maxItem = $this->_maxItem;

            if ($pages > $maxItem) {
                $delta = floor($maxItem / 2);
                $start = $curPage - $delta;
                $end   = $curPage + $delta;
                if ($start < 1) {
                    $end   = $end + (-1 * $start + 1);
                    $start = 1;
                }
                if ($end > $pages) {
                    $start = $start - ($end - $pages);
                    $end   = $pages;
                }
            } else {
                $start = 1;
                $end   = $pages;
            }
            $class   = ($curPage == 1) ? $this->_itemWrapperDisableClass : '';
            $prev    = $curPage - 1;
            $link    = ($curPage > 1) ? $this->_url . "?page=$prev" : '#';
            $items[] = "<{$this->_itemWrapper} class='$class'><a href='$link'><i class='fa fa-angle-left'></i> </a></{$this->_itemWrapper}>";

             if ($start > 1) {
                 $items[] = "<{$this->_itemWrapper}><a href='{$this->_url}?page=1'>1</a></{$this->_itemWrapper}>";
             }
             if ($start == 3) {
                 $items[] = "<{$this->_itemWrapper}><a href='{$this->_url}?page=2'>2</a></{$this->_itemWrapper}>";
             } elseif ($start > 3) {
                 $items[] = "<{$this->_itemWrapper} class='{$this->_itemWrapperDisableClass}'><a href='#'>...</a></{$this->_itemWrapper}>";
             }

            for ($i = $start; $i <= $end; $i++) {
                $class   = ($curPage == $i) ? $this->_itemWrapperActiveClass : '';
                $items[] = "<{$this->_itemWrapper} class='$class'><a href='{$this->_url}?page=$i'>$i</a></{$this->_itemWrapper}>";
            }


            if ($pages - $end == 2) {
                $num     = $pages - 1;
                $items[] = "<{$this->_itemWrapper}><a href='{$this->_url}?page=$num'>$num</a></{$this->_itemWrapper}>";
            } elseif ($pages - $end > 2) {
                $items[] = "<{$this->_itemWrapper} class='{$this->_itemWrapperDisableClass}'><a href='#'>...</a></{$this->_itemWrapper}>";
            }
            if ($pages - $end >= 1) {
                $items[] = "<{$this->_itemWrapper}><a href='{$this->_url}?page=$pages'>$pages</a></{$this->_itemWrapper}>";
            }
            $class   = ($curPage == $pages) ? $this->_itemWrapperDisableClass : '';
            $next    = $curPage + 1;
            $link    = ($curPage < $pages) ? $this->_url . "?page=$next" : '#';
            $items[] = "<{$this->_itemWrapper} class='$class'><a href='$link'><i class='fa fa-angle-right'></i> </a></{$this->_itemWrapper}>";
            return sprintf($wrapper, implode("\n", $items));
        }
        return '';
    }

}