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
define('INSTITUTIONALADMIN', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

$contentname = param_alpha('contentname');
$institution = param_alpha('institution', null);
if (empty($institution)) {
    $institution = 'mahara';
}
if (!$contentitem = get_record('site_content', 'name', $contentname, 'institution', $institution)) {
    // This might be a local site page (or otherwise missing) so we will try and load the 'mahara' version
    if (!$contentitem = get_record('site_content', 'name', $contentname, 'institution', 'mahara')) {
        json_reply('local', get_string('loadsitecontentfailed', 'admin', get_string($contentname, 'admin')));
    }
}
$pageusedefault = get_record('institution_config', 'institution', $institution, 'field', 'sitepages_' . $contentname);
$data = array(
    'contentname' => $contentname,
    'content'  => $contentitem->content,
    'pageusedefault' => (!empty($pageusedefault->value) && $pageusedefault->value == 'mahara') ? 1 : 0,
    'error'    => false,
    'message'  => false,
);
json_reply(false, $data);
