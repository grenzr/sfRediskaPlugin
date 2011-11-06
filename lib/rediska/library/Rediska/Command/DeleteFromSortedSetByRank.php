<?php

/**
 * Remove all elements in the sorted set at key with rank between start  and end
 * 
 * @param string  $name  Key name
 * @param numeric $start Start position
 * @param numeric $end   End position
 * @return integer
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version 0.4.2
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_DeleteFromSortedSetByRank extends Rediska_Command_Abstract
{
    protected $_version = '1.3.4';

    protected function _create($name, $start, $end)
    {
        $connection = $this->_rediska->getConnectionByKeyName($name);

        $command = array('ZREMRANGEBYRANK', "{$this->_rediska->getOption('namespace')}$name", $start, $end);

        $this->_addCommandByConnection($connection, $command);
    }

    protected function _parseResponses($responses)
    {
        return $responses[0];
    }
}