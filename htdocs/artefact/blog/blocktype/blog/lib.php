<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-blog
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeBlog extends MaharaCoreBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.blog/blog');
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
        return get_string('description', 'blocktype.blog/blog');
    }

    public static function get_categories() {
        return array('blog' => 10000);
    }

    public static function get_link(BlockInstance $instance) {
        $configdata = $instance->get('configdata');
        if (!empty($configdata['artefactid'])) {
            $data = get_config('wwwroot') . 'artefact/artefact.php?artefact=' . $configdata['artefactid'] . '&view=' . $instance->get('view');
            return sanitize_url($data);
        }
    }

    public static function render_instance(BlockInstance $instance, $editing=false, $versioning=false) {
        global $exporter;
        $configdata = $instance->get('configdata');

        $result = '';
        if (!empty($configdata['artefactid'])) {
            require_once(get_config('docroot') . 'artefact/lib.php');
            $blog = $instance->get_artefact_instance($configdata['artefactid']);
            $configdata['hidetitle'] = true;
            $configdata['countcomments'] = true;
            $configdata['versioning'] = $versioning;
            $configdata['viewid'] = $instance->get('view');
            if ($instance->get_view()->is_submitted()) {
                // Don't display posts added after the submitted date.
                if ($submittedtime = $instance->get_view()->get('submittedtime')) {
                    $configdata['before'] = $submittedtime;
                }
            }

            $limit = isset($configdata['count']) ? intval($configdata['count']) : 5;
            $limit = ($exporter || $versioning) ? 0 : $limit;
            $posts = ArtefactTypeBlogpost::get_posts($blog->get('id'), $limit, 0, $configdata);
            $template = 'artefact:blog:viewposts.tpl';
            if ($exporter || $versioning) {
                $pagination = false;
            }
            else {
                $baseurl = $instance->get_view()->get_url();
                $baseurl .= (strpos($baseurl, '?') === false ? '?' : '&') . 'block=' . $instance->get('id');
                $pagination = array(
                    'baseurl' => $baseurl,
                    'id' => 'blogpost_pagination_' . $instance->get('id'),
                    'datatable' => 'postlist_' . $instance->get('id'),
                    'jsonscript' => 'artefact/blog/posts.json.php',
                );
            }
            $configdata['blockid'] = $instance->get('id');
            $configdata['editing'] = $editing;
            ArtefactTypeBlogpost::render_posts($posts, $template, $configdata, $pagination);

            $smarty = smarty_core();
            if (isset($configdata['viewid'])) {
                $artefacturl = get_config('wwwroot') . 'artefact/artefact.php?artefact=' . $blog->get('id') . '&view='
                    . $configdata['viewid'];
                $smarty->assign('artefacttitle', '<a href="' . $artefacturl . '">' . hsc($blog->get('title')) . '</a>');
                if ($exporter && $posts['count'] > $limit) {
                    $posts['pagination'] = '<a href="' . $artefacturl . '">'
                        . get_string('allposts', 'artefact.blog') . '</a>';
                }
            }
            else {
                $smarty->assign('artefacttitle', hsc($blog->get('title')));
            }
            // Only show the 'New entry' link for blogs that you can add an entry to
            $canaddpost = false;
            $institution = $blog->get('institution');
            $group = $blog->get('group');
            if (ArtefactTypeBlog::can_edit_blog($blog, $institution, $group)) {
                $canaddpost = true;
            }

            $smarty->assign('alldraftposts', (isset($posts['alldraftposts']) ? $posts['alldraftposts'] : null));
            $smarty->assign('options', $configdata);
            $smarty->assign('description', $blog->get('description'));
            $smarty->assign('owner', $blog->get('owner'));
            $smarty->assign('tags', $blog->get('tags'));
            $smarty->assign('blockid', $instance->get('id'));
            $smarty->assign('editing', $editing);
            $smarty->assign('canaddpost', $canaddpost);
            $smarty->assign('blogid', $blog->get('id'));
            $smarty->assign('view', $instance->get('view'));
            $smarty->assign('posts', $posts);

            $result = $smarty->fetch('artefact:blog:blog.tpl');
        }

        return $result;
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form(BlockInstance $instance) {
        global $USER;
        safe_require('artefact', 'blog');
        $configdata = $instance->get('configdata');

        require_once(get_config('libroot') . 'view.php');
        $view = new View($instance->get('view'));
        $institution = $view->get('institution');
        $group = $view->get('group');

        if (!empty($configdata['artefactid'])) {
            $blog = $instance->get_artefact_instance($configdata['artefactid']);
        }

        $elements = array();

        // If the blog in this block is owned by the owner of the View, then
        // the block can be configured. Otherwise, the blog was copied in from
        // another View. We won't confuse users by asking them to choose a blog
        // to put in this block, when the one that is currently in it isn't
        // choosable.
        if (empty($configdata['artefactid'])
            || (ArtefactTypeBlog::can_edit_blog($blog, $institution, $group))) {
            $where = array('blog');
            $sql = "SELECT a.id FROM {artefact} a
                    WHERE a.artefacttype = ?";
            if ($institution) {
                $sql .= " AND a.institution = ?";
                $where[] = $institution;
            }
            else if ($group) {
                $sql .= " AND ( a.group = ? OR a.owner = ?)";
                $where[] = $group;
                $where[] = $USER->get('id');
            }
            else {
                $sql .= " AND a.owner = ?";
                $where[] = $USER->get('id');
            }
            $blogids = get_column_sql($sql, $where);
            $elements[] = self::artefactchooser_element((isset($configdata['artefactid'])) ? $configdata['artefactid'] : null, $blogids);
            $elements['count'] = array(
                'type' => 'text',
                'title' => get_string('postsperpage', 'blocktype.blog/blog'),
                'defaultvalue' => isset($configdata['count']) ? $configdata['count'] : 5,
                'size' => 3,
            );
            $elements[] = PluginArtefactBlog::block_advanced_options_element($configdata, 'blog');
        }
        else {
            $elements[] = array(
                'type' => 'html',
                'name' => 'notice',
                'value' => '<div class="metadata">' . get_string('blogcopiedfromanotherview', 'artefact.blog', get_string('Blog', 'artefact.blog')) . '</div>',
            );
        }
        return $elements;
    }

    /**
     * Returns a list of artefact IDs that are in this blockinstance.
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
        $configdata = $instance->get('configdata');
        $artefacts = array();
        if (isset($configdata['artefactid'])) {
            $artefacts[] = $configdata['artefactid'];

            // Artefacts that are linked to directly in blog post text aren't
            // strictly children of blog posts, which means that
            // artefact_in_view won't understand that they are "within the
            // blog". We have to help it here directly by working out what
            // artefacts are linked to in all of this blog's blog posts.
            $blog = $instance->get_artefact_instance($configdata['artefactid']);
            if ($blogposts = $blog->get_children_instances()) {
                foreach ($blogposts as $blogpost) {
                    if ($blogpost->get('published')) {
                        $artefacts[] = $blogpost->get('id');
                        $artefacts = array_merge($artefacts, $blogpost->get_referenced_artefacts_from_postbody());
                    }
                }
                $artefacts = array_unique($artefacts);
            }
        }
        return $artefacts;
    }

    public static function get_current_artefacts(BlockInstance $instance) {

        $configdata = $instance->get('configdata');
        $artefacts = array();

        if (isset($configdata['artefactid'])) {
            $blog = $instance->get_artefact_instance($configdata['artefactid']);
            if ($blogposts = $blog->get_children_instances()) {
                foreach ($blogposts as $blogpost) {
                    if ($blogpost->get('published')) {
                        $artefacts[] = $blogpost->get('id');
                    }
                }
                $artefacts = array_unique($artefacts);
            }
        }
        return $artefacts;
    }

    public static function artefactchooser_element($default=null, $blogids=array()) {
        return array(
            'name'  => 'artefactid',
            'type'  => 'artefactchooser',
            'title' => get_string('Blog', 'artefact.blog'),
            'defaultvalue' => $default,
            'blocktype' => 'blog',
            'limit'     => 10,
            'selectone' => true,
            'artefacttypes' => array('blog'),
            'template'  => 'artefact:blog:artefactchooser-element.tpl',
            'extraselect' => !empty($blogids) ? array(array('fieldname' => 'id', 'type' => 'int', 'values' => $blogids)) : null,
        );
    }

    public static function default_copy_type() {
        return 'nocopy';
    }

    /**
     * Blog blocktype is only allowed in personal / institution / group views
     */
    public static function allowed_in_view(View $view) {
        return true;
    }

    public static function feed_url(BlockInstance $instance) {
        $configdata = $instance->get('configdata');
        if (!empty($configdata['artefactid']) && $instance->get_view()->is_public()) {
            return get_config('wwwroot') . 'artefact/blog/atom.php?artefact='
                . $configdata['artefactid'] . '&view=' . $instance->get('view');
        }
    }

}
