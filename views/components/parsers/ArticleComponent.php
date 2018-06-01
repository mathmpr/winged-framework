<?php

namespace Site\Component;


use Winged\Components\ComponentParser;
use Winged\File\File;

/**
 * Class ArticleComponent
 * @package Site\Component
 */
class ArticleComponent extends ComponentParser {

    public function articles(){
        $ndom = \pQuery::parseStr('');
        foreach ($this->component->articles as $art){
            $DOM = \pQuery::parseStr($this->DOM->html());
            $DOM->query('h2')->text($art->titulo);
            $ndom->append($DOM->html());
        }
        $this->DOM = $ndom;
    }

}