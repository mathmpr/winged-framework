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
 * Node subclass for asp tags
 */
class AspEmbeddedNode extends EmbeddedNode {
	#php4 Compatibility with PHP4, this gets changed to a regular var in release tool
	#static $NODE_TYPE = self::NODE_ASP;
	#php4e
	#php5
	const NODE_TYPE = self::NODE_ASP;
	#php5e

	/**
	 * Class constructor
	 * @param DomNode $parent
	 * @param string $tag {@link $tag}
	 * @param string $text
	 * @param array $attributes array('attr' => 'val')
	 */
	function __construct($parent, $tag = '', $text = '', $attributes = array()) {
		return parent::__construct($parent, '%', $tag, $text, $attributes);
	}

	#php4 PHP4 class constructor compatibility
	#function AspEmbeddedNode($parent, $tag = '', $text = '', $attributes = array()) {return $this->__construct($parent, $tag, $text, $attributes);}
	#php4e
}

?>