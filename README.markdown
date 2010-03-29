sfRediskaPlugin
============
 
sfRediskaPlugin provides Symfony caching with Redis via Rediska (as defined in app.yml and factories.yml), and additionally allows a simple proxy interface to Rediska instances.
 
Also, a Doctrine Redis driver is provided (also using Rediska) for query/result caching.

Installation
========

This plugin is currently github hosted, so if you are already using git for your project, do the following:

`git submodule add git@github.com:mastermix/sfRediskaPlugin.git plugins/sfRediskaPlugin`

from inside the root of your project directory.

Symfony Caches
============
Symfony provides the ability to cache critical parts of your application - session storage, routing, config and the view cache.

To configure sfRediskaPlugin for session storage:

`
  storage:
    class: sfCacheSessionStorage
    param:
      cache:
        class:			sfRediskaCache        
        param:
          lifetime:			86400
          prefix:			%SF_APP_DIR%
          instance:			app
`