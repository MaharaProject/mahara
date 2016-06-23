<?php
/**
 *
* @package    mahara
* @subpackage blocktype-externalfeed
* @author     Chirp Internet: http://www.chirp.com.au (original source)
* @author     http://www.the-art-of-web.com/php/feed-reader/
* @author     Anis uddin Ahmad <admin@ajaxray.com>, PHP Universal Feed Parser http://www.ajaxray.com
* @author     Son Nguyen <son.nguyen@catalyst.net.nz>, Catalyst IT Ltd
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
* @copyright  For copyright information on Mahara, please see the README file distributed with this software.
*
*/
/**
 * PHP Feed Reader class
 *
 * Parses RSS 1.0, RSS2.0 and ATOM Feed using PHP XML parser
 */

defined('INTERNAL') || die();

class FeedReader {
    /**
     * Namespaces to detect feed version
     */
    private $namespaces = array(
        'http://purl.org/rss/1.0/'                  => 'RSS 1.0',
        'http://purl.org/rss/1.0/modules/content/'  => 'RSS 2.0',
        'http://bbc.co.uk/2009/01/ppgRss'           => 'RSS 2.0',
        'http://search.yahoo.com/mrss/'             => 'RSS 2.0',
        'http://www.w3.org/2005/Atom'               => 'ATOM 1',
    );

    /**
     * List of tag names which holds a feed item
     */
    private $itemTags = array('ITEM', 'ENTRY');

    /**
     * List of tag names which holds all channel elements
     */
    private $channelTags = array('CHANNEL', 'FEED');

    /**
     * List of tag names whose type is date and/or time
     */
    private $dateTags = array('UPDATED', 'PUBDATE', 'DATE', 'DC:DATE', 'LASTBUILDDATE', 'PUBLISHED');

    /**
     * List of tag names which have sub tags
     */
    private $hasSubTags = array('IMAGE', 'AUTHOR', 'ITUNES:OWNER');

    /**
     * List of tag names which may be empty
     */
    private $emptyTags = array('LINK', 'ATOM:LINK', 'ENCLOSURE', 'MEDIA:CONTENT');

    private $insideItem     = array();                  // Keep track of current position in tag tree
    private $currentTag     = null;                     // Last entered tag name
    private $currentAttr    = null;                     // Attributes array of last entered tag
    private $itemIndex      = 0;                        // Keep the index of current parsed item
    private $version        = null;                     // Detected feed version
    private $inXHTMLContent = false;                    // Check if parsing a XHTML content is in progress

    /**
     * Associate array to store channel's elements and sub items (not including items)
     */
    private $channel        = array();

    /**
     * array to store all items' elements and sub items
     */
    private $items          = array();

    public function __construct($inputcontent) {
        $xml_parser = xml_parser_create();
        xml_set_object($xml_parser, $this);
        xml_set_element_handler($xml_parser, 'begin_element', 'end_element');
        xml_set_character_data_handler($xml_parser, 'parse_data');

        if (!xml_parse($xml_parser, $inputcontent)) {
            throw new MaharaException(sprintf(get_class() . ": Error <b>%s</b> at line <b>%d</b><br>",
                    xml_error_string(xml_get_error_code($xml_parser)),
                    xml_get_current_line_number($xml_parser)));
        }
        xml_parser_free($xml_parser);
    }

    /**
     * Get all channel elements
     *
     * @access   public
     * @return   associate array
     */
    public function get_channel() {
        return $this->channel;
    }

    /**
     * Get all feed items
     *
     * @access   public
     * @return   array
     */
    public function get_items() {
        return $this->items;
    }

    /**
     * Get total number of feed items
     *
     * @access   public
     * @return   int
     */
    public function get_count_items() {
        return count($this->items);
    }

    /**
     * Get a feed item by index (start from 0)
     *
     * @access   public
     * @param    int  index of feed item
     * @return   associate array   feed item
     *      = false if the item does not exists
     */
    public function get_item($index) {
        return (isset($this->items[$index])) ? $this->items[$index] : false;
    }

    /**
     * Get channel title
     *
     * @access   public
     * @return   string title
     *      = false if not exists
     */
    public function get_channel_title() {
        return (isset($this->channel['TITLE'])
                && isset($this->channel['TITLE']['VALUE'])) ?
                      $this->channel['TITLE']['VALUE']
                    : false;
    }

    /**
     * Get channel link
     *
     * @access   public
     * @return   string link
     *      = false if not exists
     */
    public function get_channel_link() {
        return (isset($this->channel['LINK'])
                && isset($this->channel['LINK']['VALUE'])) ?
                      $this->channel['LINK']['VALUE']
                    : false;
    }

    /**
     * Get channel description
     *
     * @access   public
     * @return   string description
     *      = false if not exists
     */
    public function get_channel_description() {
        return (isset($this->channel['DESCRIPTION'])
                && isset($this->channel['DESCRIPTION']['VALUE'])) ?
                      $this->channel['DESCRIPTION']['VALUE']
                    : false;
    }

    /**
     * Get channel image url
     *
     * @access   public
     * @return   string image url
     *      = false if not exists
     */
    public function get_channel_image() {
        if (preg_match('/^RSS/', $this->version)) {
            if (isset($this->channel['IMAGE'])) {
                if (!empty($this->channel['IMAGE']['VALUE'])) {
                    return $this->channel['IMAGE']['VALUE'];
                }
                if (!empty($this->channel['IMAGE']['URL'])) {
                    return $this->channel['IMAGE']['URL']['VALUE'];
                }
            }
            return false;
        }
        else {
            return (isset($this->channel['LOGO'])
                    && isset($this->channel['LOGO']['VALUE'])) ?
                          $this->channel['LOGO']['VALUE']
                        : false;
        }
    }

    /**
     * Get title of an item
     *
     * @access   public
     * @param    integer  index of the feed item
     * @return   string title
     *      = false if not exists
     */
    public function get_item_title($i) {
        return (isset($this->items[$i])
                && isset($this->items[$i]['TITLE'])
                && isset($this->items[$i]['TITLE']['VALUE'])) ?
                      $this->items[$i]['TITLE']['VALUE']
                    : false;
    }

    /**
     * Get link of an item
     *
     * @access   public
     * @param    integer  index of the feed item
     * @return   string link
     *      = false if not exists
     */
    public function get_item_link($i) {
        return (isset($this->items[$i])
                && isset($this->items[$i]['LINK'])
                && isset($this->items[$i]['LINK']['VALUE'])) ?
                      $this->items[$i]['LINK']['VALUE']
                    : false;
    }

    /**
     * Get description of an item
     *
     * @access   public
     * @param    integer  index of the feed item
     * @return   string description
     *      = false if not exists
     */
    public function get_item_description($i) {
        return (isset($this->items[$i])
                && isset($this->items[$i]['DESCRIPTION'])
                && isset($this->items[$i]['DESCRIPTION']['VALUE'])) ?
                      $this->items[$i]['DESCRIPTION']['VALUE']
                    : false;
    }

    /**
     * Get content of an item (it can be a html string)
     *
     * @access   public
     * @param    integer index of the feed item
     * @return   string content
     *      = false if not exists
     */
    public function get_item_content($i) {
        return (isset($this->items[$i])
                && isset($this->items[$i]['CONTENT'])
                && isset($this->items[$i]['CONTENT']['VALUE'])) ?
                      $this->items[$i]['CONTENT']['VALUE']
                    : false;
    }

    /**
     * Get summary of an item
     *
     * @access   public
     * @param    integer index of the feed item
     * @return   string summary
     *      = false if not exists
     */
    public function get_item_summary($i) {
        return (isset($this->items[$i])
                && isset($this->items[$i]['SUMMARY'])
                && isset($this->items[$i]['SUMMARY']['VALUE'])) ?
                      $this->items[$i]['SUMMARY']['VALUE']
                    : false;
    }

    /**
     * Get pubdate of an item
     *
     * @access   public
     * @param    integer  index of the feed item
     * @return   integer timestamp of pubdate
     *      = false if not exists
     */
    public function get_item_pubdate($i) {
        return (isset($this->items[$i])
                && isset($this->items[$i]['PUBDATE'])
                && isset($this->items[$i]['PUBDATE']['VALUE'])) ?
                      $this->items[$i]['PUBDATE']['VALUE']
                    : false;
    }

    /**
     * Get date of an item
     *
     * @access   public
     * @param    integer  index of the feed item
     * @return   integer timestamp of date
     *      = false if not exists
     */
    public function get_item_date($i) {
        return (isset($this->items[$i])
                && isset($this->items[$i]['DATE'])
                && isset($this->items[$i]['DATE']['VALUE'])) ?
                      $this->items[$i]['DATE']['VALUE']
                    : false;
    }

    /**
     * Get published of an item
     *
     * @access   public
     * @param    integer  index of the feed item
     * @return   integer timestamp of published
     *      = false if not exists
     */
    public function get_item_published($i) {
        return (isset($this->items[$i])
                && isset($this->items[$i]['PUBLISHED'])
                && isset($this->items[$i]['PUBLISHED']['VALUE'])) ?
                      $this->items[$i]['PUBLISHED']['VALUE']
                    : false;
    }

    /**
     * Get updated of an item
     *
     * @access   public
     * @param    integer  index of the feed item
     * @return   integer timestamp of updated
     *      = false if not exists
     */
    public function get_item_updated($i) {
        return (isset($this->items[$i])
                && isset($this->items[$i]['UPDATED'])
                && isset($this->items[$i]['UPDATED']['VALUE'])) ?
                      $this->items[$i]['UPDATED']['VALUE']
                    : false;
    }

    /**
     * Handler function called when starting a XML tag
     *
     * @access   private
     * @param    object  the xmlParser object
     * @param    string  name of currently entering tag
     * @param    array   array of attributes
     * @return   void
     */
    private function begin_element($parser, $tagName, $attrs) {
        if (!$this->version) {
            $this->detect_version($tagName, $attrs);
        }

        array_push($this->insideItem, $tagName);

        $this->currentTag  = $tagName;
        $this->currentAttr = $attrs;

        if ($this->in_channel()
            && in_array($this->currentTag, $this->hasSubTags)) {
            $this->channel[$tagName] = array();
            // Add attributes of the current channel element
            if (!empty($attrs)) {
                $this->channel[$tagName]['ATTRIBUTES'] = $attrs;
            }
        }
        else if ($this->in_item()) {
            if (!isset($this->items[$this->itemIndex])) {
                $this->items[$this->itemIndex] = array();
            }

            if (!isset($this->items[$this->itemIndex][$tagName])) {
                $this->items[$this->itemIndex][$tagName] = array();
            }

            if (!$this->inXHTMLContent && $this->is_XHTML_content_tag($tagName, $attrs)) {
                $this->inXHTMLContent = true;
                $this->items[$this->itemIndex]['CONTENT']['VALUE'] = "<$tagName>";
            }
            // Add attributes of the current item element
            if (!empty($attrs)) {
                $this->items[$this->itemIndex][$tagName]['ATTRIBUTES'] = $attrs;
            }
        }
    }

    /**
     * Handler function called when ending a XML tag
     *
     * @access   private
     * @param    object  the xmlParser object
     * @param    string  name of currently ending tag
     * @return   void
     */
    private function end_element($parser, $tagName) {

        if ($this->inXHTMLContent) {
            $this->items[$this->itemIndex]['CONTENT']['VALUE'] .= "</$tagName>";
        }

        if ($tagName == 'CONTENT') {
            $this->inXHTMLContent = false;
        }

        if (in_array($tagName, $this->itemTags)) {
            $this->itemIndex++;
        }

        array_pop($this->insideItem);
        if (!empty($this->insideItem)) {
            $this->currentTag = $this->insideItem[count($this->insideItem)-1];
        }
    }

    /**
     * Handler function called when parsing content of a tag
     *
     * @access   private
     * @param    object  the xmlParser object
     * @param    string  tag value
     * @return   void
     */
    private function parse_data($parser, $data) {
        // Convert text date to timestamp
        if (in_array($this->currentTag, $this->dateTags)) {
            $data = strtotime($data);
        }
        else {
            // Validate other text data
            $data = html_entity_decode((trim($data)));
        }

        if ($this->in_channel()) {
            $this->set_element($this->channel, $data);
        }
        else if ($this->in_item()) {
            if ($this->inXHTMLContent) {
                $this->items[$this->itemIndex]['CONTENT']['VALUE'] .= $data;
            }
            else {
                $this->set_element($this->items[$this->itemIndex], $data);
            }
        }
    }

    /**
    * Detect the feed version based on the first tag and its namespace attribute
    * and update the $this->version
    *
    * @access   private
    * @param    string  name of the first tag
    * @param    array   array of attributes
    * @return   void
    */
    private function detect_version($tagName, $attrs) {
        foreach ($this->namespaces as $value =>$version) {
            if (in_array($value, array_values($attrs))) {
                $this->version = $version;
                return;
            }
        }
        if (($tagName = 'RSS') && isset($attrs['VERSION'])) {
            $this->version = 'RSS ' . $attrs['VERSION'];
            return;
        }
    }

    /**
     * Return the name of parent tag
     *
     * @return string the name of parent tag
     *          or null if not exists
     */
    private function get_parent_tag() {
        return (count($this->insideItem) >= 2) ?
                      $this->insideItem[count($this->insideItem) - 2]
                    : null;
    }

    /**
     * Detect if current position is in channel element
     *
     * @access   private
     * @return   bool
     */
    private function in_channel() {
        if (preg_match('/^RSS/', $this->version)) {
            return (in_array('CHANNEL', $this->insideItem)
                    && !in_array('ITEM', $this->insideItem)
                    && $this->currentTag != 'CHANNEL');
        }
        else if ($this->version == 'ATOM 1') {
            return (in_array('FEED', $this->insideItem)
                    && !in_array('ENTRY', $this->insideItem)
                    && $this->currentTag != 'FEED');
        }

        return false;
    }

    /**
     * Detect if current position is in Item element
     *
     * @access   private
     * @return   bool
     */
    private function in_item() {
        if (preg_match('/^RSS/', $this->version)) {
            return (in_array('ITEM', $this->insideItem) && $this->currentTag != 'ITEM');
        }
        else if ($this->version == 'ATOM 1') {
            return (in_array('ENTRY', $this->insideItem) && $this->currentTag != 'ENTRY');
        }

        return false;
    }

    /**
     * Check if the give tag is a xhtml content (<content type="xhtml">)
     *
     * @access   private
     * @param    string $tagName
     * @param    array  $tagAttrs
     * @return   bool
     */
    private function is_XHTML_content_tag($tagName, $tagAttrs) {
        return (($tagName == 'CONTENT')
                && (!empty($tagAttrs['TYPE'])
                && strtoupper($tagAttrs['TYPE']) == 'XHTML'));
    }

    /**
     * Set value for an element or sub-element of a channel or item
     *
     * @param array channel or item $root
     * @param string $data
     */
    private function set_element(&$root, $data) {
        // Check if it is a sub-element
        $parentTag = $this->get_parent_tag();
        if (!empty($parentTag)
            && in_array($parentTag, $this->hasSubTags)) {

            $root[$parentTag][$this->currentTag]['VALUE'] =
                empty($root[$parentTag][$this->currentTag]['VALUE']) ?
                          $data
                        : $root[$parentTag][$this->currentTag]['VALUE'] . $data;
            return;
        }

        if (!in_array($this->currentTag, $this->hasSubTags)
            && !empty($data)) {
            $root[$this->currentTag]['VALUE'] =
                empty($root[$this->currentTag]['VALUE']) ?
                      $data
                    : $root[$this->currentTag]['VALUE'] . $data;
        }
    }

}
