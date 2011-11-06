<?php

require_once 'Rediska/Command/Response/ValueAndScore.php';

class Rediska_Command_UnionSortedSetsTest extends Rediska_TestCase
{
    public function testUnionReturnCount()
    {
        $keys = $this->_createThreeSets();

        $setByPHP = $this->_compareSets($keys);

        $reply = $this->rediska->unionSortedSets($keys, 'test');

        $this->assertEquals(count($setByPHP), $reply);
    }

    public function testUnionReturnCountWithManyConnection()
    {
        $this->_addSecondServerOrSkipTest();

        $this->testUnionReturnCount();
    }

    public function testUnion()
    {
        $keys = $this->_createThreeSets();

        $this->rediska->unionSortedSets($keys, 'test');

        $set = $this->rediska->getSortedSet('test', true);
        $setByPHP = $this->_compareSets($keys);

        $this->assertEquals($setByPHP, $set);
    }

    public function testUnionWithManyConnection()
    {
        $this->_addSecondServerOrSkipTest();
        
        $this->testUnion();
    }

    public function testUnionWithWeights()
    {
        $this->_createThreeSets();

        $keys = array('set1' => 2, 'set2' => 4, 'set3' => 1);

        $this->rediska->unionSortedSets($keys, 'test');

        $set = $this->rediska->getSortedSet('test', true);
        $setByPHP = $this->_compareSets($keys);

        $this->assertEquals($setByPHP, $set);
    }

    public function testUnionWithWeightsWithManyConnections()
    {
        $this->_addSecondServerOrSkipTest();

        $this->testUnionWithWeights();
    }
    
    public function testUnionWithAggregationMax()
    {
        $keys = $this->_createThreeSets();

        $this->rediska->unionSortedSets($keys, 'test', 'max');

        $set = $this->rediska->getSortedSet('test', true);
        $setByPHP = $this->_compareSets($keys, 'max');

        $this->assertEquals($setByPHP, $set);
    }

    public function testUnionWithAggregationMaxWithManyConnections()
    {
        $this->_addSecondServerOrSkipTest();

        $this->testUnionWithAggregationMax();
    }
    
    public function testUnionWithAggregationMin()
    {   
        $keys = $this->_createThreeSets();

        $this->rediska->unionSortedSets($keys, 'test', 'min');

        $set = $this->rediska->getSortedSet('test', true);
        $setByPHP = $this->_compareSets($keys, 'min');

        $this->assertEquals($setByPHP, $set);
    }

    public function testUnionWithAggregationMinWithManyConnections()
    {
        $this->_addSecondServerOrSkipTest();

        $this->testUnionWithAggregationMin();
    }

    protected function _compareSets($names, $aggregation = 'sum')
    {
        // With weights?
        $weights = array();
        foreach($names as $nameOrIndex => $weightOrName) {
            if (is_string($nameOrIndex)) {
                $weights = $names;
                $names = array_keys($names);
                break;
            }
        }

        if (empty($weights)) {
            $weights = array_fill_keys($names, 1);
        }

        $values = array();
        $valuesWithScores = array();
        foreach ($names as $name) {
            $set = $this->rediska->getSortedSet($name, true);
            
            $values[$name] = array();
            foreach ($set as $valueOrScore) {
                $value = $this->rediska->serialize($valueOrScore->value);
                $score = $valueOrScore->score;
                
                $values[$name][] = $value;
                if (!isset($valuesWithScores[$value])) {
                    $valuesWithScores[$value] = array();
                }
                $valuesWithScores[$value][] = $score * $weights[$name];
            }
        }

        $comparedValues = array();
        foreach($values as $name => $value) {
            $comparedValues = array_merge($comparedValues, $value);
        }
        $comparedValues = array_unique($comparedValues);

        $pipeline = $this->rediska->pipeline();
        foreach($comparedValues as $value) {
            $scores = $valuesWithScores[$value];
            switch ($aggregation) {
                case 'sum':
                    $score = array_sum($scores);
                    break;
                case 'min':
                    $score = min($scores);
                    break;
                case 'max':
                    $score = max($scores);
                    break;
                default:
                    throw new Exception('Unknown aggregation method ' . $aggregation);
            }
            
            $value = $this->rediska->unserialize($value);
            
            $pipeline->addToSortedSet('test2', $value, $score);
        }
        
        $pipeline->execute();

        return $this->rediska->getSortedSet('test2', true);
    }

    protected $_sets = array(
        'set1' => array(1 => 3, 3 => 1, 6 => 431, 4 => 1, 2 => 53, 'fds' => 2),
        'set2' => array(1 => 123, 'aaa' => 143, 534 => 132),
        'set3' => array('asdas' => 12, 1 => 1, 'aaa' => 13, 4 => 1.1)
    );

    protected function _createThreeSets()
    {
        foreach($this->_sets as $key => $values) {
            foreach($values as $value => $weight) {
                $this->rediska->addToSortedSet($key, $value, $weight);
            }
        }

        return array_keys($this->_sets);
    }
}