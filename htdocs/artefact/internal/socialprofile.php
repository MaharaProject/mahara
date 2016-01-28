<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-internal
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) 2014 Gregor Anzelj <gregor.anzelj@gmail.com>
 *
 */

define('INTERNAL', true);
define('MENUITEM', 'content/profile');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'internal');
define('SECTION_PAGE', 'social');
define('INTERNAL_SUBPAGE', 'social');

require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('profile','artefact.internal'));
safe_require('artefact', 'internal');


if (!get_record('blocktype_installed', 'active', 1, 'name', 'socialprofile')) {
    // This block type is not installed. The user is not allowed in this form.
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}

$id = param_integer('id', 0);
$delete = param_integer('delete', 0);


if ($delete) {
    // Check if social profile is a mandatory system field
    // and if this is the last field, they can't delete it.
    $mandatory_fields = ArtefactTypeProfile::get_mandatory_fields();
    if (isset($mandatory_fields['socialprofile'])) {
        $social_profiles = ArtefactTypeSocialprofile::get_social_profiles();
        if (count($social_profiles) <= 1) {
            // they can't delete.
            $SESSION->add_error_msg(get_string('socialprofilerequired', 'artefact.internal'));
            redirect('/artefact/internal/index.php?fs=social');
        }
    }

    $todelete = new ArtefactTypeSocialprofile($id);
    if (!$USER->can_edit_artefact($todelete)) {
        throw new AccessDeniedException(get_string('accessdenied', 'error'));
    }
    $deleteform = array(
        'name' => 'deleteprofileform',
        'plugintype' => 'artefact',
        'pluginname' => 'internal',
        'renderer' => 'div',
        'elements' => array(
            'submit' => array(
                'type' => 'submitcancel',
                'class' => 'btn-default',
                'value' => array(get_string('deleteprofile','artefact.internal'), get_string('cancel')),
                'goto' => get_config('wwwroot') . '/artefact/internal/index.php?fs=social',
            ),
        )
    );
    $form = pieform($deleteform);
    $message = get_string('deleteprofileconfirm', 'artefact.internal');
    $subheading = get_string('deletethisprofile', 'artefact.internal', $todelete->get('description'));
}
else {
    if ($id > 0) {
        $toedit = new ArtefactTypeSocialprofile($id);
        if (!$USER->can_edit_artefact($toedit)) {
            throw new AccessDeniedException(get_string('accessdenied', 'error'));
        }
        // Get default values
        $title = $toedit->get('title');
        $description = $toedit->get('description');
        $note = $toedit->get('note');
        if ($note == 'website') {
            $note = $description;
        }
    }
    else {
        // Set default values
        $title = '';
        $description = '';
        $note = '';
    }

    $socialnetworkoptions = array();
    foreach (ArtefactTypeSocialprofile::$socialnetworks as $socialnetwork) {
        $socialnetworkoptions[$socialnetwork] = get_string($socialnetwork . '.input', 'artefact.internal');
    }

    $editform = array(
        'name' => 'editprofileform',
        'class' => 'form-editprofile',
        'plugintype' => 'artefact',
        'pluginname' => 'internal',
        'elements' => array(
            'id' => array(
                'type' => 'hidden',
                'value' => $id,
            ),
            'profiletype' => array(
                'type' => 'select',
                'class' => 'select-with-input autofocus',
                'title' => get_string('profiletype', 'artefact.internal'),
                'options' => $socialnetworkoptions,
                'allowother' => true,
                'defaultvalue' => $note,
                'width' => 171,
                'rules' => array('required' => true),
            ),
            'profileurl' => array(
                'type' => 'text',
                'title' => get_string('profileurl', 'artefact.internal'),
                'description' => get_string('profileurldesc', 'artefact.internal'),
                'defaultvalue' => $title,
                'size' => 40,
                'rules' => array('required' => true),
            ),
            'submit' => array(
                'type' => 'submitcancel',
                'class' => 'btn-primary',
                'value' => array(get_string('save'), get_string('cancel')),
                'goto' => get_config('wwwroot') . 'artefact/internal/index.php?fs=social',
            ),
        )
    );
    $form = pieform($editform);
    $message = null;
    if ($id > 0) {
        $subheading = get_string('editthisprofile', 'artefact.internal', $toedit->get('description'));
    }
    else {
        $subheading = get_string('newsocialprofile', 'artefact.internal');
    }
}

$smarty = smarty();
$smarty->assign('navtabs', PluginArtefactInternal::submenu_items());
$smarty->assign('subheading', $subheading);
$smarty->assign('form', $form);
$smarty->assign('message', $message);
$smarty->display('artefact:internal:socialprofile.tpl');


// Delete social profile
function deleteprofileform_submit(Pieform $form, $values) {
    global $SESSION, $todelete;

    $todelete->delete();
    $SESSION->add_ok_msg(get_string('profiledeletedsuccessfully', 'artefact.internal'));
    redirect(get_config('wwwroot') . 'artefact/internal/index.php?fs=social');
}

function editprofileform_validate(Pieform $form, $values) {
    global $USER;

    if (in_array($values['profiletype'], ArtefactTypeSocialprofile::$socialnetworks)) {
        $desc = get_string($values['profiletype'], 'artefact.internal');
        $type = $values['profiletype'];
    }
    else {
        $desc = $values['profiletype'];
        $type = 'website';
    }

    // We're editing. Make sure it's not a duplicate.
    $data = ArtefactTypeSocialprofile::get_social_profiles();
    foreach ($data as $i => $socialprofile) {
        // don't compare to itself.
        if ($socialprofile->id != $values['id']) {
            if ($socialprofile->title == $values['profileurl'] &&
                $socialprofile->description == $desc &&
                $socialprofile->note == $type
            ) {
                $form->set_error('profileurl', get_string('duplicateurl', 'artefact.internal'));
                break;
            }
        }
    }
}

// Add new or edit existing social profile
function editprofileform_submit(Pieform $form, $values) {
    global $SESSION, $USER, $toedit;

    if (!$toedit) {
        $toedit = new ArtefactTypeSocialprofile();
    }

    if (in_array($values['profiletype'], ArtefactTypeSocialprofile::$socialnetworks)) {
        $desc = get_string($values['profiletype'], 'artefact.internal');
        $type = $values['profiletype'];
    }
    else {
        $desc = $values['profiletype'];
        $type = 'website';
    }

    $toedit->set('owner', $USER->get('id'));
    $toedit->set('author', $USER->get('id'));
    $toedit->set('title', $values['profileurl']);
    $toedit->set('description', $desc);
    $toedit->set('note', $type);
    $toedit->commit();

    $SESSION->add_ok_msg(get_string('profilesavedsuccessfully', 'artefact.internal'));
    redirect(get_config('wwwroot') . 'artefact/internal/index.php?fs=social');
}
