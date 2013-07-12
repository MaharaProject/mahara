<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2010 Catalyst IT Ltd and others; see:
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
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('file.php');

$family = param_variable('family');
$variant = param_variable('variant');
$format = param_variable('format');
if (!in_array($format, array('EOT', 'SVG', 'TTF', 'WOFF'))) {
    throw new NotFoundException();
}
$fontrec = get_record('skin_fonts', 'title', $family);
if (
        !$fontrec
        || !($variantlist = unserialize($fontrec->variants))
        || !isset($variantlist[$variant])
        || !isset($variantlist[$variant][$format])
) {
    throw new NotFoundException();
}
$filename = $variantlist[$variant][$format];
$fontpath = get_config('dataroot') . 'skins/fonts/' . $fontrec->name . '/' . $filename;
$options = array('forcedownload' => true);
serve_file($fontpath, $filename, pathinfo($fontpath, PATHINFO_EXTENSION), $options);
