<?php

/**
 * Get length of Sorted Set
 * 
 * @param string $name Key name
 * @return integer
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version 0.4.2
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_GetSortedSetLength extends Rediska_Command_Abstract
{
    protected $_version = '1.1';
    
    protected function _create($name)
    {
        $connection = $this->_rediska->getConnectionByKeyName($name);

        $command = array('ZCARD', "{$this->_rediska->getOption('namespace')}$name");

        $this->_addCommandByConnection($connection, $command);
    }

    protected function _parseResponses($responses)
    {
        return $responses[0];
    }
}