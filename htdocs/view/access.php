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
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'editaccess');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require_once('pieforms/pieform/elements/calendar.php');
require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'collection.php');
require_once(get_config('libroot') . 'group.php');

$collection = null;
if ($collectionid = param_integer('collection', null)) {
    $collection = new Collection($collectionid);
    $views = $collection->views();
    if (empty($views)) {
        $SESSION->add_error_msg(get_string('emptycollectionnoeditaccess', 'collection'));
        redirect('/collection/views.php?id=' . $collectionid);
    }
    // Pick any old view, they all have the same access records.
    $viewid = $views['views'][0]->view;
}
else {
    $viewid = param_integer('id');
}

$view = new View($viewid);

if (empty($collection)) {
    $collection = $view->get_collection();
}

define('TITLE', get_string('editaccess', 'view'));

$group = $view->get('group');
$institution = $view->get('institution');
View::set_nav($group, $institution, true);

if (!$USER->can_edit_view($view)) {
    throw new AccessDeniedException();
}
if ($group && !group_within_edit_window($group)) {
    throw new AccessDeniedException();
}


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
    )
);

// Create checkboxes to allow the user to apply these access rules to
// any of their views/collections.
// For institution views, force edit access of one view at a time for now.  Editing multiple
// institution views requires doing some tricky stuff with the 'copy for new users/groups'
// options, and there's not much room for the 'Share' tab in the admin area anyway
if ($view->get('type') != 'profile') {
    list($collections, $views) = View::get_views_and_collections(
        $view->get('owner'), $group, $institution, $view->get('accessconf'), false
    );
}

if (!empty($collections)) {
    foreach ($collections as &$c) {
        $c = array(
            'title'        => $c['name'],
            'value'        => $c['id'],
            'defaultvalue' => $collectionid == $c['id'] || !empty($c['match']),
            'views'        => $c['views'], // Keep these hanging around to check in submit function
        );
    }
    $form['elements']['collections'] = array(
        'type'         => 'checkboxes',
        'title'        => get_string('Collections', 'collection'),
        'elements'     => $collections,
    );
}

if (!empty($views)) {
    foreach ($views as &$v) {
        $v = array(
            'title'        => $v['name'],
            'value'        => $v['id'],
            'defaultvalue' => $viewid == $v['id'] || !empty($v['match']),
        );
    }
    $form['elements']['views'] = array(
        'type'         => 'checkboxes',
        'title'        => get_string('views'),
        'elements'     => $views,
    );
}

if ($view->get('type') == 'profile') {
    // Make sure all the user's institutions have access to profile view
    $view->add_owner_institution_access();

    if (get_config('loggedinprofileviewaccess')) {
        // Force logged-in user access
        $viewaccess = new stdClass;
        $viewaccess->accesstype = 'loggedin';
        $viewaccess->startdate = null;
        $viewaccess->stopdate = null;
        $viewaccess->allowcomments = 0;
        $viewaccess->approvecomments = 1;
        $view->add_access($viewaccess);
    }
}

$allowcomments = $view->get('allowcomments');

$form['elements']['accesslist'] = array(
    'type'          => 'viewacl',
    'allowcomments' => $allowcomments,
    'defaultvalue'  => $view->get_access(get_string('strftimedatetimeshort')),
    'viewtype'      => $view->get('type'),
);


$form['elements']['more'] = array(
    'type' => 'fieldset',
    'class' => $view->get('type') == 'profile' ? 'hidden' : '',
    'collapsible' => true,
    'collapsed' => true,
    'legend' => get_string('moreoptions', 'view'),
    'elements' => array(
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
            'description'  => get_string('templatedescriptionplural1', 'view'),
            'defaultvalue' => $view->get('template'),
        ),
    ),
);

$js = '';

if ($institution) {
    if ($institution == 'mahara') {
        $form['elements']['more']['elements']['copynewuser'] = array(
            'type'         => 'checkbox',
            'title'        => get_string('copyfornewusers', 'view'),
            'description'  => get_string('copyfornewusersdescription1', 'view'),
            'defaultvalue' => $view->get('template') && $view->get('copynewuser'),
        );
        $form['elements']['more']['elements']['copyfornewgroups'] = array(
            'type'         => 'html',
            'value'        => '<label>' . get_string('copyfornewgroups', 'view') . '</label>',
        );
        $form['elements']['more']['elements']['copyfornewgroupsdescription1'] = array(
            'type'         => 'html',
            'value'        => '<div class="description">' . get_string('copyfornewgroupsdescription1', 'view') . '</div>',
        );
        $copyoptions = array('copynewuser', 'copyfornewgroups', 'copyfornewgroupsdescription1');
        $needsaccess = array('copynewuser');
        $createfor = $view->get_autocreate_grouptypes();
        foreach (group_get_grouptype_options() as $grouptype => $grouptypedesc) {
            $form['elements']['more']['elements']['copyfornewgroups_'.$grouptype] = array(
                'type'         => 'checkbox',
                'title'        => $grouptypedesc,
                'defaultvalue' => $view->get('template') && in_array($grouptype, $createfor),
            );
            $copyoptions[] = 'copyfornewgroups_'.$grouptype;
            $needsaccess[] = 'copyfornewgroups_'.$grouptype;
        }
    }
    else {
        require_once('institution.php');
        $i = new Institution($institution);
        $instname = hsc($i->displayname);
        $form['elements']['more']['elements']['copynewuser'] = array(
            'type'         => 'checkbox',
            'title'        => get_string('copyfornewmembers', 'view'),
            'description'  => get_string('copyfornewmembersdescription1', 'view', $instname),
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
    connect('editaccess_template', 'onclick', update_copy_options);
    forEach({$needsaccessstr}, function (id) {
        connect('editaccess_'+id, 'onclick', update_loggedin_access);
    });
});
EOF;
} else {
    $form['elements']['more']['elements']['retainview'] = array(
        'type'         => 'checkbox',
        'title'        => get_string('retainviewrights1', 'view'),
        'description'  => $group ? get_string('retainviewrightsgroupdescription1', 'view') : get_string('retainviewrightsdescription1', 'view'),
        'defaultvalue' => $view->get('template') && $view->get('retainview'),
    );
    $js .= <<< EOF
function update_retainview() {
    if ($('editaccess_template').checked) {
        removeElementClass($('editaccess_retainview_container'), 'hidden');
    }
    else {
        addElementClass($('editaccess_retainview_container'), 'hidden');
        $('editaccess_retainview').checked = false;
        update_loggedin_access();
    }
};
addLoadEvent(function() {
    update_retainview();
    connect('editaccess_template', 'onclick', update_retainview);
});
EOF;
    $js .= "function update_loggedin_access() {}\n";
}

if (!$allowcomments) {
    $form['elements']['more']['elements']['approvecomments']['class'] = 'hidden';
}
$allowcomments = json_encode((int) $allowcomments);

$js .= <<<EOF
var allowcomments = {$allowcomments};
function update_comment_options() {
    allowcomments = $('editaccess_allowcomments').checked;
    if (allowcomments) {
        removeElementClass($('editaccess_approvecomments'), 'hidden');
        removeElementClass($('editaccess_approvecomments_container'), 'hidden');
        forEach(getElementsByTagAndClassName(null, 'comments', 'accesslisttable'), function (elem) {
            addElementClass(elem, 'hidden');
        });
    }
    else {
        addElementClass($('editaccess_approvecomments_container'), 'hidden');
        forEach(getElementsByTagAndClassName(null, 'comments', 'accesslisttable'), function (elem) {
            removeElementClass(elem, 'hidden');
        });
    }
}
addLoadEvent(function() {
    connect('editaccess_allowcomments', 'onclick', update_comment_options);
});
EOF;

$form['elements']['more']['elements']['overrides'] = array(
    'type' => 'html',
    'value' => '<strong>' . get_string('overridingstartstopdate', 'view') . '</strong>',
    'description' => get_string('overridingstartstopdatesdescription', 'view'),
);
$form['elements']['more']['elements']['startdate'] = array(
    'type'         => 'calendar',
    'title'        => get_string('startdate','view'),
    'description'  => get_string('datetimeformatguide'),
    'defaultvalue' => isset($view) ? strtotime($view->get('startdate')) : null,
    'caloptions'   => array(
        'showsTime'      => true,
        'ifFormat'       => get_string('strftimedatetimeshort'),
    ),
);
$form['elements']['more']['elements']['stopdate'] = array(
    'type'         => 'calendar',
    'title'        => get_string('stopdate','view'),
    'description'  => get_string('datetimeformatguide'),
    'defaultvalue' => isset($view) ? strtotime($view->get('stopdate')) : null,
    'caloptions'   => array(
        'showsTime'      => true,
        'ifFormat'       => get_string('strftimedatetimeshort'),
    ),
);

$form['elements']['submit'] = array(
    'type'  => 'submitcancel',
    'value' => array(get_string('save'), get_string('cancel')),
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
    $retainview = isset($values['retainview']) ? $values['retainview'] : false;
    if ($retainview && !$values['template']) {
        $form->set_error('retainview', get_string('viewswithretainviewrightsmustbecopyable', 'view'));
    }
    if ($values['startdate'] && $values['stopdate'] && $values['startdate'] > $values['stopdate']) {
        $form->set_error('startdate', get_string('newstartdatemustbebeforestopdate', 'view', 'Overriding'));
    }

    $accesstypestrings = array(
        'public'      => get_string('public', 'view'),
        'loggedin'    => get_string('loggedin', 'view'),
        'friends'     => get_string('friends', 'view'),
        'user'        => get_string('user', 'group'),
        'group'       => get_string('group', 'group'),
        'institution' => get_string('institution'),
    );

    $loggedinaccess = false;
    if ($values['accesslist']) {
        $dateformat = get_string('strftimedatetimeshort');
        foreach ($values['accesslist'] as &$item) {
            if (empty($item['startdate'])) {
                $item['startdate'] = null;
            }
            else if (!$item['startdate'] = strptime($item['startdate'], $dateformat)) {
                $SESSION->add_error_msg(get_string('datetimeformatguide'));
                $form->set_error('accesslist', '');
                break;
            }
            if (empty($item['stopdate'])) {
                $item['stopdate'] = null;
            }
            else if (!$item['stopdate'] = strptime($item['stopdate'], $dateformat)) {
                $SESSION->add_error_msg(get_string('datetimeformatguide'));
                $form->set_error('accesslist', '');
                break;
            }
            if ($item['type'] == 'loggedin' && !$item['startdate'] && !$item['stopdate']) {
                $loggedinaccess = true;
            }
            $now = strptime(date('Y/m/d H:i'), $dateformat);
            if ($item['stopdate'] && ptimetotime($now) > ptimetotime($item['stopdate'])) {
                $SESSION->add_error_msg(get_string('newstopdatecannotbeinpast', 'view', $accesstypestrings[$item['type']]));
                $form->set_error('accesslist', '');
                break;
            }
            if ($item['startdate'] && $item['stopdate'] && ptimetotime($item['startdate']) > ptimetotime($item['stopdate'])) {
                $SESSION->add_error_msg(get_string('newstartdatemustbebeforestopdate', 'view', $accesstypestrings[$item['type']]));
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

if (!empty($institution)) {
    if ($institution == 'mahara') {
        $shareurl = 'admin/site/shareviews.php';
    }
    else {
        $shareurl = 'view/institutionshare.php';
    }
}
else if (!empty($group)) {
    $shareurl = 'group/shareviews.php?group=' . $group;
}
else {
    $shareurl = 'view/share.php';
}
$shareurl = get_config('wwwroot') . $shareurl;

function editaccess_cancel_submit() {
    global $shareurl;
    redirect($shareurl);
}

function editaccess_submit(Pieform $form, $values) {
    global $SESSION, $institution, $collections, $views, $view;

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

    $viewconfig = array(
        'startdate'       => $values['startdate'],
        'stopdate'        => $values['stopdate'],
        'template'        => (int) $values['template'],
        'retainview'      => isset($values['retainview']) ? (int) $values['retainview'] : 0,
        'allowcomments'   => (int) $values['allowcomments'],
        'approvecomments' => (int) ($values['allowcomments'] && $values['approvecomments']),
        'accesslist'      => $values['accesslist'],
    );

    $toupdate = array();

    if ($institution) {
        if (isset($values['copynewuser'])) {
            $viewconfig['copynewuser'] = (int) ($values['template'] && $values['copynewuser']);
        }
        if ($institution == 'mahara') {
            $createfor = array();
            foreach (group_get_grouptypes() as $grouptype) {
                if ($values['template'] && $values['copyfornewgroups_'.$grouptype]) {
                    $createfor[] = $grouptype;
                }
            }
            $viewconfig['copynewgroups'] = $createfor;
        }
    }
    if (isset($values['collections'])) {
        foreach ($values['collections'] as $cid) {
            if (!isset($collections[$cid])) {
                throw new UserException(get_string('editaccessinvalidviewset', 'view'));
            }
            $toupdate = array_merge($toupdate, array_keys($collections[$cid]['views']));
        }
    }

    if (isset($values['views'])) {
        foreach ($values['views'] as $viewid) {
            if (!isset($views[$viewid])) {
                throw new UserException(get_string('editaccessinvalidviewset', 'view'));
            }
            $toupdate[] = $viewid;
        }
    }
    else if ($view->get('type') == 'profile') {
        // Force default Advanced options
        $felements = $form->get_property('elements');
        if (!empty($felements['more']['elements'])) {
            foreach (array_keys($felements['more']['elements']) as $ename) {
                if (property_exists($view, $ename)) {
                    $viewconfig[$ename] = $view->get($ename);
                }
            }
        }

        $toupdate[] = $view->get('id');
    }

    if (!empty($toupdate)) {
        View::update_view_access($viewconfig, $toupdate);

        if ($view->get('type') == 'profile') {
            // Ensure the user's institutions are still added to the access list
            $view->add_owner_institution_access();

            if (get_config('loggedinprofileviewaccess')) {
                // Force logged-in user access
                $viewaccess = new stdClass;
                $viewaccess->accesstype = 'loggedin';
                $view->add_access($viewaccess);
            }
        }
    }

    $SESSION->add_ok_msg(get_string('updatedaccessfornumviews', 'view', count($toupdate)));

    if ($view->get('owner')) {
        redirect('/view/share.php');
    }
    if ($view->get('group')) {
        redirect(get_config('wwwroot') . '/group/shareviews.php?group=' . $view->get('group'));
    }
    if ($view->get('institution')) {
        redirect(get_config('wwwroot') . '/view/institutionshare.php?institution=' . $view->get('institution'));
    }
    $view->post_edit_redirect();
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
$smarty->assign('form', $form);
$smarty->assign('shareurl', $shareurl);
$smarty->assign('group', $group);
$smarty->assign('institution', $institution);
$smarty->display('view/access.tpl');
