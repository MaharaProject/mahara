<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-comment
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('libroot') . 'view.php');
safe_require('artefact', 'comment');

$extradata = json_decode(param_variable('extradata'));

if (!can_view_view($extradata->view)) {
    json_reply('local', get_string('noaccesstoview', 'view'));
}
if (!empty($extradata->artefact) && !artefact_in_view($extradata->artefact, $extradata->view)) {
    json_reply('local', get_string('accessdenied', 'error'));
}

$limit    = param_integer('limit', 10);
$offset   = param_integer('offset');

$artefact = null;
if (!empty($extradata->artefact)) {
    $artefact = artefact_instance_from_id($extradata->artefact);
}

$view = new View($extradata->view);
$commentoptions = ArtefactTypeComment::get_comment_options();
$commentoptions->limit = $limit;
$commentoptions->offset = $offset;
$commentoptions->view = $view;
$commentoptions->artefact = $artefact;
$data = ArtefactTypeComment::get_comments($commentoptions);

json_reply(false, array('data' => $data));
