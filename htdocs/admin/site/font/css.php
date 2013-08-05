<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage skin
 * @author     Gregor Anzelj
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
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
/* table with gap between rows */
#fontlist td {
    border-bottom: 2px solid #FFF;
    font-size: .9167em;
}
#fontlist ul.actionlist {
    margin: 0 0 0 10px;
    float: right;
    width: 180px;
    font-size: 1em;
}
#fontlist ul.actionlist li {
    list-style: none;
    margin: 0;
    border-top: 1px solid #d1d1d1;
    padding: 2px 3px;
    line-height: 1.2em;
}
#fontlist ul.actionlist li:first-child {
    border-top: none;
}

EOF;
}

header('Content-type: text/css');
echo $stylesheet;
exit;
