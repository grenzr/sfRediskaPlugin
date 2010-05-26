<?php

/**
 * @see Rediska_Command_CompareSets
 */
require_once 'Rediska/Command/CompareSets.php';

/**
 * Return the union between the Sets stored at key1, key2, ..., keyN
 * 
 * @param array       $names     Array of key names
 * @param string|null $storeName Store union to set with key name
 * @return array|boolean
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version 0.4.2
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_UnionSets extends Rediska_Command_CompareSets
{
	protected $_command = 'SUNION';
    protected $_storeCommand = 'SUNIONSTORE';

    protected function _compareSets($sets)
    {
        $comparedSet = array();
        foreach($sets as $setValues) {
            $comparedSet = array_merge($comparedSet, $setValues);
        }
        return array_unique($comparedSet);
    }
}