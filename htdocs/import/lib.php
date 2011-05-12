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
 * @subpackage import
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/**
 * base class for imports.
 * handles queuing and sets up some basic helper functions
 */
abstract class PluginImport extends Plugin {

    protected $id;
    protected $data;
    protected $expirytime;
    protected $usr;
    protected $usrobj;

    /** the ImporterTransport object to use */
    protected $importertransport;

    /**
     * @param int $id the queue record id
     * @param stdclass $record (optional, pass this to save db queries)
     */
    public function __construct($id, $record=null) {
        if (empty($record)) {
            if (!$record = get_record('import_queue', 'id', $id)) {
                throw new NotFoundException("Failed to find import queue record with id $id");
            }
        }
        foreach ((array)$record as $field => $value) {
            if ($field == 'data' && !is_array($value)) {
                $value = unserialize($value);
            }
            $this->{$field} = $value;
        }
        $this->usrobj = new User();
        $this->usrobj->find_by_id($this->usr);
    }

    /**
     * set the importer transport to use for this import
     *
     * @param ImporterTransport $transport
     */
    public function set_transport(ImporterTransport $transport) {
        $this->importertransport = $transport;
    }

    /**
     * initialisation.  by default just calls the transporter's prepare method
     */
    public function prepare() {
        $this->importertransport->prepare_files();
        $this->importertransport->extract_file();
    }

    /**
    * process the files and adds them to the user's artefact area
    */
    public abstract function process();

    /**
     * perform cleanup tasks, delete temp files etc
     */
    public function cleanup() {
        $this->importertransport->cleanup();
    }

    /**
     * helper method to return member variables
     * @todo maybe refactor this to just use __get
     */
    public function get($field) {
        if (!property_exists($this,$field)) {
            throw new ParamOutOfRangeException("Field $field wasn't found in class " . get_class($this));
        }
        return $this->{$field};
    }

    /**
     * helper function to return the appropriate class name from an import format
     * this will try and resolve inconsistencies (eg file/files, leap/leap2a etc
     * and also pull in the class definition for you
     */
    public static function class_from_format($format) {
        $format = trim($format);
        $corr = array(
            'files' => 'file',
            'leap2a' => 'leap'
        );
        foreach ($corr as $bad => $good) {
            if ($format == $bad) {
                $format = $good;
                break;
            }
        }
        safe_require('import', $format);
        return generate_class_name('import', $format);
    }

    /**
    * @todo check the rest of the queue table for options
    * Generate a new import to be queued
    *
    * @param int    $userid    idof user to import for
    * @param string $plugin    plugin to handle the import
    *                          not always known at this point
    * @param string $host      wwwroot of mnet host if applicable
    * @param int    $ready     whether the import is ready to start (usually no)
    */
    public static function create_new_queue($userid, $plugin=null, $host=null, $ready=0) {
        // generate a token, insert it into the queue table
        $queue = (object)array(
            'token'      => generate_token(),
            'host'       => $host,
            'usr'        => $userid,
            'queue'      => (int)!(PluginImport::import_immediately_allowed()),
            'ready'      => $ready,
            'expirytime' => db_format_timestamp(time()+(60*60*24)),
            'plugin'     => $plugin
        );
        $queue->id = insert_record('import_queue', $queue);
        return $queue;
    }

    /**
     * creates an importer object from the queue information
     *
     * @param int               $id the queue record (if there is one, else pass 0)
     * @param ImporterTransport $transport the transporter object to use
     * @param stdclass          $record the queue data (this <b>must</b> be passed when no id is given
     *
     * @return PluginImport
     */
    public static function create_importer($id, ImporterTransport $transporter, $record=null) {
        if (empty($record)) {
            if (!$record = get_record('import_queue', 'id', $id)) {
                throw new NotFoundException("Failed to find import queue record with id $id");
            }
        }
        $class = self::class_from_format($record->format);
        $i =  new $class($id,$record);
        $i->set_transport($transporter);
        $transporter->set_importer($i);
        return $i;
    }

    /**
     * validate the import data that we have after the file has been fetched.
     * This is static, because the data may need to be validated earlier than setting up everything else
     * For example, in the case of the administrator adding a user manually from a Leap2A file,
     * we want to validate the leap data before creating the user record.
     *
     * @param array $importdata usually what ImportTransporter::files_info returns
     * @throws ImportException
     */
    public static abstract function validate_transported_data(ImporterTransport $transporter);

    /**
     * Whether imports are allowed immediately or if they must be queued
     * eg if the server is under load or whatever
     * @todo not implemented yet, but <b>use this anyway</b>
     *
     * @return boolean
     */
    public static final function import_immediately_allowed() {
        return true;
    }

    /**
    * if we're sending stuff back to wherever we were called from
    * use this method
    * at the moment, the only implementation is for mnet
    * sending back a list of file ids.
    */
    public function get_return_data() {
        return array();
    }
}

/**
 * cron job to process the queue and wake up and finish imports
 */
function import_process_queue() {

    if (!$ready = get_records_select_array('import_queue',
        'ready = ? OR expirytime <  ?', array(1, db_format_timestamp(time())),
        '', '*,' . db_format_tsfield('expirytime', 'ex'))) {
        return true;
    }

    $now = time();

    $processed = array();
    foreach ($ready as $item) {
        if ($item->ex < $now) {
            log_debug('deleting expired import record', $item);
            $processed[] = $item->id;
            continue;
        }
        $tr = null;
        if (!empty($item->host)) {
            $tr = new MnetImporterTransport($item);
        }
        else {
            $tr = new LocalImporterTransport($item);
        }
        $importer = PluginImport::create_importer($item->id, $tr, $item);
        try {
            $importer->prepare();
            $importer->process();
            $importer->cleanup();
            $processed[] = $item->id;
        }
        catch (Exception $e) {
            log_debug('an error occured on import: ' . $e->getMessage());
            $importer->get('importertransport')->cleanup();
        }
    }

    if (empty($processed)) {
        return true;
    }

    delete_records_select(
        'import_queue',
        'id IN ( ' . implode(',', db_array_to_ph($processed)) . ')',
        $processed
    );
}

/**
 * base class for transport layers.
 * Implements helper methods and makes some abstract stuff
 */
abstract class ImporterTransport {

    /** temporary directory to work in if necessary  */
    protected $tempdir;

    /** the importer to eventually handle the import */
    protected $importer;

    /** unique id for the import directories.  usually the import queue id, but sometimes needs to be set manually */
    protected $importid;

    /** relative path inside the temporary directory */
    protected $relativepath;

    /** whether the tempdir has been set up already */
    private $tempdirprepared = false;

    /** the file to import (sometimes a zip file) */
    protected $importfile;

    /** the manifest file, if there is one and we know about it */
    protected $manifestfile;

    /** the mimetype of the file we are importing */
    protected $mimetype;

    /** the import queue record **/
    protected $importrecord;

    /** set when extract_files is called */
    protected $extracted;

    /** optional sha1 of the file we expect */
    protected $expectedsha1;

    /**
     * @param stdclass $import the import record. This should correspond to a record in import_queue, but can be faked
     */
    public abstract function __construct($import);

    /**
     * small helper function to set up and unserialize the import data
     */
    protected  function set_import_data($import) {
        $this->importrecord = $import;
        if (is_string($import->data)) {
            $import->data = unserialize($import->data);
        }
    }

    /**
     * figure out the temporary directory to use
     * and make sure it exists, etc
     */
    public function prepare_tempdir() {
        if ($this->tempdirprepared) {
            return true;
        }
        $this->relativepath = 'temp/import/' . $this->importid . '/';
        if ($tmpdir = get_config('unziptempdir')) {
            $this->tempdir = $tmpdir . $this->relativepath;
        }
        else {
            $this->tempdir = get_config('dataroot') . $this->relativepath;
        }
        if (!check_dir_exists($this->tempdir)) {
            throw new ImportException($this->importer, 'Failed to create the temporary directories to work in');
        }
        $this->tempdirprepared = true;

    }

    /**
     * helper get method
     * @todo maybe refactor this to __get
     */
    public function get($field) {
        if (!property_exists($this,$field)) {
            throw new ParamOutOfRangeException("Field $field wasn't found in class " . get_class($this));
        }
        return $this->{$field};
    }

    /**
    * this might be a path to a directory containing the files
    * or an array containing some other info
    * or the path to a file, depending on the format
    */
    public function files_info() {
        return array(
            'importfile'   => $this->importfile,
            'tempdir'      => $this->tempdir,
            'relativepath' => $this->relativepath,
            'manifestfile' => $this->manifestfile,
        );
    }

    /**
    * do whatever is necessary to retrieve the file(s)
    */
    public abstract function prepare_files();

    /**
    * cleanup temporary working area
    */
    public function cleanup() {
        if (empty($this->tempdir)) {
            return;
        }
        require_once('file.php');
        rmdirr($this->tempdir);
    }

    /*
     * set the importer object
     * this must be done before prepare_files is called
     *
     * @param PluginImport $importer
     */
    public function set_importer(PluginImport $importer) {
        $this->importer = $importer;
    }

    /**
     * helper function for import code to use to extract a file
     * it will either unzip a zip file, or move an import file to the destination
     *
     * @throws ImportException
     */
    public function extract_file() {
        if ($this->extracted) {
            return;
        }
        $this->prepare_tempdir();
        if ($this->expectedsha1 &&  sha1_file($this->importfile) != $this->expectedsha1) {
            throw new ImportException($this->importer, 'sha1 of recieved importfile didn\'t match expected sha1');
        }

        $todir = $this->tempdir . 'extract/';
        if (!check_dir_exists($todir)) {
            throw new ImportException($this, 'Failed to create the temporary directories to work in');
        }
        safe_require('artefact', 'file');
        $ziptypes = PluginArtefactFile::get_mimetypes_from_description('zip');
        if (empty($this->mimetype)) {
            require_once('file.php');
            $this->mimetype = file_mime_type($this->importfile);
        }
        // if we don't have a zipfile, just move the import file to the extract location
        if (!in_array($this->mimetype, $ziptypes)) {
            if (strpos($this->importfile, $todir) !== 0) {
                rename($this->importfile, $todir . $this->importfilename);
            }
            $this->manifestfile = $this->importfilename;
            $this->extracted = true;
            return;
        }

        // check that pathtounzip is valid
        if (!is_executable(get_config('pathtounzip'))) {
            throw new ImportException($this, get_string('unzipnotinstalled', 'admin'));
        }

        $command = sprintf('%s %s %s %s',
            get_config('pathtounzip'),
            escapeshellarg($this->importfile),
            get_config('unzipdirarg'),
            escapeshellarg($todir)
        );
        $output = array();
        exec($command, $output, $returnvar);
        if ($returnvar != 0) {
            if ($returnvar == 1) {
                log_warn("Unzipping the zip file caused a warning, but it is recoverable so continuing anyway");
            } else {
                throw new ImportException($this, 'Failed to unzip the file recieved from the transport object');
            }
        }
        $this->extracted = true;
    }

    /**
     * validate data to be imported
     */
    public abstract function validate_import_data();
}

/**
 * class to handle 'local' transport - eg uploaded files
*/
class LocalImporterTransport extends ImporterTransport {

    /**
     * @param stdclass $import the import record
     */
    public function __construct($import) {
        $this->set_import_data($import);
        foreach (array('importfile', 'importfilename', 'importid', 'mimetype') as $reqkey) {
            if (!array_key_exists($reqkey, $this->importrecord->data)) {
                throw new ImportException("Missing required information $reqkey");
            }
            $this->{$reqkey} = $this->importrecord->data[$reqkey];
        }
    }

    public function validate_import_data() { }


    // nothing to do, uploaded files live in /tmp
    public function cleanup() { }

    // nothing to do, unzipping is handled elsewhere
    public function prepare_files() { }
}


/**
 * transport layer for mnet based imports
 * this just overrides prepare_files to do an xmlrpc request
 */
class MnetImporterTransport extends ImporterTransport {

    /** xmlrpc host */
    private $host;
    /** token set for retrieiving files */
    private $token;

    /**
     * @param stdclass $import the import record
     */
    public function __construct($import) {
        $this->host = get_record('host', 'wwwroot', $import->host);
        $this->importid = $import->id; // since we have an import record, use the id
        $this->set_import_data($import);
        $this->expectedsha1 = $this->importrecord->data['zipfilesha1'];
    }

    /**
     * retrieves the files from the remote host
     */
    public function prepare_files() {
        if (empty($this->importer)) {
            throw new ImportException(null, 'Failed to initialise XMLRPC file retrieval - no importer object');
        }
        $this->prepare_tempdir();
        $this->token = $this->importer->get('token');
        require_once(get_config('docroot') . 'api/xmlrpc/client.php');
        $client = new Client();
        try {
            $client->set_method('portfolio/mahara/lib.php/fetch_file')
                    ->add_param($this->token)
                    ->send($this->host->wwwroot);
        } catch (XmlrpcClientException $e) {
            throw new ImportException($this->importer, 'Failed to retrieve zipfile from remote server: ' . $e->getMessage());
        }
        if (!$filecontents = base64_decode($client->response)) {
            throw new ImportException($this->importer, 'Failed to retrieve zipfile from remote server');
        }

        $this->importfilename = 'import.zip';
        $this->importfile = $this->tempdir . $this->importfilename;
        if (!file_put_contents($this->tempdir  . $this->importfilename, $filecontents)) {
            throw new ImportException($this->importer, 'Failed to write out the zipfile to local temporary storage');
        }
        // detect the filetype and bail if it's not a zip file
        safe_require('artefact', 'file');
        require_once('file.php');
        $ziptypes = PluginArtefactFile::get_mimetypes_from_description('zip');
        $this->mimetype = file_mime_type($this->tempdir . $this->importfilename);
        if (!in_array($this->mimetype, $ziptypes)) {
            throw new ImportException($this->importer, 'Not a valid zipfile - mimetype was ' . $this->mimetype);
        }
    }


    /**
     * used for appending to the description of created data
     * eg "imported from blah server"
     *
     * @return string
     */
    public function get_description() {
        return get_string('remotehost', 'mahara', $this->host->name);
    }

    public function validate_import_data() {
        $importdata = $this->importrecord->data;
        if (is_string($importdata)) {
            $importdata = unserialize($importdata);
        }
        if (empty($importdata) ||
            !is_array($importdata) ||
            !array_key_exists('filesmanifest', $importdata) ||
            !is_array($importdata['filesmanifest']) ||
            count($importdata['filesmanifest']) == 0) {
            throw new ImportException($this, 'Missing files manifest in import data');
        }
        if (!array_key_exists('zipfilesha1', $importdata)) {
            throw new ImportException($this, 'Missing zipfile sha1 in import data');
        }
        return true;
    }
}

/**
 * Looks in the import staging area in dataroot and deletes old, unneeded
 * import.
 */
function import_cleanup_old_imports() {
    require_once('file.php');
    $basedir = get_config('dataroot') . 'import/';
    if (!check_dir_exists($basedir, false)) {
        return;
    }
    $importdir = new DirectoryIterator($basedir);
    $mintime = time() - (12 * 60 * 60); // delete imports older than 12 hours

    // The import dir contains one directory for each attempted import, named
    // after their username and the import timestamp
    foreach ($importdir as $attemptdir) {
        if ($attemptdir->isDot()) continue;
        if ($attemptdir->getCTime() < $mintime) {
            rmdirr($basedir . $attemptdir->getFilename());
        }
    }
}
