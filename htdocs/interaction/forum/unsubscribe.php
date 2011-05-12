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
 * @subpackage interaction-forum
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
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
    : '/';

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
redirect('/');
