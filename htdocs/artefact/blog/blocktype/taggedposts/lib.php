<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype.blog/taggedposts
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeTaggedposts extends MaharaCoreBlocktype {

    const TAGTYPE_INCLUDE = 1;
    const TAGTYPE_EXCLUDE = 0;

    public static function get_title() {
        return get_string('title', 'blocktype.blog/taggedposts');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.blog/taggedposts');
    }

    public static function get_categories() {
        return array('blog' => 13000);
    }

    public static function get_instance_javascript(BlockInstance $bi) {
        $blockid = $bi->get('id');
        return array(
            array(
                'file'   => 'js/taggedposts.js',
                'initjs' => "addNewTaggedPostShortcut($blockid);",
            )
        );
    }

    /**
     * Given a list of tags, finds blocks by the current user that contain those tags
     * (Used to determine which ones to check for the view_artefact table)
     * @param array $tags
     * @return array
     */
    public static function find_matching_blocks(array $tags) {
        global $USER;

        $taggedblockids = (array)get_column_sql(
                'SELECT bi.id as block
                FROM
                    {blocktype_taggedposts_tags} btt
                    INNER JOIN {block_instance} bi
                        ON btt.block_instance = bi.id
                    INNER JOIN {view} v
                        ON bi.view = v.id
                WHERE
                    v.owner = ?
                    AND btt.tagtype = ?
                    AND btt.tag IN (' . implode(',', db_array_to_ph($tags)) . ')
                ',
                array_merge(
                    array(
                        $USER->id,
                        PluginBlocktypeTaggedposts::TAGTYPE_INCLUDE
                    ),
                    $tags
                )
        );
        if ($taggedblockids) {
            return $taggedblockids;
        }
        else {
            return array();
        }
    }

    /**
     * Returns the blog posts that will be displayed by this block.
     *
     * @param BlockInstance $instance
     * @param array $tagsin Optional reference variable for finding out the "include" tags used by this block
     * @param array $tagsout Optional reference variable for finding out the "extclude" tags used by this block
     * @return array of blogpost records
     */
    public static function get_blog_posts_in_block(BlockInstance $instance, &$tagsinreturn = null, &$tagsoutreturn = null, $versioning=false) {
        $configdata = $instance->get('configdata');
        $results = array();

        $tagsin = $tagsout = array();
        if ($versioning) {
            $tagrecords = $configdata['tagrecords'];
        }
        else {
            $tagrecords = get_records_array('blocktype_taggedposts_tags', 'block_instance', $instance->get('id'), 'tagtype desc, tag', 'tag, tagtype');
        }
        if ($tagrecords) {

            $view = $instance->get('view');
            $limit = isset($configdata['count']) ? (int) $configdata['count'] : 10;

            foreach ($tagrecords as $tag) {
                //tag is encoded in the db if it has special characters
                $tag->tag = htmlspecialchars_decode($tag->tag);
                if ($tag->tagtype == PluginBlocktypeTaggedposts::TAGTYPE_INCLUDE) {
                    $tagsin[] = $tag->tag;
                }
                else {
                    $tagsout[] = $tag->tag;
                }
            }
            $tagsout = array_filter($tagsout);
            $sqlvalues = array($view);
            $typecast = is_postgres() ? '::varchar' : '';
            $sql =
                "SELECT a.title, p.title AS parenttitle, a.id, a.parent, a.owner, a.author, a.authorname,
                    a.description, a.allowcomments, at.tag, a.ctime, a.mtime
                FROM {artefact} a
                JOIN {artefact} p ON a.parent = p.id
                JOIN {artefact_blog_blogpost} ab ON (ab.blogpost = a.id AND ab.published = 1)
                JOIN {tag} at ON (at.resourcetype = 'artefact' AND at.resourceid = a.id" . $typecast . ")
                WHERE a.artefacttype = 'blogpost'
                AND a.owner = (SELECT \"owner\" from {view} WHERE id = ?)";
            if (!empty($tagsin)) {
                foreach ($tagsin as $tagin) {
                    $sql .= " AND EXISTS (
                        SELECT * FROM {tag} AS at
                        WHERE at.resourcetype = 'artefact' AND at.resourceid = a.id" . $typecast . "
                        AND at.tag = ?
                    )";
                }
                $sqlvalues = array_merge($sqlvalues, $tagsin);
            }
            if (!empty($tagsout)) {
                foreach ($tagsout as $tagout) {
                    $sql .= " AND NOT EXISTS (
                        SELECT * FROM {tag} AS at
                        WHERE at.resourcetype = 'artefact' AND at.resourceid = a.id" . $typecast . "
                        AND at.tag = ?
                    )";
                }
                $sqlvalues = array_merge($sqlvalues, $tagsout);
            }
            $sql .= ' ORDER BY a.ctime DESC, a.id DESC';
            $results = get_records_sql_array($sql, $sqlvalues);
            // We need to filter this down to unique results
            if (!empty($results)) {
                $used = array();
                foreach ($results as $key => $result) {
                    if (array_search($result->id, $used) === false) {
                        $used[] = $result->id;
                    }
                    else {
                        unset($results[$key]);
                    }
                }
                if (!empty($limit)) {
                    $results = array_slice($results, 0, $limit);
                }
            }
            else {
                $results = array();
            }
        }

        if ($tagsinreturn !== null) {
            $tagsinreturn = $tagsin;
        }
        if ($tagsoutreturn !== null) {
            $tagsoutreturn = $tagsout;
        }

        return $results;
    }

    public static function render_instance(BlockInstance $instance, $editing=false, $versioning=false) {
        global $USER;

        $configdata = $instance->get('configdata');
        $view = $instance->get('view');
        $full = isset($configdata['full']) ? $configdata['full'] : false;
        $results = array();

        $smarty = smarty_core();
        $smarty->assign('view', $view);
        $viewownerdisplay = null;
        // Display all posts, from all blogs, owned by this user
        $tagsin = $tagsout = array();
        $results = self::get_blog_posts_in_block($instance, $tagsin, $tagsout, $versioning);
        if ($tagsin || $tagsout) {

            $smarty->assign('blockid', $instance->get('id'));
            $smarty->assign('editing', $editing);
            if ($editing) {
                // Get list of blogs owned by this user to create the "Add new post" shortcut while editing
                $viewowner = $instance->get_view()->get('owner');
                if (!$viewowner || !$blogs = get_records_select_array('artefact', 'artefacttype = \'blog\' AND owner = ?', array($viewowner), 'title ASC', 'id, title')) {
                    $blogs = array();
                }
                $smarty->assign('tagselect', implode(', ', $tagsin));
                $smarty->assign('blogs', $blogs);
            }

            // if posts are not found with the selected tag, notify the user
            if (!$results) {
                $smarty->assign('badtag', implode(', ', $tagsin));
                $smarty->assign('badnotag', implode(', ', $tagsout));
                return $smarty->fetch('blocktype:taggedposts:taggedposts.tpl');
            }

            // update the view_artefact table so journal entries are accessible when this is the only block on the page
            // referencing this journal
            $dataobject = array(
                'view'      => $view,
                'block'     => $instance->get('id'),
            );
            require_once(get_config('docroot') . 'lib/view.php');
            $viewobj = new View($view);
            require_once(get_config('docroot') . 'artefact/lib.php');
            safe_require('artefact', 'blog');
            safe_require('artefact', 'comment');
            foreach ($results as $result) {
                $dataobject["artefact"] = $result->parent;
                $result->displaydate= format_date(strtotime($result->ctime));
                $by = $result->author ? display_default_name($result->author) : $result->authorname;
                $result->postedbyon = ArtefactTypeBlog::display_postedby($result->ctime, $by);

                if ($result->ctime != $result->mtime) {
                    $result->updateddate= format_date(strtotime($result->mtime));
                }

                $artefact = new ArtefactTypeBlogpost($result->id);
                // get comments for this post
                $result->commentcount = count_records_select('artefact_comment_comment', "onartefact = {$result->id} AND private = 0 AND deletedby IS NULL AND hidden=0");
                $allowcomments = $artefact->get('allowcomments');
                if (empty($result->commentcount) && empty($allowcomments)) {
                    $result->commentcount = null;
                }

                list($commentcount, $comments) = ArtefactTypeComment::get_artefact_comments_for_view($artefact, $viewobj, null, false, false, $versioning);
                $result->comments = $comments;

                // get all tags for this post
                $taglist = get_records_sql_array("SELECT tag FROM {tag} WHERE resourcetype = 'artefact' AND resourceid = ? ORDER BY tag DESC", array($result->id));
                foreach ($taglist as $t) {
                    $result->taglist[] = $t->tag;
                }
                if ($full) {
                    $rendered = $artefact->render_self(array('viewid' => $view, 'details' => true, 'blockid' => $instance->get('id')));
                    $result->html = $rendered['html'];
                    if (!empty($rendered['javascript'])) {
                        $result->html .= '<script>' . $rendered['javascript'] . '</script>';
                    }
                    $attachments = $rendered['attachments'];
                    if (!empty($attachments)) {
                        $smarty->assign('attachments', $attachments);
                        $smarty->assign('postid', $result->id);
                        $result->attachments = $smarty->fetch('artefact:blog:render/blogpost_renderattachments.tpl');
                    }

                    safe_require('artefact', 'file');
                    $result->description = ArtefactTypeFolder::append_view_url($result->description, $view);
                }
            }

            // check if the user viewing the page is the owner of the selected tag
            $owner = $results[0]->owner;
            if ($USER->is_logged_in() && $USER->id != $owner) {
                $viewownerdisplay = get_user_for_display($owner);
            }
            $smarty->assign('tagsin', $tagsin);
            $smarty->assign('tagsout', $tagsout);
        }
        else if (!self::get_chooseable_tags()) {
            // error if block configuration fails
            $smarty->assign('configerror', get_string('notagsavailableerror', 'blocktype.blog/taggedposts'));
            return $smarty->fetch('blocktype:taggedposts:taggedposts.tpl');
        }
        else {
            // error if block configuration fails
            $smarty->assign('configerror', get_string('configerror', 'blocktype.blog/taggedposts'));
            return $smarty->fetch('blocktype:taggedposts:taggedposts.tpl');
        }

        // add any needed links to the tags
        $tagstr = $tagomitstr = '';
        foreach ($tagsin as $key => $tag) {
            if ($key > 0) {
                $tagstr .= ', ';
            }
            if (strpos($tag, 'tagid_') !== false) {
                $tags = get_records_sql_array("
                    SELECT CONCAT(i.displayname, ': ', t.tag) AS tag, t.resourceid
                    FROM {tag} t
                    LEFT JOIN {institution} i ON i.name = t.ownerid
                    WHERE t.id = ?", array(substr($tag, 6, 5))
                );
                $tag = $tags[0]->tag;
            }
            $tagstr .= ($USER->id != $owner) ? '"<a href="' . get_config('wwwroot') . 'relatedtags.php?tag=' . urlencode($tag) . '&view=' . $view . '">' . hsc($tag) . '</a>"' : '"<a href="' . get_config('wwwroot') . 'tags.php?tag=' . urlencode($tag) . '&sort=name&type=text">' . hsc($tag) . '</a>"';
        }
        if (!empty($tagsout)) {
            foreach ($tagsout as $key => $tag) {
                if ($key > 0) {
                    $tagomitstr .= ', ';
                }
                $tagomitstr .= ($USER->id != $owner) ? '"<a href="' . get_config('wwwroot') . 'relatedtags.php?tag=' . urlencode($tag) . '&view=' . $view . '">' . hsc($tag) . '</a>"' : '"<a href="' . get_config('wwwroot') . 'tags.php?tag=' . urlencode($tag) . '&sort=name&type=text">' . hsc($tag) . '</a>"';
            }
        }
        if (empty($tagsin)) {
            $blockheading = get_string('blockheadingtagsomitonly', 'blocktype.blog/taggedposts', count($tagsout), $tagomitstr);
        }
        else {
            $blockheading = get_string('blockheadingtags', 'blocktype.blog/taggedposts', count($tagsin), $tagstr);
            $blockheading .= (!empty($tagomitstr)) ? get_string('blockheadingtagsomitboth', 'blocktype.blog/taggedposts', count($tagsout), $tagomitstr) : '';
        }
        $blockheading .= ($viewownerdisplay) ? ' ' . get_string('by', 'artefact.blog') . ' <a href="' . profile_url($viewownerdisplay) . '">' . display_name($viewownerdisplay) . '</a>' : '';
        $smarty->assign('full', $full);
        $smarty->assign('results', $results);
        $smarty->assign('blockheading', $blockheading);
        return $smarty->fetch('blocktype:taggedposts:taggedposts.tpl');
    }

    private static function get_selected_tags() {

    }


    /**
     * Get the tags the user can choose from
     * (i.e. tags they use on their blogpost artefacts)
     * @return array
     */
    private static function get_chooseable_tags() {
        global $USER;

        $typecast = is_postgres() ? '::varchar' : '';
        return get_records_sql_array("
            SELECT at.tag
            FROM {tag} at
            JOIN {artefact} a ON (at.resourcetype ='artefact' AND at.resourceid = a.id" . $typecast . ")
            WHERE
                a.owner = ?
                AND a.artefacttype = 'blogpost'
            GROUP BY at.tag
            ORDER BY at.tag ASC
            ", array($USER->id));
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form(BlockInstance $instance) {
        global $USER;
        safe_require('artefact', 'blog');
        $configdata = $instance->get('configdata');
        $tags = self::get_chooseable_tags();

        $elements = array();
        if (!empty($tags)) {
            $tagselect = array();
            $typecast = is_postgres() ? '::varchar' : '';
            $tagrecords = get_records_sql_array("
                 SELECT
                     (CASE
                         WHEN bt.tag LIKE 'tagid_%' THEN CONCAT(i.displayname, ': ', t.tag)
                         ELSE bt.tag
                     END) AS tag, bt.tagtype
                 FROM {blocktype_taggedposts_tags} bt
                 LEFT JOIN {tag} t ON t.id" . $typecast . " = SUBSTRING(bt.tag, 7)
                 LEFT JOIN {institution} i ON i.name = t.ownerid
                 WHERE bt.block_instance = ?
                ORDER BY tagtype DESC", array($instance->get('id')));
            if ($tagrecords) {
                foreach ($tagrecords as $tag) {
                    if ($tag->tagtype == PluginBlocktypeTaggedposts::TAGTYPE_INCLUDE) {
                        $tagselect[] = $tag->tag;
                    }
                    else {
                        $tagselect[] = '-' . $tag->tag;
                    }
                }
            }
            // The javascript to alter the display for the excluded tags
            $excludetag = get_string('excludetag', 'blocktype.blog/taggedposts');
            $formatSelection = <<<EOF
function (item, container) {
    item.title = item.id;
    if (item.id[0] == "-") {
        container.addClass("tagexcluded");
        if (!item.text.match(/sr\-only/)) {
            return '<span class="sr-only">{$excludetag}</span>' + jQuery('<div>').text(item.text).html();
        }
    }
    return item.text;
}
EOF;
            $elements['tagselect'] = array(
                'type'          => 'autocomplete',
                'title'         => get_string('taglist','blocktype.blog/taggedposts'),
                'description'   => get_string('taglistdesc1', 'blocktype.blog/taggedposts'),
                'defaultvalue'  => $tagselect,
                'ajaxurl' => get_config('wwwroot') . 'artefact/blog/blocktype/taggedposts/taggedposts.json.php',
                'initfunction' => 'translate_ids_to_tags',
                'multiple' => true,
                'ajaxextraparams' => array(),
                'rules'         => array('required' => 'true'),
                'required'      => true,
                'blockconfig'   => true,
                'help'          => true,
                'mininputlength' => 0,
                'extraparams'   => array(
                        'templateSelection' => "$formatSelection",
                        // We'll escape the text on the PHP side, so select2 doesn't need to
                        'escapeMarkup' => 'function(textToEscape) {
                            if (textToEscape.match(/sr\-only/)) {
                                return textToEscape;
                            }
                            else {
                                return jQuery("<div>").text(textToEscape).html();
                            }
                        }',
                ),
            );
            $elements[] = PluginArtefactBlog::block_advanced_options_element($configdata, 'taggedposts');
            $elements['count']  = array(
                'type'          => 'text',
                'title'         => get_string('itemstoshow', 'blocktype.blog/taggedposts'),
                'description'   => get_string('betweenxandy', 'mahara', 1, 100),
                'defaultvalue'  => isset($configdata['count']) ? $configdata['count'] : 10,
                'size'          => 3,
                'rules'         => array('integer' => true, 'minvalue' => 1, 'maxvalue' => 999),
            );
            $elements['full']  = array(
                'type'         => 'switchbox',
                'title'        => get_string('showjournalitemsinfull', 'blocktype.blog/taggedposts'),
                'description'  => get_string('showjournalitemsinfulldesc1', 'blocktype.blog/taggedposts'),
                'defaultvalue' => isset($configdata['full']) ? $configdata['full'] : false,
            );

            return $elements;
        }
        else {
            return array(
                'notags'    => array(
                    'type'          => 'html',
                    'title'         => get_string('taglist', 'blocktype.blog/taggedposts'),
                    'value'         => get_string('notagsavailable', 'blocktype.blog/taggedposts'),
                ),
            );
        }

    }

    public static function delete_instance(BlockInstance $instance) {
        delete_records('blocktype_taggedposts_tags', 'block_instance', $instance->get('id'));
    }

    public static function instance_config_validate(Pieform $form, $values) {

        if (empty($values['tagselect'])) {
            // We don't have a tagselect field due to no journal entries having a tag
            $form->set_error(null, get_string('notagsavailableerror', 'blocktype.blog/taggedposts'));
        }
        else {
            // Need to fully check that the returned array is empty
            $values['tagselect'] = array_filter($values['tagselect']);
            if (empty($values['tagselect'])) {
                $result['message'] = get_string('required', 'mahara');
                $form->set_error('tagselect', $form->i18n('rule', 'required', 'required'), false);
                $form->reply(PIEFORM_ERR, $result);
            }
        }
    }

    public static function instance_config_save($values, BlockInstance $instance) {
        $tagselect = $values['tagselect'];
        unset($values['tagselect']);
        if (!empty($tagselect)) {
            self::save_tag_selection($tagselect, $instance);
        }
        return $values;
    }

    public static function save_tag_selection($tagselect, BlockInstance $instance) {
        delete_records('blocktype_taggedposts_tags', 'block_instance', $instance->get('id'));
        foreach ($tagselect as $tag) {
            $value = PluginBlocktypeTaggedposts::TAGTYPE_INCLUDE;
            if (substr($tag, 0, 1) == '-') {
                $value = PluginBlocktypeTaggedposts::TAGTYPE_EXCLUDE;
                $tag = substr($tag, 1);
            }
            // If tag is institution tag, save it's correct form.
            if (strpos($tag, ':')) {
                $tagarray = explode(': ', $tag);
                $sql = "SELECT t.id
                    FROM {tag} t
                    JOIN {institution} i ON i.name = t.ownerid
                    WHERE t.tag = ? AND t.resourcetype = 'institution' AND i.displayname = ?";
                $insttagid = get_field_sql($sql, array($tagarray[1], $tagarray[0]));
                $tag = 'tagid_' . $insttagid;
            }
            $todb = new stdClass();
            $todb->block_instance = $instance->get('id');
            $todb->tag = htmlspecialchars_decode($tag);
            $todb->tagtype = $value;
            insert_record('blocktype_taggedposts_tags', $todb);
        }
    }

    /**
     * Returns a list of artefact IDs that are "in" this blockinstance.
     *
     * {@internal{Because links to artefacts within blogposts don't count
     * as making those artefacts 'children' of the blog post, we have to add
     * them directly to the blog.}}
     *
     * @return array List of artefact IDs that are 'in' this blog - all
     *               blogposts in it plus all links to other artefacts that are
     *               part of the blogpost text. Note that proper artefact
     *               children, such as blog post attachments, aren't included -
     *               the artefact parent cache is used for them
     * @see PluginBlocktypeBlogPost::get_artefacts()
     */
    public static function get_artefacts(BlockInstance $instance) {
        $artefacts = array();
        $blogposts = self::get_blog_posts_in_block($instance);
        foreach ($blogposts as $blogpost) {
            $artefacts[] = $blogpost->id;

            $blogpostobj = $instance->get_artefact_instance($blogpost->id);
            $artefacts = array_merge($artefacts, $blogpostobj->get_referenced_artefacts_from_postbody());
        }
        $artefacts = array_unique($artefacts);
        return $artefacts;
    }

    public static function default_copy_type() {
        return 'nocopy';
    }

    /**
     * Taggedposts blocktype is only allowed in personal views, because currently
     * there's no such thing as group/site blogs
     */
    public static function allowed_in_view(View $view) {
        return $view->get('owner') != null;
    }

}

function translate_ids_to_tags(array $ids) {
    $ids = array_diff($ids, array(''));
    $results = array();
    if (!empty($ids)) {
        foreach ($ids as $id) {
            $text = (substr($id, 0, 1) == '-') ? substr($id, 1) : $id;
            $results[] = (object) array('id' => $id, 'text' => $text);
        }
    }
    return $results;
}
