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

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
require_once(get_config('libroot') . 'skin.php');
define('TITLE', get_string('sitefonts', 'skin'));

if (!get_config('skins')) {
    throw new FeatureNotEnabledException();
}

$font = param_variable('font', false);
if ($font) {
    $font = preg_replace(Skin::FONTNAME_FILTER_CHARACTERS, '', $font);
}
$fontdata = null;

if (!empty($font)) {
    $stylesheet = Skin::get_css_font_face_from_font_name($font);
    $fontdata = get_record('skin_fonts', 'name', $font);
    $fonttitle = $fontdata->title;
    if (!empty($fontdata->licence) && !is_null($fontdata->licence)) {
        $fontpath = get_config('wwwroot') . 'skins/fonts/' . $fontdata->name . '/';
        $fontlicence = '<a href="' . $fontpath . $fontdata->licence . '">' . get_string('fontlicence', 'skin') . '</a>';
    }
    else {
        $fontlicence = get_string('fontlicencenotfound', 'skin');
    }
}

$smarty = smarty();
$smarty->assign('fontname', $font);
// Prepend "font" to create a font CSS class name (in case the font name starts with numerals)
$smarty->assign('fontclass', "font{$font}");
$smarty->assign('stylesheet', $stylesheet);
$smarty->assign('fonttitle', $fonttitle);
$smarty->assign('fontlicence', $fontlicence);
$specimen = $smarty->fetch('skin/specimen.tpl');


header('Content-type: text/html');
echo $specimen;
exit;
