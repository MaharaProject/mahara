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
 * @subpackage artefact-blog
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
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
    
    public static function get_block_types() {
        return array();
    }

    public static function get_plugin_name() {
        return 'blog';
    }

    public static function menu_items() {
        global $USER;
        $tab = array(
            'path'   => 'content/blogs',
            'weight' => 40,
        );
        if ($USER->get_account_preference('multipleblogs')) {
            $tab['url']   = 'artefact/blog/';
            $tab['title'] = get_string('blogs', 'artefact.blog');
        }
        else {
            $tab['url']   = 'artefact/blog/view/';
            $tab['title'] = get_string('blog', 'artefact.blog');
        }
        return array('content/blogs' => $tab);
    }

    public static function get_cron() {
        return array();
    }


    public static function get_event_subscriptions() {
        return array(
            (object)array(
                'plugin'       => 'blog',
                'event'        => 'createuser',
                'callfunction' => 'create_default_blog',
            ),
        );
    }

    public static function block_advanced_options_element($configdata, $artefacttype) {
        $strartefacttype = get_string($artefacttype, 'artefact.blog');
        return array(
            'type' => 'fieldset',
            'name' => 'advanced',
            'collapsible' => true,
            'collapsed' => false,
            'legend' => get_string('moreoptions', 'artefact.blog'),
            'elements' => array(
                'copytype' => array(
                    'type' => 'select',
                    'title' => get_string('blockcopypermission', 'view'),
                    'description' => get_string('blockcopypermissiondesc', 'view'),
                    'defaultvalue' => isset($configdata['copytype']) ? $configdata['copytype'] : 'nocopy',
                    'options' => array(
                        'nocopy' => get_string('copynocopy', 'artefact.blog'),
                        'reference' => get_string('copyreference', 'artefact.blog', $strartefacttype),
                        'full' => get_string('copyfull', 'artefact.blog', $strartefacttype),
                    ),
                ),
            ),
        );
    }

    public static function create_default_blog($event, $user) {
        $name = display_name($user, null, true);
        $blog = new ArtefactTypeBlog(0, (object) array(
            'title'       => get_string('defaultblogtitle', 'artefact.blog', $name),
            'owner'       => $user['id'],
        ));
        $blog->commit();
    }

    public static function get_artefact_type_content_types() {
        return array(
            'blogpost' => array('text'),
        );
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
     * We override the constructor to fetch the extra data.
     *
     * @param integer
     * @param object
     */
    public function __construct($id = 0, $data = null) {
        parent::__construct($id, $data);

        if (empty($this->id)) {
            $this->container = 1;
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

        // Delete the artefact and all children.
        parent::delete();
    }

    /**
     * Checks that the person viewing this blog is the owner. If not, throws an 
     * AccessDeniedException. Used in the blog section to ensure only the 
     * owners of the blogs can view or change them there. Other people see 
     * blogs when they are placed in views.
     */
    public function check_permission() {
        global $USER;
        if ($USER->get('id') != $this->owner) {
            throw new AccessDeniedException(get_string('youarenottheownerofthisblog', 'artefact.blog'));
        }
    }


    public function describe_size() {
        return $this->count_children() . ' ' . get_string('posts', 'artefact.blog');
    }

    /**
     * Renders a blog.
     *
     * @param  array  Options for rendering
     * @return array  A two key array, 'html' and 'javascript'.
     */
    public function render_self($options) {
        $this->add_to_render_path($options);

        if (!isset($options['limit'])) {
            $limit = self::pagination;
        }
        else if ($options['limit'] === false) {
            $limit = null;
        }
        else {
            $limit = (int) $options['limit'];
        }
        $offset = isset($options['offset']) ? intval($options['offset']) : 0;

        if (!isset($options['countcomments'])) {
            // Count comments if this is a view
            $options['countcomments'] = (!empty($options['viewid']));
        }

        $posts = ArtefactTypeBlogpost::get_posts($this->id, $limit, $offset, $options);

        $template = 'artefact:blog:viewposts.tpl';

        $baseurl = get_config('wwwroot') . 'view/artefact.php?artefact=' . $this->id;
        if (!empty($options['viewid'])) {
            $baseurl .= '&view=' . $options['viewid'];
        }
        $pagination = array(
            'baseurl' => $baseurl,
            'id' => 'blogpost_pagination',
            'datatable' => 'postlist',
            'jsonscript' => 'artefact/blog/posts.json.php',
        );

        ArtefactTypeBlogpost::render_posts($posts, $template, $options, $pagination);

        $smarty = smarty_core();
        if (isset($options['viewid'])) {
            $smarty->assign('artefacttitle', '<a href="' . get_config('wwwroot') . 'view/artefact.php?artefact='
                                             . $this->get('id') . '&view=' . $options['viewid']
                                             . '">' . hsc($this->get('title')) . '</a>');
        }
        else {
            $smarty->assign('artefacttitle', hsc($this->get('title')));
        }

        $options['hidetitle'] = true;
        $smarty->assign('options', $options);
        $smarty->assign('description', $this->get('description'));
        $smarty->assign('owner', $this->get('owner'));
        $smarty->assign('tags', $this->get('tags'));

        $smarty->assign_by_ref('posts', $posts);

        return array('html' => $smarty->fetch('artefact:blog:blog.tpl'), 'javascript' => '');
    }

                
    public static function get_icon($options=null) {
        global $THEME;
        return $THEME->get_url('images/blog.gif', false, 'artefact/blog');
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
    public static function get_blog_list($limit, $offset) {
        global $USER;
        ($result = get_records_sql_array("
         SELECT b.id, b.title, b.description, b.locked, COUNT(p.id) AS postcount
         FROM {artefact} b LEFT JOIN {artefact} p ON (p.parent = b.id AND p.artefacttype = 'blogpost')
         WHERE b.owner = ? AND b.artefacttype = 'blog'
         GROUP BY b.id, b.title, b.description, b.locked
         ORDER BY b.title", array($USER->get('id')), $offset, $limit))
            || ($result = array());

        foreach ($result as &$r) {
            if (!$r->locked) {
                $r->deleteform = ArtefactTypeBlog::delete_form($r->id);
            }
        }

        $count = (int)get_field('artefact', 'COUNT(*)', 'owner', $USER->get('id'), 'artefacttype', 'blog');

        return array($count, $result);
    }

    public static function build_blog_list_html(&$blogs) {
        $smarty = smarty_core();
        $smarty->assign_by_ref('blogs', $blogs);
        $blogs->tablerows = $smarty->fetch('artefact:blog:bloglist.tpl');
        $pagination = build_pagination(array(
            'id' => 'bloglist_pagination',
            'class' => 'center',
            'url' => get_config('wwwroot') . 'artefact/blog/index.php',
            'jsonscript' => 'artefact/blog/index.json.php',
            'datatable' => 'bloglist',
            'count' => $blogs->count,
            'limit' => $blogs->limit,
            'offset' => $blogs->offset,
            'firsttext' => '',
            'previoustext' => '',
            'nexttext' => '',
            'lasttext' => '',
            'numbersincludefirstlast' => false,
            'resultcounttextsingular' => get_string('blog', 'artefact.blog'),
            'resultcounttextplural' => get_string('blogs', 'artefact.blog'),
        ));
        $blogs->pagination = $pagination['html'];
        $blogs->pagination_js = $pagination['javascript'];
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
        $artefact->set('tags', $values['tags']);
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
        $artefact->set('tags', $values['tags']);
        $artefact->commit();
    }

    public static function get_links($id) {
        $wwwroot = get_config('wwwroot');

        return array(
            '_default'                                  => $wwwroot . 'artefact/blog/view/?id=' . $id,
            get_string('blogsettings', 'artefact.blog') => $wwwroot . 'artefact/blog/settings/?id=' . $id,
        );
    }

    public function copy_extra($new) {
        $new->set('title', get_string('Copyof', 'mahara', $this->get('title')));
    }

    /**
     * Returns the number of posts in this blog that have been published.
     *
     * The result of this function looked up from the database each time, so 
     * cache it if you know it's safe to do so.
     *
     * @return int
     */
    public function count_published_posts() {
        return (int)get_field_sql("
            SELECT COUNT(*)
            FROM {artefact} a
            LEFT JOIN {artefact_blog_blogpost} bp ON a.id = bp.blogpost
            WHERE a.parent = ?
            AND bp.published = 1", array($this->get('id')));
    }

    public static function delete_form($id) {
        global $THEME;
        return pieform(array(
            'name' => 'delete_' . $id,
            'successcallback' => 'delete_blog_submit',
            'renderer' => 'oneline',
            'elements' => array(
                'delete' => array(
                    'type' => 'hidden',
                    'value' => $id,
                ),
                'submit' => array(
                    'type' => 'image',
                    'src' => $THEME->get_url('images/icon_close.gif'),
                    'elementtitle' => get_string('delete', 'artefact.blog'),
                    'confirm' => get_string('deleteblog?', 'artefact.blog'),
                ),
            ),
        ));
    }
}

/**
 * BlogPost artefacts occur within Blog artefacts
 */
class ArtefactTypeBlogPost extends ArtefactType {

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

        if ($this->id) {
            if ($bpdata = get_record('artefact_blog_blogpost', 'blogpost', $this->id)) {
                foreach($bpdata as $name => $value) {
                    if (property_exists($this, $name)) {
                        $this->$name = $value;
                    }
                }
            }
            else {
                // This should never happen unless the user is playing around with blog post IDs in the location bar or similar
                throw new ArtefactNotFoundException(get_string('blogpostdoesnotexist', 'artefact.blog'));
            }
        }
        else {
            $this->allowcomments = 1; // Turn comments on for new posts
        }
    }

    /**
     * This method extends ArtefactType::commit() by adding additional data
     * into the artefact_blog_blogpost table.
     *
     * This method also works out what blockinstances this blogpost is in, and 
     * informs them that they should re-check what artefacts they have in them.
     * The post content may now link to different artefacts. See {@link 
     * PluginBlocktypeBlogPost::get_artefacts for more information}
     */
    public function commit() {
        if (empty($this->dirty)) {
            return;
        }

        db_begin();
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

        // We want to get all blockinstances that contain this blog post. That is currently:
        // 1) All blogpost blocktypes with this post in it
        // 2) All blog blocktypes with this posts's blog in it
        //
        // With these, we tell them to rebuild what artefacts they have in them, 
        // since the post content could have changed and now have links to 
        // different artefacts in it
        $blockinstanceids = (array)get_column_sql('SELECT block
            FROM {view_artefact}
            WHERE artefact = ?
            OR artefact = ?', array($this->get('id'), $this->get('parent')));
        if ($blockinstanceids) {
            require_once(get_config('docroot') . 'blocktype/lib.php');
            foreach ($blockinstanceids as $id) {
                $instance = new BlockInstance($id);
                $instance->rebuild_artefact_list();
            }
        }

        db_commit();
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

        db_begin();
        $this->detach(); // Detach all file attachments
        delete_records('artefact_blog_blogpost', 'blogpost', $this->id);
      
        parent::delete();
        db_commit();
    }

    public static function bulk_delete($artefactids) {
        if (empty($artefactids)) {
            return;
        }

        $idstr = join(',', array_map('intval', $artefactids));

        db_begin();
        delete_records_select('artefact_blog_blogpost', 'blogpost IN (' . $idstr . ')');
        parent::bulk_delete($artefactids);
        db_commit();
    }


    /**
     * Checks that the person viewing this blog is the owner. If not, throws an 
     * AccessDeniedException. Used in the blog section to ensure only the 
     * owners of the blogs can view or change them there. Other people see 
     * blogs when they are placed in views.
     */
    public function check_permission() {
        global $USER;
        if ($USER->get('id') != $this->owner) {
            throw new AccessDeniedException(get_string('youarenottheownerofthisblogpost', 'artefact.blog'));
        }
    }
  
    public function describe_size() {
        return $this->count_attachments() . ' ' . get_string('attachments', 'artefact.blog');
    }

    public function render_self($options) {
        $smarty = smarty_core();
        $artefacturl = get_config('wwwroot') . 'view/artefact.php?artefact=' . $this->get('id');
        if (isset($options['viewid'])) {
            $artefacturl .= '&view=' . $options['viewid'];
        }
        $smarty->assign('artefacturl', $artefacturl);
        if (empty($options['hidetitle'])) {
            if (isset($options['viewid'])) {
                $smarty->assign('artefacttitle', '<a href="' . $artefacturl . '">' . hsc($this->get('title')) . '</a>');
            }
            else {
                $smarty->assign('artefacttitle', hsc($this->get('title')));
            }
        }

        // We need to make sure that the images in the post have the right viewid associated with them
        $postcontent = $this->get('description');
        if (isset($options['viewid'])) {
            safe_require('artefact', 'file');
            $postcontent = ArtefactTypeFolder::append_view_url($postcontent, $options['viewid']);
            if (isset($options['countcomments']) && $this->allowcomments) {
                safe_require('artefact', 'comment');
                $empty = array();
                $ids = array($this->id);
                $commentcount = ArtefactTypeComment::count_comments($empty, $ids);
                $smarty->assign('commentcount', $commentcount ? $commentcount[$this->id]->comments : 0);
            }
        }
        $smarty->assign('artefactdescription', $postcontent);
        $smarty->assign('artefact', $this);
        $attachments = $this->get_attachments();
        if ($attachments) {
            $this->add_to_render_path($options);
            require_once(get_config('docroot') . 'artefact/lib.php');
            foreach ($attachments as &$attachment) {
                $f = artefact_instance_from_id($attachment->id);
                $attachment->size = $f->describe_size();
                $attachment->iconpath = $f->get_icon(array('id' => $attachment->id, 'viewid' => isset($options['viewid']) ? $options['viewid'] : 0));
                $attachment->viewpath = get_config('wwwroot') . 'view/artefact.php?artefact=' . $attachment->id . '&view=' . (isset($options['viewid']) ? $options['viewid'] : 0);
                $attachment->downloadpath = get_config('wwwroot') . 'artefact/file/download.php?file=' . $attachment->id;
                if (isset($options['viewid'])) {
                    $attachment->downloadpath .= '&view=' . $options['viewid'];
                }
            }
            $smarty->assign('attachments', $attachments);
        }
        $smarty->assign('postedbyon', get_string('postedbyon', 'artefact.blog',
                                                 display_name($this->owner),
                                                 format_date($this->ctime)));
        return array('html' => $smarty->fetch('artefact:blog:render/blogpost_renderfull.tpl'),
                     'javascript' => '');
    }


    public function can_have_attachments() {
        return true;
    }


    public static function get_icon($options=null) {
        global $THEME;
        return $THEME->get_url('images/blogpost.gif', false, 'artefact/blog');
    }

    public static function is_singular() {
        return false;
    }

    public static function collapse_config() {
    }

    /**
     * This function returns a list of posts in a given blog.
     *
     * @param integer
     * @param integer
     * @param integer
     * @param array
     */
    public static function get_posts($id, $limit, $offset, $viewoptions=null) {

        $results = array(
            'limit'  => $limit,
            'offset' => $offset,
        );

        // If viewoptions is null, we're getting posts for the my blogs area,
        // and we should get all posts & show drafts first.  Otherwise it's a
        // blog in a view, and we should only get published posts.

        $from = "
            FROM {artefact} a LEFT JOIN {artefact_blog_blogpost} bp ON a.id = bp.blogpost
            WHERE a.artefacttype = 'blogpost' AND a.parent = ?";

        if (!is_null($viewoptions)) {
            if (isset($viewoptions['before'])) {
                $from .= " AND a.ctime < '{$viewoptions['before']}'";
            }
            $from .= ' AND bp.published = 1';
        }

        $results['count'] = count_records_sql('SELECT COUNT(*) ' . $from, array($id));

        $data = get_records_sql_assoc('
            SELECT
                a.id, a.title, a.description, a.author, a.authorname, ' .
                db_format_tsfield('a.ctime', 'ctime') . ', ' . db_format_tsfield('a.mtime', 'mtime') . ',
                a.locked, bp.published, a.allowcomments ' . $from . '
            ORDER BY bp.published ASC, a.ctime DESC',
            array($id),
            $offset, $limit
        );

        if (!$data) {
            $results['data'] = array();
            return $results;
        }

        // Get the attached files.
        $postids = array_map(create_function('$a', 'return $a->id;'), $data);
        $files = ArtefactType::attachments_from_id_list($postids);
        if ($files) {
            safe_require('artefact', 'file');
            foreach ($files as &$file) {
                $file->icon = call_static_method(generate_artefact_class_name($file->artefacttype), 'get_icon', array('id' => $file->attachment));
                $data[$file->artefact]->files[] = $file;
            }
        }

        if ($tags = ArtefactType::tags_from_id_list($postids)) {
            foreach($tags as &$at) {
                $data[$at->artefact]->tags[] = $at->tag;
            }
        }

        // Get comment counts
        if (!empty($viewoptions['countcomments'])) {
            safe_require('artefact', 'comment');
            $viewids = array();
            $commentcounts = ArtefactTypeComment::count_comments($viewids, array_keys($data));
        }

        foreach ($data as &$post) {
            // Format dates properly
            if (is_null($viewoptions)) {
                // My Blogs area: create forms for publishing & deleting posts.
                if (!$post->published) {
                    $post->publish = ArtefactTypeBlogpost::publish_form($post->id);
                }
                $post->delete = ArtefactTypeBlogpost::delete_form($post->id);
            }
            else {
                $by = $post->author ? display_default_name($post->author) : $post->authorname;
                $post->postedby = get_string('postedbyon', 'artefact.blog', $by, format_date($post->ctime));
                if (isset($commentcounts)) {
                    $post->commentcount = isset($commentcounts[$post->id]) ? $commentcounts[$post->id]->comments : 0;
                }
            }
            $post->ctime = format_date($post->ctime, 'strftimedaydatetime');
            $post->mtime = format_date($post->mtime);

            // Ensure images in the post have the right viewid associated with them
            if (!empty($viewoptions['viewid'])) {
                safe_require('artefact', 'file');
                $post->description = ArtefactTypeFolder::append_view_url($post->description, $viewoptions['viewid']);
            }
        }

        $results['data'] = array_values($data);

        return $results;
    }

    /**
     * This function renders a list of posts as html
     *
     * @param array posts
     * @param string template
     * @param array options
     * @param array pagination
     */
    public function render_posts(&$posts, $template, $options, $pagination) {
        $smarty = smarty_core();
        $smarty->assign('options', $options);
        $smarty->assign('posts', $posts['data']);

        $posts['tablerows'] = $smarty->fetch($template);

        if ($posts['limit'] && $pagination) {
            $pagination = build_pagination(array(
                'id' => $pagination['id'],
                'class' => 'center',
                'datatable' => $pagination['datatable'],
                'url' => $pagination['baseurl'],
                'jsonscript' => $pagination['jsonscript'],
                'count' => $posts['count'],
                'limit' => $posts['limit'],
                'offset' => $posts['offset'],
                'numbersincludefirstlast' => false,
                'resultcounttextsingular' => get_string('post', 'artefact.blog'),
                'resultcounttextplural' => get_string('posts', 'artefact.blog'),
            ));
            $posts['pagination'] = $pagination['html'];
            $posts['pagination_js'] = $pagination['javascript'];
        }
    }

    /** 
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
        $artefact->set('tags', $values['tags']);
        $artefact->commit();
        return true;
    }


    public static function publish_form($id) {
        return pieform(array(
            'name' => 'publish_' . $id,
            'successcallback' => 'publish_submit',
            'jsform' => true,
            'jssuccesscallback' => 'publish_success',
            'renderer' => 'oneline',
            'elements' => array(
                'publish' => array(
                    'type' => 'hidden',
                    'value' => $id,
                    'help' => true,
                ),
                'submit' => array(
                    'type' => 'submit',
                    'class' => 'publish',
                    'value' => get_string('publish', 'artefact.blog'),
                ),
            ),
        ));
    }


    public static function delete_form($id) {
        global $THEME;
        return pieform(array(
            'name' => 'delete_' . $id,
            'successcallback' => 'delete_submit',
            'jsform' => true,
            'jssuccesscallback' => 'delete_success',
            'renderer' => 'oneline',
            'elements' => array(
                'delete' => array(
                    'type' => 'hidden',
                    'value' => $id,
                    'help' => true,
                ),
                'submit' => array(
                    'type' => 'image',
                    'src' => $THEME->get_url('images/icon_close.gif'),
                    'elementtitle' => get_string('delete', 'artefact.blog'),
                    'confirm' => get_string('deleteblogpost?', 'artefact.blog'),
                ),
            ),
        ));
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

    
    public static function get_links($id) {
        $wwwroot = get_config('wwwroot');

        return array(
            '_default'                                  => $wwwroot . 'artefact/blog/post.php?blogpost=' . $id,
        );
    }

    public function update_artefact_references(&$view, &$template, &$artefactcopies, $oldid) {
        parent::update_artefact_references($view, $template, $artefactcopies, $oldid);
        // Attach copies of the files that were attached to the old post.
        // Update <img> tags in the post body to refer to the new image artefacts.
        $regexp = array();
        $replacetext = array();
        if (isset($artefactcopies[$oldid]->oldattachments)) {
            foreach ($artefactcopies[$oldid]->oldattachments as $a) {
                if (isset($artefactcopies[$a])) {
                    $this->attach($artefactcopies[$a]->newid);
                }
                $regexp[] = '#<img([^>]+)src="' . get_config('wwwroot') . 'artefact/file/download.php\?file=' . $a . '"#';
                $replacetext[] = '<img$1src="' . get_config('wwwroot') . 'artefact/file/download.php?file=' . $artefactcopies[$a]->newid . '"';
            }
            $this->set('description', preg_replace($regexp, $replacetext, $this->get('description')));
        }
    }

    /**
     * During the copying of a view, we might be allowed to copy
     * blogposts but not the containing blog.  We need to create a new
     * blog to hold the copied posts.
     */
    public function default_parent_for_copy(&$view, &$template, $artefactstoignore) {
        static $blogid;

        if (!empty($blogid)) {
            return $blogid;
        }

        $blogname = get_string('viewposts', 'artefact.blog', $view->get('id'));
        $data = (object) array(
            'title'       => $blogname,
            'description' => get_string('postscopiedfromview', 'artefact.blog', $template->get('title')),
            'owner'       => $view->get('owner'),
            'group'       => $view->get('group'),
            'institution' => $view->get('institution'),
        );
        $blog = new ArtefactTypeBlog(0, $data);
        $blog->commit();

        $blogid = $blog->get('id');

        return $blogid;
    }

    /**
     * Looks through the blog post text for links to download artefacts, and 
     * returns the IDs of those artefacts.
     */
    public function get_referenced_artefacts_from_postbody() {
        return artefact_get_references_in_html($this->get('description'));
    }
}
