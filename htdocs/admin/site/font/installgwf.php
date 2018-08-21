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

// TODO: The *only* reason we require the admin to upload a Google Fonts archive, is so we can use
// it to generate the skin preview images. For actually displaying pages that use the skins, we do
// not need to download anything. So, if we could figure out a way to generate those skin previews
// without requiring the download, that'd be great.
define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'configsite/sitefonts');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'installgwf');

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
require_once(get_config('libroot') . 'skin.php');
define('TITLE', get_string('installgwfont', 'skin'));

if (!get_config('skins')) {
    throw new FeatureNotEnabledException();
}

$form = pieform(array(
    'name' => 'gwfontform',
    //'jsform' => true,
    'plugintype' => 'artefact',
    'pluginname' => 'skin',
    'elements' => array(
        'gwfinstructions' => array(
            'type' => 'markup',
            'value' => get_string('gwfinstructions', 'skin'),
        ),
        'gwfzipfile' => array(
            'type' => 'file',
            'labelhtml' => get_string('gwfzipfile', 'skin'),
            'description' => get_string('gwfzipdescription', 'skin'),
            'rules'   => array('required' => true),
        ),
        'submit' => array(
            'type' => 'submitcancel',
            'class'=> 'btn-primary',
            'value' => array(get_string('installgwfont', 'skin'), get_string('cancel', 'mahara')),
            'goto' => get_config('wwwroot') . 'admin/site/fonts.php',
        ),
    ),
));


$smarty = smarty(array('tablerenderer'));
setpageicon($smarty, 'icon-text-width');
$smarty->assign('form', $form);
$smarty->assign('PAGEHEADING', hsc(TITLE));
$smarty->display('form.tpl');


function gwfontform_validate(Pieform $form, $values) {
    global $USER, $SESSION;
    require_once('file.php');
    require_once('uploadmanager.php');

    $valid = false;
    if ($values['gwfzipfile'] <> null) {
        $filetype = $values['gwfzipfile']['type'];
        // Ensures that the correct file was chosen
        $accepted = array('application/zip',
                          'application/x-zip-compressed',
                          'multipart/x-zip',
                          'application/s-compressed');
        foreach($accepted as $mimetype) {
            if ($mimetype == $filetype) {
                $valid = true;
                break;
            }
        }
        // Safari and Chrome don't register zip mime types. Something better could be used here.
        // Check if file extension, that is the last 4 characters in file name, equals '.zip'...
        $valid = substr($values['gwfzipfile']['name'], -4) == '.zip' ? true : false;

        if (!$valid) {
            $form->set_error('gwfzipfile', get_string('notvalidzipfile', 'skin'));
        }

        // pass it through the virus checker
        $um = new upload_manager('gwfzipfile');
        if ($error = $um->preprocess_file()) {
            $form->set_error($inputname, $error);
        }
    }
}

function gwfontform_submit(Pieform $form, $values) {
    global $USER, $SESSION;
    require_once('file.php');
    require_once('uploadmanager.php');

    $fontpath = get_config('dataroot') . 'skins/fonts/';
    check_dir_exists($fontpath, true, true);

    $currentfont = null;
    $licence = null;
    $previewfont = null;

    $variants = array();
    $variants['regular'] = array(
        "variant" => "regular",
        "font-weight" => "normal",
        "font-style" => "normal"
    );

    $zip = new ZipArchive();
    if ($zip->open($values['gwfzipfile']['tmp_name'])) {
        $currentfont = dirname($zip->getNameIndex(0));
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $extractfiles = array();
            $fontname = dirname($zip->getNameIndex($i));
            $filename = basename($zip->getNameIndex($i));
            $makefolder = false;
            if (empty($fontname) || $fontname == '.') {
                $fontname = substr($values['gwfzipfile']['name'], 0, -1 * strlen('.zip'));
                $makefolder = true;
            }
            // Find correct licence file...
            if (substr($zip->getNameIndex($i), -3) == 'txt') {
                $licence = $filename;
                $extractfiles[] = $zip->getNameIndex($i);
            }

            // Find correct TTF font file for generating skin previews...
            $possiblenames = array(
                str_replace("_", "", $fontname) . ".ttf",
                str_replace("_", "", $fontname) . "-Regular.ttf",
                str_replace("_", "", $fontname) . "-Normal.ttf",
            );
            if (in_array($filename, $possiblenames)) {
                $previewfont = $filename;
                $extractfiles[] = $zip->getNameIndex($i);
            }

            // Reset settings for each new font...
            if (!is_null($licence) && !is_null($previewfont)) {
                $foldername = preg_replace(Skin::FONTNAME_FILTER_CHARACTERS, '', $fontname);
                // Assign a new name, if the font with the same name already exists...
                $foldername = Skin::new_font_name($foldername);
                if ($makefolder == true) {
                    $fontpath .= $foldername . '/';
                    check_dir_exists($fontpath, true, true);
                }

                $installfont = array(
                    "name" => $foldername,
                    "title" => str_replace("_", " ", $fontname),
                    "licence" => $licence,
                    "notice" => "", // null ???
                    "previewfont" => $previewfont,
                    "variants" => serialize($variants),
                    "fonttype" => "google",
                    "onlyheading" => 0,
                    "fontstack" => "'" . escape_css_string(str_replace("_", " ", $fontname)) . "'",
                    "genericfont" => "sans-serif",
                );
                // Install fonts (write data into database). Check if the record doesn't exist!!!
                ensure_record_exists('skin_fonts',
                    (object) array('name' => $installfont['name']),
                    (object) $installfont
                );
                // Extract installed fonts
                foreach ($extractfiles as $extractfile) {
                    $fullfontpath = $fontpath . $foldername . '/';
                    check_dir_exists($fullfontpath, true, true);
                    copy("zip://" . $values['gwfzipfile']['tmp_name'] . "#" . $extractfile, $fullfontpath . $previewfont);
                }

                $currentfont = $fontname;
                $licence = null;
                $previewfont = null;
            }
        }
        $SESSION->add_ok_msg(get_string('gwfontadded', 'skin'));
    }
    else {
        $SESSION->add_error_msg(get_string('archivereadingerror', 'skin'));
    }

    redirect('/admin/site/fonts.php');
}
