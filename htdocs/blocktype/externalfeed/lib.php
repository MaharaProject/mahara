<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-externalfeed
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
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
            if (is_postgres()) {
                $table = new XMLDBTable('blocktype_externalfeed_data');
                $index = new XMLDBIndex('urlautautix');
                $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('url', 'authuser', 'authpassword'));
                add_index($table, $index);
            }
            else if (is_mysql()) {
                // MySQL needs size limits when indexing text fields
                execute_sql('ALTER TABLE {blocktype_externalfeed_data} ADD INDEX
                               {blocextedata_urlautaut_ix} (url(255), authuser(255), authpassword(255))');
            }
        }
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        $configdata = $instance->get('configdata');
        if (!empty($configdata['feedid'])) {

            $data = $instance->get_data('feed', $configdata['feedid']);

            if (isset($data) && is_string($data->content)) {
                $data->content = unserialize($data->content);
            }
            if (isset($data) && (is_string($data->image) || is_array($data->image))) {
                $data->image = @unserialize($data->image);
            }
            else {
                $data->image = null;
            }

            // only keep the number of entries the user asked for
            if (count($data->content) && !empty($data->content) && is_array($data->content)) {
                $chunks = array_chunk($data->content, isset($configdata['count']) ? $configdata['count'] : 10);
                $data->content = $chunks[0];

                foreach ($data->content as &$c) {
                    $c->link =  sanitize_url($c->link);
                    $c->pubdate = !empty($c->pubdate) ? format_date($c->pubdate) : null;
                }
            }

            // Attempt to fix relative URLs in the feeds
            if (!empty($data->image['link'])) {
                $data->description = preg_replace(
                    '/src="(\/[^"]+)"/',
                    'src="' . $data->image['link'] . '$1"',
                    $data->description
                );
                if (!empty($data->content) && is_array($data->content)) {
                    foreach ($data->content as &$entry) {
                        $entry->description = preg_replace(
                            '/src="(\/[^"]+)"/',
                            'src="' . $data->image['link'] . '$1"',
                            $entry->description
                        );
                    }
                }
            }

            $smarty = smarty_core();
            $smarty->assign('title', $data->title);
            $smarty->assign('description', $data->description);
            $smarty->assign('url', $data->url);
            // 'full' won't be set for feeds created before 'full' support was added
            $smarty->assign('full', isset($configdata['full']) ? $configdata['full'] : false);
            $smarty->assign('link', sanitize_url($data->link));
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
            'id,url,link,title,description,content,authuser,authpassword,insecuresslmode,' . db_format_tsfield('lastupdate') . ',image'
        );
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        $configdata = $instance->get('configdata');

        if (!empty($configdata['feedid'])) {
            $instancedata = $instance->get_data('feed', $configdata['feedid']);
            $url = $instancedata->url;
            $insecuresslmode = $instancedata->insecuresslmode;
            $authuser = $instancedata->authuser;
            $authpassword = $instancedata->authpassword;
        }
        else {
            $url = '';
            $insecuresslmode = 0;
            $authuser = '';
            $authpassword = '';
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
            'insecuresslmode' => array(
                'type'  => 'checkbox',
                'title' => get_string('insecuresslmode', 'blocktype.externalfeed'),
                'description' => get_string('insecuresslmodedesc', 'blocktype.externalfeed'),
                'defaultvalue' => (bool)$insecuresslmode,
            ),
            'authuser' => array(
                'type' => 'text',
                'title' => get_string('authuser', 'blocktype.externalfeed'),
                'description' => get_string('authuserdesc', 'blocktype.externalfeed'),
                'width' => '90%',
                'defaultvalue' => $authuser,
            ),
            'authpassword' => array(
                'type' => 'passwordnoread',
                'title' => get_string('authpassword', 'blocktype.externalfeed'),
                'description' => get_string('authpassworddesc', 'blocktype.externalfeed'),
                'width' => '90%',
                'defaultvalue' => $authpassword,
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
                $form->set_error('url', get_string('invalidurl', 'blocktype.externalfeed'), false);
            }
        }

        // If you're changing the URL on an authenticated feed, force them to re-enter the password
        if (!empty($values['blockconfig'])) {
            $instance = new BlockInstance($values['blockconfig']);
            $configdata = $instance->get('configdata');
            if (!empty($configdata['feedid'])) {
                $olddata = $instance->get_data('feed', $configdata['feedid']);
                if ($olddata) {
                    if ($values['url'] <> $olddata->url && $olddata->authpassword != '' && $values['authpassword']['submittedvalue'] === null) {
                        $form->set_error('authpassword', get_string('reenterpassword', 'blocktype.externalfeed'), false);
                        return;
                    }
                }
            }
        }

        if (!$form->get_error('url')) {
            try {
                $authpassword = ($values['authpassword']['submittedvalue'] !== null) ? $values['authpassword']['submittedvalue'] : $values['authpassword']['defaultvalue'];
                self::parse_feed($values['url'], $values['insecuresslmode'], $values['authuser'], $authpassword);
                return;
            }
            catch (XML_Feed_Parser_Exception $e) {
                $form->set_error('url', get_string('invalidfeed', 'blocktype.externalfeed',  hsc($e->getMessage())), false);
            }
        }
    }

    public static function instance_config_save($values) {
        // we need to turn the feed url into an id in the feed_data table..
        if (strpos($values['url'], '://') == false) {
            // try add on http://
            $values['url'] = 'http://' . $values['url'];
        }
        // We know this is safe because self::parse_feed caches its result and
        // the validate method would have failed if the feed was invalid
        $authpassword = !empty($values['authpassword']['submittedvalue']) ? $values['authpassword']['submittedvalue'] : (!empty($values['authpassword']['defaultvalue']) ? $values['authpassword']['defaultvalue'] : '');
        $data = self::parse_feed($values['url'], $values['insecuresslmode'], $values['authuser'], $authpassword);
        $data->content  = serialize($data->content);
        $data->image    = serialize($data->image);
        $data->lastupdate = db_format_timestamp(time());
        $wheredata = array('url' => $values['url'], 'authuser' => $values['authuser'], 'authpassword' => $authpassword);
        $id = ensure_record_exists('blocktype_externalfeed_data', $wheredata, $data, 'id', true);
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
            'lastupdate < ? OR lastupdate IS NULL', array(db_format_timestamp(strtotime('-30 minutes'))),
            '', 'id,url,authuser,authpassword,insecuresslmode,' . db_format_tsfield('lastupdate', 'tslastupdate'))) {
            return;
        }
        $yesterday = time() - 60*60*24;
        foreach ($feeds as $feed) {
            // Hack to stop the updating of dead feeds from delaying other
            // more important stuff that runs on cron.
            if (defined('CRON') && !empty($feed->tslastupdate) && $feed->tslastupdate < $yesterday) {
                // We've been trying for 24 hours already, so waste less
                // time on this one and just try it once a day
                if (mt_rand(0, 24) != 0) {
                    continue;
                }
            }

            try {
                unset($data);
                $data = self::parse_feed($feed->url, $feed->insecuresslmode, $feed->authuser, $feed->authpassword);
            }
            catch (XML_Feed_Parser_Exception $e) {
                // The feed must have changed in such a way as to become
                // invalid since it was added. We ignore this case in the hope
                // the feed will become valid some time later
                continue;
            }

            if (isset($data)) {
                if (!isset($data->image)) {
                    $data->image = null;
                }
                try {
                    $data->content = $data->content ? serialize($data->content) : '' ;
                    $data->image = $data->image ? serialize($data->image) : '';
                    $data->id = $feed->id;
                    $data->lastupdate = db_format_timestamp(time());
                    update_record('blocktype_externalfeed_data', $data);
                }
                catch (XML_Feed_Parser_Exception $e) {
                    // We tried to add the newly parsed data
                }
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
     * @param bool $insecuresslmode Skip certificate checking
     * @param string $authuser HTTP basic auth username to use
     * @param string $authpassword HTTP basic auth password to use
     * @throws XML_Feed_Parser_Exception
     */
    public static function parse_feed($source, $insecuresslmode=false, $authuser='', $authpassword='') {

        static $cache;
        if (empty($cache)) {
            $cache = array();
        }
        if (array_key_exists($source, $cache)) {
            if ($cache[$source] instanceof Exception) {
               throw $cache[$source];
            }
            return $cache[$source];
        }

        $config = array(
            CURLOPT_URL => $source,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_USERAGENT => '',
        );

        if (!empty($authuser) || !empty($authpassword)) {
            $config[CURLOPT_USERPWD] = $authuser . ':' . $authpassword;
        }

        if ($insecuresslmode) {
            $config[CURLOPT_SSL_VERIFYPEER] = false;
        }

        $result = mahara_http_request($config, true);

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
        $data->authuser = $authuser;
        $data->authpassword = $authpassword;
        $data->insecuresslmode = (int)$insecuresslmode;
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
            if (!$item->title) {
                if (!empty($description)) {
                    $item->title = substr($description, 0, 60);
                }
                else if ($item->link) {
                    $item->title = $item->link;
                }
                else {
                    $item->title = get_string('notitle', 'view');
                }
            }
            if (!$pubdate = $item->pubDate) {
                if (!$pubdate = $item->date) {
                    if (!$pubdate = $item->published) {
                        $pubdate = $item->updated;
                    };
                }
            }

            $data->content[] = (object)array(
                'title'       => $item->title,
                'link'        => $item->link,
                'description' => $description,
                'pubdate'     => $pubdate,
            );
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

        if ($image['url']) {
            $image['url'] = sanitize_url($image['url']);
        }

        if (!$image['url']) {
            return '';
        }

        if (is_string($image)) {
            if (is_https() and stripos($image, 'http://') !== false) {
                // HTTPS sites should not display HTTP images
                return '';
            }
            return '<img src="' . hsc($image) . '">';
        }

        if ($image['link']) {
            $image['link'] = sanitize_url($image['link']);
        }

        if (!empty($image['link'])) {
            $result .= '<a href="' . $image['link'] . '">';
        }

        $url = $image['url'];
        // Try and fix URLs that aren't absolute. The standards all say URLs
        // are supposed to be absolute in RSS feeds, yet still some people
        // can't even get the basics right...
        if (substr($url, 0, 1) == '/' && !empty($image['link'])) {
            $url = $image['link'] . $image['url'];
        }

        if (is_https() and stripos($url, 'http://') !== false) {
            // HTTPS sites should not display HTTP images
            return '';
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

        $url = $authuser = $authpassword = '';
        $insecuresslmode = false;
        if (!empty($config['feedid']) and $record = get_record('blocktype_externalfeed_data', 'id', $config['feedid'])) {
            $url =  $record->url;
            $authuser = $record->authuser;
            $insecuresslmode = (bool)$record->insecuresslmode;
        }

        // Note: We don't include authpassword, because that would mean printing out the
        // RSS password in plain text. The user will have to re-enter the password when
        // they import this archive.
        return array(
            'url' => $url,
            'authuser' => $authuser,
            'authpassword' => '',
            'insecuresslmode' => $insecuresslmode ? 1 : 0,
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
                $urloptions = array('url' => $config['config']['url'],
                                    'authuser' => !empty($config['config']['authuser']) ? $config['config']['authuser'] : '',
                                    'authpassword' => !empty($config['config']['authpassword']) ? $config['config']['authpassword'] : '',
                                    'insecuresslmode' => !empty($config['config']['insecuresslmode']) ? (bool)$config['config']['insecuresslmode'] : false,
                                    );
                $values = self::instance_config_save($urloptions);
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
