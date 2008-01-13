<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2007 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'profile/icons');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'internal');
define('SECTION_PAGE', 'profileficons');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('profileicons', 'artefact.internal'));
$smarty = smarty(
    array('tablerenderer'),
    array(),
    array(),
    array(
        'sideblocks' => array(
            array(
                'name'   => 'quota',
                'weight' => -10,
                'data'   => array(),
            ),
        ),
    )
);

$settingsform = new Pieform(array(
    'name'      => 'settings',
    'renderer'  => 'oneline',
    'autofocus' => false,
    'presubmitcallback' => '',
    'elements' => array(
        'default' => array(
            'type'  => 'submit',
            'value' => get_string('Default', 'artefact.internal'),
        ),
        'delete' => array(
            'type'  => 'submit', 
            'value' => get_string('Delete', 'artefact.internal'),
        ),
        'unsetdefault' => array(
            'type' => 'submit',
            'value' => get_string('usenodefault', 'artefact.internal'),
        ),
    )
));

$uploadform = pieform(array(
    'name'   => 'upload',
    'jsform' => true,
    'presubmitcallback'  => 'preSubmit',
    'postsubmitcallback' => 'postSubmit',
    'plugintype' => 'artefact',
    'pluginname' => 'internal',
    'elements' => array(
        'file' => array(
            'type' => 'file',
            'title' => get_string('profileicon', 'artefact.internal'),
            'rules' => array('required' => true)
        ),
        'title' => array(
            'type' => 'text',
            'title' => get_string('Title', 'artefact.internal'),
            'help'  => true,
        ),
        'submit' => array(
            'type' => 'submit',
            'value' => get_string('upload')
        )
    )
));

$strnoimagesfound = json_encode(get_string('noimagesfound', 'artefact.internal'));
$struploadingfile = json_encode(get_string('uploadingfile', 'artefact.internal'));
$wwwroot = get_config('wwwroot');
$smarty->assign('INLINEJAVASCRIPT', <<<EOF
var table = new TableRenderer(
    'profileicons',
    'profileicons.json.php',
    [
        function(rowdata) {
            return TD(null, IMG({'src': '$wwwroot/thumb.php?type=profileiconbyid&maxsize=100&id=' + rowdata.id, 'alt': rowdata.note}));
        },
        function(rowdata) {
            return TD(null, rowdata.title ? rowdata.title : rowdata.note);
        },
        function(rowdata) {
            var options = {
                'type': 'radio',
                'name': 'd',
                'value': rowdata.id
            };
            if (rowdata['isdefault'] == 't' || rowdata['isdefault'] == 1) {
                options.checked = 'checked';
            }
            return TD({'class': 'center'}, INPUT(options));
        },
        function(rowdata) {
            return TD({'class': 'center'}, INPUT({'type': 'checkbox', 'name': 'icons[' + rowdata.id + ']'}));
        }
    ]
);
table.updateOnLoad();
table.emptycontent = {$strnoimagesfound};
table.paginate = false;

var uploadingMessage = TR(null,
    TD(null, {$struploadingfile})
);

function preSubmit(form, data) {
    formStartProcessing(form, data);
    insertSiblingNodesAfter($('upload_submit_container'), uploadingMessage);
}

function postSubmit(form, data) {
    removeElement(uploadingMessage);
    table.doupdate();
    formStopProcessing(form, data);
    quotaUpdate();
    if (!data.error) {
        $(form).reset();
    }
}

EOF
);

$filesize = 0;
function upload_validate(Pieform $form, $values) {
    global $USER, $filesize;
    require_once('file.php');
    if (!is_image_mime_type(get_mime_type($values['file']['tmp_name']))) {
        $form->set_error('file', get_string('filenotimage'));
    }

    if (get_field('artefact', 'COUNT(*)', 'artefacttype', 'profileicon', 'owner', $USER->id) >= 5) {
        $form->set_error('file', get_string('onlyfiveprofileicons', 'artefact.internal'));
    }

    $filesize = filesize($values['file']['tmp_name']);
    if (!$USER->quota_allowed($filesize)) {
        $form->set_error('file', get_string('profileiconuploadexceedsquota', 'artefact.internal', get_config('wwwroot')));
    }

    // Check the file isn't greater than the max allowable size
    list($width, $height) = getimagesize($values['file']['tmp_name']);
    $imagemaxwidth  = get_config('imagemaxwidth');
    $imagemaxheight = get_config('imagemaxheight');
    if ($width > $imagemaxwidth || $height > $imagemaxheight) {
        $form->set_error('file', get_string('profileiconimagetoobig', 'artefact.internal', $width, $height, $imagemaxwidth, $imagemaxheight));
    }
}

function upload_submit(Pieform $form, $values) {
    global $USER, $filesize;

    // If there are no icons, we can set this one that is being uploaded to be
    // the default for the user
    $setasdefault = false;
    if (0 == get_field('artefact', 'COUNT(*)', 'artefacttype', 'profileicon', 'owner', $USER->id)) {
        $setasdefault = true;
    }

    try {
        $USER->quota_add($filesize);
    }
    catch (QuotaException $qe) {
        $form->json_reply(PIEFORM_ERR, array(
            'message' => get_string('profileiconuploadexceedsquota', 'artefact.internal', get_config('wwwroot'))
        ));
    }

    // Entry in artefact table
    $artefact = new ArtefactTypeProfileIcon();
    $artefact->set('owner', $USER->id);
    $artefact->set('title', ($values['title']) ? $values['title'] : $values['file']['name']);
    $artefact->set('note', $values['file']['name']);
    $artefact->commit();

    $id = $artefact->get('id');

    // Move the file into the correct place.
    $directory = get_config('dataroot') . 'artefact/internal/profileicons/originals/' . ($id % 256) . '/';
    check_dir_exists($directory);
    move_uploaded_file($values['file']['tmp_name'], $directory . $id);

    if ($setasdefault) {
        $USER->profileicon = $id;
    }

    $USER->commit();

    $form->json_reply(PIEFORM_OK, get_string('uploadedprofileiconsuccessfully', 'artefact.internal'));
}

function settings_submit_default(Pieform $form, $values) {
    global $USER, $SESSION;

    $default = param_integer('d');

    if (1 != get_field('artefact', 'COUNT(*)', 'id', $default, 'artefacttype', 'profileicon', 'owner', $USER->id)) {
        throw new UserException(get_string('profileiconsetdefaultnotvalid', 'artefact.internal'));
    }

    $USER->profileicon = $default;
    $USER->commit();
    $SESSION->add_ok_msg(get_string('profileiconsdefaultsetsuccessfully', 'artefact.internal'));
    redirect('/artefact/internal/profileicons.php');
}

function settings_submit_delete(Pieform $form, $values) {
    require_once('file.php');
    global $USER, $SESSION;

    $icons = param_variable('icons', array());
    $icons = array_keys($icons);

    $icons = join(',', array_map('intval', $icons));
    if ($icons) {
        db_begin();
        delete_records_select('view_artefact', "artefact IN ($icons)");
        delete_records_select('artefact', "
            artefacttype = 'profileicon' AND
            owner = ? AND
            id IN($icons)", array($USER->id));

        if (in_array($USER->get('profileicon'), explode(',', $icons))) {
            $USER->profileicon = null;
        }

        db_commit();

        // Now all the database manipulation has happened successfully, remove 
        // all of the images
        foreach (explode(',', $icons) as $icon) {
            $USER->quota_remove(filesize(get_config('dataroot') . 'artefact/internal/profileicons/originals/' . ($icon % 256) . '/' . $icon));
            $USER->commit();
            delete_image('artefact/internal/profileicons', $icon);
        }

        $SESSION->add_ok_msg(get_string('profileiconsdeletedsuccessfully', 'artefact.internal'));
    }
    else {
        $SESSION->add_info_msg(get_string('profileiconsnoneselected', 'artefact.internal'));
    }

    redirect('/artefact/internal/profileicons.php');
}

function settings_submit_unsetdefault(Pieform $form, $values) {
    global $USER, $SESSION;
    $USER->profileicon = null;
    $USER->commit();
    $SESSION->add_info_msg(get_string('usingnodefaultprofileicon', 'artefact.internal'));
}

$smarty->assign('uploadform', $uploadform);
// This is a rare case where we don't actually care about the form, because
// it only contains submit buttons (which we can just write as HTML), and
// the buttons need to be inside the tablerenderer.
$smarty->assign('settingsformtag', $settingsform->get_form_tag());
$smarty->assign('imagemaxdimensions', array(get_config('imagemaxwidth'), get_config('imagemaxheight')));
$smarty->display('artefact:internal:profileicons.tpl');

?>
