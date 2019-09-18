<?php

/**
 * sfRediskaPlugin
 * Provides Symfony caching with Redis via Rediska (defined in app.yml and factories.yml)
 * and additionally allows a simple proxy interface to Rediska instances
 * http://rediska.geometria-lab.net
 *
 * @package    sfRediskaPlugin
 * @subpackage cache
 * @author     Ryan Grenz <info@ryangrenz.com>
 * @version    SVN: $Id$
 */
class sfRediskaCache extends sfCache
{
	/**
	 * Rediska instance
	 *
	 * @var Rediska
	 */
	protected $_rediska;

	/**
	 * Instantiate Rediska client and initialise it with all servers listed in the instance
	 *
	 * Available options :
	 * * instance: which instance of redis servers to work with (defined in app.yml, app_redis_default by default)
	 *
	 * @see sfCache
	 */
	public function initialize($options = array())
	{
		parent::initialize($options);
		$instance = $this->getOption('instance','default');
		$this->_rediska = sfRediska::getInstance($instance);
	}

	/**
	 * @see sfCache
	 */
	public function getBackend()
	{
		return $this->_rediska;
	}

	/**
	 * @see sfCache
	 */
	public function get($key, $default = null)
	{
		$value = $this->_rediska->get($this->getKey($key));
		return null === $value ? $default : $value;
	}

	/**
	 * @see sfCache
	 */
	public function has($key)
	{
		return $this->_rediska->exists($this->getKey($key));
	}

	/**
	 * @see sfCache
	 */
	public function set($key, $data, $lifetime = null)
	{
		$lifetime = null === $lifetime ? $this->getOption('lifetime') : $lifetime;

		if ($lifetime < 1) {
			$response = $this->remove($key);
		} else {
			$pipeline = $this->_rediska->pipeline();

			$origKey = $this->getKey($key);
			$metaKey = $this->getKey($key, 'lastmodified');
			$result = $pipeline->set(array($origKey => $data, $metaKey => time()))
								->expire($origKey, $lifetime)
								->expire($metaKey, $lifetime)
								->execute();

	  		$response = $result[0] && $result[1] && $result[2];
		}

		return $response;
	}

	/**
	 * @see sfCache
	 */
	public function remove($key)
	{
		return $this->_rediska->delete(array($this->getKey($key), $this->getKey($key, 'lastmodified')));
	}

	/**
	 * @see sfCache
	 */
	public function clean($mode = sfCache::ALL)
	{
		if (sfCache::ALL === $mode) {
			return $this->_rediska->flushDb();
		}

		return false;
	}

	public function getMany($keys)
	{
        $keys = array_map(array($this, 'getKey'), $keys);

        if (is_array($keys) && count($keys) == 1) {
            $keys = $keys[0];
        }

        $keysList = $this->_rediska->getKeysByPattern($keys);

        foreach ($keysList as $index => $key) {
            $keysList[$index] = preg_replace("/^{$this->getOption('prefix')}/", "", $key);
        }

        return $keysList;
	}

	/**
	 * @see sfCache
	 */
	public function getLastModified($key)
	{
		return $this->_rediska->get($this->getKey($key, 'lastmodified'));
	}

	/**
	 * Checks if a key is expired or not
	 */
	public function isExpired($key)
	{
		return time() > $this->getTimeout($key);
	}

	/**
	 * @see sfCache
	 */
	public function getTimeout($key)
	{
		$ttl = $this->_rediska->getLifetime($this->getKey($key));
		return ($ttl >= 0) ? time() + $ttl : 0;
	}

	/**
	 * We manually remove keys as the redis glob style * == sfCache ** style
	 *
	 * @see sfCache
     *
     * @return int
	 */
	public function removePattern($pattern)
	{
		$pattern = $this->getKey($pattern);
        $regexp = self::patternToRegexp($pattern);
        $keys = $this->_rediska->getKeysByPattern($pattern);
        $nbKeysDeleted = 0;

		foreach ($keys as $key) {
			if (preg_match($regexp, $key)) {
				if ($this->remove(substr($key, strlen($this->getOption('prefix'))))) {
				    $nbKeysDeleted++;
                }
			}
		}

		return $nbKeysDeleted;
	}

	/*
	 * Apply prefix (if set), and append separator+suffix (if set)
	 */
	protected function getKey($key, $suffix = null) 
	{
		$key = $this->getOption('prefix') . $key;
		return ($suffix !== null) ? $key . self::SEPARATOR.$suffix : $key;
	}
}