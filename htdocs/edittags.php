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
    execute_sql(
        "UPDATE {tag} SET tag = ? WHERE tag = ? AND ownertype = ? AND ownerid = ? AND resourcetype IN ('artefact', 'view', 'collection')",
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
        "DELETE FROM {tag} WHERE tag = ? AND ownertype = ? AND ownerid = ? AND resourcetype IN ('artefact', 'view', 'collection')",
        array($tag, 'user', $userid)
    );
    db_commit();
    $SESSION->add_ok_msg(get_string('tagdeletedsuccessfully'));
    redirect(get_config('wwwroot') . 'tags.php');
}
