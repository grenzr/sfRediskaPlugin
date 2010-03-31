<?php

require_once 'Zend/Application.php';

class Test_Zend_Application_Resource extends PHPUnit_Framework_TestCase
{
    public function testBootstrap()
    {
        $application = new Zend_Application('tests', REDISKA_TESTS_PATH . '/Test/Zend/Application/application.ini');
        $rediska = $application->bootstrap()
                               ->getBootstrap()
                               ->getResource('rediska');
        $this->assertType('Rediska', $rediska);
        $this->assertEquals('Rediska_Test_', $rediska->getOption('namespace'));
    }
}