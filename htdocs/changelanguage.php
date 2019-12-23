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
define('PUBLIC', 1);
require('init.php');

$lang = param_alphanumext('lang');
$languages = get_languages();
if (!isset($languages[$lang])) {
    throw new ParameterException('Unknown language');
}
if ($USER->is_logged_in()) {
    $USER->set_account_preference('lang', $lang);
}
$SESSION->set('lang', $lang);
if (isset($_SERVER['HTTP_REFERER'])) {
    redirect($_SERVER['HTTP_REFERER']);
}
redirect();


