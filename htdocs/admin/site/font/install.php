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

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'configsite/sitefonts');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'install');

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
require_once(get_config('libroot') . 'skin.php');
define('TITLE', get_string('installfont', 'skin'));

if (!get_config('skins')) {
    throw new FeatureNotEnabledException();
}

$form = pieform(array(
    'name' => 'addfontform',
//    'jsform' => true,
    'plugintype' => 'artefact',
    'pluginname' => 'skin',
    'elements' => array(
        'fontinstructions' => array(
            'type' => 'html',
            'title' => '',
            'value' => get_string('installfontinstructions', 'skin'),
        ),
        'fonttitle' => array(
            'type' => 'text',
            'title' => get_string('fontname', 'skin'),
            'rules'   => array('required' => true),
        ),
        'fontnotice' => array(
            'type' => 'text',
            'title' => get_string('fontnotice', 'skin'),
            'description' => get_string('fontnoticedescription', 'skin'),
        ),
        'fontstyle' => array(
            'type' => 'select',
            'title' => get_string('fontstyle', 'skin'),
            'defaultvalue' => 'regular',
            'options' => array(
                'regular' => get_string('regular', 'skin'),
                'bold' => get_string('bold', 'skin'),
                'italic' => get_string('italic', 'skin'),
                'bolditalic' => get_string('bolditalic', 'skin'),
            ),
            'rules'   => array('required' => true),
        ),
        'fonttype' => array(
            'type' => 'radio',
            'title' => get_string('fonttype', 'skin'),
            'defaultvalue' => 'text',
            'options' => array(
                'text' => get_string('headingandtext', 'skin'),
                'heading' => get_string('headingonly', 'skin'),
            ),
            'rules'   => array('required' => true),
        ),
        'genericfont' => array(
            'type' => 'select',
            'title' => get_string('genericfontfamily', 'skin'),
            'defaultvalue' => 'sans-serif',
            'options' => array(
                'serif' => 'serif',
                'sans-serif' => 'sans-serif',
                'monospace' => 'monospace',
                'cursive' => 'cursive',
                'fantasy' => 'fantasy',
            ),
            'rules'   => array('required' => true),
        ),
        'uploadinstructions' => array(
            'title' => '',
            'type' => 'html',
            'value' => get_string('fontuploadinstructions', 'skin'),
        ),
        'zipfontfiles' => array(
            'type' => 'fieldset',
            'class' => 'zipfile',
            'legend' => get_string('zipfontfiles', 'skin'),
            'elements' => array(
                'fontfileZip' => array(
                    'type' => 'file',
                    'title' => get_string('fontfilezip', 'skin'),
                    'description' => get_string('zipdescription', 'skin'),
                ),
            )
        ),
        'fontfiles' => array(
            'type' => 'fieldset',
            'class' => 'individualfiles form-condensed',
            'legend' => get_string('fontfiles', 'skin'),
            'elements' => array(
                'fontfileEOT' => array(
                    'type' => 'file',
                    'title' => get_string('fontfileeot', 'skin'),
                    'description' => get_string('eotdescription', 'skin'),
                ),
                'fontfileSVG' => array(
                    'type' => 'file',
                    'title' => get_string('fontfilesvg', 'skin'),
                    'description' => get_string('svgdescription', 'skin'),
                ),
                'fontfileTTF' => array(
                    'type' => 'file',
                    'title' => get_string('fontfilettf', 'skin'),
                    'description' => get_string('ttfdescription', 'skin'),
                ),
                'fontfileWOFF' => array(
                    'type' => 'file',
                    'title' => get_string('fontfilewoff', 'skin'),
                    'description' => get_string('woffdescription', 'skin'),
                ),
                'fontfilelicence' => array(
                    'type' => 'file',
                    'title' => get_string('fontfilelicence', 'skin'),
                ),
            )
        ),
        'submit' => array(
            'type' => 'submitcancel',
            'class' => 'btn-primary',
            'value' => array(get_string('installfont', 'skin'), get_string('cancel', 'mahara')),
            'goto' => get_config('wwwroot') . 'admin/site/fonts.php',
        ),
    ),
));

$inlinejs = <<<EOF

jQuery(function($) {
    $('#addfontform_fontfileZip').change(function() {
        // need to hide the 'font files' fieldset
        $('fieldset.individualfiles').hide(500);
    });
    $('fieldset.individualfiles input').each(function() {
        $(this).change(function() {
            // need to hide the 'zip archive' fieldset
            $('fieldset.zipfile').hide(500);
        });
    });
});

EOF;

$smarty = smarty(array('tablerenderer'));
$smarty->assign('form', $form);
$smarty->assign('PAGEHEADING', hsc(TITLE));
$smarty->assign('INLINEJAVASCRIPT', $inlinejs);
$smarty->display('form.tpl');


function addfontform_validate(Pieform $form, $values) {
    global $USER, $SESSION;
    require_once('file.php');
    require_once('uploadmanager.php');

    $foldername = preg_replace(Skin::FONTNAME_FILTER_CHARACTERS, '', $values['fonttitle']);
    if (!$foldername) {
        $form->set_error('fonttitle', get_string('invalidfonttitle', 'skin'));
    }

    // If we are uploading a zip file we need to extract things before we can validate them
    if (!empty($values['fontfileZip'])) {
        safe_require('artefact', 'file');
        $ziptypes = PluginArtefactFile::get_mimetypes_from_description('zip');
        $zipmimetype = file_mime_type($values['fontfileZip']['name']);
        $zipmimetype = $zipmimetype || (substr($values['fontfileZip']['name'], -4) == '.zip' ? 'application/zip' : null);
        if (in_array($zipmimetype, $ziptypes)) {
            // we are dealing with a zip file
            // First pass it through the virus checker
            $um = new upload_manager('fontfileZip');
            if ($error = $um->preprocess_file()) {
                $form->set_error('fontfileZip', $error);
            }
            $zip = new ZipArchive();
            if ($zip->open($values['fontfileZip']['tmp_name'])) {
                $check = uploadfiles_info();
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $fontname = dirname($zip->getNameIndex($i));
                    $filename = basename($zip->getNameIndex($i));
                    if (empty($fontname) || $fontname == '.') {
                        $fontname = substr($values['fontfileZip']['name'], 0, -1 * strlen('.zip'));
                    }
                    // Check that all the needed files exist in the zip file
                    foreach ($check as $key => $item) {
                        if (end(explode('.', $zip->getNameIndex($i))) == $item['suffix']) {
                            $check[$key]['found'] = true;
                        }
                    }
                }
                // now examine our $check array to make sure at least one of each of the required files was found
                foreach ($check as $key => $item) {
                    if ($item['required'] == true && $item['found'] == false) {
                        $form->set_error('fontfileZip', get_string('fontfilemissing', 'skin', $item['suffix']));
                    }
                }
            }
            else {
                $form->set_error('fontfileZip', get_string('archivereadingerror', 'skin'));
            }
        }
        else {
            $form->set_error('fontfileZip', get_string('notvalidzipfile', 'skin'));
        }
    }
    else {
        foreach (uploadfiles_info() as $inputname => $details) {
            $um = new upload_manager($inputname, false, null, !$details['required']);
            if ($error = $um->preprocess_file()) {
                $form->set_error($inputname, $error);
            }
            if (!$um->optionalandnotsupplied && $details['suffix']) {
                $reqext = ".{$details['suffix']}";
                $fileext = substr($values[$inputname]['name'], (-1 * strlen($reqext)));
                if ($fileext <> $reqext) {
                    $form->set_error($inputname, get_string('notvalidfontfile', 'skin', strtoupper($details['suffix'])));
                }
            }
        }
    }
}

function addfontform_submit(Pieform $form, $values) {
    global $USER, $SESSION;

    $foldername = preg_replace(Skin::FONTNAME_FILTER_CHARACTERS, '', $values['fonttitle']);
    // Assign a new name, if the font with the same name already exists...
    $foldername = Skin::new_font_name($foldername);
    $fontpath = get_config('dataroot') . 'skins/fonts/' . $foldername . '/';
    check_dir_exists($fontpath, true, true);

    // If we are uploading a zip file
    if (!empty($values['fontfileZip'])) {
        safe_require('artefact', 'file');
        $zip = new ZipArchive();
        if ($zip->open($values['fontfileZip']['tmp_name'])) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $fontname = dirname($zip->getNameIndex($i));
                $filename = basename($zip->getNameIndex($i));
                if (empty($fontname) || $fontname == '.') {
                    $fontname = substr($values['fontfileZip']['name'], 0, -1 * strlen('.zip'));
                }
                // Check that all the needed files exist in the zip file
                $check = uploadfiles_info();
                foreach ($check as $key => $item) {
                    if (end(explode('.', $zip->getNameIndex($i))) == $item['suffix']) {
                        // Extract font file
                        $zip->extractTo($fontpath, $zip->getNameIndex($i));
                        $values['fontfile' . strtoupper($item['suffix'])]['name'] = $zip->getNameIndex($i);
                    }
                }
            }
        }
    }

    // Get SVG id from SVG font file...
    $tempname = (!empty($values['fontfileZip'])) ? $fontpath . $values['fontfileSVG']['name'] : $values['fontfileSVG']['tmp_name'];
    $filename = $values['fontfileSVG']['name'];

    libxml_before(true);
    $xmlDoc = simplexml_load_string(file_get_contents($tempname));
    $svg_id = (string) $xmlDoc->defs->font->attributes()->id;
    libxml_after();

    // Insert new record with font data into 'skin_fonts' table in database...
    // $foldername equals (only alphanumerical) font name, e.g. 'Nimbus Roman No.9' -> 'NimbusRomanNo9'
    // $foldername is also used as primary key in 'skin_fonts' table.
    switch ($values['fontstyle']) {
        case 'regular':
            $font_variant = 'regular';
            $font_weight = 'normal';
            $font_style = 'normal';
            break;
        case 'bold':
            $font_variant = 'bold';
            $font_weight = 'bold';
            $font_style = 'normal';
            break;
        case 'italic':
            $font_variant = 'italic';
            $font_weight = 'normal';
            $font_style = 'italic';
            break;
        case 'bolditalic':
            $font_variant = 'bolditalic';
            $font_weight = 'bold';
            $font_style = 'italic';
            break;
    }
    $variantdata = array(
        'variant' => $font_variant,
        'EOT' => $values['fontfileEOT']['name'],
        'SVG' => $values['fontfileSVG']['name'],
        'SVGid' => $svg_id,
        'TTF' => $values['fontfileTTF']['name'],
        'WOFF' => $values['fontfileWOFF']['name'],
        'font-weight' => $font_weight,
        'font-style' => $font_style,
    );

    // We'll create the database record before we copy the files over, so that
    // Mahara will know about this font in order to be able to delete its contents
    // from the filesystem if something goes wrong.
    ensure_record_exists(
        'skin_fonts',
        (object) array('name' => $foldername),
        (object) array(
            'name' => $foldername,
            'title' => $values['fonttitle'],
            'licence' => $values['fontfilelicence']['name'],
            'notice' => $values['fontnotice'],
            'previewfont' => $values['fontfileTTF']['name'],
            'variants' => serialize(array($font_variant => $variantdata)),
            'fonttype' => 'site',
            'onlyheading' => ($values['fonttype'] == 'heading' ? 1 : 0),
            'fontstack' => '\'' . escape_css_string($values['fonttitle']) . '\'',
            'genericfont' => $values['genericfont'],
        )
    );

    if (empty($values['fontfileZip'])) {
        // Copy SVG font file to folder...
        $tempname = $values['fontfileSVG']['tmp_name'];
        $filename = $values['fontfileSVG']['name'];
        move_uploaded_file($tempname, $fontpath.$filename);

        // Copy EOT font file.
        $tempname = $values['fontfileEOT']['tmp_name'];
        $filename = $values['fontfileEOT']['name'];
        move_uploaded_file($tempname, $fontpath.$filename);

        // Copy TTF font file to folder...
        $tempname = $values['fontfileTTF']['tmp_name'];
        $filename = $values['fontfileTTF']['name'];
        move_uploaded_file($tempname, $fontpath.$filename);

        // Copy WOFF font file to folder...
        $tempname = $values['fontfileWOFF']['tmp_name'];
        $filename = $values['fontfileWOFF']['name'];
        move_uploaded_file($tempname, $fontpath.$filename);

        // Copy optional font licence file to folder, if it exists...
        if (!empty($values['fontfilelicence'])) {
            $tempname = $values['fontfilelicence']['tmp_name'];
            $filename = $values['fontfilelicence']['name'];
            move_uploaded_file($tempname, $fontpath.$filename);
        }
    }

    $SESSION->add_ok_msg(get_string('fontinstalled', 'skin'));
    redirect('/admin/site/fonts.php');
}

function uploadfiles_info() {
    return array(
        'fontfileEOT' => array(
            'required' => true,
            'suffix' => 'eot',
            'found' => false,
        ),
        'fontfileSVG' => array(
            'required' => true,
            'suffix' => 'svg',
            'found' => false,
        ),
        'fontfileTTF' => array(
            'required' => true,
            'suffix' => 'ttf',
            'found' => false,
        ),
        'fontfileWOFF' => array(
            'required' => true,
            'suffix' => 'woff',
            'found' => false,
        ),
        'fontfilelicence' => array(
            'required' => false,
            'suffix' => 'txt',
            'found' => false,
        ),
    );
}
