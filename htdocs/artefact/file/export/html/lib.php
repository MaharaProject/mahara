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
 * @subpackage artefact-file-export-html
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/**
 * TODO:
 *  - handle exporting when there's no files/folders
 */
class HtmlExportFile extends HtmlExportArtefactPlugin {

    private $artefactdata = array();
    private $owner;

    // Keep track of files not owned by the exporting user.  These should be included in the
    // export tarball, but should not appear in the browseable folder structure.
    private $otherfiles = array();

    public function dump_export_data() {

        $this->owner = $this->exporter->get('user')->get('id');

        foreach ($this->exporter->get('artefacts') as $artefact) {
            if (in_array($artefact->get('artefacttype'), PluginArtefactFile::get_artefact_types())) {
                $id = $artefact->get('id');
                $this->artefactdata[$id] = $artefact;
                if ($artefact->get('owner') != $this->owner) {
                    $this->otherfiles[$id] = $id;
                }
            }
        }

        // Grab all parent folders of all artefacts owned by the exporting user, so we can export
        // the files in their correct folder location
        if ($this->exporter->get('artefactexportmode') != PluginExport::EXPORT_ALL_ARTEFACTS && $this->artefactdata) {
            $folderids = array();
            foreach (array_keys($this->artefactdata) as $artefactid) {
                if (!isset($this->otherfiles[$artefactid])) {
                    $folderids = array_merge($folderids, array_keys(artefact_get_parents_for_cache($artefactid)));
                }
            }
            $folderids = array_unique($folderids);

            foreach ($folderids as $folderid) {
                if (!isset($this->artefactdata[$folderid])) {
                    $artefact = artefact_instance_from_id($folderid);
                    // We grabbed all parents of the artefacts in the export, 
                    // but not all parents are folders
                    if ($artefact->get('artefacttype') == 'folder') {
                        $this->artefactdata[$folderid] = $artefact;
                    }
                }
            }

        }

        $this->populate_profileicons();
        $this->create_index_for_directory($this->fileroot, 0, null);
        $this->populate_filedir($this->fileroot, 0, null);

        // Copy other users' files into the extrafileroot directory
        foreach ($this->otherfiles as $id) {
            if (!$this->artefactdata[$id] instanceof ArtefactTypeFile) {
                continue;
            }
            $dest = $this->extrafileroot . $id . '-' . PluginExportHtml::sanitise_path($this->artefactdata[$id]->get('title'));
            if (!copy($this->artefactdata[$id]->get_path(), $dest)) {
                throw new SystemException("Unable to copy artefact $id's file");
            }
        }
    }

    public function get_summary() {
        $smarty = $this->exporter->get_smarty();
        $smarty->assign('filecount', count(array_filter($this->artefactdata, create_function('$a', 'return $a->get("artefacttype") != "folder";'))));
        $smarty->assign('foldercount', count(array_filter($this->artefactdata, create_function('$a', 'return $a->get("artefacttype") == "folder";'))));
        $smarty->assign('spaceused', $this->exporter->get('user')->get('quotaused'));

        return array(
            'title' => get_string('Files', 'artefact.file'),
            'description' => $smarty->fetch('export:html/file:summary.tpl'),
        );
    }

    public function get_summary_weight() {
        return 20;
    }

    /**
     * Puts all profile icons in the static/profileicons/ directory
     */
    private function populate_profileicons() {
        $profileiconsdir = $this->exporter->get('exportdir') . '/' . $this->exporter->get('rootdir') . '/static/profileicons/';
        $removekeys = array();
        foreach ($this->artefactdata as $artefactid => $artefact) {
            if ($artefact->get('artefacttype') == 'profileicon') {
                $removekeys[] = $artefactid;

                if (!copy($artefact->get_path(), $profileiconsdir . PluginExportHtml::sanitise_path($artefact->get('title')))) {
                    throw new SystemException("Unable to copy profile icon $artefactid into export");
                }

                // Make sure we grab a nicely resized version too
                $maxdimension = 200;
                $resizedpath = get_dataroot_image_path('artefact/file/profileicons/', $artefactid, $maxdimension);
                if (!copy($resizedpath, $profileiconsdir . $maxdimension . 'px-' . PluginExportHtml::sanitise_path($artefact->get('title')))) {
                    throw new SystemException("Unable to copy resized profile icon {$maxdimension}px-{$artefact->get('title')} into export");
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
        foreach ($this->artefactdata as $artefactid => $artefact) {
            if ($artefact->get('parent') == $parentid && $artefact->get('owner') == $this->owner) {
                if ($artefact->get('artefacttype') == 'folder') {
                    $directory = $filesystemdirectory . PluginExportHtml::sanitise_path($artefact->get('title')) . '/';
                    check_dir_exists($directory);
                    $this->create_index_for_directory($directory, $level + 1, $artefact);
                    $this->populate_filedir($directory, $level + 1, $artefactid);
                }
                else {
                    $artefact = artefact_instance_from_id($artefactid);
                    if (!$artefact->get_path() || !copy($artefact->get_path(), $filesystemdirectory . PluginExportHtml::sanitise_path($artefact->get('title')))) {
                        throw new SystemException(get_string('nonexistentfile', 'export', $artefact->get('title')));
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
    private function create_index_for_directory($filesystemdirectory, $level, ArtefactTypeFolder $artefact=null) {
        $smarty = $this->exporter->get_smarty(str_repeat('../', $level + 2), 'file');
        $smarty->assign('page_heading', get_string('Files', 'artefact.file'));
        $smarty->assign('breadcrumbs', array(array('text' => 'Files', 'path' => 'index.html')));

        if ($artefact) {
            $smarty->assign('folder', ArtefactTypeFileBase::get_full_path($artefact->get('id'), $this->artefactdata));
        }
        else {
            $smarty->assign('folder', '/');
        }

        $id = ($artefact) ? $artefact->get('id') : null;
        $smarty->assign('folders', $this->prepare_artefacts_for_smarty($id, true));
        $smarty->assign('files',   $this->prepare_artefacts_for_smarty($id, false));

        $content = $smarty->fetch('export:html/file:index.tpl');
        if (false === file_put_contents($filesystemdirectory . 'index.html', $content)) {
            throw new SystemException("Unable to create index.html for directory $id");
        }
    }

    /**
     * Helper to convert artefacts into smarty-friendly data
     *
     * @param int $parent   The ID of the parent folder for the artefact to 
     *                      convert
     * @param bool $folders True to get folders, false to get everything but 
     *                      folders
     */
    private function prepare_artefacts_for_smarty($parent, $folders) {
        $data = array();
        $equality = ($folders) ? '==' : '!=';
        $parent = (is_null($parent)) ? 'null': intval($parent);
        $artefacts = array_filter($this->artefactdata, create_function('$a',
            'return $a->get("parent") == ' . $parent
            . ' && $a->get("artefacttype") ' . $equality . ' "folder"'
            . ' && $a->get("owner") == ' . $this->owner . ';'
        ));
        foreach ($artefacts as $artefact) {
            $size = '';
            if ($artefact->get('artefacttype') != 'folder') {
                $size = $artefact->get('size');
                $size = ($size) ? display_size($size) : '';
            }
            $data[] = array(
                'icon'        => '',
                'title'       => $artefact->get('title'),
                'path'        => PluginExportHtml::sanitise_path($artefact->get('title')),
                'description' => $artefact->get('description'),
                'size'        => $size,
                'date'        => strftime(get_string('strftimedaydatetime'), $artefact->get('ctime')),
            );
        }

        return $data;
    }

}
