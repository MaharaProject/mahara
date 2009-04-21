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
 * @subpackage artefact-file-export-html
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/**
 * TODO:
 *  - handle filenames with slashes
 *  - handle exporting when there's no files/folders
 */
class HtmlExportFile extends HtmlExportArtefactPlugin {

    private $artefactdata;

    public function dump_export_data() {
        // Get all folders/files
        $this->artefactdata = get_records_select_assoc(
            'artefact',
            "owner = ? AND
            artefacttype IN ('" . join("','", PluginArtefactFile::get_artefact_types()) . "')",
            array($this->exporter->get('user')->get('id')),
            '',
            'id, artefacttype, parent, title'
        );
        if (!$this->artefactdata) {
            $this->artefactdata = array();
        }

        $this->populate_profileicons();
        $this->create_index_for_directory($this->fileroot, 0, null);
        $this->populate_filedir($this->fileroot, 0, null);

    }

    public function get_summary() {
        $filecount   = count(array_filter($this->artefactdata, create_function('$a', 'return $a->artefacttype != "folder";')));
        $foldercount = count(array_filter($this->artefactdata, create_function('$a', 'return $a->artefacttype == "folder";')));
        return array(
            'title' => 'Files',
            'description' => "<p>You have {$filecount} files in {$foldercount} folders. <a href=\"files/file/index.html\">Browse</a>.</p>",
        );
    }

    /**
     * Puts all profile icons in the static/profileicons/ directory
     */
    private function populate_profileicons() {
        $madeprofileiconsdir = false;
        $profileiconsdir = $this->exporter->get('exportdir') . '/' . $this->exporter->get('rootdir') . '/static/profileicons/';
        $removekeys = array();
        foreach (array_keys($this->artefactdata) as $key) {
            $artefactdata = $this->artefactdata[$key];
            if ($artefactdata->artefacttype == 'profileicon') {
                $removekeys[] = $key;

                if (!$madeprofileiconsdir) {
                    check_dir_exists($profileiconsdir);
                }

                // TODO: sanitise path for /'s
                $artefact = artefact_instance_from_id($artefactdata->id);
                if (!copy($artefact->get_path(), $profileiconsdir . $artefactdata->title)) {
                    throw new SystemException("Unable to copy profile icon $artefactdata->title into export");
                }

                // Make sure we grab a nicely resized version too
                $maxdimension = 200;
                $resizedpath = get_dataroot_image_path('artefact/file/profileicons/', $artefactdata->id, $maxdimension);
                if (!copy($resizedpath, $profileiconsdir . $maxdimension . 'px-' . $artefactdata->title)) {
                    throw new SystemException("Unable to copy resized profile icon {$maxdimension}px-$artefactdata->title into export");
                }
            }
        }

        foreach ($removekeys as $key) {
            unset($this->artefactdata[$key]);
        }
    }

    /**
     * Given a filesystem directory and the id of an artefact, fill the 
     * filesystem directory with the files and folders that Mahara considers 
     * are inside the artefact.
     *
     * This method is recursive, creating the file/directory structure for all 
     * directories under the one passed.
     *
     * This method also creates index.htmls in each directory created.
     *
     * @param string $filesystemdirectory The file system directory to populate
     * @param int    $level               How deep the directory is
     * @param int    $parentid            The folder to start from - can be null
     */
    private function populate_filedir($filesystemdirectory, $level, $parentid) {
        foreach ($this->artefactdata as $artefactdata) {
            if ($artefactdata->parent == $parentid) {
                if ($artefactdata->artefacttype == 'folder') {
                    $directory = $filesystemdirectory . $artefactdata->title . '/';
                    check_dir_exists($directory);
                    $this->create_index_for_directory($directory, $level + 1, $artefactdata);
                    $this->populate_filedir($directory, $level + 1, $artefactdata->id);
                }
                else {
                    $artefact = artefact_instance_from_id($artefactdata->id);
                    if (!copy($artefact->get_path(), $filesystemdirectory . $artefactdata->title)) {
                        throw new SystemException("HtmlExportFile::populate_filedir: unable to copy artefact $artefactdata->id's file");
                    }
                }
            }
        }
    }

    /**
     * Given a filesystem directory and the artefact data corresponding to that 
     * directory, creates an index.html for it.
     *
     * @param string $filesystemdirectory The file system directory to make the 
     *                                    index.html inside
     * @param int    $level               How deep this directory index is
     * @param object $artefactdata        Artefact data relating to the folder 
     *                                    represented by this directory
     */
    private function create_index_for_directory($filesystemdirectory, $level, $artefactdata=null) {
        $smarty = $this->exporter->get_smarty(str_repeat('../', $level + 2));
        $smarty->assign('breadcrumbs', array(array('text' => 'Files', 'path' => 'index.html')));

        if ($artefactdata) {
            $smarty->assign('folder', ArtefactTypeFileBase::get_full_path($artefactdata->id, $this->artefactdata));
        }
        else {
            $smarty->assign('folder', '/');
        }

        $id = ($artefactdata) ? $artefactdata->id : 'null';
        $smarty->assign('folders', array_filter($this->artefactdata, create_function('$a', 'return $a->parent == ' . $id . ' && $a->artefacttype == "folder";')));
        $smarty->assign('files',   array_filter($this->artefactdata, create_function('$a', 'return $a->parent == ' . $id . ' && $a->artefacttype != "folder";')));

        $content = $smarty->fetch('export:html/file:index.tpl');
        if (false === file_put_contents($filesystemdirectory . 'index.html', $content)) {
            throw new SystemException("HtmlExportFile::create_index_for_directory: unable to create index.html for directory $id");
        }
    }

    public function get_summary_weight() {
        return 20;
    }

}

?>
