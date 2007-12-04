<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2007 Catalyst IT Ltd (http://www.catalyst.net.nz)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage blocktype-externalfeed
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeExternalfeed extends SystemBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.externalfeed');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.externalfeed');
    }

    public static function get_categories() {
        return array('feeds');
    }

    public static function render_instance(BlockInstance $instance) {
        $configdata = $instance->get('configdata');
        if ($configdata['feedid']) {
            $data = get_record('blocktype_externalfeed_data', 'id', $configdata['feedid']);
            unserialize($data->content);
            $smarty = smarty_core();
            $smarty->assign('title', $data->title);
            $smarty->assign('description', $data->description);
            $smarty->assign('link', $data->link);
            $smarty->assign('entries', unserialize($data->content));
            $smarty->assign('lastupdated', get_string('lastupdatedon', 'blocktype.externalfeed', format_date(time($data->lastupdate))));
            return $smarty->fetch('blocktype:externalfeed:feed.tpl');
        }
        return '';
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        $configdata = $instance->get('configdata');

        if (isset($configdata['feedid'])) {
            $url = get_field('blocktype_externalfeed_data', 'url', 'id', $configdata['feedid']);
        }
        else {
            $url = '';
        }

        return array(
            'url' => array(
                'type'  => 'text',
                'title' => get_string('feedlocation', 'blocktype.externalfeed'),
                'description' => get_string('feedlocationdesc', 'blocktype.externalfeed'),
                'width' => '90%',
                'defaultvalue' => $url,
                'rules' => array(
                    'required' => true,
                    'maxlength' => 255, // mysql hack, see install.xml for this plugin
                ),
            ),
        );
    }

    /**
     * Optional method. If exists, allows this class to decide the title for 
     * all blockinstances of this type
     */
    public static function get_instance_title(BlockInstance $bi) {
        $configdata = $bi->get('configdata');

        if (!empty($configdata['feedid'])) {
            if ($title = get_field('blocktype_externalfeed_data', 'title', 'id', $configdata['feedid'])) {
                return $title;
            }
        }
        return '';
    }

    public static function instance_config_validate(Pieform $form, $values) {
        if (strpos($values['url'], '://') == false) {
            // try add on http://
            $values['url'] = 'http://' . $values['url'];
        }
        else {
            $proto = substr($values['url'], 0, strpos($values['url'], '://'));
            if (!in_array($proto, array('http', 'https'))) {
                $form->set_error('url', get_string('invalidurl', 'blocktype.externalfeed'));
            }
        }
        if (!$form->get_error('url') && !record_exists('blocktype_externalfeed_data', 'url', $values['url']) && !self::parse_feed($values['url'])) {
            $form->set_error('url', get_string('invalidurl', 'blocktype.externalfeed'));
        }
    }

    public static function instance_config_save($values) {
        // we need to turn the feed url into an id in the feed_data table..
        if (strpos($values['url'], '://') == false) {
            // try add on http://
            $values['url'] = 'http://' . $values['url'];
        }
        if ($exists = get_record('blocktype_externalfeed_data', 'url', $values['url'])) {
            $values['feedid'] = $exists->id;
            unset($values['url']);
            return $values;
        }
        $data = self::parse_feed($values['url']);
        $data->content  = serialize($data->content);
        $data->lastupdate = db_format_timestamp(time());
        $id = ensure_record_exists('blocktype_externalfeed_data', array('url' => $values['url']), $data, 'id', true);
        $values['feedid'] = $id;
        unset($values['url']);
        return $values;

    }

    public static function get_cron() {
        $refresh = new StdClass;
        $refresh->callfunction = 'refresh_feeds';
        $refresh->hour = '*';
        $refresh->minute = '0';

        $cleanup = new StdClass;
        $cleanup->callfunction = 'cleanup_feeds';
        $cleanup->hour = '3';
        $cleanup->minute = '0';

        return array($refresh, $cleanup);

    }

    public static function refresh_feeds() {
        if (!$feeds = get_records_select_array('blocktype_externalfeed_data', 
            'lastupdate < ?', array(db_format_timestamp(strtotime('-30 minutes'))))) {
            return;
        }
        foreach ($feeds as $feed) {
            $data = self::parse_feed($feed->url);
            $data->id = $feed->id;
            $data->lastupdate = db_format_timestamp(time());
            $data->content = serialize($data->content);
            update_record('blocktype_externalfeed_data', $data);
        }
    }

    public static function cleanup_feeds() {
        $ids = array();
        if ($instances = get_records_array('block_instance', 'blocktype', 'externalfeed')) {
            foreach ($instances as $block) {
                $data = unserialize($block->configdata);
                $ids[$data['feedid']] = true;
            }
        }
        if (count($ids) == 0) {
            delete_records('blocktype_externalfeed_data'); // just delete it all 
            return;
        }
        $usedids = implode(', ', array_map('db_quote', array_keys($ids)));
        delete_records_select('blocktype_externalfeed_data', 'id NOT IN ( ' . $usedids . ' )');
    }


    public static function parse_feed($source) {

        static $cache;
        if (empty($cache)) {
            $cache = array();
        }
        if (array_key_exists($source, $cache)) {
            return $cache[$source];
        }

        require_once('XML/Feed/Parser.php');
        require_once('snoopy/Snoopy.class.php');

        $snoopy = new Snoopy();
        $success = true;

        if (!$snoopy->fetch($source)) {
            $cache[$source] = false;
            return false;
        }

        try {
            $feed = new XML_Feed_Parser($snoopy->results, false, true, false);
        }
        catch (XML_Feed_Parser_Exception $e) {
            $cache[$source] = false;
            return false;
        }
        
        $data = new StdClass;
        $data->title = $feed->title;
        $data->url = $source;
        $data->link = $feed->link;
        $data->description = $feed->description;
        $data->content = array();
        foreach ($feed as $count => $item) {
            if ($count == 11) { // it starts at one!
                break;
            }
            $data->content[] = (object)array('title' => $item->title, 'link' => $item->link);
        }
        $cache[$source] = $data;
        return $data;
    }
}

?>
