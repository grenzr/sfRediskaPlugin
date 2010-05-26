<?php

/**
 * @see Rediska_Key_Abstract
 */
require_once 'Rediska/Key/Abstract.php';

/**
 * Rediska Sorted set key
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version 0.4.2
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Key_SortedSet extends Rediska_Key_Abstract implements IteratorAggregate, ArrayAccess, Countable
{
    /**
     * Add the specified member to the Sorted set
     * 
     * @param mixin $value Value
     * @param numeric $score Score
     * @return boolean
     */
    public function add($value, $score)
    {
        $result = $this->_getRediskaOn()->addToSortedSet($this->_name, $value, $score);

        if ($result && !is_null($this->_expire)) {
            $this->expire($this->_expire, $this->_isExpireTimestamp);
        }

        return $result;
    }
    
    /**
     * Remove the specified member from the Sorted set
     * 
     * @param mixin $value Value
     * @return boolean
     */
    public function remove($value)
    {
        $result = $this->_getRediskaOn()->deleteFromSortedSet($this->_name, $value);

        if ($result && !is_null($this->_expire)) {
            $this->expire($this->_expire, $this->_isExpireTimestamp);
        }

        return $result;
    }
    
    /**
     * Get Sorted set length
     * 
     * @return integer
     */
    public function count()
    {
        return $this->_getRediskaOn()->getSortedSetLength($this->_name);
    }

    /**
     * Get Sorted set by score
     * 
     * @param number  $min        Min score
     * @param number  $max        Max score
     * @param boolean $withScores Get with scores
     * @param integer $limit      Limit
     * @param integer $offset     Offset
     * @return array
     */
    public function getByScore($min, $max, $withScores = false, $limit = null, $offset = null)
    {
        return $this->_getRediskaOn()->getFromSortedSetByScore($this->_name, $min, $max, $withScores, $limit, $offset);
    }
    
    /**
     * Remove members from sorted set by score
     * 
     * @param $min Min score
     * @param $max Max score
     * @return integer
     */
    public function removeByScore($min, $max)
    {
        return $this->_getRediskaOn()->DeleteFromSortedSetByScore($this->_name, $min, $max);
    }

    /**
     * Get member score from Sorted Set
     * 
     * @param mixin $value
     * @return numeric
     */
    public function getScore($value)
    {
    	return $this->_getRediskaOn()->getScoreFromSortedSet($this->_name, $value);
    }
    
    /**
     * Increment score of element
     * 
     * @param $value
     * @return integer
     */
    public function incrementScore($value, $score)
    {
        return $this->_getRediskaOn()->incrementScoreInSortedSet($this->_name, $value, $score);
    }

    /**
     * Remove all elements in the sorted set at key with rank between start and end
     * 
     * @param numeric $start Start position
     * @param numeric $end   End position
     * @return integer
     */ 
    public function removeByRank($start, $end)
    {
        return $this->_getRediskaOn()->deleteFromSortedSetByRank($this->_name, $start, $end);
    }

    /**
     * Get rank of member
     * 
     * @param integer $value  Member value
     * @param boolean $revert Revert elements (not used in sorting)
     * @return integer
     */ 
    public function getRank($value, $revert = false)
    {
        return $this->_getRediskaOn()->getRankFromSortedSet($this->_name, $value, $revert);
    }
    
    /**
     * Store to key union between the sorted sets
     * 
     * @param string|array  $setOrSets    Sorted set key name or object, or array of its
     * @param string        $storeKeyName Result sorted set key name
     * @param string        $aggregation  Aggregation method: SUM (for default), MIN, MAX.
     * @return integer
     */
    public function union($setOrSets, $storeKeyName, $aggregation = 'sum')
    {
        $sets = $this->_prepareSetsForComapre($setOrSets);

        return $this->_getRediskaOn()->unionSortedSets($sets, $storeKeyName, $aggregation);
    }
    
    /**
     * Store to key intersection between sorted sets
     * 
     * @param string|array  $setOrSets    Sorted set key name or object, or array of its
     * @param string        $storeKeyName Result sorted set key name
     * @param string        $aggregation  Aggregation method: SUM (for default), MIN, MAX.
     * @return integer
     */
    public function intersect($setOrSets, $storeKeyName, $aggregation = 'sum')
    {
        $sets = $this->_prepareSetsForComapre($setOrSets);

        return $this->_getRediskaOn()->intersectSortedSets($sets, $storeKeyName, $aggregation);
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
     * Get Sorted set values
     * 
     * @param integer $withScores  Return values with scores
     * @param integer $start       Start index
     * @param integer $end         End index
     * @param boolean $revert      Revert elements (not used in sorting)
     * @return array
     */
    public function toArray($withScores = false, $start = 0, $end = -1, $revert = false)
    {
        return $this->_getRediskaOn()->getSortedSet($this->_name, $withScores, $start, $end, $revert);
    }

    /**
     * Add array to Sorted set
     * 
     * @param array $array
     */
    public function fromArray(array $array)
    {
        $pipeline = $this->_getRediskaOn()->pipeline();

        foreach($array as $score => $value) {
            $pipeline->addToSortedSet($this->_name, $value, $score);
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

    public function offsetSet($score, $value)
    {
        if (is_null($score)) {
            throw new Rediska_Key_Exception('Score must be present');
        }

        $this->add($value, $score);

        return $value;
    }

    public function offsetExists($score)
    {
        return (boolean)$this->offsetGet($score);
    }

    public function offsetUnset($score)
    {
        $value = $this->offsetGet($score);
        if (!is_null($value)) {
            $this->remove($value);

            return true;
        } else {
            return false;
        }
    }

    public function offsetGet($score)
    {
        $values = $this->getByScore($score, $score);

        if (!empty($values)) {
            return $values[0];
        }
    }

    protected function _prepareSetsForComapre($setOrSets)
    {
        if (!is_array($setOrSets)) {
            $sets = array($setOrSets);
        } else {
            $sets = $setOrSets;
        }
        
        // With weights?
        $withWeights = false;
        foreach($sets as $nameOrIndex => $weightOrName) {
            if (is_string($nameOrIndex)) {
                $withWeights = true;
                break;
            }
        }

        if ($withWeights) {
            if (!array_key_exists($this->getName(), $sets)) {
                $sets[$this->getName()] = 1;
            }
        } else {
            foreach($sets as &$set) {
                if ($set instanceof Rediska_Key_Set) {
                    $set = $set->getName();
                }
            }

            if (!in_array($this->getName(), $sets)) {
                $sets[] = $this->getName();
            }
        }

        return $sets;
    }
}