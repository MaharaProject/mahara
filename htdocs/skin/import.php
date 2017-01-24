<?php
/**
 *
 * @package    mahara
 * @subpackage skin
 * @author     Gregor Anzelj
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) 2010-2013 Gregor Anzelj <gregor.anzelj@gmail.com>
 *
 */

define('INTERNAL', true);
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'skin');
define('SECTION_PAGE', 'import');

require_once(dirname(dirname(__FILE__)) . '/init.php');
require_once('skin.php');
safe_require('artefact', 'file');
require_once('uploadmanager.php');
define('TITLE', get_string('importskins', 'skin'));

$importsiteskins = param_boolean('site', false);

if (!can_use_skins(null, $importsiteskins)) {
    throw new FeatureNotEnabledException();
}

if ($importsiteskins) {
    if (!$USER->get('admin')) {
        $SESSION->add_error_msg(get_string('accessforbiddentoadminsection'));
        redirect();
    }
    define('ADMIN', 1);
    define('MENUITEM', 'configsite/siteskins');
}
else {
    define('MENUITEM', 'myportfolio/myskins');
}

$form = pieform(array(
    'name' => 'importskinform',
    'plugintype' => 'artefact',
    'pluginname' => 'skin',
    'elements' => array(
        'file' => array(
            'type' => 'file',
            'title' => get_string('validxmlfile', 'skin'),
            'rules' => array('required' => true)
        ),
        'skintype' => array(
            'type' => 'hidden',
            'value' => ($importsiteskins ? 'site' : 'private'), // Are we importing site skin(s)?
        ),
        'submit' => array(
            'type' => 'submitcancel',
            'class' => 'btn-primary',
            'value' => array(get_string('import', 'skin'), get_string('cancel', 'mahara')),
            'goto' => ($importsiteskins ? get_config('wwwroot') . 'admin/site/skins.php' : get_config('wwwroot') . 'skin/'),
        ),
    ),
));


$smarty = smarty(array('tablerenderer'));
$smarty->assign('form', $form);
$smarty->assign('PAGEHEADING', hsc(TITLE));
$smarty->display('form.tpl');


function importskinform_validate(Pieform $form, $values) {
    global $USER, $SESSION;

    $filetype = $values['file']['type'];
    if (!$filetype || $filetype <> 'text/xml') {
        $form->set_error('file', get_string('notvalidxmlfile', 'skin'));
    }

    require_once('file.php');
    require_once('uploadmanager.php');
    $um = new upload_manager('file');
    if ($error = $um->preprocess_file()) {
        $form->set_error('file', $error);
    }
}

function importskinform_submit(Pieform $form, $values) {
    global $USER, $SESSION;
    require_once(get_config('docroot'). 'artefact/file/lib.php');

    // Open XML file and import Skin(s)...
    $filename = $values['file']['tmp_name'];
    $contents = file_get_contents($filename);

    libxml_before(true);
    $xmldoc = new DOMDocument('1.0', 'UTF-8');
    //$xmldoc->load($filename);
    $xmldoc->loadXML($contents);

    $skinsdata = $xmldoc->getElementsByTagName('skin');
    libxml_after();

    $siteskin = ($values['skintype'] == 'site');
    // A non-admin can't create a site skin.
    if ($siteskin && !$USER->get('admin')) {
        $values['skintype'] = 'private';
        $siteskin = false;
    }

    foreach ($skinsdata as $skindata) {
        db_begin();
        // Join all view skin css/formating data to array...
        $skin = array();

        // Body element...
        $items = $skindata->getElementsByTagName('body');
        foreach ($items as $item) {
            $skin = array_merge($skin, array('body_background_color' => $item->getAttribute('background-color')));
            $skin = array_merge($skin, array('body_background_image' => 0));
            $skin = array_merge($skin, array('body_background_repeat' => Skin::background_repeat_value_to_number($item->getAttribute('background-repeat'))));
            $skin = array_merge($skin, array('body_background_attachment' => $item->getAttribute('background-attachment')));
            $skin = array_merge($skin, array('body_background_position' => Skin::background_position_value_to_number($item->getAttribute('background-position'))));
        }

        // Header element...  // TODO remove this
        $items = $skindata->getElementsByTagName('header');
        foreach ($items as $item) {
            $skin = array_merge($skin, array('header_background_color' => $item->getAttribute('background-color')));
            $skin = array_merge($skin, array('header_text_font_color' => $item->getAttribute('font-color')));
            $skin = array_merge($skin, array('header_link_normal_color' => $item->getAttribute('normal-color')));
            if ($item->getAttribute('normal-decoration') == 'none') {
                $skin = array_merge($skin, array('header_link_normal_underline' => 0));
            }
            else {
                $skin = array_merge($skin, array('header_link_normal_underline' => 1));
            }
            $skin = array_merge($skin, array('header_link_hover_color' => $item->getAttribute('hover-color')));
            if ($item->getAttribute('hover-decoration') == 'none') {
                $skin = array_merge($skin, array('header_link_hover_underline' => 0));
            }
            else {
                $skin = array_merge($skin, array('header_link_hover_underline' => 1));
            }
            $skin = array_merge($skin, array('header_logo_image' => $item->getAttribute('logo-image')));
        }

        // View element...  // TODO remove this
        $items = $skindata->getElementsByTagName('view');
        foreach ($items as $item) {
            $skin = array_merge($skin, array('view_background_color' => $item->getAttribute('background-color')));
            $skin = array_merge($skin, array('view_background_image' => 0));
            $skin = array_merge($skin, array('view_background_repeat' => Skin::background_repeat_value_to_number($item->getAttribute('background-repeat'))));
            $skin = array_merge($skin, array('view_background_attachment' => $item->getAttribute('background-attachment')));
            $skin = array_merge($skin, array('view_background_position' => Skin::background_position_value_to_number($item->getAttribute('background-position'))));
            $skin = array_merge($skin, array('view_background_width' => str_replace("%", "", $item->getAttribute('width')))); // odstrani znak %!
            $skin = array_merge($skin, array('view_background_margin' => $item->getAttribute('margin-top')));
        }

        // Text element...
        $items = $skindata->getElementsByTagName('text');
        foreach ($items as $item) {
            $skin = array_merge($skin, array('view_text_font_family' => $item->getAttribute('text-font')));
            $skin = array_merge($skin, array('view_heading_font_family' => $item->getAttribute('heading-font')));
            $skin = array_merge($skin, array('view_text_font_size' => $item->getAttribute('font-size')));
            $skin = array_merge($skin, array('view_text_font_color' => $item->getAttribute('font-color')));
            $skin = array_merge($skin, array('view_text_heading_color' => $item->getAttribute('heading-color')));
            $skin = array_merge($skin, array('view_text_emphasized_color' => $item->getAttribute('emphasized-color')));
        }

        // Link element...
        $items = $skindata->getElementsByTagName('link');
        foreach ($items as $item) {
            $skin = array_merge($skin, array('view_link_normal_color' => $item->getAttribute('normal-color')));
            if ($item->getAttribute('normal-decoration') == 'none') {
                $skin = array_merge($skin, array('view_link_normal_underline' => 0));
            }
            else {
                $skin = array_merge($skin, array('view_link_normal_underline' => 1));
            }
            $skin = array_merge($skin, array('view_link_hover_color' => $item->getAttribute('hover-color')));
            if ($item->getAttribute('hover-decoration') == 'none') {
                $skin = array_merge($skin, array('view_link_hover_underline' => 0));
            }
            else {
                $skin = array_merge($skin, array('view_link_hover_underline' => 1));
            }
        }

        // Table element...  // TODO remove this
        $items = $skindata->getElementsByTagName('table');
        foreach ($items as $item) {
            $skin = array_merge($skin, array('view_table_border_color' => $item->getAttribute('border-color')));
            $skin = array_merge($skin, array('view_table_odd_row_color' => $item->getAttribute('odd-row-color')));
            $skin = array_merge($skin, array('view_table_even_row_color' => $item->getAttribute('even-row-color')));
        }

        // Custom CSS element...
        $items = $skindata->getElementsByTagName('customcss');
        foreach ($items as $item) {
            $contents = $item->getAttribute('contents');
            if (is_serialized_string($contents)) {
                $skin['view_custom_css'] = clean_css(unserialize($contents), $preserve_css=true);
            }
            else {
                $skin['view_custom_css'] = "/* Invalid imported CSS */";
            }
        }

        // Image element...
        // TODO: Background image file support for site skins
        if ($siteskin) {
            $skin['body_background_image'] = 0;
            $skin['view_background_image'] = 0;
        }
        else {
            $items = $skindata->getElementsByTagName('image');
            foreach ($items as $item) {
                // Write necessary data in 'artefact' table...
                // TODO: When we rework the file upload code to make it more general,
                // rewrite this to reuse content from filebrowser.php
                $now = date("Y-m-d H:i:s");
                $artefact_attr = $item->getAttribute('artefact');
                $artefact_file_files_attr = $item->getAttribute('artefact_file_files');
                $artefact_file_image_attr = $item->getAttribute('artefact_file_image');
                if (is_valid_serialized_skin_attribute($artefact_attr)
                    && is_valid_serialized_skin_attribute($artefact_file_files_attr)
                    && is_valid_serialized_skin_attribute($artefact_file_image_attr)
                    ) {
                    $artefact = (object)array_merge(
                        (array)unserialize($artefact_attr),
                        (array)unserialize($artefact_file_files_attr),
                        (array)unserialize($artefact_file_image_attr)
                    );
                }
                else {
                    $artefact = new stdClass();
                }
                unset($artefact->id);
                unset($artefact->fileid);
                $artefact->owner  = $USER->get('id');
                $artefact->author = $USER->get('id');
                $artefact->atime = $now;
                $artefact->ctime = $now;
                $artefact->mtime = $now;
                $artobj = new ArtefactTypeImage(0, $artefact);
                $artobj->commit();
                $id = $artobj->get('id');

                // Create folder and file inside it. then write contents into it...
                $imagedir = get_config('dataroot') . ArtefactTypeFile::get_file_directory($id);
                if (!check_dir_exists($imagedir, true, true)) {
                    throw new SystemException("Unable to create folder $imagedir");
                }
                else {
                    // Write contents to a file...
                    $imagepath = $imagedir . '/' . $id;
                    $contents = base64_decode($item->getAttribute('contents'));
                    $fp = fopen($imagepath, 'w');
                    fwrite($fp, $contents);
                    fclose($fp);
                    // We can keep going, but the skin will be missing one of its files
                    if ($clamerror = mahara_clam_scan_file($imagepath)) {
                        $SESSION->add_error_msg($clamerror);
                    }
                    chmod($imagepath, get_config('filepermissions'));
                }

                $type = $item->getAttribute('type');
                if ($type == 'body-background-image') {
                    $skin['body_background_image'] = $id;
                }
                if ($type == 'view-background-image') {  // TODO remove this
                    $skin['view_background_image'] = $id;
                }
            }
        }

        $viewskin = array();
        if ($skindata->getAttribute('title') <> '') {
            $viewskin['title'] = $skindata->getAttribute('title');
        }
        $viewskin['description'] = $skindata->getAttribute('description');
        $viewskin['owner'] = $USER->get('id');
        $viewskin['type'] = $values['skintype'];
        $viewskin['viewskin'] = $skin;

        // Fonts element...
        // Only admins can install site fonts
        if ($USER->get('admin')) {
            $fonts = $skindata->getElementsByTagName('font');
            foreach ($fonts as $font) {
                $fontname = preg_replace("#[^A-Za-z0-9]#", "", $font->getAttribute('name'));
                $fontname = Skin::new_font_name($fontname);
                // Only upload font if it doesn't already exist on the site
                if (!(Skin::font_exists($font->getAttribute('title')))) {
                    $fontdata = array(
                        'name' => $fontname,
                        'title' => $font->getAttribute('title'),
                        'licence' => $font->getAttribute('font-licence'),
                        'previewfont' => $font->getAttribute('font-preview'),
                        'variants' => base64_decode($font->getAttribute('font-variants')),
                        'fonttype' => $font->getAttribute('font-type'),
                        'onlyheading' => $font->getAttribute('heading-font-only'),
                        'fontstack' => $font->getAttribute('font-stack'),
                        'genericfont' => $font->getAttribute('generic-font'),
                    );
                    insert_record('skin_fonts', $fontdata);

                    $fontpath = get_config('dataroot') . 'skins/fonts/' . $fontdata['name'] . '/';
                    if (!check_dir_exists($fontpath, true, true)) {
                        throw new SystemException("Unable to create folder $fontpath");
                    }
                    else {
                        $files = $font->getElementsByTagName('file');
                        foreach ($files as $file) {
                            // Read the filename and the contents of each file from XML...
                            $filename = $file->getAttribute('name');
                            $contents = base64_decode($file->getAttribute('contents'));
                            // Import and copy each file to the appropriate folder...
                            $fp = fopen($fontpath . $filename, 'wb');
                            fwrite($fp, $contents);
                            fclose($fp);
                            // We can keep going, but the skin will be missing one of its files
                            if ($clamerror = mahara_clam_scan_file($fontpath . $filename)) {
                                $SESSION->add_error_msg($clamerror);
                            }
                            chmod($fontpath . $filename, get_config('filepermissions'));
                        }
                    }
                }
            }
        }

        Skin::create($viewskin);
        db_commit();
    }

    $SESSION->add_ok_msg(get_string('skinimported', 'skin'));
    if ($values['skintype'] == 'site') {
        redirect('/admin/site/skins.php');
    }
    else {
        redirect('/skin/index.php');
    }
}
