sfRediskaPlugin
============
 
sfRediskaPlugin provides Symfony caching with Redis via Rediska (as defined in app.yml and factories.yml), and additionally allows a simple proxy interface to Rediska instances.
 
Also, a Doctrine Redis driver is provided (also using Rediska) for query/result caching.

Installation
---

There are several ways you can install the plugin:

**Clone from git**

Clone from our plugin github repository to your Symfony project plugins directory:

`git clone http://github.com/mastermix/sfRediskaPlugin.git`

**Add as submodule**

Add plugin github repository to you project repository as git submodule:

`git submodule add http://github.com/mastermix/sfRediskaPlugin.git plugins/sfRediskaPlugin`

**Export from SVN**

Export from plugin SVN repository to your Symfony project plugins directory:

`svn export http://svn.symfony-project.com/plugins/sfRediskaPlugin`

**Add as externals**

Add plugin SVN repository to you project repository as externals:

`svn propset svn:externals "plugins/sfRediskaPlugin http://svn.symfony-project.com/plugins/sfRediskaPlugin" .`

The SVN repository is automatically synchronised whenever a commit is made to the github repository.

Enable sfRediskaPlugin
---

Don't forget to enable the plugin in your ProjectConfiguration.class.php:

    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        $this->enablePlugins('sfRediskaPlugin');
      } 
    }

Configuration
---

First of all, lets configure your Rediska instances (eg. app, otherstuff) in app.yml:


    all:
      rediska:

        app:
          servers:
            server_01:
              host: 127.0.0.1
              persistent: true 
    
        otherstuff: 
          servers:
            server_01:
              host: 127.0.0.1
              persistent: true          
            server_02:
              host: 127.0.0.1
              port: 6380
              persistent: true  

All configuration parameters that Rediska offers when setting up a Rediska instance are available here.

Now we can use these instances to configure a great number of things!

In the above example, we are going to use the `app` instance as the storage for Symfony internal caching, and `otherstuff` for caching other critical data.

Symfony Caches
---

Symfony provides the ability to cache critical parts of your application - session storage, routing, and the view cache.

To configure sfRediskaPlugin for session storage:

      storage:
        class: sfCacheSessionStorage
        param:
          cache:
            class:			sfRediskaCache        
            param:
              lifetime:			86400
              prefix:			%SF_APP_DIR%
              instance:			app

For routing:

      routing:
        class: sfPatternRouting
        param:
          generate_shortest_url:            true
          extra_parameters_as_query_string: true
          cache: 
            class: sfRediskaCache
            param:
              lifetime:			86400
              prefix:			routing:%SF_APP%:%SF_ENVIRONMENT%
              instance:			app      

For view cache:

      view_cache:
        class: sfRediskaCache
        param:
          instance:			app    
          prefix:	                view:%SF_APP%:%SF_ENVIRONMENT%

**Rediska Commands**

You may also use sfRediskaPlugin to directly use any Rediska instance.
These are only instantiated once per page load for efficiency.

Heres an example:

    $rediska = redis = sfRediska::getInstance('otherstuff');
    $this->redis->pipeline()
      ->addToSortedSet(...)
      ->deleteFromSortedSet(..)
      ->delete(..)
      ->execute();

As you can see all the Rediska commands are fully accessible this way.

Doctrine Driver
---

To setup the Rediska Doctrine driver, first you must edit your application configuration file, eg. frontendConfiguration.class.php

    public function configureDoctrine(Doctrine_Manager $manager)
    {
        $cacheDriver = new Doctrine_Cache_Redis(array('instance' => 'otherstuff', 'prefix' => 'dql:'));
        $manager->setAttribute(Doctrine_Core::ATTR_RESULT_CACHE, $cacheDriver);
    } 

**NOTE:** ProjectConfiguration.class.php  cannot be used as sfRediskaPlugin depends on configuration variables set at application level.

Links
---

Rediska - [http://rediska.geometria-lab.net/][1]

  [1]: http://rediska.geometria-lab.net/

Thanks
---

Credit is due to Thomas Parisot and Benjamin Viellard for their individual plugin contributions. 
Some implementation ideas from their plugins have also been considered when developing this plugin.