<?php
/**
 * Add friend invites and friend suggestions to groups.
 *
 * @package    mahara
 * @subpackage core
 * @author     Richard Mansfield
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('PUBLIC', 1);
define('INTERNAL', 1);
define('MENUITEM', 'engage/index');
define('MENUITEM_SUBPAGE', 'members');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('group.php');

define('GROUP', param_integer('id'));
define('SUBSECTIONHEADING', get_string('members'));
$group = group_current_group();
if (!is_logged_in() && !$group->public) {
    throw new AccessDeniedException();
}

$role = group_user_access($group->id);

if (!$role || !$group->suggestfriends) {
    throw new AccessDeniedException();
}

$subheading = get_string('suggesttofriends', 'group');
define('TITLE', $group->name . ' - ' . $subheading);

$form = pieform(array(
    'name' => 'addmembers',
    'elements' => array(
        'users' => array(
            'type' => 'userlist',
            'lefttitle' => get_string('potentialmembers', 'group'),
            'righttitle' => get_string('userstosendrecommendationsto', 'group'),
            'leftarrowlabel' => get_string('potentialmemberstorecommended', 'group'),
            'rightarrowlabel' => get_string('recommendedtopotentialmembers', 'group'),
            'searchscript' => 'group/membersearchresults.json.php',
            'defaultvalue' => array(),
            'searchparams' => array(
                'id' => GROUP,
                'limit' => 100,
                'html' => 0,
                'membershiptype' => 'notinvited',
                'friends' => 1,
            ),
        ),
        'submit' => array(
            'type' => 'submit',
            'class' => 'btn-primary',
            'value' => get_string('submit'),
        )
    )
));

$smarty = smarty();
$smarty->assign('subheading', $subheading);
$smarty->assign('form', $form);
$smarty->display('group/form.tpl');
exit;

/**
 * Submission of adding members.
 *
 * @param  Pieform $form
 * @param  array $values
 * @return void
 */
function addmembers_submit(Pieform $form, $values) {
    global $SESSION, $group, $USER;

    if (empty($values['users'])) {
        redirect(get_config('wwwroot') . 'group/suggest.php?id=' . GROUP);
    }

    require_once('activity.php');
    $groupurl = group_homepage_url($group, false);
    activity_occurred('maharamessage', array(
        'users'   => $values['users'],
        'subject' => '',
        'message' => '',
        'strings'       => (object) array(
            'subject' => (object) array(
                'key'     => 'suggestgroupnotificationsubject',
                'section' => 'group',
                'args'    => array(display_name($USER)),
            ),
            'message' => (object) array(
                'key'     => 'suggestgroupnotificationmessage',
                'section' => 'group',
                'args'    => array(display_name($USER), hsc($group->name), get_config('sitename')),
            ),
        ),
        'url'     => $groupurl,
        'urltext' => hsc($group->name),
    ));

    $SESSION->add_ok_msg(get_string('nrecommendationssent', 'group', count($values['users'])));
    redirect(get_config('wwwroot') . $groupurl);
}
