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

if (!get_config('skins')) {
    throw new FeatureNotEnabledException();
}

$font = param_alphanumext('font');

$fontdata = null;
$fontdata = get_record('skin_fonts', 'name', $font);
if ($fontdata == false) {
    throw new AccessDeniedException("Font not found");
}
// Admins can not delete 'common' fonts
if ($fontdata->fonttype == 'common') {
    throw new AccessDeniedException("Cannot delete this font");
}

define('TITLE', get_string('deletespecifiedfont', 'skin', $fontdata->title));

// Check to see if the font is being used in a skin and if so indicate this to admin
$usedinskins = 0;
$skins = get_records_array('skin');
if (is_array($skins)) {
    foreach ($skins as $skin) {
        $options = unserialize($skin->viewskin);
        foreach ($options as $key => $option) {
            if (preg_match('/font_family/', $key) && $option == $fontdata->name) {
                $usedinskins ++;
            }
        }
    }
}
$submittitle = get_string('deletefontconfirm1', 'skin') . (($usedinskins) ? get_string('deletefontconfirmused', 'skin', $usedinskins) : ' ') . get_string('deletefontconfirm2', 'skin');
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
            'title' => $submittitle,
            'class' => 'btn-primary',
            'value' => array(get_string('yes'), get_string('no')),
            'goto' => get_config('wwwroot') . 'admin/site/fonts.php',
        )
    ),
));

$smarty = smarty();
setpageicon($smarty, 'icon-text-width');
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
        // Check to see if the font is being used in a skin. If it is remove it from
        // the skin's viewskin data
        $skins = get_records_array('skin');
        if (is_array($skins)) {
            foreach ($skins as $skin) {
                $options = unserialize($skin->viewskin);
                foreach ($options as $key => $option) {
                    if (preg_match('/font_family/', $key) && $option == $fontname) {
                        require_once(get_config('docroot') . 'lib/skin.php');
                        $skinobj = new Skin($skin->id);
                        $viewskin = $skinobj->get('viewskin');
                        $viewskin[$key] = 'Arial'; // the default font
                        $skinobj->set('viewskin', $viewskin);
                        $skinobj->commit();
                    }
                }
            }
        }

        // Also delete all the files in the appropriate folder and the folder itself...
        $fontpath = get_config('dataroot') . 'skins/fonts/' . $fontname;
        recurse_remove_dir($fontpath);
        $SESSION->add_ok_msg(get_string('fontdeleted', 'skin'));
    }
    redirect('/admin/site/fonts.php');
}

/**
 * Loops through a directory recursively and removes the files and subdirectories
 *
 * @param   string  $dir    The directory to remove
 *
 * @return  bool
 */
function recurse_remove_dir($dir) {

    if (!is_dir($dir)) {
        return false;
    }
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? recurse_remove_dir("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
}
