<?php
/**
 * @author Niels A.D.
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2010 Niels A.D., 2014 Todd Burry
 * @license http://opensource.org/licenses/LGPL-2.1 LGPL-2.1
 * @package pQuery
 */

namespace pQuery;
/**
 * Node subclass for embedded tags like xml, php and asp
 */
class EmbeddedNode extends DomNode {

    /**
     * @var string
     * @internal specific char for tags, like ? for php and % for asp
     * @access private
     */
    var $tag_char = '';

    /**
     * @var string
     */
    var $text = '';

    /**
     * Class constructor
     * @param DomNode $parent
     * @param string $tag_char {@link $tag_char}
     * @param string $tag {@link $tag}
     * @param string $text
     * @param array $attributes array('attr' => 'val')
     */
    function __construct($parent, $tag_char = '', $tag = '', $text = '', $attributes = array()) {
        $this->parent = $parent;
        $this->tag_char = $tag_char;
        if ($tag[0] !== $this->tag_char) {
            $tag = $this->tag_char.$tag;
        }
        $this->tag = $tag;
        $this->text = $text;
        $this->attributes = $attributes;
        $this->self_close_str = $tag_char;
    }

    #php4 PHP4 class constructor compatibility
    #function EmbeddedNode($parent, $tag_char = '', $tag = '', $text = '', $attributes = array()) {return $this->__construct($parent, $tag_char, $tag, $text, $attributes);}
    #php4e

    protected function filter_element() {return false;}
    function toString($attributes = true, $recursive = true, $content_only = false) {
        $s = '<'.$this->tag;
        if ($attributes) {
            $s .= $this->toString_attributes();
        }
        $s .= $this->text.$this->self_close_str.'>';
        return $s;
    }
}