<?php

/**
 * Set a time to live in seconds or timestamp on a key
 * 
 * @throws Rediska_Command_Exception
 * @param string  $name               Key name
 * @param integer $secondsOrTimestamp Time in seconds or timestamp
 * @param boolean $isTimestamp        Time is timestamp. For default is false.
 * @return boolean 
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version 0.4.2
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_Expire extends Rediska_Command_Abstract
{
    protected function _create($name, $secondsOrTimestamp, $isTimestamp = false)
    {
        if (!is_integer($secondsOrTimestamp) || $secondsOrTimestamp <= 0) {
            throw new Rediska_Command_Exception(($isTimestamp ? 'Time' : 'Seconds') . ' must be positive integer');
        }

        $connection = $this->_rediska->getConnectionByKeyName($name);

        if ($isTimestamp) {
            $this->_checkVersion('1.1');
            $command = 'EXPIREAT';
        } else {
            $command = 'EXPIRE';
        }

        $command = "$command {$this->_rediska->getOption('namespace')}$name $secondsOrTimestamp";

        $this->_addCommandByConnection($connection, $command);
    }

    protected function _parseResponse($responses)
    {
        return (boolean)$responses[0];
    }
}