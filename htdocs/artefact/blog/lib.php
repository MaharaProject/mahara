<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-blog
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
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

    public static function is_active() {
        return get_field('artefact_installed', 'active', 'name', 'blog');
    }

    public static function menu_items() {
        global $USER;
        $tab = array(
            'path'   => 'content/blogs',
            'weight' => 40,
        );
        if ($USER->get_account_preference('multipleblogs')) {
            $tab['url']   = 'artefact/blog/index.php';
            $tab['title'] = get_string('blogs', 'artefact.blog');
        }
        else {
            $tab['url']   = 'artefact/blog/view/index.php';
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
        $strartefacttype = strtolower(get_string($artefacttype, 'artefact.blog'));
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

    public static function progressbar_link($artefacttype) {
        return 'artefact/blog/view/index.php';
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

    public static function is_allowed_in_progressbar() {
        return false;
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

        $baseurl = get_config('wwwroot') . 'artefact/artefact.php?artefact=' . $this->id;
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
            $smarty->assign('artefacttitle', '<a href="' . get_config('wwwroot') . 'artefact/artefact.php?artefact='
                                             . $this->get('id') . '&view=' . $options['viewid']
                                             . '">' . hsc($this->get('title')) . '</a>');
        }
        else {
            $smarty->assign('artefacttitle', hsc($this->get('title')));
        }

        if (!empty($options['details']) and get_config('licensemetadata')) {
            $smarty->assign('license', render_license($this));
        }
        else {
            $smarty->assign('license', false);
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
        return $THEME->get_image_url('journal');
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
                $r->deleteform = ArtefactTypeBlog::delete_form($r->id, $r->title);
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
        require_once('embeddedimage.php');
        db_begin();
        $artefact = new ArtefactTypeBlog();
        $artefact->set('title', $values['title']);
        $artefact->set('description', $values['description']);
        $artefact->set('owner', $user->get('id'));
        $artefact->set('tags', $values['tags']);
        if (get_config('licensemetadata')) {
            $artefact->set('license', $values['license']);
            $artefact->set('licensor', $values['licensor']);
            $artefact->set('licensorurl', $values['licensorurl']);
        }
        $artefact->commit();
        $blogid = $artefact->get('id');
        $newdescription = EmbeddedImage::prepare_embedded_images($artefact->get('description'), 'blog', $blogid);
        $artefact->set('description', $newdescription);
        db_commit();
    }

    /**
     * This function updates an existing blog.
     *
     * @param User
     * @param array
     */
    public static function edit_blog(User $user, array $values) {
        require_once('embeddedimage.php');
        if (empty($values['id']) || !is_numeric($values['id'])) {
            return;
        }

        $artefact = new ArtefactTypeBlog($values['id']);
        if ($user->get('id') != $artefact->get('owner')) {
            return;
        }

        $artefact->set('title', $values['title']);
        $newdescription = EmbeddedImage::prepare_embedded_images($values['description'], 'blog', $values['id']);
        $artefact->set('description', $newdescription);
        $artefact->set('tags', $values['tags']);
        if (get_config('licensemetadata')) {
            $artefact->set('license', $values['license']);
            $artefact->set('licensor', $values['licensor']);
            $artefact->set('licensorurl', $values['licensorurl']);
        }
        $artefact->commit();
    }

    public static function get_links($id) {
        $wwwroot = get_config('wwwroot');

        return array(
            '_default'                                  => $wwwroot . 'artefact/blog/view/index.php?id=' . $id,
            get_string('blogsettings', 'artefact.blog') => $wwwroot . 'artefact/blog/settings/index.php?id=' . $id,
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

    public static function delete_form($id, $title = '') {
        global $THEME;

        $confirm = get_string('deleteblog?', 'artefact.blog');

        // Check if this blog has posts.
        $postcnt = count_records_sql("
            SELECT COUNT(*)
            FROM {artefact} a
            INNER JOIN {artefact_blog_blogpost} bp ON a.id = bp.blogpost
            WHERE a.parent = ?
            ", array($id));
        if ($postcnt > 0) {
            $confirm = get_string('deletebloghaspost?', 'artefact.blog', $postcnt);

            // Check if this blog posts used in views.
            $viewscnt = count_records_sql("
                SELECT COUNT(DISTINCT(va.view))
                FROM {artefact} a
                INNER JOIN {view_artefact} va ON a.id = va.artefact
                WHERE a.parent = ? OR a.id = ?
                ", array($id, $id));
            if ($viewscnt > 0) {
                $confirm = get_string('deletebloghasview?', 'artefact.blog', $viewscnt);
            }
        }

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
                    'src' => $THEME->get_image_url('btn_deleteremove'),
                    'alt' => get_string('deletespecific', 'mahara', $title),
                    'elementtitle' => get_string('delete'),
                    'confirm' => $confirm,
                    'value' => get_string('delete'),
                ),
            ),
        ));
    }

    /**
     * During the copying of a view, we might be allowed to copy
     * blogs. Users need to have multipleblogs enabled for these
     * to be visible.
     */
    public function default_parent_for_copy(&$view, &$template, $artefactstoignore) {
        global $USER, $SESSION;

        $viewid = $view->get('id');

        try {
            $user = get_user($view->get('owner'));
            set_account_preference($user->id, 'multipleblogs', 1);
            $SESSION->add_ok_msg(get_string('copiedblogpoststonewjournal', 'collection'));
        }
        catch (Exception $e) {
            $SESSION->add_error_msg(get_string('unabletosetmultipleblogs', 'error', $user->username, $viewid, get_config('wwwroot') . 'account/index.php'), false);
        }

        try {
            $USER->accountprefs = load_account_preferences($user->id);
        }
        catch (Exception $e) {
            $SESSION->add_error_msg(get_string('pleaseloginforjournals', 'error'));
        }

        return null;
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
    protected function postcommit_hook($new) {
        require_once(get_config('docroot') . 'blocktype/lib.php');
        require_once(get_config('docroot') . 'artefact/blog/blocktype/taggedposts/lib.php');
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

        // We want to get all blockinstances that may contain this blog post. That is currently:
        // 1) All blogpost blocktypes with this post in it
        // 2) All blog blocktypes with this posts's blog in it
        // 3) All recentposts blocktypes with this post's blog in it
        // 4) All taggedposts blocktypes with this post's tags
        $blocks = (array)get_column_sql('SELECT block
            FROM {view_artefact}
            WHERE artefact = ?
            OR artefact = ?', array($this->get('id'), $this->get('parent')));
        if (!$blocks) {
            $blocks = array();
        }

        // Get all "tagged blog entries" blocks that may contain this block
        // (we'll just check for a single matching tag here, and let each block
        // instance further down decide whether or not it matches
        $tags = $this->get('tags');
        if ($tags) {
            $blocks = array_merge($blocks, PluginBlocktypeTaggedposts::find_matching_blocks($tags));
        }

        // Now rebuild the list of which artefacts these blocks contain
        // in the view_artefacts table. (This is used for watchlist notifications)
        if ($blocks) {
            foreach ($blocks as $id) {
                $instance = new BlockInstance($id);
                $instance->rebuild_artefact_list();
            }
        }
    }

    /**
     * This function extends ArtefactType::delete() by also deleting anything
     * that's in blogpost.
     */
    public function delete() {
        if (empty($this->id)) {
            return;
        }

        require_once('embeddedimage.php');
        db_begin();
        $this->detach(); // Detach all file attachments
        delete_records('artefact_blog_blogpost', 'blogpost', $this->id);
        EmbeddedImage::delete_embedded_images('blogpost', $this->id);
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
        global $USER;

        $smarty = smarty_core();
        $smarty->assign('published', $this->get('published'));
        if (!$this->get('published')) {
            $notpublishedblogpoststr = get_string('notpublishedblogpost', 'artefact.blog');
            if ($this->get('owner') == $USER->get('id')) {
                $notpublishedblogpoststr .= ' <a href="' . get_config('wwwroot') . 'artefact/blog/post.php?id=' . $this->get('id') . '">' . get_string('publishit', 'artefact.blog') . '</a>';
            }
            $smarty->assign('notpublishedblogpost', $notpublishedblogpoststr);
        }
        $artefacturl = get_config('wwwroot') . 'artefact/artefact.php?artefact=' . $this->get('id');
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
        }
        $smarty->assign('artefactdescription', $postcontent);
        $smarty->assign('artefacttags', $this->get('tags'));
        $smarty->assign('artefactowner', $this->get('owner'));
        if (!empty($options['details']) and get_config('licensemetadata')) {
            $smarty->assign('license', render_license($this));
        }
        else {
            $smarty->assign('license', false);
        }

        $attachments = $this->get_attachments();
        if ($attachments) {
            require_once(get_config('docroot') . 'artefact/lib.php');
            foreach ($attachments as &$attachment) {
                $f = artefact_instance_from_id($attachment->id);
                $attachment->size = $f->describe_size();
                $attachment->iconpath = $f->get_icon(array('id' => $attachment->id, 'viewid' => isset($options['viewid']) ? $options['viewid'] : 0));
                $attachment->viewpath = get_config('wwwroot') . 'artefact/artefact.php?artefact=' . $attachment->id . '&view=' . (isset($options['viewid']) ? $options['viewid'] : 0);
                $attachment->downloadpath = get_config('wwwroot') . 'artefact/file/download.php?file=' . $attachment->id;
                if (isset($options['viewid'])) {
                    $attachment->downloadpath .= '&view=' . $options['viewid'];
                }
            }
            $smarty->assign('attachments', $attachments);
            if (isset($options['blockid'])) {
                $smarty->assign('blockid', $options['blockid']);
            }
            $smarty->assign('postid', $this->get('id'));
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
        return $THEME->get_image_url('journal_entry');
    }

    public static function is_singular() {
        return false;
    }

    public static function collapse_config() {
    }

    /**
     * This function returns the blog id and offset for a given post.
     *
     * @param integer $postid The id of the required blog post
     * @return object An object containing the required data
     */
    public static function get_post_data($postid) {
        $post = new stdClass();

        $post->blogid = get_field('artefact', 'parent', 'id', $postid, 'artefacttype', 'blogpost');

        if (is_postgres()) {
            $rownum = get_field_sql("SELECT rownum
                                    FROM (SELECT id, ROW_NUMBER() OVER (ORDER BY id DESC) AS rownum
                                        FROM {artefact}
                                        WHERE parent = ?
                                        ORDER BY id DESC) AS posts
                                    WHERE id = ?",
                    array($post->blogid, $postid));
        }
        else if (is_mysql()) {
            $initvar = execute_sql("SET @row_num = 0");
            if ($initvar) {
                $rownum = get_field_sql("SELECT rownum
                                        FROM (SELECT id, @row_num := @row_num + 1 AS rownum
                                            FROM {artefact}
                                            WHERE parent = ?
                                            ORDER BY id DESC) AS posts
                                        WHERE id = ?",
                        array($post->blogid, $postid));
            }
        }
        $post->offset = $rownum - 1;

        return $post;
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
            ORDER BY bp.published ASC, a.ctime DESC, a.id DESC',
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
                $params = array('id' => $file->attachment);
                if (!empty($viewoptions['viewid'])) {
                    $params['viewid'] = $viewoptions['viewid'];
                }
                $file->icon = call_static_method(generate_artefact_class_name($file->artefacttype), 'get_icon', $params);
                $data[$file->artefact]->files[] = $file;
            }
        }

        if ($tags = ArtefactType::tags_from_id_list($postids)) {
            foreach($tags as &$at) {
                $data[$at->artefact]->tags[] = $at->tag;
            }
        }

        foreach ($data as &$post) {
            // Format dates properly
            if (is_null($viewoptions)) {
                // My Blogs area: create forms for changing post status & deleting posts.
                $post->changepoststatus = ArtefactTypeBlogpost::changepoststatus_form($post->id, $post->published);
                $post->delete = ArtefactTypeBlogpost::delete_form($post->id, $post->title);
            }
            else {
                $by = $post->author ? display_default_name($post->author) : $post->authorname;
                $post->postedby = get_string('postedbyon', 'artefact.blog', $by, format_date($post->ctime));
                // Get comment counts
                if (!empty($viewoptions['countcomments'])) {
                    safe_require('artefact', 'comment');
                    require_once(get_config('docroot') . 'lib/view.php');
                    $view = new View($viewoptions['viewid']);
                    $artefact = artefact_instance_from_id($post->id);
                    list($commentcount, $comments) = ArtefactTypeComment::get_artefact_comments_for_view($artefact, $view, null, false);
                    $post->commentcount = $commentcount;
                    $post->comments = $comments;
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

        $setlimit = isset($pagination['setlimit']) ? $pagination['setlimit'] : false;

        if ($posts['limit'] && $pagination) {
            $pagination = build_pagination(array(
                'id' => $pagination['id'],
                'class' => 'center',
                'datatable' => $pagination['datatable'],
                'url' => $pagination['baseurl'],
                'jsonscript' => $pagination['jsonscript'],
                'count' => $posts['count'],
                'limit' => $posts['limit'],
                'setlimit' => $setlimit,
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
        if (get_config('licensemetadata')) {
            $artefact->set('license', $values['license']);
            $artefact->set('licensor', $values['licensor']);
            $artefact->set('licensorurl', $values['licensorurl']);
        }
        $artefact->commit();
        return true;
    }

    public static function changepoststatus_form($id, $published = null) {
        //Get current post status from database
        if ($published === null) {
            $post = new ArtefactTypeBlogPost($id);
            $published = $post->published;
        }
        if ($published) {
            $strchangepoststatus = get_string('unpublish', 'artefact.blog');
        }
        else {
            $strchangepoststatus = get_string('publish', 'artefact.blog');
        }
        return pieform(array(
            'name' => 'changepoststatus_' . $id,
            'jssuccesscallback' => 'changepoststatus_success',
            'successcallback' => 'changepoststatus_submit',
            'jsform' => true,
            'renderer' => 'oneline',
            'elements' => array(
                'changepoststatus' => array(
                    'type' => 'hidden',
                    'value' => $id,
                ),
                'currentpoststatus' => array(
                    'type' => 'hidden',
                    'value' => $published,
                ),'submit' => array(
                    'type' => 'submit',
                    'class' => 'publish',
                    'value' => $strchangepoststatus,
                    'help' => true,
                ),
            ),
        ));
    }

    public static function delete_form($id, $title = '') {
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
                    'src' => $THEME->get_image_url('btn_deleteremove'),
                    'alt' => get_string('deletespecific', 'mahara', $title),
                    'elementtitle' => get_string('delete'),
                    'confirm' => get_string('deleteblogpost?', 'artefact.blog'),
                    'value' => get_string('delete'),
                ),
            ),
        ));
    }

    /**
     * This function changes the blog post status.
     *
     * @param $newpoststatus: boolean 1=published, 0=draft
     * @return boolean
     */
    public function changepoststatus($newpoststatus) {
        if (!$this->id) {
            return false;
        }

        $this->set('published', (int) $newpoststatus);
        $this->commit();

        return true;
    }

    public static function get_links($id) {
        $wwwroot = get_config('wwwroot');

        return array(
            '_default' => $wwwroot . 'artefact/blog/view/index.php?blogpost=' . $id,
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
        static $blogids;
        global $USER, $SESSION;

        $viewid = $view->get('id');

        if (isset($blogids[$viewid])) {
            return $blogids[$viewid];
        }

        $blogname = get_string('viewposts', 'artefact.blog', $viewid);
        $data = (object) array(
            'title'       => $blogname,
            'description' => get_string('postscopiedfromview', 'artefact.blog', $template->get('title')),
            'owner'       => $view->get('owner'),
            'group'       => $view->get('group'),
            'institution' => $view->get('institution'),
        );
        $blog = new ArtefactTypeBlog(0, $data);
        $blog->commit();

        $blogids[$viewid] = $blog->get('id');

        try {
            $user = get_user($view->get('owner'));
            set_account_preference($user->id, 'multipleblogs', 1);
            $SESSION->add_ok_msg(get_string('copiedblogpoststonewjournal', 'collection'));
        }
        catch (Exception $e) {
            $SESSION->add_error_msg(get_string('unabletosetmultipleblogs', 'error', $user->username, $viewid, get_config('wwwroot') . 'account/index.php'), false);
        }

        try {
            $USER->accountprefs = load_account_preferences($user->id);
        }
        catch (Exception $e) {
            $SESSION->add_error_msg(get_string('pleaseloginforjournals', 'error'));
        }

        return $blogids[$viewid];
    }

    /**
     * Looks through the blog post text for links to download artefacts, and
     * returns the IDs of those artefacts.
     */
    public function get_referenced_artefacts_from_postbody() {
        return artefact_get_references_in_html($this->get('description'));
    }

    public static function is_countable_progressbar() {
        return true;
    }
}
