<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);

define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'copy');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'group.php');

$viewid = param_integer('id');
$collection = param_integer('collection', null);
$groupid = param_integer('group', null);

$view = new View($viewid);
if (!can_view_view($view)) {
    throw new AccessDeniedException(get_string('thisviewmaynotbecopied', 'view'));
}
if (!$view->is_copyable()) {
    throw new AccessDeniedException(get_string('thisviewmaynotbecopied', 'view'));
}

copyview($view->get('id'), 0, $groupid, $collection);
