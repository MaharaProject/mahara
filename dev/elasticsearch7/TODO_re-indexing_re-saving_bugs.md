# Re-indexing/re-saving bugs on ES7

- [[FIXED] Re-indexing/re-saving bugs on ES7](#fixed-re-indexingre-saving-bugs-on-es7)
  - [Base testing steps](#base-testing-steps)
  - [Elasticsearch7Type_artefact.php bug](#elasticsearch7type_artefactphp-bug)
  - [Elasticsearch7Type_block_instance.php bug](#elasticsearch7type_block_instancephp-bug)

## Base testing steps

1. Edit `test.feature` and put `And I insert breakpoint after the login step.
2. Run `mahara/test/behat/mahara_behat.sh rundebug test.feature --format=pretty`.
3. At the breakpoint, go to localhost:8000
4. Turn ES7 on.
5. Change the main search to be ES7.
6. Go to the settings page for ES7.


**Note** on updating plugin admin: search: elasticsearch7

When you save/reindex SQL DELETE + INSERTS go into search_elasticsearch_7_queue. The page needs to be refreshed to see the frontend change and 'In queue' and 'In index' to be correct.

## [FIXED] Elasticsearch7Type_artefact.php bug

**Cause**: SQL query not working for requeueing

Test steps:

1. Select the `academicgoal` artefacts to be indexed.
2. Press save. Get a green success message.
3. Go back and untick the `academicgoal`
    - expect: A green success message
    - actual: Red warning line
4. Refresh the page to see errors.

Output:

```bash
[DBG] d3 (lib/dml.php:180) postgres9 error: [-1: ERROR:  column "academicskill" does not exist
LINE 1: ...ueue" WHERE type = 'artefact'AND artefacttype IN (academicsk...
                                                            ^] in EXECUTE("DELETE FROM "behat_search_elasticsearch_7_queue" WHERE type = 'artefact'AND artefacttype IN (academicskill)")Command was: DELETE FROM "behat_search_elasticsearch_7_queue" WHERE type = 'artefact'AND artefacttype IN (academicskill)
[WAR] d3 (lib/errors.php:853) Could not execute command: DELETE FROM "behat_search_elasticsearch_7_queue" WHERE type = 'artefact'AND artefacttype IN (academicskill)
Call stack (most recent first):

    log_message("Could not execute command: DELETE FROM "behat_sear...", 8, true, true) at /home/doristam/code/mahara/htdocs/lib/errors.php:89
    log_warn("Could not execute command: DELETE FROM "behat_sear...") at /home/doristam/code/mahara/htdocs/lib/errors.php:853
    SQLException->__construct("Could not execute command: DELETE FROM "behat_sear...") at /home/doristam/code/mahara/htdocs/lib/dml.php:181
    execute_sql("DELETE FROM "behat_search_elasticsearch_7_queue" W...") at /home/doristam/code/mahara/htdocs/search/elasticsearch7/lib/Elasticsearch7Type_artefact.php:665
    Elasticsearch7Type_artefact::requeue_searchtype_contents(array(size 0), "academicskill") at /home/doristam/code/mahara/htdocs/search/elasticsearch7/lib/Elasticsearch7Indexing.php:139
    Elasticsearch7Indexing::requeue_searchtype_contents("artefact", array(size 0), "academicskill") at /home/doristam/code/mahara/htdocs/search/elasticsearch7/lib/PluginSearchElasticsearch7.php:353
    PluginSearchElasticsearch7::get_config_options_artefact_types_process(object(Pieform), array(size 30)) at /home/doristam/code/mahara/htdocs/search/elasticsearch7/lib/PluginSearchElasticsearch7.php:251
    PluginSearchElasticsearch7::save_config_options(object(Pieform), array(size 30)) at /home/doristam/code/mahara/htdocs/lib/mahara.php:1914
    call_static_method("PluginSearchElasticsearch7", "save_config_options", object(Pieform), array(size 30)) at /home/doristam/code/mahara/htdocs/admin/extensions/pluginconfig.php:117
    pluginconfig_submit(object(Pieform), array(size 30)) at /home/doristam/code/mahara/htdocs/lib/pieforms/pieform.php:577
    Pieform->__construct(array(size 9)) at /home/doristam/code/mahara/htdocs/lib/pieforms/pieform.php:173
    Pieform::process(array(size 9)) at /home/doristam/code/mahara/htdocs/lib/mahara.php:5754
    pieform(array(size 9)) at /home/doristam/code/mahara/htdocs/admin/extensions/pluginconfig.php:90

[WAR] d3 (admin/extensions/pluginconfig.php:133) First parameter must either be an object or the name of an existing class
Call stack (most recent first):

    log_message("First parameter must either be an object or the na...", 8, true, true, "/home/doristam/code/mahara/htdocs/admin/extensions...", 133) at /home/doristam/code/mahara/htdocs/lib/errors.php:515
    error(2, "First parameter must either be an object or the na...", "/home/doristam/code/mahara/htdocs/admin/extensions...", 133, array(size 9)) at Unknown:0
    property_exists(null, "error") at /home/doristam/code/mahara/htdocs/admin/extensions/pluginconfig.php:133
    pluginconfig_submit(object(Pieform), array(size 30)) at /home/doristam/code/mahara/htdocs/lib/pieforms/pieform.php:577
    Pieform->__construct(array(size 9)) at /home/doristam/code/mahara/htdocs/lib/pieforms/pieform.php:173
    Pieform::process(array(size 9)) at /home/doristam/code/mahara/htdocs/lib/mahara.php:5754
    pieform(array(size 9)) at /home/doristam/code/mahara/htdocs/admin/extensions/pluginconfig.php:90

[DBG] 4e (lib/dml.php:180) postgres9 error: [-1: ERROR:  column "academicgoal" does not exist
LINE 1: ...ueue" WHERE type = 'artefact'AND artefacttype IN (academicgo...
                                                            ^] in EXECUTE("DELETE FROM "behat_search_elasticsearch_7_queue" WHERE type = 'artefact'AND artefacttype IN (academicgoal)")Command was: DELETE FROM "behat_search_elasticsearch_7_queue" WHERE type = 'artefact'AND artefacttype IN (academicgoal)
[WAR] 4e (lib/errors.php:853) Could not execute command: DELETE FROM "behat_search_elasticsearch_7_queue" WHERE type = 'artefact'AND artefacttype IN (academicgoal)
Call stack (most recent first):

    log_message("Could not execute command: DELETE FROM "behat_sear...", 8, true, true) at /home/doristam/code/mahara/htdocs/lib/errors.php:89
    log_warn("Could not execute command: DELETE FROM "behat_sear...") at /home/doristam/code/mahara/htdocs/lib/errors.php:853
    SQLException->__construct("Could not execute command: DELETE FROM "behat_sear...") at /home/doristam/code/mahara/htdocs/lib/dml.php:181
    execute_sql("DELETE FROM "behat_search_elasticsearch_7_queue" W...") at /home/doristam/code/mahara/htdocs/search/elasticsearch7/lib/Elasticsearch7Type_artefact.php:665
    Elasticsearch7Type_artefact::requeue_searchtype_contents(array(size 0), "academicgoal") at /home/doristam/code/mahara/htdocs/search/elasticsearch7/lib/Elasticsearch7Indexing.php:139
    Elasticsearch7Indexing::requeue_searchtype_contents("artefact", array(size 0), "academicgoal") at /home/doristam/code/mahara/htdocs/search/elasticsearch7/lib/PluginSearchElasticsearch7.php:353
    PluginSearchElasticsearch7::get_config_options_artefact_types_process(object(Pieform), array(size 30)) at /home/doristam/code/mahara/htdocs/search/elasticsearch7/lib/PluginSearchElasticsearch7.php:251
    PluginSearchElasticsearch7::save_config_options(object(Pieform), array(size 30)) at /home/doristam/code/mahara/htdocs/lib/mahara.php:1914
    call_static_method("PluginSearchElasticsearch7", "save_config_options", object(Pieform), array(size 30)) at /home/doristam/code/mahara/htdocs/admin/extensions/pluginconfig.php:117
    pluginconfig_submit(object(Pieform), array(size 30)) at /home/doristam/code/mahara/htdocs/lib/pieforms/pieform.php:577
    Pieform->__construct(array(size 9)) at /home/doristam/code/mahara/htdocs/lib/pieforms/pieform.php:173
    Pieform::process(array(size 9)) at /home/doristam/code/mahara/htdocs/lib/mahara.php:5754
    pieform(array(size 9)) at /home/doristam/code/mahara/htdocs/admin/extensions/pluginconfig.php:90

```

## [FIXED] Elasticsearch7Type_block_instance.php bug

Test steps:

1. Select all the artefacts to be indexed.
2. Press save. Get a green success message.
3. Press reset. Get a green success message.
4. Press save
   - Expect: Get a green success message
   - Actual: Red warning message
5. Refresh the page to see the errors

**Cause**: block instance uses 'OR' when looking for config index 'text' and block instances do not have a description field as they're only available when config['text'] exists and is not '' or null.

Output:

```bash
[WAR] 1b (search/elasticsearch7/lib/Elasticsearch7Type_block_instance.php:142) array_key_exists() expects parameter 2 to be array, bool given

Call stack (most recent first):

    log_message("array_key_exists() expects parameter 2 to be array...", 8, true, true, "/home/doristam/code/mahara/htdocs/search/elasticse...", 142) at /home/doristam/code/mahara/htdocs/lib/errors.php:515
    error(2, "array_key_exists() expects parameter 2 to be array...", "/home/doristam/code/mahara/htdocs/search/elasticse...", 142, array(size 9)) at /home/doristam/code/mahara/htdocs/search/elasticsearch7/lib/Elasticsearch7Type_block_instance.php:142
    Elasticsearch7Type_block_instance::get_record_data_by_id("block_instance", "14") at /home/doristam/code/mahara/htdocs/search/elasticsearch7/lib/Elasticsearch7Type_block_instance.php:57
    Elasticsearch7Type_block_instance::get_record_by_id("block_instance", "14") at /home/doristam/code/mahara/htdocs/search/elasticsearch7/lib/PluginSearchElasticsearch7.php:1600
    PluginSearchElasticsearch7::preprocess_queued_items(array(size 106), array(size 53)) at /home/doristam/code/mahara/htdocs/search/elasticsearch7/lib/PluginSearchElasticsearch7.php:1458
    PluginSearchElasticsearch7::index_queued_items() at /home/doristam/code/mahara/htdocs/search/elasticsearch7/lib/PluginSearchElasticsearch7.php:875
    PluginSearchElasticsearch7::reset_search_index() at /home/doristam/code/mahara/htdocs/search/elasticsearch7/lib/PluginSearchElasticsearch7.php:815
    PluginSearchElasticsearch7::get_config_options_index_reset_process(object(Pieform), array(size 30)) at /home/doristam/code/mahara/htdocs/search/elasticsearch7/lib/PluginSearchElasticsearch7.php:247
    PluginSearchElasticsearch7::save_config_options(object(Pieform), array(size 30)) at /home/doristam/code/mahara/htdocs/lib/mahara.php:1914
    call_static_method("PluginSearchElasticsearch7", "save_config_options", object(Pieform), array(size 30)) at /home/doristam/code/mahara/htdocs/admin/extensions/pluginconfig.php:117
    pluginconfig_submit(object(Pieform), array(size 30)) at /home/doristam/code/mahara/htdocs/lib/pieforms/pieform.php:577
    Pieform->__construct(array(size 9)) at /home/doristam/code/mahara/htdocs/lib/pieforms/pieform.php:173
    Pieform::process(array(size 9)) at /home/doristam/code/mahara/htdocs/lib/mahara.php:5754
    pieform(array(size 9)) at /home/doristam/code/mahara/htdocs/admin/extensions/pluginconfig.php:90

[WAR] 1b (search/elasticsearch7/lib/Elasticsearch7Type_block_instance.php:66) Undefined property: stdClass::$description
Call stack (most recent first):

    log_message("Undefined property: stdClass::$description", 8, true, true, "/home/doristam/code/mahara/htdocs/search/elasticse...", 66) at /home/doristam/code/mahara/htdocs/lib/errors.php:515
    error(8, "Undefined property: stdClass::$description", "/home/doristam/code/mahara/htdocs/search/elasticse...", 66, array(size 5)) at /home/doristam/code/mahara/htdocs/search/elasticsearch7/lib/Elasticsearch7Type_block_instance.php:66
    Elasticsearch7Type_block_instance::get_record_by_id("block_instance", "14") at /home/doristam/code/mahara/htdocs/search/elasticsearch7/lib/PluginSearchElasticsearch7.php:1600
    PluginSearchElasticsearch7::preprocess_queued_items(array(size 106), array(size 53)) at /home/doristam/code/mahara/htdocs/search/elasticsearch7/lib/PluginSearchElasticsearch7.php:1458
    PluginSearchElasticsearch7::index_queued_items() at /home/doristam/code/mahara/htdocs/search/elasticsearch7/lib/PluginSearchElasticsearch7.php:875
    PluginSearchElasticsearch7::reset_search_index() at /home/doristam/code/mahara/htdocs/search/elasticsearch7/lib/PluginSearchElasticsearch7.php:815
    PluginSearchElasticsearch7::get_config_options_index_reset_process(object(Pieform), array(size 30)) at /home/doristam/code/mahara/htdocs/search/elasticsearch7/lib/PluginSearchElasticsearch7.php:247
    PluginSearchElasticsearch7::save_config_options(object(Pieform), array(size 30)) at /home/doristam/code/mahara/htdocs/lib/mahara.php:1914
    call_static_method("PluginSearchElasticsearch7", "save_config_options", object(Pieform), array(size 30)) at /home/doristam/code/mahara/htdocs/admin/extensions/pluginconfig.php:117
    pluginconfig_submit(object(Pieform), array(size 30)) at /home/doristam/code/mahara/htdocs/lib/pieforms/pieform.php:577
    Pieform->__construct(array(size 9)) at /home/doristam/code/mahara/htdocs/lib/pieforms/pieform.php:173
    Pieform::process(array(size 9)) at /home/doristam/code/mahara/htdocs/lib/mahara.php:5754
    pieform(array(size 9)) at /home/doristam/code/mahara/htdocs/admin/extensions/pluginconfig.php:90

[WAR] 1b (search/elasticsearch7/lib/Elasticsearch7Type_block_instance.php:142) array_key_exists() expects parameter 2 to be array, bool given
Call stack (most recent first):

    log_message("array_key_exists() expects parameter 2 to be array...", 8, true, true, "/home/doristam/code/mahara/htdocs/search/elasticse...", 142) at /home/doristam/code/mahara/htdocs/lib/errors.php:515
    error(2, "array_key_exists() expects parameter 2 to be array...", "/home/doristam/code/mahara/htdocs/search/elasticse...", 142, array(size 9)) at /home/doristam/code/mahara/htdocs/search/elasticsearch7/lib/Elasticsearch7Type_block_instance.php:142
    Elasticsearch7Type_block_instance::get_record_data_by_id("block_instance", "15") at /home/doristam/code/mahara/htdocs/search/elasticsearch7/lib/Elasticsearch7Type_block_instance.php:57
    Elasticsearch7Type_block_instance::get_record_by_id("block_instance", "15") at /home/doristam/code/mahara/htdocs/search/elasticsearch7/lib/PluginSearchElasticsearch7.php:1600
    PluginSearchElasticsearch7::preprocess_queued_items(array(size 106), array(size 53)) at /home/doristam/code/mahara/htdocs/search/elasticsearch7/lib/PluginSearchElasticsearch7.php:1458
    PluginSearchElasticsearch7::index_queued_items() at /home/doristam/code/mahara/htdocs/search/elasticsearch7/lib/PluginSearchElasticsearch7.php:875
    PluginSearchElasticsearch7::reset_search_index() at /home/doristam/code/mahara/htdocs/search/elasticsearch7/lib/PluginSearchElasticsearch7.php:815
    PluginSearchElasticsearch7::get_config_options_index_reset_process(object(Pieform), array(size 30)) at /home/doristam/code/mahara/htdocs/search/elasticsearch7/lib/PluginSearchElasticsearch7.php:247
    PluginSearchElasticsearch7::save_config_options(object(Pieform), array(size 30)) at /home/doristam/code/mahara/htdocs/lib/mahara.php:1914
    call_static_method("PluginSearchElasticsearch7", "save_config_options", object(Pieform), array(size 30)) at /home/doristam/code/mahara/htdocs/admin/extensions/pluginconfig.php:117
    pluginconfig_submit(object(Pieform), array(size 30)) at /home/doristam/code/mahara/htdocs/lib/pieforms/pieform.php:577
    Pieform->__construct(array(size 9)) at /home/doristam/code/mahara/htdocs/lib/pieforms/pieform.php:173
    Pieform::process(array(size 9)) at /home/doristam/code/mahara/htdocs/lib/mahara.php:5754
    pieform(array(size 9)) at /home/doristam/code/mahara/htdocs/admin/extensions/pluginconfig.php:90
```
