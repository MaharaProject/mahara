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
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'groups/groupsiown');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require_once('group.php');
define('TITLE', get_string('creategroup', 'group'));

if (!group_can_create_groups()) {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}
$elements = array();
$elements['name'] = array(
            'type'         => 'text',
            'title'        => get_string('groupname', 'group'),
            'rules'        => array( 'required' => true, 'maxlength' => 128 ));
$elements['description'] = array(
            'type'         => 'wysiwyg',
            'title'        => get_string('groupdescription', 'group'),
            'rows'         => 10,
            'cols'         => 55);
$elements['grouptype'] = array(
            'type'         => 'select',
            'title'        => get_string('grouptype', 'group'),
            'options'      => group_get_grouptype_options(),
            'defaultvalue' => 'standard.open',
            'help'         => true);
if (get_config('allowgroupcategories')) {
    $elements['category'] = array(
                'type'         => 'select',
                'title'        => get_string('groupcategory', 'group'),
                'options'      => array('0'=>get_string('nocategoryselected', 'group')) + get_records_menu('group_category','','','displayorder', 'id,title'),
                'defaultvalue' => '',
                'help'         => true);
}
$elements['public'] = array(
            'type'         => 'select',
            'title'        => get_string('publiclyviewablegroup', 'group'),
            'description'  => get_string('publiclyviewablegroupdescription', 'group'),
            'options'      => array(true  => get_string('yes'),
                                    false => get_string('no')),
            'defaultvalue' => 'no',
            'help'         => true,
            'ignore'       => !(get_config('createpublicgroups') == 'all' || get_config('createpublicgroups') == 'admins' && $USER->get('admin')));
$elements['usersautoadded'] = array(
            'type'         => 'select',
            'title'        => get_string('usersautoadded', 'group'),
            'description'  => get_string('usersautoaddeddescription', 'group'),
            'options'      => array(true  => get_string('yes'),
                                    false => get_string('no')),
            'defaultvalue' => 'no',
            'help'         => true,
            'ignore'       => !$USER->get('admin'));
$elements['viewnotify'] = array(
    'type' => 'checkbox',
    'title' => get_string('viewnotify', 'group'),
    'description' => get_string('viewnotifydescription', 'group'),
    'defaultvalue' => 1
);
$elements['submit']   = array(
            'type'  => 'submitcancel',
            'value' => array(get_string('savegroup', 'group'), get_string('cancel')));

$creategroup = pieform(array(
    'name'     => 'creategroup',
    'method'   => 'post',
    'plugintype' => 'core',
    'pluginname' => 'groups',
    'elements' => $elements
));

$smarty = smarty();
$smarty->assign('form', $creategroup);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->display('form.tpl');


function creategroup_validate(Pieform $form, $values) {
    if (get_field('group', 'id', 'name', $values['name'])) {
        $form->set_error('name', get_string('groupalreadyexists', 'group'));
    }
}

function creategroup_cancel_submit() {
    redirect('/group/mygroups.php');
}

function creategroup_submit(Pieform $form, $values) {
    global $USER;
    global $SESSION;

    list($grouptype, $jointype) = explode('.', $values['grouptype']);
    $values['public'] = (isset($values['public'])) ? $values['public'] : 0;
    $values['usersautoadded'] = (isset($values['usersautoadded'])) ? $values['usersautoadded'] : 0;

    $id = group_create(array(
        'name'           => $values['name'],
        'description'    => $values['description'],
        'grouptype'      => $grouptype,
        'category'       => empty($values['category']) ? null : intval($values['category']),
        'jointype'       => $jointype,
        'public'         => intval($values['public']),
        'usersautoadded' => intval($values['usersautoadded']),
        'members'        => array($USER->get('id') => 'admin'),
        'viewnotify'     => intval($values['viewnotify']),
    ));

    $USER->reset_grouproles();

    $SESSION->add_ok_msg(get_string('groupsaved', 'group'));

    redirect('/group/view.php?id=' . $id);
}

?>
