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
define('SECTION_PAGE', 'editfont');

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
define('TITLE', get_string('editfont', 'skin'));

if (!get_config('skins')) {
    throw new FeatureNotEnabledException();
}

$font = param_alphanum('font');
$fontdata = get_record('skin_fonts', 'name', $font);
if ($fontdata == false) {
    throw new AccessDeniedException("Font not found");
}

$form = pieform(array(
    'name' => 'editfontform',
    //'jsform' => true,
    'plugintype' => 'artefact',
    'pluginname' => 'skin',
    'elements' => array(
        'fontname' => array(
            'type' => 'hidden',
            'value' => $font,
        ),
        'fonttitle' => array(
            'type' => 'text',
            'title' => get_string('fontname', 'skin'),
            'defaultvalue' => (!empty($fontdata->title) ? $fontdata->title : null),
        ),
        'fontnotice' => array(
            'type' => 'text',
            'title' => get_string('fontnotice', 'skin'),
            'description' => get_string('fontnoticedescription', 'skin'),
            'defaultvalue' => (!empty($fontdata->notice) ? $fontdata->notice : null),
        ),
        'fonttype' => array(
            'type' => 'radio',
            'title' => get_string('fonttype', 'skin'),
            'defaultvalue' => (!empty($fontdata->onlyheading) && ($fontdata->onlyheading == 1) ? 'heading' : 'text'),
            'options' => array(
                'text' => get_string('headingandtext', 'skin'),
                'heading' => get_string('headingonly', 'skin'),
            )
        ),
        'genericfont' => array(
            'type' => 'select',
            'title' => get_string('genericfontfamily', 'skin'),
            'defaultvalue' => (!empty($fontdata->genericfont) ? $fontdata->genericfont : 'sans-serif'),
            'options' => array(
                'serif' => 'serif',
                'sans-serif' => 'sans-serif',
                'monospace' => 'monospace',
                'cursive' => 'cursive',
                'fantasy' => 'fantasy',
            ),
            'rules'   => array('required' => true),
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


function editfontform_submit(Pieform $form, $values) {
    global $USER, $SESSION;

    update_record('skin_fonts', array(
        'title' => $values['fonttitle'],
        'notice' => $values['fontnotice'],
        'onlyheading' => ($values['fonttype'] == 'heading' ? 1 : 0),
        'fontstack' => '\'' . escape_css_string($values['fonttitle']) . '\'',
        'genericfont' => $values['genericfont']
    ), array('name' => $values['fontname']));

    $SESSION->add_ok_msg(get_string('fontedited', 'skin'));
    redirect('/admin/site/fonts.php');
}
