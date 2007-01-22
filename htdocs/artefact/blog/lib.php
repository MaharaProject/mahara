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
                'link' => '',
            )
        );
    }

    public static function get_toplevel_artefact_types() {
        return array('blog');
    }

    public static function get_cron() {
        return array(
            (object)array(
                'callfunction' => 'clean_post_files',
                'hour'         => '4',
                'minute'       => '40'
            )
        );
    }

    /**
     * This function cleans out any files that have been uploaded, but which
     * are not associated with a blog, because of an aborted blog creation.
     */
    public static function clean_post_files() {

        $bloguploadbase = get_config('dataroot') . ArtefactTypeBlogPost::$blogattachmentroot;
        if (!$basedir = opendir($bloguploadbase)) {
            throw new Exception('Unable to read blog upload directory '.$bloguploadbase);
        }

        $currenttime = time();

        // Read through all the upload session directories
        while (false !== ($sessionupload = readdir($basedir))) {
            if ($sessionupload != "." && $sessionupload != "..") {
                $sessionupload = $bloguploadbase . $sessionupload;
                $subdir = opendir($sessionupload);

                // Remove all files older than the session timeout plus two hours.
                while (false !== ($uploadfile = readdir($subdir))) {
                    if ($uploadfile != "." && $uploadfile != "..") {
                        $uploadfile = $sessionupload . '/' . $uploadfile;
                        if ($currenttime - filemtime($uploadfile) > get_config('session_timeout') + 7200) {
                            unlink($uploadfile);
                        }
                    }
                }

                closedir($subdir);
                rmdir($sessionupload);
            }
        }

        closedir($basedir);
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
     *
     * @var boolean
     */
    protected $commentsallowed = false;

    /** 
     * Whether the blog owner will be notified of comments or not.
     *
     * @var boolean
     */
    protected $commentsnotify = false;

    /**
     * We override the constructor to fetch the extra data.
     *
     * @param integer
     * @param object
     */
    public function __construct($id = 0, $data = null) {
        parent::__construct($id, $data);

        if (!$data && $this->id
            && ($blogdata = get_record('artefact_blog_blog', 'blog', $this->id))) {
            foreach($blogdata as $name => $value) {
                if (property_exists($this, $name)) {
                    $this->$name = $value;
                }
            }
        }
    }

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

        $data = (object)array(
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

        // Delete the blog-specific data.
        delete_records('artefact_blog_blog', 'blog', $this->id);

        // Delete the artefact and all children.
        parent::delete();
    }

    /**
     * This function overrides the default functionality for listing children,
     * using a smarty template and tablerenderer stuff.
     *
     * @param array
     * @return string
     */
    protected function listchildren($options) {
        // This is because if there are multiple blocks on a page, they need separate
        // js variables.
        $blockid = isset($options['blockid'])
            ? $options['blockid']
            : mt_rand();

        // This uses the above blockid, so needs to be inlcuded after.
        $javascript = require(get_config('docroot') . 'artefact/blog/render/blog_listchildren.js.php');

        $smarty = smarty();
        $smarty->assign('artefact', $this);
        $smarty->assign('blockid', $blockid);
        $smarty->assign_by_ref('options', $options);
        $smarty->assign_by_ref('javascript', $javascript);
        return $smarty->fetch('artefact:blog:render/blog_listchildren.tpl');
    }


    public function describe_size() {
        return $this->count_children() . ' ' . get_string('posts', 'artefact.blog');
    }


    /**
     * This function implements the render_full functionality for blogs.
     * Rendering full involves rendering blog posts with render_full, and
     * possibly some other stuff.
     *
     * @param array
     * @return string
     */
    function render_full($options) {
        // This is because if there are multiple blocks on a page, they need separate
        // js variables.
        $blockid = isset($options['blockid'])
            ? $options['blockid']
            : mt_rand();

        // This uses the above blockid, so needs to be inlcuded after.
        $javascript = require(get_config('docroot') . 'artefact/blog/render/blog_renderfull.js.php');
        
        $smarty = smarty();
        $smarty->assign('artefact', $this);
        $smarty->assign('blockid', $blockid);
        $smarty->assign_by_ref('options', $options);
        $smarty->assign_by_ref('javascript', $javascript);
        return $smarty->fetch('artefact:blog:render/blog_renderfull.tpl');
    }

    protected function get_metadata($options = array()) {
        $data = parent::get_metadata();
        $data['description'] = array('name' => get_string('description'),
                                     'value' => $this->get('description'));
        $data['type']['value'] = get_string($this->get('artefacttype'), 'artefact.blog');
        $data['size'] = array('name' => get_string('size'),
                              'value' => $this->count_children() . ' ' 
                                         . get_string('posts', 'artefact.blog'));

        if (isset($options['viewid']) && artefact_in_view($id = $this->get('id'), $options['viewid'])) {
            $data['title']['value'] = '<a href="' . get_config('wwwroot') . 'view/view.php?view=' . $options['viewid'] . '&artefact=' . $id . '">' . $data['title']['value'] . '</a>';
        }

        return $data;
    }

    protected function render_metadata($options) {
        $smarty = smarty();
        $smarty->assign('PROPERTIES', $this->get_metadata($options));
        return $smarty->fetch('artefact:blog:render/blog_rendermetadata.tpl');
    }

                
    public function get_icon() {
    }

    public static function get_render_list() {
        return array_merge(
            array(
                FORMAT_ARTEFACT_LISTCHILDREN,
                FORMAT_ARTEFACT_RENDERFULL
            ),
            parent::get_render_list()
        );
    }

    public static function is_singular() {
        return false;
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
    public static function new_blog(User $user, array $values) {
        $artefact = new ArtefactTypeBlog();
        $artefact->set('title', $values['title']);
        $artefact->set('description', $values['description']);
        $artefact->set('owner', $user->get('id'));
        $artefact->set('commentsallowed', $values['commentsallowed'] ? true : false);
        $artefact->set('commentsnotify', $values['commentsnotify'] ? true : false);
        $artefact->commit();
    }

    /**
     * This function updates an existing blog.
     *
     * @param User
     * @param array
     */
    public static function edit_blog(User $user, array $values) {
        if (empty($values['id']) || !is_numeric($values['id'])) {
            return;
        }

        $artefact = new ArtefactTypeBlog($values['id']);
        if ($user->get('id') != $artefact->get('owner')) {
            return;
        }
        
        $artefact->set('title', $values['title']);
        $artefact->set('description', $values['description']);
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
     * This defines whether the blogpost is published or not.
     *
     * @var boolean
     */
    protected $published = false;

    /**
     * We override the constructor to fetch the extra data.
     *
     * @param integer
     * @param object
     */
    public function __construct($id = 0, $data = null) {
        parent::__construct($id, $data);

        if (!$data && $this->id
            && ($bpdata = get_record('artefact_blog_blogpost', 'blogpost', $this->id))) {
            foreach($bpdata as $name => $value) {
                if (property_exists($this, $name)) {
                    $this->$name = $value;
                }
            }
        }
    }

    /**
     * This function extends ArtefactType::commit() by adding additional data
     * into the artefact_blog_blogpost table.
     */
    public function commit() {
        if (empty($this->dirty)) {
            return;
        }

        $new = empty($this->id);
      
        parent::commit();

        $this->dirty = true;

        $data = (object)array(
            'blogpost'  => $this->get('id'),
            'published' => ($this->get('published') ? 1 : 0)
        );

        if ($new) {
            insert_record('artefact_blog_blogpost', $data);
        }
        else {
            update_record('artefact_blog_blogpost', $data, 'blogpost');
        }

        $this->dirty = false;
    }

    /**
     * This function extends ArtefactType::delete() by also deleting anything
     * that's in blogpost.
     */
    public function delete() {
        if (empty($this->id)) {
            return;
        }

        delete_records('artefact_blog_blogpost_file', 'blogpost', $this->id);
        delete_records('artefact_blog_blogpost', 'blogpost', $this->id);
      
        parent::delete();
    }
  
    public function describe_size() {
        return $this->count_attachments() . ' ' . get_string('attachments', 'artefact.blog');
    }

    /**
     * This function displays the blogpost in renderfull mode.
     *
     * @param array
     */
    protected function render_full($options) {
        $smarty = smarty();
        $smarty->assign('artefact', $this);
        $smarty->assign('postedbyon', get_string('postedbyon', 'artefact.blog',
                                                 display_name($this->owner),
                                                 format_date($this->ctime)));
        return $smarty->fetch('artefact:blog:render/blogpost_renderfull.tpl');
    }


    protected function count_attachments() {
        return count_records('artefact_blog_blogpost_file', 'blogpost', $this->get('id'));
    }


    protected function get_metadata() {
        $data = parent::get_metadata();
        unset($data['description']);
        unset($data['size']);
        $data['type']['value'] = get_string($this->get('artefacttype'), 'artefact.blog');
        $data['attachments'] = array('name' => get_string('attachments', 'artefact.blog'),
                                     'value' => $this->count_attachments() . ' ' 
                                               . get_string('files', 'artefact.file'));
        return $data;
    }

                
    protected function render_metadata($options) {
        $smarty = smarty();
        $smarty->assign('PROPERTIES', $this->get_metadata());
        return $smarty->fetch('artefact:blog:render/blog_rendermetadata.tpl');
    }



    public function get_icon() {
    }

    public static function get_render_list() {
        return array_merge(
            array(
                FORMAT_ARTEFACT_LISTSELF,
                FORMAT_ARTEFACT_RENDERFULL
            ),
            parent::get_render_list()
        );
    }

    public static function is_singular() {
        return false;
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
        $prefix = get_config('dbprefix');
        ($result = get_records_sql_assoc("
         SELECT a.id, a.title, a.description, a.ctime, a.mtime, bp.published
         FROM " . $prefix . "artefact a
          LEFT OUTER JOIN " . $prefix . "artefact_blog_blogpost bp
           ON a.id = bp.blogpost
         WHERE a.parent = ?
          AND a.artefacttype = 'blogpost'
          AND a.owner = ?
         ORDER BY bp.published ASC, a.ctime DESC
         LIMIT ? OFFSET ?;", array(
            $id,
            $user->get('id'),
            $limit,
            $offset
        )))
            || ($result = array());

        $count = (int)get_field('artefact', 'COUNT(*)', 'owner', $user->get('id'), 
                                'artefacttype', 'blogpost', 'parent', $id);

        // Get the attached files.
        if (count($result) > 0) {
            $idlist = implode(', ', array_map(create_function('$a', 'return $a->id;'), $result));
            $files = get_records_sql_array('
               SELECT
                  bf.blogpost, bf.file, a.artefacttype, a.title, a.description
               FROM ' . $prefix . 'artefact_blog_blogpost_file bf
                  INNER JOIN ' . $prefix . 'artefact a ON bf.file = a.id
               WHERE bf.blogpost IN (' . $idlist . ')', '');
            if ($files) {
                foreach ($files as $file) {
                    $result[$file->blogpost]->files[] = $file;
                }
            }
        }

        return array($count, array_values($result));
    }

    /** 
     * This function returns a list of rendered blog posts.
     *
     * @param integer
     * @param integer
     * @param integer
     * @param integer
     */
    public static function render_posts($format, $options, $id, $limit = self::pagination, $offset = 0) {
        ($postids = get_records_sql_array("
         SELECT a.id
         FROM " . get_config('dbprefix') . "artefact a
          LEFT OUTER JOIN " . get_config('dbprefix') . "artefact_blog_blogpost bp
           ON a.id = bp.blogpost
         WHERE a.parent = ?
          AND bp.published = 1
         ORDER BY a.ctime DESC
         LIMIT ? OFFSET ?;", array($id, $limit, $offset)))
            || ($postids = array());

        $posts = array();
        foreach($postids as $postid) {
            $blogpost = new ArtefactTypeBlogPost($postid->id);
            $posts[] = array(
                'id' => $postid->id,
                'content' => $blogpost->render($format, (array) $options)
            );
        }

        $count = (int)get_field_sql("
         SELECT COUNT(*)
         FROM " . get_config('dbprefix') . "artefact a
          LEFT OUTER JOIN " . get_config('dbprefix') . "artefact_blog_blogpost bp
           ON a.id = bp.blogpost
         WHERE a.parent = ?
          AND bp.published = 1", array($id));

        return array($count, $posts);
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
        $artefact->set('published', $values['published']);
        $artefact->set('owner', $user->get('id'));
        $artefact->set('parent', $values['parent']);
        $artefact->commit();
        return true;
    }

    /** 
     * This function updates an existing blog post.
     *
     * @param User
     * @param array
     */
    public static function edit_post(User $user, array $values) {
        $artefact = new ArtefactTypeBlogPost($values['id']);
        if ($user->get('id') != $artefact->get('owner')) {
            return false;
        }

        $artefact->set('title', $values['title']);
        $artefact->set('description', $values['description']);
        $artefact->set('published', $values['published']);
        $artefact->commit();
        return true;
    }

    // Where to store temporary blog post files under dataroot
    static $blogattachmentroot = 'artefact/blog/uploads/';


    public static function get_temp_file_path($createid, $uploadnumber) {
        return get_config('dataroot') . self::$blogattachmentroot 
            . session_id() . $createid . '/' . $uploadnumber;
    }


    /**
     * Returns the size of a temporary attachment
     */
    public static function temp_attachment_size($createid, $uploadnumber) {
        return filesize(self::get_temp_file_path($createid, $uploadnumber));
    }


    /** 
     * This function saves an uploaded file to a temporary directory in dataroot
     *
     */
    public static function save_attachment_temporary($inputname, $dirname, $filename) {
        require_once('uploadmanager.php');
        $um = new upload_manager($inputname);
        $result = new StdClass;
        $tempdir = self::$blogattachmentroot . $dirname;
        $result->error = $um->process_file_upload($tempdir, $filename);
        $tempfile = $tempdir . '/' . $filename;
        safe_require('artefact', 'file');
        $result->type = ArtefactTypeFile::detect_artefact_type($tempfile);
        return $result;
    }


    /**
     * Save a temporary uploaded file to the myfiles area.
     */
    public function save_attachment($directory, $filename, $title, $description) {

        // Create the blogfiles folder if it doesn't exist yet.
        $blogfilesid = self::blogfiles_folder_id();
        if (!$blogfilesid) {
            return false;
        }

        global $USER;

        safe_require('artefact', 'file');

        $data = new StdClass;
        $data->title = $title;
        $data->description = $description;
        $data->owner = $USER->get('id');
        $data->adminfiles = 0; // No admin blogs yet...
        $data->parent = $blogfilesid;
        
        $path = self::$blogattachmentroot . $directory . '/' . $filename;

        if (!$fileid = ArtefactTypeFile::save_file($path, $data)) {
            return false;
        }
            
        $data = new StdClass;
        $data->blogpost = $this->id;
        $data->file = $fileid;
        insert_record('artefact_blog_blogpost_file', $data);

        return $fileid;
    }

    public static function blogfiles_folder_id() {
        $name = get_string('blogfilesdirname', 'artefact.blog');
        $description = get_string('blogfilesdirdescription', 'artefact.blog');
        safe_require('artefact', 'file');
        return ArtefactTypeFolder::get_folder_id($name, $description);
    }

    /**
     * This function publishes the blog post.
     *
     * @return boolean
     */
    public function publish() {
        if (!$this->id) {
            return false;
        }
        
        $data = (object)array(
            'blogpost'  => $this->id,
            'published' => 1
        );

        if (get_field('artefact_blog_blogpost', 'COUNT(*)', 'blogpost', $this->id)) {
            update_record('artefact_blog_blogpost', $data, 'blogpost');
        }
        else {
            insert_record('artefact_blog_blogpost', $data);
        }
        return true;
    }

    /**
     * This function returns a list of files attached to a post to use
     * when displaying or editing a blog post
     *
     * @return array
     */
    public function get_attached_files() {
        $prefix = get_config('dbprefix');
        return get_records_sql_array('SELECT a.id, a.artefacttype, a.title, a.description 
            FROM ' . $prefix . 'artefact_blog_blogpost_file f
            INNER JOIN ' . $prefix . 'artefact a ON a.id = f.file
            WHERE f.blogpost = ' . $this->id, '');
    }

    
    
}

?>
