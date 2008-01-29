<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2007 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @subpackage artefact-internal
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

class PluginArtefactFile extends PluginArtefact {

    public static function get_artefact_types() {
        return array(
            'file',
            'folder',
            'image',
        );
    }
    
    public static function get_block_types() {
        return array('image');
    }

    public static function get_plugin_name() {
        return 'file';
    }

    public static function menu_items() {
        return array(
            array(
                'path' => 'myportfolio/files',
                'url' => 'artefact/file/',
                'title' => get_string('myfiles', 'artefact.file'),
                'weight' => 20,
            ),
        );
    }

    public static function get_event_subscriptions() {
        $subscriptions = array(
            (object)array(
                'plugin'       => 'file',
                'event'        => 'createuser',
                'callfunction' => 'newuser',
            ),
        );

        return $subscriptions;
    }

    public static function postinst() {
        set_config_plugin('artefact', 'file', 'defaultquota', 10485760);
        self::resync_filetype_list();
    }

    public static function newuser($event, $user) {
        update_record('usr', array('quota' => get_config_plugin('artefact', 'file', 'defaultquota')), array('id' => $user['id']));
    }
    

    public static function sort_child_data($a, $b) {
        if ($a->container && !$b->container) {
            return -1;
        }
        else if (!$a->container && $b->container) {
            return 1;
        }
        return strnatcasecmp($a->text, $b->text);
    }

    public static function themepaths($type) {
        static $themepaths = array(
            'file' => array(
                'images/file.gif',
                'images/folder.gif',
                'images/image.gif',
            ),
        );
        return $themepaths[$type];
    }

    public static function jsstrings($type) {
        static $jsstrings = array(
            'file' => array(
                'mahara' => array(
                    'cancel',
                    'delete',
                    'edit',
                    'tags',
                ),
                'artefact.file' => array(
                    'copyrightnotice',
                    'create',
                    'createfolder',
                    'deletefile?',
                    'deletefolder?',
                    'Description',
                    'destination',
                    'editfile',
                    'editfolder',
                    'File',
                    'fileexistsoverwritecancel',
                    'filenamefieldisrequired',
                    'home',
                    'Name',
                    'namefieldisrequired',
                    'nofilesfound',
                    'overwrite',
                    'savechanges',
                    'title',
                    'titlefieldisrequired',
                    'unlinkthisfilefromblogposts?',
                    'upload',
                    'uploadfile',
                    'uploadfileexistsoverwritecancel',
                    'uploadingfiletofolder',
                    'youmustagreetothecopyrightnotice',
                ),
            ),
        );
        return $jsstrings[$type];
    }

    public static function jshelp($type) {
        static $jshelp = array(
            'file' => array(
                'artefact.file' => array(
                    'cancelfolder',
                    'cancel',
                    'createfolder',
                    'delete',
                    'description',
                    'edit',
                    'folderdescription',
                    'name',
                    'notice',
                    'quota_message',
                    'title',
                    'uploadfile',
                    'tags',
                ),
            ),
        );
        return $jshelp[$type];
    }


    /**
     * Resyncs the allowed filetypes list with the XML configuration file.
     *
     * This can be called on install (and is, in the postinst method above),
     * and every time an upgrade is made that changes the file.
     */
    function resync_filetype_list() {
        require_once('xmlize.php');
        db_begin();
        log_info('Beginning resync of filetype list');

        $currentlist = get_column('artefact_file_file_types', 'description');
        $newlist     = xmlize(file_get_contents(get_config('docroot') . 'artefact/file/filetypes.xml'));
        $filetypes = $newlist['filetypes']['#']['filetype'];
        $newfiletypes = array();

        // Step one: if a filetype is in the new list that is not in the current
        // list, add it to the current list.
        foreach ($filetypes as $filetype) {
            $type = $filetype['#']['description'][0]['#'];
            if (!in_array($type, $currentlist)) {
                log_debug('Adding filetype: ' . $type);
                $currentlist[] = $type;
                $record = new StdClass;
                $record->description = $type;
                $record->enabled     = $filetype['#']['enabled'][0]['#'];
                insert_record('artefact_file_file_types', $record);
            }
            $newfiletypes[] = $type;
        }

        // Step two: If a filetype is in the current list that is not in the
        // new list, remove it from the current list.
        foreach ($currentlist as $key => $type) {
            if (!in_array($type, $newfiletypes)) {
                log_debug('Removing filetype: ' . $type);
                unset($currentlist[$key]);
                delete_records('artefact_file_mime_types', 'description', $type);
                delete_records('artefact_file_file_types', 'description', $type);
            }
        }


        // Get a list of all current mimetypes for each file type
        $currentmimetypes = array();
        $dbmimetypes = get_records_array('artefact_file_mime_types');
        if ($dbmimetypes) {
            foreach ($dbmimetypes as $mimetype) {
                $currentmimetypes[$mimetype->description][] = $mimetype->mimetype;
            }
        }
        unset($dbmimetypes);

        // Step three: For each filetype in the current list, update the mime
        // types allowed for it if necessary
        foreach ($currentlist as $description) {
            // Get the new mime types
            $newmimetypes = array();
            foreach ($filetypes as $filetype) {
                if ($filetype['#']['description'][0]['#'] == $description) {
                    foreach ($filetype['#']['mimetypes'][0]['#']['mimetype'] as $mimetype) {
                        $newmimetypes[] = $mimetype['#'];
                    }
                }
            }

            // Roll up roll up to see the famous array_equals implementation!
            // You'd think PHP would have a way to do this, but I couldn't find
            // it...
            sort($newmimetypes);
            if (isset($currentmimetypes[$description])) {
                sort($currentmimetypes[$description]);
            }

            if ((!isset($currentmimetypes[$description]) && $newmimetypes)
                || ((join('', $currentmimetypes[$description]) != join('', $newmimetypes)))) {
                log_debug('Need to update mime types for ' . $description);
                delete_records('artefact_file_mime_types', 'description', $description);
                foreach ($newmimetypes as $newmimetype) {
                    $record = new StdClass;
                    $record->mimetype    = $newmimetype;
                    $record->description = $description;
                    insert_record('artefact_file_mime_types', $record);
                }
            }
        }
       
        db_commit();
        //db_rollback();
    }

}

abstract class ArtefactTypeFileBase extends ArtefactType {

    protected $adminfiles = 0;
    protected $size;
    // The original filename extension (when the file is first
    // uploaded) is saved here.  This is used as a workaround for IE's
    // detecting filetypes by extension: when the file is downloaded,
    // the extension can be appended to the name if it's not there
    // already.
    protected $oldextension;

    public function __construct($id = 0, $data = null) {
        parent::__construct($id, $data);
        
        if (empty($this->id)) {
            $this->locked = 0;
        }

        if ($this->id && ($filedata = get_record('artefact_file_files', 'artefact', $this->id))) {
            foreach($filedata as $name => $value) {
                if (property_exists($this, $name)) {
                    $this->set($name, $value);
                }
            }
        }

    }

    public function render_self($options) {
        $options['id'] = $this->get('id');

        $downloadpath = get_config('wwwroot') . 'artefact/file/download.php?file=' . $this->get('id');
        if (isset($options['viewid'])) {
            $downloadpath .= '&view=' . $options['viewid'];
        }
        $filetype = get_string($this->get('oldextension'), 'artefact.file');
        if (substr($filetype, 0, 2) == '[[') {
            $filetype = $this->get('oldextension') . ' ' . get_string('file', 'artefact.file');
        }

        $smarty = smarty_core();
        $smarty->assign('iconpath', $this->get_icon($options));
        $smarty->assign('downloadpath', $downloadpath);
        $smarty->assign('filetype', $filetype);
        $smarty->assign('owner', display_name($this->get('owner')));
        $smarty->assign('created', strftime(get_string('strftimedaydatetime'), $this->get('ctime')));
        $smarty->assign('modified', strftime(get_string('strftimedaydatetime'), $this->get('mtime')));
        $smarty->assign('size', $this->describe_size() . ' (' . $this->get('size') . ' ' . get_string('bytes', 'artefact.file') . ')');

        foreach (array('title', 'description', 'artefacttype') as $field) {
            $smarty->assign($field, $this->get($field));
        }

        return array('html' => $smarty->fetch('artefact:file:file_render_self.tpl'), 'javascript' => '');
    }

    /**
     * This function updates or inserts the artefact.  This involves putting
     * some data in the artefact table (handled by parent::commit()), and then
     * some data in the artefact_file_files table.
     */
    public function commit() {
        // Just forget the whole thing when we're clean.
        if (empty($this->dirty)) {
            return;
        }
      
        // We need to keep track of newness before and after.
        $new = empty($this->id);

        $this->mtime = time();

        // Commit to the artefact table.
        parent::commit();

        // Reset dirtyness for the time being.
        $this->dirty = true;

        $data = (object)array(
            'artefact'      => $this->get('id'),
            'size'          => $this->get('size'),
            'adminfiles'    => $this->get('adminfiles'),
            'oldextension'  => $this->get('oldextension')
        );

        if ($new) {
            insert_record('artefact_file_files', $data);
        }
        else {
            update_record('artefact_file_files', $data, 'artefact');
        }

        $this->dirty = false;
    }

    public static function is_singular() {
        return false;
    }

    public static function get_icon($options=null) {

    }

    public static function collapse_config() {
        return 'file';
    }

    public function move($newparentid) {
        $this->set('parent', $newparentid);
        $this->commit();
        return true;
    }

    public function delete() {
        if (empty($this->id)) {
            return; 
        }
        try {
            delete_records('artefact_blog_blogpost_file', 'file', $this->id);
        } 
        catch ( Exception $e ) {}
        delete_records('artefact_file_files', 'artefact', $this->id);
        parent::delete();
    }

    // Check if something exists in the db with a given title and parent,
    // either in adminfiles or with a specific owner.
    public static function file_exists($title, $owner, $folder, $adminfiles=false) {
        $filetypesql = "('" . join("','", PluginArtefactFile::get_artefact_types()) . "')";
        return get_field_sql('SELECT a.id FROM {artefact} a
            LEFT OUTER JOIN {artefact_file_files} f ON f.artefact = a.id
            WHERE ' . ($adminfiles ? 'f.adminfiles = 1' : 'f.adminfiles <> 1 AND a.owner = ' . $owner) . '
            AND a.title = ?
            AND a.parent ' . (empty($folder) ? ' IS NULL' : ' = ' . $folder) . '
            AND a.artefacttype IN ' . $filetypesql, array($title));
    }


    // Sort folders before files; then use nat sort order.
    public static function my_files_cmp($a, $b) {
        return strnatcasecmp((int)($a->artefacttype != 'folder') . $a->title,
                             (int)($b->artefacttype != 'folder') . $b->title);
    }


    public static function get_my_files_data($parentfolderid, $userid, $adminfiles=false) {

        $foldersql = $parentfolderid ? ' = ' . $parentfolderid : ' IS NULL';

        // if blogs are installed then also return the number of blog
        // posts each file is attached to
        $bloginstalled = !$adminfiles && get_field('artefact_installed', 'active', 'name', 'blog');

        $filetypesql = "('" . join("','", PluginArtefactFile::get_artefact_types()) . "')";
        $filedata = get_records_sql_array('
            SELECT
                a.id, a.artefacttype, a.mtime, f.size, a.title, a.description,
                COUNT(c.id) AS childcount ' 
                . ($bloginstalled ? ', COUNT (b.blogpost) AS attachcount' : '') . '
            FROM {artefact} a
                LEFT OUTER JOIN {artefact_file_files} f ON f.artefact = a.id
                LEFT OUTER JOIN {artefact} c ON c.parent = a.id '
                . ($bloginstalled ? ('LEFT OUTER JOIN
                                     {artefact_blog_blogpost_file} b ON b.file = a.id') : '') . '
            WHERE a.parent' . $foldersql . '
                AND ' . ($adminfiles ? 'f.adminfiles = 1' : ('f.adminfiles = 0 AND a.owner = ' . $userid)) . '
                AND a.artefacttype IN ' . $filetypesql . '
            GROUP BY
                1, 2, 3, 4, 5, 6;', '');

        if (!$filedata) {
            $filedata = array();
        }
        else {
            foreach ($filedata as $item) {
                $item->mtime = format_date(strtotime($item->mtime), 'strfdaymonthyearshort');
                $item->tags = get_column('artefact_tag', 'tag', 'artefact', $item->id);
                if (!is_array($item->tags)) {
                    $item->tags = array();
                }
            }
        }

        // Add parent folder to the list
        if (!empty($parentfolderid)) {
            $grandparentid = get_field('artefact', 'parent', 'id', $parentfolderid);
            $filedata[] = (object) array(
                'title'        => '..',
                'artefacttype' => 'folder',
                'description'  => get_string('parentfolder', 'artefact.file'),
                'isparent'     => true,
                'id'           => (int) $grandparentid
            );
        }

        usort($filedata, array("ArtefactTypeFileBase", "my_files_cmp"));
        return $filedata;
    }
}

class ArtefactTypeFile extends ArtefactTypeFileBase {

    public function __construct($id = 0, $data = null) {
        parent::__construct($id, $data);
        
        if (empty($this->id)) {
            $this->container = 0;
        }

    }

    public static function get_file_directory($id) {
        return "artefact/file/originals/" . ($id % 256);
    }

    public function get_path() {
        return get_config('dataroot') . self::get_file_directory($this->id) . '/' .  $this->id;
    }

    public static function detect_artefact_type($file) {
        require_once('file.php');
        if (ArtefactTypeImage::is_image_mime_type(get_mime_type(get_config('dataroot') . $file))) {
            return 'image';
        }
        return 'file';
    }

    /**
     * Test file type and return a new Image or File.
     */
    public static function new_file($path, $data) {
        require_once('file.php');
        $type = get_mime_type($path);
        if (ArtefactTypeImage::is_image_mime_type($type)) {
            list($data->width, $data->height) = getimagesize($path);
            return new ArtefactTypeImage(0, $data);
        }
        return new ArtefactTypeFile(0, $data);
    }

    /**
     * Moves a file into the myfiles area.
     * Takes the name of a file outside the myfiles area.
     * Returns a boolean indicating success or failure.
     */
    public static function save_file($pathname, $data) {
        // This is only used when blog posts are saved: Files which
        // have been uploaded to the post are moved to a permanent
        // location in the files area using this function. 
        $dataroot = get_config('dataroot');
        $pathname = $dataroot . $pathname;
        if (!$size = filesize($pathname)) {
            return false;
        }
        $f = self::new_file($pathname, $data);
        $f->set('size', $size);
        $f->commit();
        $id = $f->get('id');

        $newdir = $dataroot . self::get_file_directory($id);
        check_dir_exists($newdir);
        $newname = $newdir . '/' . $id;
        if (!rename($pathname, $newname)) {
            $f->delete();
            return false;
        }
        global $USER;
        $USER->quota_add($size);
        $USER->commit();
        return $id;
    }

    /**
     * Processes a newly uploaded file, copies it to disk, and creates
     * a new artefact object.
     * Takes the name of a file input.
     * Returns false for no errors, or a string describing the error.
     */
    public static function save_uploaded_file($inputname, $data) {
        require_once('uploadmanager.php');
        $um = new upload_manager($inputname);
        if ($error = $um->preprocess_file()) {
            return $error;
        }
        $size = $um->file['size'];
        global $USER;
        if (!$USER->quota_allowed($size) && !$data->adminfiles) {
            return get_string('uploadexceedsquota');
        }
        $f = self::new_file($um->file['tmp_name'], $data);
        $f->set('owner', $USER->id);
        $f->set('size', $size);
        $f->set('oldextension', $um->original_filename_extension());
        $f->commit();
        $id = $f->get('id');
        // Save the file using its id as the filename, and use its id modulo
        // the number of subdirectories as the directory name.
        if ($error = $um->save_file(self::get_file_directory($id) , $id)) {
            $f->delete();
        }
        else {
            $USER->quota_add($size);
            $USER->commit();
        }
        return $error;
    }


    // Return the title with the original extension appended to it if
    // it's not already there.
    public function download_title() {
        $extn = $this->get('oldextension');
        $name = $this->get('title');
        if (substr($name, -1-strlen($extn)) == '.' . $extn) {
            return $name;
        }
        return $name . (substr($name, -1) == '.' ? '' : '.') . $extn;
    }


    public static function get_admin_files($public) {
        $pubfolder = ArtefactTypeFolder::admin_public_folder_id();
        if ($public) {
            $foldersql = ' a.parent = ' . $pubfolder;
        }
        else {
            $foldersql = ' (a.parent = ' . $pubfolder . ' OR a.parent IS NULL) ';
        }
        return get_records_sql_array('
            SELECT
                a.id, a.title, a.parent
            FROM {artefact} a
                INNER JOIN {artefact_file_files} f ON f.artefact = a.id
            WHERE f.adminfiles = 1 
                AND ' . $foldersql . "
                AND a.artefacttype != 'folder'", null);
    }

    public function delete() {
        if (empty($this->id)) {
            return; 
        }
        $file = $this->get_path();
        // Detach this file from any view feedback
        set_field('view_feedback', 'attachment', null, 'attachment', $this->id);
        if (is_file($file)) {
            $size = filesize($file);
            unlink($file);
            global $USER;
            // Deleting other users' files won't lower their quotas yet...
            if (!$this->adminfiles && $USER->id == $this->get('owner')) {
                $USER->quota_remove($size);
                $USER->commit();
            }
        }
        parent::delete();
    }

    public static function has_config() {
        return true;
    }

    public static function get_icon($options=null) {
        return theme_get_url('images/file.gif');
    }

    public static function get_config_options() {
        $elements = array();
        $defaultquota = get_config_plugin('artefact', 'file', 'defaultquota');
        if (empty($defaultquota)) {
            $defaultquota = 1024 * 1024 * 10;
        }
        $elements['quotafieldset'] = array(
            'type' => 'fieldset',
            'legend' => get_string('defaultquota', 'artefact.file'),
            'elements' => array(
                'defaultquotadescription' => array(
                    'value' => '<tr><td colspan="2">' . get_string('defaultquotadescription', 'artefact.file') . '</td></tr>'
                ),
                'defaultquota' => array(
                    'title'        => get_string('defaultquota', 'artefact.file'), 
                    'type'         => 'bytes',
                    'defaultvalue' => $defaultquota,
                )
            ),
            'collapsible' => true
        );


        // Allowed file types
        $filetypes = array();
        foreach (get_records_array('artefact_file_file_types', null, null, 'description') as $filetype) {
            $filetype->description = preg_replace('/[^a-zA-Z0-9_]/', '_', $filetype->description);
            $filetypes[$filetype->description] = array(
                'type'  => 'checkbox',
                'title' => get_string($filetype->description, 'artefact.file'),
                'defaultvalue' => $filetype->enabled
            );
        }
        uasort($filetypes, create_function('$a, $b', 'return $a["title"] > $b["title"];'));
        $filetypes = array_merge(array(
            'filetypedescription' => array(
                'value' => '<tr><td colspan="2">' . get_string('filetypedescription', 'artefact.file') . '</td></tr>'
            )
        ), $filetypes);
        $elements['filetypes'] = array(
            'type' => 'fieldset',
            'legend' => get_string('filetypes', 'artefact.file'),
            'elements' => $filetypes,
            'collapsible' => true,
            'collapsed' => true
        );
 
        return array(
            'elements' => $elements,
            'renderer' => 'table'
        );
    }

    public static function save_config_options($values) {
        set_config_plugin('artefact', 'file', 'defaultquota', $values['defaultquota']);
        foreach (get_records_array('artefact_file_file_types') as $filetype) {
            $key = preg_replace('/[^a-zA-Z0-9_]/', '_', $filetype->description);
            $filetype->enabled = intval($values[$key]);
            update_record('artefact_file_file_types', $filetype, 'description');
        }
    }

    public function describe_size() {
        $bytes = $this->get('size');
        if ($bytes < 1024) {
            return $bytes <= 0 ? '0' : ($bytes . ' ' . get_string('bytes', 'artefact.file'));
        }
        if ($bytes < 1048576) {
            return floor(($bytes / 1024) * 10 + 0.5) / 10 . 'K';
        }
        return floor(($bytes / 1048576) * 10 + 0.5) / 10 . 'M';
    }

    public static function get_links($id) {
        $wwwroot = get_config('wwwroot');

        return array(
            '_default' => $wwwroot . 'artefact/file/download.php?file=' . $id,
            get_string('folder', 'artefact.file') => $wwwroot . 'artefact/file/?folder=' . $id,
        );
    }

    public function override_content_type() {
        static $extensions;
        if (empty($extensions)) {
            $extensions = array(
                'wmv' => 'video/x-ms-wmv',
                'flv' => 'video/x-flv',
            );
        }
        if (array_key_exists($this->get('oldextension'), $extensions)) {
            return $extensions[$this->get('oldextension')];
        }
        return false;
    }
}

class ArtefactTypeFolder extends ArtefactTypeFileBase {

    public function __construct($id = 0, $data = null) {

        parent::__construct($id, $data);

        if (empty($this->id)) {
            $this->container = 1;
            $this->size = null;
        }

    }

    public function folder_contents() {
        return get_records_array('artefact', 'parent', $this->get('id'));
    }

    public function render_self($options) {
        $smarty = smarty_core();
        $smarty->assign('title', $this->get('title'));
        $smarty->assign('description', $this->get('description'));
        $smarty->assign('viewid', $options['viewid']);
        $smarty->assign('hidetitle', isset($options['hidetitle']) ? $options['hidetitle'] : false);

        if ($childrecords = $this->folder_contents()) {
            $this->add_to_render_path($options);
            usort($childrecords, array('ArtefactTypeFileBase', 'my_files_cmp'));
            $children = array();
            foreach ($childrecords as &$child) {
                $c = artefact_instance_from_id($child->id);
                $child->title = $c->get('title');
                $child->date = format_date(strtotime($child->mtime), 'strfdaymonthyearshort');
                $child->iconsrc = call_static_method(generate_artefact_class_name($child->artefacttype), 'get_icon', array('id' => $child->id, 'viewid' => $options['viewid']));
            }
            $smarty->assign('children', $childrecords);
        }
        return array('html' => $smarty->fetch('artefact:file:folder_render_self.tpl'),
                     'javascript' => null);
    }

    public function describe_size() {
        return $this->count_children() . ' ' . get_string('files', 'artefact.file');
    }

    public static function get_icon($options=null) {
        return theme_get_url('images/folder.gif');
    }

    public static function collapse_config() {
        return 'file';
    }
    
    public static function admin_public_folder_id() {
        $name = get_string('adminpublicdirname', 'admin');
        $folderid = get_field_sql('
           SELECT
             a.id
           FROM {artefact} a
             INNER JOIN {artefact_file_files} f ON a.id = f.artefact
           WHERE a.title = ?
             AND a.artefacttype = ?
             AND f.adminfiles = 1
             AND a.parent IS NULL', array($name, 'folder'));
        if (!$folderid) {
            global $USER;
            if (get_field('usr', 'admin', 'id', $USER->id)) {
                $description = get_string('adminpublicdirdescription', 'admin');
                $data = (object) array('title' => $name,
                                       'description' => $description,
                                       'owner' => $USER->id,
                                       'adminfiles' => 1);
                $f = new ArtefactTypeFolder(0, $data);
                $f->commit();
                $folderid = $f->get('id');
            } else {
                return false;
            }
        }
        return $folderid;
    }

    public static function get_folder_by_name($name, $parentfolderid=null, $userid=null) {
        if (empty($userid)) {
            global $USER;
            $userid = $USER->id;
        }
        $parentclause = $parentfolderid ? 'parent = ' . $parentfolderid : 'parent IS NULL';
        return get_record_sql('SELECT * FROM {artefact}
           WHERE title = ? AND ' . $parentclause . ' AND owner = ' . $userid . "
           AND artefacttype = 'folder'", array($name));
    }

    // Get the id of a folder, creating the folder if necessary
    public static function get_folder_id($name, $description, $parentfolderid=null, $userid=null, $create=true) {
        if (empty($userid)) {
            global $USER;
            $userid = $USER->id;
        }
        if (!$record = self::get_folder_by_name($name, $parentfolderid, $userid)) {
            if (!$create) {
                return false;
            }
            $data = new StdClass;
            $data->title = $name;
            $data->description = $description;
            $f = new ArtefactTypeFolder(0, $data);
            $f->set('owner', $userid);
            $f->set('parent', $parentfolderid);
            $f->commit();
            return $f->get('id');
        }
        return $record->id;
    }

    public static function get_links($id) {
        $wwwroot = get_config('wwwroot');

        return array(
            '_default' => $wwwroot . 'artefact/file/?folder=' . $id,
        );
    }

    public static function change_language($userid, $oldlang, $newlang) {
        $oldname = get_string_from_language($oldlang, 'feedbackattachdirname', 'view');
        $artefact = ArtefactTypeFolder::get_folder_by_name($oldname, null, $userid);
        if (empty($artefact)) {
            return;
        }

        $name = get_string_from_language($newlang, 'feedbackattachdirname', 'view');
        $description = get_string_from_language($newlang, 'feedbackattachdirdesc', 'view');
        if (!empty($name)) {
            $artefact = artefact_instance_from_id($artefact->id);
            $artefact->set('title', $name);
            $artefact->set('description', $description);
            $artefact->commit();
        }
    }

}

class ArtefactTypeImage extends ArtefactTypeFile {

    protected $width;
    protected $height;

    public function __construct($id = 0, $data = null) {
        parent::__construct($id, $data);

        if ($this->id && ($filedata = get_record('artefact_file_image', 'artefact', $this->id))) {
            foreach($filedata as $name => $value) {
                if (property_exists($this, $name)) {
                    $this->set($name, $value);
                }
            }
        }
    }

    /**
     * This function updates or inserts the artefact.  This involves putting
     * some data in the artefact table (handled by parent::commit()), and then
     * some data in the artefact_file_image table.
     */
    public function commit() {
        // Just forget the whole thing when we're clean.
        if (empty($this->dirty)) {
            return;
        }
      
        // We need to keep track of newness before and after.
        $new = empty($this->id);

        $this->mtime = time();

        // Commit to the artefact table.
        parent::commit();

        // Reset dirtyness for the time being.
        $this->dirty = true;

        $data = (object)array(
            'artefact'      => $this->get('id'),
            'width'         => $this->get('width'),
            'height'        => $this->get('height')
        );

        if ($new) {
            insert_record('artefact_file_image', $data);
        }
        else {
            update_record('artefact_file_image', $data, 'artefact');
        }

        $this->dirty = false;
    }

    public static function collapse_config() {
        return 'file';
   } 

    /**
     * err... wtf. Let's find where this method is called and change the call eh?
     */
    public static function is_image_mime_type($type) {
        require_once('file.php');
        return is_image_mime_type($type);
    }

    public static function get_icon($options=null) {
        $url = get_config('wwwroot') . 'artefact/file/download.php?';
        $url .= 'file=' . $options['id'];

        if (isset($options['viewid'])) {
            $url .= '&view=' . $options['viewid'];
        }
        if (isset($options['size'])) {
            $url .= '&size=' . $options['size'];
        }
        else {
            $url .= '&size=20x20';
        }

        return $url;
    }

    public function get_path($data=array()) {
        require_once('file.php');
        $size = (isset($data['size'])) ? $data['size'] : null;
        $result = get_dataroot_image_path('artefact/file/', $this->id, $size);
        return $result;
    }

    public function delete() {
        if (empty($this->id)) {
            return; 
        }
        delete_records('artefact_file_image', 'artefact', $this->id);
        parent::delete();
    }

    public function render_self($options) {
        $result = parent::render_self($options);
        $result['html'] = '<div class="fr filedata-icon" style="text-align: center;"><h4>' . get_string('Preview', 'artefact.file') . '</h4><img src="'
            . hsc(get_config('wwwroot') . 'artefact/file/download.php?file=' . $this->id . '&view=' . $options['viewid'] . '&maxwidth=400')
            . '" alt=""></div>' . $result['html'];
        return $result;
    }
}

?>
