<?php

namespace Site\Components;

use Winged\Components\ComponentParser;

/**
 * Class HomeComponent
 * @package Site\Component
 */
class HomeComponent extends ComponentParser
{

    function begin(){
        $this->component->get('Article')->articles();
        $this->render();
    }

}