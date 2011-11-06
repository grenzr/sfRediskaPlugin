<?php

/**
 * Append value to the head of List
 * 
 * @param string $name Key name
 * @param mixin  $value Value
 * @return boolean
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version 0.4.2
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_PrependToList extends Rediska_Command_Abstract
{
    protected function _create($name, $value) 
    {
        $connection = $this->_rediska->getConnectionByKeyName($name);

        $value = $this->_rediska->serialize($value);

        $command = "LPUSH {$this->_rediska->getOption('namespace')}$name " . strlen($value) . Rediska::EOL . $value;

        $this->_addCommandByConnection($connection, $command);
    }

    protected function _parseResponses($responses)
    {
        return (boolean)$responses[0];
    }
}