<?php

class Rediska_PipelineTest extends Rediska_TestCase
{
	public function testPiplineInstance()
	{
		$instance = $this->rediska->pipeline();
		$this->assertType('Rediska_Pipeline', $instance);

		$instance = $this->rediska->pipeline()->set('a', 'b');
        $this->assertType('Rediska_Pipeline', $instance);
	}

    public function testPipelineIncapsulation()
    {
    	$this->rediska->pipeline()->set('a', 'b');
    	$reply = $this->rediska->get('a');
    	$this->assertNull($reply);
    }

    /**
     * @expectedException Rediska_Exception
     */
    public function testPipelineNothingToExecute()
    {
    	$this->rediska->pipeline()->execute();
    }

    public function testPipeline()
    {
    	$reply = $this->rediska->pipeline()
		    	               ->set('a', 123)
		    	               ->get('a')
		    	               ->addToSet('b', 123)
		    	               ->getSet('b')
		    	               ->execute();

		$this->assertEquals(array(true, 123, true, array(123)), $reply);
    }

    public function testPipelineWithMultipleConnections()
    {
    	$this->_addSecondServerOrSkipTest();
    	
    	$this->testPipeline();
    }

    public function testPipelineWithSpecifiedConnection()
    {
    	$this->_addSecondServerOrSkipTest();

    	$this->rediska->on(REDISKA_SECOND_HOST . ':' . REDISKA_SECOND_PORT)->pipeline()
    	                                    ->set(1, 1)
    	                                    ->set(2, 2)
    	                                    ->on(REDISKA_HOST . ':' . REDISKA_PORT)->set(3, 3)
    	                                    ->set(4, 4)
    	                                    ->execute();

        $this->assertEquals(1, $this->rediska->on(REDISKA_SECOND_HOST . ':' . REDISKA_SECOND_PORT)->get(1));
        $this->assertEquals(2, $this->rediska->on(REDISKA_SECOND_HOST . ':' . REDISKA_SECOND_PORT)->get(2));
        $this->assertEquals(3, $this->rediska->on(REDISKA_HOST . ':' . REDISKA_PORT)->get(3));
        $this->assertEquals(4, $this->rediska->on(REDISKA_SECOND_HOST . ':' . REDISKA_SECOND_PORT)->get(4));
    }
}