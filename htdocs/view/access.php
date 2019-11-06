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
define('SECTION_PAGE', 'editaccess');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform/elements/calendar.php');
require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'collection.php');
require_once(get_config('libroot') . 'group.php');
define('SUBSECTIONHEADING', get_string('share'));
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
if ($view->get('template') == View::SITE_TEMPLATE) {
    throw new AccessDeniedException();
}


$form = array(
    'name' => 'editaccess',
    'renderer' => 'div',
    'class' => 'form-simple stacked block-relative',
    'plugintype' => 'core',
    'pluginname' => 'view',
    'viewid' => $view->get('id'),
    'userview' => (int) $view->get('owner'),
    'elements' => array(
        'id' => array(
            'type' => 'hidden',
            'value' => $view->get('id'),
        ),
        'progress_meter_token' => array(
            'type' => 'hidden',
            'value' => 'copyviewexistingmembersprogress',
            'readonly' => TRUE,
        ),
    )
);

// Create select options to allow the user to apply these access rules to
// any of their views/collections.
// For institution views, force edit access of one view at a time for now.  Editing multiple
// institution views requires doing some tricky stuff with the 'copy for new users/groups'
// options, and there's not much room for the 'Share' tab in the admin area anyway
if ($view->get('type') != 'profile') {
    list($collections, $views) = View::get_views_and_collections(
        $view->get('owner'), $group, $institution, false, false
    );

    if ($institution === 'mahara') {
        // Remove site templates from the list
        foreach ($views as $k => $v) {
            if ((int)$v['template'] === View::SITE_TEMPLATE) {
                unset($views[$k]);
            }
        }
    }
}

if (!empty($collections) || !empty($views)) {
    $form['elements']['subjectgroup'] = array(
        'type' => 'fieldset',
        'class' => 'with-heading',
        'renderelementsonly' => true
    );
}

if (!empty($collections)) {
    $defaultvalues = array();
    $data = array();
    foreach ($collections as &$c) {
        $data[$c['id']] = $c['name'];
        if ($collectionid == $c['id'] || !empty($c['match'])) {
            $defaultvalues[$c['id']] = $c['id'];
        }
    }

    $form['elements']['subjectgroup']['elements']['collections'] = array(
        'type'         => 'select',
        'title'        => get_string('Collections', 'collection'),
        'class'     =>  'js-select2 text-inline input-pair',
        'isSelect2' => true,
        'multiple' => true,
        'options' => $data,
        'defaultvalue' => $defaultvalues,
        'defaultvaluereadonly' => true,
        'collapseifoneoption' => false,
    );
}

if (!empty($views)) {
    $defaultvalues = array();
    $data = array();
    foreach ($views as &$v) {
        $data[$v['id']] =  $v['name'];

        if ($viewid == $v['id'] || !empty($v['match'])) {
            $defaultvalues[$v['id']] = $v['id'];
        }
    }

    $form['elements']['subjectgroup']['elements']['views'] = array(
        'type'         => 'select',
        'title'        => get_string('views'),
        'class'     =>  'js-select2 text-inline input-pair',
        'isSelect2' => true,
        'multiple' => true,
        'options' => $data,
        'defaultvalue' => $defaultvalues,
        'defaultvaluereadonly' => true,
        'collapseifoneoption' => false,
    );
}

if ($view->get('type') == 'profile') {
    // Make sure all the user's institutions have access to profile view
    $view->add_owner_institution_access();

    if (get_config('loggedinprofileviewaccess') && !is_isolated()) {
        // Force logged-in user access
        $viewaccess = new stdClass();
        $viewaccess->accesstype = 'loggedin';
        $viewaccess->startdate = null;
        $viewaccess->stopdate = null;
        $viewaccess->allowcomments = 0;
        $viewaccess->approvecomments = 1;
        $view->add_access($viewaccess);
    }
}

$allowcomments = $view->get('allowcomments');

$form['elements']['more'] = array(
    'type' => 'fieldset',
    'class' => $view->get('type') == 'profile' ? ' d-none' : 'last form-condensed as-link link-expand-right with-heading',
    'collapsible' => true,
    'collapsed' => true,
    'legend' => get_string('moreoptions', 'view'),
    'elements' => array(
        'allowcomments' => array(
            'type'         => 'switchbox',
            'title'        => get_string('allowcomments','artefact.comment'),
            'description'  => get_string('allowcommentsonview1','view'),
            'defaultvalue' => $view->get('allowcomments'),
        ),
        'approvecomments' => array(
            'type'         => 'switchbox',
            'title'        => get_string('moderatecomments', 'artefact.comment'),
            'description'  => get_string('moderatecommentsdescription2', 'artefact.comment'),
            'defaultvalue' => $view->get('approvecomments'),
        ),
        'template' => array(
            'type'         => 'switchbox',
            'title'        => get_string('allowcopying', 'view'),
            'description'  => get_string('templatedescriptionplural2', 'view'),
            'defaultvalue' => $view->get('template'),
        ),
    ),
);

$admintutorids = group_get_member_ids($group, array('admin', 'tutor'));
if ($group && in_array( $USER->get('id'), $admintutorids, true )) {
    $form['elements']['more']['elements'] = array_merge($form['elements']['more']['elements'], array('existinggroupmembercopy' => array(
            'type'         => 'switchbox',
            'title'        => get_string('existinggroupmembercopy', 'view'),
            'description'  => get_string('existinggroupmembercopydesc1', 'view'),
            'defaultvalue' => 0,
    )));
}
$viewaccess = $view->get_access('%s');
if (is_isolated() && !empty($viewaccess)) {
    $viewaccess = filter_isolated_view_access($view, $viewaccess);
}

$form['elements']['accesslist'] = array(
    'type'          => 'viewacl',
    'allowcomments' => $allowcomments,
    'defaultvalue'  => $viewaccess,
    'viewtype'      => $view->get('type'),
    'isformgroup' => false
);

$js = '';

if ($institution) {
    if ($institution == 'mahara') {
        $form['elements']['more']['elements']['copynewuser'] = array(
            'type'         => 'switchbox',
            'title'        => get_string('copyfornewusers', 'view'),
            'description'  => get_string('copyfornewusersdescription2', 'view'),
            'defaultvalue' => $view->get('copynewuser'),
        );
        $form['elements']['more']['elements']['copyfornewgroups'] = array(
            'type'         => 'html',
            'value'        => '<strong>' . get_string('copyfornewgroups', 'view') . '</strong>',
        );
        $form['elements']['more']['elements']['copyfornewgroupsdescription1'] = array(
            'type'         => 'html',
            'value'        => '<div class="description">' . get_string('copyfornewgroupsdescription1', 'view') . '</div>',
        );
        $createfor = $view->get_autocreate_grouptypes();
        foreach (group_get_grouptype_options() as $grouptype => $grouptypedesc) {
            $form['elements']['more']['elements']['copyfornewgroups_'.$grouptype] = array(
                'type'         => 'switchbox',
                'title'        => $grouptypedesc,
                'defaultvalue' => in_array($grouptype, $createfor),
            );
        }
    }
    else {
        require_once('institution.php');
        $i = new Institution($institution);
        $instname = hsc($i->displayname);
        $form['elements']['more']['elements']['copynewuser'] = array(
            'type'         => 'switchbox',
            'title'        => get_string('copyfornewmembers', 'view'),
            'description'  => get_string('copyfornewmembersdescription2', 'view', $instname),
            'defaultvalue' => $view->get('copynewuser'),
        );
    }
} else {
    $form['elements']['more']['elements']['retainview'] = array(
        'type'         => 'switchbox',
        'title'        => get_string('retainviewrights1', 'view'),
        'description'  => $group ? get_string('retainviewrightsgroupdescription2', 'view') : get_string('retainviewrightsdescription2', 'view'),
        'defaultvalue' => $view->get('template') && $view->get('retainview'),
    );
    $js .= <<< EOF
jQuery(function($) {
    function update_retainview() {
        if ($('#editaccess_template').prop('checked')) {
            $('#editaccess_retainview_container').removeClass('d-none');
        }
        else {
            $('#editaccess_retainview_container').addClass('d-none');
            $('#editaccess_retainview').prop('checked',false);
            update_loggedin_access();
        }
    };
    update_retainview();

    $('#editaccess_template').on('click', update_retainview);
});
EOF;
    $js .= "function update_loggedin_access() {}\n";
}

if (!$allowcomments) {
    $form['elements']['more']['elements']['approvecomments']['class'] = 'd-none';
}
$allowcomments = json_encode((int) $allowcomments);

$js .= <<<EOF
jQuery(function($) {
    var allowcomments = {$allowcomments};
    function update_comment_options() {
        allowcomments = $('#editaccess_allowcomments').prop('checked');
        if (allowcomments) {
            $('#editaccess_approvecomments').removeClass('d-none');
            $('#editaccess_approvecomments_container').removeClass('d-none');
            $('#accesslisttable .commentcolumn').each(function () {
                $(this).addClass('d-none');
            });
        }
        else {

            $('#editaccess_approvecomments_container').addClass('d-none');
            $('#accesslisttable .commentcolumn').each(function () {
                $(this).removeClass('d-none');
            });
        }
    }
    $('#editaccess_allowcomments').on('click', update_comment_options);
    update_comment_options();
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
    'description'  => get_string('datetimeformatguide1', 'mahara', pieform_element_calendar_human_readable_datetimeformat()),
    'defaultvalue' => isset($view) ? strtotime($view->get('startdate')) : null,
    'caloptions'   => array(
        'showsTime'      => true,
    ),
);
$form['elements']['more']['elements']['stopdate'] = array(
    'type'         => 'calendar',
    'title'        => get_string('stopdate','view'),
    'description'  => get_string('datetimeformatguide1', 'mahara', pieform_element_calendar_human_readable_datetimeformat()),
    'defaultvalue' => isset($view) ? strtotime($view->get('stopdate')) : null,
    'caloptions'   => array(
        'showsTime'      => true,
    ),
);

$form['elements']['submit'] = array(
    'type'  => 'submitcancel',
    'class' => 'btn-primary',
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
        $ptime['tm_mon'] + 1,
        $ptime['tm_mday'],
        $ptime['tm_year'] + 1900
    );
}

function editaccess_validate(Pieform $form, $values) {
    global $SESSION, $institution, $group;

    $retainview = isset($values['retainview']) ? $values['retainview'] : false;
    if ($retainview && !$values['template']) {
        $form->set_error('retainview', get_string('viewswithretainviewrightsmustbecopyable', 'view'));
    }
    if ($values['startdate'] && $values['stopdate'] && $values['startdate'] > $values['stopdate']) {
        $form->set_error('startdate', get_string('newstartdatemustbebeforestopdate', 'view', 'Overriding'));
    }

    $accesstypestrings = array(
        'public'      => get_string('public', 'view'),
        'loggedin'    => get_string('registeredusers', 'view'),
        'friends'     => get_string('friends', 'view'),
        'user'        => get_string('user', 'group'),
        'group'       => get_string('group', 'group'),
        'institution' => get_string('institution'),
    );

    $loggedinaccess = false;
    if ($values['accesslist']) {
        $dateformat = get_string('strftimedatetimeshort');
        foreach ($values['accesslist'] as &$item) {

            if (isset($item['startdate']) && $item['startdate'] === false) {
                $SESSION->add_error_msg(get_string('datetimeformatguide1', 'mahara', pieform_element_calendar_human_readable_dateformat()));
                $form->set_error('accesslist', '');
                break;
            }

            if (isset($item['stopdate']) && $item['stopdate'] === false) {
                $SESSION->add_error_msg(get_string('datetimeformatguide1', 'mahara', pieform_element_calendar_human_readable_dateformat()));
                $form->set_error('accesslist', '');
                break;
            }

            if ($item['type'] == 'loggedin' && empty($item['startdate']) && empty($item['stopdate'])) {
                $loggedinaccess = true;
            }

            $now = time();
            if (!empty($item['stopdate']) && $now > $item['stopdate']) {
                $SESSION->add_error_msg(get_string('newstopdatecannotbeinpast', 'view', $accesstypestrings[$item['type']]));
                $form->set_error('accesslist', '');
                break;
            }
            if (!empty($item['startdate']) && !empty($item['stopdate']) && $item['startdate'] > $item['stopdate']) {
                $SESSION->add_error_msg(get_string('newstartdatemustbebeforestopdate', 'view', $accesstypestrings[$item['type']]));
                $form->set_error('accesslist', '');
                break;
            }
            // $values['startdate'] and $values['stopdate'] from override
            // check if there is a conflict
            if ((!empty($item['startdate']) && !empty($values['startdate']) && $item['startdate'] < $values['startdate'])
                ||
                (!empty($item['stopdate']) && !empty($values['stopdate']) && $values['stopdate'] < $item['stopdate'])
                ||
                (!empty($item['stopdate']) && !empty($values['startdate']) && $item['stopdate'] < $values['startdate'])
                ||
                (!empty($item['startdate']) && !empty($values['stopdate']) && $values['stopdate'] < $item['startdate'])
            ) {
                $SESSION->add_error_msg(get_string('overrideconflict', 'view', $accesstypestrings[$item['type']]));
                break;
            }
        }
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
    global $SESSION, $institution, $collections, $views, $view, $group;

    if ($values['accesslist']) {
        $dateformat = get_string('strftimedatetimeshort');

        foreach ($values['accesslist'] as $i => $value) {
            if (empty($values['accesslist'][$i]['type'])) {
                unset($values['accesslist'][$i]);
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

    if ($group) {
        $viewconfig['existinggroupmembercopy'] = !empty($values['existinggroupmembercopy']) ? $values['existinggroupmembercopy'] : 0;

        // Add funtionality here which copies the page into existing group members pages.
        if ($viewconfig['existinggroupmembercopy']) {
            $groupmembers = group_get_member_ids($group, array('member'));
            $key = 0;
            $total = count($groupmembers);
            foreach ($groupmembers as $groupmember) {
                if (!($key % 5)) {
                    set_progress_info('copyviewexistingmembersprogress', $key, $total, get_string('copyforexistingmembersprogress', 'view'));
                }
                $key++;

                $userobj = new User();
                $userobj->find_by_id($groupmember);
                if (!empty($values['collections'])) {
                    $userobj->copy_group_views_collections_to_existing_members($values['collections'], true);
                }
                if (!empty($values['views'])) {
                    $userobj->copy_group_views_collections_to_existing_members($values['views']);
                }
            }
        }
    }

    if ($institution) {
        if (isset($values['copynewuser'])) {
            $viewconfig['copynewuser'] = (int) $values['copynewuser'];
        }
        if ($institution == 'mahara') {
            $createfor = array();
            foreach (group_get_grouptypes() as $grouptype) {
                if ($values['copyfornewgroups_'.$grouptype]) {
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

            if (get_config('loggedinprofileviewaccess') && !is_isolated()) {
                // Force logged-in user access
                $viewaccess = new stdClass();
                $viewaccess->accesstype = 'loggedin';
                $view->add_access($viewaccess);
            }
        }
    }

    $SESSION->add_ok_msg(get_string('updatedaccessfornumviews1', 'view', count($toupdate)));
    set_progress_done('copyviewexistingmembersprogress');
    if ($view->get('owner')) {
        redirect('/view/share.php');
    }
    if ($view->get('group')) {
        redirect(get_config('wwwroot') . '/group/shareviews.php?group=' . $view->get('group'));
    }
    if ($view->get('institution')) {
        redirect(get_config('wwwroot') . '/view/institutionshare.php?institution=' . $view->get('institution'));
    }
}

$form = pieform($form);

$smarty = smarty(
    array(),
    array(),
    array(
        'mahara' => array('From', 'To'),
        'view' => array('startdate', 'stopdate', 'addaccess', 'addaccessinstitution', 'addaccessgroup'),
        'artefact.comment' => array('Comments', 'Allow', 'Moderate')
    ),
    array('sidebars' => false)
);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('form', $form);
$smarty->assign('shareurl', $shareurl);
$smarty->assign('group', $group);
$smarty->assign('institution', $institution);
$smarty->display('view/access.tpl');
