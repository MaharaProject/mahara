<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-plans
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'plans');

$limit = param_integer('limit', 10);
$offset = param_integer('offset', 0);

$plans = ArtefactTypePlan::get_plans($offset, $limit);
ArtefactTypePlan::build_plans_list_html($plans);

json_reply(false, (object) array('message' => false, 'data' => $plans));
