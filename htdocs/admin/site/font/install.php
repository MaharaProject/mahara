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
require_once('pieforms/pieform.php');
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
            'labelhtml' => get_string('fontname', 'skin'),
            'rules'   => array('required' => true),
        ),
        'fontnotice' => array(
            'type' => 'text',
            'labelhtml' => get_string('fontnotice', 'skin'),
            'description' => get_string('fontnoticedescription', 'skin'),
        ),
        'fontstyle' => array(
            'type' => 'select',
            'labelhtml' => get_string('fontstyle', 'skin'),
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
            'labelhtml' => get_string('fonttype', 'skin'),
            'defaultvalue' => 'text',
            'options' => array(
                'text' => get_string('headingandtext', 'skin'),
                'heading' => get_string('headingonly', 'skin'),
            ),
            'separator' => '<br />',
            'rules'   => array('required' => true),
        ),
        'genericfont' => array(
            'type' => 'select',
            'labelhtml' => get_string('genericfontfamily', 'skin'),
            'defaultvalue' => 'sans-serif',
            'options' => array(
                'serif' => 'serif',
                'sans-serif' => 'sans-serif',
                'monospace' => 'monospace',
                'cursive' => 'cursive',
                'fantasy' => 'fantasy',
            ),
            'separator' => '<br />',
            'rules'   => array('required' => true),
        ),
        'fontfiles' => array(
            'type' => 'fieldset',
            'legend' => get_string('fontfiles', 'skin'),
            'elements' => array(
                'fontfileEOT' => array(
                    'type' => 'file',
                    'labelhtml' => get_string('fontfileeot', 'skin'),
                    'description' => get_string('eotdescription', 'skin'),
                    'rules'   => array('required' => true),
                ),
                'fontfileSVG' => array(
                    'type' => 'file',
                    'labelhtml' => get_string('fontfilesvg', 'skin'),
                    'description' => get_string('svgdescription', 'skin'),
                    'rules'   => array('required' => true),
                ),
                'fontfileTTF' => array(
                    'type' => 'file',
                    'labelhtml' => get_string('fontfilettf', 'skin'),
                    'description' => get_string('ttfdescription', 'skin'),
                    'rules'   => array('required' => true),
                ),
                'fontfileWOFF' => array(
                    'type' => 'file',
                    'labelhtml' => get_string('fontfilewoff', 'skin'),
                    'description' => get_string('woffdescription', 'skin'),
                    'rules'   => array('required' => true),
                ),
                'fontfilelicence' => array(
                    'type' => 'file',
                    'labelhtml' => get_string('fontfilelicence', 'skin'),
                    'rules'   => array('required' => true),
                ),
            )
        ),
        'submit' => array(
            'type' => 'submitcancel',
            'value' => array(get_string('installfont', 'skin'), get_string('cancel', 'mahara')),
            'goto' => get_config('wwwroot') . 'admin/site/fonts.php',
        ),
    ),
));


$smarty = smarty(array('tablerenderer'));
$smarty->assign('form', $form);
$smarty->assign('PAGEHEADING', hsc(TITLE));
$smarty->display('form.tpl');


function addfontform_validate(Pieform $form, $values) {
    global $USER, $SESSION;
    require_once('file.php');
    require_once('uploadmanager.php');

    $foldername = preg_replace(Skin::FONTNAME_FILTER_CHARACTERS, '', $values['fonttitle']);
    if (!$foldername) {
        $form->set_error('fonttitle', get_string('invalidfonttitle', 'skin'));
    }

    $uploadfiles = array(
        'fontfileEOT' => array(
            'required' => true,
            'suffix' => 'eot'
        ),
        'fontfileSVG' => array(
            'required' => true,
            'suffix' => 'svg',
        ),
        'fontfileTTF' => array(
            'required' => true,
            'suffix' => 'ttf',
        ),
        'fontfileWOFF' => array(
            'required' => true,
            'suffix' => 'woff',
        ),
        'fontfilelicence' => array(
            'required' => false,
            'suffix' => false,
        ),
    );
    foreach ($uploadfiles as $inputname => $details) {
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

function addfontform_submit(Pieform $form, $values) {
    global $USER, $SESSION;

    $foldername = preg_replace(Skin::FONTNAME_FILTER_CHARACTERS, '', $values['fonttitle']);
    // Assign a new name, if the font with the same name already exists...
    $foldername = Skin::new_font_name($foldername);
    $fontpath = get_config('dataroot') . 'skins/fonts/' . $foldername . '/';
    check_dir_exists($fontpath, true, true);

    // Get SVG id from SVG font file...
    $tempname = $values['fontfileSVG']['tmp_name'];
    $filename = $values['fontfileSVG']['name'];
    $xmlDoc = simplexml_load_string(file_get_contents($tempname));
    $svg_id = (string) $xmlDoc->defs->font->attributes()->id;

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

    $SESSION->add_ok_msg(get_string('fontinstalled', 'skin'));
    redirect('/admin/site/fonts.php');
}
