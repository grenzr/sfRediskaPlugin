<?php

require_once 'Rediska/Key/List.php';

class Rediska_Key_ListTest extends Rediska_TestCase
{
	/**
     * @var Rediska_Key_List
     */
    private $list;

    protected function setUp()
    {
        parent::setUp();
        $this->list = new Rediska_Key_List('test');
    }
    
    public function testAppend()
    {
    	$reply = $this->list->append(123);
    	$this->assertTrue($reply);
    	$reply = $this->list->append(456);
    	$this->assertTrue($reply);

    	$values = $this->rediska->getFromList('test', 1);
    	$this->assertEquals(456, $values);
    }
    
    public function testPrepend()
    {
    	$reply = $this->list->prepend(123);
    	$this->assertTrue($reply);
        $reply = $this->list->prepend(456);
        $this->assertTrue($reply);

        $values = $this->rediska->getFromList('test', 1);
        $this->assertEquals(123, $values);
    }
    
    public function testCount()
    {
    	$this->rediska->appendToList('test', 123);
    	$this->rediska->appendToList('test', 456);

        $this->assertEquals(2, $this->list->count());
        $this->assertEquals(2, count($this->list));
    }
    
    public function testToArray()
    {
    	$this->rediska->appendToList('test', 123);
        $this->rediska->appendToList('test', 456);

        $values = $this->list->toArray();
        $this->assertEquals(array(123, 456), $values);
    }
    
    public function testFromArray()
    {
    	$reply = $this->list->fromArray(array(123, 456));
    	$this->assertTrue($reply);

    	$values = $this->rediska->getList('test');
        $this->assertEquals(array(123, 456), $values);
    }

    public function testTruncate()
    {
    	$this->rediska->appendToList('test', 123);
        $this->rediska->appendToList('test', 456);

        $reply = $this->list->truncate(1);
        $this->assertTrue($reply);

        $values = $this->rediska->getList('test');
        $this->assertEquals(array(123), $values);
    }
    
    public function testGet()
    {
    	$this->rediska->appendToList('test', 123);
    	
    	$value = $this->list->get(0);
    	$this->assertEquals(123, $value);
    }

    public function testSet()
    {
    	$this->rediska->appendToList('test', 123);

        $reply = $this->list->set(0, 456);
        $this->assertTrue($reply);

        $value = $this->rediska->getFromList('test', 0);
        $this->assertEquals(456, $value);
    }

    public function testRemove()
    {
    	$this->rediska->appendToList('test', 123);
    	$this->rediska->appendToList('test', 456);

    	$reply = $this->list->remove(123);
    	$this->assertEquals(1, $reply);

    	$value = $this->rediska->getFromList('test', 0);
        $this->assertEquals(456, $value);
    }

    public function testShift()
    {
    	$this->rediska->appendToList('test', 123);
        $this->rediska->appendToList('test', 456);
        
        $value = $this->list->shift();
        $this->assertEquals(123, $value);
        
        $value = $this->rediska->getFromList('test', 0);
        $this->assertEquals(456, $value);
    }

    public function testPop()
    {
        $this->rediska->appendToList('test', 123);
        $this->rediska->appendToList('test', 456);
        
        $value = $this->list->pop();
        $this->assertEquals(456, $value);
        
        $value = $this->rediska->getFromList('test', 1);
        $this->assertNull($value);
    }

    public function testIteration()
    {
    	$values = array(123, 456, 789);
    	
    	foreach($values as $value) {
    		$this->rediska->appendToList('test', $value);
    	}

    	$count = 0;
    	foreach($this->list as $value) {
    		$this->assertTrue(in_array($value, $values));
    		$count++;
    	}
    	$this->assertEquals(3, $count);
    }

    public function testOffsetSet()
    {
    	$this->list[] = 123;
    	$this->list[] = 456;

    	$value = $this->rediska->getFromList('test', 0);
    	$this->assertEquals(123, $value);

    	$value = $this->rediska->getFromList('test', 1);
        $this->assertEquals(456, $value);

        $this->list[1] = 789;

        $value = $this->rediska->getFromList('test', 1);
        $this->assertEquals(789, $value);
    }

    public function testOffsetExists()
    {
    	$reply = isset($this->list[0]);
    	$this->assertFalse($reply);

    	$this->rediska->appendToList('test', 123);

    	$reply = isset($this->list[0]);
        $this->assertTrue($reply);
    }

    public function testOffsetGet()
    {
    	$this->rediska->appendToList('test', 123);

        $value = $this->list[0];
        $this->assertEquals(123, $value);
    }
}