<?php
/**
 *
 * @package    mahara
 * @subpackage export
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

require_once('view.php');
require_once(get_config('docroot') . '/artefact/lib.php');

/**
 * Helper interface to hold PluginExport's abstract static methods
 */
interface IPluginExport {
    /**
     * A human-readable title for the export
     */
    public static function get_title();

    /**
     * A human-readable description for the export
     */
    public static function get_description();
}

/**
 * Base class for all Export plugins.
 *
 * This class does some basic setup for export plugins, as well as interfacing
 * with the Mahara Plugin API. Mostly, the work of generating exports is
 * delegated to the plugins themselves.
 *
 * TODO: split generation of an archive file from the export() method,
 * implement zipping the export in a method in this class to reduce
 * duplication.
 */
abstract class PluginExport extends Plugin implements IPluginExport {

    /**
     * Export all views and collections owned by this user
     */
    const EXPORT_ALL_VIEWS_COLLECTIONS = -1;

    /**
     * Export only certain views - used internally when a list of views is
     * passed to the constructor
     */
    const EXPORT_LIST_OF_VIEWS = -2;

    /**
     * Export all artefacts owned by this user
     */
    const EXPORT_ALL_ARTEFACTS = -3;

    /**
     * Export artefacts that are part of the views to be exported
     */
    const EXPORT_ARTEFACTS_FOR_VIEWS = -4;

    /**
     * Export only certain artefacts - used internally when a list of artefacts
     * is passed to the constructor
     */
    const EXPORT_LIST_OF_ARTEFACTS = -5;

    /*
     * Export only certain collections and their artefacts
     */
    const EXPORT_LIST_OF_COLLECTIONS = -6;

    /**
     * Maximum filename length in UTF-8 encoding characters
     * Most file systems (FAT, FAT32, NTFS, ext2, ext3, ext4) support the filename length of 255 bytes
     * UTF-8 needs at most 3 bytes to encode
     */
    const MAX_FILENAME_LENGTH = 80;

    public static function get_plugintype_name() {
        return 'export';
    }

    /**
     * Where the theme assets for export plugins live. Usually it's in the normal location,
     * but they can also have assets that live under artefacts.
     *
     * @param string $pluginname
     * @return string
     */
    public static function get_theme_path($pluginname) {
        if (strpos($pluginname, '/')) {
            // Path for export plugins that sit under an artefact plugin
            // i.e. "export:html/file:index.tpl"
            list($exportname, $artefactname) = explode('/', $pluginname, 2);
            return 'artefact/' . $artefactname . '/export/' . $exportname;
        }
        else {
            return parent::get_theme_path($pluginname);
        }
    }

    /**
     * Perform the export and return the path to the resulting file.
     *
     * @return string path to the resulting file (relative to dataroot)
     */
    abstract public function export();

    /**
     * Perform the checks to see if there is enough space for the
     * export() resulting file before trying to write it.
     *
     * @return bool
     */
    abstract public function is_diskspace_available();

    //  MAIN CLASS DEFINITION

    /**
     * List of artefacts to export. Set up by constructor.
     */
    public $artefacts = array();

    /**
     * List of views to export. Set up by constructor.
     */
    public $views = array();

    /**
     * Whether the user requested to export comments as well
     */
    public $includefeedback = false;

    /**
     * User object for the user being exported.
     */
    protected $user;

    /**
     * Represents the mode for exporting views - one of the class consts
     * defined above
     */
    protected $viewexportmode;

    /**
     * Represents the mode for exporting artefacts - one of the class consts
     * defined above
     */
    protected $artefactexportmode;

    /**
     * The time the export was generated.
     *
     * Technically, this is the time at which the export object was created,
     * not the time at which export() was called.
     */
    protected $exporttime;

    /**
     * Callback to notify when progress is made
     */
    private $progresscallback = null;

    /**
     * Establishes exactly what views and artefacts are to be exported, and
     * sets up temporary export directories
     *
     * Subclasses can override this if they need to do anything else, but
     * they must call parent::__construct.
     *
     * @param User $user       The user to export data for
     * @param mixed $views     can be:
     *                         - PluginExport::EXPORT_ALL_VIEWS_COLLECTIONS
     *                         - array, containing:
     *                             - int - view ids
     *                             - stdclass objects - db rows
     *                             - View objects
     * @param mixed $artefacts can be:
     *                         - PluginExport::EXPORT_ALL_ARTEFACTS
     *                         - PluginExport::EXPORT_ARTEFACTS_FOR_VIEWS
     *                         - PluginExport::EXPORT_LIST_OF_COLLECTIONS
     *                         - array, containing:
     *                             - int - artefact ids
     *                             - stdclass objects - db rows
     *                             - ArtefactType subclasses
     */
    public function __construct(User $user, $views, $artefacts, $progresscallback=null) {
        if (!is_null($progresscallback)) {
            if (is_callable($progresscallback)) {
                $this->progresscallback = $progresscallback;
            }
            else {
                throw new SystemException("The specified progress callback isn't callable");
            }
        }
        $this->notify_progress_callback(0, 'Starting');

        $this->exporttime = time();
        $this->user = $user;

        $userid = $this->user->get('id');
        $tmpviews = array();
        $tmpartefacts = array();

        // Get the list of views to export
        if ($views == self::EXPORT_ALL_VIEWS_COLLECTIONS) {
            $tmpviews = get_column_sql('SELECT id FROM {view} WHERE owner = ? ORDER BY id', array($userid));
            $this->viewexportmode = $views;
        }
        else if (is_array($views) && $artefacts == self::EXPORT_LIST_OF_COLLECTIONS) {
            $tmpviews = $views;
            $this->viewexportmode = self::EXPORT_LIST_OF_COLLECTIONS;
        }
        else if (is_array($views)) {
            $tmpviews = $views;
            $this->viewexportmode = self::EXPORT_LIST_OF_VIEWS;
        }
        foreach ($tmpviews as $v) {
            $view = null;
            if ($v instanceof View) {
                $view = $v;
            }
            else if (is_object($v)) {
                $view = new View($v->id, $v);
            }
            else if (is_numeric($v)) {
                $view = new View($v);
            }
            if (is_null($view)) {
                throw new ParamOutOfRangeException("Invalid view $v");
            }
            if ($view->get('owner') != $userid) {
                throw new UserException("User $userid does not own view " . $view->get('id'));
            }
            $this->views[$view->get('id')] = $view;
        }

        // Get the list of artefacts to export
        if ($artefacts == self::EXPORT_ALL_ARTEFACTS) {
            $query = 'SELECT id FROM {artefact} WHERE "owner" = ?';
            $args = array($userid);
            if (sizeof($tmpviews)) {
                $query .= 'UNION
                    SELECT artefact
                    FROM {view_artefact}
                WHERE "view" IN (SELECT id FROM {view} WHERE "owner" = ?)
                    ORDER BY id';
                $args[] = $userid;
                $this->artefactexportmode = $tmpartefacts = get_column_sql($query, $args);
            }
        }
        else {
            if ($tmpviews) {
                $sql = "SELECT DISTINCT va.artefact
                    FROM {view_artefact} va
                    LEFT JOIN {view} v ON v.id = va.view
                    WHERE v.owner = ?
                    AND va.view IN ( " . implode(',', array_keys($this->views)) . ")
                    ORDER BY va.artefact";
                $tmpartefacts = (array)get_column_sql($sql, array($userid));

                // Some artefacts are not inside the view, but still need to be exported with it
                $tmpartefacts = array_unique(array_merge($tmpartefacts, $this->get_view_extra_artefacts()));
                $tmpartefacts = artefact_get_descendants($tmpartefacts);
                $tmpartefacts = array_unique(array_merge($tmpartefacts, $this->get_artefact_extra_artefacts($tmpartefacts)));
            }
            if ($artefacts == self::EXPORT_ARTEFACTS_FOR_VIEWS) {
                $this->artefactexportmode = $artefacts;
            }
            else if ($artefacts == self::EXPORT_LIST_OF_COLLECTIONS) {
                $this->artefactexportmode = self::EXPORT_ARTEFACTS_FOR_VIEWS;
            }
            else {
                $tmpartefacts = array_unique(array_merge($tmpartefacts, $artefacts));
                $this->artefactexportmode = self::EXPORT_LIST_OF_ARTEFACTS;
            }
        }
        $typestoplugins = get_records_assoc('artefact_installed_type');
        $ids_to_get = array();
        foreach ($tmpartefacts as $a) {
            if ($a instanceof ArtefactType) {
                continue;
            }
            else if (is_object($a) && isset($a->id)) {
                $ids_to_get[] = $a->id;
            }
            else if (is_numeric($a)) {
                $ids_to_get[] = $a;
            }
        }
        $artefacts = artefact_instances_from_ids($ids_to_get);
        foreach ($tmpartefacts as $a) {
            $artefact = null;
            if ($a instanceof ArtefactType) {
                $artefact = $a;
            }
            else if (is_object($a) && isset($a->id)) {
                $artefact = $artefacts[$a->id];
            }
            else if (is_numeric($a)) {
                $artefact = $artefacts[$a];
            }
            if (is_null($artefact)) {
                throw new ParamOutOfRangeException("Invalid artefact $a");
            }
            // This check won't work, at the _least_ because at the time of
            // writing, can_view_artefact does not support normal users viewing
            // site files. This check is also pretty damn slow. So think twice
            // before uncommenting it. I presume if you _are_ uncommenting it,
            // it's because you're trying to isloate a security vulnerability
            // where a user can export another user's files or something. In
            // which case you'll be being careful anyway, I hope.
            //if (!$this->user->can_view_artefact($artefact)) {
            //    throw new SystemException("User $userid does not own artefact " . $artefact->get('id'));
            //}
            if ($artefact->exportable()) {
                $this->artefacts[$artefact->get('id')] = $artefact;
            }
        }

        $this->collections = array();
        if (empty($this->views)) {
            $collections = FALSE;
        }
        else {
            $collections = get_records_sql_assoc('
                    SELECT * FROM {collection} WHERE id IN (
                        SELECT collection
                        FROM {collection_view}
                        WHERE view IN (' . join(',', array_keys($this->views)) . ')
                        )',
                    array());
        }

        if ($collections) {
            require_once('collection.php');
            foreach ($collections as &$c) {
                $this->collections[$c->id] = new Collection(0, $c);
            }
        }

        // Now set up the temporary export directories
        $this->exportdir = get_config('dataroot')
            . 'export/'
            . $this->user->get('id')  . '/'
            . $this->exporttime .  '/';
        if (!check_dir_exists($this->exportdir)) {
            throw new SystemException("Couldn't create the temporary export directory $this->exportdir");
        }

        $this->messages = array();

        $this->notify_progress_callback(10, 'Setup');
    }

    /**
     * Accessor
     *
     * @param string $field The field to get (see the class definition to find
     *                      which fields are available)
     */
    public function get($field) {
        if (!property_exists($this, $field)) {
            throw new ParamOutOfRangeException("Field $field wasn't found in class " . get_class($this));
        }
        return $this->{$field};
    }

    /**
     * Notifies the registered progress callback about the progress in generating the export.
     *
     * This is provided as exports can take a long time to generate. Export
     * plugins are encouraged to call this at least after performing some major
     * operation, and should always call it saying when the execution of
     * export() is done. However, it is unnecessary to call it too often.
     *
     * For testing purposes, you may find it useful to register a progress
     * callback that simply log_debug()s the data, so you can check that the
     * percentage is always increasing, for example.
     *
     * @param int $percent   The total percentage of the way through generating
     *                       the export. The base class constructor hands over
     *                       control claiming 10% of the work is done.
     * @param string $status A string describing the current status of the
     *                       export - e.g. 'Exporting Artefact (20/75)'
     */
    protected function notify_progress_callback($percent, $status) {
        if ($this->progresscallback) {
            call_user_func_array($this->progresscallback, array(
                $percent, $status
            ));
        }
    }

    /**
     * Returns embedded artefacts in view description and
     * additional artefacts required for view export from artefact plugins
     */
    protected function get_view_extra_artefacts() {
        $extra = View::get_embedded_artefacts(array_keys($this->views));
        $plugins = plugins_installed('artefact');
        foreach ($plugins as &$plugin) {
            safe_require('artefact', $plugin->name);
            $classname = generate_class_name('artefact', $plugin->name);
            if (is_callable($classname . '::view_export_extra_artefacts')) {
                if ($artefacts = call_static_method($classname, 'view_export_extra_artefacts', array_keys($this->views))) {
                    $extra = array_unique(array_merge($extra, $artefacts));
                }
            }
        }
        return $extra;
    }

    protected function get_artefact_extra_artefacts(&$artefactids) {
        if (empty($artefactids)) {
            return array();
        }
        $extra = array();
        $plugins = plugins_installed('artefact');
        foreach ($plugins as &$plugin) {
            safe_require('artefact', $plugin->name);
            $classname = generate_class_name('artefact', $plugin->name);
            if (is_callable($classname . '::artefact_export_extra_artefacts')) {
                if ($artefacts = call_static_method($classname, 'artefact_export_extra_artefacts', $artefactids)) {
                    $extra = array_unique(array_merge($extra, $artefacts));
                }
            }
        }
        return $extra;
    }
}

/**
 * Looks in the export staging area in dataroot and deletes old, unneeded
 * exports.
 */
function export_cleanup_old_exports() {
    require_once('file.php');
    $basedir = get_config('dataroot') . 'export/';

    // If the export directory hasn't been created yet, there's no point
    // running the cron.
    if(!is_dir($basedir)) {
        return;
    }

    $exportdir = new DirectoryIterator($basedir);
    $mintime = time() - (24 * 60 * 60); // delete exports older than 24 hours

    // The export dir contains one directory for each user who has created
    // an export, named after their UID
    foreach ($exportdir as $userdir) {
        if ($userdir->isDot()) continue;

        // Each user's directory contains one directory for each export
        // they made, named as the unix timestamp of the time they
        // generated it
        $udir = new DirectoryIterator($basedir . $userdir->getFilename());
        foreach ($udir as $dir) {
            if ($dir->isDot()) continue;
            if ($dir->getCTime() < $mintime) {
                rmdirr($basedir . $userdir->getFilename() . '/' . $dir->getFilename());
            }
        }
    }

    // Remove any rows from the export_archive that are older than 24 hours and are not exports for submissions
    delete_records_sql('DELETE FROM {export_archive} WHERE submission = 0 AND ctime < ?', array(date('Y-m-d H:i:s', $mintime)));
}

/**
 * Add an export item's information to the export_queue and export_queue_items tables
 *
 * @param   $objectarray Array of objects. Currently handles collection or view objects
 * @param   $external Name of the external connection
 * @param   $submitter Name of the submitter if different to owner
 * @param   $type Specify what type of export is to be done. Valid options are: 'collections', 'views', 'all'
 *
 * @return bool   If adds to queue successfully
 */
function export_add_to_queue($object, $external = null, $submitter = null, $type = null) {
    if (!is_array($object)) {
        if (!($object instanceof Collection) && !($object instanceof View)) {
            // not supported object type
            return true;
        }
        $objectarray[] = $object;
    }
    else {
        $objectarray = $object;
    }

    $queue = new stdClass();
    if ($submitter && $submitter->get('id')) {
        $queue->submitter = $submitter->get('id');
    }
    $owner = (!empty($objectarray[0])) ? $objectarray[0]->get('owner') : null;
    if ($owner) {
        $queue->usr = $objectarray[0]->get('owner');
    }
    else {
        $queue->usr = $queue->submitter;
    }
    $queue->exporttype = 'leap';
    if (!empty($type)) {
        $queue->type = $type;
    }
    $queue->ctime = db_format_timestamp(time());
    if ($external) {
        $queue->externalid = $external->id;
    }
    db_begin();
    $queueid = insert_record('export_queue', $queue, 'id', true);
    $counter = (!empty($type) && $type == 'all') ? 1 : 0;
    foreach ($objectarray as $key => $object) {
        if (!($object instanceof Collection) && !($object instanceof View)) {
            // not supported object type
            continue;
        }
        if ($object instanceof Collection) {
            require_once(get_config('docroot') . 'lib/view.php');
            $views = $object->views();
            foreach ($views['views'] as $view) {
                $v = new View($view->id);
                $queueitems = new stdClass();
                $queueitems->exportqueueid = $queueid;
                $queueitems->collection = $object->get('id');
                $queueitems->view = $view->id;
                insert_record('export_queue_items', $queueitems);
                $counter++;
            }
        }
        else if ($object instanceof View) {
            $queueitems = new stdClass();
            $queueitems->exportqueueid = $queueid;
            $queueitems->view = $object->get('id');
            insert_record('export_queue_items', $queueitems);
            $counter++;
        }
    }
    if (empty($counter)) {
        db_rollback();
    }
    else {
        db_commit();
    }
    return true;
}

/**
 * cron job to process the export queue
 * @param string $id  Specify which row of export_queue table you want to run - could be used for debugging purposes
 */
function export_process_queue($id = false) {

    $where = 'starttime IS NULL';
    $values = array();
    if ($id) {
        $where .= ' AND id = ?';
        $values = array($id);
    }

    // Try getting the first 100 items in queue - TODO; work out a good number to get at once
    if (!$ready = get_records_select_array('export_queue', $where, $values, 'ctime', '*', 0, 100)) {
        return true;
    }

    $now = date('Y-m-d H:i:s', time());

    foreach ($ready as $row) {
        // If there server is getting too busy we abort and wait for next cron run.
        if (server_busy()) {
            log_info('too busy');
            return true;
        }

        $errors = array();
        // update the item with start process time
        execute_sql('UPDATE {export_queue} SET starttime = ? WHERE id = ?', array($now, $row->id));
        $items = get_records_select_array('export_queue_items', 'exportqueueid = ?', array($row->id), 'id');
        if (!$items && $row->type == 'all') {
            $items = array();
            $row->what = 'all';
        }
        $views = array();
        // To make sure we process the item with this id only once we keep a track of the $lastid
        // We don't know if the $item will be a collection or view (or artefact possibly in the future)
        // In the case of a user exporting to leap2a there can be a number of collections/views to deal
        // with so we want to deal with each collection or view only once.
        $lastid = '';
        $submitted = false;
        foreach ($items as $key => $item) {
            if (!empty($item->collection) && $lastid != 'collection_' . $item->collection) {
                $row->what = 'collections';
                $lastid = 'collection_' . $item->collection;
                $views = array_merge($views, get_column('collection_view', 'view', 'collection', $item->collection));
                $submitted = get_record('collection', 'id', $item->collection);
            }
            else if (empty($item->collection) && !empty($item->view) && $lastid != 'view_' . $item->view) {
                $row->what = 'views';
                $lastid = 'view_' . $item->view;
                $views = array_merge($views, array($item->view));
                $submitted = get_record('view', 'id', $item->view);
            }
        }
        $views = array_unique($views);

        // Bail if we don't have enough data to do an export
        if (!isset($row->exporttype)
            || !isset($row->what)
            || !isset($views)) {
            $errors[] = get_string('unabletogenerateexport', 'export');
            log_warn(get_string('unabletogenerateexport', 'export'));
            continue;
        }

        safe_require('export', $row->exporttype);
        $user = new User();
        $user->find_by_id($row->usr);
        $class = generate_class_name('export', $row->exporttype);

        switch($row->what) {
            case 'all':
                $exporter = new $class($user, PluginExport::EXPORT_ALL_VIEWS_COLLECTIONS, PluginExport::EXPORT_ALL_ARTEFACTS);
                break;
            case 'views':
                $exporter = new $class($user, $views, PluginExport::EXPORT_ARTEFACTS_FOR_VIEWS);
                break;
            case 'collections':
                $exporter = new $class($user, $views, PluginExport::EXPORT_LIST_OF_COLLECTIONS);
                break;
            default:
                $errors[] = get_string('unabletoexportportfoliousingoptionsadmin', 'export');
                log_warn(get_string('unabletoexportportfoliousingoptionsadmin', 'export'));
        }

        $exporter->includefeedback = false; // currently only doing leap2a exports and they can't handle feedback

        // Get an estimate of how big the unzipped export file would be
        // so we can check that we have enough disk space for it
        $space = $exporter->is_diskspace_available();
        if (!$space) {
            $errors[] = get_string('exportfiletoobig', 'mahara');
            log_warn(get_string('exportfiletoobig', 'mahara'));
        }

        try {
            $zipfile = $exporter->export();
        }
        catch (SystemException $e) {
            $errors[] = get_string('exportzipfileerror', 'export', $e->getMessage());
            log_warn($e->getMessage());
        }

        $filepath = $exporter->get('exportdir');
        // If export is a submission we need to save this from being deleted by the export_cleanup_old_exports cron
        // so we need to put it somewhere safe
        if (!empty($submitted->submittedtime)) {
            // Now set up the export submission directories
            $submissiondir = get_config('dataroot')
            . 'submission/'
            . $row->usr  . '/';
            if (!check_dir_exists($submissiondir)) {
                $errors[] = get_string('submissiondirnotwritable', 'export', $submissiondir);
            }
            else {
                copy($filepath . $zipfile, $submissiondir . $zipfile);
                $filepath = $submissiondir;
            }
        }

        $filetitle = '';
        if (!empty($row->type)) {
            switch ($row->type) {
                case 'all':
                    $filetitle = get_string('allmydata', 'export');
                    break;
                default:
                    $filetitle = get_string('exporting' . $row->type, 'export');
            }
        }
        else {
            $filetitle = !empty($submitted->name) ? $submitted->name : $submitted->title;
        }
        $externalhost = !empty($submitted->submittedhost) ? $submitted->submittedhost : null;

        db_begin();
        // Need to record this in the export_archive table so one can fetch the file via a download link
        $archiveid = insert_record('export_archive', (object) array('usr' => $row->usr,
                                                                    'filename' => $zipfile,
                                                                    'filetitle' => $filetitle,
                                                                    'filepath' => $filepath,
                                                                    'submission' => ((!empty($submitted->submittedtime)) ? 1 : 0),
                                                                    'ctime' => db_format_timestamp(time()),
                                                                    ), 'id', true);
        if (!$archiveid) {
            $errors[] = get_string('exportarchivesavefailed', 'export');
        }
        // If the export row is for a submitted view/collection
        if (!empty($submitted->submittedtime)) {
            $inserted = insert_record('archived_submissions', (object) array('archiveid' => $archiveid,
                                                                             'group' => $submitted->submittedgroup,
                                                                             'externalhost' => $externalhost,
                                                                             'externalid' => $row->externalid,
                                                                             ));
            if (!$inserted) {
                $errors[] = get_string('archivedsubmissionfailed', 'export');
            }
            require_once(get_config('docroot') . 'lib/view.php');
            if ($submitted->submittedstatus == View::PENDING_RELEASE) {
                // we are running this export as part of the releasing submission process
                if ($row->what == 'collections') {
                    require_once(get_config('docroot') . 'lib/collection.php');
                    $id = substr($lastid, strlen('collection_'));
                    $collection = new Collection($id);
                    try {
                        $collection->release($row->submitter);
                    }
                    catch (SystemException $e) {
                        $errors[] = get_string('submissionreleasefailed', 'export');
                        log_warn($e->getMessage());
                    }
                }
                else if ($row->what == 'views') {
                    $id = substr($lastid, strlen('view_'));
                    $view = new View($id);
                    try {
                        $view->release($row->submitter);
                    }
                    catch (SystemException $e) {
                        $errors[] = get_string('submissionreleasefailed', 'export');
                        log_warn($e->getMessage());
                    }
                }
                else {
                    $errors[] = get_string('submissionreleasefailed', 'export');
                }
            }
        }
        else {
            // Need to send emails with the download link in them - so we add the data to the activity_queue table
            $arg = display_name($row->usr);
            $data = (object) array(
                'subject'   => false,
                'message'   => false,
                'strings'   => (object) array(
                    'subject' => (object) array(
                        'key'     => 'exportdownloademailsubject',
                        'section' => 'admin',
                        'args'    => array($filetitle),
                    ),
                    'message' => (object) array(
                        'key'     => 'exportdownloademailmessage',
                        'section' => 'admin',
                        'args'    => array(hsc($arg), $filetitle),
                    ),
                    'urltext' => (object) array(
                        'key'     => 'exportdownloadurl',
                        'section' => 'admin',
                    ),
                ),
                'users'     => array($row->usr),
                'url'       => get_config('webroot') . 'downloadarchive.php?id=' . $archiveid,
            );
            activity_occurred('maharamessage', $data);
        }

        // finally delete the queue item
        if (!delete_records('export_queue_items', 'exportqueueid', $row->id)) {
            $errors[] = get_string('deleteexportqueueitems', 'export', $row->id);
            log_warn('Unable to delete export queue items for ID: ' . $row->id);
        }
        if (!delete_records('export_queue', 'id', $row->id)) {
            $errors[] = get_string('deleteexportqueuerow', 'export', $row->id);
            log_warn('Unable to delete export queue row ID: ' . $row->id);
        }

        // if there are any errors then we need to alert the site and institution admins
        if (!empty($errors)) {
            $admins = get_column('usr', 'id', 'admin', 1, 'deleted', 0);
            $institutions = $user->get('institutions');
            if (!empty($institutions)) {
                foreach ($institutions as $key => $value) {
                    require_once(get_config('docroot') . 'lib/institution.php');
                    $institution = new Institution($key);
                    $admins = array_merge($admins, $institution->admins());
                }
            }

            $arg = "\n\n -" . implode("\n - ", $errors);
            $data = (object) array(
                'subject'   => false,
                'message'   => false,
                'strings'   => (object) array(
                    'subject' => (object) array(
                        'key'     => 'exportqueueerrorsadminsubject',
                        'section' => 'export',
                    ),
                    'message' => (object) array(
                        'key'     => 'exportqueueerrorsadminmessage',
                        'section' => 'export',
                        'args'    => array(hsc($row->id), hsc($arg)),
                    ),
                    'urltext' => (object) array(
                        'key'     => 'exportdownloadurl',
                        'section' => 'admin',
                    ),
                ),
                'users'     => $admins,
                'url'       => get_config('webroot') . 'admin/users/exportqueue.php',
            );
            activity_occurred('maharamessage', $data);
            db_rollback();
        }
        else {
            db_commit();
        }
    }

    return true;
}

/**
 * In between function for adding thing to export queue that are submitted items.
 * Would be useful if we need to do special checking/handling of these compared to normal exports.
 * Currently only passes thru the variables.
 */
function add_submission_to_export_queue($object, $submitter) {
    return export_add_to_queue($object, null, $submitter);
}

/**
 * Check to see if any archived submission leap2a files have been removed from server
 * and if so then remove the corresponding database information.
 */
function submissions_delete_removed_archive() {

    $remove = array();
    // Get all the items in the archived_submissions table
    $results = get_records_sql_assoc("SELECT * FROM {export_archive} e JOIN {archived_submissions} a ON e.id = a.archiveid", array());
    if (!empty($results)) {
        foreach ($results as $key => $result) {
            // make sure the archive file is still on server (not removed by server admin)
            if (!file_exists($result->filepath . $result->filename)) {
                $remove[] = $result->archiveid;
            }
        }
    }

    if (!empty($remove)) {
        // we have some export_archive row ids so lets remove the rows
        $idstr = join(',', $remove);
        db_begin();
        delete_records_select('archived_submissions', 'archiveid IN (' . $idstr . ')');
        delete_records_select('export_archive', 'id IN (' . $idstr . ')');
        db_commit();
    }
}

/**
 * Create a zip file containing the specified files and folders, including subfolders
 *
 * @param string $exportdir Export directory - files to export will be found here
 *                          and the archive file will be placed here once created
 * @param string $filename  The desired name of the archive file
 * @param array $files      An array of files and folders to add to the archive
 *                          (relative to the export directory)
 */
function create_zip_archive($exportdir, $filename, $files) {
    $filename = $exportdir . $filename;
    $archive = new ZipArchive();
    if ($archive->open($filename, ZIPARCHIVE::CREATE)) {
        $allfiles = array();
        $directories = array();
        // add plain files and mark directories to process
        foreach ($files as $file) {
            if (is_file($exportdir . $file)) {
                $archive->addFile($exportdir . $file, $file);
            }
            else {
                $directories[] = $file . '/';
            }
        }
        // add the contents of all directories and subdirectories
        while (count($directories) > 0) {
            $dir = array_shift($directories);
            $files = array_diff(scandir($exportdir . $dir), array('..', '.'));
            if (count($files) == 0) {
                $archive->addEmptyDir($dir);
            }
            foreach($files as $file) {
                if (is_file($exportdir . $dir . $file)) {
                    $archive->addFile($exportdir . $dir . $file, $dir . $file);
                }
                else {
                    $directories[] = $dir . $file . '/';
                }
            }
        }
        $archive->close();
    }
    else {
        throw new SystemException('could not open zip file');
    }
}
