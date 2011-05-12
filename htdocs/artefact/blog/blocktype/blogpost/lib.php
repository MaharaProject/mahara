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
 * @subpackage blocktype-blogpost
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeBlogpost extends PluginBlocktype {

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
        return array('blog');
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        $configdata = $instance->get('configdata');

        $result = '';
        if (!empty($configdata['artefactid'])) {
            require_once(get_config('docroot') . 'artefact/lib.php');
            $blogpost = $instance->get_artefact_instance($configdata['artefactid']);
            $configdata['hidetitle'] = true;
            $configdata['countcomments'] = true;
            $configdata['viewid'] = $instance->get('view');
            $result = $blogpost->render_self($configdata);
            $result = $result['html'];
        }

        return $result;
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

    public static function instance_config_form($instance) {
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
        //
        // Note: the owner check will have to change when we do group/site 
        // blogs
        if (empty($configdata['artefactid']) || $blog->get('owner') == $USER->get('id')) {
            $elements[] = self::artefactchooser_element((isset($configdata['artefactid'])) ? $configdata['artefactid'] : null);
            $elements[] = PluginArtefactBlog::block_advanced_options_element($configdata, 'blogpost');
        }
        else {
            $elements[] = array(
                'type' => 'html',
                'name' => 'notice',
                'value' => '<div class="message">' . get_string('blogcopiedfromanotherview', 'artefact.blog', get_string('blogpost', 'artefact.blog')) . '</div>',
            );
        }
        return $elements;
    }

    public static function artefactchooser_element($default=null) {
        $element = array(
            'name'  => 'artefactid',
            'type'  => 'artefactchooser',
            'title' => get_string('blogpost', 'artefact.blog'),
            'defaultvalue' => $default,
            'blocktype' => 'blogpost',
            'limit'     => 10,
            'selectone' => true,
            'artefacttypes' => array('blogpost'),
            'template'  => 'artefact:blog:artefactchooser-element.tpl',
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
     * Blogpost blocktype is only allowed in personal views, because currently 
     * there's no such thing as group/site blogs
     */
    public static function allowed_in_view(View $view) {
        return $view->get('owner') != null;
    }

}
