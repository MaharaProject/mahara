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
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('file.php');

if (!get_config('skins')) {
    throw new FeatureNotEnabledException();
}

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
