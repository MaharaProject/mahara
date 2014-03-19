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

class PluginBlocktypeTaggedposts extends SystemBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.blog/taggedposts');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.blog/taggedposts');
    }

    public static function get_categories() {
        return array('blog');
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

    public static function render_instance(BlockInstance $instance, $editing=false) {
        global $USER;

        $configdata = $instance->get('configdata');
        $view = $instance->get('view');
        $limit = isset($configdata['count']) ? (int) $configdata['count'] : 10;
        $full = isset($configdata['full']) ? $configdata['full'] : false;
        $results = array();

        $smarty = smarty_core();
        $smarty->assign('view', $view);
        $viewownerdisplay = null;
        // Display all posts, from all blogs, owned by this user
        $configdata['tagselect'] = (!empty($configdata['tagselect'])) ? $configdata['tagselect'] : array();
        if (!empty($configdata['tagselect'])) {
            $tagselect = $configdata['tagselect'];
            $tagsin = $tagsout = array();
            foreach ($tagselect as $key => $value) {
                if (!empty($value)) {
                    $tagsin[] = $key;
                }
                else {
                    $tagsout[] = $key;
                }
            }
            $tagsout = array_filter($tagsout);
            $sqlvalues = array($view);
            $sql =
                'SELECT a.title, p.title AS parenttitle, a.id, a.parent, a.owner, a.description, a.allowcomments, at.tag, a.ctime
                FROM {artefact} a
                JOIN {artefact} p ON a.parent = p.id
                JOIN {artefact_blog_blogpost} ab ON (ab.blogpost = a.id AND ab.published = 1)
                JOIN {artefact_tag} at ON (at.artefact = a.id)
                WHERE a.artefacttype = \'blogpost\'
                AND a.owner = (SELECT "owner" from {view} WHERE id = ?)';
            if (!empty($tagsin)) {
                foreach ($tagsin as $tagin) {
                    $sql .= ' AND EXISTS (
                        SELECT * FROM {artefact_tag} AS at
                        WHERE a.id = at.artefact
                        AND at.tag = ?
                    )';
                }
                $sqlvalues = array_merge($sqlvalues, $tagsin);
            }
            if (!empty($tagsout)) {
                foreach ($tagsout as $tagout) {
                    $sql .= ' AND NOT EXISTS (
                        SELECT * FROM {artefact_tag} AS at
                        WHERE a.id = at.artefact
                        AND at.tag = ?
                    )';
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
                $smarty->assign('badtag', implode(', ', array_keys($tagselect,1)));
                $smarty->assign('badnotag', implode(', ', array_keys($tagselect,0)));
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
                ensure_record_exists('view_artefact', $dataobject, $dataobject);
                $result->postedbyon = get_string('postedbyon', 'artefact.blog', display_default_name($result->owner), format_date(strtotime($result->ctime)));
                $result->displaydate= format_date(strtotime($result->ctime));

                $artefact = new ArtefactTypeBlogpost($result->id);
                // get comments for this post
                $result->commentcount = count_records_select('artefact_comment_comment', "onartefact = {$result->id} AND private = 0 AND deletedby IS NULL");
                $allowcomments = $artefact->get('allowcomments');
                if (empty($result->commentcount) && empty($allowcomments)) {
                    $result->commentcount = null;
                }

                list($commentcount, $comments) = ArtefactTypeComment::get_artefact_comments_for_view($artefact, $viewobj, null, false);
                $result->comments = $comments;

                // get all tags for this post
                $taglist = get_records_array('artefact_tag', 'artefact', $result->id, "tag DESC");
                foreach ($taglist as $t) {
                    $result->taglist[] = $t->tag;
                }
                if ($full) {
                    $rendered = $artefact->render_self(array('viewid' => $view, 'details' => true));
                    $result->html = $rendered['html'];
                    if (!empty($rendered['javascript'])) {
                        $result->html .= '<script type="text/javascript">' . $rendered['javascript'] . '</script>';
                    }
                }
            }

            // check if the user viewing the page is the owner of the selected tag
            $owner = $results[0]->owner;
            if ($USER->id != $owner) {
                $viewownerdisplay = get_user_for_display($owner);
            }
            $smarty->assign('tagsin', $tagsin);
            $smarty->assign('tagsout', $tagsout);
        }
        else if (!self::get_tags()) {
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
            $tagstr .= ($viewownerdisplay) ? '"' . $tag . '"' : '"<a href="' . get_config('wwwroot') . 'tags.php?tag=' . $tag . '&sort=name&type=text">' . $tag . '</a>"';
        }
        if (!empty($tagsout)) {
            foreach ($tagsout as $key => $tag) {
                if ($key > 0) {
                    $tagomitstr .= ', ';
                }
                $tagomitstr .= ($viewownerdisplay) ? '"' . $tag . '"' : '"<a href="' . get_config('wwwroot') . 'tags.php?tag=' . $tag . '&sort=name&type=text">' . $tag . '</a>"';
            }
        }
        $blockheading = get_string('blockheadingtags', 'blocktype.blog/taggedposts', count($tagsin), $tagstr);
        $blockheading .= (!empty($tagomitstr)) ? get_string('blockheadingtagsomit', 'blocktype.blog/taggedposts', count($tagsout), $tagomitstr) : '';
        $blockheading .= ($viewownerdisplay) ? ' ' . get_string('by', 'artefact.blog') . ' <a href="' . profile_url($viewownerdisplay) . '">' . display_name($viewownerdisplay) . '</a>' : '';
        $smarty->assign('full', $full);
        $smarty->assign('results', $results);
        $smarty->assign('blockheading', $blockheading);
        return $smarty->fetch('blocktype:taggedposts:taggedposts.tpl');
    }

    private static function get_tags() {
        global $USER;

        return get_records_sql_array("
            SELECT at.tag
            FROM {artefact_tag} at
            JOIN {artefact} a
            ON a.id = at.artefact
            WHERE a.owner = ?
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

        $configdata = $instance->get('configdata');
        $tags = self::get_tags();

        $elements = array();
        $tagselect = array();
        if (!empty($tags)) {
            if (!empty($configdata['tagselect'])) {
                foreach ($configdata['tagselect'] as $tag => $option) {
                    if ($option == '1') {
                        $tagselect[] = $tag;
                    }
                    else {
                        $tagselect[] = '-' . $tag;
                    }
                }
            }
            // The javascript to alter the display for the excluded tags
            $excludetag = get_string('excludetag', 'blocktype.blog/taggedposts');
            $formatSelection = <<<EOF
function (item, container) {
    if (item.id[0] == "-") {
        container.parent().addClass("tagexcluded");
        item.text = '<span class="accessible-hidden">{$excludetag}</span>' + item.text;
    }
    return item.text;
}
EOF;
            $elements['tagselect'] = array(
                'type'          => 'autocomplete',
                'title'         => get_string('taglist','blocktype.blog/taggedposts'),
                'description'   => get_string('taglistdesc', 'blocktype.blog/taggedposts'),
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
                'extraparams'   => array('formatSelection' => "$formatSelection"),
            );
            $elements['count']  = array(
                'type'          => 'text',
                'title'         => get_string('itemstoshow', 'blocktype.blog/taggedposts'),
                'description'   => get_string('betweenxandy', 'mahara', 1, 100),
                'defaultvalue'  => isset($configdata['count']) ? $configdata['count'] : 10,
                'size'          => 3,
                'rules'         => array('integer' => true, 'minvalue' => 1, 'maxvalue' => 999),
            );
            $elements['full']  = array(
                'type'         => 'checkbox',
                'title'        => get_string('showjournalitemsinfull', 'blocktype.blog/taggedposts'),
                'description'  => get_string('showjournalitemsinfulldesc', 'blocktype.blog/taggedposts'),
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

    public static function instance_config_validate($form, $values) {

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

    public static function instance_config_save($values) {
        $tagselect = $values['tagselect'];
        unset($values['tagselect']);
        if (!empty($tagselect)) {
            foreach ($tagselect as $tag) {
                $value = 1;
                if (substr($tag, 0, 1) == '-') {
                    $value = 0;
                    $tag = substr($tag, 1);
                }
                $values['tagselect'][$tag] = $value;
            }
        }
        return $values;
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
