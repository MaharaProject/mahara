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
define('JSON', 1);
define('PUBLIC', 1);
define('NOSESSKEY', 1);

require(dirname(dirname(__FILE__)) . '/init.php');

json_headers();

$plugintype = param_alpha('plugintype');
$pluginname = param_alpha('pluginname');
$page       = param_alphanumext('page', null);
$section    = param_alphanumext('section', null);
$form       = param_alphanumext('form', null);
$element    = param_alphanumext('element', null);

$data = get_helpfile($plugintype, $pluginname, $form, $element, $page, $section);

if (empty($data)) {
    json_reply('local', get_string('nohelpfound'));
}

$json = array('error' => false, 'content' => $data);
json_reply(false, $json);
