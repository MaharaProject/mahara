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
 * @subpackage import-file
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
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

    public function process() {
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
                $pathtoclam = escapeshellcmd(trim(get_config('pathtoclam')));
                if ($pathtoclam && file_exists($pathtoclam) && is_executable($pathtoclam)) {
                    if ($errormsg = mahara_clam_scan_file($uzd . $f)) {
                        throw new ImportException($this, $errormsg);
                    }
                }
                else {
                    clam_mail_admins(get_string('clamlost', 'mahara', $pathtoclam));
                }
            }
            $sha1 = sha1_file($uzd . $f);
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
                $data = (object)array(
                    'title' => $f->wantsfilename,
                    'description' => $f->wantsfilename . ' (' . get_string('importedfrom', 'mahara', $this->get('importertransport')->get_description()) . ')',
                    'parent' => $this->importdir,
                    'owner' => $this->get('usr'),
                    'container' => 0,
                );

                if ($imagesize = getimagesize($uzd . $f->actualfilename)) {
                    $mime = $imagesize['mime'];
                    $data->filetype = $mime;
                }

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
