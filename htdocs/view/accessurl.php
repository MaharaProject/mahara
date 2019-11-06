<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Antonella De Chiara - Eticeo Santé
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'accessurl');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform/elements/calendar.php');
require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'collection.php');
require_once(get_config('libroot') . 'antispam.php');
require_once(get_config('libroot') . 'group.php');

$collection = null;
if ($collectionid = param_integer('collection', null)) {
    $collection = new Collection($collectionid);
    $views = $collection->views();
    if (empty($views)) {
        $SESSION->add_error_msg(get_string('emptycollectionnoaccessurl', 'collection'));
        redirect('/collection/views.php?id=' . $collectionid);
    }
    // Pick any old view, they all have the same access records.
    $viewid = $views['views'][0]->view;
}
else {
    $viewid = param_integer('id');
}

$view = new View($viewid);

define('VIEWTITLE', $view->get('title'));
define('SUBSECTIONHEADING', VIEWTITLE);

if (empty($collection)) {
    $collection = $view->get_collection();
}

$in_editor = param_integer('editor', 0);
$viewtitle = $in_editor ? $view->get('title') : get_string('editaccess', 'view');
define('TITLE', $viewtitle);

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
    'name' => 'accessurl',
    'renderer' => 'div',
    'class' => 'form-simple stacked block-relative',
    'plugintype' => 'core',
    'pluginname' => 'view',
    'presubmitcallback' => 'formStartProcessing',
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
    'class' => $view->get('type') == 'profile' ? ' d-none' : 'last with-heading',
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
if ($group && in_array($USER->get('id'), $admintutorids, true)) {
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
}
else {
    $form['elements']['more']['elements']['retainview'] = array(
        'type'         => 'switchbox',
        'title'        => get_string('retainviewrights1', 'view'),
        'description'  => $group ? get_string('retainviewrightsgroupdescription2', 'view') : get_string('retainviewrightsdescription2', 'view'),
        'defaultvalue' => $view->get('template') && $view->get('retainview'),
    );
    $js .= <<< EOF
jQuery(function($) {
    function update_retainview() {
        if ($('#accessurl_template').prop('checked')) {
            $('#accessurl_retainview_container').removeClass('d-none');
        }
        else {
            $('#accessurl_retainview_container').addClass('d-none');
            $('#accessurl_retainview').prop('checked',false);
            update_loggedin_access();
        }
    };
    update_retainview();

    $('#accessurl_template').on('click', update_retainview);
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
        allowcomments = $('#accessurl_allowcomments').prop('checked');
        if (allowcomments) {
            $('#accessurl_approvecomments').removeClass('d-none');
            $('#accessurl_approvecomments_container').removeClass('d-none');
            $('#accesslisttable .commentcolumn').each(function () {
                $(this).addClass('d-none');
            });
        }
        else {

            $('#accessurl_approvecomments_container').addClass('d-none');
            $('#accesslisttable .commentcolumn').each(function () {
                $(this).removeClass('d-none');
            });
        }
    }
    $('#accessurl_allowcomments').on('click', update_comment_options);
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

function accessurl_validate(Pieform $form, $values) {
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

            if ($item['type'] == 'loggedin' && !$item['startdate'] && !$item['stopdate']) {
                $loggedinaccess = true;
            }

            $now = time();
            if (!empty($item['stopdate']) && $item['stopdate'] && $now > $item['stopdate']) {
                $SESSION->add_error_msg(get_string('newstopdatecannotbeinpast', 'view', $accesstypestrings[$item['type']]));
                $form->set_error('accesslist', '');
                break;
            }
            if (!empty($item['startdate']) && !empty($item['stopdate']) && $item['startdate'] && $item['stopdate'] && $item['startdate'] > $item['stopdate']) {
                $SESSION->add_error_msg(get_string('newstartdatemustbebeforestopdate', 'view', $accesstypestrings[$item['type']]));
                $form->set_error('accesslist', '');
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
        $shareurl = 'view/institutionviews.php';
    }
}
else if (!empty($group)) {
    $shareurl = 'view/groupviews.php?group=' . $group;
}
else {
    $shareurl = 'view/index.php';
}
$shareurl = get_config('wwwroot') . $shareurl;

function accessurl_cancel_submit() {
    global $shareurl;
    redirect($shareurl);
}

function accessurl_submit(Pieform $form, $values) {
    global $SESSION, $institution, $view, $group, $collection;

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

    if ($group) {
        $viewconfig['existinggroupmembercopy'] = !empty($values['existinggroupmembercopy']) ? $values['existinggroupmembercopy'] : 0;

        // Add functionality here which copies the page into existing
        // group members pages.
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
                if (!empty($collection)) {
                    $userobj->copy_group_views_collections_to_existing_members(array($collection->get('id')), true);
                }
                else if (!empty($view->get('id'))) {
                    $userobj->copy_group_views_collections_to_existing_members(array($view->get('id')));
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

    $toupdate = array();
    if ($collection = $view->get_collection()) {
      $views = isset($collection->views()['views']) ? $collection->views()['views'] : null;
      foreach ($views as $v) {
          $toupdate[] = $v->view;
      }
    }
    else {
        $toupdate[] = $view->get('id');
        if ($view->get('type') == 'profile') {
            // Force default Advanced options
            $felements = $form->get_property('elements');
            if (!empty($felements['more']['elements'])) {
                foreach (array_keys($felements['more']['elements']) as $ename) {
                    if (property_exists($view, $ename)) {
                        $viewconfig[$ename] = $view->get($ename);
                    }
                }
            }
        }
    }

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
    set_progress_done('copyviewexistingmembersprogress');
    if ($view->get('owner')) {
        redirect(get_config('wwwroot') . 'view/blocks.php?id=' . $view->get('id'));
    }
    if ($view->get('group')) {
        redirect(get_config('wwwroot') . 'group/shareviews.php?group=' . $view->get('group'));
    }
    if ($view->get('institution')) {
        redirect(get_config('wwwroot') . 'view/institutionshare.php?institution=' . $view->get('institution'));
    }
}

$form = pieform($form);

// Antox code
$displaylink = $view->get_url();
// End

// URLS
$newform = array(
    'name'     => 'newurl',
    'autofocus'     => false,
    'elements' => array(
        'submit' => array(
            'type'        => 'button',
            'usebuttontag' => true,
            'class'       => 'btn-secondary',
            'elementtitle' => get_string('generatesecreturl', 'view', hsc(isset($title) ? $title : '')),
            'value'       =>  '<span class="icon icon-plus icon-lg left" role="presentation" aria-hidden="true"></span> ' .get_string('newsecreturl', 'view'),
        ),
    ),
);

$editurls = array();

$allowcomments = $view->get('allowcomments');

$records = get_records_select_array(
    'view_access',
    'view = ? AND visible = 1 AND NOT token IS NULL',
    array($view->get('id')),
    'token'
);

if (!$records) {
    $records = array();
}

$tokens = array();

for ($i = 0; $i < count($records); $i++) {
    $r =& $records[$i];
    $tokens[$r->token] = $r->token;
    $elements = array(
        'token'     => array(
            'type'         => 'hidden',
            'value'        => $r->token,
        ),
        'startdate' => array(
            'type'         => 'calendar',
            'title'        => get_string('From') . ':',
            'class'        => 'form-inline',
            'defaultvalue' => !empty($r->startdate) ? strtotime($r->startdate) : null,
            'caloptions'   => array(
                'showsTime'      => true,
            ),
        ),
        'stopdate'  => array(
            'type'         => 'calendar',
            'title'        => get_string('To') . ':',
            'class'        => 'form-inline',
            'defaultvalue' => !empty($r->stopdate) ? strtotime($r->stopdate) : null,
            'caloptions'   => array(
                'showsTime'      => true,
            ),
        ),
    );
    if (!$allowcomments) {
        $elements['allowcomments'] = array(
            'type'         => 'switchbox',
            'title'        => get_string('allowcomments', 'artefact.comment'),
            'defaultvalue' => $r->allowcomments,
        );
        $elements['approvecomments'] = array(
            'type'         => 'switchbox',
            'title'        => get_string('moderatecomments', 'artefact.comment'),
            'defaultvalue' => $r->approvecomments,
        );
    }
    $elements['submit'] = array(
        'type'  => 'submit',
        'class' => 'btn-primary',
        'value' => get_string('save'),
    );
    $editurls[$i] = array(
        'id'         => $i,
        'url'        => get_config('wwwroot') . 'view/view.php?t=' . $r->token,
        'editform'   => pieform(array(
            'name'             => 'editurl_' . $i,
            'successcallback'  => 'editurl_submit',
            'validatecallback' => 'editurl_validate',
            'jsform'           => true,
            'elements'         => $elements,
        )),
        'deleteform' => pieform(array(
            'name'             => 'deleteurl_' . $i,
            'successcallback'  => 'deleteurl_submit',
            'renderer'         => 'div',
            'class'            => 'form-as-button btn-group form-inline float-left',
            'renderelementsonly' => true,
            'elements'         => array(
                'token'  => array(
                    'type'         => 'hidden',
                    'value'        => $r->token,
                ),
                'submit' => array(
                    'type'         => 'button',
                    'usebuttontag' => true,
                    'class'        => 'btn-secondary btn-sm',
                    'elementtitle' => get_string('delete'),
                    'confirm'      => get_string('reallydeletesecreturl', 'view'),
                    'value'        => '<span class="icon icon-trash text-danger icon-lg" role="presentation" aria-hidden="true"></span><span class="sr-only">' . get_string('delete') . '</span>',
                ),
            ),
        )),
    );
}

// Only add the call if there is any zclip setup to be done.
$count = count($records);
if ($count) {
    $js .= <<<EOF
jQuery(function($) {
      $(function() {
            for (i = 0; i < {$count}; i++) {
                var element = document.getElementById("copytoclipboard-" + i);
                try {
                    var client = new ClipboardJS(element);
                    client.on("error", function(e) {
                        var element = document.getElementById("copytoclipboard-" + e.client.id);
                        $(element).hide();
                    });
                }
                catch(err) {
                    $(element).hide();
                }
            }
    });
});

EOF;
}

function editurl_validate(Pieform $form, $values) {
    if (empty($values['startdate'])) {
        $values['startdate'] = null;
    }
    if (empty($values['stopdate'])) {
        $values['stopdate'] = null;
    }
    if ($values['stopdate'] && time() > $values['stopdate']) {
        $form->set_error('stopdate', get_string('stopdatecannotbeinpast1', 'view'));
    }
    if ($values['startdate'] && $values['stopdate'] && $values['startdate'] > $values['stopdate']) {
        $form->set_error('startdate', get_string('startdatemustbebeforestopdate', 'view'));
    }
}

function editurl_submit(Pieform $form, $values) {
    global $tokens, $view, $collection, $SESSION;

    $viewid = $view->get('id');

    if ($collection) {
        $viewids = get_column('collection_view', 'view', 'collection', $collection->get('id'));
    }
    else {
        $viewids = array($viewid);
    }

    $access = (object) array(
        'token'     => $values['token'],
        'startdate' => db_format_timestamp($values['startdate']),
        'stopdate'  => db_format_timestamp($values['stopdate']),
    );
    if (!$view->get('allowcomments')) {
        if ($access->allowcomments = (int) $values['allowcomments']) {
            $access->approvecomments = (int) $values['approvecomments'];
        }
    }

    $whereobject = (object) array('token' => $values['token']);

    if (isset($tokens[$values['token']])) {
        foreach ($viewids as $id) {
            $access->view = $id;
            $whereobject->view = $id;
            $vaid = update_record('view_access', $access, $whereobject, 'id', true);
            handle_event('updateviewaccess', array(
                'id' => $vaid,
                'eventfor' => 'token',
                'parentid' => $id,
                'parenttype' => 'view',
                'rules' => $access)
            );
        }

        $message = get_string('secreturlupdated', 'view');
        $form->reply(PIEFORM_OK, $message);
    }

    $form->reply(PIEFORM_ERR, get_string('formerror'));
}

function deleteurl_submit(Pieform $form, $values) {
    global $tokens, $view, $collection, $SESSION;

    $viewid = $view->get('id');

    if ($collection) {
        $viewids = get_column('collection_view', 'view', 'collection', $collection->get('id'));
    }
    else {
        $viewids = array($viewid);
    }

    if (isset($tokens[$values['token']])) {
        $select = 'token = ? AND view IN (' . join(',', $viewids) . ')';
        delete_records_select('view_access', $select, array($values['token']));
        $SESSION->add_ok_msg(get_string('secreturldeleted', 'view'));
    }

    redirect('/view/accessurl.php?id=' . $viewid);
}

function newurl_submit(Pieform $form, $values) {
    global $view, $collection;

    $viewid = $view->get('id');

    if ($collection) {
        $collection->new_token();
        $views = $collection->get_viewids();
        $viewid = reset($views);
    }
    else {
        View::new_token($viewid);
    }

    redirect('/view/accessurl.php?id=' . $viewid);
}

// Determine whether
$allownew = get_config('allowpublicviews') // Public view turned off sitewide
            && (!$view->get('owner') || $USER->institution_allows_public_views()); // The page belongs to a user in an institution without public views

// If the user would be allowed to create new views, check whether they should be prohibited because they're on probation
if ($allownew) {
    $onprobation = get_config('allowpublicviews') && is_probationary_user();
    $allownew = !$onprobation;
}
else {
    $onprobation = false;
}
$newform = $allownew ? pieform($newform) : null;

$js .= <<<EOF
jQuery(function($) {
    $('.url-open-editform').on("click", function(e) {
        e.preventDefault();
        $('#' + this.id).addClass('collapse-indicator');
        $('#' + this.id).toggleClass('open');
        $('#' + this.id).toggleClass('closed');
        $('#' + this.id + '-form').toggleClass('js-hidden');
    });
});
EOF;

$smarty = smarty(
    array('js/clipboard/clipboard.min.js'),
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
$smarty->assign('collectionid', $collectionid);
$smarty-> assign('collectiontitle', ($collection ? $collection->get('name') : null));
// Antox code
$smarty->assign('editurls', $editurls);
$smarty->assign('viewid', $view->get('id'));
$smarty->assign('edittitle', $view->can_edit_title());
$smarty->assign('displaylink', $displaylink);
$smarty->assign('allownew', $allownew);
$smarty->assign('onprobation', $onprobation);
$smarty->assign('newform', $newform);
// end
$smarty->display('view/accessurl.tpl');
