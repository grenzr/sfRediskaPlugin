<?php
/**
* sfRediska client class
*
* @package    sfRediskaPlugin
* @subpackage cache
* @author     Ryan Grenz <info@ryangrenz.com>
* @version    SVN: $Id$
*/
class sfRediska {

  private static $config = null;
  protected static $instances = array();
	
  public static function getInstance($instance='default') 
  {
  	if (!isset(self::$instances[$instance])) {
  		// Needed to make Rediska require paths resolve correctly:
	  	set_include_path(get_include_path() . PATH_SEPARATOR . sfConfig::get('sf_plugins_dir').'/sfRediskaPlugin/lib/rediska/library');  
	  	self::$instances[$instance] = new Rediska(self::getConfig($instance));
  	}
  	
  	return self::$instances[$instance];
  }
  
  public static function getConfig($instance='default') 
  {
  	$path = "app_redis_$instance";
  	$config = sfConfig::get($path);
  	if (!$config) throw new sfInitializationException("No Redis config located at '$path' in app.yml");
  	return $config;
  }  	
	
}