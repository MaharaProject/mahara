<?php
/**
 *
 * @package    mahara
 * @subpackage interaction-forum
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('PUBLIC', 1);
define('INTERNAL', 1);
define('MENUITEM', 'groups/forums');
define('SECTION_PLUGINTYPE', 'interaction');
define('SECTION_PLUGINNAME', 'forum');
define('SECTION_PAGE', 'index');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('group.php');
safe_require('interaction', 'forum');
require_once(get_config('docroot') . 'interaction/lib.php');
define('SUBSECTIONHEADING', get_string('nameplural', 'interaction.forum'));

$groupid = param_integer('group');
define('GROUP', $groupid);
$group = group_current_group();

$membership = group_user_access($groupid);

if (!$membership && !$group->public) {
    throw new GroupAccessDeniedException(get_string('cantviewforums', 'interaction.forum'));
}

define('TITLE', $group->name . ' - ' . get_string('nameplural', 'interaction.forum'));

$forums = get_forum_list($group->id, $USER->get('id'));
if ($forums) {
    // query gets a new forum object for every moderator of that forum
    // this combines all moderators together into one object per forum
    $count = count($forums);
    for ($i = 0; $i < $count; $i++) {
        $forums[$i]->moderators = array();
        if ($forums[$i]->moderator) {
            $forums[$i]->moderators[] = $forums[$i]->moderator;
        }
        $temp = $i;
        while (isset($forums[$i+1]) && $forums[$i+1]->id == $forums[$temp]->id) {
            $i++;
            $forums[$temp]->moderators[] = $forums[$i]->moderator;
            unset($forums[$i]);
        }
    }

    $i = 0;
    foreach ($forums as $forum) {
        $forum->feedlink = get_config('wwwroot') . 'interaction/forum/atom.php?type=f&id=' . $forum->id;

        if ($membership) {
            $forum->subscribe = pieform(array(
                'name'     => 'subscribe_forum' . ($i == 0 ? '' : $i),
                'plugintype' => 'interaction',
                'pluginname' => 'forum',
                'validatecallback' => 'subscribe_forum_validate',
                'successcallback' => 'subscribe_forum_submit',
                'autofocus' => false,
                'renderer' => 'div',
                'class' => 'form-as-button',
                'elements' => array(
                    'submit' => array(
                    'type'  => 'button',
                    'usebuttontag' => true,
                    'class' => 'btn-default btn-sm',
                    'renderelementsonly' => true,
                    'value' => $forum->subscribed ? '<span class="icon icon-lg icon-times left text-danger" role="presentation" aria-hidden="true"></span> ' . get_string('Unsubscribe', 'interaction.forum') : '<span class="icon icon-lg icon-star left" role="presentation" aria-hidden="true"></span> ' . get_string('Subscribe', 'interaction.forum'),
                    'help' => false
                    ),
                    'forum' => array(
                        'type' => 'hidden',
                        'value' => $forum->id
                    ),
                    'redirect' => array(
                        'type' => 'hidden',
                        'value' => 'index'
                    ),
                    'group' => array(
                        'type' => 'hidden',
                        'value' => $groupid
                    ),
                    'type' => array(
                        'type' => 'hidden',
                        'value' => $forum->subscribed ? 'unsubscribe' : 'subscribe'
                    ),
                )
            ));
            $i++;
        }
    }
}

$feedlink = get_config('wwwroot') . 'interaction/forum/atom.php?type=g&id=' . $group->id;
$headers = array();
if ($group->public) {
    $headers[] ='<link rel="alternate" type="application/atom+xml" href="' . $feedlink . '">';
}

$smarty = smarty(array(), $headers);
$smarty->assign('groupid', $groupid);
$smarty->assign('publicgroup', $group->public);
$smarty->assign('feedlink', $feedlink);
$smarty->assign('heading', $group->name);
$smarty->assign('headingclass', 'page-header');
$smarty->assign('rsswithtitle', true);
$smarty->assign('admin', $membership == 'admin');
$smarty->assign('groupadmins', group_get_admins(array($groupid)));
$smarty->assign('forums', $forums);
$smarty->display('interaction:forum:index.tpl');
