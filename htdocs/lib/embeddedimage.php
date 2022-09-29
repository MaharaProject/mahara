<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Mike Kelly UAL <m.f.kelly@arts.ac.uk>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
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
     * Return the resourcetable mapping.
     *
     * The mapping tells us which DB table the resource ID comes from.
     * It can let us how to go about checking the access rights.
     *  e.g. academic goals => 'usr' tells us that it won't with the same logic as
     *  resourcetypes that map to view_artefact.
     *
     * TODO: Finish mapping all resource types.
     *
     * @return array
     */
    public static function get_resourcetable_mapping() {
        return [
            'academicgoal' => 'usr',
            'academicskill' => 'usr',
            'careergoal' => 'usr',
            'annotation' => 'view_artefact',
            'annotationfeedback' => 'artefact_annotation_feedback',
            'assessment' => 'artefact_peer_assessment',
            'blog' => 'view_artefact',
            'blogpost' => 'view_artefact',
            'book' => 'usr',
            'comment' => 'artefact_comment_comment',
            'coverletter' => 'usr',
            'forum' => 'interaction_forum_post',
            'group' => 'group',
            'institution' => 'institution',
            // @TODO: Instructions needs to be updated.
            // view instructions are to become viewinstructions.
            // text can remain as instructions.
            // Update task: load all artefact_file_embedded records
            // where resourcetype = 'instructions' and update.
            // To update try to load the resourceid as a view and as a block instance.
            // Save it.  The images should update.
            // Check view instruction fields do not have an 'instructions' querystring.
            'instructions' => 'resourcetype_is_viewid',
            'viewinstructions' => 'view',
            'interest' => 'usr',
            'introduction' => 'usr',
            'introtext' => 'block_instance',
            'membership' => 'usr',
            'peerassessment' => 'block_instance',
            'peerinstruction' => 'block_instance',
            'personalgoal' => 'usr',
            'personalskill' => 'usr',
            'post' => 'interaction_forum_post',
            'staticpages' => 'site_content',
            'text' => 'block_instance',
            'textbox' => 'view_artefact',
            'textinstructions' => 'block_instance',
            'topic' => 'interaction_forum_topic',
            // Special case view images blocks (and to work with comments in the side modal).
            'view' => 'view_artefact', // generalise this to view_artefact as can be used there too
            'verification_comment' => 'blocktype_verification_comment',
            'wallpost' => 'blocktype_wall_post',
            'workskill' => 'usr',
        ];
    }

    /**
     * Return the resourcetable value for a resource type.
     *
     * @param string $resourcetype
     * @return string
     */
    public static function get_resourcetable($resourcetype) {
        $mapping = self::get_resourcetable_mapping();
        if (isset($mapping[$resourcetype])) {
            return $mapping[$resourcetype];
        }
        return '';
    }

    /**
     * Prepare/update embedded images
     *
     * These are generally artefacts inserted into a TinyMCE editor by clicking the 'Insert/edit image' button.
     *
     * Format HTML content in a WYSIWYG text box to correctly serve an embedded image
     * which was added via the TinyMCE imagebrowser plugin.
     * Add a database reference to the embedded image if required, to set viewing permissions for it
     *
     * @param string $fieldvalue The HTML source of the text body added to the TinyMCE text editor
     * @param string $resourcetype The type of resource which the TinyMCE editor is used in, e.g. 'forum', 'topic',
     *  'post' for forum text boxes
     * @param int $resourceid The resourcetype ID, e.g. the block instance using the resource (artefact)
     * @param int $groupid The id of the group the resource is in if applicable
     * @param int $userid The user trying to embed the image (current user if null)
     * @param int $checkonly Check if the fieldvalue needs to embed images only
     * @return string|boolean The updated $fieldvalue
     */
    public static function prepare_embedded_images(
        $fieldvalue,
        $resourcetype,
        $resourceid,
        $groupid = null,
        $userid = null,
        $checkonly = false
    ) {

        if (empty($fieldvalue) || empty($resourcetype) || empty($resourceid)) {
            return $fieldvalue;
        }

        // Check that resourcetype exists in get_resourcetable_mapping
        if (in_array($resourcetype, array_keys(self::get_resourcetable_mapping()))) {
            // yay - otherwise do not continue
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
                if ($checkonly) {
                    return false;
                }
                else {
                    self::remove_embedded_images($resourcetype, $resourceid);
                    return $fieldvalue;
                }
            }
            $hasimage = false;
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
                            $hasimage = true;
                            $publicimages[] = $imgid;
                            $imgispublic = get_field('artefact_file_embedded', 'id', 'fileid', $imgid, 'resourcetype', $resourcetype, 'resourceid', $resourceid);
                            // add to embedded_images table for public access, specifying context
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
            if ($checkonly) {
                return $hasimage;
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
        return $fieldvalue;
    }

    /**
     * Check to see if the markup contains an embedded image.
     *
     * This is useful if we just want to know if the markup contains an embedded image without
     * executing the delete embedded images part of the prepare_embedded_images() function.
     * Needed when checking other parts of a composite resume artefact when we need to find out if
     * any of the artefact's items has an embedded image (not just the particular artefact item we
     * are currently saving).
     *
     * @param string $fieldvalue The HTML source of the text body added to the TinyMCE text editor
     * @param string $resourcetype The type of resource which the TinyMCE editor is used in
     * @param int $resourceid The resourcetype ID, e.g. the block instance using the resource (artefact)
     * @param int $compositeid The id of the composite
     * @param int $userid The user trying to embed the image (current user if null)
     * @return boolean An embedded image exists
     */
    public static function has_embedded_image($fieldvalue, $resourcetype, $resourceid, $userid = NULL) {

        if (empty($fieldvalue) || empty($resourcetype) || empty($resourceid)) {
            // not enough info
            return false;
        }
        return self::prepare_embedded_images($fieldvalue, $resourcetype, $resourceid, NULL, $userid, true);
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

    /**
     * Locate *any* Views that the embedded file is used in by checking view_artefact.
     *
     * No access control is performed here.
     *
     * Important:
     * - not all artefacts in a view are in embedded in a TinyMCE, e.g. image blocks in views
     * - not all embedded files appear in view_artefacts, i.e. they are not all saved there
     *
     * At this point, the resourcetype doesn't matter as we are sure that
     * only artefacts are being used there
     * Artefacttypes are the matching resourcetypes (in this context),
     * See accesscontrol.php that ensures the resourcetable
     * is 'artefact' before calling this function.
     *
     * @param int $fileid The file ID we are looking up.
     * @return array $view_ids
     * @throws InvalidArgumentException
     */
    public static function find_viewids_from_embedded_file($fileid) {
        if (!is_int($fileid)) {
            throw new InvalidArgumentException("File ID must be an integer");
        }
        $validartefactresourcetypes = array_keys(self::get_resourcetable_mapping(), 'view_artefact');
        $validblockresourcetypes = array_keys(self::get_resourcetable_mapping(), 'block_instance');
        $sql = "
        /* Find Views matching View Artefacts artefact as the Resource ID */
        SELECT va.view
        FROM
            {view_artefact} va
            JOIN {artefact_file_embedded} afe ON afe.resourceid = va.artefact
            AND afe.resourcetype IN (" . join(',', array_map('db_quote', $validartefactresourcetypes)) . ")
        WHERE afe.fileid = ?
        /* Find Views matching View Artefacts block as the Resource ID */
        UNION
        SELECT va.view
        FROM
            {view_artefact} va
            JOIN {artefact_file_embedded} afe ON afe.resourceid = va.block
            AND afe.resourcetype IN (" . join(',', array_map('db_quote', $validblockresourcetypes)) . ")
        WHERE afe.fileid = ?
        ";
        $data = get_records_sql_array($sql, [$fileid, $fileid]);

        if (!$data) {
            return [];
        }

        $view_ids = [];
        foreach ($data as $object) {
            $view_ids[] = $object->view;
        }
        return $view_ids;
    }

    /**
     * Check if the embedded image is visible to the user.
     *
     * @param object $file The file object
     * @param string $resourcetype The type of the resource the image is in.
     * @param int $resourceid The id of the resource the image is in.
     * @return boolean
     */
    public static function can_see_embedded_image(object $file, $resourcetype = '', $resourceid = 0) {
        global $USER;
        return AccessControl::user($USER)
            ->set_file($file)
            ->set_resource($resourcetype, $resourceid)
            ->is_visible();
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
                    . 'https?\://.*?/artefact/file/download\.php\?file\='
                    . '([\d]+)'
                    . '(&|&amp;)embedded=1([^"]*)"#',
                    $text,
                    $matches)
                ) {
                foreach ($matches[3] as $id) {
                    if (!empty($artefactids["portfolio:artefact$id"])) {
                        // Replace the old image id by the new one
                        $regexp[] = '#<img([^>]+)src=("|\\")'
                            . 'https?\://.*?/artefact/file/download\.php\?file\=' . $id
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
