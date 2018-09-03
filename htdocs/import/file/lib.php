<?php
/**
 *
 * @package    mahara
 * @subpackage import-file
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

require_once('uploadmanager.php');

class PluginImportFile extends PluginImport {

    private $manifest;
    private $files;
    private $zipfilesha1;
    private $artefacts;
    private $importdir;

    public function __construct($id, $record=null) {
        parent::__construct($id, $record);
        $data = $this->get('data');
        $this->manifest = $data['filesmanifest'];
        $this->zipfilesha1 = $data['zipfilesha1'];
    }

    public static function validate_transported_data(ImporterTransport $transport) {
        return true; // nothing to do , we're just importing files to the file artefact plugin
    }

    public function process($step = PluginImport::STEP_NON_INTERACTIVE) {
    //    $this->importertransport->extract_file($this->importertransport->get('mimetype'), $this->zipfilesha1);
        $this->verify_file_contents();
        $this->add_artefacts();
    }

    public function verify_file_contents() {
        $uzd = $this->importertransport->get('tempdir') . 'extract/';
        $includedfiles = get_dir_contents($uzd);
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
            if (get_config('viruschecking')) {
                if ($errormsg = mahara_clam_scan_file($uzd . $f)) {
                    throw new ImportException($this, $errormsg);
                }
            }
            $sha1 = sha1_file($uzd . $f);
            if (array_key_exists($sha1, $this->manifest)) {
                $tmp = new stdClass();
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
            throw new ImportException($this, 'Files receieved did not exactly match what was in the manifest');
            // @todo penny later - better reporting (missing files, too many files, etc)
        }
        $this->files = $okfiles;
    }

    public function add_artefacts() {
        // we're just adding them as files into an 'incoming' directory in the user's file area.
        safe_require('artefact', 'file');
        $uzd = $this->importertransport->get('tempdir') . 'extract/';
        try {
            $this->importdir = ArtefactTypeFolder::get_folder_id('incoming', get_string('incomingfolderdesc'), null, true, $this->get('usr'));
        } catch (Exception $e) {
            throw new ImportException($this, $e->getMessage());
        }
        $savedfiles = array(); // to put files into so we can delete them should we encounter an exception
        foreach ($this->files as $f) {
            try {
                $explode_wantsfilename = explode('.', $f->wantsfilename);
                $data = (object)array(
                    'title' => $f->wantsfilename,
                    'oldextension' => end($explode_wantsfilename),
                    'description' => $f->wantsfilename . ' (' . get_string('importedfrom', 'mahara', $this->get('importertransport')->get_description()) . ')',
                    'parent' => $this->importdir,
                    'owner' => $this->get('usr'),
                    'container' => 0,
                );

                $id = ArtefactTypeFile::save_file(
                    $uzd . $f->actualfilename,
                    $data,
                    $this->get('usrobj'),
                    true
                );
                if (empty($id)) {
                    throw new ImportException($this, "Failed to create new artefact for $f->sha1");
                }
                $savedfiles[] = $id;
            }
            catch (Exception $e) {
                foreach ($savedfiles as $fileid) {
                    $tmp = artefact_instance_from_id($fileid);
                    $tmp->delete();
                }
                throw new ImportException($this, 'Failed to create some new artefacts');
            }
        }
        $this->artefacts = $savedfiles;
    }

    public function get_return_data() {
        return array('folder' => $this->importdir, 'file' => (count($this->artefacts) == 1) ? $this->artefacts[0] : 0);
    }
}
