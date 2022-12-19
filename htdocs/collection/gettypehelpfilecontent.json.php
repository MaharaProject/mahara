<?php
/**
 *
 * @package    mahara
 * @subpackage collection
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(__FILE__)) . '/init.php');
require_once('collection.php');
require_once(dirname(dirname(__FILE__)). '/group/outcomes.php');

json_headers();

$collectionid = param_integer('collectionid');
$collection = new Collection($collectionid);

// check if user admin or tutor
if (!($collection->get('group') && (
  group_user_access($collection->get('group'))
))) {
  throw new AccessDeniedException();
}

$outcometypes = get_outcome_types($collection);

if ($outcometypes) {
  $smarty = smarty();
  $smarty->assign('outcometypes', $outcometypes);
  $html = $smarty->fetch('collection/outcometypehelp.tpl');
    json_reply(false, array(
      'message' => null,
      'data'=> array(
        'html' => $html
      )
    ));
}

json_reply('local', get_string('nooutcometypes', 'collection'));
