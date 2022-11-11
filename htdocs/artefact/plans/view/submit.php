<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-plans
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @author     Alexander Del Ponte <delponte@uni-bremen.de>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

use artefact\plans\tools\PlansTools;

define('INTERNAL', 1);

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
require_once('activity.php');
safe_require('artefact', 'plans');
require_once(get_config('docroot') . 'artefact/plans/tools/PlansTools.php');

$portfolioType = param_variable('portfoliotype');
$portfolioId = param_integer('portfolio');
$groupId = param_integer('group');
$returnToPlanId = param_integer('returntoplan');

switch ($portfolioType) {
    case 'view':
        require_once(get_config('libroot').'view.php');
        $portfolioElement = new \View($portfolioId);
        break;
    case 'collection':
        require_once(get_config('libroot').'collection.php');
        $portfolioElement = new \Collection($portfolioId);
        break;
    default:
        throw new \MaharaException(get_string('unsupportedportfoliotype','artefact.plans'));
}

list($ownerType, $ownerId) = PlansTools::getOwnerTypeAndOwnerIdFromMaharaObject($portfolioElement);
switch ($ownerType) {
    case 'owner':
        define('MENUITEM', 'create/plans');
        break;
    case 'group':
        define('MENUITEM', 'engage/index');
        define('MENUITEM_SUBPAGE', 'groupplans');
        break;
}

$group = get_record_sql(
    'SELECT g.id, g.name, g.grouptype, g.urlid
       FROM {group_member} u
       INNER JOIN {group} g ON (u.group = g.id AND g.deleted = 0)
       WHERE u.member = ?
       AND g.id = ?
       AND g.submittableto = 1',
    array($USER->get('id'), $groupId)
);

if (!$group || !group_within_edit_window($group)) {
    throw new AccessDeniedException(get_string('cantsubmittogroup', 'view'));
}

if (!$portfolioElement || $portfolioElement->is_submitted() || ($portfolioElement->get('owner') !== $USER->get('id'))) {
    throw new AccessDeniedException(get_string('cantsubmit' . $portfolioType . 'togroup', 'view'));
}

$submissionTitle = PlansTools::getPortfolioElementTitle($portfolioElement);
define('TITLE', get_string('submitviewtogroup', 'view', $submissionTitle, $group->name));

$form = pieform(array(
    'name' => 'submitview',
    'renderer' => 'div',
    'autofocus' => false,
    'method' => 'post',
    'elements' => array(
        'submit' => array(
            'subclass' => array('btn-secondary'),
            'type' => 'submitcancel',
            'value' => array(get_string('yes'), get_string('no')),
            'goto' => gotoURL(),
        )
    ),
));

$smarty = smarty();
$smarty->assign('message', get_string('submitconfirm1', 'view', $submissionTitle, $group->name));
$smarty->assign('form', $form);
$smarty->display('view/submit.tpl');

function submitview_submit(Pieform $form, $values) {
    global $SESSION, $USER, $portfolioType, $portfolioElement, $group;

    if (!empty($portfolioElement)) {
        $portfolioElement->submit($group, null, $USER->get('id'), true);
        $SESSION->add_ok_msg(get_string($portfolioType .'submitted', 'view'));
    }
    redirect(gotoURL());
}

function gotoURL() {
    global $returnToPlanId, $portfolioElement;

    try {
        $returnPlan = new ArtefactTypePlan($returnToPlanId);
        $urlQuery = ['id' => $returnPlan->get('id')];

        if ($returnPlan->is_groupplan()) {
            $urlQuery['group'] = $returnPlan->get('group');
        }
    }
    catch (\Exception $e) {
        return get_config('wwwroot') . $portfolioElement->get_url(false);
    }
    return get_config('wwwroot') . 'artefact/plans/plan/view.php?' . http_build_query($urlQuery);
}
