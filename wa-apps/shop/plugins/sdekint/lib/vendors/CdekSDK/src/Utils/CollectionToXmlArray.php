<?php
/**
 * @author Serge Rodovnichenko, <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2018
 * @license MIT
 */

namespace SergeR\Webasyst\CdekSDK\Utils;

use SergeR\CakeUtility\Hash;
use Traversable;

/**
 * Trait CollectionToXmlArray
 * @package SergeR\Webasyst\CdekSDK\Utils
 * @property string $_xml_tag
 */
trait CollectionToXmlArray
{
    /**
     * @param array $options
     * @return array
     */
    public function toXmlArray(array $options = [])
    {
        if(property_exists($this, '_xml_tag')) {
            $options = array_merge(['tag' => $this->_xml_tag], $options);
        }

        $arr = array();

        if($this instanceof Traversable) {
            foreach ($this as $item) {
                $arr[] = $item->toXmlArray(['tag' => null]);
            }
        }

        $tag = Hash::get($options, 'tag');
        if ($tag !== null) {
            $arr = [$tag => $arr];
        }

        return $arr;
    }
}