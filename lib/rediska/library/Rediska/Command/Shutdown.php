<?php

/**
 * Synchronously save the DB on disk, then shutdown the server
 * 
 * @return boolean
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version 0.4.2
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_Shutdown extends Rediska_Command_Abstract
{
    protected function _create($background = false) 
    {
        $command = "SHUTDOWN";

        foreach($this->_rediska->getConnections() as $connection) {
            $this->_addCommandByConnection($connection, $command);
        }
    }

    protected function _parseResponses($responses)
    {
        return true;
    }
}