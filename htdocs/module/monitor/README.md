# Monitor

This module exposes a number of URL and CLI endpoints that return information
that can be presented on a dashboard to show the health of your Mahara site.

## CLI Endpoints
These are the scripts in the `htdocs/module/monitor/cli` folder.

### croncheck.php
Reports on any stuck/locked processes.
 
### elasticsearchcheck.php
Reports on any items that are stuck in the index queue.

### ldaplookupcheck.php
Run the cron check to ensure that all LDAP connections are valid.

### ldapsuspendeduserscheck.php
Run the cron check for the number of suspended users via the LDAP user sync.

### searchcheck.php
Returns the status the configured search plugin.
> This is the new search plugin. For the previous `elasticsearch` plugin use `elasticsearchcheck.php`.

## Monitor is modular

With the addition of `ModuleType_search` we are working towards making this more modular. This means more of the functionality is moved to the class in `type/MonitorType_type.php`.

### ModuleType_type::has_config()

If this returns `true` config functions will be checked for.

### ModuleType_type::config_elements()

Return an array of Pieform elements. These will be wrapped in a named fieldset.

### ModuleType_type::save_config_options($values)

Allows for saving of your config fields. These are Plugin config variables. You'll want to use `set_config_plugin()` for the monitor module.
```php
set_config_plugin(
  'module',
  'monitor',
  'my_field',
  $values['my_field']
);
```

## lib.php

We still have a number of things still in the core Monitor module that need to be set.

### PluginModuleMonitor::get_default_config_value($name)

Due to how the default values are configured they still need to be set here.

### translation strings
Many of the strings are modular, but still need to be set in the `htdocs/module/monitor/lang/en.utf8/module.monitor.php` file. In the strings below the `...` is replaced with a lowercase version of the module type class name.  e.g. For the `ModuleType_search` the fieldset legend would be `configmonitortype_searchlegend`.

`config...legend` : The legend/title in the fieldset for Module Type provided config elements.
