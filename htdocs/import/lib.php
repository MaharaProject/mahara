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

abstract class PluginImport extends Plugin {

    private $id;
    private $data;
    private $host; // this might move
    private $expirytime;
    private $token;
    private $usr;
    private $usrobj;
    private $importertransport;

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

        if (!empty($this->host)) {
            $this->importertransport = new MnetImporterTransport($this);
        }
        else {
            $this->importertransport = new LocalImporterTransport($this);
        }
        // we could do more here later I guess
    }

    public function prepare() {
        $this->importertransport->prepare_files();
    }

    /**
    * processes the files and adds them to the user's artefact area
    */
    public abstract function process();

    public function cleanup() {
        $this->importertransport->cleanup();
    }

    public function get($field) {
        if (!property_exists($this,$field)) {
            throw new ParamOutOfRangeException("Field $field wasn't found in class " . get_class($this));
        }
        return $this->{$field};
    }

    public static function class_from_format($format) {
        $format = trim($format);
        if ($format == 'files') {
            $format = 'file';
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

    public static function create_importer($id, $record=null) {
        if (empty($record)) {
            if (!$record = get_record('import_queue', 'id', $id)) {
                throw new NotFoundException("Failed to find import queue record with id $id");
            }
        }
        $class = self::class_from_format($record->format);
        return new $class($id,$record);
    }

    public static abstract function validate_import_data($importdata);

    public static final function import_immediately_allowed() {
    // @todo change this (check whatever)
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
        $importer = PluginImport::create_importer($item->id, $item);
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

abstract class ImporterTransport {

    /**
    * this might be a path to a directory containing the files
    * or an array containing some other info
    * or the path to a file, depending on the format
    */
    public abstract function files_info();

    /**
    * do whatever is necessary to retrieve the file(s)
    */
    public abstract function prepare_files();

    /**
    * cleanup temporary working area
    */
    public abstract function cleanup();
}

class LocalImporterTransport extends ImporterTransport {

    private $relativepath;
    private $zipfilename;

    public function __construct(PluginImport $importer) {
    }

    public function cleanup() {
        // TODO
    }

    public function prepare_files() {
    }

    /**
     * For this to work with the 'file' import plugin, it needs to provide 'zipfile' and 'relativepath'
     *
     * Other import plugins might need different things
     */
    public function files_info() {
        return array(
            'zipfile' => $this->zipfilename,
            'relativepath' => $this->relativepath,
        );
    }
}


class MnetImporterTransport extends ImporterTransport {

    private $importer;

    private $host;
    private $token;

    private $relativepath;
    private $tempdir;
    private $zipfilename;

    public function __construct(PluginImport $importer) {
        $this->importer = $importer;
        $this->token = $importer->get('token');
        $this->host = get_record('host', 'wwwroot', $importer->get('host'));
    }

    public function cleanup() {
        if (empty($this->tempdir)) {
            return;
        }
        require_once('file.php');
        rmdirr($this->tempdir);
    }

    public function prepare_files() {
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

        $this->relativepath = 'temp/import/' . $this->importer->get('id') . '/';
        if ($tmpdir = get_config('unziptempdir')) {
            $this->tempdir = $tmpdir . $this->relativepath;
        }
        else {
            $this->tempdir = get_config('dataroot') . $this->relativepath;
        }
        if (!check_dir_exists($this->tempdir)) {
            throw new ImportException($this->importer, 'Failed to create the temporary directories to work in');
        }

        $this->zipfilename = 'import.zip';
        if (!file_put_contents($this->tempdir  . $this->zipfilename, $filecontents)) {
            throw new ImportException($this->importer, 'Failed to write out the zipfile to local temporary storage');
        }
    }

    public function files_info() {
        return array(
            'zipfile' => $this->zipfilename,
            'tempdir' => $this->tempdir,
            'relativepath' => $this->relativepath,
        );
    }

    public function get_description() {
        return get_string('remotehost', 'mahara', $this->host->name);
    }
}

/**
 * Looks in the import staging area in dataroot and deletes old, unneeded
 * import.
 */
function import_cleanup_old_imports() {
    require_once('file.php');
    $basedir = get_config('dataroot') . 'import/';
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

?>
