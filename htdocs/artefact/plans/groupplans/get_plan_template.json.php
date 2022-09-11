<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-plans
 * @author     Alexander Del Ponte <delponte@uni-bremen.de>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

use artefact\plans\tools\PlansTools;

define('INTERNAL', 1);
define('JSON', 1);

try {
    require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
    require(dirname(dirname(__FILE__)) . '/tools/PlansTools.php');
    safe_require('artefact', 'plans');

    if (!PluginArtefactPlans::is_active()) {
        throw new AccessDeniedException(get_string('plugindisableduser', 'mahara', get_string('Plans', 'artefact.plans')));
    }

    $planId = param_integer('planid');
    $userPlanTemplate = new ArtefactTypePlan($planId);

    $result = PlansTools::getArrayOfUserPlanTemplateForForm($userPlanTemplate);
}
catch (Exception $e) {
    json_reply(true, $e->getMessage());
    die;
}

json_reply(false, (object) $result);
