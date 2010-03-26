<?php

/**
 * sfRediskaPlugin
 * Provides Symfony caching with Redis via Rediska (defined in factories.yml)
 * and additionally allows a simple proxy interface to Rediska instances
 * http://rediska.geometria-lab.net
 *
 * @package    sfRediskaPlugin
 * @subpackage cache
 * @author     Ryan Grenz <info@ryangrenz.com>
 * @version    SVN: $Id$
 */
class sfRediska extends sfCache
{
  protected $redis = null;
  protected static $instances = array();

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
    // To resolve require paths in Rediska source:
    set_include_path(get_include_path() . PATH_SEPARATOR . sfConfig::get('sf_plugins_dir').'/sfRediskaPlugin/lib/rediska/library');
    $this->redis = new Rediska(self::getConfig($instance,true));
  }

  public static function getInstance($instance='default') {
  	if (isset(self::$instances[$instance])) return self::$instances[$instance]->getBackend();
  	self::$instances[$instance] = new sfRediska(array('instance' => $instance));
  	return self::$instances[$instance]->getBackend();
  }
  
  public static function getConfig($instance='default',$error=false) {
  	$path = "app_redis_$instance";
  	$config = sfConfig::get($path);
  	if (!$config && $error) throw new sfInitializationException("No Redis config located at '$path' in app.yml");
  	return $config;
  }  
  
  /**
   * @see sfCache
   */
  public function getBackend()
  {
    return $this->redis;
  }

  public function __call($name, $args) {
  	return call_user_func_array(array($this->redis, $name),$args);
  }
  
 /**
  * @see sfCache
  */
  public function get($key, $default = null)
  {
    $value = $this->redis->get($key);

    return null === $value ? $default : $value;
  }

  /**
   * @see sfCache
   */
  public function has($key)
  {
    return $this->redis->exists($key);
  }

  /**
   * @see sfCache
   */
  public function set($key, $data, $lifetime = null)
  {
    $lifetime = null === $lifetime ? $this->getOption('lifetime') : $lifetime;

    if ($lifetime < 1)
    {
      $response = $this->remove($key);
    }
    else
    {
      $this->setMetadata($key, $lifetime);
      $response = $this->redis->pipeline()
      	->set($key, $data, false)
      	->expire($key, $lifetime)
      	->execute();
    }

    return $response;
  }

  /**
   * @see sfCache
   */
  public function remove($key)
  {
    $this->deleteMetadata($key);
    return $this->redis->delete($key);
  }

  /**
   * @see sfCache
   */
  public function clean($mode = sfCache::ALL)
  {
    if (sfCache::ALL === $mode)
    {
      $this->removePattern('*');
    }
  }

  public function getMany($keys)
  {
    return $this->redis->get($keys);
  }

  /**
   * @see sfCache
   */
  public function getLastModified($key)
  {
    return $this->redis->get('lastmodified'.self::SEPARATOR.$key);
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
    return (time() + $this->redis->getLifetime($key));
  }

  /**
   * We manually remove keys as the redis glob style * == sfCache ** style
   *
   * @author oncletom
   * @see sfCache
   */
  public function removePattern($pattern)
  {
    $keys = $this->redis->getKeysByPattern($pattern);

    $regexp = self::patternToRegexp($pattern);
    foreach ($keys as $key)
    {
      if (preg_match($regexp, $key))
      {
        $this->remove($key);
      }
    }
  }

  /**
   * Stores metada for a key
   */
  protected function setMetadata($key, $lifetime)
  {
    $response = $this->redis->pipeline()
    	->set('lastmodified'.self::SEPARATOR.$key, time())
    	->expire('lastmodified'.self::SEPARATOR.$key, $lifetime)
    	->execute();

    return $response;
  }

  /**
   * Deletes every metadata related to a key
   */
  protected function deleteMetadata($key)
  {
    return !!$this->redis->delete('lastmodified'.self::SEPARATOR.$key);
  }
}