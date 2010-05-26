<?php

/**
 * Test if a key exists
 * 
 * @param string $name Key name
 * @return boolean
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version 0.4.2
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_Exists extends Rediska_Command_Abstract
{
    protected function _create($name) 
    {
        $connection = $this->_rediska->getConnectionByKeyName($name);
        
        $command = "EXISTS {$this->_rediska->getOption('namespace')}$name";

        $this->_addCommandByConnection($connection, $command);
    }

    protected function _parseResponses($responses)
    {
        return (boolean)$responses[0];
    }
}