sfRediskaPlugin
============
 
sfRediskaPlugin provides Symfony caching with Redis via Rediska (as defined in app.yml and factories.yml), and additionally allows a simple proxy interface to Rediska instances.
 
Also, a Doctrine Redis driver is provided (also using Rediska) for query/result caching.

Installation
========

This plugin is currently github hosted, so if you are already using git for your project, do the following:

`git submodule add git@github.com:mastermix/sfRediskaPlugin.git plugins/sfRediskaPlugin`

from inside the root of your project directory.

First of all, lets configure your Rediska instances (eg. app, otherstuff) in app.yml:

    all:
      redis:

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
In the above example, we are going to use the 'app' instance as the storage for Symfony internal caching, and 'otherstuff' for caching other critical data.

Symfony Caches
============
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
              prefix:			routing
              instance:			app      
