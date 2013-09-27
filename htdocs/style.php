<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Richard Mansfield
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
define('NOCHECKREQUIREDFIELDS', 1);
require('init.php');

header('Content-type: text/css');
if (!get_config('nocache')) {
    $maxage = 604800;
    header('Expires: '. gmdate('D, d M Y H:i:s', time() + $maxage) .' GMT');
    header('Cache-Control: max-age=' . $maxage);
    header('Pragma: public');
}

if ($style = param_integer('id', null)) {
    echo get_field('style', 'css', 'id', $style);
}

perf_to_log();
