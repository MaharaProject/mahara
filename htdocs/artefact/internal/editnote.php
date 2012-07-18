<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2011 Catalyst IT Ltd and others; see:
 *                    http://wiki.mahara.org/Contributors
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
 * @subpackage artefact-internal
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

define('INTERNAL', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'internal');

define('TITLE', get_string('editnote', 'artefact.internal'));

$artefact = new ArtefactTypeHtml(param_integer('id'));
if (!$USER->can_edit_artefact($artefact) || $artefact->get('locked')) {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}

$goto = get_config('wwwroot') . 'artefact/internal/notes.php';
if ($group = $artefact->get('group')) {
    define('MENUITEM', 'groups');
    define('GROUP', $group);
    $goto .= '?group=' . $group;
}
else if ($institution = $artefact->get('institution')) {
    define('INSTITUTIONALADMIN', 1);
    define('MENUITEM', 'manageinstitutions');
    $goto .= '?institution=' . $institution;
}
else {
    define('MENUITEM', 'content/notes');
}

$form = pieform(array(
    'name' => 'editnote',
    'elements' => array(
        'title' => array(
            'type'         => 'text',
            'title'        => get_string('Title', 'artefact.internal'),
            'defaultvalue' => $artefact->get('title'),
        ),
        'description' => array(
            'type'         => 'wysiwyg',
            'title'        => get_string('Note', 'artefact.internal'),
            'rows'         => 10,
            'cols'         => 70,
            'defaultvalue' => $artefact->get('description'),
        ),
        'allowcomments' => array(
            'type'         => 'checkbox',
            'title'        => get_string('allowcomments', 'artefact.comment'),
            'defaultvalue' => $artefact->get('allowcomments'),
        ),
        'perms' => array(
            'type'         => 'rolepermissions',
            'title'        => get_string('Permissions'),
            'defaultvalue' => $artefact->get('rolepermissions'),
            'group'        => $group,
            'ignore'       => !$group,
        ),
        'submit' => array(
            'type'         => 'submitcancel',
            'value'        => array(get_string('save'), get_string('cancel')),
            'goto'         => $goto,
        ),
    ),
));

$smarty = smarty();
$smarty->assign('PAGEHEADING', $artefact->get('title'));
$smarty->assign('form', $form);
$smarty->display('form.tpl');

function editnote_submit(Pieform $form, array $values) {
    global $SESSION, $artefact, $goto;
    $artefact->set('title', $values['title']);
    $artefact->set('description', $values['description']);
    $artefact->set('allowcomments', (int) $values['allowcomments']);
    if (isset($values['perms'])) {
        $artefact->set('rolepermissions', $values['perms']);
        $artefact->set('dirty', true);
    }
    $artefact->commit();
    $SESSION->add_ok_msg(get_string('noteupdated', 'artefact.internal'));
    redirect($goto);
}
