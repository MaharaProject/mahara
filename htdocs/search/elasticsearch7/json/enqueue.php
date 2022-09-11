<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */


define('INTERNAL', 1);
define('PUBLIC', 1);
define('JSON', 1);

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');

require_once(get_config('libroot') . 'searchlib.php');
require_once(get_config('docroot') . 'search/elasticsearch7/lib/PluginSearchElasticsearch7.php');

$type = param_variable('type', '');
$ids = param_variable('ids', '');

// Sanity check.
if (empty($type)) {
  json_reply(true, ['error' => 'type not set.']);
}

// Parse ids if set.
if (!empty($ids)) {
  $ids = explode(',', $ids);
  $ids = array_map('trim', $ids);
  foreach ($ids as $id) {
    if (!is_numeric($id) || $id != intval($id)) {
      json_reply(false, ['error' => 'IDs should be integers.']);
    }
  }
}

$ret= [];

// If $ids is empty we would add *everything*.
Elasticsearch7Indexing::requeue_searchtype_contents($type, $ids);

// Get the current queue count.
$queuecount = get_records_array('search_elasticsearch_7_queue', 'type', $type);
$indexcount = PluginSearchElasticsearch7::count_type_in_index($type);
$ret = [
  'queue' => count($queuecount),
  'index' => $indexcount,
];

json_reply(false, $ret);