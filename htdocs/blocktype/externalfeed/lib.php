<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
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
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

require_once('XML/Feed/Parser.php');

class PluginBlocktypeExternalfeed extends SystemBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.externalfeed');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.externalfeed');
    }

    public static function get_categories() {
        return array('external');
    }

    public static function postinst($prevversion) {
        if ($prevversion == 0) {
            // MySQL can't handle uniqueness of > 255 chars
            if (is_postgres()) {
                execute_sql('CREATE UNIQUE INDEX {blocextedata_url_uix} ON {blocktype_externalfeed_data}(url);');
            }
            else if (is_mysql()) {
                execute_sql('ALTER TABLE {blocktype_externalfeed_data} ADD UNIQUE {blocextedata_url_uix} (url(255))');
            }
            else {
                // TODO: support other databases
            }
        }
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        $configdata = $instance->get('configdata');
        if (!empty($configdata['feedid'])) {

            $data = $instance->get_data('feed', $configdata['feedid']);

            $data->content = unserialize($data->content);
            $data->image   = unserialize($data->image);

            // only keep the number of entries the user asked for
            $chunks = array_chunk($data->content, isset($configdata['count']) ? $configdata['count'] : 10);
            $data->content = $chunks[0];

            // Attempt to fix relative URLs in the feeds
            if (!empty($data->image['link'])) {
                $data->description = preg_replace(
                    '/src="(\/[^"]+)"/',
                    'src="' . $data->image['link'] . '$1"',
                    $data->description
                );
                foreach ($data->content as &$entry) {
                    $entry->description = preg_replace(
                        '/src="(\/[^"]+)"/',
                        'src="' . $data->image['link'] . '$1"',
                        $entry->description
                    );
                }
            }

            $smarty = smarty_core();
            $smarty->assign('title', $data->title);
            $smarty->assign('description', $data->description);
            $smarty->assign('url', $data->url);
            // 'full' won't be set for feeds created before 'full' support was added
            $smarty->assign('full', isset($configdata['full']) ? $configdata['full'] : false); 
            $smarty->assign('link', $data->link);
            $smarty->assign('entries', $data->content);
            $smarty->assign('feedimage', self::make_feed_image_tag($data->image));
            $smarty->assign('lastupdated', get_string('lastupdatedon', 'blocktype.externalfeed', format_date($data->lastupdate)));
            return $smarty->fetch('blocktype:externalfeed:feed.tpl');
        }
        return '';
    }

    // Called by $instance->get_data('feed', ...).
    public static function get_instance_feed($id) {
        return get_record(
            'blocktype_externalfeed_data', 'id', $id, null, null, null, null,
            'id,url,link,title,description,content,' . db_format_tsfield('lastupdate') . ',image'
        );
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        $configdata = $instance->get('configdata');

        if (!empty($configdata['feedid'])) {
            $url = $instance->get_data('feed', $configdata['feedid'])->url;
        }
        else {
            $url = '';
        }

        if (isset($configdata['full'])) {
            $full = $configdata['full'];
        }
        else {
            $full = false;
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
                    'maxlength' => 2048, // See install.xml for this plugin - MySQL can only safely handle up to 255 chars
                ),
            ),
            'count' => array(
                'type' => 'text',
                'title' => get_string('itemstoshow', 'blocktype.externalfeed'),
                'description' => get_string('itemstoshowdescription', 'blocktype.externalfeed'),
                'defaultvalue' => isset($configdata['count']) ? $configdata['count'] : 10,
                'size' => 3,
                'minvalue' => 1,
                'maxvalue' => 20,
            ),
            'full' => array(
                'type'         => 'checkbox',
                'title'        => get_string('showfeeditemsinfull', 'blocktype.externalfeed'),
                'description'  => get_string('showfeeditemsinfulldesc', 'blocktype.externalfeed'),
                'defaultvalue' => (bool)$full,
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
            if ($title = $bi->get_data('feed', $configdata['feedid'])->title) {
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
        if (!$form->get_error('url') && !record_exists('blocktype_externalfeed_data', 'url', $values['url'])) {
            try {
                self::parse_feed($values['url']);
                return;
            }
            catch (XML_Feed_Parser_Exception $e) {
                $form->set_error('url', get_string('invalidfeed', 'blocktype.externalfeed',  $e->getMessage()));
            }
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
        // We know this is safe because self::parse_feed caches its result and 
        // the validate method would have failed if the feed was invalid
        $data = self::parse_feed($values['url']);
        $data->content  = serialize($data->content);
        $data->image    = serialize($data->image);
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
        $cleanup->minute = '30';

        return array($refresh, $cleanup);

    }

    public static function refresh_feeds() {
        if (!$feeds = get_records_select_array('blocktype_externalfeed_data', 
            'lastupdate < ?', array(db_format_timestamp(strtotime('-30 minutes'))),
            '', 'id,url,' . db_format_tsfield('lastupdate', 'tslastupdate'))) {
            return;
        }
        $yesterday = time() - 60*60*24;
        foreach ($feeds as $feed) {
            // Hack to stop the updating of dead feeds from delaying other
            // more important stuff that runs on cron.
            if (defined('CRON') && $feed->tslastupdate < $yesterday) {
                // We've been trying for 24 hours already, so waste less
                // time on this one and just try it once a day
                if (mt_rand(0, 24) != 0) {
                    continue;
                }
            }
            try {
                $data = self::parse_feed($feed->url);
                $data->id = $feed->id;
                $data->lastupdate = db_format_timestamp(time());
                $data->content = serialize($data->content);
                $data->image   = serialize($data->image);
                update_record('blocktype_externalfeed_data', $data);
            }
            catch (XML_Feed_Parser_Exception $e) {
                // The feed must have changed in such a way as to become 
                // invalid since it was added. We ignore this case in the hope 
                // the feed will become valid some time later
            }
        }
    }

    public static function cleanup_feeds() {
        $ids = array();
        if ($instances = get_records_array('block_instance', 'blocktype', 'externalfeed')) {
            foreach ($instances as $block) {
                if (is_string($block->configdata) && strlen($block->configdata)) {
                    $data = unserialize($block->configdata);
                    if (isset($data['feedid']) && $data['feedid']) {
                        $ids[$data['feedid']] = true;
                    }
                }
            }
        }
        if (count($ids) == 0) {
            delete_records('blocktype_externalfeed_data'); // just delete it all 
            return;
        }
        $usedids = implode(', ', array_map('db_quote', array_keys($ids)));
        delete_records_select('blocktype_externalfeed_data', 'id NOT IN ( ' . $usedids . ' )');
    }

    /**
     * Parses the RSS feed given by $source. Throws an exception if the feed 
     * isn't able to be parsed
     *
     * @param string $source The URI for the feed
     * @throws XML_Feed_Parser_Exception
     */
    public static function parse_feed($source) {

        static $cache;
        if (empty($cache)) {
            $cache = array();
        }
        if (array_key_exists($source, $cache)) {
            return $cache[$source];
        }

        $config = array(
            CURLOPT_URL => $source,
            CURLOPT_TIMEOUT => 15,
        );

        $result = mahara_http_request($config);

        if ($result->error) {
            throw new XML_Feed_Parser_Exception($result->error);
        }

        if (empty($result->data)) {
            throw new XML_Feed_Parser_Exception('Feed url returned no data');
        }

        try {
            $feed = new XML_Feed_Parser($result->data, false, true, false);
        }
        catch (XML_Feed_Parser_Exception $e) {
            $cache[$source] = $e;
            throw $e;
            // Don't catch other exceptions, they're an indication something 
            // really bad happened
        }

        $data = new StdClass;
        $data->title = $feed->title;
        $data->url = $source;
        $data->link = $feed->link;
        $data->description = $feed->description;

        // Work out the icon for the feed depending on whether it's RSS or ATOM
        $data->image = $feed->image;
        if (!$data->image) {
            // ATOM feed. These are simple strings
            $data->image = $feed->logo ? $feed->logo : null;
        }

        $data->content = array();
        foreach ($feed as $count => $item) {
            if ($count == 20) {
                break;
            }
            $description = $item->content ? $item->content : ($item->description ? $item->description : ($item->summary ? $item->summary : null));
            $data->content[] = (object)array('title' => $item->title, 'link' => $item->link, 'description' => $description);
        }
        $cache[$source] = $data;
        return $data;
    }

    /**
     * Returns the HTML for the feed icon (not the little RSS one, but the 
     * actual logo associated with the feed)
     */
    private static function make_feed_image_tag($image) {
        $result = '';

        if (!$image['url']) {
            return '';
        }

        if (is_string($image)) {
            // Easy!
            return '<img src="' . hsc($image) . '">';
        }

        if (!empty($image['link'])) {
            $result .= '<a href="' . hsc($image['link']) . '">';
        }

        $url = $image['url'];
        // Try and fix URLs that aren't absolute. The standards all say URLs 
        // are supposed to be absolute in RSS feeds, yet still some people 
        // can't even get the basics right...
        if (substr($url, 0, 1) == '/' && !empty($image['link'])) {
            $url = $image['link'] . $image['url'];
        }

        $result .= '<img src="' . hsc($url) . '"';
        // Required by the specification, but we can't count on it...
        if (!empty($image['title'])) {
            $result .= ' alt="' . hsc($image['title']) . '"';
        }

        if (!empty($image['width']) || !empty($image['height'])) {
            $result .= ' style="';
            if (!empty($image['width'])) {
                $result .= 'width: ' . hsc($image['width']) . 'px;"';
            }
            if (!empty($image['height'])) {
                $result .= 'height: ' . hsc($image['height']) . 'px;"';
            }
            $result .= '"';
        }

        $result .= '>';

        if (!empty($image['link'])) {
            $result .= '</a>';
        }

        return $result;
    }

    public static function default_copy_type() {
        return 'full';
    }

    /**
     * The URL is not stored in the configdata, so we need to get it separately
     */
    public static function export_blockinstance_config(BlockInstance $bi) {
        $config = $bi->get('configdata');
        $url = !empty($config['feedid']) ? get_field('blocktype_externalfeed_data', 'url', 'id', $config['feedid']) : '';
        return array(
            'url' => $url,
            'full' => isset($config['full']) ? ($config['full'] ? 1 : 0) : 0,
        );
    }

    /**
     * Overrides default import to trigger retrieving the feed.
     */
    public static function import_create_blockinstance(array $config) {
        // Trigger retrieving the feed
        // Note: may have to re-think this at some point - we'll end up retrieving all the 
        // RSS feeds for this user at import time, which could easily be quite 
        // slow. This plugin will need a bit of re-working for this to be possible
        if (!empty($config['config']['url'])) {
            try {
                $values = self::instance_config_save(array('url' => $config['config']['url']));
            }
            catch (XML_Feed_Parser_Exception $e) {
                log_info("Note: was unable to parse RSS feed for new blockinstance. URL was {$config['config']['url']}");
                $values = array();
            }
        }

        $bi = new BlockInstance(0,
            array(
                'blocktype'  => 'externalfeed',
                'configdata' => array(
                    'feedid' => (isset($values['feedid'])) ? $values['feedid'] : '',
                    'full'   => (isset($config['config']['full']))   ? $config['config']['full']   : '',
                ),
            )
        );

        return $bi;
    }

}
