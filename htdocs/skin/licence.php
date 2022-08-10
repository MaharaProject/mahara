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
define('PUBLIC', 1);
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('file.php');

if (!get_config('skins')) {
    throw new FeatureNotEnabledException();
}

$family = param_variable('family');
$fontrec = get_record('skin_fonts', 'title', $family);
if (
        !$fontrec
        || !$fontrec->licence
) {
    throw new NotFoundException();
}
$licencepath = get_config('dataroot') . 'skins/fonts/' . $fontrec->name . '/' . $fontrec->licence;
$options = array('forcedownload' => true);
serve_file($licencepath, $fontrec->licence, pathinfo($licencepath, PATHINFO_EXTENSION), $options);
