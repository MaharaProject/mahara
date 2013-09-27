<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('STAFF', 1);
require(dirname(dirname(__FILE__)).'/init.php');

$type = param_alphanumext('type');

if (preg_match('/^([a-z]*_)?(viewtypes|weekly)$/', $type) ||
    $type == 'institutions' || $type == 'grouptypes') {
    header('Content-type: ' . 'image/png');
    if (!get_config('nocache')) {
        $maxage = 3600;
        header('Expires: '. gmdate('D, d M Y H:i:s', time() + $maxage) .' GMT');
        header('Cache-Control: max-age=' . $maxage);
        header('Pragma: public');
    }

    readfile(get_config('dataroot') . 'images/' . $type . '.png');
    exit;
}
