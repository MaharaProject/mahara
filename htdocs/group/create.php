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
            'rules'        => array('maxlength' => 65536),
            'rows'         => 10,
            'cols'         => 55);

$grouptypeoptions = group_get_grouptype_options();
if ($grouptypeparam = param_alphanumext('grouptype', 0) and isset($grouptypeoptions[$grouptypeparam])) {
    $elements['grouptype'] = array(
        'type'         => 'hidden',
        'value'        => $grouptypeparam,
    );
}
else {
    $elements['grouptype'] = array(
        'type'         => 'select',
        'title'        => get_string('grouptype', 'group'),
        'options'      => $grouptypeoptions,
        'defaultvalue' => 'standard.open',
        'help'         => true
    );
}

if (get_config('allowgroupcategories')
    && ($groupcategories = get_records_menu('group_category','','','displayorder', 'id,title'))) {
    if ($groupcategoryparam = param_integer('category', 0) and isset($groupcategories[$groupcategoryparam])) {
        $elements['category'] = array(
            'type'  => 'hidden',
            'value' => $groupcategoryparam,
        );
    }
    else {
        $elements['category'] = array(
            'type'         => 'select',
            'title'        => get_string('groupcategory', 'group'),
            'options'      => array('0'=>get_string('nocategoryselected', 'group')) + $groupcategories,
            'defaultvalue' => ''
        );
    }
}

$publicallowed = get_config('createpublicgroups') == 'all' || (get_config('createpublicgroups') == 'admins' && $USER->get('admin'));
if (!param_variable('creategroup_submit', null)) {
    // If 'public=0' param is passed on first page load, hide the public checkbox.
    $publicparam = param_integer('public', null);
}

$elements['public'] = array(
    'type'         => 'checkbox',
    'title'        => get_string('publiclyviewablegroup', 'group'),
    'description'  => get_string('publiclyviewablegroupdescription', 'group'),
    'help'         => true,
    'ignore'       => !$publicallowed || (isset($publicparam) && $publicparam === 0),
);

$elements['usersautoadded'] = array(
            'type'         => 'checkbox',
            'title'        => get_string('usersautoadded', 'group'),
            'description'  => get_string('usersautoaddeddescription', 'group'),
            'help'         => true,
            'ignore'       => !$USER->get('admin'));
$elements['viewnotify'] = array(
    'type' => 'checkbox',
    'title' => get_string('viewnotify', 'group'),
    'description' => get_string('viewnotifydescription', 'group'),
    'defaultvalue' => 1
);
$elements['submit'] = array(
    'type'  => 'submitcancel',
    'name'  => 'creategroup_submit',
    'value' => array(get_string('savegroup', 'group'), get_string('cancel'))
);

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
    // This check has not always been case-insensitive; don't use get_record in case we get >1 row back.
    if (get_records_sql_array('SELECT id FROM {group} WHERE LOWER(TRIM(name)) = ?', array(strtolower(trim($values['name']))))) {
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
        'name'           => trim($values['name']),
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
