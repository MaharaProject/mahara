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
     * @return string The updated $fieldvalue
     */
    public function prepare_embedded_images($fieldvalue, $resourcetype, $resourceid, $groupid = NULL) {

        if (empty($fieldvalue) || empty($resourcetype) || empty($resourceid)) {
            return $fieldvalue;
        }

        global $USER;
        $dom = new DOMDocument();
        $dom->encoding = 'utf-8';
        $oldval = libxml_use_internal_errors(true);
        $success = $dom->loadHTML(utf8_decode($fieldvalue));
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
                        $file = artefact_instance_from_id($imgid);
                        if (!($file instanceof ArtefactTypeImage) || !$USER->can_publish_artefact($file)) {
                            return $form->i18n('rule', 'wysiwygimagerights', 'wysiwygimagerights', $element);
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
            $innerhtml = '';
            foreach ($childnodes as $child) {
                $fragment = $dom->saveHTML($child);
                $innerhtml .= html_entity_decode($fragment, ENT_QUOTES, 'UTF-8');
            }
            $fieldvalue = $innerhtml;
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
    public function delete_embedded_images($resourcetype, $resourceid) {

        self::remove_embedded_images($resourcetype, $resourceid);

        if ($resourcetype == 'forum') {
            // we deleted embedded forum image above, now delete any embedded child topic and post images for that forum
            $topicids = get_records_array('interaction_forum_topic', 'forum', $resourceid, 'id DESC', 'id');
            foreach ($topicids as $id) {
                // note recursion to remove posts associated with topic
                self::delete_embedded_images('topic', $id->id);
            }
        }
        else if ($resourcetype == 'topic') {
            // we deleted embedded topic image above, now delete any embedded child post images for that topic
            $postids = get_records_array('interaction_forum_post', 'topic', $resourceid, 'id DESC', 'id');
            foreach ($postids as $id) {
                self::remove_embedded_images('post', $id->id);
            }
        }
        else if ($resourcetype == 'blog') {
            // we deleted blog image above, now delete any embedded blogpost images for that blog
            $blogpostids = get_records_array('artefact', 'parent', $resourceid, 'id DESC', 'id');
            foreach ($blogpostids as $id) {
                self::remove_embedded_images('blogpost', $id->id);
            }
        }
    }

    public function can_see_embedded_image($fileid, $resourcetype, $resourceid) {
        $imgispublic = get_field('artefact_file_embedded', 'id', 'fileid', $fileid, 'resourcetype', $resourcetype, 'resourceid', $resourceid);
        return $imgispublic !== false;
    }

    function remove_embedded_images($resourcetype, $resourceid, $publicimages = NULL) {
        $existingpublicimages = get_records_select_array('artefact_file_embedded', "resourcetype = ? AND resourceid = ?", array($resourcetype, $resourceid));
        if (!$existingpublicimages) {
            return;
        }
        foreach ($existingpublicimages as $img) {
            if (empty($publicimages) || !in_array($img->fileid, $publicimages)) {
                delete_records('artefact_file_embedded', 'fileid', $img->fileid);
            }
        }
    }
}
