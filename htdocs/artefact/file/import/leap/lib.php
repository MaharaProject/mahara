<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-file-import-leap
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

/**
 * Implements Leap2A import of file/folder related entries into Mahara
 *
 * For more information about Leap file importing, see:
 * https://wiki.mahara.org/wiki/Developer_Area/Import//Export/LEAP_Import/File_Artefact_Plugin
 *
 * TODO:
 * - Protect get_children_of_folder against circular references
 */
class LeapImportFile extends LeapImportArtefactPlugin {

    /**
     * Import an entry as a file
     */
    const STRATEGY_IMPORT_AS_FILE = 1;

    /**
     * Import an entry as a folder, using any children folders and files
     */
    const STRATEGY_IMPORT_AS_FOLDER = 2;

    public static function get_import_strategies_for_entry(SimpleXMLElement $entry, PluginImportLeap $importer) {
        $strategies = array();

        if (!self::has_parent_folder($entry, $importer)) {
            if (self::is_file($entry, $importer)) {
                // We import these into the top level directory of a user's 'My
                // Files' area
                $strategies[] = array(
                    'strategy' => self::STRATEGY_IMPORT_AS_FILE,
                    'score'    => 110, // attached files need to imported before their parent artefact so need higher score
                    'other_required_entries' => array(),
                );
            }
            else if (self::is_folder($entry, $importer)) {
                // It's a folder with no parent. We import these into the top level
                // directory, using all the files/folders under it to do so
                $strategies[] = array(
                    'strategy' => self::STRATEGY_IMPORT_AS_FOLDER,
                    'score'    => 100,
                    'other_required_entries' => self::get_children_of_folder($entry, $importer, true),
                );
            }
        }

        return $strategies;
    }

    public static function add_import_entry_request_using_strategy(SimpleXMLElement $entry, PluginImportLeap $importer, $strategy, array $otherentries) {
        $importid = $importer->get('importertransport')->get('importid');
        switch ($strategy) {
        case self::STRATEGY_IMPORT_AS_FILE:
            self::add_import_entry_request_file($entry, $importer);
            break;
        case self::STRATEGY_IMPORT_AS_FOLDER:
            self::add_import_entry_request_folder_and_children($entry, $importer);
            break;
        default:
            throw new ImportException($importer, 'TODO: get_string: unknown strategy chosen for importing entry');
        }
    }

/**
 * Import from entry requests for Mahara files/folders
 *
 * @param PluginImportLeap $importer
 * @return updated DB
 * @throw    ImportException
 */
    public static function import_from_requests(PluginImportLeap $importer) {
        $importid = $importer->get('importertransport')->get('importid');
        // Create a folder for import files/folders
        $folder = new ArtefactTypeFolder(0, (object) array(
            'owner' => $importer->get('usr'),
            'title' => get_string('importfolder', 'import', db_format_timestamp(time())),
        ));
        $folder->commit();
        $importfolderid = $folder->get('id');
        if ($entry_requests = get_records_select_array('import_entry_requests', 'importid = ? AND entrytype = ? AND entryparent IS NULL', array($importid, 'file'))) {
            foreach ($entry_requests as $entry_request) {
                self::create_file_from_request($importer, $entry_request, $importfolderid);
            }
        }
        if ($entry_requests = get_records_select_array('import_entry_requests', 'importid = ? AND entrytype = ? AND entryparent IS NULL', array($importid, 'folder'))) {
            foreach ($entry_requests as $entry_request) {
                self::import_folder_and_children_from_request($importer, $entry_request, $importfolderid);
            }
        }
    }

    /**
     * Import a folder, and recursively, all subfolders and files under it.
     *
     * @param PluginImportLeap $importer    The importer
     * @param $entry_request    The entry request for the folder
     * @param int $parent       The ID of the parent artefact for this folder
     * @return updated DB
     */
    private static function import_folder_and_children_from_request(PluginImportLeap $importer, $entry_request, $parent=null) {
        $importid = $importer->get('importertransport')->get('importid');
        $folderid = self::create_artefact_from_request($importer, $entry_request, $parent);
        if ($subfolder_requests = get_records_select_array('import_entry_requests', 'importid = ? AND entrytype = ? AND entryparent = ?' ,
                                                            array($importid, 'folder', $entry_request->entryid))) {
            foreach ($subfolder_requests as $subfolder_request) {
                self::import_folder_and_children_from_request($importer, $subfolder_request, $folderid);
            }
        }
        if ($subfile_requests = get_records_select_array('import_entry_requests', 'importid = ? AND entrytype = ? AND entryparent = ?' ,
                                                            array($importid, 'file', $entry_request->entryid))) {
            foreach ($subfile_requests as $subfile_request) {
                self::create_file_from_request($importer, $subfile_request, $folderid);
            }
        }
    }

    // TODO: we're assuming an empty files area to work with, but that might
    // not be the case, in which case we have conflicting file/folder names to
    // deal with!
    public static function import_using_strategy(SimpleXMLElement $entry, PluginImportLeap $importer, $strategy, array $otherentries) {
        $artefactmapping = array();
        switch ($strategy) {
        case self::STRATEGY_IMPORT_AS_FILE:
            if ($file = self::create_file($entry, $importer)) {
                $artefactmapping[(string)$entry->id] = array($file->get('id'));
            }
            break;
        case self::STRATEGY_IMPORT_AS_FOLDER:
            $artefactmapping = self::create_folder_and_children($entry, $importer);
            break;
        default:
            throw new ImportException($importer, 'TODO: get_string: unknown strategy chosen for importing entry');
        }
        return $artefactmapping;
    }

    /**
     * Render import entry requests for Mahara files. (Folders will be automatically added)
     * @param PluginImportLeap $importer
     * @return HTML code for displaying files and choosing how to import them
     */
    public static function render_import_entry_requests(PluginImportLeap $importer) {
        $importid = $importer->get('importertransport')->get('importid');
        // Get import entry requests for Mahara files
        $entryfiles = array();
        if ($ierfiles = get_records_select_array('import_entry_requests', 'importid = ? AND entrytype = ?', array($importid, 'file'))) {
            foreach ($ierfiles as $ierfile) {
                $file = unserialize($ierfile->entrycontent);
                $file['id'] = $ierfile->id;
                $file['decision'] = $ierfile->decision;
                $file['disabled'][PluginImport::DECISION_IGNORE] = false;
                $file['disabled'][PluginImport::DECISION_ADDNEW] = false;
                $file['disabled'][PluginImport::DECISION_APPEND] = true;
                $file['disabled'][PluginImport::DECISION_REPLACE] = true;
                $entryfiles[] = $file;
            }
        }
        $smarty = smarty_core();
        $smarty->assign('displaydecisions', $importer->get('displaydecisions'));
        $smarty->assign('entryfiles', $entryfiles);
        return $smarty->fetch('artefact:file:import/files.tpl');
    }

    /**
     * Returns whether the given entry is a file
     *
     * We consider an entry to be a file if it has its content out of line, and
     * if it's of rdf:type rdf:resource. This may be more strict than necessary
     * - possibly just having the content ouf of line should be enough.
     *
     * In the 2010-07 version of Leap2A, an entry is *also* a file, if it's in an enclosure link.
     * However, since we have to support BC, those might actually be entries too, so we have to check
     * to see if what's in there is actually something that exists as a key in the array of entry ids.
     *
     * @param SimpleXMLElement $entry    The entry to check
     * @param PluginImportLeap $importer The importer
     * @return boolean Whether the entry is a file
     */
    public static function is_file(SimpleXMLElement $entry, PluginImportLeap $importer) {
        if (PluginImportLeap::is_rdf_type($entry, $importer, 'resource')
            && isset($entry->content['src'])) {
                return true;
        }
        else {
            // go through all the links and look for enclsures
            $filesfound = 0;
            foreach ($entry->link as $link) {
                if ($importer->curie_equals($link['rel'], '', 'enclosure') && isset($link['href']) && !$importer->entry_exists((string)$link['href'])) {
                    $filesfound++;
                }
            }
            return ($filesfound == 1);
        }
        return false;
    }

    /**
     * Returns whether the given entry is a folder
     *
     * @param SimpleXMLElement $entry    The entry to check
     * @param PluginImportLeap $importer The importer
     * @return boolean Whether the entry is a folder
     */
    private static function is_folder(SimpleXMLElement $entry, PluginImportLeap $importer) {
        static $cache = array();
        $id = (string)$entry->id;
        if (isset($cache[$id])) {
            return $cache[$id];
        }
        return ($cache[$id] = PluginImportLeap::is_rdf_type($entry, $importer, 'selection')
            && PluginImportLeap::is_correct_category_scheme($entry, $importer, 'selection_type', 'Folder'));
    }

    /**
     * Returns whether the given entry considers itself "part of" a folder -
     * i.e., whether it's in a folder.
     *
     * The entry itself can be any entry, although in the context of this
     * plugin, it is a file or folder.
     *
     * @param SimpleXMLElement $entry    The entry to check
     * @param PluginImportLeap $importer The importer
     * @return boolean Whether this entry is in a folder
     */
    private static function has_parent_folder(SimpleXMLElement $entry, PluginImportLeap $importer) {
        foreach ($entry->link as $link) {
            if ($importer->curie_equals($link['rel'], $importer->get_leap2a_namespace(), 'is_part_of') && isset($link['href'])) {
                $potentialfolder = $importer->get_entry_by_id((string)$link['href']);
                if ($potentialfolder && self::is_folder($potentialfolder, $importer)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Returns a list of entry IDs that are children of this folder
     *
     * If necessary, this method can act recursively to find children at all
     * levels under this folder
     *
     * TODO: protection against circular references
     *
     * @param SimpleXMLElement $entry    The folder to get children for
     * @param PluginImportLeap $importer The importer
     * @param boolean $recurse           Whether to return children at all levels below this folder
     * @return array A list of the entry IDs of children in this folder
     */
    private static function get_children_of_folder(SimpleXMLElement $entry, PluginImportLeap $importer, $recurse=false) {
        $children = array();

        // Get entries that this folder feels are a part of it
        foreach ($entry->link as $link) {
            if ($importer->curie_equals($link['rel'], $importer->get_leap2a_namespace(), 'has_part') && isset($link['href'])) {
                $child = $importer->get_entry_by_id((string)$link['href']);
                if ($child) {
                    if (self::is_file($child, $importer) || self::is_folder($child, $importer)) {
                        $children[] = (string)$link['href'];
                    }
                    else {
                        $importer->trace("NOTICE: Child $child->id of folder $entry->id won't be imported by the file plugin because it is not a file or folder");
                    }
                }
                else {
                    $importer->trace("WARNING: folder $entry->id claims to have child $link[href] which does not exist");
                }
            }
        }

        if ($recurse) {
            foreach ($children as $childid) {
                $child = $importer->get_entry_by_id($childid);
                if (self::is_folder($child, $importer)) {
                    $children = array_merge($children, self::get_children_of_folder($child, $importer, true));
                }
            }
        }

        return $children;
    }

    private static function get_file_entry_data(SimpleXMLElement $entry, PluginImportLeap $importer, $parent=null) {
        if (!self::is_file($entry, $importer)) {
            throw new ImportException($importer, "create_file(): Cannot create a file artefact from an entry we don't recognise as a file");
        }

        // TODO: make sure there's no arbitrary file inclusion
        // TODO: the src attribute must be an IRI, according to the ATOM spec.
        // This means that it could have UTF8 characters in it, and the PHP
        // documentation doesn't sound hopeful that urldecode will work with
        // UTF8 characters
        $pathname = false;
        $description = '';
        if (isset($entry->content['src'])) {
            $pathname = urldecode((string)$entry->content['src']);
            $filetype = (string)$entry->content['type'];
        }
        else {
            foreach ($entry->link as $link) {
                if ($importer->curie_equals($link['rel'], '', 'enclosure') && isset($link['href']) && !$importer->entry_exists((string)$link['href'])) {
                    $pathname = urldecode((string)$link['href']);
                    $filetype = (string)$link['type'];
                    $description = strip_tags(PluginImportLeap::get_entry_content($entry, $importer)); // TODO do we have a better way of stripping tags? and why isn't this html anyway?
                }
            }
        }
        if (!$pathname) {
            $importer->trace("WARNING: couldn't find a file for $entry->id ");
            return;
        }
        // TODO: might want to make it easier to get at the directory where the import files are
        $dir = dirname($importer->get('filename'));

        // Note: this data is passed (eventually) to ArtefactType->__construct,
        // which calls strtotime on the dates for us
        $data = (object)array(
            'title' => (string)$entry->title,
            'description' => $description,
            'owner' => $importer->get('usr'),
            'filetype' => $filetype,
        );
        if (isset($entry->summary) && empty($description)) {
            $data->description = (string)$entry->summary;
        }
        if ($published = strtotime((string)$entry->published)) {
            $data->ctime = (string)$entry->published;
        }
        if ($updated = strtotime((string)$entry->updated)) {
            $data->mtime = (string)$entry->updated;
        }

        if ($parent) {
            $data->parent = $parent;
        }

        $data->pathname = $dir . '/' . $pathname;

        // Work out if the file was really a profile icon
        $isprofileicon = false;
        $match = $entry->xpath('mahara:artefactplugin[@mahara:plugin="file" and @mahara:type="profileicon"]');
        if (count($match) == 1) {
            $isprofileicon = true;
        }
        else if ($importer->get('persondataid')) {
            $persondata = $importer->get_entry_by_id($importer->get('persondataid'));
            if (count($persondata->xpath('a:link[@rel="related" and @href="' . (string)$entry->id . '"]')) == 1) {
                $isprofileicon = true;
            }
        }
        $data->isprofileicon = $isprofileicon;
        $data->tags = PluginImportLeap::get_entry_tags($entry);
        return $data;
    }

    /**
     * Add import entry request for a file artefact based on the given entry.
     *
     * @param SimpleXMLElement $entry    The entry to base the file's data on
     * @param PluginImportLeap $importer The importer
     * @param int $parent                The ID of the parent entry
     * @throws ImportException If the given entry is not detected as being a file
     */
    public static function add_import_entry_request_file(SimpleXMLElement $entry, PluginImportLeap $importer, $parent=null) {
        $data = self::get_file_entry_data($entry, $importer, $parent);

        $pathname = $data->pathname;
        if (file_exists($pathname)) {
            $filesize = filesize($pathname);
        }

        // Don't save full pathname to db, only the relative path to dataroot
        $pathname = preg_replace('#^' . get_config('dataroot') . '#', '', $pathname);

        // Work around that save_file doesn't let us set the mtime
        return PluginImportLeap::add_import_entry_request($importer->get('importertransport')->get('importid'), (string)$entry->id, self::STRATEGY_IMPORT_AS_FILE, 'file', array(
            'owner'   => $data->owner,
            'type'    => $data->isprofileicon ? 'profileicon' : 'file',
            'parent'  => $parent,
            'content' => array(
                'title'       => $data->title,
                'description' => $data->description,
                'ctime'       => isset($data->ctime) ? $data->ctime : null,
                'mtime'       => isset($data->mtime) ? $data->mtime : null,
                'pathname'    => $pathname,
                'filetype'    => $data->filetype,
                'filesize'    => isset($filesize) ? $filesize : null,
                'tags'        => $data->tags,
                'isprofileicon' => $data->isprofileicon,
            ),
        ));
    }

    /**
     * Creates a file artefact based on the given entry request.
     *
     * @param $entry_request    The entry request to base the file's data on
     * @param int $parent       The ID of the parent artefact
     * @throws ImportException If the given entry is not detected as being a file
     * @return ArtefactTypeFile The file artefact created
     */
    public static function create_file_from_request($importer, $entry_request, $parent=null) {
        global $USER;
        if ($entry_request->decision == PluginImport::DECISION_ADDNEW) {
            $data = (object)unserialize($entry_request->entrycontent);
            if ($parent) {
                $data->parent = $parent;
            }
            $data->owner = $entry_request->ownerid;
            $data->pathname = get_config('dataroot') . $data->pathname;
            if ($artefact = self::create_file_from_entry_data($data, $importer, $entry_request->entryid)) {
                $importer->add_artefactmapping($entry_request->entryid, $artefact->get('id'), true);
                return $artefact;
            }
        }
        return false;
    }

    /**
     * Creates a file artefact based on the given entry.
     *
     * @param SimpleXMLElement $entry    The entry to base the file's data on
     * @param PluginImportLeap $importer The importer
     * @param int $parent                The ID of the parent artefact for this file
     * @throws ImportException If the given entry is not detected as being a file
     * @return ArtefactTypeFile The file artefact created
     */
    public static function create_file(SimpleXMLElement $entry, PluginImportLeap $importer, $parent=null) {
        $data = self::get_file_entry_data($entry, $importer, $parent);
        return self::create_file_from_entry_data($data, $importer, $entry->id);
    }

    /**
     * Logic shared by create_file_from_request() and create_file(). This actually takes the data and processes it into a file artefact
     * @param object $data
     * @param PluginImportLeap $importer
     * @param int $parent
     * @param SimplexMLElement $entry
     * @param unknown_type $entry_request
     * @return PluginArtefactFile
     * @throws ImportException
     */
    private static function create_file_from_entry_data($data, PluginImportLeap $importer, $entryid, $fromrequest = false) {
        global $USER;

        if ($fromrequest) {
            $usr = $USER;
        }
        else {
            $usr = $importer->get('usrobj');
        }
        $data->oldextension = end(explode('.', $data->title));
        // This API sucks, but that's not my problem
        if (!$id = ArtefactTypeFile::save_file($data->pathname, $data, $usr, true)) {
            $importer->trace("WARNING: the file for entry $entryid does not exist in the import (path={$data->pathname})");
            return false;
        }

        $artefact = artefact_instance_from_id($id);
        $artefact->set('tags', $data->tags);

        if ($fromrequest) {
            $artefact->set('mtime', $data->mtime);
        }
        else {
            // Work around that save_file doesn't let us set the mtime
            $artefact->set('mtime', strtotime($data->mtime));
        }

        // Now that we've actually imported the file, let's check to see whether it was an image, before making it a real profile icon
        $isprofileicon = ($data->isprofileicon && ($artefact->get('artefacttype') == 'image'));
        if ($isprofileicon) {
            $artefact->set('artefacttype', 'profileicon');

            // Put profile pic in 'profile pics' folder
            $artefact->set('parent', ArtefactTypeFolder::get_folder_id(get_string('imagesdir', 'artefact.file'),
                get_string('imagesdirdesc', 'artefact.file'), null, true, $importer->get('usr')));

            // Sadly the process for creating a profile icon is a bit dumb. To
            // be honest, it shouldn't even be a separate artefact type
            $basedir = get_config('dataroot') . 'artefact/file/';
            $olddir  = 'originals/' . ($id % 256) . '/';
            $newdir  = 'profileicons/originals/' . ($id % 256) . '/';
            check_dir_exists($basedir . $newdir);
            if (!rename($basedir  . $olddir . $id, $basedir . $newdir . $id)) {
                throw new ImportException($importer, 'TODO: get_string: was unable to move profile icon');
            }
        }
        $artefact->commit();
        return $artefact;
    }

    private static function get_folder_entry_data(SimpleXMLElement $entry, PluginImportLeap $importer, $parent=null) {
        if (!self::is_folder($entry, $importer)) {
            throw new ImportException($importer, "get_folder_entry_data(): Cannot add an import entry request for a folder we don't recognise as a folder");
        }

        return array(
            'owner'   => $importer->get('usr'),
            'type'    => 'folder',
            'parent'  => $parent,
            'content' => array(
                'title'       => (string)$entry->title,
                'description' => PluginImportLeap::get_entry_content($entry, $importer),
                'ctime'       => (string)$entry->published,
                'mtime'       => (string)$entry->updated,
                'tags'        => PluginImportLeap::get_entry_tags($entry),
            ),
        );
    }

    /**
     * Add import entry request for a folder artefact based on the given entry.
     *
     * @param SimpleXMLElement $entry    The entry to base the folder's data on
     * @param PluginImportLeap $importer The importer
     * @param int $parent                The ID of the parent artefact for this folder
     * @throws ImportException If the given entry is not detected as being a folder
     * @return int The ID of the folder import entry request created
     */
    private static function add_import_entry_request_folder(SimpleXMLElement $entry, PluginImportLeap $importer, $parent=null) {
        $config = self::get_folder_entry_data($entry, $importer, $parent);
        return PluginImportLeap::add_import_entry_request($importer->get('importertransport')->get('importid'), (string)$entry->id, self::STRATEGY_IMPORT_AS_FOLDER, 'file', $config);
    }

    /**
     * Creates a folder artefact based on the given entry.
     *
     * @param SimpleXMLElement $entry    The entry to base the folder's data on
     * @param PluginImportLeap $importer The importer
     * @param int $parent                The ID of the parent artefact for this folder
     * @throws ImportException If the given entry is not detected as being a folder
     * @return int The ID of the folder artefact created
     */
    private static function create_folder(SimpleXMLElement $entry, PluginImportLeap $importer, $parent=null) {
        $config = self::get_folder_entry_data($entry, $importer, $parent);

        $folder = new ArtefactTypeFolder();
        $folder->set('title', $config['content']['title']);
        $folder->set('description', $config['content']['description']);
        if ($config['content']['ctime']) {
            $folder->set('ctime', $config['content']['ctime']);
        }
        if ($config['content']['mtime']) {
            $folder->set('mtime', $config['content']['mtime']);
        }
        $folder->set('owner', $config['owner']);
        $folder->set('tags', $config['content']['tags']);
        if ($config['parent']) {
            $folder->set('parent', $config['parent']);
        }
        $folder->commit();
        return $folder->get('id');
    }

    /**
     * Add import entry request for a folder, and recursively, all folders and files under it.
     * TODO: Refactor this to combine it with create_folder_and_children()
     *
     * @param SimpleXMLElement $entry    The entry to base the folder's data on
     * @param PluginImportLeap $importer The importer
     * @param int $parent                The ID of the parent entry
     * @throws ImportException If the given entry is not detected as being a folder
     */
    private static function add_import_entry_request_folder_and_children(SimpleXMLElement $entry, PluginImportLeap $importer, $parent=null) {
        if (!self::is_folder($entry, $importer)) {
            throw new ImportException($importer, "create_folder(): Cannot create a folder artefact from an entry we don't recognise as a folder");
        }

        // Add entry request for the folder
        self::add_import_entry_request_folder($entry, $importer, $parent);

        // Then create all folders/files under it
        foreach (self::get_children_of_folder($entry, $importer) as $childid) {
            $child = $importer->get_entry_by_id($childid);
            if (self::is_folder($child, $importer)) {
                self::add_import_entry_request_folder_and_children($child, $importer, (string)$entry->id);
            }
            else {
                self::add_import_entry_request_file($child, $importer, (string)$entry->id);
            }
        }
    }

    /**
     * Creates a folder, and recursively, all folders and files under it.
     * TODO: Refactor this to combine it with add_import_entry_request_folder_and_children()
     *
     * @param SimpleXMLElement $entry    The entry to base the folder's data on
     * @param PluginImportLeap $importer The importer
     * @param int $parent                The ID of the parent artefact for this folder
     * @throws ImportException If the given entry is not detected as being a folder
     * @return array The artefact mapping for the folder and all children - a
     *               list of entry ID => artefact IDs for each entry processed. See
     *               PluginImport::import_from_load_mapping() for more information
     */
    private static function create_folder_and_children(SimpleXMLElement $entry, PluginImportLeap $importer, $parent=null) {
        if (!self::is_folder($entry, $importer)) {
            throw new ImportException($importer, "create_folder(): Cannot create a folder artefact from an entry we don't recognise as a folder");
        }

        $artefactmapping = array();

        // Create the folder
        $folderid = self::create_folder($entry, $importer, $parent);
        $artefactmapping[(string)$entry->id] = array($folderid);

        // Then create all folders/files under it
        foreach (self::get_children_of_folder($entry, $importer) as $childid) {
            $child = $importer->get_entry_by_id($childid);
            if (self::is_folder($child, $importer)) {
                $result = self::create_folder_and_children($child, $importer, $folderid);
                $artefactmapping = array_merge($artefactmapping, $result);
            }
            else {
                if ($file = self::create_file($child, $importer, $folderid)) {
                    $artefactmapping[$childid] = array($file->get('id'));
                }
            }
        }

        return $artefactmapping;
    }
}
