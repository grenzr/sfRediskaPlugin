<?php

/**
 * @see Rediska_Command_CompareSets
 */
require_once 'Rediska/Command/CompareSortedSets.php';

/**
 * Store to key union between the sorted sets
 * 
 * @param array  $names       Array of key names or associative array with weights
 * @param string $storeName   Result sorted set key name
 * @param string $aggregation Aggregation method: SUM (for default), MIN, MAX.
 * @return integer
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version 0.4.2
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_UnionSortedSets extends Rediska_Command_CompareSortedSets
{
	protected $_command = 'ZUNION';

    protected function _compareSets($sets)
    {
        $resultSet = array();
        foreach($sets as $name => $values) {
            $resultSet = array_merge($resultSet, $values);
        }
        return array_unique($resultSet);
    }
}