<?php

/**
 * Rediska command interface
 * 
 * @author Ivan Shumkov
 * @package Rediska
 * @version 0.4.2
 * @link http://rediska.geometria-lab.net
 * @licence http://www.opensource.org/licenses/bsd-license.php
 */
interface Rediska_Command_Interface
{
    public function __construct(Rediska $rediska, $name, $arguments);
    public function write();
    public function read();
    public function isAtomic();
}