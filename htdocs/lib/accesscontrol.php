<?php

/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Gold <gold@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

require_once(get_config('libroot') . 'group.php');
require_once(get_config('libroot') . 'view.php');
require_once(get_config('docroot') . 'artefact/lib.php');
require_once(get_config('docroot') . 'artefact/comment/lib.php');
safe_require('artefact', 'blog');
safe_require('blocktype', 'wall');
safe_require('interaction', 'forum');

/**
 * Manage access control for various scenarios.
 */
class AccessControl {
  /**
   * A LiveUser object.
   *
   * @var LiveUser
   */
  protected $user;

  /**
   * An artefact.
   *
   * @var object
   */
  protected $file;

  /**
   * The Resource Type.
   *
   * @var string
   */
  protected $resourcetype;

  /**
   * The Resource Table.
   *
   * Primarily used for identifying how we process the related resourcetype.
   *
   * @var string
   */
  protected $resourcetable;

  /**
   * The Resource ID.
   *
   * @var int
   */
  protected $resourceid;

  /**
   * The resource types we can handle.
   *
   * The full list. Should all of these be in the array?
   *
   * - annotation
   * - annotationfeedback
   * - assessment
   * - blog
   * - blogpost
   * - book
   * - comment
   * - description
   * - forum
   * - group
   * - instructions
   * - introtext
   * - membership
   * - peerinstruction
   * - post
   * - introduction
   * - resumecoverletter
   * - resumeinterest
   * - staticpages
   * - text
   * - textbox
   * - topic
   * - verification_comment
   * - wallpost
   *
   * @var array
   */
  protected static $resourceTypes = [
    'comment',
    'annotation',
    'annotationfeedback',
    'assessment',
    'peerinstruction',
    'blog',
    'blogpost',
    'textbox',
    'editnote',
    'text',
    'textinstructions',
    'introtext',
    'wallpost',
    'staticpages',
    'verification_comment',
    //text is also verification
    // Extras.
    'group',
  ];

  /**
   * Constructor.
   *
   * At a minimum we need a user to check things against.
   *
   * @param LiveUser $user
   */
  public function __construct(LiveUser $user) {
    $this->user = $user;
    self::log('AccessControl starts for User ID:' . $this->user->get('id'));
  }

  /**
   * Return an access control object for the given user.
   *
   * @param LiveUser $user
   * @return AccessControl
   */
  public static function user(LiveUser $user) {
    return new AccessControl($user);
  }

  /**
   * Set the file we are working with.
   *
   * @param object $file
   * @return AccessControl
   * @throws InvalidArgumentException
   */
  public function set_file($file) {
    // Check if the $file is of type ArtefactTypeFile.
    if ($file instanceof ArtefactTypeFile) {
      // We can handle this. Set the file.
      $this->file = $file;
      self::log('File ID: ' . $this->file->get('id'));
      return $this;
    }
    // If we don't have a file, then we can't continue.
    throw new InvalidArgumentException('Invalid file type');
  }

  /**
   * Set the resource type and resource id we will be checking.
   *
   * @param string $resourcetype
   * @param int $resourceid
   * @return AccessControl
   */
  public function set_resource($resourcetype, $resourceid) {
    $this->resourcetype = $resourcetype ?: '';
    $this->resourceid = $resourceid ?: null;
    $this->resourcetable = EmbeddedImage::get_resourcetable($resourcetype);
    self::log('Resource Type: ' . $this->resourcetype);
    self::log('Resource ID: ' . $this->resourceid);
    self::log('Resource Table: ' . $this->resourcetable);
    return $this;
  }

  /**
   * Check if the user can view the resource.
   *
   * @return bool
   */
  public function is_visible() {
    self::log('Checking if the user can view the artefact');

    if (empty($this->file)) {
      throw new SystemException('No file set');
    }

    // Does the user own the file?
    if ($this->view_via_ownership()) {
      self::log('TRUE: View via ownership');
      return true;
    }
    else {
      self::log('Account (' . $this->user->get('id') . ') is not the file owner (' . $this->file->get('owner') . ')');
    }

    // Can we see the file via view_artefact or _artefact_file_embedded or artefact_attachment
    if ($this->view_via_file_on_view()) {
      self::log('TRUE: Can see a View via view_artefact or artefact_file_embedded');
      return true;
    }

    // If there is a resourcetype given, check access with the given resourcetable and ID info.
    self::log('Checking access via resource table: ' . $this->resourcetable);
    switch ($this->resourcetable) {
      case 'view_artefact':
        // If $this->view_via_file_on_view() is false we can end up here and
        // we know that the user does not have access to the file  directly so we check it's ancestors.
        $ancestors = $this->file->get_item_ancestors();
        if (!empty($ancestors)) {
          foreach ($ancestors as $ancestor) {
            $pathitem = artefact_instance_from_id($ancestor);
            $viewids = get_column_sql("SELECT view FROM {view_artefact} WHERE artefact = ?", array($ancestor));
            foreach ($viewids as $viewid) {
              if (artefact_in_view($pathitem, $viewid) && self::can_view_view($viewid)) {
                self::log('TRUE: Artefact is within another artefact that we have access to');
                return true;
              }
            }
          }
        }
        // we know that the user does not have access to the file.
        self::log('FALSE: No access to the file via view_artefact');
        return false;

      case 'block_instance':
        self::log('Checking block_instance');
        return $this->view_via_file_on_block();

      case 'resourcetype_is_viewid':
        self::log('Checking resourcetype_is_viewid');
        return $this->view_via_resourcetype_on_view();

      default:
        // Fall through to checking via the resource type.
        self::log('Checking via resource type');
        if (empty($this->resourcetype)) {
          self::log('FALSE: No resource type set');
          return false;
        }
        else {
          return $this->view_via_resourcetype();
        }
    }
    // We should never get here, but if we do we should deny access.
    return false;
  }

  /**
   * Check if the file is not an embedded file.
   *
   * @return bool
   */
  protected function file_is_not_an_embedded_file() {
    // Sanity checks.
    if (empty($this->resourcetype) || empty($this->resourceid)) {
      return false;
    }
    $imgispublic = get_field(
      'artefact_file_embedded',
      'id',
      'fileid',
      $this->file->get('fileid'),
      'resourcetype',
      $this->resourcetype,
      'resourceid',
      $this->resourceid
    );
    return $imgispublic === false;
  }

  /**
   * Check if the file is not an attachment.
   *
   * @return bool
   */
  protected function file_is_not_an_attachment() {
    $imgisanattachment = get_field(
      'artefact_attachment',
      'artefact',
      'attachment',
      $this->file->get('id')
    );
    return $imgisanattachment === false;
  }

  /**
   * Check if the user can view the artefact via ownership.
   *
   *  If the user owns the file, they can view it.
   * @return bool
   */
  protected function view_via_ownership() {
    $fileowner = $this->file->get('owner');
    if ($this->user->is_logged_in() && $fileowner == $this->user->get('id')) {
      return true;
    }
    return false;
  }

  /**
   * Check if the user can view the artefact via view
   *
   * This is done with when a resourcetype is mapped to the view_artefact table
   * OR When resourcetype and ID is not provided.
   * Note: files must appear in artefact_file_embedded table for a truthy response.
   *
   * @todo Do this when expanding the AccessControl class.
   * @return bool
   */
  protected function view_via_file_on_view() {
    // Check that the file is embedded in a block on a view to prevent 'view'
    // being removed from the URL to expose the file.
    $embedded_files_view_ids = EmbeddedImage::find_viewids_from_embedded_file((int) $this->file->get('fileid'));
    $view_artefact_view_ids = $this->find_viewids_from_view_artefact();
    $attachments_view_ids = $this->find_viewids_from_view_attachment();

    // We cannot use array_unique here because the array has objects.
    // This will still be a short list though.
    $view_ids = array_merge($embedded_files_view_ids, $view_artefact_view_ids, $attachments_view_ids);
    foreach ($view_ids as $id) {
      return self::can_view_view($id);
    }
    self::log('No views were found via embedded files or view artefacts for fileid ' . $this->file->get('id'));
    return false;
  }

  /**
   * Check if the user can view a single view.
   *
   * The resourcetype is the view id.
   *
   * @return bool
   */
  protected function view_via_resourcetype_on_view() {
    // Doublecheck we have a valid resourcetype because 'instructions' for textblock became 'textinstructions'
    // and we might have an old bad url
    if ($this->resourcetype == 'instructions' && !get_field('artefact_file_embedded', 'id', 'fileid', $this->file->get('id'), 'resourcetype',  'instructions', 'resourceid', $this->resourceid)) {
      self::log('FALSE: Obsolete resourcetype ' . $this->resourcetype . ' for this resource - expected textinstructions');
      return false;
    }
    $view = new View($this->resourceid);
    $message = 'View via resourcetype being viewid. e.g. view.id = ' . $this->resourceid;
    if (self::can_view_view($view)) {
      self::log('TRUE: ' . $message . ' from ' . $this->resourcetype . '=' . $this->resourceid . ' on the URL');
      return true;
    }
    else {
      self::log('FALSE: ' . $message . ' from ' . $this->resourcetype . '=' . $this->resourceid . ' on the URL');
      return false;
    }
  }

  /**
   * Check if the user can view the artefact via a block.
   *
   * If the embedded file's resourcetype refers to a blocktype (resourcetable = 'block_instance'),
   * find the view it lives on. Then, check if the block is on a view the user can see.
   * This case is if the view_via_file_on_view doesn't catch, i.e. the artefact doesn't have an entry in view_artefact
   * We must do this the long way.
   *
   * @return bool
   */
  protected function view_via_file_on_block() {
    $block = new BlockInstance($this->resourceid);
    $configdata = $block->get('configdata');
    $blocktype = $block->get('blocktype');

    // Special cases where the the block_instance table is not helpful. E.g. private/draft content...

    if ($this->resourcetype == 'peerinstruction') {
      self::log('SPECIAL CASE: View via file on instructions of peer assessment block  | peerinstruction )');
      return $this->can_view_peerinstruction();
    }

    if ($this->resourcetype == 'text' && $blocktype == 'verification') {
      // These are not saved to a view/embedded table as progress completion pages
      // where verification blocks live are not typical views.
      // These blocks don't have a draft mode - the block may turn yellow only because there is
      // a related comment that is draft. The content is not however.
      return self::can_view_view($block->get('view'));
    }

    // Text blocks can be in draft mode
    if ($this->resourcetype == 'text' && $blocktype == 'text') {
      self::log('SPECIAL CASE: Resource type: ' . $this->resourcetype);

      $is_draft = array_key_exists('draft', $configdata) ? $configdata['draft'] : false;
      $log_message = 'View via file on draft text block id ' . $this->resourceid;

      if (!$is_draft) {
        self::log('TRUE: Not a draft text block' . $log_message);
      }
      else {
        self::log('FALSE: Is a draft text block ' . $log_message);
      }

      $can_view_view = self::can_view_view($block->get('view'));
      if (!$can_view_view) {
        self::log('FALSE: We cannot see the View the Block appears on.');
        return false;
      }
      return !$is_draft;
    }

    // General check
    $block_view = $block->get('view');
    $can_view_block = self::can_view_view($block_view);
    if ($can_view_block) {
      self::log('TRUE: View via file on block on view id ' . $block_view);
      return true;
    }

    // Final case: blocktypes that match the resourcetype.
    // @todo: Could this just be a new BlockInstance($this->resourceid)?
    $sql = "
    /* Find Views matching the Block Instance ID as the resource ID, blocktype as resourcetype */
        SELECT bi.view
        FROM {block_instance} bi
        INNER JOIN {artefact_file_embedded} embedded_files
            ON (
              embedded_files.resourceid = bi.id
              AND embedded_files.resourcetype = bi.blocktype
            )
        WHERE bi.id = ?
    ";
    $embedded_views = get_records_sql_array($sql, [$this->resourceid]);
    if ($embedded_views && is_array($embedded_views)) {
      foreach ($embedded_views as $view) {
        if (self::can_view_view((int) $view->view)) {
          // The currently logged in user can view this view, so
          // return it.
          self::log('TRUE: View via file on block on view id ' . $view->view);
          return true;
        }
        else {
          self::log('FALSE: View via file on block on view id ' . $view->view);
        }
      }
    }
    self::log('FALSE: No views were found to be visible to user ' . $this->user->get('id'));

    return false;
  }

  /**
   * Check resource types.
   *
   * @return bool
   */
  protected function view_via_resourcetype() {
    self::log('Checking resourcetype ' . $this->resourcetype);
    switch ($this->resourcetype) {
      case 'comment':
        // Test comment on Page and an Artefact(?)
        return $this->process_check('can_view_comment');

      case 'annotation':
        return $this->process_check('can_view_annotation');

      case 'annotationfeedback':
        return $this->process_check('can_view_annotationfeedback');

      case 'assessment':
        return $this->process_check('can_view_assessment');

      case 'blog':
        return $this->process_check('can_view_blog');

      case 'blogpost':
        return $this->process_check('can_view_blogpost');

      case 'text':
        return $this->process_check('can_view_blockinstance');

      case 'textbox':
        return $this->process_check('can_view_textbox');

      case 'editnote':
        return $this->process_check('can_view_thing');

      case 'introtext':
        return $this->process_check('can_view_introtext');

      case 'wallpost':
        return $this->process_check('can_view_wallpost');

      case 'staticpages':
        return $this->process_check('can_view_staticpages');

      case 'group':
        return $this->process_check('can_view_artefact_in_group');

      case 'institution':
        return $this->process_check('can_view_artefact_in_institution');

      case 'verification_comment':
        return $this->process_check('can_view_verification_comment');

      case 'academicgoal':
      case 'academicskill':
      case 'careergoal':
      case 'careerskill':
      case 'personalgoal':
      case 'personalskill':
      case 'interest':
      case 'coverletter':
      case 'introduction':
        return $this->process_check('can_view_resume_composite_resourcetype');
    }

    self::log('Case missing for Resource Type: ' . $this->resourcetype);
    return false;
  }

  /**
   * Can view files in the instructions content of a peer assessment block
   *
   * Peer instructions are on a Peer Assessment block.
   * Resource ID is block ID
   * The owner will already get the image through ownership, so can skip that case here
   *
   * In the context of peer assessments, peer instructions are written by a template author,
   * however the view owner should also see the instructions as they can edit the block.
   *
   * @return bool
   */
  protected function can_view_peerinstruction() {
    // Get the view for this peer assessment block
    $get_block_view_sql = 'SELECT view FROM {artefact_peer_assessment} where block = ? LIMIT 1';
    $peerassessment_block = get_record_sql($get_block_view_sql, [$this->resourceid]);

    $user_is_peer = $this->user_is_peer($peerassessment_block->view);
    return $user_is_peer;
  }

  /**
   * Check if the user is a peer on the view
   *
   * @param int $viewid
   * @return bool
   */
  protected function user_is_peer($viewid) {
    // Check if the viewer of the page is a peer
    $get_access_sql = "SELECT usr FROM {view_access} WHERE usr = ? AND role = ? AND view = ? LIMIT 1";
    $user_is_peer = get_field_sql($get_access_sql, [$this->user->get('id'), 'peer', $viewid]);
    if ($user_is_peer) {
        self::log('TRUE: User has access to view as a peer role');
        return true;
    }
    self::log('FALSE: User does not have access to view as a peer role');
    return false;
  }

  /**
   * Check if the user is a verifier on the view
   *
   * @param int $viewid
   * @return bool
   */
  protected function user_is_verifier($viewid) {
    // Check if the viewer of the page is a verifier.
    $get_access_sql = "
      SELECT usr
      FROM {view_access}
      WHERE usr = ?
        AND role = ?
        AND view = ?
      LIMIT 1";
    $user_is_verifier = get_field_sql($get_access_sql, [
      $this->user->get('id'),
      'verifier',
      $viewid]);
    if ($user_is_verifier) {
        self::log('User has access to view as a verifier role');
        return true;
    }
    self::log('User does not have access to view as a verifier role');
    return false;
  }

  /**
   * Check peer can see content + special cases
   *
   * Note: published comments can be seen by peers who can see content
   * When peers can't see portfolio content they can still see verification blocks
   * that are published. Final assessments on the overview page.
   * Context: It is content put on by the final assessor, not the view owner.
   *
   *  Reference: https://manual.mahara.org/en/21.10/blocks/general.html
   *
   * @param  mixed $view_id
   * @return bool
   */
  protected function peer_can_see_content($view_id) {
    // SPECIAL CASES: Even if the institution setting (is turned on) that prevents peers to see pages..
    // - portfolio-completion pages (type='progress') CAN still be seen if there is
    // - page comments CAN still be seen

    $view = new View($view_id);
    $is_progress_page = $view->get('type') == 'progress';
    $peer_can_see_all_content = $this->user->peers_allowed_content();
    // Is the comment on a view or an artefact?
    $commentonview = null;
    if ($this->resourceid) {
        $commentonview = get_field('artefact_comment_comment', 'onview', 'hidden', 0, 'private', 0, 'artefact', $this->resourceid);
    }
    if ($is_progress_page) {
      self::log('TRUE: View is a "progress" page, peers can see content regardless of instititon rule
      that prevents them from seeing block content.');
      return true;
    }
    else if ($this->resourcetype == 'comment' && $commentonview) {
      self::log('TRUE: Artefact is a page comment, peers can see page comments regardless of instititon rule
      that prevents them from seeing block content.');
      return true;
    }
    else if ($this->resourcetype == 'instructions') {
      self::log('TRUE: User can view the view as a Peer, so can see the instructions');
      return true;
    }
    else {
      if ($peer_can_see_all_content) {
        self::log('TRUE: Peer can see all content on this non-portfolio completion page.');
        return true;
      }
      else {
        self::log('FALSE: Peer cannot see all content and this is not a progress page.');
        return false;
      }
    }
  }

  protected function can_view_resume_composite_resourcetype() {
    // Differently to view_via_file_on_view, here the resource ID is not the view ID.
    // So we will use the file ID as the artefact ID to find our views.
    $sql = "SELECT * FROM {view_artefact} va WHERE va.artefact = ?";
    $fileID = $this->file->get('id');

    $views = get_records_sql_array($sql, [$fileID]);

    if ($views && is_array($views)) {
      foreach ($views as $view) {
        if (can_view_view((int) $view->view, $this->user->get('id'))) {
          self::log('TRUE: View via resume composite: ' . $this->resourcetype . ' file on view ' . $view->view);
          return true;
        }
        else {
          self::log('FALSE: View via resume composite: ' . $this->resourcetype . ' file on view ' . $view->view);
        }
      }
    }
    self::log('FALSE: No views were found to be visible to user ' . $this->user->get('id'));
    return false;
  }

  /**
   * Check if they can see verification comment files
   *
   * @return bool
   */
  protected function can_view_verification_comment() {
    // The resourcetable for verification_comment is blocktype_verification_comment.
    $block_instance_id = get_field($this->resourcetable, 'instance', 'id', $this->resourceid);
    $block_instance = new BlockInstance($block_instance_id);
    $is_private = get_field($this->resourcetable, 'private', 'instance', $block_instance_id);

    // Check if the user is a verifier on the view.
    $viewid = $block_instance->get('view');
    $user_is_verifier = $this->user_is_verifier($viewid);

    // Draft verification comments can only be seen by the verifier
    // Return false if draft and user is not verifier
    if (!$user_is_verifier && $is_private) {
      self::log('FALSE: User is not a verifier and the comment on the verification block is private/draft');
      return false;
    }
    else {
      if (self::can_view_view($viewid)) {
        self::log('TRUE: Comment on the verification block is public, User can view the View.');
        return true;
      }
      else {
        self::log('FALSE: The View is not visible to the user.');
        return false;
      }
    }
  }

  /**
   * Process a "can view" check with logging.
   *
   * @param string $check The check to perform.
   * @return bool
   */
  protected function process_check($check) {
    $result = $this->$check();
    if ($result) {
      self::log('TRUE: ' . $check);
    }
    else {
      self::log('FALSE: ' . $check);
    }
    return $result;
  }

  /**
   * Check if the user can view a textbox.
   *
   * @return bool
   */
  protected function can_view_textbox() {
    // Check that the user can view the textbox.
    $textbox = get_record('artefact_file_embedded', 'resourceid', $this->resourceid, 'resourcetype', 'textbox', 'fileid', $this->file->get('fileid'));
    if (!$textbox) {
      return false;
    }
    // Get view from view_artefact.
    $view = get_record('view_artefact', 'artefact', $textbox->resourceid);
    if (!$view) {
      return false;
    }

    return self::can_view_view($view->view);
  }

  /**
   * Check if the user can view an introtext.
   *
   * @return bool
   */
  protected function can_view_introtext() {
    // Check that the user can view the introtext.
    $introtext = get_record(
      'artefact_file_embedded',
      'resourceid',
      $this->resourceid,
      'resourcetype',
      'introtext',
      'fileid',
      $this->file->get('fileid')
    );
    if (!$introtext) {
      return false;
    }
    // Get view from block_instance.
    $block = get_record('block_instance', 'id', $introtext->resourceid);
    if (!$block) {
      return false;
    }
    // Get the view the block instance is on.
    $view = new View($block->view);
    if (!$view) {
      return false;
    }

    return self::can_view_view($view);
  }

  /**
   * Check if the user can view a blog.
   *
   * @param int $blogid Optional blog id to check.
   * @return bool
   */
  protected function can_view_blog($blogid = null) {
    if (empty($blogid)) {
      $blogid = $this->resourceid;
    }
    // Check that the user can view the blog.
    $blog = new ArtefactTypeBlog($blogid);
    if (!$blog) {
      return false;
    }
    // This checks against the global $USER. Our use of $this->user is not
    // taken into account here.
    $can_view = $blog->check_permission();
    return $can_view;
  }

  /**
   * Check if the user can view a blogpost.
   */
  protected function can_view_blogpost() {
    // Check that the user can view the blogpost.
    $blogpost = get_record('artefact', 'id', $this->resourceid);
    if (!$blogpost) {
      return false;
    }
    $blogpost = new ArtefactTypeBlogPost($blogpost->id);
    $blogid = $blogpost->get('parent');
    $can_view = $this->can_view_blog($blogid);
    return $can_view;
  }

  /**
   * Check if the Artefact is embedded in a Comment.
   *
   * A comment is on a view and can be private.
   * The resourcetype 'comment' is also used for comment attachments
   *
   * @param int $artefactid Optional artefact id to check.
   *
   * @return bool
   */
  protected function can_view_comment($artefactid = null) {
    // If $artefactid is not set, use the resourceid.
    if (empty($artefactid)) {
      $artefactid = $this->resourceid;
    }
    // Check that the file is embedded in a block on a view to prevent 'view'
    // being removed from the URL to expose the file.
    // $comment = get_record('artefact_comment_comment', 'artefact', $this->resourceid);
    $comment = new ArtefactTypeComment($artefactid);
    if (!$comment) {
      return false;
    }

    // First check, embedded images on a comment (page comments)

    $sql = 'SELECT c.artefact AS comment_artefact, c.private,
                   c.onview, a.author, c.onartefact,
                   CASE WHEN c.onview IS NOT NULL
                   THEN (SELECT owner FROM {view} WHERE id = c.onview) ELSE NULL END AS view_owner
            FROM {artefact_comment_comment} AS c
            JOIN {artefact} a ON a.id = c.artefact
            WHERE a.id = ? AND c.deletedby is null AND c.hidden = 0
    ';
    $comment = get_record_sql($sql, [$artefactid]);
    $is_private_comment = $comment->private;
    // Get the view that this comment is on and check if it's private, then if on the user can see.

    if (!$comment) {
      self::log('FALSE: No comment found');
      return false;
    }

    $artefact = null;
    $view_owner = null;
    $commentonartefact = $comment->onartefact;
    if ($commentonartefact) {
      // find the top ancestor
      if (!is_object($commentonartefact)) {
        require_once(get_config('docroot') . 'artefact/lib.php');
        $artefact = artefact_instance_from_id($commentonartefact);
        $view_owner = $artefact->get('owner');
      }
      $ancestors = $artefact->get_item_ancestors();

      if ($is_private_comment) {
        return $this->can_view_private_comment($comment->author, $view_owner);
      }

      // Use the highest ancestor to see if it exists on a page
      $views = get_column('view_artefact', 'view', 'artefact', $ancestors[0]);
      foreach ($views as $view) {
        return self::can_view_view($view);
      }
    }
    // Ownership check will have given the comment owner (aka view owner) the file already.
    // Exception - when you add attachments, the files will belong to the view owner.
    // So for private comment attachments, check author is logged in so they can see it too.
    else if ($is_private_comment) {
      return $this->can_view_private_comment($comment->author, $comment->view_owner);
    }
    // If the user is the author, show it to them
    else if ($comment->author == $this->user->get('id')) {
      return true;
    }
    // Fall back to access control's can_view_view.
    return self::can_view_view($comment->onview);
  }

  /**
   * Checking if views can be seen by logged in user + check peer conditions
   *
   * @param  int|View $view id or View object
   * @return bool
   */
  protected function can_view_view($view) {
    $view_id = $view instanceof View ? $view->get('id') : (int) $view;

    // Calling mahara.php's generic can_view_view
    if (can_view_view($view_id, $this->user->get('id'))) {
      if ($this->user_is_peer($view_id) && !self::peer_can_see_content($view_id)) {
        self::log('Cannot view via file on View ' . $view_id);
        return false;
      }
      // The currently logged in user can view this view
      self::log('Can view via file on View ' . $view_id);
      return true;
    }
    self::log('Cannot view via file on View ' . $view_id);
    return false;
  }

  /**
   * Check if the user can view a private comment.
   *
   * @param  mixed $comment_author_id
   * @param  mixed $view_owner_id
   * @return bool
   */
  protected function can_view_private_comment($comment_author_id, $view_owner_id) {
    $usr_is_view_owner = $view_owner_id == $this->user->get('id');
    if ($usr_is_view_owner) self::log('User is view owner');
    $usr_is_comment_author = $comment_author_id == $this->user->get('id');
    if ($usr_is_view_owner) self::log('User is comment author ');

    if ($usr_is_view_owner || $usr_is_comment_author) {
      self::log('TRUE: private comment is visible to comment author/view owner');
      return true;
    }
    self::log('FALSE: private comment is NOT visible to this user');
    return false;
  }

  /**
   * Check if the Artefact is embedded in an Annotation Feedback.
   *
   * @todo How to respond to private == 1?
   * @return bool
   */
  protected function can_view_annotationfeedback() {
    // Check that the file is embedded in a block on a view to prevent 'view'
    // being removed from the URL to expose the file.
    require_once(get_config('docroot') . 'artefact/annotation/lib.php');
    $annotation_feedback_info = get_record('artefact_annotation_feedback', 'artefact', $this->resourceid);
    $annotation_feedback = new ArtefactTypeAnnotationfeedback($annotation_feedback_info->artefact);

    if (!$annotation_feedback_info) {
      return false;
    }

    $is_private_feedback = $annotation_feedback_info->private;
    $feedback_author = $annotation_feedback->get('author');
    $view_owner = $annotation_feedback->get('owner');

    if ($is_private_feedback) {
      self::log('Looking at private annotation feedback');
      $can_see_private_feedback = $this->can_view_private_comment($feedback_author, $view_owner);
      if ($can_see_private_feedback) {
        self::log('TRUE: can see private annotation feedback');
        return true;
      }
      else {
        self::log('FALSE: cannot see private annotation feedback');
        return false;
      }
    }
    self::log('Not looking at private annotation feedback');
    return $this->can_view_annotation($annotation_feedback_info->onannotation);
  }

  /**
   * Check if the Artefact is embedded in an Annotation.
   *
   * @param int $annotationid An optional annotation ID to check.
   *
   * @return bool
   */
  protected function can_view_annotation($annotationid = null) {
    // Allow for the check to come from annotation feedback.
    if (empty($annotationid)) {
      $annotationid = $this->resourceid;
    }
    // Check that the file is embedded in a block on a view to prevent 'view'
    // being removed from the URL to expose the file.
    $annotation = get_record('artefact_annotation', 'annotation', $annotationid);
    if (!$annotation) {
      return false;
    }

    if (!empty($annotation->view)) {
      return self::can_view_view($annotation->view);
    }

    return false;
  }

  /**
   * Check if the Artefact is embedded in a Assessment.
   *
   * @return bool
   */
  protected function can_view_assessment() {
    // Check that the file is embedded in a block on a view to prevent 'view'
    // being removed from the URL to expose the file.
    $assessment = get_record('artefact_peer_assessment', 'assessment', $this->resourceid);
    if (!$assessment) {
      return false;
    }

    require_once(get_config('docroot') . 'artefact/peerassessment/lib.php');
    $peer_assessment = new ArtefactTypePeerassessment($this->resourceid);
    $assessment_author = $peer_assessment->get('usr');
    $assessment_view = $peer_assessment->get('view');
    $is_private = $peer_assessment->get('private');
    if ($is_private) {
      // Check if the user is the assessment author to let them see the draft/private content.
      return $this->user->get('id') == $assessment_author;
    }
    return self::can_view_view($assessment_view);
  }

  /**
   * Check if the Artefact is embedded in a wall post.
   *
   * @todo Can a wall post be in a Group rather than a View?
   *
   * @return bool
   */
  protected function can_view_wallpost() {
    // Check that the file is embedded in a block on a view to prevent 'view'
    // being removed from the URL to expose the file.
    $sql = '
      SELECT wallpost.private, v.id as view_id, v.owner as view_owner
      FROM {blocktype_wall_post} wallpost
      JOIN {block_instance} block
        ON block.id = wallpost.instance
      JOIN {view} v
        ON v.id = block.view
      WHERE wallpost.id = ?
    ';

    $params = [$this->resourceid];
    $posts = get_records_sql_array($sql, $params);
    foreach ($posts as $post) {
      if ($post->private) {
        // Check for ownership will have served the file before this point,
        // So all we need to check is if the user is the owner of the view with the wall block.
        return $this->user->get('id') == $post->view_owner;
      }
      else {
        return self::can_view_view($post->view_id);
      }
    }
  }

  /**
   * Check if the Artefact is embedded in a block instance.
   *
   * If the Block Instance is on a View we just need to know if we can see
   * that View.
   *
   * @return bool
   */
  protected function can_view_blockinstance() {
    // Fetch the block instance.
    $block_instance = get_record(
      'block_instance',
      'id',
      $this->resourceid,
      'blocktype',
      $this->resourcetype
    );
    // Is our block on a View?
    if ($block_instance && $block_instance->view) {
      // Can we view this View?
      return self::can_view_view($block_instance->view);
    }
    return false;
  }

  /**
   * Check if the Artefact is in a Group the user can see.
   *
   * @return bool
   */
  protected function can_view_artefact_in_group() {
    // Fetch the Group.
    self::log('Checking access to artefact in group');
    $group = get_record('group', 'id', $this->resourceid);
    if ($group) {
      // Are we a member of the group that can see the files in files area
      if (group_user_access($group->id) && $this->user->can_view_artefact($this->file)) {
        self::log('TRUE: User is in the group');
        return true;
      }
      if ($group->public) {
        // The group is public so we can see this Artefact.
        self::log('TRUE: Group is public');
        return true;
      }

      // Fetch the artefact_file_embedded record for this fileid.
      $afe = get_record(
        'artefact_file_embedded',
        'fileid',
        $this->file->get('id')
      );
      if ($afe) {
        self::log('We have an embedded file record');
        // Do we have a matching parameter?
        $paramid = param_integer($afe->resourcetype, null);
        if ($paramid != $afe->resourceid) {
          // We don't have a matching parameter so we can't see this image.
          self::log('FALSE: No matching parameter');
          return false;
        }
        // The URL parameter matches the resourceid for the file. We can
        // trust this.  Can we see the area of the group?
        // Check access based on the artefact_file_embedded resourcetype.
        switch ($afe->resourcetype) {
          case 'group':
            // This is a Group level image. If we have access we can see it.
            return $this->user->is_logged_in();

          case 'comment':
            self::log('Checking access to artefact in a comment');
            return $this->can_view_comment($paramid);


            // Topic and Post are related to a Forum. We can look up the forumid
            // and let it fall through to the Forum check.
          case 'post':
            $postid = null;
            $post = get_record('interaction_forum_post', 'id', $afe->resourceid);
            if (!$post) {
              self::log('FALSE: No matching post');
              return false;
            }
            else {
              self::log('Found post for interaction_forum_post.id: ' . $afe->resourceid);
            }

          case 'topic':
            if (!empty($post)) {
              // If we have a $post we can use it to get the forumid.
              self::log('Using $post to get the Topic');
              $topic = get_record('interaction_forum_topic', 'id', $post->topic);
            }
            else {
              // If we don't have a $post we are looking at a topic.
              self::log('Looking up Topic from the embedded file record');
              $topic = get_record('interaction_forum_topic', 'id', $afe->resourceid);
            }
            if ($topic) {
              self::log('Found Topic for interaction_forum_topic.id: ' . $topic->id);
            }
            else {
              self::log('FALSE: No matching topic');
              return false;
            }

          case 'forum':
            self::log('Checking access to artefact in a forum');
            if (!empty($topic)) {
              // If we have a $topic we can use it to get the forumid.
              self::log('Using $topic to get the Forum');
              $forumid = $topic->forum;
            }
            else {
              // If we don't have a $topic we are looking at a forum.
              self::log('Looking up Forum from the embedded file record');
              $forumid = $afe->resourceid;
            }
            if ($forumid) {
              self::log('Checking Forum for interaction_forum.id: ' . $forumid);
              $canview = user_can_access_forum((int) $forumid, (int) $this->user->get('id'));
              if ($canview) {
                self::log('TRUE: User can access forum');
                return true;
              }
              else {
                self::log('FALSE: User cannot access forum');
                return false;
              }
            }
            else {
              self::log('FALSE: No matching forum');
              return false;
            }

          default:
            // We don't handle this subresource type.
            self::log('FALSE: We do not handle this subresource type yet (' . $afe->resourcetype . ')');
            return false;
        }
      }
      else {
        self::log('FALSE: No embedded file record');
        return false;
      }
    }
    self::log('FALSE: No group found for group id ' . $this->resourceid);
    return false;
  }

  /**
   * Check if the Artefact is embedded in a Static Page.
   *
   * If so, check if the user can view the Static Page.
   *
   * @return bool
   */
  protected function can_view_staticpages() {
    $embedded_file = $this->get_artefact_file_embedded();
    if (!$embedded_file) {
      return false;
    }
    // Check $embeddedfile->name is a public static page.
    if ($embedded_file->name == 'about' || $embedded_file->name == 'loggedouthome') {
      return true;
    }

    // Checks for logged in users.
    if ($this->user->is_logged_in()) {
      // The 'mahara' institution is public for all logged in users.
      if ($embedded_file->institution == 'mahara') {
        return true;
      }

      // Check if the logged in user is in the institution the file belongs to.
      if ($this->user->in_institution($embedded_file->institution)) {
        return true;
      }
    }

    return false;
  }

  /**
   * Check if the user can view this thing.
   *
   * This is a placeholder function for now.
   *
   * @return bool
   */
  protected function can_view_thing() {
    // Check that the file is in the artefact_file_embedded table.
    log_debug('Unhandled Resource Type: ' . $this->resourcetype);
    return false;
  }

  /**
   * Fetch the artefact_file_embedded record.
   *
   * @return object
   */
  protected function get_artefact_file_embedded() {
    $sql = "SELECT afe.*, sc.name, sc.institution
        FROM site_content sc
        JOIN artefact_file_embedded afe
          ON afe.resourceid = sc.id
        WHERE afe.resourcetype = ?
          AND afe.resourceid = ?
          AND afe.fileid = ?";
    $result = get_record_sql(
      $sql,
      [
        $this->resourcetype,
        $this->resourceid,
        $this->file->get('fileid'),
      ]
    );
    if (!$result) {
      return false;
    }
    return $result;
  }

  /**
   * Getter for resourceTypes.
   *
   * @return array
   */
  public static function get_resource_types() {
    return self::$resourceTypes;
  }

  /**
   * Log a message to the screen.
   *
   * If the site is not in production mode and we have 'dev' on the querystring
   * we can log the message to the screen.
   *
   * @param string $message
   * @return void
   */
  public static function log($message) {
    if (!get_config('productionmode') && param_boolean('dev')) {
      $bt = debug_backtrace();
      if (count($bt) == 2) {
        log_debug($bt[1]['function'] . '() - ' . $message . ' on line ' . $bt[0]['line']);
      }
      else {
        log_debug($message . ' on line ' . $bt[0]['line']);
      }
    }
  }


  /**
   * Retrieve the 'view' for this 'artefact' in the 'view_artefact' table.
   *
   * @return array $view_ids.
   */
  protected function find_viewids_from_view_artefact() {
    $sql = "
      /* Find Views matching View Artefacts as the Resource ID */
      SELECT view
      FROM {view_artefact}
      WHERE artefact = ?
      ";
    $data = get_records_sql_array($sql, [$this->file->get('fileid')]);

    if (!$data) {
      return [];
    }

    $view_ids = [];
    foreach ($data as $object) {
      $view_ids[] = $object->view;
    }
    return $view_ids;
  }

  /**
   * Find the view of file attachments on artefacts
   *
   * Note: in some cases the resourcetype is already given as 'view' but that can be
   * manipulated so we'll ignore that and look for file attachment record instead
   *
   * @return array $view_ids
   */
  protected function find_viewids_from_view_attachment() {
    $sql = "
      /* Find artefact that this attachment (file) is related to */
      SELECT view
      FROM {artefact_attachment} attachment
      JOIN {view_artefact} va ON attachment.artefact = va.artefact
      WHERE attachment.attachment = ?
      ";

    $attachment_on_artefact_views = get_records_sql_array($sql, [$this->file->get('fileid')]);

    if (!$attachment_on_artefact_views) {
      return [];
    }

    $view_ids = [];
    foreach ($attachment_on_artefact_views as $object) {
      $view_ids[] = $object->view;
    }
    return $view_ids;
  }


  protected function can_view_artefact_in_institution() {
    self::log('Checking access to artefact in institution');
    $institution = get_record('institution', 'name', $this->resourceid);
    if ($institution) {
        // If the file is in the public directory, it's fine to serve
        $fileispublic = $this->file->get('institution') == 'mahara';
        $fileispublic = $fileispublic && (bool)get_field('artefact', 'id', 'id', $this->file->get('fileid'), 'parent', ArtefactTypeFolder::admin_public_folder_id());
        if ($fileispublic) {
            self::log('TRUE: We can see the file as it lives in the site public folder');
            return true;
        }
        // If the file is in the logged in menu and the user is logged in then
        // they can view it
        $fileinloggedinmenu = $this->file->get('institution') == 'mahara';
        // check if users are allowed to access files in subfolders
        if (!get_config('sitefilesaccess')) {
            $fileinloggedinmenu = $fileinloggedinmenu && $this->file->get('parent') == null;
        }
        $fileinloggedinmenu = $fileinloggedinmenu && $this->user->is_logged_in();
        $fileinloggedinmenu = $fileinloggedinmenu && record_exists('site_menu', 'file', $this->file->get('fileid'), 'public', 0);
        if ($fileinloggedinmenu) {
            self::log('TRUE: We can see the file as it is in a logged in menu');
            return true;
        }
        // Alternatively, if you own the file or you are an admin, it should always work.
        if ($this->user->can_view_artefact($this->file)) {
            self::log('TRUE: We can see the file as we can see the artefact');
            return true;
        }
    }
    self::log('FALSE: No embedded file record');
    return false;
  }
}
