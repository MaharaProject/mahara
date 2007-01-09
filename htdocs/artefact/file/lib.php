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

    public static function get_plugin_name() {
        return 'file';
    }

    public static function menu_items() {
        return array(
            array(
                'name' => 'myfiles',
                'link' => '',
            )
        );
    }
    
    public static function get_toplevel_artefact_types() {
        return array('file');
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

    public static function jsstrings($type) {
        static $jsstrings = array(
            'file' => array(
                'mahara' => array(
                    'cancel',
                    'delete',
                    'edit',
                ),
                'artefact.file' => array(
                    'copyrightnotice',
                    'create',
                    'createfolder',
                    'deletefile?',
                    'deletefolder?',
                    'description',
                    'description',
                    'destination',
                    'editfile',
                    'editfolder',
                    'file',
                    'fileexistsoverwritecancel',
                    'filenamefieldisrequired',
                    'home',
                    'name',
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

}

class ArtefactTypeFileBase extends ArtefactType {

    protected $adminfiles;
    protected $size;

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
            'adminfiles'    => $this->get('adminfiles')
        );

        if ($new) {
            insert_record('artefact_file_files', $data);
        }
        else {
            update_record('artefact_file_files', $data, 'artefact');
        }

        $this->dirty = false;
    }

    public static function get_render_list() {
        return array(FORMAT_ARTEFACT_LISTSELF, FORMAT_ARTEFACT_RENDERMETADATA);
    }

    public static function is_singular() {
        return false;
    }

    public function get_icon() {

    }

    public static function collapse_config() {
        return 'file';
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
        $prefix = get_config('dbprefix');
        $filetypesql = "('" . join("','", PluginArtefactFile::get_artefact_types()) . "')";
        return get_field_sql('SELECT a.id FROM ' . $prefix . 'artefact a
            LEFT OUTER JOIN ' . $prefix . 'artefact_file_files f ON f.artefact = a.id
            WHERE ' . ($adminfiles ? 'f.adminfiles = 1' : 'f.adminfiles <> 1 AND a.owner = ' . $owner) . '
            AND a.title = ?
            AND a.parent ' . (empty($folder) ? ' IS NULL' : ' = ' . $folder) . '
            AND a.artefacttype IN ' . $filetypesql, array($title));
    }

    public static function get_my_files_data($parentfolderid, $userid, $adminfiles=false) {

        $prefix = get_config('dbprefix');

        $foldersql = $parentfolderid ? ' = ' . $parentfolderid : ' IS NULL';

        // if blogs are installed then also return the number of blog
        // posts each file is attached to
        $bloginstalled = !$adminfiles && get_field('artefact_installed', 'active', 'name', 'blog');

        $filetypesql = "('" . join("','", PluginArtefactFile::get_artefact_types()) . "')";
        $filedata = get_records_sql_array('
            SELECT
                a.id, a.artefacttype, a.mtime, f.size, a.title, a.description,
                COUNT(c.*) AS childcount ' 
                . ($bloginstalled ? ', COUNT (b.*) AS attachcount' : '') . '
            FROM ' . $prefix . 'artefact a
                LEFT OUTER JOIN ' . $prefix . 'artefact_file_files f ON f.artefact = a.id
                LEFT OUTER JOIN ' . $prefix . 'artefact c ON c.parent = a.id '
                . ($bloginstalled ? ('LEFT OUTER JOIN ' . $prefix .
                                     'artefact_blog_blogpost_file b ON b.file = a.id') : '') . '
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
                $item->mtime = strftime(get_string('strfdaymonthyearshort'),strtotime($item->mtime));
            }
        }

        // Sort folders before files; then use nat sort order on title.
        function fileobjcmp ($a, $b) {
            return strnatcasecmp(($a->artefacttype == 'folder') . $a->title,
                                 ($b->artefacttype == 'folder') . $b->title);
        }
        usort($filedata, "fileobjcmp");
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


    // Where to store files under dataroot in the filesystem
    static $artefactfileroot = 'artefact/file/';

    // Number of subdirectories to create under $artefactfileroot (should be configurable).
    static $artefactfilesubdirs = 256;

    private static function get_file_directory($id) {
        return self::$artefactfileroot . $id % self::$artefactfilesubdirs;
    }

    public function get_path() {
        return get_config('dataroot') . self::get_file_directory($this->id) . '/' .  $this->id;
    }



    /**
     * Test file type and return a new Image or File.
     */
    public static function new_file($path, $data) {
        //require_once('file.php');
        //$type = get_mime_type($path);
        $type = 'foo';
        if (ArtefactTypeImage::is_image_mime_type($type)) {
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
        $f = self::new_file($um->file['tmp_name'], $data);
        global $USER;
        $f->set('owner', $USER->get('id'));
        $f->set('size', $um->file['size']);
        $f->commit();
        $id = $f->get('id');
        // Save the file using its id as the filename, and use its id modulo
        // the number of subdirectories as the directory name.
        if ($error = $um->save_file(self::get_file_directory($id) , $id)) {
            $f->delete();
        }
        return $error;
    }


    public function delete() {
        if (empty($this->id)) {
            return; 
        }
        unlink($this->get_path());
        parent::delete();
    }

    public static function has_config() {
        return true;
    }

    public function get_icon() {

    }

    public static function get_config_options() {
        return array(); // @todo  
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

    public function render($format, $options) {
        if ($format == FORMAT_ARTEFACT_RENDERFULL) {
            return $this->title;
        }
        if ($format == FORMAT_ARTEFACT_LISTCHILDREN) {
            return $this->listchildren($options);
        }
        return parent::render($format, $options);
    }

    public function get_icon() {

    }

    public static function collapse_config() {
        return 'file';
    }
    
    public static function get_render_list() {
        return array(FORMAT_ARTEFACT_LISTSELF, FORMAT_ARTEFACT_LISTCHILDREN,
                     FORMAT_ARTEFACT_RENDERFULL, FORMAT_ARTEFACT_RENDERMETADATA);
    }
    
    public static function get_folder_by_name($name, $parentfolderid=null) {
        global $USER;
        $prefix = get_config('dbprefix');
        $parentclause = $parentfolderid ? 'parent = ' . $parentfolderid : 'parent IS NULL';
        return get_record_sql('SELECT * FROM ' . $prefix . 'artefact
           WHERE title = ? AND ' . $parentclause . ' AND owner = ' . $USER->get('id') . "
           AND artefacttype = 'folder'", array($name));
    }

    // Get the id of a folder, creating the folder if necessary
    public static function get_folder_id($name, $description, $parentfolderid=null) {
        global $USER;
        if (!$record = self::get_folder_by_name($name, $parentfolderid)) {
            $data = new StdClass;
            $data->title = $name;
            $data->description = $description;
            $f = new ArtefactTypeFolder(0, $data);
            $f->set('owner', $USER->get('id'));
            $f->set('parent', $parentfolderid);
            $f->commit();
            return $f->get('id');
        }
        return $record->id;
    }

}

class ArtefactTypeImage extends ArtefactTypeFile {
    
    public static function collapse_config() {
        return 'file';
    }

    public function render($format, $options) {
        if ($format == FORMAT_ARTEFACT_RENDERFULL) {
            return 'render image ' . $this->title . ' here';
        }
        return parent::render($format, $options);
    }

    public static function get_render_list() {
        return array(FORMAT_ARTEFACT_LISTSELF, FORMAT_ARTEFACT_RENDERFULL, 
                     FORMAT_ARTEFACT_RENDERMETADATA);
    }

    public static function is_image_mime_type($type) {
        return in_array($type, array('image/jpeg', 'image/jpg', 'image/gif', 'image/png'));
    }

}

?>
