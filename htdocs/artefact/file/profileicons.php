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
define('MENUITEM', 'profileicons');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'file');
define('SECTION_PAGE', 'profileicons');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('profileicons', 'artefact.file'));

$settingsform = pieform_instance(array(
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
    'class'  => 'form-upload',
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
            'maxfilesize'  => get_max_upload_size(true),
        ),
        'title' => array(
            'type' => 'text',
            'title' => get_string('imagetitle', 'artefact.file'),
        ),
        'submit' => array(
            'type' => 'submit',
            'class' => 'btn-primary',
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
$profileiconappearsinposts = json_encode(get_string('profileiconappearsinposts', 'artefact.file'));
$confirmdeletefile = json_encode(get_string('confirmdeletefile', 'artefact.file'));

$IJS = <<<EOF
formchangemanager.add('settings');

var profileiconschecker = formchangemanager.find('settings');

var table = new TableRenderer(
    'profileicons',
    'profileicons.json.php',
    [
        function(rowdata) {
            if (rowdata.id) {
              return jQuery('<td>', {'class': 'profileiconcell'})
                .append(jQuery('<img>', {'src': '{$wwwroot}thumb.php?type=profileiconbyid&maxsize=100&id=' + rowdata.id, 'alt': rowdata.title ? rowdata.title : rowdata.note}))[0];
            }
            else {
              return jQuery('<td>', {'class': 'profileiconcell'})
                .append(jQuery('<img>', {'src': '{$ravatar}', 'alt': rowdata.title ? rowdata.title : rowdata.note}))[0];
            }
        },
        function(rowdata) {
            return jQuery('<td>', {'text': rowdata.title ? rowdata.title : rowdata.note})[0];
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
            var label = jQuery('<label>', {'class': 'accessible-hidden sr-only', 'for': 'setdefault_' + rowdata.id, 'text': rowdata.default_str});
            return jQuery('<td>', {'class': 'defaultcell'})
                .append(jQuery('<input>', options).append(label))[0];
        },
        function(rowdata) {
            var options = {
                'id'      : 'markdelete_' + rowdata.id,
                'type'    : 'checkbox',
                'class'   : 'checkbox',
                'name'    : 'icons[' + rowdata.id + ']',
                'value'   : rowdata.attachcount + ',' + rowdata.viewcount + ',' + rowdata.skincount + ',' + rowdata.postcount
            };
            if (!rowdata.id) {
                options.disabled = 'disabled';
            }
            var label =  jQuery('<label>',{'class': 'accessible-hidden sr-only', 'for': 'markdelete_' + rowdata.id, 'text': rowdata.delete_str});
            return jQuery('<td>',{'class': 'deletecell'})
              .append(jQuery('<input>', options), label)[0];
        }
    ]
);
table.updateOnLoad();
table.emptycontent = {$strnoimagesfound};

table.postupdatecallback = function(response) {
    profileiconschecker.init();
};

var uploadingMessage = jQuery('<tr><td>' + {$struploadingfile} + '</td></tr>') ;

function preSubmit(form, data) {
    formStartProcessing(form, data);
    uploadingMessage.insertAfter(jQuery('#upload_submit_container'));
}

function postSubmit(form, data) {
    // removeElement(uploadingMessage);
    table.doupdate();
    formStopProcessing(form, data);
    quotaUpdate();

    jQuery(form)[0].reset();
    jQuery('#upload_title').val('');
}

jQuery(function($) {
    $('#settings_delete').on('click', function(e) {
        profileiconschecker.set(FORM_SUBMITTED);

        // Find form
        var form = $(this).closest('form.pieform');
        $(form).find('input.checkbox').each(function () {
            var id = $(this).prop('name').match(/\d+/)[0];
            if ($(this).prop('checked')) {
                var counts = $(this).prop('value').split(',', 4);
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
                if (counts[3] > 0) {
                    warn += {$profileiconappearsinposts} + ' ';
                }
                if (warn != '') {
                    warn += {$confirmdeletefile};
                    if (!confirm(warn)) {
                        e.preventDefault();
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

    $um = new upload_manager('file', false, null, false, get_max_upload_size(true));
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
    safe_require('artefact', 'file');

    $data = new stdClass();
    $data->title = $values['title'] ? $values['title'] : $values['file']['name'];

    try {
        ArtefactTypeProfileIcon::save_uploaded_file($values['file']['tmp_name'], $data);
    }
    catch (QuotaExceededException $e) {
        $form->json_reply(PIEFORM_ERR, array(
            'message' => get_string('profileiconuploadexceedsquota', 'artefact.file', get_config('wwwroot'))
        ));
    }
    catch (UploadException $e) {
        $form->json_reply(PIEFORM_ERR, array(
            'message' => get_string('uploadoffilefailed', 'artefact.file',  $data->title) . ': ' . $e->getMessage()
        ));
    }
    catch (Exception $e) {
        $form->json_reply(PIEFORM_ERR, array(
            'message' => get_string('uploadoffilefailed', 'artefact.file',  $data->title) . ': ' . $e->getMessage()
        ));
    }

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
    require_once(get_config('docroot') . 'artefact/lib.php');
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

$smarty = smarty(array('tablerenderer'));
setpageicon($smarty, 'icon-id-badge');
$smarty->assign('INLINEJAVASCRIPT', $IJS);
$smarty->assign('uploadform', $uploadform);
// This is a rare case where we don't actually care about the form, because
// it only contains submit buttons (which we can just write as HTML), and
// the buttons need to be inside the tablerenderer.
$smarty->assign('settingsformtag', $settingsform->get_form_tag());
$smarty->assign('imagemaxdimensions', array(get_config('imagemaxwidth'), get_config('imagemaxheight')));
$smarty->display('artefact:file:profileicons.tpl');
