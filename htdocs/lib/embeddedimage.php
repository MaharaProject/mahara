<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Mike Kelly UAL <m.f.kelly@arts.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();
global $CFG;
require_once($CFG->docroot . '/artefact/lib.php');

class EmbeddedImage {

    function __construct() {
    }

    /**
     * Format HTML content in a WYSIWYG text box to correctly serve an embedded image
     * which was added via the TinyMCE imagebrowser plugin.
     * Add a database reference to the embedded image if required, to set viewing permissions for it
     *
     * @param string $fieldvalue The HTML source of the text body added to the TinyMCE text editor
     * @param string $resourcetype The type of resource which the TinyMCE editor is used in, e.g. 'forum', 'topic', 'post' for forum text boxes
     * @param int $resourceid The id of the resourcetype
     * @param int $groupid The id of the group the resource is in if applicable
     * @param int $userid The user trying to embed the image (current user if null)
     * @return string The updated $fieldvalue
     */
    public static function prepare_embedded_images($fieldvalue, $resourcetype, $resourceid, $groupid = NULL, $userid = NULL) {

        if (empty($fieldvalue) || empty($resourcetype) || empty($resourceid)) {
            return $fieldvalue;
        }

        global $USER;
        if ($userid == null) {
            $user = $USER;
        }
        else {
            $user = new User();
            try {
                $user->find_by_id($userid);
            }
            catch (AuthUnknownUserException $e) {
                log_warn('No user found with ID ' . $userid);
                return $fieldvalue;
            }
        }

        $dom = new DOMDocument();
        $dom->encoding = 'utf-8';
        $oldval = libxml_use_internal_errors(true);
        $tmpstr = (mb_detect_encoding($fieldvalue, 'auto') == 'UTF-8')
                ? '<?xml version="1.0" encoding="utf-8"?>' . $fieldvalue
                : $fieldvalue;
        $success = $dom->loadHTML($tmpstr);
        libxml_use_internal_errors($oldval);
        if ($success) {
            $publicimages = array();
            $xpath = new DOMXPath($dom);
            $srcstart = get_config('wwwroot') . 'artefact/file/download.php?';
            $query = '//img[starts-with(@src,"' . $srcstart . '")]';
            $images = $xpath->query($query);
            if (!$images->length) {
                self::remove_embedded_images($resourcetype, $resourceid);
                return $fieldvalue;
            }
            foreach ($images as $image) {
                // is this user allowed to publish this image?
                $imgsrc = $image->getAttribute('src');
                $searchpattern = '`file=(\d+)`';
                $foundmatch = preg_match_all($searchpattern, $imgsrc, $matches);
                if ($foundmatch) {
                    foreach ($matches[1] as $imgid) {
                        try {
                            $file = artefact_instance_from_id($imgid);
                        }
                        catch (ArtefactNotFoundException $e) {
                            continue;
                        }

                        if (
                            !($file instanceof ArtefactTypeImage)
                            || !$user->can_publish_artefact($file)
                        ) {
                            continue;
                        }
                        else {
                            $publicimages[] = $imgid;
                            $imgispublic = get_field('artefact_file_embedded', 'id', 'fileid', $imgid, 'resourcetype', $resourcetype, 'resourceid', $resourceid);
                            // add to embedded_images table for public access, specifiying context
                            if (!$imgispublic) {
                                insert_record('artefact_file_embedded', (object) array('fileid' => $imgid, 'resourcetype' => $resourcetype, 'resourceid' => $resourceid));
                            }
                        }
                    }
                }
                // rewrite to include group value and resource value
                // if user has group access, he or she will have access to view forum-based content
                $imgnode = $dom->createElement("img");
                $imgnode->setAttribute('width', $image->getAttribute('width'));
                $imgnode->setAttribute('height', $image->getAttribute('height'));
                $imgnode->setAttribute('style', $image->getAttribute('style'));
                $imgnode->setAttribute('alt', $image->getAttribute('alt'));

                if (!empty($groupid)) {
                    $searchpattern = '`group=(\d+)`';
                    $foundmatch = preg_match_all($searchpattern, $imgsrc, $matches);
                    if (!$foundmatch) {
                        $imgsrc = $imgsrc . '&group=' . $groupid;
                        $imgnode->setAttribute('src', $imgsrc);
                    }
                    else {
                        // check that the group value hasn't been spoofed
                        foreach ($matches[1] as $index => $grpid) {
                            if ($matches[1][$index] != $groupid) {
                                $imgsrc = str_replace('group=' . $matches[1][$index], 'group=' . $groupid, $imgsrc);
                            }
                        }
                        $imgnode->setAttribute('src', $imgsrc);
                    }
                }
                $searchpattern = '`' . $resourcetype . '=(\d+)`';
                $foundmatch = preg_match_all($searchpattern, $imgsrc, $matches);
                if (!$foundmatch) {
                    $imgnode->setAttribute('src', $imgsrc . '&' . $resourcetype . '=' . $resourceid);
                }
                else {
                    // check that the resourceid hasn't been spoofed
                    foreach ($matches[1] as $index => $rsrcid) {
                        if ($matches[1][$index] != $resourceid) {
                            $imgsrc = str_replace($resourcetype . '=' . $matches[1][$index], $resourcetype . '=' . $resourceid, $imgsrc);
                        }
                    }
                    $imgnode->setAttribute('src', $imgsrc);
                }
                $image->parentNode->replaceChild($imgnode, $image);
            }
            self::remove_embedded_images($resourcetype, $resourceid, $publicimages);

            // we only want the fragments inside the body tag created by new DOMDocument
            $childnodes = $dom->getElementsByTagName('body')->item(0)->childNodes;
            $dummydom = new DOMDocument();
            $dummydom->loadHTML('<?xml version="1.0" encoding="utf-8"?><div></div>');
            $dummydiv = $dummydom->getElementsByTagName('div')->item(0);
            foreach ($childnodes as $child) {
                $dummydiv->appendChild($dummydom->importNode($child, true));
            }
            $fieldvalue = substr($dummydom->saveHTML($dummydom->getElementsByTagName('div')->item(0)), strlen('<div>'), -strlen('</div>'));
            return $fieldvalue;
        }
    }

    /**
     * Remove database references to an embedded image
     * which was removed from a TinyMCE WYSIWYG text editor.
     * If parent resource has children, e.g. a forum with topics, delete refs. to embedded images in child resources
     *
     * @param string $resourcetype The type of resource which the TinyMCE editor is used in, e.g. 'forum', 'topic', 'post' for forum text boxes
     * @param int $resourceid The id of the resourcetype
     * @return void
     */
    public static function delete_embedded_images($resourcetype, $resourceid) {

        self::remove_embedded_images($resourcetype, $resourceid);

        if ($resourcetype == 'forum') {
            // we deleted embedded forum image above, now delete any embedded child topic and post images for that forum
            if ($topicids = get_records_array('interaction_forum_topic', 'forum', $resourceid, 'id DESC', 'id')) {
                foreach ($topicids as $id) {
                    // note recursion to remove posts associated with topic
                    self::delete_embedded_images('topic', $id->id);
                }
            }
        }
        else if ($resourcetype == 'topic') {
            // we deleted embedded topic image above, now delete any embedded child post images for that topic
            if ($postids = get_records_array('interaction_forum_post', 'topic', $resourceid, 'id DESC', 'id')) {
                foreach ($postids as $id) {
                    self::remove_embedded_images('post', $id->id);
                }
            }
        }
    }

    public static function can_see_embedded_image($fileid, $resourcetype, $resourceid) {
        $imgispublic = get_field('artefact_file_embedded', 'id', 'fileid', $fileid, 'resourcetype', $resourcetype, 'resourceid', $resourceid);
        return $imgispublic !== false;
    }

    static function remove_embedded_images($resourcetype, $resourceid, $publicimages = NULL) {
        $existingpublicimages = get_records_select_array('artefact_file_embedded', "resourcetype = ? AND resourceid = ?", array($resourcetype, $resourceid));
        if (!$existingpublicimages) {
            return;
        }
        foreach ($existingpublicimages as $img) {
            if (empty($publicimages) || !in_array($img->fileid, $publicimages)) {
                delete_records('artefact_file_embedded', 'fileid', $img->fileid, 'resourceid', $img->resourceid);
            }
        }
    }
/**
 * Rewrites all possible embedded image urls when import a html string
 *
 * @param string $text the html string
 * @param array $artefactids artefact ID mapping, see more PluginImportLeap::$artefactids
 * @param string $resourcetype
 * @param string $resourceid
 * @return mixed
 */
    public static function rewrite_embedded_image_urls_from_import($text, array $artefactids, $resourcetype=null, $resourceid=null) {
        $resourcestr = (!empty($resourcetype) && !empty($resourceid)) ?
                          "&$resourcetype=$resourceid"
                        : '';
        // Find all possible embedded image artefact ids
        // We support 2 formats of embedded image urls
        // 1. <img ... src=".../artefact/file/download.php?file=...">
        //      generated by TinyMCE embedded image plugin
        // 2. <img ... rel="leap2:has_part" href="(portfolio:artefact[\d]+)"...>
        //      generated by export_leap_rewrite_links()
        if (!empty($text) && strpos($text, '<img') !== false) {
            $ids = array();
            $regexp = array();
            $replacetext = array();
            $matches = array();
            if (preg_match_all(
                    '#<img([^>]+)src=("|\\")'
                    . preg_quote(
                        get_config('wwwroot')
                        . 'artefact/file/download.php?file='
                    )
                    . '([\d]+)'
                    . '(&|&amp;)embedded=1([^"]*)"#',
                    $text,
                    $matches)
                ) {
                foreach ($matches[3] as $id) {
                    if (!empty($artefactids["portfolio:artefact$id"])) {
                        // Replace the old image id by the new one
                        $regexp[] = '#<img([^>]+)src=("|\\")'
                            . preg_quote(
                                get_config('wwwroot')
                                . 'artefact/file/download.php?file=' . $id
                            )
                            . '(&|&amp;)embedded=1#';
                        $replacetext[] = '<img$1src="' . get_config('wwwroot')
                            . 'artefact/file/download.php?file='
                            . $artefactids["portfolio:artefact$id"][0] . '&embedded=1'
                            . $resourcestr;
                        $ids[] = $id;
                    }
                }
            }
            $matches = array();
            if (preg_match_all(
                    '#<(img[^>]+)rel="leap2:has_part"'
                    . ' href="portfolio:artefact([\d]+)"([^>]*)>#',
                    $text,
                    $matches)
                ) {
                foreach ($matches[2] as $id) {
                    if (!empty($artefactids["portfolio:artefact$id"])) {
                        // Replace the old entry id to the new one
                        $regexp[] = '#<img([^>]+)rel="leap2:has_part"'
                            . ' href="portfolio:artefact' . $id . '"#';
                        $replacetext[] = '<img$1src="' . get_config('wwwroot')
                            . 'artefact/file/download.php?file='
                            . $artefactids["portfolio:artefact$id"][0] . '&embedded=1'
                            . $resourcestr
                            . '"';
                        $ids[] = $id;
                    }
                }
            }
            if (!empty($ids)) {
                $text = preg_replace($regexp, $replacetext, $text);
                // Update the table 'artefact_file_embedded'
                if (!empty($resourcetype) && !empty($resourceid)) {
                    foreach ($ids as $id) {
                        insert_record('artefact_file_embedded', (object) array(
                        'fileid' => $artefactids["portfolio:artefact$id"][0],
                        'resourcetype' => $resourcetype,
                        'resourceid' => $resourceid
                        ));
                    }
                }
            }
        }
        return $text;

    }
}
