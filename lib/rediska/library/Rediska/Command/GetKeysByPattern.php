<?php

/**
 * Returns all the keys matching the glob-style pattern
 * Glob style patterns examples:
 *   h?llo will match hello hallo hhllo
 *   h*llo will match hllo heeeello
 *   h[ae]llo will match hello and hallo, but not hillo
 * 
 * @throws Rediska_Command_Exception
 * @param string $pattern
 * @return array
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version 0.4.2
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
class Rediska_Command_GetKeysByPattern extends Rediska_Command_Abstract
{
    protected function _create($pattern) 
    {
        if ($pattern == '') {
            throw new Rediska_Command_Exception("Pattern can't be empty");
        }

        $command = "KEYS {$this->_rediska->getOption('namespace')}$pattern";
        foreach($this->_rediska->getConnections() as $connection) {
            $this->_addCommandByConnection($connection, $command);
        }
    }

    protected function _parseResponses($responses)
    {
        $keys = array();
        foreach($responses as $response) {
            if (!empty($response)) {
                $keys = array_merge($keys, is_array($response) ? $response : explode(' ', $response));
            }
        }

        $keys = array_unique($keys);
        foreach($keys as &$key) {
            $key = substr($key, strlen($this->_rediska->getOption('namespace')));
        }

        return $keys;
    }
}