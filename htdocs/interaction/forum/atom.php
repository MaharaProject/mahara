<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2010 Catalyst IT Ltd and others; see:
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
 * @copyright  (C) 2006-2010 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

function atom_date($date) {
    $date = str_replace(' ', 'T', $date);
    $date .= date('P');
    return $date;
}

function generate_feed($feed, $posts) {
    $smarty = smarty();
    $smarty->assign('feed', $feed);
    $smarty->assign('posts', $posts);

    header("Content-Type: application/atom+xml");
    $smarty->display('interaction:forum:atom.xml.tpl');
}

function error_feed() {
    return array(
        'title' => get_string('accessdenied', 'error'),
        'link' => '',
        'selflink' => '',
        'id' => '',
        'description' => '',
        'ownername' => '',
        'updated' => '',
        'logo' => '',
    );
}

function error_post($message) {
    return array(
        0 => array(
            'title' => get_string('accessdenied', 'error'),
            'link' => '',
            'id' => '',
            'description' => $message,
            'mtime' => '',
        ));
}

$feedtype = param_alpha('type'); //g = group, f = forum, t = topic
$id = param_integer('id');

if ($feedtype == 'g') {
    if (!record_exists('group', 'id', $id, 'deleted', 0)) {
        generate_feed(error_feed(), error_post(get_string('groupnotfound', 'group', $id)));
        exit();
    }

    $groupid = $id;

    $sql = "
        SELECT u.firstname, u.lastname, p.id, p.parent, p.topic, p.subject, p.body, p.ctime
        FROM {interaction_forum_post} p
        INNER JOIN (
            SELECT t.id
            FROM {interaction_forum_topic} t
            INNER JOIN {interaction_instance} f ON t.forum = f.id
            WHERE f.group = ?
        ) gt ON p.topic = gt.id
        INNER JOIN {usr} u ON p.poster = u.id
        WHERE p.deleted = 0";

    $link = get_config('wwwroot') . 'interaction/forum/index.php?group=' . $id;
    $title = implode(' - ', array(get_field('group', 'name', 'id', $id),
        get_string('allposts', 'interaction.forum')));
}
elseif ($feedtype == 'f') {
    if (!($groupid = get_field('interaction_instance', '"group"', 'id', $id, 'deleted', 0))) {
        generate_feed(error_feed(), error_post(get_string('cantfindforum', 'interaction.forum', $id)));
        exit();
    }

    $sql = "
        SELECT u.firstname, u.lastname, p.id, p.parent, p.topic, p.subject, p.body, p.ctime
        FROM {interaction_forum_post} p
        INNER JOIN {interaction_forum_topic} t ON p.topic = t.id
        INNER JOIN {usr} u ON p.poster = u.id
        WHERE t.forum = ?
        AND p.deleted = 0";

    $link = get_config('wwwroot') . 'interaction/forum/view.php?id=' . $id;
    $title = implode(' - ', array(get_field('group', 'name', 'id', $groupid),
        get_field('interaction_instance', 'title', 'id', $id),
        get_string('allposts', 'interaction.forum')));
}
elseif ($feedtype == 't') {
    if (!($forumid = get_field('interaction_forum_topic', 'forum', 'id', $id, 'deleted', 0))) {
        generate_feed(error_feed(), error_post(get_string('cantfindtopic', 'interaction.forum', $id)));
        exit();
    }

    $groupid = get_field('interaction_instance', '"group"', 'id', $forumid, 'deleted', 0);

    $sql = "
        SELECT u.firstname, u.lastname, p.id, p.parent, p.topic, p.subject, p.body, p.ctime
        FROM {interaction_forum_post} p, {usr} u
        WHERE p.deleted = 0
        AND p.topic = ?
        AND p.poster = u.id";

    $link = get_config('wwwroot') . 'interaction/forum/topic.php?id=' . $id;
    $title = implode(' - ', array(get_field('group', 'name', 'id', $groupid),
        get_field('interaction_instance', 'title', 'id', $forumid),
        get_field_sql("
            SELECT p.subject
            FROM {interaction_forum_post} p
            WHERE p.topic = ?
            AND p.parent IS NULL", array($id)),
        get_string('allposts', 'interaction.forum')));
}
if (!get_field('"group"', 'public', 'id', $groupid)) {
    generate_feed(error_feed(), error_post(get_string('notpublic', 'group')));
    exit();
}

$sql .= "
    ORDER BY p.ctime DESC
    LIMIT ?;";

$selflink = get_config('wwwroot') . 'interaction/forum/atom.php?type=' . $feedtype . '&id=' . $id;

$postcount = 20;
$postrecords = get_records_sql_array($sql, array($id, $postcount));

$image = get_config('wwwroot') . 'theme/raw/static/images/site-logo.png';
$updated = $postrecords[0]->ctime;

$generator = array(
    'uri' => 'http://mahara.org',
    'version' => get_config('release'),
    'text' => 'Mahara',
);

$feed = array(
    'title' => $title,
    'link' => $link,
    'selflink' => $selflink,
    'id' => implode(',', array(get_config('wwwroot'), $feedtype, $id)),
    'updated' => atom_date($updated),
    'logo' => $image,
    'icon' => get_config('wwwroot') . 'favicon.ico',
    'generator' => $generator,
);

$posts = array();
if($postrecords) {
    foreach($postrecords as &$post) {
        $parent = $post->parent;
        while(!$post->subject) {
            $post->subject = get_field('interaction_forum_post', 'subject', 'id', $parent);
            $parent = get_field('interaction_forum_post', 'parent', 'id', $parent);
        }
        if ($parent != $post->parent) {
            $post->subject = get_string('re', 'interaction.forum', $post->subject);
        }
        $posts[] = array(
            'title' => $post->subject,
            'link' => get_config('wwwroot') . 'interaction/forum/topic.php?id=' .
                $post->topic . '#post' . $post->id,
            'id' => implode(',', array(get_config('wwwroot'), $post->topic, $post->id)),
            'description' => $post->body,
            'mtime' => atom_date($post->ctime),
            'author' => implode(' ', array($post->firstname, $post->lastname)),
        );
    }
}

generate_feed($feed, $posts);
?>
