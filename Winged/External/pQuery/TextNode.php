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
 * Node subclass for text
 */
class TextNode extends DomNode {
    #php4 Compatibility with PHP4, this gets changed to a regular var in release tool
    #static $NODE_TYPE = self::NODE_TEXT;
    #php4e
    #php5
    const NODE_TYPE = self::NODE_TEXT;
    #php5e
    var $tag = '~text~';

    /**
     * @var string
     */
    var $text = '';

    /**
     * Class constructor
     * @param DomNode $parent
     * @param string $text
     */
    function __construct($parent, $text = '') {
        $this->parent = $parent;
        $this->text = $text;
    }

    #php4 PHP4 class constructor compatibility
    #function TextNode($parent, $text = '') {return $this->__construct($parent, $text);}
    #php4e

    function isText() {return true;}
    function isTextOrComment() {return true;}
    protected function filter_element() {return false;}
    protected function filter_text() {return true;}
    function toString_attributes() {return '';}
    function toString_content($attributes = true, $recursive = true, $content_only = false) {return $this->text;}
    function toString($attributes = true, $recursive = true, $content_only = false) {return $this->text;}

    /**
     * {@inheritdoc}
     */
    public function text($value = null) {
        if ($value !== null) {
            $this->text = $value;
            return $this;
        }
        return $this->text;
    }

    /**
     * {@inheritdoc}
     */
    public function html($value = null) {
        if ($value !== null) {
            $this->text = $value;
            return $this;
        }
        return $this->text;
    }
}