<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage blocktype-blog
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeBlog extends PluginBlocktype {

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
        return array('blog');
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        global $exporter;
        $configdata = $instance->get('configdata');

        $result = '';
        if (!empty($configdata['artefactid'])) {
            require_once(get_config('docroot') . 'artefact/lib.php');
            $blog = $instance->get_artefact_instance($configdata['artefactid']);
            $configdata['hidetitle'] = true;
            $configdata['countcomments'] = true;
            $configdata['viewid'] = $instance->get('view');
            if ($instance->get_view()->is_submitted()) {
                // Don't display posts added after the submitted date.
                if ($submittedtime = $instance->get_view()->get('submittedtime')) {
                    $configdata['before'] = $submittedtime;
                }
            }

            $limit = isset($configdata['count']) ? intval($configdata['count']) : 5;
            $posts = ArtefactTypeBlogpost::get_posts($blog->get('id'), $limit, 0, $configdata);
            $template = 'artefact:blog:viewposts.tpl';
            if ($exporter) {
                $pagination = false;
            }
            else {
                $pagination = array(
                    'baseurl' => $instance->get_view()->get_url() . '&block=' . $instance->get('id'),
                    'id' => 'blogpost_pagination_' . $instance->get('id'),
                    'datatable' => 'postlist_' . $instance->get('id'),
                    'jsonscript' => 'artefact/blog/posts.json.php',
                );
            }
            ArtefactTypeBlogpost::render_posts($posts, $template, $configdata, $pagination);

            $smarty = smarty_core();
            if (isset($configdata['viewid'])) {
                $artefacturl = get_config('wwwroot') . 'view/artefact.php?artefact=' . $blog->get('id') . '&amp;view='
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

            $smarty->assign('options', $configdata);
            $smarty->assign('description', $blog->get('description'));
            $smarty->assign('owner', $blog->get('owner'));
            $smarty->assign('tags', $blog->get('tags'));
            $smarty->assign('blockid', $instance->get('id'));
            $smarty->assign('posts', $posts);

            $result = $smarty->fetch('artefact:blog:blog.tpl');
        }

        return $result;
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        global $USER;
        safe_require('artefact', 'blog');
        $configdata = $instance->get('configdata');

        if (!empty($configdata['artefactid'])) {
            $blog = $instance->get_artefact_instance($configdata['artefactid']);
        }

        $elements = array();

        // If the blog in this block is owned by the owner of the View, then 
        // the block can be configured. Otherwise, the blog was copied in from 
        // another View. We won't confuse users by asking them to choose a blog 
        // to put in this block, when the one that is currently in it isn't 
        // choosable.
        //
        // Note: the owner check will have to change when we do group/site 
        // blogs
        if (empty($configdata['artefactid']) || $blog->get('owner') == $USER->get('id')) {
            $elements[] = self::artefactchooser_element((isset($configdata['artefactid'])) ? $configdata['artefactid'] : null);
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
                'value' => '<div class="message">' . get_string('blogcopiedfromanotherview', 'artefact.blog', get_string('blog', 'artefact.blog')) . '</div>',
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
                    $artefacts = array_merge($artefacts, $blogpost->get_referenced_artefacts_from_postbody());
                }
                $artefacts = array_unique($artefacts);
            }
        }
        return $artefacts;
    }

    public static function artefactchooser_element($default=null) {
        return array(
            'name'  => 'artefactid',
            'type'  => 'artefactchooser',
            'title' => get_string('blog', 'artefact.blog'),
            'defaultvalue' => $default,
            'blocktype' => 'blog',
            'limit'     => 10,
            'selectone' => true,
            'artefacttypes' => array('blog'),
            'template'  => 'artefact:blog:artefactchooser-element.tpl',
        );
    }

    /**
     * Optional method. If specified, allows the blocktype class to munge the 
     * artefactchooser element data before it's templated
     */
    //public static function artefactchooser_get_element_data($artefact) {
    //    static $blognames = array();

    //    if (!isset($blognames[$artefact->parent])) {
    //        $blognames[$artefact->parent] = get_field('artefact', 'title', 'id', $artefact->parent);
    //    }
    //    $artefact->blog = $blognames[$artefact->parent];

    //    $ellipsis = '';
    //    $artefact->description = trim(strip_tags($artefact->description));
    //    if (strlen($artefact->description) > 100) {
    //        $ellipsis = '…';
    //    }
    //    $artefact->description = substr($artefact->description, 0, 100) . $ellipsis;

    //    return $artefact;
    //}

    public static function default_copy_type() {
        return 'nocopy';
    }

    /**
     * Blog blocktype is only allowed in personal views, because currently 
     * there's no such thing as group/site blogs
     */
    public static function allowed_in_view(View $view) {
        return $view->get('owner') != null;
    }

    public static function feed_url(BlockInstance $instance) {
        $configdata = $instance->get('configdata');
        if (!empty($configdata['artefactid']) && $instance->get_view()->is_public()) {
            return get_config('wwwroot') . 'artefact/blog/atom.php?artefact='
                . $configdata['artefactid'] . '&view=' . $instance->get('view');
        }
    }

}
