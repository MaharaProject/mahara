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
 * @subpackage artefact-blog
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2010 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

define('POSTCOUNT', 20); // number of posts to include in the feed

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
    $smarty->display('artefact:blog:atom.xml.tpl');
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

$artefactid = param_integer('artefact');
$viewid     = param_integer('view');

require_once(get_config('docroot') . 'artefact/lib.php');
$artefact = artefact_instance_from_id($artefactid);

if (!can_view_view($viewid)) {
    generate_feed(error_feed(), error_post(''));
}
elseif (!artefact_in_view($artefactid, $viewid)) {
    generate_feed(error_feed(), error_post(get_string('artefactnotinview', 'error', $artefactid, $viewid)));
}
elseif (!$artefact->in_view_list()) {
    generate_feed(error_feed(), error_post(get_string('artefactonlyviewableinview', 'error')));
}
elseif ($artefact->get('artefacttype') != 'blog') {
    generate_feed(error_feed(), error_post(get_string('feedsnotavailable', 'artefact.blog')));
}
else {
    $owner = get_records_sql_array("
        SELECT a.mtime, u.id, u.firstname, u.lastname, u.profileicon
        FROM {usr} u, {artefact} a
        WHERE a.id = ?
        AND a.owner = u.id
        LIMIT 1;",
        array($artefactid));

    if ($owner[0]->profileicon) {
        $image = get_config('wwwroot') . 'thumb.php?type=profileiconbyid&maxsize=100&id='
            . $owner[0]->profileicon;
    }
    else {
        // use the Mahara logo
        $image = get_config('wwwroot') . 'theme/raw/static/images/site-logo.png';
    }

    // if the owner has a personal website set, use it as the author URI
    $personal_site = get_field('artefact', 'title', 'artefacttype', 'personalwebsite', 'owner', $owner[0]->id);

    $author = array(
        'name' => implode(' ', array($owner[0]->firstname, $owner[0]->lastname)),
        'uri' => $personal_site,
    );
    $link = get_config('wwwroot') . 'view/artefact.php?artefact=' .
        $artefactid . '&view=' . $viewid;
    $selflink = get_config('wwwroot') . 'artefact/blog/atom.php?artefact=' .
        $artefactid . '&view=' . $viewid;

    $postids = get_records_sql_array("
        SELECT a.id, a.title, a.description, a.mtime
        FROM {artefact} a, {artefact_blog_blogpost} bp
        WHERE a.id = bp.blogpost
        AND a.parent = ?
        AND bp.published = 1
        ORDER BY a.ctime DESC
        LIMIT ?;",
        array($artefactid, POSTCOUNT));

    if ($postids) {
        $updated = $postids[0]->mtime;
    }
    else {
        $updated = $owner[0]->mtime;
    }

    $generator = array(
        'uri' => 'http://mahara.org',
        'version' => get_config('release'),
        'text' => 'Mahara',
    );

    $rights = get_string('feedrights', 'artefact.blog', substr($updated, 0, 4) . ' ' . $author['name']);

    // is there a Creative Commons block in this view?
    // if so, set the feed rights accordingly
    $ccblock = get_records_sql_array("
        SELECT b.id
        FROM {block_instance} b
        WHERE b.view = ?
        AND b.blocktype = 'creativecommons'
        LIMIT 1;", array($viewid));

    if ($ccblock) {
        require_once(get_config('docroot') . 'blocktype/lib.php');
        $ccblock_instance = new BlockInstance($ccblock[0]->id);
        $configdata = $ccblock_instance->get('configdata');
        $licensetype = $configdata['license'];
        $licenseurl = "http://creativecommons.org/licenses/$licensetype/3.0/";
        $licensename = get_string($licensetype, 'blocktype.creativecommons');
        $rights .= ' ' . get_string('licensestatement', 'blocktype.creativecommons', $licenseurl, $licensename);
    }

    $feed = array(
        'title' => $artefact->get('title'),
        'link' => $link,
        'selflink' => $selflink,
        'id' => implode(',', array(get_config('wwwroot'), $artefactid, $viewid)),
        'description' => $artefact->get('description'),
        'author' => $author,
        'updated' => atom_date($updated),
        'logo' => $image,
        'icon' => get_config('wwwroot') . 'favicon.ico',
        'generator' => $generator,
        'rights' => $rights,
    );

    $posts = array();
    if ($postids) {
        foreach ($postids as $postid) {
            $post = artefact_instance_from_id($postid->id);
            $attachments = $post->get_attachments();
            $attachlinks = array();
            foreach ($attachments as $attachment) {
                $attachlinks[] = array(
                    'link' => get_config('wwwroot') . 'artefact/file/download.php?file=' .
                        $attachment->id . '&view=' . $viewid,
                    'title' => $attachment->title,
                );
            }
            $posts[] = array(
                'title' => $post->get('title'),
                'link' => get_config('wwwroot') . 'view/artefact.php?artefact=' .
                    $post->get('id') . '&view=' . $viewid,
                'id' => implode(',', array(get_config('wwwroot'), $post->get('id'), $viewid)),
                'description' => $post->get('description'),
                'mtime' => atom_date($postid->mtime),
                'attachments' => $attachlinks,
            );
        }
    }

    generate_feed($feed, $posts);
}
