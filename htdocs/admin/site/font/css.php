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
if (!empty($family)) {
    $stylesheet = '';
    $fonts = explode('|', $family);
    foreach ($fonts as $font) {
        $fontname = get_field('skin_fonts', 'name', 'title', $font);
        $stylesheet .= Skin::get_css_font_face_from_font_name($fontname) . "\n";
    }
}
else {

    $stylesheet = <<< EOF
#fontlist ul.actionlist {
    margin: 0;
    width: 200px;
}
#fontlist ul.actionlist li {
    list-style: none;
    margin: 1px 0 2px 0;
    line-height: 1.25em;
}
#fontlist ul.actionlist li a {
    border: 1px solid #DADADA;
    -webkit-border-radius: 5px;
    -moz-border-radius: 5px;
    border-radius: 5px;
    padding: 7px 5px 7px 25px;
    display: block;
    color: #333333;
    text-decoration: none;
    background-color: #F7F7F7;
    background-position: 7px 50%;
    background-repeat: no-repeat;
}
#fontlist ul.actionlist li a:hover {
    background-color: #ffffff;
    color: #000000;
}
#fontlist ul.actionlist li a:active {
    background-color: #989898;
    color: #FFFFFF;
    opacity: 0.9;
    border: 1px solid #e3e3e3;
}
#fontlist ul.actionlist li:first-child {
    font-weight: bold;
    padding: 5px 0 3px 0;
}
#preview {
    margin: 10px 0;
}

EOF;
}

header('Content-type: text/css');
echo $stylesheet;
exit;
