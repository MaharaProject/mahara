<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package    mahara
 * @subpackage artefact-blog
 * @author     Alastair Pharo <alastair@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/** 
 * Users can create blogs and blog posts using this plugin.
 */
class PluginArtefactBlog extends PluginArtefact {

    public static function get_artefact_types() {
        return array(
            'blog',
            'blogpost',
        );
    }

    public static function get_plugin_name() {
        return 'blog';
    }

    public static function menu_items() {
        return array(
            array(
                'name' => 'myblogs',
                'link' => 'list/',
            )
        );
    }

    public static function get_toplevel_artefact_types() {
        return array('blog');
    }
}

/**
 * A Blog artefact is a collection of BlogPost artefacts.
 */
class ArtefactTypeBlog extends ArtefactType {

    /**
     * This constant gives the per-page pagination for listing blogs.
     */
    const pagination = 10;
    
    
    /** 
     * Whether comments are allowed on this blog or not.
     */
    protected $commentsallowed = false;

    /** 
     * Whether the blog owner will be notified of comments or not.
     */
    protected $commentsnotify = false;


    /**
     * This function updates or inserts the artefact.  This involves putting
     * some data in the artefact table (handled by parent::commit()), and then
     * some data in the artefact_blog_blog table.
     */
    public function commit() {
        // Just forget the whole thing when we're clean.
        if (empty($this->dirty)) {
            return;
        }
      
        // We need to keep track of newness before and after.
        $new = empty($this->id);
        
        // Commit to the artefact table.
        parent::commit();

        // Reset dirtyness for the time being.
        $this->dirty = true;

        $data = array(
            'blog'            => $this->get('id'),
            'commentsallowed' => ($this->get('commentsallowed') ? 1 : 0),
            'commentsnotify'  => ($this->get('commentsnotify') ? 1 : 0)
        );

        if ($new) {
            insert_record('artefact_blog_blog', $data);
        }
        else {
            update_record('artefact_blog_blog', $data, 'blog');
        }

        $this->dirty = false;
    }

    /**
     * This function extends ArtefactType::delete() by deleting blog-specific
     * data.
     */
    public function delete() {
        if (empty($this->id)) {
            return;
        }
        log_debug('Deleting blog:'.$this->id);

        // Delete the blog-specific data.
        delete_records('artefact_blog_blog', 'blog', $this->id);

        // Delete the artefact and all children.
        parent::delete();
    }

    /** 
     * FIXME - Not sure about this.  It is copied entirely from
     * ArtefactTypeProfile
     */
    public function render($format, $options) {
        if ($format == ARTEFACT_FORMAT_LISTITEM && $this->title) {
            return $this->title;
        }
        return false;
    }

    public function get_icon() {
    }

    public static function get_render_list() {
    }

    public static function collapse_config() {
    }

    /**
     * This function returns a list of the given user's blogs.
     *
     * @param User
     * @return array (count: integer, data: array)
     */
    public static function get_blog_list(User $user, $limit = self::pagination, $offset = 0) {
        ($result = get_records_sql_array("
         SELECT id, title, description
         FROM " . get_config('dbprefix') . "artefact
         WHERE owner = ?
          AND artefacttype = 'blog'
         ORDER BY title
         LIMIT ? OFFSET ?", array($user->get('id'), $limit, $offset)))
            || ($result = array());

        $count = (int)get_field('artefact', 'COUNT(*)', 'owner', $user->get('id'), 'artefacttype', 'blog');

        return array($count, $result);
    }

    /**
     * This function creates a new blog.
     *
     * @param User
     * @param array
     */
    public static function new_blog(User $user, $values) {
        $artefact = new ArtefactTypeBlog();
        $artefact->set('title', $values['title']);
        $artefact->set('description', $values['description']);
        $artefact->set('owner', $user->get('id'));
        $artefact->set('commentsallowed', $values['commentsallowed'] ? true : false);
        $artefact->set('commentsnotify', $values['commentsnotify'] ? true : false);
        $artefact->commit();
    }
}

/**
 * BlogPost artefacts occur within Blog artefacts
 */
class ArtefactTypeBlogPost extends ArtefactType {

    /**
     * This gives the number of blog posts to display at a time.
     */
    const pagination = 10;
    

    /**
     * Just the basic commit.
     */
    public function commit() {
        $this->commit_basic();
    }

    /**
     * @todo
     */
    public function delete() {
        log_debug('Deleting blogpost:'.$this->id);
        $this->delete_basic();
    }

    public function render($format, $options) {
    }

    public function get_icon() {
    }

    public static function get_render_list() {
    }

    public static function collapse_config() {
    }

    /**
     * This function returns a list of the current user's blog posts, for the
     * given blog.
     *
     * @param User
     * @param integer
     * @param integer
     */
    public static function get_posts(User $user, $id, $limit = self::pagination, $offset = 0) {
        ($result = get_records_sql_array("
         SELECT id, title, description, ctime, mtime
         FROM " . get_config('dbprefix') . "artefact
         WHERE parent = ?
          AND artefacttype = 'blogpost'
          AND owner = ?
         ORDER BY ctime DESC
         LIMIT ? OFFSET ?;", array(
            $id,
            $user->get('id'),
            $limit,
            $offset
        )))
            || ($result = array());

        $count = (int)get_field('artefact', 'COUNT(*)', 'owner', $user->get('id'), 'artefacttype', 'blogpost', 'parent', $id);

        return array($count, $result);
    }

    /**
     * This function creates a new blog post.
     *
     * @param User
     * @param array
     */
    public static function new_post(User $user, array $values) {
        $artefact = new ArtefactTypeBlogPost();
        $artefact->set('title', $values['title']);
        $artefact->set('description', $values['description']);
        $artefact->set('owner', $user->get('id'));
        $artefact->set('parent', $values['id']);
        $artefact->commit();
    }
}

?>
