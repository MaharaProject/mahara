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

define('PUBLIC', 1);
define('INTERNAL', 1);
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'collection');
define('SECTION_PAGE', 'progress');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once('collection.php');
require_once(get_config('libroot') . 'objectionable.php');

$collectionid = param_integer('id');

$collection = new Collection($collectionid);

$javascript = array(
    'js/collection-navigation.js',
    'js/jquery/jquery-mobile/jquery.mobile.custom.min.js',
    'tinymce',
    'viewmenu',
    'js/jquery/jquery-ui/js/jquery-ui.min.js',
    'js/lodash/lodash.js',
    'js/gridstack/gridstack.js',
    'js/gridlayout.js');

$views = $collection->get('views');

if (!$pid = $collection->has_progresscompletion()) {
    throw new AccessDeniedException();
}

// Get the first view from the collection
$firstview = $views['views'][0];
$view = new View($firstview->id);

if (!can_view_view($pid)) {
    $errorstr = (param_integer('objection', null)) ? get_string('accessdeniedobjection', 'error') : '';
    $errorstr = (param_integer('undo', null)) ? get_string('accessdeniedundo', 'collection') : $errorstr;
    throw new AccessDeniedException($errorstr);
}
else {
    $pview = new View($pid);
    $blocks = $pview->get_blocks();
    $blocks = json_encode($blocks);
    $blocksjs = <<<EOF
$(function () {
    var options = {
        verticalMargin: 5,
        cellHeight: 10,
        disableDrag : true,
        disableResize: true,
    };
    var grid = $('.grid-stack');
    grid.gridstack(options);
    grid = $('.grid-stack').data('gridstack');
    // should add the blocks one by one
    var blocks = {$blocks};
    loadGrid(grid, blocks);
});
EOF;
}

// Set up theme
$viewtheme = $view->get('theme');
if ($viewtheme && $THEME->basename != $viewtheme) {
    $THEME = new Theme($viewtheme);
}
$headers[] = '<meta name="robots" content="noindex">';

$objectionform = false;
$undoverificationform = false;
if ($USER->is_logged_in()) {
    $objectionform = pieform(objection_form());
    $reviewform = pieform(review_form($pview->get('id')));
    if ($notrudeform = notrude_form()) {
        $notrudeform = pieform($notrudeform);
    }
    // For admin to review objection claim, add comment
    // about objectionable content and possibly remove access
    if ($stillrudeform = stillrude_form()) {
        $stillrudeform = pieform($stillrudeform);
    }
    // Check to see if there are any 'verified' verification blocks that the $USER can undo
    if ($pview->get('owner') && $vblocks = get_records_sql_array("SELECT * FROM {block_instance} WHERE blocktype = ? AND view = ?", array('verification', $pid))) {
        $vblockids = array();
        foreach ($vblocks as $vblock) {
            $blockinstance = new BlockInstance($vblock->id);
            $configdata = $blockinstance->get('configdata');
            if (empty($configdata['resetstatement'])) {
                continue; // no one to undo the block
            }
            if (!empty($configdata['availabilitydate']) && $configdata['availabilitydate'] > time()) {
                continue; // not currently verifiable
            }
            if (!empty($configdata['verified']) && $configdata['verifierid'] == $USER->get('id')) {
                $vblockids[$vblock->id] = 1;
            }
            if (!empty($configdata['addcomment'])) {
                if (record_exists('blocktype_verification_comment', 'instance', $vblock->id, 'from', $USER->get('id'))) {
                    $vblockids[$vblock->id] = 1;
                }
            }
        }
        $vblockids = array_keys($vblockids);
        if (!empty($vblockids)) {
            $undoverificationform = pieform(undo_verification_form($vblockids));
        }
    }
}

$smarty = smarty(
    $javascript,
    $headers,
    array('View' => 'view',
        'Collection' => 'collection'
    ),
    array(
        'sidebars' => false,
        'pagehelp' => true,
    )
);

$smarty->assign('PAGETITLE', get_string('portfoliocompletion', 'collection'));
$smarty->assign('maintitle', $collection->get('name'));
$smarty->assign('name', get_string('portfoliocompletion', 'collection'));
$smarty->assign('INLINEJAVASCRIPT', $blocksjs);
if (isset($objectionform)) {
    $smarty->assign('objectionform', $objectionform);
    if ($USER->is_logged_in()) {
        $smarty->assign('notrudeform', $notrudeform);
        $smarty->assign('stillrudeform', $stillrudeform);
    }
    $smarty->assign('objectedpage', $pview->is_objectionable());
    $smarty->assign('objector', $pview->is_objectionable($USER->get('id')));
    $smarty->assign('objectionreplied', $pview->is_objectionable(null, true));
}

if (isset($undoverificationform)) {
    $smarty->assign('undoverificationform', $undoverificationform);
}
if (isset($reviewform)) {
    $smarty->assign('reviewform', $reviewform);
}

if ($view->is_anonymous()) {
    $smarty->assign('author', get_string('anonymoususer'));
    if ($view->is_staff_or_admin_for_page()) {
        $smarty->assign('realauthor', $view->display_author());
    }
}
else {
    $smarty->assign('author', $view->display_author());
}

// collection top navigation
if ($collection) {
    $shownav = $collection->get('navigation');
    if ($shownav) {
        $viewnav = $views['views'];
        if ($collection->get('framework')) {
            array_unshift($viewnav, $collection->collection_nav_framework_option());
        }
        array_unshift($viewnav, $collection->collection_nav_progresscompletion_option());
        $smarty->assign('collection', $viewnav);
    }
    $smarty->assign('collectiontitle', $collection->get('name'));
}

$smarty->assign('progresscompletion', true);

// progress bar
$smarty->assign('quotamessage', get_string('overallcompletion', 'collection'));
list($completedactionspercentage, $totalactions) = $collection->get_signed_off_and_verified_percentage();
$smarty->assign('completedactionspercentage', $completedactionspercentage);
$smarty->assign('totalactions', $totalactions);


// table
foreach ($views['views'] as &$view) {
    $viewobj = new View($view->id);
    $owneraction = $viewobj->get_progress_action('owner');
    $manageraction = $viewobj->get_progress_action('manager');

    $view->ownericonclass = $owneraction->get_icon();
    $view->owneraction = $owneraction->get_action();
    $view->ownertitle = $owneraction->get_title();
    $view->signedoff = ArtefactTypePeerassessment::is_signed_off($viewobj);

    $view->managericonclass = $manageraction->get_icon();
    $view->manageraction = $manageraction->get_action();
    $view->managertitle = $manageraction->get_title();
    $view->verified = ArtefactTypePeerassessment::is_verified($viewobj);
}

$viewobj = new View($firstview->id); // Need to call this as $viewobj to avoid clash with $view in foreach loop above
$submittedgroup = (int)$viewobj->get('submittedgroup');
$can_edit = $USER->can_edit_view($viewobj) && !$submittedgroup && !$viewobj->is_submitted();
if ($viewobj->get_collection()) {
    $can_edit = $can_edit && $USER->can_edit_collection($viewobj->get_collection());
}
$owner = $collection->get('owner');
$smarty->assign('usercaneditview', $can_edit);
$smarty->assign('userisowner', ($owner && $owner == $USER->get('id')));

$smarty->assign('views', $views['views']);
$smarty->assign('viewlocked', $viewobj->get('locked'));
// Is progress page editable?
$pageistemplate = $pview->get_original_template();
if ($can_edit && !$collection->get('lock')) {
    if (($pview->get('owner') && !$pageistemplate) || !$pview->get('owner')) {
        $smarty->assign('editurl', get_config('wwwroot') . 'view/blocks.php?id=' . $collection->has_progresscompletion());
    }
}
$smarty->display('collection/progresscompletion.tpl');

function undo_verification_form($ids) {
    $form = array(
        'name'              => 'undo_verification_form',
        'method'            => 'post',
        'jsform'            => false,
        'autofocus'         => false,
        'elements'          => array(),
    );
    safe_require('blocktype', 'verification');
    foreach ($ids as $id) {
        $bi = new BlockInstance($id);
        $options[$id] = $bi->get('title');
    }
    natsort($options);
    $form['elements']['options'] = array(
        'type'         => 'select',
        'title'        => get_string('verifiedbyme', 'collection'),
        'options'      => $options,
        'description'  => get_string('verifiedbymedescription', 'collection'),
        'collapseifoneoption' => false,
        'rules' => array(
            'required' => true
        )
    );
    $form['elements']['message'] = array(
        'type'  => 'textarea',
        'class' => 'under-label',
        'title' => get_string('reasonforundo', 'collection'),
        'rows'  => 5,
        'cols'  => 80,
        'rules' => array(
            'required' => true
        )
    );

    $form['elements']['submit'] = array(
        'type'    => 'submitcancel',
        'subclass'   => array('btn-secondary'),
        'value'   => array(get_string('notifyappointed', 'collection'), get_string('cancel')),
    );
    return $form;
}

function undo_verification_form_submit(Pieform $form, $values) {
    global $USER, $collection, $pview;

    if (!$USER->is_logged_in()) {
        throw new AccessDeniedException();
    }
    if (!$pview->get('owner')) {
        throw new AccessDeniedException();
    }
    // Double check the block exists on the page
    if ($values['options'] && !record_exists('block_instance', 'id', $values['options'], 'view', $pview->get('id'))) {
        throw new AccessDeniedException();
    }
    require_once('activity.php');
    safe_require('blocktype', 'verification');
    $goto = get_config('wwwroot') . 'collection/progresscompletion.php?id=' . $collection->get('id');

    // Notification
    $title = $pview->get('id');
    $bi = new BlockInstance($values['options']);
    $configdata = $bi->get('configdata');
    $groups = get_column_sql('SELECT "group" FROM {view_access} WHERE "group" IS NOT NULL AND view = ?', array($pview->get('id')));
    if (is_array($groups) && !empty($groups)) {
        $grouproles = PluginBlocktypeVerification::get_roleoptions('grouprole');
    }
    $owner = new User();
    $owner->find_by_id($pview->get('owner'));

    $users = array();
    $accessroles = PluginBlocktypeVerification::get_roleoptions('accessrole');
    $userroles = PluginBlocktypeVerification::get_roleoptions('userrole');
    foreach ($configdata['resetstatement'] as $type) {
        if ($type == 'siteadmin') {
            $users = array_merge($users, get_column('usr', 'id', 'admin', 1, 'active', 1));
        }
        if ($type == 'sitestaff') {
            $users = array_merge($users, get_column('usr', 'id', 'staff', 1, 'active', 1));
        }
        if ($type == 'institutionadmin') {
            $ownerinstitutions = array_keys($owner->get('institutions'));
            foreach ($ownerinstitutions as $i) {
                $users = array_merge($users, get_column_sql("SELECT u.id FROM {usr} u JOIN {usr_institution} ui ON ui.usr = u.id WHERE u.active = 1 AND ui.admin = 1 AND ui.institution = ?", array($i)));
            }
        }
        if ($type == 'institutionstaff') {
            $ownerinstitutions = array_keys($owner->get('institutions'));
            foreach ($ownerinstitutions as $i) {
                $users = array_merge($users, get_column_sql("SELECT u.id FROM {usr} u JOIN {usr_institution} ui ON ui.usr = u.id WHERE u.active = 1 AND ui.staff = 1 AND ui.institution = ?", array($i)));
            }
        }
        if (isset($accessroles[$type])) {
            $users = array_merge($users, get_column('view_access', 'usr', 'role', $type, 'view', $pview->get('id')));
        }
        if (isset($userroles[$type])) {
            $users = array_merge($users, get_column('usr_roles', 'usr', 'role', $type));
        }
        if (isset($grouproles) && !empty($grouproles)) {
            foreach ($groups as $groupid) {
                if (isset($grouproles[$type])) {
                    $users = array_merge($users, get_column('group_member', 'member', 'group', $groupid, 'role', preg_replace('/^group/', '', $type)));
                }
            }
        }
    }
    $users = array_keys(array_flip($users));
    // Save these user ids so we know who is allowed to undo the verification
    foreach ($users as $u) {
        ensure_record_exists('blocktype_verification_undo', (object) array('usr' => $u,
                                                                           'block' => $values['options'],
                                                                           'reporter' => $USER->get('id'),
                                                                           'view' => $pview->get('id')),
                                                            (object) array('usr' => $u,
                                                                           'block' => $values['options'],
                                                                           'reporter' => $USER->get('id'),
                                                                           'view' => $pview->get('id')));
    }
    if (!empty($users)) {
        $message = (object) array(
            'users' => $users,
            'subject' => get_string('undoreportsubject', 'collection'),
            'message' => get_string('undoreportmessage', 'collection', $bi->get('title'), $pview->get_collection()->get('name'), display_name($USER), hsc($values['message'])),
            'url' => $goto . '&undo=1',
        );

        activity_occurred('maharamessage', $message);
        $form->reply(PIEFORM_OK, array(
                'message' => get_string('undoreportsent', 'collection'),
                'goto' => $goto,
            )
        );
    }
    else {
        $form->reply(PIEFORM_ERR, array(
                'message' => get_string('undoreportnotsent', 'collection'),
                'goto' => $goto,
            )
        );
    }
}

function undo_verification_form_cancel_submit(Pieform $form) {
    global $collection, $pview;
    $goto = get_config('wwwroot') . 'collection/progresscompletion.php?id=' . $collection->get('id');

    $form->reply(PIEFORM_OK, array(
            'goto' => $goto,
        )
    );
}
