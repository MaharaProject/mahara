<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PAGE', 'editaccess');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require_once('pieforms/pieform/elements/calendar.php');
require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'collection.php');
require_once(get_config('libroot') . 'group.php');

$new = param_boolean('new');

$collection = null;
if ($collectionid = param_integer('collection', null)) {
    define('SECTION_PLUGINNAME', 'collection');
    $collection = new Collection($collectionid);
    $views = $collection->views();
    if (empty($views)) {
        $SESSION->add_error_msg(get_string('emptycollectionnoeditaccess', 'collection'));
        redirect('/collection/views.php?id=' . $collectionid . '&new=' . $new);
    }
    // Pick any old view, they all have the same access records.
    $viewid = $views['views'][0]->view;
}
else {
    $viewid = param_integer('id');
    define('SECTION_PLUGINNAME', 'view');
}

$view = new View($viewid);

if ($collection) {
    define('TITLE', $collection->get('name') . ': ' . get_string('editaccess', 'view'));
}
else {
    define('TITLE', $view->get('title') . ': ' . get_string('editaccess', 'view'));
}

$group = $view->get('group');
$institution = $view->get('institution');
View::set_nav($group, $institution, false, $collection);


if (!$USER->can_edit_view($view)) {
    throw new AccessDeniedException();
}

$js = '';
if (empty($collection) && !count_records('block_instance', 'view', $view->get('id'))) {
    $confirmmessage = get_string('reallyaddaccesstoemptyview', 'view');
    $js .= <<<EOF
addLoadEvent(function() {
    connect('editaccess_submit', 'onclick', function () {
        var accesslistrows = getElementsByTagAndClassName('tr', null, 'accesslistitems');
        if (accesslistrows.length > 0 && !confirm('{$confirmmessage}')) {
            replaceChildNodes('accesslistitems', []);
        }
    });
});
EOF;
}

// @todo need a rule here that prevents stopdate being smaller than startdate
$form = array(
    'name' => 'editaccess',
    'renderer' => 'div',
    'plugintype' => 'core',
    'pluginname' => 'view',
    'viewid' => $view->get('id'),
    'userview' => (int) $view->get('owner'),
    'elements' => array(
        'id' => array(
            'type' => 'hidden',
            'value' => $view->get('id'),
        ),
        'new' => array(
            'type' => 'hidden',
            'value' => $new,
        ),
        'allowcomments' => array(
            'type'         => 'checkbox',
            'title'        => get_string('allowcomments','artefact.comment'),
            'description'  => get_string('allowcommentsonview','view'),
            'defaultvalue' => $view->get('allowcomments'),
        ),
        'approvecomments' => array(
            'type'         => 'checkbox',
            'title'        => get_string('moderatecomments', 'artefact.comment'),
            'description'  => get_string('moderatecommentsdescription', 'artefact.comment'),
            'defaultvalue' => $view->get('approvecomments'),
        ),
        'template' => array(
            'type'         => 'checkbox',
            'title'        => get_string('allowcopying', 'view'),
            'description'  => $collection ? get_string('templatedescriptionplural', 'view') : get_string('templatedescription', 'view'),
            'defaultvalue' => $view->get('template'),
        ),
    )
);

if ($institution) {
    if ($institution == 'mahara') {
        $form['elements']['copynewuser'] = array(
            'type'         => 'checkbox',
            'title'        => get_string('copyfornewusers', 'view'),
            'description'  => get_string('copyfornewusersdescription', 'view'),
            'defaultvalue' => $view->get('template') && $view->get('copynewuser'),
        );
        $form['elements']['copyfornewgroups'] = array(
            'type'         => 'html',
            'value'        => '<label>' . get_string('copyfornewgroups', 'view') . '</label>',
        );
        $form['elements']['copyfornewgroupsdescription'] = array(
            'type'         => 'html',
            'value'        => '<div class="description">' . get_string('copyfornewgroupsdescription', 'view') . '</div>',
        );
        $copyoptions = array('copynewuser', 'copyfornewgroups', 'copyfornewgroupsdescription');
        $needsaccess = array('copynewuser');
        $createfor = $view->get_autocreate_grouptypes();
        foreach (group_get_grouptypes() as $grouptype) {
            safe_require('grouptype', $grouptype);
            $jointypestrings = array();
            foreach (call_static_method('GroupType' . $grouptype, 'allowed_join_types', true) as $jointype) {
                $jointypestrings[] = get_string('membershiptype.'.$jointype, 'group');
            }
            $form['elements']['copyfornewgroups_'.$grouptype] = array(
                'type'         => 'checkbox',
                'title'        => get_string('name', 'grouptype.' . $grouptype) . ' (' . join(', ', $jointypestrings) . ')',
                'defaultvalue' => $view->get('template') && in_array($grouptype, $createfor),
            );
            $copyoptions[] = 'copyfornewgroups_'.$grouptype;
            $needsaccess[] = 'copyfornewgroups_'.$grouptype;
        }
    }
    else {
        $form['elements']['copynewuser'] = array(
            'type'         => 'checkbox',
            'title'        => get_string('copyfornewmembers', 'view'),
            'description'  => get_string('copyfornewmembersdescription', 'view', get_field('institution', 'displayname', 'name', $institution)),
            'defaultvalue' => $view->get('template') && $view->get('copynewuser'),
        );
        $copyoptions = array('copynewuser');
        $needsaccess = array('copynewuser');
    }
    $copyoptionstr = json_encode($copyoptions);
    $needsaccessstr = json_encode($needsaccess);
    $js .= <<<EOF
function update_copy_options() {
    if ($('editaccess_template').checked) {
        forEach({$copyoptionstr}, function (id) {
            removeElementClass($('editaccess_'+id+'_container'), 'hidden');
        });
    }
    else {
        forEach({$copyoptionstr}, function (id) {
            addElementClass($('editaccess_'+id+'_container'), 'hidden');
        });
        forEach({$needsaccessstr}, function (id) {
            $('editaccess_'+id).checked = false;
        });
        update_loggedin_access();
    }
}
function update_loggedin_access() {
    if (some({$needsaccessstr}, function (id) { return $('editaccess_'+id).checked; })) {
        ensure_loggedin_access();
    }
    else {
        relax_loggedin_access();
    }
}
addLoadEvent(function() {
    update_copy_options();
    connect('editaccess_template', 'onchange', update_copy_options);
    forEach({$needsaccessstr}, function (id) {
        connect('editaccess_'+id, 'onchange', update_loggedin_access);
    });
});
EOF;
} else {
    $js .= "function update_loggedin_access() {}\n";
}

if (!$allowcomments = $view->get('allowcomments')) {
    $form['elements']['approvecomments']['class'] = 'hidden';
}
$allowcomments = json_encode((int) $allowcomments);

$js .= <<<EOF
var allowcomments = {$allowcomments};
function update_comment_options() {
    allowcomments = $('editaccess_allowcomments').checked;
    if (allowcomments) {
        removeElementClass($('editaccess_approvecomments'), 'hidden');
        removeElementClass($('editaccess_approvecomments_container'), 'hidden');
        forEach(getElementsByTagAndClassName('tr', 'comments', 'accesslistitems'), function (elem) {
            addElementClass(elem, 'hidden');
        });
    }
    else {
        addElementClass($('editaccess_approvecomments_container'), 'hidden');
        forEach(getElementsByTagAndClassName('tr', 'comments', 'accesslistitems'), function (elem) {
            removeElementClass(elem, 'hidden');
        });
    }
}
addLoadEvent(function() {
    connect('editaccess_allowcomments', 'onchange', update_comment_options);
});
EOF;


$form['elements']['accesslist'] = array(
    'type'         => 'viewacl',
    'defaultvalue' => isset($view) ? $view->get_access(get_string('strftimedatetimeshort')) : null
);

$form['elements']['overrides'] = array(
    'type' => 'fieldset',
    'legend' => get_string('overridingstartstopdate', 'view'),
    'elements' => array(
        'description' => array(
            'type' => 'html',
            'value' => get_string('overridingstartstopdatesdescription', 'view'),
        ),
        'startdate'        => array(
            'type'         => 'calendar',
            'title'        => get_string('startdate','view'),
            'description'  => get_string('datetimeformatguide'),
            'defaultvalue' => isset($view) ? strtotime($view->get('startdate')) : null,
            'caloptions'   => array(
                'showsTime'      => true,
                'ifFormat'       => get_string('strftimedatetimeshort'),
            ),
        ),
        'stopdate'  => array(
            'type'         => 'calendar',
            'title'        => get_string('stopdate','view'),
            'description'  => get_string('datetimeformatguide'),
            'defaultvalue' => isset($view) ? strtotime($view->get('stopdate')) : null,
            'caloptions'   => array(
                'showsTime'      => true,
                'ifFormat'       => get_string('strftimedatetimeshort'),
            ),
        ),
    ),
);

$confirmcancelstr = $collection ? get_string('confirmcancelcreatingcollection', 'collection') : get_string('confirmcancelcreatingview', 'view');
$goto = $collection ? '' : '';
$form['elements']['submit'] = array(
    'type'  => !empty($new) ? 'cancelbackcreate' : 'submitcancel',
    'value' => !empty($new) 
        ? array(get_string('cancel'), get_string('back','view'), get_string('save'))
        : array(get_string('save'), get_string('cancel')),
    'confirm' => !empty($new) ? array($confirmcancelstr, null, null) : null,
);

if (!function_exists('strptime')) {
    // Windows doesn't have this, use an inferior version
    function strptime($date, $format) {
        $result = array(
            'tm_sec'  => 0, 'tm_min'  => 0, 'tm_hour' => 0, 'tm_mday'  => 1,
            'tm_mon'  => 0, 'tm_year' => 0, 'tm_wday' => 0, 'tm_yday'  => 0,
        );
        $formats = array(
            '%Y' => array('len' => 4, 'key' => 'tm_year'),
            '%m' => array('len' => 2, 'key' => 'tm_mon'),
            '%d' => array('len' => 2, 'key' => 'tm_mday'),
            '%H' => array('len' => 2, 'key' => 'tm_hour'),
            '%M' => array('len' => 2, 'key' => 'tm_min'),
        );
        while ($format) {
            $start = substr($format, 0, 2);
            switch ($start) {
            case '%Y': case '%m': case '%d': case '%H': case '%M':
                $result[$formats[$start]['key']] = substr($date, 0, $formats[$start]['len']);
                $format = substr($format, 2);
                $date = substr($date, $formats[$start]['len']);
            default:
                $format = substr($format, 1);
                $date = substr($date, 1);
            }
        }
        if ($result['tm_mon'] < 1 || $result['tm_mon'] > 12
            || $result['tm_mday'] < 1 || $result['tm_mday'] > 31
            || $result['tm_hour'] < 0 || $result['tm_hour'] > 23
            || $result['tm_min'] < 0 || $result['tm_min'] > 59) {
            return false;
        }
        return $result;
    }
}

/*
 * Converts parsed time array to unix timestamp.
 * @param array // date parsed using strptime()
 * @return int  // Unix timestamp
 */
function ptimetotime($ptime) {
    return mktime(
        $ptime['tm_hour'],
        $ptime['tm_min'],
        $ptime['tm_sec'],
        1,
        $ptime['tm_yday'] + 1,
        $ptime['tm_year'] + 1900
    );
}

function editaccess_validate(Pieform $form, $values) {
    global $SESSION, $institution, $group;
    if ($institution && $values['copynewuser'] && !$values['template']) {
        $form->set_error('copynewuser', get_string('viewscopiedfornewusersmustbecopyable', 'view'));
    }
    $createforgroup = false;
    if ($institution == 'mahara') {
        foreach (group_get_grouptypes() as $grouptype) {
            if ($values['copyfornewgroups_'.$grouptype]) {
                $createforgroup = true;
                break;
            }
        }
        if ($createforgroup && !$values['template']) {
            $form->set_error('copyfornewgroups', get_string('viewscopiedfornewgroupsmustbecopyable', 'view'));
        }
    }
    if ($values['startdate'] && $values['stopdate'] && $values['startdate'] > $values['stopdate']) {
        $form->set_error('startdate', get_string('startdatemustbebeforestopdate', 'view'));
    }
    $loggedinaccess = false;
    if ($values['accesslist']) {
        $dateformat = get_string('strftimedatetimeshort');
        foreach ($values['accesslist'] as &$item) {
            if (empty($item['startdate'])) {
                $item['startdate'] = null;
            }
            else if (!$item['startdate'] = strptime($item['startdate'], $dateformat)) {
                $SESSION->add_error_msg(get_string('unrecogniseddateformat', 'view'));
                $form->set_error('accesslist', '');
                break;
            }
            if (empty($item['stopdate'])) {
                $item['stopdate'] = null;
            }
            else if (!$item['stopdate'] = strptime($item['stopdate'], $dateformat)) {
                $SESSION->add_error_msg(get_string('unrecogniseddateformat', 'view'));
                $form->set_error('accesslist', '');
                break;
            }
            if ($item['type'] == 'loggedin' && !$item['startdate'] && !$item['stopdate']) {
                $loggedinaccess = true;
            }
            $now = strptime(date('Y/m/d H:i'), $dateformat);
            if ($item['stopdate'] && ptimetotime($now) > ptimetotime($item['stopdate'])) {
                $SESSION->add_error_msg(get_string('stopdatecannotbeinpast', 'view'));
                $form->set_error('accesslist', '');
                break;
            }
            if ($item['startdate'] && $item['stopdate'] && ptimetotime($item['startdate']) > ptimetotime($item['stopdate'])) {
                $SESSION->add_error_msg(get_string('startdatemustbebeforestopdate', 'view'));
                $form->set_error('accesslist', '');
                break;
            }
        }
    }

    // Must have logged in user access for copy new user/group settings.
    if (($createforgroup || ($institution && $values['copynewuser'])) && !$loggedinaccess) {
        $SESSION->add_error_msg(get_string('copynewusergroupneedsloggedinaccess', 'view'));
        $form->set_error('accesslist', '');
    }
}

function editaccess_cancel_submit() {
    global $view, $new, $collection;
    if ($new) {
        if (!$collection) {
            $view->delete();
            $view->post_edit_redirect();
        }
        else {
            $collection->delete();
            $collection->post_edit_redirect();
        }
    }
    $collection ? $collection->post_edit_redirect() : $view->post_edit_redirect();
}


function editaccess_submit(Pieform $form, $values) {
    global $SESSION, $view, $new, $institution, $collection;

    if (param_boolean('back')) {
        if (!$collection) {
            redirect('/view/edit.php?id=' . $view->get('id') . '&new=' . $new);
        }
        else {
            redirect('/collection/views.php?id=' . $collection->get('id') . '&new=' . $new);
        }
    }

    if ($values['accesslist']) {
        $dateformat = get_string('strftimedatetimeshort');
        foreach ($values['accesslist'] as &$item) {
            if (!empty($item['startdate'])) {
                $item['startdate'] = ptimetotime(strptime($item['startdate'], $dateformat));
            }
            if (!empty($item['stopdate'])) {
                $item['stopdate'] = ptimetotime(strptime($item['stopdate'], $dateformat));
            }
        }
    }

    $view->set('startdate', $values['startdate']);
    $view->set('stopdate', $values['stopdate']);
    $istemplate = (int) $values['template'];
    $view->set('template', $istemplate);
    if (isset($values['copynewuser'])) {
        $view->set('copynewuser', (int) ($istemplate && $values['copynewuser']));
    }
    if ($institution == 'mahara') {
        $createfor = array();
        foreach (group_get_grouptypes() as $grouptype) {
            if ($istemplate && $values['copyfornewgroups_'.$grouptype]) {
                $createfor[] = $grouptype;
            }
        }
        $view->set('copynewgroups', $createfor);
    }

    $view->set('allowcomments', (int) $values['allowcomments']);
    if ($values['allowcomments']) {
        $view->set('approvecomments', (int) $values['approvecomments']);
    }

    db_begin();

    $view->commit();

    $view->set_access($values['accesslist']);

    if ($collection) {
        $collection->set_access($view->get('id'));
    }

    db_commit();

    if ($values['new']) {
        $str = $collection ? get_string('collectioncreatedsuccessfully','collection') : get_string('viewcreatedsuccessfully', 'view');
    }
    else {
        $str = $collection ? get_string('collectionaccesseditedsuccessfully','collection') : get_string('viewaccesseditedsuccessfully', 'view');
    }
    $SESSION->add_ok_msg($str);

    if (!$collection) {
        $view->post_edit_redirect();
    }
    else {
        $collection->post_edit_redirect();
    }
}

$form = pieform($form);

$smarty = smarty(
    array('tablerenderer'),
    array(),
    array(
        'mahara' => array('From', 'To', 'datetimeformatguide'),
        'artefact.comment' => array('Comments', 'Allow', 'Moderate')
    ),
    array('sidebars' => false)
);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('PAGEHEADING', TITLE);
if ($collection) {
    $views = $collection->views();
    if ($views['count'] > 1) {
        $smarty->assign('views', $views);
    }
}
$thing = $collection ? get_string('Collection', 'collection') : get_string('View', 'view');
$smarty->assign('pagedescriptionhtml', get_string('editaccessdescription', 'view', $thing, $thing));
$smarty->assign('form', $form);
$smarty->display('view/access.tpl');
