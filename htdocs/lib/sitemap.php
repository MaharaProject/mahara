<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Darryl Hamilton
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

/**
 * The core sitemap class which generates the sitemaps.org standard sitemap files.
 */
class Sitemap {

    /**
     * @var null|string The date to check against for limiting what goes into the sitemap
     */
    private $date_to_check;

    /**
     * @var array Array of sitemap files
     */
    private $sitemaps = array();

    /**
     * @var int Maximum size an individual sitemap or sitemap index file can be
     */
    private $maxfilesize = 10485760; // 10MB

    /**
     * @var int Maximum number of URLs allowed in a sitemap or sitemap index file
     */
    private $maxurlcount = 50000; // 50k

    /**
     * @var float How close to the maximums we can get before starting anew
     */
    private $gracesize = 0.90;

    /**
     * @var DOMDocument The sitemap currently getting added to
     */
    private $currentsitemap;

    /**
     * @var DOMElement The urlset for $current_sitemap
     */
    private $currenturlset;

    /**
     * @var string The directory in which to put sitemap files
     */
    private $directory;

    /**
     * @param bool $forcefull Force generation of a full sitemap (non-daily)
     */
    public function __construct($forcefull = false) {
        $this->directory = get_config('dataroot') . 'sitemaps/';

        // on the first of the month, or if forced, generate the full sitemap
        if (date("d") == 1 || $forcefull === true) {
            $this->date_to_check = null;
            $remove = 'sitemap_*.xml';
        }
        else { // otherwise limit to 'yesterday'
            $this->date_to_check = date("Y-m-d", strtotime('yesterday'));
            $remove = 'sitemap_' . date('Ymd') . '_*.xml';
        }

        // remove any sitemaps we're about to replace
        if (!$oldsitemaps = glob($this->directory . $remove)) {
            $oldsitemaps = array();
        }
        if ($oldcompressed = glob($this->directory . $remove . '.gz')) {
            $oldsitemaps = array_merge($oldsitemaps, $oldcompressed);
        }
        foreach ($oldsitemaps as $sitemap) {
            if (!unlink($sitemap)) {
                log_warn(sprintf("Failed to remove sitemap: %s, please check directory and file permissions.", basename($sitemap)));
            }
        }
    }

    /**
     * Generate sitemap(s) and an index
     *
     * @return bool
     */
    public function generate() {

        // check that the sitemaps directory exists and create it if it doesn't
        check_dir_exists($this->directory, true);

        // this is used by PluginInteractionForum::get_active_topics
        $USER = new User();

        // create a new sitemap
        $this->create_sitemap();

        // get a list of public groups
        $publicgroups = get_records_select_array('group', 'public = 1 AND deleted = 0');
        if (!empty($publicgroups)) {
            foreach ($publicgroups as $group) {
                if (isset($group->mtime) && $this->check_date($group->mtime)) {
                    // each group gets a url entry
                    $groupurl     = group_homepage_url($group);
                    $groupurl     = utf8_encode(htmlspecialchars($groupurl, ENT_QUOTES, 'UTF-8'));
                    $grouplastmod = format_date(strtotime($group->mtime), 'strftimew3cdate');

                    $this->add_url($groupurl, $grouplastmod);
                }

                // build a list of forums in each public group
                $forums = get_forum_list($group->id);
                $forumids = array();
                foreach ($forums as $forum) {
                    $forumids[] = $forum->id;
                }

                // active topics within the specified forums (public only)
                $activetopics = PluginInteractionForum::get_active_topics(0, 0, 0, $forumids);
                foreach ($activetopics['data'] as $topic) {
                    if (
                        (isset($topic->mtime) && $this->check_date($topic->mtime))
                        || (isset($topic->ctime) && $this->check_date($topic->ctime))
                    ) {

                        $forumurl = get_config('wwwroot') . 'interaction/forum/topic.php?id=' . $topic->id;
                        $forumurl = utf8_encode(htmlspecialchars($forumurl, ENT_QUOTES, 'UTF-8'));

                        // mtime will be set if the last post has been edited
                        if (isset($topic->mtime) && strtotime($topic->mtime) !== FALSE) {
                            $forumlastmod = format_date(strtotime($topic->mtime), 'strftimew3cdate');
                        } // otherwise, use the last post creation date
                        else {
                            $forumlastmod = format_date(strtotime($topic->ctime), 'strftimew3cdate');
                        }

                        $this->add_url($forumurl, $forumlastmod);
                    }
                }
            }
        }

        // views shared with the public
        // grouphomepage type views are handled above
        $types = array('portfolio');
        $views = View::view_search(null, null, null, null, null, 0, true, null, $types);
        if (!empty($views->data)) {
            foreach ($views->data as $view) {
                if (isset($view['mtime']) && $this->check_date($view['mtime'])) {
                    $viewurl = utf8_encode(htmlspecialchars($view['fullurl'], ENT_QUOTES, 'UTF-8'));
                    $viewlastmod = format_date(strtotime($view['mtime']), 'strftimew3cdate');

                    $this->add_url($viewurl, $viewlastmod);
                }
            }
        }

        // add the urlset and print the xml out
        // only if the urlset has any children
        if ($this->currenturlset->hasChildNodes()) {
            $this->save_sitemap(true);
        }

        return true;
    }

    /**
     * Generate the sitemap index file
     *
     * Assumption - there is currently no checking done on the size of the sitemap index
     * file, as we're not expecting that limit to be reached for some time.
     */
    public function generate_index() {
        // main index file
        $doc = new DOMDocument('1.0', 'utf-8');

        // root node
        $sitemapindex = $doc->createElement('sitemapindex');
        $sitemapindex->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        // step through each sitemap we have generated
        foreach ($this->sitemaps as $key => $sitemap) {
            $filename = sprintf("%ssitemap_%s_%d.xml", $this->directory, date("Ymd"), $key);
            // if the save succeeded, add it to the index
            $xml = $sitemap->saveXML();
            if ($xml !== false) {
                // try to gzip the xml file
                if (function_exists('gzopen') and $zp = gzopen($filename . '.gz', 'w9')) {
                    gzwrite($zp, $xml);
                    gzclose($zp);
                }
                else {
                    log_info('Skipping compression of xml file - gzip command not found, or not executable.');
                    if (!file_put_contents($filename, $xml)) {
                        throw new SystemException(sprintf("Saving of this sitemap file failed: %s", $filename));
                    }
                }
            }
            else {
                throw new SystemException(sprintf("Saving of this sitemap file failed: %s", $filename));
            }
        }

        // get a list of sitemaps in the sitemap directory
        $sitemaps = glob($this->directory . 'sitemap_*.xml*');
        foreach ($sitemaps as $sitemap) {
            // create a <sitemap> node for each one we're adding
            $sitemapelement = $doc->createElement('sitemap');

            // create and encode the url
            $sitemapurl = sprintf("%sdownload.php?type=sitemap&name=%s", get_config('wwwroot'), basename($sitemap));
            $sitemapurl = utf8_encode(htmlspecialchars($sitemapurl, ENT_QUOTES, 'UTF-8'));

            // add it to the <sitemap> node
            $loc = $doc->createElement('loc', $sitemapurl);
            $sitemapelement->appendChild($loc);

            // formatted date, uses the files modified date
            $sitemaplastmod = format_date(filemtime($sitemap), 'strftimew3cdate');

            // add it to the <sitemap> node
            $lastmod = $doc->createElement('lastmod', $sitemaplastmod);
            $sitemapelement->appendChild($lastmod);

            // add this <sitemap> node to the parent index
            $sitemapindex->appendChild($sitemapelement);
        }

        // add the index to the main doc and save it
        $doc->appendChild($sitemapindex);
        $indexfilename = sprintf("%ssitemap_index.xml", $this->directory);
        $doc->save($indexfilename);
    }

    /**
     * @param   string  $date   The date to compare with the check date
     * @return  bool Returns true if the specified date is within the bounds of the check date
     */
    private function check_date($date) {
        $time = strtotime($date);
        if ($time !== false && strtotime($this->date_to_check) !== false) {
            $starttime = strtotime($this->date_to_check);
            $endtime   = strtotime(sprintf("%s 23:59:59", $this->date_to_check));
            return ($time >= $starttime && $time <= $endtime);
        }

        // null is used for the monthly 'grab everything' sitemap
        return is_null($this->date_to_check);
    }

    /**
     * Create a new sitemap and urlset and assign to class variables
     */
    private function create_sitemap() {
        $this->currentsitemap = new DOMDocument('1.0', 'utf-8');

        $this->currenturlset = $this->currentsitemap->createElement('urlset');
        $this->currenturlset->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
    }

    /**
     * Add the urlset to the current sitemap and save it into the array
     *
     * @param   bool    $final      If this is the last sitemap, generate an index file
     */
    private function save_sitemap($final = false) {
        $this->currentsitemap->appendChild($this->currenturlset);
        $this->sitemaps[] = $this->currentsitemap;

        if ($final) {
            // save to file(s), generate index
            $this->generate_index();
        }
    }

    /**
     * Add a url to a urlset
     *
     * @param   string  $loc        The url
     * @param   string  $lastmod    The last modification time
     */
    private function add_url($loc, $lastmod) {
        // if the number of urls is within the grace size of the maximum url count
        // or the size of the sitemap is within the grace size of the maximum file size
        // save the current sitemap and start a new one.
        $filesize = mb_strlen($this->currentsitemap->saveXML(), 'UTF-8');
        $urlcount = $this->currenturlset->childNodes->length;
        if ($urlcount >= ($this->maxurlcount * $this->gracesize) || $filesize >= ($this->maxfilesize * $this->gracesize)) {
            $this->save_sitemap();
            $this->create_sitemap();

            // log a note that filesize or url count limits were reached
            log_info('New sitemap created due to filesize or url count limits');
        }

        $url = $this->create_and_append('url', '');
        $this->create_and_append('loc', $loc, $url);
        $this->create_and_append('lastmod', $lastmod, $url);
    }

    /**
     * @param   string       $name       The name of the element to add
     * @param   mixed        $value      The value of the element
     * @param   mixed        $element    The element to append to
     * @return  DOMElement               The newly created element
     */
    private function create_and_append($name, $value, &$element = null) {
        $e = $this->currentsitemap->createElement($name, $value);
        if (is_null($element)) {
            $element = $this->currenturlset;
        }
        $element->appendChild($e);

        return $e;
    }
}
