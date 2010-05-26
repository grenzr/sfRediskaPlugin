<?php

/**
 * Rewrite the Append Only File in background when it gets too big
 * 
 * @return boolean
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version 0.4.2
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_RewriteAppendOnlyFile extends Rediska_Command_Abstract
{
    protected function _create() 
    {
        $command = "BGREWRITEAOF";

        foreach($this->_rediska->getConnections() as $connection) {
            $this->_addCommandByConnection($connection, $command);
        }
    }

    protected function _parseResponses($responses)
    {
        return true;
    }
}