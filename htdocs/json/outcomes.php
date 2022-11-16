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
define('JSON', 1);
require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('docroot') . 'lib/collection.php');

$collectionid =  param_integer('collection', null);
$outcomes =  param_variable('outcomes', null);

$collection = new Collection($collectionid);
// check if user admin
if (!($collection->get('group') && group_user_access($collection->get('group')) === 'admin')) {
  throw new AccessDeniedException();
}

json_headers();

try {
  if ($outcomes) {
    foreach($outcomes as $outcome) {
      if ($outcome["id"]) {
        update_record(
          'outcome',
          (object) array(
            "id"           => $outcome["id"],
            "short_title"  => $outcome["short_title"],
            "full_title"   => $outcome["full_title"],
            "outcome_type" => $outcome["outcome_type"] > 0 ? $outcome["outcome_type"] : null,
            "collection"   => $collectionid,
            )
          );
        }
        else {
        insert_record(
          'outcome',
          (object) array(
            "short_title"  => $outcome["short_title"],
            "full_title"   => $outcome["full_title"],
            "outcome_type" => $outcome["outcome_type"] > 0 ? $outcome["outcome_type"] : null,
            "collection"   => $collectionid,
          )
        );
      }
    }
  }
}
catch(Exception $e) {
  print json_encode(get_string('outcomesaveerror', 'collection'));
}
print json_encode(get_string('outcomesavesuccess', 'collection'));
