<?php

/**
 * @see Rediska_Key_Abstract
 */
require_once(dirname(__FILE__).'/Abstract.php');

/**
 * Rediska Set key
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version 0.4.2
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Key_Set extends Rediska_Key_Abstract implements IteratorAggregate, ArrayAccess, Countable
{
    /**
     * Add the specified member to the Set
     * 
     * @param mixin $value Value
     * @return boolean
     */
    public function add($value)
    {
        $result = $this->_getRediskaOn()->addToSet($this->_name, $value);

        if ($result && !is_null($this->_expire)) {
            $this->expire($this->_expire, $this->_isExpireTimestamp);
        }

        return $result;
    }
    
    /**
     * Remove the specified member from the Set
     * 
     * @param mixin $value Value
     * @return boolean
     */
    public function remove($value)
    {
        $result = $this->_getRediskaOn()->deleteFromSet($this->_name, $value);

        if ($result && !is_null($this->_expire)) {
        	$this->expire($this->_expire, $this->_isExpireTimestamp);
        }

        return $result;
    }
    
    /**
     * Move the specified member from one Set to another atomically
     * 
     * @param string|Rediska_Key_Set $set   Set key name or object
     * @param mixin                  $value Value
     * @return boolean
     */
    public function move($set, $value)
    {
    	if ($set instanceof Rediska_Key_Set) {
    		$set = $set->getName();
    	}

    	return $this->_getRediskaOn()->moveToSet($this->_name, $set, $value);
    }
    
    /**
     * Get Set length
     * 
     * @return integer
     */
    public function count()
    {
        return $this->_getRediskaOn()->getSetLength($this->_name);
    }
    
    /**
     * Test if the specified value is a member of the Set
     * 
     * @prarm mixin  $value Value
     * @return boolean
     */
    public function exists($value)
    {
        return $this->_getRediskaOn()->existsInSet($this->_name, $value);
    }
    
    /**
     * Return the intersection between the Sets
     * 
     * @param string|array $setOrSets    Set key name or object, or array of its
     * @param string|null  $storeKeyName Store intersection to set with key name
     * @return array|boolean
     */
    public function intersect($setOrSets, $storeKeyName = null)
    {
    	$sets = $this->_prepareSetsForCompare($setOrSets);
    	
    	return $this->_getRediskaOn()->intersectSets($sets, $storeKeyName);
    }
    
    /**
     * Return the union between the Sets
     * 
     * @param string|array $setOrSets    Set key name or object, or array of its
     * @param string|null  $storeKeyName Store union to set with key name
     * @return array|boolean
     */
    public function union($setOrSets, $storeKeyName = null)
    {
        $sets = $this->_prepareSetsForCompare($setOrSets);

        return $this->_getRediskaOn()->unionSets($sets, $storeKeyName);
    }
    
    /**
     * Return the difference between the Sets
     * 
     * @param string|array $setOrSets    Set key name or object, or array of its
     * @param string|null  $storeKeyName Store union to set with key name
     * @return array|boolean
     */
    public function diff($setOrSets, $storeKeyName = null)
    {
        $sets = $this->_prepareSetsForCompare($setOrSets);

        return $this->_getRediskaOn()->diffSets($sets, $storeKeyName);
    }

    /**
     * Get sorted the elements
     * 
     * @param string|array  $value Options or SORT query string (http://code.google.com/p/redis/wiki/SortCommand).
     *                             Important notes for SORT query string:
     *                                 1. If you set Rediska namespace option don't forget add it to key names.
     *                                 2. If you use more then one connection to Redis servers, it will choose by key name,
     *                                    and key by you pattern's may not present on it.
     * @return array
     */
    public function sort($options = array())
    {
        return $this->_getRediskaOn()->sort($this->_name, $options);
    }

    /**
     * Get Set values
     * 
     * @param string $sort Deprecated
     * @return array
     */
    public function toArray($sort = null)
    {
        return $this->_getRediskaOn()->getSet($this->_name, $sort);
    }
    
    /**
     * Add array to Set
     * 
     * @param array $array
     */
    public function fromArray(array $array)
    {
        // TODO: Use pipelines
        $pipeline = $this->_getRediskaOn()->pipeline();
        foreach($array as $item) {
            $pipeline->addToSet($this->_name, $item);
        }

        if (!is_null($this->_expire)) {
        	$pipeline->expire($this->_name, $this->_expire, $this->_isExpireTimestamp);
        }

        $pipeline->execute();

        return true;
    }

    /**
     * Implement intrefaces
     */

    public function getIterator()
    {
        return new ArrayObject($this->toArray());
    }

    public function offsetSet($offset, $value)
    {
        if (!is_null($offset)) {
            throw new Rediska_Key_Exception('Offset is not allowed in sets');
        }

        $this->add($value);

        return $value;
    }

    public function offsetExists($value)
    {
        throw new Rediska_Key_Exception('Offset is not allowed in sets');
    }

    public function offsetUnset($value)
    {
        throw new Rediska_Key_Exception('Offset is not allowed in sets');
    }

    public function offsetGet($value)
    {
        throw new Rediska_Key_Exception('Offset is not allowed in sets');
    }
    
    protected function _prepareSetsForCompare($setOrSets)
    {
        if (!is_array($setOrSets)) {
            $sets = array($setOrSets);
        } else {
            $sets = $setOrSets;
        }

        foreach($sets as &$set) {
            if ($set instanceof Rediska_Key_Set) {
                $set = $set->getName();
            }
        }

        if (!in_array($this->_name, $sets)) {
            array_unshift($sets, $this->_name);
        }

        return $sets;
    }
}