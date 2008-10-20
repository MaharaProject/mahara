<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 */

defined('INTERNAL') || die();

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
        $importer = Importer::create_importer($item->id, $item);
        try {
            $importer->prepare();
            $importer->process();
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

abstract class Importer {

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

    public function get($field) {
        if (!property_exists($this,$field)) {
            throw new ParamOutOfRangeException("Field $field wasn't found in class " . get_class($this));
        }
        return $this->{$field};
    }

    public static function class_from_format($format) {
        switch (trim($format)) {
            case 'file':
            case 'files':
                return 'FilesImporter';
            default:
                // @todo more laterz (like mahara native and/or leap)
                throw new ParamException("unknown import format $format");

        }
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

    public function import_immediately_allowed() {
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
}

/**
* base case - just import files into the artefact area as files.
* don't interpret anything or try and create anything other than straight files.
*/
class FilesImporter extends Importer {

    private $manifest;
    private $files;
    private $unzipdir;
    private $zipfilesha1;
    private $artefacts;
    private $importdir;

    public function __construct($id, $record=null) {
        parent::__construct($id, $record);
        $data = $this->get('data');
        self::validate_import_data($data);
        $this->manifest = $data['filesmanifest'];
        $this->zipfilesha1 = $data['zipfilesha1'];
    }

    public static function validate_import_data($importdata) {
        if (empty($importdata) ||
            !is_array($importdata) ||
            !array_key_exists('filesmanifest', $importdata) ||
            !is_array($importdata['filesmanifest']) ||
            count($importdata['filesmanifest']) == 0) {
            throw new ImportException('Missing files manifest in import data');
        }
        if (!array_key_exists('zipfilesha1', $importdata)) {
            throw new ImportException('Missing zipfile sha1 in import data');
        }
        return true;
    }

    public function process() {
        $this->extract_file();
        $this->verify_file_contents();
        $this->add_artefacts();
    }

    public function extract_file() {
        $filesinfo = $this->get('importertransport')->files_info();
        // this contains relativepath and zipfile name
        $this->relativepath = $filesinfo['relativepath'];
        $this->zipfile = $filesinfo['zipfile'];

        if (sha1_file(get_config('dataroot') . '/' . $this->relativepath . '/' . $this->zipfile) != $this->zipfilesha1) {
            throw new ImportException('sha1 of recieved zipfile didn\'t match expected sha1');
        }

        $this->unzipdir = get_config('dataroot') .  '/' . $this->relativepath . 'extract/';
        if (!check_dir_exists($this->unzipdir)) {
            throw new ImportException('Failed to create the temporary directories to work in');
        }

        $command = sprintf('%s %s %s %s',
            get_config('pathtounzip'),
            escapeshellarg(get_config('dataroot') . '/' . $this->relativepath . '/' . $this->zipfile),
            get_config('unzipdirarg'),
            escapeshellarg($this->unzipdir)
        );
        $output = array();
        exec($command, $output, $returnvar);
        if ($returnvar != 0) {
            throw new ImportException('Failed to unzip the file recieved from the transport object');
        }
    }

    public function verify_file_contents() {
        $includedfiles = get_dir_contents($this->unzipdir);
        $okfiles = array();
        $badfiles = array();
        // check what arrived in the directory
        foreach ($includedfiles as $k => $f) {
            // @todo penny later we might need this
            if (is_dir($f)) {
                $badfiles[] = $f;
                unset($includedfiles[$k]);
                continue;
            }
            $sha1 = sha1_file($this->unzipdir . $f);
            if (array_key_exists($sha1, $this->manifest)) {
                $tmp = new StdClass;
                $tmp->sha1 = $sha1;
                $tmp->wantsfilename = $this->manifest[$sha1]['filename'];
                $tmp->actualfilename = $f;
                $okfiles[] = $tmp;
                unset($includedfiles[$k]);
                continue;
            }
            $badfiles[] = $f;
            unset($includedfiles[$k]);
        }
        $ok_c  = count($okfiles);
        $bad_c = count($badfiles);
        $man_c = count($this->manifest);
        if ($ok_c != $man_c) {
            throw new ImportException('Files receieved did not exactly match what was in the manifest');
            // @todo penny later - better reporting (missing files, too many files, etc)
        }
        $this->files = $okfiles;
    }

    public function add_artefacts() {
        // we're just adding them as files into an 'incoming' directory in the user's file area.
        safe_require('artefact', 'file');
        try {
            $this->importdir = ArtefactTypeFolder::get_folder_id('incoming', get_string('incomingfolderdesc'), null, true, $this->get('usr'));
        } catch (Exception $e) {
            throw new ImportException($e->getMessage());
        }
        $savedfiles = array(); // to put files into so we can delete them should we encounter an exception
        foreach ($this->files as $f) {
            try {
                $data = (object)array(
                    'title' => $f->wantsfilename,
                    'description' => $f->wantsfilename . ' (' . get_string('importedfrom', 'mahara', $this->get('importertransport')->get_description()) . ')',
                    'parent' => $this->importdir,
                    'owner' => $this->get('usr'),
                    'container' => 0,
                    'locked' => 0,
                );

                if ($imagesize = getimagesize(get_config('dataroot') . $this->relativepath . 'extract/' . $f->actualfilename)) {
                    $mime = $imagesize['mime'];
                    $data->filetype = $mime;
                }

                $id = ArtefactTypeFile::save_file(
                    $this->relativepath . 'extract/' . $f->actualfilename,
                    $data,
                    $this->get('usrobj')
                );
                if (empty($id)) {
                    throw new ImportException("Failed to create new artefact for $f->sha1");
                }
                $savedfiles[] = $id;
            }
            catch (Exception $e) {
                foreach ($savedfiles as $fileid) {
                    $tmp = artefact_instance_from_id($fileid);
                    $tmp->delete();
                }
                throw new ImportException('Failed to create some new artefacts');
            }
        }
        $this->artefacts = $savedfiles;
    }

    public function get_return_data() {
        return array('folder' => $this->importdir, 'file' => (count($this->artefacts) == 1) ? $this->artefacts[0] : 0);
    }
}

class MnetImporterTransport extends ImporterTransport {

    private $importer;

    private $host;
    private $token;

    private $relativepath;
    private $tempdir;
    private $zipfilename;

    public function __construct(Importer $importer) {
        $this->importer = $importer;
        $this->token = $importer->get('token');
        $this->host = get_record('host', 'wwwroot', $importer->get('host'));
    }

    public function cleanup() {
        if (empty($this->tempdir)) {
            return;
        }
        rmdir($this->tempdir);
    }

    public function prepare_files() {
        require_once(get_config('docroot') . 'api/xmlrpc/client.php');
        $client = new Client();
        try {
            $client->set_method('portfolio/mahara/lib.php/fetch_file')
                    ->add_param($this->token)
                    ->send($this->host->wwwroot);
        } catch (XmlrpcClientException $e) {
            throw new ImportException('Failed to retrieve zipfile from remote server: ' . $e->getMessage());
        }
        if (!$filecontents = base64_decode($client->response)) {
            throw new ImportException('Failed to retrieve zipfile from remote server');
        }

        $this->relativepath = 'temp/import/' . $this->importer->get('id') . '/';
        $this->tempdir = get_config('dataroot') . $this->relativepath;
        if (!check_dir_exists($this->tempdir)) {
            throw new ImportException('Failed to create the temporary directories to work in');
        }

        $this->zipfilename = 'import.zip';
        if (!file_put_contents($this->tempdir . '/' . $this->zipfilename, $filecontents)) {
            throw new ImportException('Failed to write out the zipfile to local temporary storage');
        }
    }

    public function files_info() {
        return array(
            'zipfile' => $this->zipfilename,
            'relativepath' => $this->relativepath,
        );
    }

    public function get_description() {
        return get_string('remotehost', 'mahara', $this->host->name);
    }
}



?>
