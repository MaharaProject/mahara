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
define('SECTION_PAGE', 'addfontvariant');

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
require_once(get_config('libroot') . 'skin.php');
define('TITLE', get_string('addfontvariant', 'skin'));

if (!get_config('skins')) {
    throw new FeatureNotEnabledException();
}

$font = param_alphanum('font');
$fonttitle = get_field('skin_fonts', 'title', 'name', $font);

$form = pieform(array(
    'name' => 'addvariantform',
    //'jsform' => true,
    'plugintype' => 'artefact',
    'pluginname' => 'skin',
    'elements' => array(
        'fontname' => array(
            'type' => 'hidden',
            'value' => $font,
        ),
        'fonttitle' => array(
            'type' => 'html',
            'labelhtml' => get_string('fontname', 'skin'),
            'value' => $fonttitle,
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
        'fontfiles' => array(
            'type' => 'fieldset',
            'legend' => get_string('fontfiles', 'skin'),
            'elements' => array(
                'fontfileEOT' => array(
                    'type' => 'file',
                    'title' => get_string('fontfileeot', 'skin'),
                    'description' => get_string('eotdescription', 'skin'),
                    'rules'   => array('required' => true),
                ),
                'fontfileSVG' => array(
                    'type' => 'file',
                    'title' => get_string('fontfilesvg', 'skin'),
                    'description' => get_string('svgdescription', 'skin'),
                    'rules'   => array('required' => true),
                ),
                'fontfileTTF' => array(
                    'type' => 'file',
                    'title' => get_string('fontfilettf', 'skin'),
                    'description' => get_string('ttfdescription', 'skin'),
                    'rules'   => array('required' => true),
                ),
                'fontfileWOFF' => array(
                    'type' => 'file',
                    'title' => get_string('fontfilewoff', 'skin'),
                    'description' => get_string('woffdescription', 'skin'),
                    'rules'   => array('required' => true),
                ),
            )
        ),
        'submit' => array(
            'type' => 'submitcancel',
            'value' => array(get_string('save', 'mahara'), get_string('cancel', 'mahara')),
            'goto' => get_config('wwwroot') . 'admin/site/fonts.php',
        ),
    ),
));


$smarty = smarty(array('tablerenderer'));
$smarty->assign('form', $form);
$smarty->assign('PAGEHEADING', hsc(TITLE));
$smarty->display('form.tpl');

// TODO: Consolidate portions of this with addfontform_validate in install.php, to reduce
// code duplication.
function addvariantform_validate(Pieform $form, $values) {
    global $USER, $SESSION;
    require_once('file.php');
    require_once('uploadmanager.php');

    // Make sure they didn't hack the hidden variable to have the name of
    // a font that doesn't exist
    if (!record_exists('skin_fonts', 'name', $values['fontname'])) {
        $form->set_error('fontname', get_string('nosuchfont', 'skin'));
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
    );
    foreach ($uploadfiles as $inputname => $details) {
        $um = new upload_manager($inputname, false, null, $details['required']);
        if ($error = $um->preprocess_file()) {
            $form->set_error($inputname, $error);
        }
        if ($details['suffix']) {
            $reqext = ".{$details['suffix']}";
            $fileext = substr($values[$inputname]['name'], (-1 * strlen($reqext)));
            if ($fileext <> $reqext) {
                $form->set_error($inputname, get_string('notvalidfontfile', 'skin', strtoupper($details['suffix'])));
            }
        }
    }
}

// TODO: Consolidate this with addfontform_submit() in font/install.php to reduce
// code duplication. It's just different enough that it's non-trivial to combine them.
function addvariantform_submit(Pieform $form, $values) {
    global $USER, $SESSION;

    // We know the font name is valid (and therefore a directory for it exists) because of the
    // previous validation that there's a record with that font name
    // But just to be extra careful...
    $foldername = preg_replace(Skin::FONTNAME_FILTER_CHARACTERS, "", $values['fontname']);
    $fontpath = get_config('dataroot') . 'skins/fonts/' . $foldername . '/';
    check_dir_exists($fontpath, true, true);

    // Get SVG id from SVG font file...
    $tempname = $values['fontfileSVG']['tmp_name'];
    $filename = $values['fontfileSVG']['name'];
    libxml_before(true);
    $xmlDoc = simplexml_load_string(file_get_contents($tempname));
    $svg_id = (string) $xmlDoc->defs->font->attributes()->id;
    libxml_after();

    // Update variants column in database record...
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

    $fontvariants = unserialize(get_field('skin_fonts', 'variants', 'name', $values['fontname']));
    $fontvariants[$font_variant] = array($variantdata);

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

    // Hold off on updating the DB until we've successfully uploaded all the files
    update_record('skin_fonts', array('variants' => serialize($fontvariants)), array('name' => $values['fontname']));

    $SESSION->add_ok_msg(get_string('fontvariantadded', 'skin'));
    redirect('/admin/site/fonts.php');
}
