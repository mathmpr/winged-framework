<?php

class PaginateCliente
{

    private $_search;
    private $_limit;
    private $_page;
    private $_total;

    public function __construct($count, Model $search)
    {
        $this->_search = $search;
        $this->_total = $count;
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
            $results = $this->_search->limit((($this->_page - 1) * $this->_limit), $this->_limit)->execute();
        }

        $result = new stdClass();
        $result->page = $this->_page;
        $result->limit = $this->_limit;
        $result->total = $this->_total;
        $result->data = $results;

        return $result;
    }

    public function createLinks($links, $page_name = '')
    {
        if ($this->_limit == 'all') {
            return '';
        }

        $last = ceil($this->_total / $this->_limit);

        $start = (($this->_page - $links) > 0) ? $this->_page - $links : 1;
        $end = (($this->_page + $links) < $last) ? $this->_page + $links : $last;

        $html = '<ul class="pagination clearfix">';

        $class = ($this->_page == 1) ? "prev disabled" : "";
        $href = ($this->_page == 1) ? "javacript:;" : Winged::$protocol . $page_name . '/pagina/' . ($this->_page - 1);
        $html .= '<li class="' . $class . '"><a href="' . $href . '">página anterior</a></li>';

        if ($start > 1) {
            $html .= '<li class="nopad"><a href="' . Winged::$protocol . $page_name . 'pagina/1">1</a></li>';
            $html .= '<li class="nopad disabled"><a href="javascript:;">...</a></li>';
        }

        for ($i = $start; $i <= $end; $i++) {
            $class = ($this->_page == $i) ? "active nopad" : "nopad";
            $html .= '<li class="' . $class . '"><a href="' . Winged::$protocol . $page_name . '/pagina/' . $i . '">' . $i . '</a></li>';
        }

        if ($end < $last) {
            $html .= '<li class="nopad disabled"><a href="javascript:;">...</a></li>';
            $html .= '<li class="nopad"><a href="' . Winged::$protocol . $page_name . '/pagina/' . $last . '">' . $last . '</a></li>';
        }

        $class = ($this->_page == $last) ? "next disabled" : "";
        $href = ($this->_page == $last) ? "javacript:;" : Winged::$protocol . $page_name . '/pagina/' . ($this->_page + 1);
        $html .= '<li class="' . $class . '"><a href="' . $href . '">próxima página</a></li>';

        $html .= '</ul>';

        return $html;
    }


}