<?php

/**
 * Rediska cache driver
 *
 * @package     sfRediskaPlugin
 * @subpackage  Cache
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision: 7074 $
 * @author      Ryan Grenz <info@ryangrenz.com>
 */
class Doctrine_Cache_Redis extends Doctrine_Cache_Driver
{
    /**
     * @var Rediska $_rediska     rediska object
     */
    protected $_rediska = null;

    /**
     * constructor
     *
     * @param array $options        associative array of cache driver options
     */
    public function __construct($options = array())
    {
        parent::__construct($options);
        if (isset($options['rediska'])) {
        	if ($options['rediska'] instanceof Rediska) {
        		$this->_rediska = $options['rediska'];
        	} else {
        		throw new Doctrine_Cache_Exception('The rediska instance supplied to Doctrine_Cache_Redis is not a Rediska object.');
        	}
        } else {
			$instance = $this->getOption('instance','default');
	        $this->_rediska = new sfRediskaCache(['instance' => 'app']);
        }
    }

    /**
     * Test if a cache record exists for the passed id
     *
     * @param string $id cache id
     * @return mixed  Returns either the cached data or false
     */
    protected function _doFetch($id, $testCacheValidity = true)
    {
        $id = $this->prepareId($id);

        return ($get = $this->_rediska->get($id)) ? $get : false;
    }

    /**
     * Test if a cache is available or not (for the given id)
     *
     * @param string $id cache id
     * @return mixed false (a cache is not available) or "last modified" timestamp (int) of the available cache record
     */
    protected function _doContains($id)
    {
        $id = $this->prepareId($id);

        return $this->_rediska->exists($id);
    }

    /**
     * Save a cache record directly. This method is implemented by the cache
     * drivers and used in Doctrine_Cache_Driver::save()
     *
     * @param string $id        cache id
     * @param string $data      data to cache
     * @param int $lifeTime     if != false, set a specific lifetime for this cache record (null => infinite lifeTime)
     * @return boolean true if no problem
     */
    protected function _doSave($id, $data, $lifeTime = false)
    {
        $id = $this->prepareId($id);

        return $this->_rediska->set($id, $data, $lifeTime);
    }

    /**
     * Remove a cache record directly. This method is implemented by the cache
     * drivers and used in Doctrine_Cache_Driver::delete()
     *
     * @param string $id cache id
     * @return boolean true if no problem
     */
    protected function _doDelete($id)
    {
        return $this->_rediska->remove($id);
    }

    /**
     * Fetch an array of all keys stored in cache
     *
     * @return array Returns the array of cache keys
     */
    protected function _getCacheKeys()
    {
        $cacheKeys = $this->_rediska->getMany(['*']);

        foreach ($cacheKeys as $index => $key) {
            $cacheKeys[$index] = preg_replace("/^{$this->getOption('prefix')}/", "", $key);
        }

        return $cacheKeys;
    }

    /**
     *
     * Remove forbidden characters of the key
     *
     * @param $id
     *
     * @return string|string[]|null
     */
    private function prepareId($id)
    {
        return preg_replace('#[\s+:]#', '_', $id);
    }
}
