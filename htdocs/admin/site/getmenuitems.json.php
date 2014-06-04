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

$public = (int) param_boolean('public');

$result = array();

//$menuitems = get_records_array('site_menu','public',$public,'displayorder');
$menuitems = get_records_sql_array('
   SELECT
      s.*, a.title AS filename
   FROM {site_menu} s
      LEFT OUTER JOIN {artefact} a ON s.file = a.id
   WHERE
      s.public = ?
   ORDER BY s.displayorder', array($public));
$rows = array();
if ($menuitems) {
    foreach ($menuitems as $i) {
        $r = array();
        $r['id'] = $i->id;
        $r['name'] = $i->title;
        $safeurl = sanitize_url($i->url);
        if (empty($i->url) && !empty($i->file)) {
            $r['type'] = 'sitefile';
            $r['linkedto'] = get_config('wwwroot') . 'artefact/file/download.php?file=' . $i->file;
            $r['linktext'] = $i->filename;
            $r['file'] = $i->file;
        }
        else if ($safeurl == '') {
            $r['type'] = 'externallink';
            $r['linkedto'] = '';
            $r['linktext'] = strtoupper(get_string('badurl', 'admin')) .  ': ' . $i->url;
        }
        else if (!empty($i->url) && empty($i->file)) {
            $r['type'] = 'externallink';
            $r['linkedto'] = $safeurl;
            $r['linktext'] = $safeurl;
        }
        else {
            json_reply('local',get_string('loadmenuitemsfailed','admin'));
        }
        $rows[] = $r;
    }
}

$result['menuitems'] = array_values($rows);
$result['error'] = false;
$result['message'] = false;

json_headers();
echo json_encode($result);
