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
            'uploader' => array(
                'cancel' => 'mahara',
                'copyrightnotice' => 'artefact.file',
                'description' => 'artefact.file',
                'destination' => 'artefact.file',
                'file' => 'artefact.file',
                'filenamefieldisrequired' => 'artefact.file',
                'home' => 'artefact.file',
                'overwrite' => 'artefact.file',
                'title' => 'artefact.file',
                'titlefieldisrequired' => 'artefact.file',
                'upload' => 'artefact.file',
                'uploadcomplete' => 'artefact.file',
                'uploadfailed' => 'artefact.file',
                'uploadfile' => 'artefact.file',
                'uploadfileexistsoverwritecancel' => 'artefact.file',
                'uploading' => 'artefact.file',
                'youmustagreetothecopyrightnotice' => 'artefact.file',
            ),
            'filebrowser' => array(
                'cancel' => 'mahara',
                'create' => 'artefact.file',
                'createfolder' => 'artefact.file',
                'delete' => 'mahara',
                'deletefile?' => 'artefact.file',
                'deletefolder?' => 'artefact.file',
                'description' => 'artefact.file',
                'destination' => 'artefact.file',
                'edit' => 'mahara',
                'editfile' => 'artefact.file',
                'editfolder' => 'artefact.file',
                'fileexistsoverwritecancel' => 'artefact.file',
                'home' => 'artefact.file',
                'name' => 'artefact.file',
                'namefieldisrequired' => 'artefact.file',
                'nofilesfound' => 'artefact.file',
                'overwrite' => 'artefact.file',
                'savechanges' => 'artefact.file',
            )
        );
        return $jsstrings[$type];
    }

}

class ArtefactTypeFileBase extends ArtefactType {

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
        delete_records('artefact_file_files', 'artefact', $this->id);
        parent::delete();
    }

    // Check if something exists in the db with a given title, owner, parent folder
    public static function exists_in_db($title, $owner, $folder) {
        $prefix = get_config('dbprefix');
        $parentsql = empty($folder) ? 'parent IS NULL' : 'parent = ' . $folder;
        $filetypesql = "('" . join("','", PluginArtefactFile::get_artefact_types()) . "')";
        return get_field_sql('SELECT id FROM ' . $prefix . 'artefact
            WHERE owner = ' . $owner . '
            AND title = ?
            AND ' . $parentsql . '
            AND artefacttype IN ' . $filetypesql, array($title));
    }

    public static function get_my_files_data($parentfolderid, $userid) {

        // @todo: add an 'emptyfolder => true' field for empty folders

        // @todo: add an 'attachedtoblogpost => true' field for files attached to blog posts.

        if ($parentfolderid) {
            $foldersql = ' = ' . $parentfolderid;
        }
        else {
            $foldersql = ' IS NULL';
        }
        $filetypesql = "('" . join("','", PluginArtefactFile::get_artefact_types()) . "')";
        $prefix = get_config('dbprefix');
        $filedata = get_records_sql_array('SELECT a.id, a.artefacttype, a.mtime, f.size, a.title, a.description
            FROM ' . $prefix . 'artefact a
            LEFT OUTER JOIN ' . $prefix . 'artefact_file_files f ON f.artefact = a.id
            WHERE a.owner = ' . $userid . '
            AND a.parent' . $foldersql . "
            AND a.artefacttype IN " . $filetypesql, '');

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

    protected $size;

    public function __construct($id = 0, $data = null) {
        parent::__construct($id, $data);
        
        // So far the only thing in the artefact_file_files table is the file size
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
            'size'          => $this->get('size')
        );

        if ($new) {
            insert_record('artefact_file_files', $data);
        }
        else {
            update_record('artefact_file_files', $data, 'artefact');
        }

        $this->dirty = false;
    }

    // Where to store files under dataroot in the filesystem
    static $artefactfileroot = 'artefact/file/';

    // Number of subdirectories to create under $artefactfileroot
    static $artefactfilesubdirs = 256;

    private static function get_file_directory($id) {
        return self::$artefactfileroot . $id % self::$artefactfilesubdirs;
    }

    public function get_path() {
        return get_config('dataroot') . self::get_file_directory($this->id) . '/' .  $this->id;
    }


    /**
     * Processes a newly uploaded file, copies it to disk, and associates it with
     * the artefact object.
     * Takes the name of a file input.
     * Returns a boolean indicating success or failure.
     */
    public function save_uploaded_file($inputname) {
        require_once('uploadmanager.php');
        $um = new upload_manager($inputname);
        if (!$um->preprocess_file()) {
            return false;
        }
        $this->size = $um->file['size'];
        $this->dirty = true;
        $this->commit();
        // Save the file using its id as the filename, and use its id modulo
        // the number of subdirectories as the directory name.
        if (!$um->save_file(self::get_file_directory($this->id) , $this->id)) {
            $this->delete();
            return false;
        }
        return true;
    }

    // Deal with this once I know about how the mime type detection will work.

//     public static function construct_from_upload($inputname, $data) {
//         require_once('uploadmanager.php');
//         $um = new upload_manager($inputname);
//         if (!$um->preprocess_file()) {
//             return false;
//         }
//     }

//     /**
//      * Processes a newly uploaded file, copies it to disk, creates a new artefact object and
//      * associates the newly uploaded file with it.
//      * Takes the name of a file input.
//      * Returns a boolean indicating success or failure.
//      */
//     public static function uploaded_file_to_artefact_file($inputname, $data) {
//         // Get the new file object first, because its id is used to
//         // determine where the file goes on the filesystem
//         $f = new ArtefactTypeFile(0, $data);
//         require_once('uploadmanager.php');
//         $um = new upload_manager($inputname);
//         if (!$um->preprocess_file()) {
//             return false;
//         }
//         $this->size = $um->file['size'];
//         $this->dirty = true;
//         $this->commit();
//         // Save the file using its id as the filename, and use its id modulo
//         // the number of subdirectories as the directory name.
//         if (!$um->save_file(self::get_file_directory($this->id) , $this->id)) {
//             $this->delete();
//             return false;
//         }
//         return true;
//     }
    
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

}

?>
