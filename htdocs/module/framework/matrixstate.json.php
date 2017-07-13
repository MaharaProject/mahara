<?php
/**
 *
 * @package    mahara
 * @subpackage module-framework
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('module', 'framework');

global $USER;

if (!is_plugin_active('annotation','blocktype')) {
    json_reply(true, get_string('needtoactivate', 'module.framework'));
}

$sectionid  = param_integer('section');
$collectionid = param_integer('collection');
$state = param_alphanum('state', 'open');
form_validate(param_variable('sesskey', null));

if ($frameworksection = get_record_sql("SELECT fs.framework, fs.shortname FROM {framework_standard} fs
                                   JOIN {collection} c on c.framework = fs.framework
                                   WHERE fs.id = ? and c.id = ?", array($sectionid, $collectionid))) {
    if (isset($_SESSION['matrixsettings'])) {
        $matrixsettings = $_SESSION['matrixsettings'];
    }
    else {
        $matrixsettings = array();
    }
    $title = isset($frameworksection->shortname) ? hsc($frameworksection->shortname) : '';

    $matrixsettings[$collectionid][$sectionid] = $state;
    $matrixsettings['description']['open'] = get_string('collapsesection', 'module.framework', $title);
    $matrixsettings['description']['close'] = get_string('uncollapsesection', 'module.framework', $title);
    $matrixsettings['description']['sectioncollapsed'] = get_string('collapsedsection','module.framework');
    $SESSION->set('matrixsettings', $matrixsettings);
    json_reply(false, (object) array('settings' => $matrixsettings));
}
else {
    json_reply(true, get_string('frameworkstateerror', 'module.framework'));
}
