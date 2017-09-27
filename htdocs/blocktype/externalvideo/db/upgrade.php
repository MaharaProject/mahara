<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-textbox
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

safe_require('blocktype', 'externalvideo');

function xmldb_blocktype_externalvideo_upgrade($oldversion=0) {
    if ($oldversion < 2014030500) {
        $urlpattern = '#\b(https?://)prezi\.com/bin/preziloader\.swf\?prezi_id=([a-z0-9]+)\b#';
        $matches = array();

        $sql = "SELECT id, configdata FROM {block_instance} WHERE blocktype='externalvideo'";
        $records = get_records_sql_array($sql, array());

        if ($records) {
            foreach ($records as $r) {
                $configdata = unserialize($r->configdata);

                if (isset($configdata['html'])) {
                    preg_match($urlpattern, $configdata['html'], $matches);
                }
                else if (isset($configdata['videoid'])) {
                    preg_match($urlpattern, $configdata['videoid'], $matches);
                }

                if (count($matches) >= 3) {
                    $newurl = $matches[1] . 'prezi.com/embed/' . $matches[2];
                    $width  = (!empty($configdata['width'])) ? $configdata['width'] : 0;
                    $height = (!empty($configdata['height'])) ? $configdata['height'] : 0;

                    $configdata['html'] = $configdata['videoid'] = PluginBlocktypeExternalvideo::iframe_code($newurl, $width, $height);
                    set_field('block_instance', 'configdata', serialize($configdata), 'id', $r->id);
                }
            }
        }

        ensure_record_exists('iframe_source_icon', (object) array('name' => 'Prezi', 'domain' => 'prezi.com'), (object) array('name' => 'Prezi', 'domain' => 'prezi.com'));
        ensure_record_exists('iframe_source', (object) array('prefix' => 'prezi.com/embed/', 'name' => 'Prezi'), (object) array('prefix' => 'prezi.com/embed/', 'name' => 'Prezi'));
        update_safe_iframe_regex();
    }

    if ($oldversion < 2017092500) {
        ensure_record_exists('iframe_source_icon', (object) array('name' => 'Youtube [privacy mode]', 'domain' => 'www.youtube.com'), (object) array('name' => 'Youtube [privacy mode]', 'domain' => 'www.youtube.com'));
        ensure_record_exists('iframe_source', (object) array('prefix' => 'www.youtube-nocookie.com/embed/', 'name' => 'Youtube [privacy mode]'), (object) array('prefix' => 'www.youtube-nocookie.com/embed/', 'name' => 'Youtube [privacy mode]'));
        update_safe_iframe_regex();
    }

    return true;
}
