<?php
/**
 *
 * @package    mahara
 * @subpackage skin
 * @author     Gregor Anzelj
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
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
$fontlicence = '';
$fonttitle = '';
$stylesheet = '';
if (!empty($font)) {
    $stylesheet = Skin::get_css_font_face_from_font_name($font);
    $fontdata = get_record('skin_fonts', 'name', $font);
    $fonttitle = $fontdata->title;
    if (!empty($fontdata->licence) && !is_null($fontdata->licence)) {
        if (preg_match('/^t_(.*)/', $fontdata->fonttype, $matches)) {
            $fontpath = 'theme/' . $matches[1] . '/fonts/' . strtolower($fontdata->name) . '/' . $fontdata->licence;
        }
        else {
            $fontpath = 'skins/fonts/' . $fontdata->name . '/' . $fontdata->licence;
        }

        if (!file_exists(get_config('docroot') . $fontpath)) {
            // Try the dataroot
            if (file_exists(get_config('dataroot') . 'skins/fonts/' . $fontdata->name . '/' . $fontdata->licence)) {
                $fontpath = 'skin/licence.php?family=' . $fontdata->title;
            }
        }
        $fontlicence = '<a href="' . get_config('wwwroot') . $fontpath . '">' . get_string('fontlicence', 'skin') . '</a>';
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

json_reply(false, array(
    'message' => null,
    'data' => array(
        'font' => $font,
        'html' => $specimen
    )
));
