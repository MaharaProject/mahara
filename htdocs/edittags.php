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
define('MENUITEM', 'create/tags');
require('init.php');

define('TITLE', get_string('edittags'));

$tags = get_my_tags(null, true, 'freq', true);

if ($tag = param_variable('tag', null)) {
    $edittagform = pieform(array(
        'name'     => 'edit_tag',
        'elements' => array(
            'tagname' => array(
                'type'         => 'text',
                'size'         => 30,
                'title'        => get_string('name'),
                'defaultvalue' => $tag,
                'rules'        => array('required' => true),
            ),
            'submit' => array(
                'type'         => 'submit',
                'class'        => 'btn-primary',
                'value'        => get_string('submit'),
            ),
        ),
    ));
    $deletetagform = pieform(array(
        'name'     => 'delete_tag',
        'renderer' => 'oneline',
        'elements' => array(
            'submit' => array(
                'type'         => 'submit',
                'value'        => get_string('delete'),
                'class'        => 'btn-danger',
                'confirm'      => get_string('confirmdeletetag'),
            ),
        ),
    ));
}

$smarty = smarty();
$smarty->assign('tags', $tags);
if ($tag) {
    $smarty->assign('tag', $tag);
    $smarty->assign('tagsearchurl', get_config('wwwroot') . 'tags.php?tag=' . urlencode($tag));
    $smarty->assign('edittagform', $edittagform);
    $smarty->assign('deletetagform', $deletetagform);
}
$smarty->display('edittags.tpl');

function edit_tag_submit(Pieform $form, $values) {
    global $SESSION, $USER, $tag;
    if (!$userid = $USER->get('id')) {
        redirect(get_config('wwwroot') . 'edittags.php?tag=' . urlencode($tag));
    }
    if ($values['tagname'] == $tag) {
        redirect(get_config('wwwroot') . 'edittags.php?tag=' . urlencode($tag));
    }
    db_begin();
    // Check we don't end up with a clash where the tag value change matches another tag on the same object
    if ($newtags = get_records_sql_assoc("SELECT id, tag, resourcetype, resourceid
                                          FROM {tag} WHERE tag = ? AND ownertype = ? AND ownerid = ? AND resourcetype IN ('artefact', 'view', 'collection', 'blocktype')",
                                          array($values['tagname'], 'user', $userid))) {
        $newtagarray = array();
        foreach ($newtags as $nk => $nv) {
            $tagstr = $nv->tag . $nv->resourcetype . $nv->resourceid;
            $newtagarray[$tagstr] = 1;
        }

        if ($oldtags = get_records_sql_assoc("SELECT id, tag, resourcetype, resourceid
                                              FROM {tag} WHERE tag = ? AND ownertype = ? AND ownerid = ? AND resourcetype IN ('artefact', 'view', 'collection', 'blocktype')",
                                              array($tag, 'user', $userid))) {
            $deltags = array();
            foreach ($oldtags as $ok => $ov) {
                $potentialtagstr = $values['tagname'] . $ov->resourcetype . $ov->resourceid;
                if (isset($newtagarray[$potentialtagstr])) {
                    $deltags[] = $ov->id;
                }
            }
            if ($deltags) {
                execute_sql("DELETE FROM {tag} WHERE id IN (" . implode(',', $deltags) . ")");
            }
        }
    }

    execute_sql(
        "UPDATE {tag} SET tag = ? WHERE tag = ? AND ownertype = ? AND ownerid = ? AND resourcetype IN ('artefact', 'view', 'collection', 'blocktype')",
        array($values['tagname'], $tag, 'user', $userid)
    );

    db_commit();
    $SESSION->add_ok_msg(get_string('tagupdatedsuccessfully'));
    redirect(get_config('wwwroot') . 'tags.php?tag=' . urlencode($values['tagname']));
}

function delete_tag_submit(Pieform $form, $values) {
    global $SESSION, $USER, $tag;
    if (!$userid = $USER->get('id')) {
        redirect(get_config('wwwroot') . 'edittags.php?tag=' . urlencode($tag));
    }
    db_begin();
    execute_sql(
        "DELETE FROM {tag} WHERE tag = ? AND ownertype = ? AND ownerid = ? AND resourcetype IN ('artefact', 'view', 'collection', 'blocktype')",
        array($tag, 'user', $userid)
    );
    db_commit();
    $SESSION->add_ok_msg(get_string('tagdeletedsuccessfully'));
    redirect(get_config('wwwroot') . 'tags.php');
}
