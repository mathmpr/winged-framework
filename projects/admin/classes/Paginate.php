<?php

class Paginate
{
    private $_search;
    private $_limit;
    private $_page;
    private $_total;

    public function __construct($count, $search)
    {
        $this->_total = $count;
        $this->_search = $search;
    }

    /**
     * @param int $limit
     * @param int $page
     * @return stdClass
     */
    public function getData($limit = 10, $page = 1)
    {
        $this->_limit = $limit;
        $this->_page = $page;
        if ($this->_limit == 'all') {
            $results = $this->_search->execute();
        } else {
            $results = $this->_search->limit((((int)$this->_page - 1) * $this->_limit), $this->_limit)->execute();
        }
        $result = new stdClass();
        $result->page = $this->_page;
        $result->limit = $this->_limit;
        $result->total = $this->_total;
        $result->data = $results;
        return $result;
    }

    private function getLink($number = 1)
    {
        $link = \Admin::buildGetUrl();
        if (is_int(strpos($link, 'page/'))) {
            $exp = explode('page/', $link);
            $c = array_shift($exp);
            $e = array_pop($exp);
            $e = explode('?', $e);
            $e = array_pop($e);
            return $c . 'page/' . $number . '?' . $e;
        }
    }

    public function createLinks($links)
    {
        if ($this->_limit == 'all') {
            return '';
        }

        $last = ceil($this->_total / $this->_limit);
        $start = (((int)$this->_page - $links) > 0) ? (int)$this->_page - $links : 1;
        $end = (((int)$this->_page + $links) < $last) ? (int)$this->_page + $links : $last;
        $html = '<ul class="pagination clearfix">';
        $class = ($this->_page == 1) ? "prev disabled" : "";
        $href = ($this->_page == 1) ? "javacript:;" : $this->getLink($this->_page - 1);
        $html .= '<li class="' . $class . '"><a href="' . $href . '"><i class="fa fa-angle-double-left"></i></a></li>';
        if ($start > 1) {
            $html .= '<li class="nopad"><a href="' . $this->getLink() . '">1</a></li>';
            $html .= '<li class="nopad disabled"><a href="javascript:;">...</a></li>';
        }
        for ($i = $start; $i <= $end; $i++) {
            $class = ($this->_page == $i) ? "active nopad" : "nopad";
            $html .= '<li class="' . $class . '"><a href="' . $this->getLink($i) . '">' . $i . '</a></li>';
        }

        if ($end < $last) {
            $html .= '<li class="nopad disabled"><a href="javascript:;">...</a></li>';
            $html .= '<li class="nopad"><a href="' . $this->getLink($last) . '">' . $last . '</a></li>';
        }
        $class = ($this->_page == $last) ? "next disabled" : "";
        $href = ($this->_page == $last) ? "javacript:;" : $this->getLink($this->_page + 1);
        $html .= '<li class="' . $class . '"><a href="' . $href . '"><i class="fa fa-angle-double-right"></i></a></li>';
        $html .= '</ul>';
        return $html;
    }

}