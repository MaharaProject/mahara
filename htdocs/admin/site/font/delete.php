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
define('SECTION_PAGE', 'deletefont');

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
require_once('pieforms/pieform.php');

if (!get_config('skins')) {
    throw new FeatureNotEnabledException();
}

$font = param_alphanum('font');

$fontdata = null;
$fontdata = get_record('skin_fonts', 'name', $font);
if ($fontdata == false) {
    throw new AccessDeniedException("Font not found");
}
if ($fontdata->fonttype != 'site') {
    throw new AccessDeniedException("Cannot delete this font");
}

define('TITLE', get_string('deletespecifiedfont', 'skin', $fontdata->title));

$form = pieform(array(
    'name' => 'deletefontform',
    'autofocus' => false,
    'method' => 'post',
    'elements' => array(
        'font' => array(
            'type' => 'hidden',
            'value' => $font,
        ),
        'submit' => array(
            'type' => 'submitcancel',
            'title' => get_string('deletefontconfirm', 'skin'),
            'value' => array(get_string('yes'), get_string('no')),
            'goto' => get_config('wwwroot') . 'admin/site/fonts.php',
        )
    ),
));

$smarty = smarty();
$smarty->assign('PAGEHEADING', hsc(TITLE));
$smarty->assign('form', $form);
$smarty->display('form.tpl');

function deletefontform_submit(Pieform $form, $values) {
    global $SESSION;

    $fontname = $values['font'];
    $result = delete_records('skin_fonts', 'name', $fontname);
    if ($result == false) {
        $SESSION->add_error_msg(get_string('cantdeletefont', 'skin'));
    }
    else {
        // Also delete all the files in the appropriate folder and the folder itself...
        $fontpath = get_config('dataroot') . 'skins/fonts/' . $fontname . '/';
        if ($handle = opendir($fontpath)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    unlink($fontpath . $file);
                }
            }
            closedir($handle);
        }
        rmdir($fontpath);

        $SESSION->add_ok_msg(get_string('fontdeleted', 'skin'));
    }
    redirect('/admin/site/fonts.php');
}
