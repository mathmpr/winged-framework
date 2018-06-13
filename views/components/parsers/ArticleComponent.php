<?php

namespace Site\Component;


use Winged\Components\ComponentParser;

/**
 * Class ArticleComponent
 * @package Site\Component
 */
class ArticleComponent extends ComponentParser
{
    public function articles()
    {
        $empty = $this->getEmptyDOM();
        foreach ($this->component->articles as $art) {
            $original = $this->getOriginalDOM();
            $original->query('h2')->text($art->titulo);
            $original->query('img')->attr('src', $art->getImagem());
            $original->query('img')->attr('alt', $art->titulo);
            $original->query('img')->attr('title', $art->titulo);
            $empty->append($original->html());
        }
        $this->DOM = $empty;
    }


}