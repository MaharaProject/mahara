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
 * @subpackage export-html
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/**
 * HTML export plugin
 */
class PluginExportHtml extends PluginExport {

    /**
     * name of resultant zipfile
     */
    protected $zipfile;

    /**
     * The time the export occured
     */
    protected $export_time;

    /**
     * The name of the directory under which all the other directories and 
     * files will be placed in the export
     */
    protected $rootdir;

    /**
    * constructor.  overrides the parent class
    * to set up smarty and the attachment directory
    */
    public function __construct(User $user, $views, $artefacts) {
        parent::__construct($user, $views, $artefacts);
        $this->export_time = time(); // TODO: move into parent class
        // TODO move this normalisation into a method
        $this->rootdir = 'portfolio-for-' . preg_replace('#[^a-zA-Z0-9_-]+#', '-', $user->get('username'));

        // Create basic required directories
        foreach (array('files', 'views', 'static') as $directory) {
            $directory = "{$this->exportdir}/{$this->rootdir}/{$directory}/";
            if (!check_dir_exists($directory)) {
                throw new SystemException("Couldn't create the temporary export directory $directory");
            }
        }
        $this->zipfile = 'mahara-export-html-user'
            . $this->get('user')->get('id') . '-' . $this->export_time . '.zip';
    }

    /**
    * main export routine
    */
    public function export() {
        // For each artefact plugin, if it implements leap export, ask it to 
        // dump out its structure
        $summaries = array();
        foreach (plugins_installed('artefact', true) as $plugin) {
            $plugin = $plugin->name;
            if (safe_require('export', 'html/' . $plugin, 'lib.php', 'require_once', true)) {
                $classname = 'HtmlExport' . ucfirst($plugin);
                if (!is_subclass_of($classname, 'HtmlExportArtefactPlugin')) {
                    throw new SystemException("Class $classname does not extend HtmlExportArtefactPlugin as it should");
                }

                safe_require('artefact', $plugin);

                $artefactexporter = new $classname($this);
                $artefactexporter->dump_export_data();
                $summaries[$plugin] = array($artefactexporter->get_summary_weight(), $artefactexporter->get_summary());
            }
        }

        // Get the view data
        $this->dump_view_export_data();
        $summaries['view'] = array(100, $this->get_view_summary());

        // Sort by weight (then drop the weight information)
        uasort($summaries, create_function('$a, $b', 'return $a[0] > $b[0];'));
        foreach ($summaries as &$summary) {
            $summary = $summary[1];
        }

        // Build index.html
        $this->build_index_page($summaries);
        

        // zip everything up
        $cwd = getcwd();
        $command = sprintf('%s %s %s %s',
            get_config('pathtozip'),
            get_config('ziprecursearg'),
            escapeshellarg($this->exportdir .  $this->zipfile),
            escapeshellarg($this->rootdir)
        );
        $output = array();
        chdir($this->exportdir);
        exec($command, $output, $returnvar);
        chdir($cwd);
        if ($returnvar != 0) {
            throw new SystemException('Failed to zip the export file');
        }
        return $this->zipfile;
    }

    public function cleanup() {
        // @todo remove temporary files and directories
        // @todo maybe move the zip file somewhere else - like to files/export or something
    }

    public function get_smarty($rootpath='') {
        $smarty = smarty_core();
        $smarty->assign('user', $this->get('user'));
        $smarty->assign('rootpath', $rootpath);

        return $smarty;
    }

    private function build_index_page($summaries) {
        $smarty = $this->get_smarty();
        $smarty->assign('summaries', $summaries);
        $content = $smarty->fetch('export:html:index.tpl');
        file_put_contents($this->exportdir . '/' . $this->rootdir . '/index.html', $content);
    }

    /**
     * Dumps all views into the HTML export
     *
     * TODO: respect $this->views
     */
    private function dump_view_export_data() {
        if ($viewids = get_column('view', 'id', 'owner', $this->get('user')->get('id'), 'type', 'portfolio')) {
            $smarty = $this->get_smarty('../../');
            foreach ($viewids as $viewid) {
                $view = new View($viewid);

                $directory = $this->exportdir . '/' . $this->rootdir . '/views/' . preg_replace('#[^a-zA-Z0-9_-]+#', '-', $view->get('title'));
                if (!check_dir_exists($directory)) {
                    throw new SystemException("Could not create directory for view $viewid");
                }

                $smarty->assign('view', $view->build_columns());
                $content = $smarty->fetch('export:html:view.tpl');
                if (!file_put_contents("$directory/index.html", $content)) {
                    throw new SystemException("Could not write view page for view $viewid");
                }
            }
        }
    }

    private function get_view_summary() {
        $smarty = $this->get_smarty('../');

        $views = array();
        foreach ($this->views as $view) {
            if ($view->get('type') != 'profile') {
                $views[] = array(
                    'title' => $view->get('title'),
                    'folder' => preg_replace('#[^a-zA-Z0-9_-]+#', '-', $view->get('title')),
                );
            }
        }
        $smarty->assign('views', $views);
        $smarty->assign('viewcount', count($views));

        return array(
            'title' => 'Views',
            'description' => $smarty->fetch('export:html:viewsummary.tpl'),
        );
    }

}

abstract class HtmlExportArtefactPlugin {

    protected $exporter;

    protected $fileroot;

    public function __construct(PluginExportHTML $exporter) {
        $this->exporter = $exporter;
        $pluginname = strtolower(substr(get_class($this), strlen('HtmlExport')));
        $this->fileroot = $this->exporter->get('exportdir') . '/' . $this->exporter->get('rootdir') . '/files/' . $pluginname . '/';
        if (!check_dir_exists($this->fileroot)) {
            throw new SystemException("Couldn't create the temporary export directory $this->fileroot");
        }
    }

    abstract public function dump_export_data();

    abstract public function get_summary();

    abstract public function get_summary_weight();

}

?>
