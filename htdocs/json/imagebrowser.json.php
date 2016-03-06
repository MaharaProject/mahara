<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Mike Kelly UAL <m.f.kelly@arts.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('imagebrowser.php');
$change                 = param_boolean('change', false);
$viewid                 = param_integer('view', 0);
$forumpostid            = param_integer('post', 0);
$groupid                = param_integer('group', 0);
$institution            = param_alphanum('institution', 0);
$blogid                 = param_alphanum('blogid', 0);
$fileid                 = param_alphanum('selected', null);
$changebrowsetab        = param_integer('imgbrowserconf_artefactid_changeowner', 0);
// Folder value is 0 when returning to Home folder
$changefolder = param_exists('imgbrowserconf_artefactid_changefolder')? true : false;
$uploadimg = param_integer('imgbrowserconf_artefactid_upload', 0);
$formsubmit = param_exists('action_submitimage')? true : false;
$formcancel = param_exists('cancel_action_submitimage')? true : false;

if ($forumpostid && !$groupid) {
    $sql =    "SELECT g.id
                FROM {group} g
                INNER JOIN {interaction_instance} ii ON ii.group = g.id
                INNER JOIN {interaction_forum_topic} ift ON ift.forum = ii.id
                INNER JOIN {interaction_forum_post} ifp ON ifp.topic = ift.id
                WHERE ifp.id = ?
                AND ifp.deleted = 0";
    $groupid = get_field_sql($sql, array($forumpostid));
}

if ($blogid) {
    safe_require('artefact', 'blog');
    $blogobj = new ArtefactTypeBlog($blogid);
    $institution = $blogobj->get('institution');
    $institution = !empty($institution) ? $institution : 0;
    $groupid = $blogobj->get('group');
    $groupid = !empty($groupid) ? $groupid : 0;
}

// Create new image browser
if ($change) {
    $ib = new ImageBrowser(array('view' => $viewid,
                                 'post' => $forumpostid,
                                 'group' => $groupid,
                                 'institution' => $institution,
                                 'selected' => $fileid));
    try {
        $returndata = $ib->render_image_browser();
        json_reply(false, array('data' => $returndata));
    }
    catch (Exception $e) {
        json_reply(true, $e->getMessage());
    }
}

// If an image browser was already created and updated somehow, rebuild or submit the form now
// TODO why are other values true when submitting form?
if ($changebrowsetab || $changefolder || $uploadimg || $formsubmit || $formcancel) {
    $ib = new ImageBrowser(array('view' => $viewid,
                                 'post' => $forumpostid,
                                 'group' => $groupid,
                                 'institution' => $institution));
    $ib->render_image_browser();
}