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
define('PUBLIC', 1);
define('NOCHECKPASSWORDCHANGE', 1);
require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
require_once(get_config('libroot') . 'skin.php');

if (!get_config('skins')) {
    throw new FeatureNotEnabledException();
}

// We use the font title as the "family" name for the font
$family = param_variable('family', false);
$stylesheet = '';
if (!empty($family)) {
    $fonts = explode('|', $family);
    foreach ($fonts as $font) {
        $fontname = get_field('skin_fonts', 'name', 'title', $font);
        $stylesheet .= Skin::get_css_font_face_from_font_name($fontname) . "\n";
    }
}

header('Content-type: text/css');
echo $stylesheet;
exit;
