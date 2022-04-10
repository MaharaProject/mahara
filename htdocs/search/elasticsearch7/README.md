# Local testing with Docker

> This README assumes you are familiar with [Docker](https://www.docker.com/).
> If the contents here do not work please check [the guide](https://www.elastic.co/guide/en/kibana/current/docker.html) for the latest version.

This docker instance is a very basic, single node ES7 instance with no security, TLS, or user accounts. If you need to test a more comprehensive, secure example of a cluster check the contents of `dev/elasticsearch7` in this repo.
## tl;dr;

Add the following to your `htdocs/config.php`:
```php
$cfg->plugin_search_elasticsearch7_host = 'localhost';
$cfg->plugin_search_elasticsearch7_port = '9200';
$cfg->plugin_search_elasticsearch7_scheme = 'http';
$cfg->plugin_search_elasticsearch7_types = 'usr,interaction_instance,interaction_forum_post,group,view,collection,artefact,block_instance';
```
Once you have docker installed:
```bash
# Add a network for elastic.
docker network create elastic
# Spin up the elasticsearch instance.
docker pull docker.elastic.co/elasticsearch/elasticsearch:7.13.3
docker run --name es01-test --net elastic -p 9200:9200 -p 9300:9300 -e "discovery.type=single-node" docker.elastic.co/elasticsearch/elasticsearch:7.13.3
```
If you want the kibana tool for debugging/checking your ES instance, in another
shell:
```bash
docker pull docker.elastic.co/kibana/kibana:7.13.3
docker run --name kib01-test --net elastic -p 5601:5601 -e "ELASTICSEARCH_HOSTS=http://es01-test:9200" docker.elastic.co/kibana/kibana:7.13.3
```
Point your browser at http://localhost:5601/app/dev_tools#/console

Doneburgers

## The longer version

Once you have the ES7 instance running and your config is updated point your
browser at Admin > Configure > Site Options. Expand Search settings and select
Elasticsearch 7.  Save the form.

Next hit Admin menu > Extensions > Plugin administration and click the gear on
Elasticsearch 7.

Under the Index reset section there is a Reset button.  Clicking this will
create the index and populate it with the current content.

### ES7 containers

If you need to stop your containers:
```bash
docker stop es01-test
docker stop kib01-test
```
If you need to remove the containers for any reason:
```bash
docker network rm elastic
docker rm es01-test
docker rm kib01-test
```
Clean up of docker images after that is a normal docker task.

# Development

Module: `htdocs/search/elasticsearch7`

Paths in this section will be relative to the Module directory.

## Getting data into ES7

Data is currently loaded through the Index Reset form on the Plugin administration: search: elasticsearch7 config page.

Clicking the Reset button there calls PluginSearchElasticsearch7::reset_all_searchtypes().  This deletes (if needed) and recreates the ES7 index, Clears and and reloads all content on the indexing queue, and calls PluginSearchElasticsearch7::index_queued_items() to repopulate the ES7 index.

While this is happening there is a config field written to the DB to prevent the indexing process being started by another request.  e.g., from cron.  If this gets in the way you can clear it with this query:

```sql
DELETE FROM config WHERE field = '_cron_lock_search_elasticsearch7_cron';
```

### Index Queue

The index queue is a list of pointers to the content being indexed.  Typically the content type (e.g., view) and the ID of the item.  This queue is stored in the `search_elasticsearch_7_queue` table.

### Index Queued Items

Call: `PluginSearchElasticsearch7::index_queued_items()`

* Fetch content pointers from the `search_elasticsearch_7_queue` table
* Prepare documents for indexing via `PluginSearchElasticsearch7::preprocess_queued_items()`
  * The object class is built from `$ES_class = 'Elasticsearch7Type_' . $type;`. These classes must extend `Elasticsearch7Type` and any specialised handling for the type can be done by overriding the methods in the parent class.
  * Fetch the document for the record ID. 
  * Build an object out of it creating the index mapping in the process.
  * Return the fields of the record that match the mapping (`PluginSearchElasticsearch7::getMapping()`)
* Index the records.
  * `PluginSearchElasticsearch7::send_queued_items_in_bulk()` builds batches of records and inserts them into the ES7 index.
  * This is either done through one of:
    * `PluginSearchElasticsearch7::process_bulk_insertions()`
    * `PluginSearchElasticsearch7::process_single_insertion()`
  * Process Bulk Insertions
    * Prepares the `$params` array that will be converted to JSON and inserted into the ES7 index.

## Getting data out of ES7