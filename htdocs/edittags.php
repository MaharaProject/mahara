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
define('MENUITEM', 'myportfolio');
require('init.php');

define('TITLE', get_string('edittags'));

$tags = get_my_tags();

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
        "UPDATE {view_tag} SET tag = ? WHERE tag = ? AND \"view\" IN (SELECT id FROM {view} WHERE \"owner\" = ?)",
        array($values['tagname'], $tag, $userid)
    );
    execute_sql(
        "UPDATE {collection_tag} SET tag = ? WHERE tag = ? AND \"collection\" IN (SELECT id FROM {collection} WHERE \"owner\" = ?)",
        array($values['tagname'], $tag, $userid)
    );
    execute_sql(
        "UPDATE {artefact_tag} SET tag = ? WHERE tag = ? AND artefact IN (SELECT id FROM {artefact} WHERE \"owner\" = ?)",
        array($values['tagname'], $tag, $userid)
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
        "DELETE FROM {view_tag} WHERE tag = ? AND view IN (SELECT id FROM {view} WHERE \"owner\" = ?)",
        array($tag, $userid)
    );
    execute_sql(
        "DELETE FROM {collection_tag} WHERE tag = ? AND collection IN (SELECT id FROM {collection} WHERE \"owner\" = ?)",
        array($tag, $userid)
    );
    execute_sql(
        "DELETE FROM {artefact_tag} WHERE tag = ? AND artefact IN (SELECT id FROM {artefact} WHERE \"owner\" = ?)",
        array($tag, $userid)
    );
    db_commit();
    $SESSION->add_ok_msg(get_string('tagdeletedsuccessfully'));
    redirect(get_config('wwwroot') . 'tags.php');
}
