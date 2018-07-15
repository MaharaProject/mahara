<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-blogpost
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeBlogpost extends MaharaCoreBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.blog/blogpost');
    }

    /**
     * Optional method. If exists, allows this class to decide the title for
     * all blockinstances of this type
     */
    public static function get_instance_title(BlockInstance $bi) {
        $configdata = $bi->get('configdata');

        if (!empty($configdata['artefactid'])) {
            return $bi->get_artefact_instance($configdata['artefactid'])->get('title');
        }
        return '';
    }

    public static function get_description() {
        return get_string('description', 'blocktype.blog/blogpost');
    }

    public static function get_categories() {
        return array('blog' => 11000);
    }

    public static function render_instance(BlockInstance $instance, $editing=false, $versioning=false) {
        $configdata = $instance->get('configdata');

        $result = '';
        $artefactid = isset($configdata['artefactid']) ? $configdata['artefactid'] : null;
        if ($artefactid) {
            require_once(get_config('docroot') . 'artefact/lib.php');
            $artefact = $instance->get_artefact_instance($artefactid);
            $configdata['hidetitle'] = true;
            $configdata['countcomments'] = true;
            $configdata['viewid'] = $instance->get('view');
            $configdata['blockid'] = $instance->get('id');
            $result = $artefact->render_self($configdata);
            $result = $result['html'];
            require_once(get_config('docroot') . 'artefact/comment/lib.php');
            require_once(get_config('docroot') . 'lib/view.php');
            $view = new View($configdata['viewid']);
            list($commentcount, $comments) = ArtefactTypeComment::get_artefact_comments_for_view($artefact, $view, $instance->get('id'), true, $editing, $versioning);
        }

        $smarty = smarty_core();
        if ($artefactid) {
            $smarty->assign('commentcount', $commentcount);
            $smarty->assign('comments', $comments);
        }
        $smarty->assign('html', $result);
        return $smarty->fetch('blocktype:blogpost:blogpost.tpl');
    }

    /**
     * Returns a list of artefact IDs that are in this blockinstance.
     *
     * Normally this would just include the blogpost ID itself (children such
     * as attachments don't need to be included here, they're handled by the
     * artefact parent cache). But people might just link to artefacts without
     * using the attachment facility. There's nothing wrong with them doing
     * that, so if they do we should scrape the post looking for such links and
     * include those artefacts as being part of this blockinstance.
     *
     * @return array List of artefact IDs that are 'in' this blogpost - all
     *               the blogpost ID plus links to other artefacts that are
     *               part of the blogpost text. Note that proper artefact
     *               children, such as blog post attachments, aren't included -
     *               the artefact parent cache is used for them
     * @see PluginBlocktypeBlog::get_artefacts()
     */
    public static function get_artefacts(BlockInstance $instance) {
        $configdata = $instance->get('configdata');
        $artefacts = array();
        if (isset($configdata['artefactid'])) {
            $artefacts[] = $configdata['artefactid'];

            // Add all artefacts found in the blogpost text
            $blogpost = $instance->get_artefact_instance($configdata['artefactid']);
            $artefacts = array_unique(array_merge($artefacts, $blogpost->get_referenced_artefacts_from_postbody()));
        }
        return $artefacts;
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form(BlockInstance $instance) {
        global $USER;
        safe_require('artefact', 'blog');
        $configdata = $instance->get('configdata');

        if (!empty($configdata['artefactid'])) {
            $blog = $instance->get_artefact_instance($configdata['artefactid']);
        }

        $elements = array();

        // If the blog post in this block is owned by the owner of the View,
        // then the block can be configured. Otherwise, the blog post was
        // copied in from another View. We won't confuse users by asking them to
        // choose a blog post to put in this block, when the one that is
        // currently in it isn't choosable.
        $institution = $instance->get('view_obj')->get('institution');
        $group = $instance->get('view_obj')->get('group');
        if (empty($configdata['artefactid']) || ArtefactTypeBlog::can_edit_blog($blog, $institution, $group)) {
            $sql = "SELECT a.id FROM {artefact} a
                    INNER JOIN {artefact_blog_blogpost} p ON p.blogpost = a.id
                    WHERE p.published = 1";
            if ($institution) {
                $sql .= " AND a.institution = ?";
                $where = array($institution);
            }
            else if ($group) {
                $sql .= " AND ( a.group = ? OR a.owner = ? )";
                $where = array($group, $USER->get('id'));
            }
            else {
                $sql .= " AND a.owner = ?";
                $where = array($USER->get('id'));
            }
            $publishedposts = get_column_sql($sql, $where);
            $elements[] = self::artefactchooser_element((isset($configdata['artefactid'])) ? $configdata['artefactid'] : null, $publishedposts);
            $elements[] = PluginArtefactBlog::block_advanced_options_element($configdata, 'blogpost');
        }
        else {
            $elements[] = array(
                'type' => 'html',
                'name' => 'notice',
                'value' => '<div class="metadata">' . get_string('blogcopiedfromanotherview', 'artefact.blog', get_string('blogpost', 'artefact.blog')) . '</div>',
            );
        }
        return $elements;
    }

    public static function artefactchooser_element($default=null, $publishedposts=array()) {
        $element = array(
            'name'  => 'artefactid',
            'type'  => 'artefactchooser',
            'title' => get_string('blogpost', 'artefact.blog'),
            'description' => get_string('choosepublishedblogpostsdescription', 'blocktype.blog/blogpost'),
            'defaultvalue' => $default,
            'blocktype' => 'blogpost',
            'limit'     => 10,
            'selectone' => true,
            'artefacttypes' => array('blogpost'),
            'template'  => 'artefact:blog:artefactchooser-element.tpl',
            'extraselect' => !empty($publishedposts) ? array(array('fieldname' => 'id', 'type' => 'int', 'values' => $publishedposts)) : null,
        );
        return $element;
    }

    /**
     * Optional method. If specified, allows the blocktype class to munge the
     * artefactchooser element data before it's templated
     */
    public static function artefactchooser_get_element_data($artefact) {
        static $blognames = array();

        if (!isset($blognames[$artefact->parent])) {
            $blognames[$artefact->parent] = get_field('artefact', 'title', 'id', $artefact->parent);
        }
        $artefact->blog = $blognames[$artefact->parent];
        $artefact->description = str_shorten_html($artefact->description, 50, true);

        return $artefact;
    }

    /**
     * Optional method. If specified, changes the order in which the artefacts are sorted in the artefact chooser.
     *
     * This is a valid SQL string for the ORDER BY clause. Fields you can sort on are as per the artefact table
     */
    public static function artefactchooser_get_sort_order() {
        return array(array('fieldname' => 'parent'),
                     array('fieldname' => 'ctime', 'order' => 'DESC'));
    }

    public static function default_copy_type() {
        return 'nocopy';
    }

    /**
     * Blogpost blocktype is allowed in personal / institution / group views
     */
    public static function allowed_in_view(View $view) {
        return true;
    }

}
