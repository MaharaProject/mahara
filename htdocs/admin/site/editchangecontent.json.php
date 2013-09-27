<?php
/**
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

$contentname = param_alpha('contentname');

if (!$contentitem = get_record('site_content', 'name', $contentname)) {
    json_reply('local', get_string('loadsitecontentfailed', 'admin', get_string($contentname, 'admin')));
}

$data = array(
    'contentname' => $contentname,
    'content'  => $contentitem->content,
    'error'    => false,
    'message'  => false,
);
json_reply(false, $data);
