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

        // Display all posts, from all blogs, owned by this user
        if (!empty($configdata['tagselect'])) {
            $tagselect = $configdata['tagselect'];

            $sql =
                'SELECT a.title, p.title AS parenttitle, a.id, a.parent, a.owner, a.description, a.allowcomments, at.tag, a.ctime
                FROM {artefact} a
                JOIN {artefact} p ON a.parent = p.id
                JOIN {artefact_blog_blogpost} ab ON (ab.blogpost = a.id AND ab.published = 1)
                JOIN {artefact_tag} at ON (at.artefact = a.id)
                WHERE a.artefacttype = \'blogpost\'
                AND a.owner = (SELECT "owner" from {view} WHERE id = ?)
                AND at.tag = ?
                ORDER BY a.ctime DESC, a.id DESC
                LIMIT ?';

            $results = get_records_sql_array($sql, array($view, $tagselect, $limit));

            $smarty->assign('blockid', $instance->get('id'));
            $smarty->assign('editing', $editing);
            if ($editing) {
                // Get list of blogs owned by this user to create the "Add new post" shortcut while editing
                $viewowner = $instance->get_view()->get('owner');
                if (!$viewowner || !$blogs = get_records_select_array('artefact', 'artefacttype = \'blog\' AND owner = ?', array($viewowner), 'title ASC', 'id, title')) {
                    $blogs = array();
                }
                $smarty->assign('tagselect', $tagselect);
                $smarty->assign('blogs', $blogs);
            }

            // if posts are not found with the selected tag, notify the user
            if (!$results) {
                $smarty->assign('badtag', $tagselect);
                return $smarty->fetch('blocktype:taggedposts:taggedposts.tpl');
            }

            // update the view_artefact table so journal entries are accessible when this is the only block on the page
            // referencing this journal
            $dataobject = array(
                'view'      => $view,
                'block'     => $instance->get('id'),
            );

            foreach ($results as $result) {
                $dataobject["artefact"] = $result->parent;
                ensure_record_exists('view_artefact', $dataobject, $dataobject);
                $result->postedbyon = get_string('postedbyon', 'artefact.blog', display_default_name($result->owner), format_date(strtotime($result->ctime)));
                $result->displaydate= format_date(strtotime($result->ctime));

                // get comment count for this post
                $result->commentcount = count_records_select('artefact_comment_comment', "onartefact = {$result->id} AND private = 0 AND deletedby IS NULL");

                // get all tags for this post
                $taglist = get_records_array('artefact_tag', 'artefact', $result->id, "tag DESC");
                foreach ($taglist as $t) {
                    $result->taglist[] = $t->tag;
                }
            }

            // check if the user viewing the page is the owner of the selected tag
            $owner = $results[0]->owner;
            if ($USER->id != $owner) {
                $viewowner = get_user_for_display($owner);
                $smarty->assign('viewowner', $viewowner);
            }

            $smarty->assign('tag', $tagselect);
        }
        else {
            // error if block configuration fails
            $smarty->assign('configerror', true);
            return $smarty->fetch('blocktype:taggedposts:taggedposts.tpl');
        }

        $smarty->assign('full', $full);
        $smarty->assign('results', $results);
        return $smarty->fetch('blocktype:taggedposts:taggedposts.tpl');
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        global $USER;

        $configdata = $instance->get('configdata');

        $tags = get_records_sql_array("
            SELECT at.tag
            FROM {artefact_tag} at
            JOIN {artefact} a
            ON a.id = at.artefact
            WHERE a.owner = ?
            AND a.artefacttype = 'blogpost'
            GROUP BY at.tag
            ORDER BY at.tag ASC
            ", array($USER->id));

        $elements = array();
        $options = array();
        if (!empty($tags)) {
            foreach ($tags as $tag) {
                $options[$tag->tag] = $tag->tag;
            }
            $elements['tagselect'] = array(
                'type'          => 'select',
                'title'         => get_string('taglist','blocktype.blog/taggedposts'),
                'options'       => $options,
                'defaultvalue'  => !empty($configdata['tagselect']) ? $configdata['tagselect'] : $tags[0]->tag,
                'required'      => true,
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
