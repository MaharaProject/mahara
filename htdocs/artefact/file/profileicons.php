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
define('MENUITEM', 'content/profileicons');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'file');
define('SECTION_PAGE', 'profileicons');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('profileicons', 'artefact.file'));

$settingsform = new Pieform(array(
    'name'      => 'settings',
    'renderer'  => 'oneline',
    'autofocus' => false,
    'presubmitcallback' => '',
    'elements' => array(
        'default' => array(
            'type'  => 'submit',
            'value' => get_string('Default', 'artefact.file'),
        ),
        'delete' => array(
            'type'  => 'submit',
            'value' => get_string('Delete', 'artefact.file'),
        ),
    )
));

$uploadform = pieform(array(
    'name'   => 'upload',
    'jsform' => true,
    'presubmitcallback'  => 'preSubmit',
    'postsubmitcallback' => 'postSubmit',
    'plugintype' => 'artefact',
    'pluginname' => 'file',
    'elements' => array(
        'file' => array(
            'type' => 'file',
            'title' => get_string('profileicon', 'artefact.file'),
            'rules' => array('required' => true),
            'maxfilesize'  => get_max_upload_size(false),
        ),
        'title' => array(
            'type' => 'text',
            'title' => get_string('imagetitle', 'artefact.file'),
        ),
        'submit' => array(
            'type' => 'submit',
            'value' => get_string('upload')
        )
    )
));

$strnoimagesfound = json_encode(get_string('noimagesfound', 'artefact.file'));
$struploadingfile = json_encode(get_string('uploadingfile', 'artefact.file'));
$wwwroot = get_config('wwwroot');
$notfound = $THEME->get_image_url('no_userphoto');
if (!get_config('remoteavatars')) {
    $ravatar = $notfound;
}
else {
    $ravatar = remote_avatar($USER->get('email'), array('maxw' => '100', 'maxh' => '100'), $notfound);
}
$profileiconattachedtoportfolioitems = json_encode(get_string('profileiconattachedtoportfolioitems', 'artefact.file'));
$profileiconappearsinviews = json_encode(get_string('profileiconappearsinviews', 'artefact.file'));
$profileiconappearsinskins = json_encode(get_string('profileiconappearsinskins', 'artefact.file'));
$confirmdeletefile = json_encode(get_string('confirmdeletefile', 'artefact.file'));
$setdefault = json_encode(get_string('setdefault', 'artefact.file'));
$markfordeletion = json_encode(get_string('markfordeletion', 'artefact.file'));
$IJS = <<<EOF
formchangemanager.add('settings');

var profileiconschecker = formchangemanager.find('settings');

var table = new TableRenderer(
    'profileicons',
    'profileicons.json.php',
    [
        function(rowdata) {
            if (rowdata.id) {
                return TD({'class': 'profileiconcell'}, null, IMG({'src': '{$wwwroot}thumb.php?type=profileiconbyid&maxsize=100&id=' + rowdata.id, 'alt': rowdata.title ? rowdata.title : rowdata.note}));
            }
            else {
                return TD({'class': 'profileiconcell'}, null, IMG({'src': '{$ravatar}', 'alt': rowdata.title ? rowdata.title : rowdata.note}));
            }
        },
        function(rowdata) {
            return TD(null, rowdata.title ? rowdata.title : rowdata.note);
        },
        function(rowdata) {
            var options = {
                'id': 'setdefault_' + rowdata.id,
                'type': 'radio',
                'name': 'd',
                'value': rowdata.id
            };
            if (rowdata['isdefault'] == 't' || rowdata['isdefault'] == 1) {
                options.checked = 'checked';
            }
            var label = LABEL({'class': 'accessible-hidden', 'for': 'setdefault_' + rowdata.id}, {$setdefault});
            return TD({'class': 'defaultcell'}, INPUT(options), label);
        },
        function(rowdata) {
            var options = {
                'id'      : 'markdelete_' + rowdata.id,
                'type'    : 'checkbox',
                'class'   : 'checkbox',
                'name'    : 'icons[' + rowdata.id + ']',
                'value'   : rowdata.attachcount + ',' + rowdata.viewcount + ',' + rowdata.skincount
            };
            if (!rowdata.id) {
                options.disabled = 'disabled';
            }
            var label = LABEL({'class': 'accessible-hidden', 'for': 'markdelete_' + rowdata.id}, {$markfordeletion});
            return TD({'class': 'deletecell'}, INPUT(options), label);
        }
    ]
);
table.updateOnLoad();
table.emptycontent = {$strnoimagesfound};
table.paginate = false;
table.updatecallback = function(response) {
    var defaultIcon = filter(function (i) { return i.isdefault == 't'; }, response.data);

    if (defaultIcon.length) {
        defaultIcon = defaultIcon[0].id;
        forEach(getElementsByTagAndClassName('img', null, 'column-right'), function(i) {
            if (i.src.match(/thumb\.php\?type=profileiconbyid/)) {
                i.src = i.src.replace(/id=[0-9]+/, 'id=' + String(defaultIcon));
            }
        });
    }
};

table.postupdatecallback = function(response) {
    profileiconschecker.init();
};

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
    $(form).reset();
    $('upload_title').value = '';
}

addLoadEvent( function() {
    connect('settings_delete', 'onclick', function(e) {
        profileiconschecker.set(FORM_SUBMITTED);

        // Find form
        var form = getFirstParentByTagAndClassName(this, 'form', 'pieform');
        forEach (getElementsByTagAndClassName('input', 'checkbox', form), function (profileicon) {
            var id = getNodeAttribute(profileicon, 'name').match(/\d+/)[0];
            if (profileicon.checked == true) {
                var counts = profileicon.value.split(',', 3);
                var warn = '';
                if (counts[0] > 0) {
                    warn += {$profileiconattachedtoportfolioitems} + ' ';
                }
                if (counts[1] > 0) {
                    warn += {$profileiconappearsinviews} + ' ';
                }
                if (counts[2] > 0) {
                    warn += {$profileiconappearsinskins} + ' ';
                }
                if (warn != '') {
                    warn += {$confirmdeletefile};
                    if (!confirm(warn)) {
                        e.stop();
                        return false;
                    }
                }
            }
        });
    });
});

EOF;

$filesize = 0;
function upload_validate(Pieform $form, $values) {
    global $USER, $filesize;
    require_once('file.php');
    require_once('uploadmanager.php');

    $um = new upload_manager('file');
    if ($error = $um->preprocess_file()) {
        $form->set_error('file', $error);
        return false;
    }

    $imageinfo = getimagesize($values['file']['tmp_name']);
    if (!$imageinfo || !is_image_type($imageinfo[2])) {
        $form->set_error('file', get_string('filenotimage'));
        return false;
    }

    if (get_field('artefact', 'COUNT(*)', 'artefacttype', 'profileicon', 'owner', $USER->id) >= 5) {
        $form->set_error('file', get_string('onlyfiveprofileicons', 'artefact.file'));
        return false;
    }

    $filesize = $um->file['size'];
    if (!$USER->quota_allowed($filesize)) {
        $form->set_error('file', get_string('profileiconuploadexceedsquota', 'artefact.file', get_config('wwwroot')));
        return false;
    }

    // Check the file isn't greater than the max allowable size
    $width          = $imageinfo[0];
    $height         = $imageinfo[1];
    $imagemaxwidth  = get_config('imagemaxwidth');
    $imagemaxheight = get_config('imagemaxheight');
    if ($width > $imagemaxwidth || $height > $imagemaxheight) {
        $form->set_error('file', get_string('profileiconimagetoobig', 'artefact.file', $width, $height, $imagemaxwidth, $imagemaxheight));
    }
}

function upload_submit(Pieform $form, $values) {
    global $USER, $filesize;
    safe_require('artefact', 'file');

    try {
        $USER->quota_add($filesize);
    }
    catch (QuotaException $qe) {
        $form->json_reply(PIEFORM_ERR, array(
            'message' => get_string('profileiconuploadexceedsquota', 'artefact.file', get_config('wwwroot'))
        ));
    }

    // Entry in artefact table
    $data = new stdClass;
    $data->owner = $USER->id;
    $data->parent = ArtefactTypeFolder::get_folder_id(get_string('imagesdir', 'artefact.file'), get_string('imagesdirdesc', 'artefact.file'), null, true, $USER->id);
    $data->title = $values['title'] ? $values['title'] : $values['file']['name'];
    $data->title = ArtefactTypeFileBase::get_new_file_title($data->title, (int)$data->parent, $USER->id);  // unique title
    $data->note = $values['file']['name'];
    $data->size = $filesize;
    $imageinfo = getimagesize($values['file']['tmp_name']);
    $data->width    = $imageinfo[0];
    $data->height   = $imageinfo[1];
    $data->filetype = $imageinfo['mime'];
    $data->description = get_string('uploadedprofileicon', 'artefact.file');

    $artefact = new ArtefactTypeProfileIcon(0, $data);
    if (preg_match("/\.([^\.]+)$/", $values['file']['name'], $saved)) {
        $artefact->set('oldextension', $saved[1]);
    }
    $artefact->commit();

    $id = $artefact->get('id');

    // Move the file into the correct place.
    $directory = get_config('dataroot') . 'artefact/file/profileicons/originals/' . ($id % 256) . '/';
    check_dir_exists($directory);
    move_uploaded_file($values['file']['tmp_name'], $directory . $id);

    $USER->commit();

    $form->json_reply(PIEFORM_OK, get_string('profileiconaddedtoimagesfolder', 'artefact.file', get_string('imagesdir', 'artefact.file')));
}

function settings_submit_default(Pieform $form, $values) {
    global $USER, $SESSION;

    $default = param_integer('d');

    if ($default) {
        if (1 != get_field('artefact', 'COUNT(*)', 'id', $default, 'artefacttype', 'profileicon', 'owner', $USER->id)) {
            throw new UserException(get_string('profileiconsetdefaultnotvalid', 'artefact.file'));
        }

        $USER->profileicon = $default;
    }
    else {
        $USER->profileicon = null;
    }
    $USER->commit();

    $SESSION->add_ok_msg(get_string('profileiconsdefaultsetsuccessfully', 'artefact.file'));
    redirect('/artefact/file/profileicons.php');
}

function settings_submit_delete(Pieform $form, $values) {
    require_once('file.php');
    global $USER, $SESSION;

    $icons = param_variable('icons', array());
    $icons = array_keys($icons);

    if ($icons) {
        db_begin();
        foreach ($icons as $icon) {
            $iconartefact = artefact_instance_from_id($icon);
            // Just to be sure
            if ($iconartefact->get('artefacttype') == 'profileicon' && $iconartefact->get('owner') == $USER->get('id')) {
                // Remove the skin background and update the skin thumbs
                require_once(get_config('libroot') . 'skin.php');
                Skin::remove_background($iconartefact->get('id'));
                $iconartefact->delete();
            }
            else {
                throw new AccessDeniedException();
            }
        }

        if (in_array($USER->get('profileicon'), $icons)) {
            $USER->profileicon = null;
            $USER->commit();
        }

        db_commit();

        $SESSION->add_ok_msg(
            get_string('filethingdeleted', 'artefact.file', get_string('nprofilepictures', 'artefact.file', count($icons)))
        );
    }
    else {
        $SESSION->add_info_msg(get_string('profileiconsnoneselected', 'artefact.file'));
    }

    redirect('/artefact/file/profileicons.php');
}

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
$smarty->assign('INLINEJAVASCRIPT', $IJS);
$smarty->assign('uploadform', $uploadform);
// This is a rare case where we don't actually care about the form, because
// it only contains submit buttons (which we can just write as HTML), and
// the buttons need to be inside the tablerenderer.
$smarty->assign('settingsformtag', $settingsform->get_form_tag());
$smarty->assign('imagemaxdimensions', array(get_config('imagemaxwidth'), get_config('imagemaxheight')));
$smarty->assign('PAGEHEADING', TITLE);
$smarty->display('artefact:file:profileicons.tpl');
