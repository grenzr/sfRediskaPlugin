<?php

/**
 * Get key lifetime
 * 
 * @param string $name
 * @return integer
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version 0.4.2
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_GetLifetime extends Rediska_Command_Abstract
{
    protected function _create($name)
    {
        $connection = $this->_rediska->getConnectionByKeyName($name);

        $command = "TTL {$this->_rediska->getOption('namespace')}$name";

        $this->_addCommandByConnection($connection, $command);
    }

    protected function _parseResponses($responses)
    {
        $reply = $responses[0];

        if ($reply == -1) {
            $reply = null;
        }

        return $reply;
    }
}