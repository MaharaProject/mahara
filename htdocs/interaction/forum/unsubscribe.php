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

define('INTERNAL', 1);
define('PUBLIC', 1);
define('MENUITEM', 'groups/forums');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');

$forum = $topic = 0;

$key = param_alphanum('key', '');
$subscriptiontype = 'forum';
$forum = param_integer('forum', 0);
if (!$forum) {
    $subscriptiontype = 'topic';
    $topic = param_integer('topic');
}
$goto = $USER->is_logged_in()
    ? ($subscriptiontype == 'forum') ? '/interaction/forum/view.php?id=' . $forum : '/interaction/forum/topic.php?id=' . $topic
    : '/index.php';

if ($key || $USER->is_logged_in()) {
    // get record from forum subscriptions for this key
    if ($key) {
        $subscription = get_record('interaction_forum_subscription_' . $subscriptiontype, 'key', $key);
    }
    else {
        $subscription = get_record('interaction_forum_subscription_' . $subscriptiontype, 'user', $USER->get('id'), $subscriptiontype, $$subscriptiontype);
    }

    if (!$subscription) {
        $SESSION->add_info_msg(get_string('youarenotsubscribedtothis' . $subscriptiontype, 'interaction.forum'));
        redirect($goto);
    }

    if ($USER->is_logged_in() && $subscription->user != $USER->get('id')) {
        throw new AccessDeniedException(get_string('youcannotunsubscribeotherusers', 'interaction.forum'));
    }

    if ($key) {
        delete_records('interaction_forum_subscription_' . $subscriptiontype, 'key', $key);
    }
    else {
        delete_records('interaction_forum_subscription_' . $subscriptiontype, 'user', $USER->get('id'), $subscriptiontype, $$subscriptiontype);
    }
    $SESSION->add_ok_msg(get_string($subscriptiontype . 'successfulunsubscribe', 'interaction.forum'));
    redirect($goto);
}

// Not logged in and no key provided
redirect('/index.php');
