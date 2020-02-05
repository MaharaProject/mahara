<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-groupviews
 * @author     Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('docroot') . 'blocktype/lib.php');
require_once(get_config('docroot') . 'blocktype/courseinfo/lib.php');

$offset = param_integer('offset', 0);
$limit = param_integer('limit', 10);

if ($blockid = param_integer('block', null)) {
    $bi = new BlockInstance($blockid);
    $owner = $bi->get_view()->get('owner');
    if ($owner) {
        $options = $configdata = $bi->get('configdata');
        $configdata['ownerid'] = $owner;

        $courses = PluginBlocktypeCourseinfo::get_data($configdata, $offset, $limit);
        $template = 'blocktype:courseinfo:courserows.tpl';
        $baseurl = $bi->get_view()->get_url();
        $baseurl .= ((false === strpos($baseurl, '?')) ? '?' : '&') . 'block=' . $blockid;
        $pagination = array(
            'baseurl'    => $baseurl,
            'id'         => 'block' . $blockid . '_pagination',
            'datatable'  => 'coursedata_' . $blockid,
            'jsonscript' => 'blocktype/courseinfo/courses.json.php',
        );

        PluginBlocktypeCourseinfo::render_courses($courses, $template, $options, $pagination);
        json_reply(false, (object) array('message' => false, 'data' => $courses));
    }
    else {
        json_reply(true, get_string('accessdenied', 'error'));
    }
}
else {
    json_reply(true, get_string('accessdenied', 'error'));
}
