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
     * The name of the directory under which all the other directories and 
     * files will be placed in the export
     */
    protected $rootdir;

    /**
     * List of directories of static files provided by artefact plugins
     */
    private $pluginstaticdirs = array();

    /**
     * List of stylesheets to include in the export.
     *
     * This is keyed by artefact plugin name, the empty string key contains 
     * stylesheets that will be included on all pages.
     */
    private $stylesheets = array('' => array());

    /**
     * Whether the user requested to export just one view. In this case,
     * the generated export doesn't have the home page - just the View is 
     * exported (plus artefacts of course)
     */
    protected $exportingoneview = false;

    /**
    * constructor.  overrides the parent class
    * to set up smarty and the attachment directory
    */
    public function __construct(User $user, $views, $artefacts, $progresscallback=null) {
        parent::__construct($user, $views, $artefacts, $progresscallback);
        $this->rootdir = 'portfolio-for-' . self::text_to_path($user->get('username'));

        // Create basic required directories
        foreach (array('files', 'views', 'static', 'static/smilies') as $directory) {
            $directory = "{$this->exportdir}/{$this->rootdir}/{$directory}/";
            if (!check_dir_exists($directory)) {
                throw new SystemException("Couldn't create the temporary export directory $directory");
            }
        }
        $this->zipfile = 'mahara-export-html-user'
            . $this->get('user')->get('id') . '-' . $this->exporttime . '.zip';

        // Find what stylesheets need to be included
        $themedirs = theme_get_path('', 'export/html/', true);
        $stylesheets = array('style.css', 'print.css');
        foreach ($themedirs as $theme => $themedir) {
            foreach ($stylesheets as $stylesheet) {
                if (is_readable($themedir . 'style/' . $stylesheet)) {
                    array_unshift($this->stylesheets[''], 'theme/' . $theme . '/static/style/' . $stylesheet);
                }
            }
        }

        $this->exportingoneview = (
            $this->viewexportmode == PluginExport::EXPORT_LIST_OF_VIEWS &&
            $this->artefactexportmode == PluginExport::EXPORT_ARTEFACTS_FOR_VIEWS &&
            count($this->views) == 1
        );

        $this->notify_progress_callback(15, 'Setup complete');
    }

    public static function get_title() {
        return get_string('title', 'export.html');
    }

    public static function get_description() {
        return get_string('description', 'export.html');
    }

    /**
     * Main export routine
     */
    public function export() {
        raise_memory_limit('128M');

        $summaries = array();
        $plugins = plugins_installed('artefact', true);
        $exportplugins = array();
        $progressstart = 15;
        $progressend   = 25;
        $plugincount   = count($plugins);

        // First pass: find out which plugins are exporting like us, and ask 
        // them about the static data they want to include in every template
        $i = 0;
        foreach ($plugins as $plugin) {
            $plugin = $plugin->name;
            $this->notify_progress_callback(intval($progressstart + (++$i / $plugincount) * ($progressend - $progressstart)), 'Preparing ' . $plugin);

            if (safe_require('export', 'html/' . $plugin, 'lib.php', 'require_once', true)) {
                $exportplugins[] = $plugin;

                $classname = 'HtmlExport' . ucfirst($plugin);
                if (!is_subclass_of($classname, 'HtmlExportArtefactPlugin')) {
                    throw new SystemException("Class $classname does not extend HtmlExportArtefactPlugin as it should");
                }

                safe_require('artefact', $plugin);

                // Find out whether the plugin has static data for us
                $themestaticdirs = array_reverse(theme_get_path('', 'artefact/' . $plugin . '/export/html/', true));
                foreach ($themestaticdirs as $dir) {
                    $staticdir = substr($dir, strlen(get_config('docroot') . 'artefact/'));
                    $this->pluginstaticdirs[] = $staticdir;
                    foreach (array('style.css', 'print.css') as $stylesheet) {
                        if (is_readable($dir . 'style/' . $stylesheet)) {
                            $this->stylesheets[$plugin][] = str_replace('export/html/', '', $staticdir) . 'style/' . $stylesheet;
                        }
                    }
                }
            }
        }

        // Second pass: actually dump data for active export plugins
        $progressstart = 25;
        $progressend   = 60;
        $i = 0;
        foreach ($exportplugins as $plugin) {
            $this->notify_progress_callback(intval($progressstart + (++$i / $plugincount) * ($progressend - $progressstart)), 'Exporting data for ' . $plugin);
            $classname = 'HtmlExport' . ucfirst($plugin);
            $artefactexporter = new $classname($this);
            $artefactexporter->dump_export_data();
            // If just exporting a list of views, we don't care about the summaries for each artefact plugin
            if (!($this->viewexportmode == PluginExport::EXPORT_LIST_OF_VIEWS && $this->artefactexportmode == PluginExport::EXPORT_ARTEFACTS_FOR_VIEWS)) {
                $summaries[$plugin] = array($artefactexporter->get_summary_weight(), $artefactexporter->get_summary());
            }
        }

        // Get the view data
        $this->notify_progress_callback(65, 'Exporting Views');
        $this->dump_view_export_data();

        if (!$this->exportingoneview) {
            $summaries['view'] = array(100, $this->get_view_summary());

            // Sort by weight (then drop the weight information)
            $this->notify_progress_callback(75, 'Building index page');
            uasort($summaries, create_function('$a, $b', 'return $a[0] > $b[0];'));
            foreach ($summaries as &$summary) {
                $summary = $summary[1];
            }

            // Build index.html
            $this->build_index_page($summaries);
        }

        // Copy all static files into the export
        $this->notify_progress_callback(80, 'Copying static files');
        $this->copy_static_files();
        

        // zip everything up
        $this->notify_progress_callback(90, 'Creating zipfile');
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
        $this->notify_progress_callback(100, 'Done');
        return $this->zipfile;
    }

    public function cleanup() {
        // @todo remove temporary files and directories
        // @todo maybe move the zip file somewhere else - like to files/export or something
    }

    public function get_smarty($rootpath='', $section='') {
        if ($section && isset($this->stylesheets[$section])) {
            $stylesheets = array_merge($this->stylesheets[''], $this->stylesheets[$section]);
        }
        else {
            $stylesheets = $this->stylesheets[''];
        }
        $smarty = smarty_core();
        $smarty->assign('user', $this->get('user'));
        $smarty->assign('rootpath', $rootpath);
        $smarty->assign('export_time', $this->exporttime);
        $smarty->assign('sitename', get_config('sitename'));
        $smarty->assign('stylesheets', $stylesheets);
        $smarty->assign('maharalogo', $rootpath . $this->theme_path('images/logo.png'));

        return $smarty;
    }

    /**
     * Converts a relative path to a static file that the HTML export theme 
     * should have, to a path in the static export where the file will reside.
     *
     * This returns the path in the most appropriate theme.
     */
    private function theme_path($path) {
        $themestaticdirs = theme_get_path('', 'export/html/', true);
        foreach ($themestaticdirs as $theme => $dir) {
            if (is_readable($dir . $path)) {
                return 'static/theme/' . $theme . '/static/' . $path;
            }
        }
    }

    /**
     * Converts the passed text into a a form that could be used in a URL.
     *
     * @param string $text The text to convert
     * @return string      The converted text
     */
    public static function text_to_path($text) {
        return substr(preg_replace('#[^a-zA-Z0-9_-]+#', '-', $text), 0, 255);
    }

    /**
     * Sanitises a string meant to be used as a filesystem path.
     *
     * Mahara allows file/folder artefact names to have slashes in them, which 
     * aren't legal on most real filesystems.
     */
    public static function sanitise_path($path) {
        return substr(str_replace('/', '_', $path), 0, 255);
    }


    private function build_index_page($summaries) {
        $smarty = $this->get_smarty();
        $smarty->assign('page_heading', full_name($this->get('user')));
        $smarty->assign('summaries', $summaries);
        $content = $smarty->fetch('export:html:index.tpl');
        if (!file_put_contents($this->exportdir . '/' . $this->rootdir . '/index.html', $content)) {
            throw new SystemException("Could not create index.html for the export");
        }
    }

    /**
     * Dumps all views into the HTML export
     */
    private function dump_view_export_data() {
        $rootpath = ($this->exportingoneview) ? './' : '../../';
        $smarty = $this->get_smarty($rootpath);
        foreach ($this->views as $viewid => $view) {
            $smarty->assign('page_heading', $view->get('title'));
            $smarty->assign('viewdescription', $view->get('description'));

            if ($this->exportingoneview) {
                $smarty->assign('nobreadcrumbs', true);
                $directory = $this->exportdir . '/' . $this->rootdir;
            }
            else {
                $smarty->assign('breadcrumbs', array(
                    array('text' => get_string('Views', 'view')),
                    array('text' => $view->get('title'), 'path' => 'index.html'),
                ));
                $directory = $this->exportdir . '/' . $this->rootdir . '/views/' . self::text_to_path($view->get('title'));
                if (!check_dir_exists($directory)) {
                    throw new SystemException("Could not create directory for view $viewid");
                }
            }

            $outputfilter = new HtmlExportOutputFilter($rootpath);
            $smarty->assign('view', $outputfilter->filter($view->build_columns()));
            $content = $smarty->fetch('export:html:view.tpl');
            if (!file_put_contents("$directory/index.html", $content)) {
                throw new SystemException("Could not write view page for view $viewid");
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
                    'folder' => self::text_to_path($view->get('title')),
                );
            }
        }
        $smarty->assign('views', $views);

        if ($views) {
            $stryouhaveviews = (count($views) == 1)
                ? get_string('youhaveoneview', 'view')
                : get_string('youhaveviews', 'view', count($views));
        }
        else {
            $stryouhaveviews = get_string('youhavenoviews', 'view');
        }
        $smarty->assign('stryouhaveviews', $stryouhaveviews);

        return array(
            'title' => get_string('Views', 'view'),
            'description' => $smarty->fetch('export:html:viewsummary.tpl'),
        );
    }

    /**
     * Copies the static files (stylesheets etc.) into the export
     */
    private function copy_static_files() {
        require_once('file.php');
        $staticdir = $this->get('exportdir') . '/' . $this->get('rootdir') . '/static/';
        $directoriestocopy = array();

        // Get static directories from each theme for HTML export
        $themestaticdirs = theme_get_path('', 'export/html/', true);
        foreach ($themestaticdirs as $theme => $dir) {
            $themedir = $staticdir . 'theme/' . $theme . '/static/';
            $directoriestocopy[$dir] = $themedir;
            if (!check_dir_exists($themedir)) {
                throw new SystemException("Could not create theme directory for theme $theme");
            }
        }

        // Smilies
        $directoriestocopy[get_config('docroot') . 'js/tinymce/plugins/emotions/images'] = $staticdir . 'smilies/';

        $filestocopy = array(
            get_config('docroot') . 'theme/views.css' => $staticdir . 'views.css',
        );

        foreach ($this->pluginstaticdirs as $dir) {
            $destinationdir = str_replace('export/html/', '', $dir);
            if (!check_dir_exists($staticdir . $destinationdir)) {
                throw new SystemException("Could not create static directory $destinationdir");
            }
            $directoriestocopy[get_config('docroot') . 'artefact/' . $dir] = $staticdir . $destinationdir;
        }

        foreach ($directoriestocopy as $from => $to) {
            if (!copyr($from, $to)) {
                throw new SystemException("Could not copy $from to $to");
            }
        }

        foreach ($filestocopy as $from => $to) {
            if (!copy($from, $to)) {
                throw new SystemException("Could not copy static file $from");
            }
        }
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
            throw new SystemException("Could not create the temporary export directory $this->fileroot");
        }
    }

    abstract public function dump_export_data();

    abstract public function get_summary();

    abstract public function get_summary_weight();

}

/**
 * Provides a mechanism for converting the HTML generated by views and 
 * artefacts for the HTML export.
 *
 * Mostly, this means rewriting links to artefacts to point the correct place 
 * in the export.
 */
class HtmlExportOutputFilter {

    /**
     * The relative path to the root of the generated export - used for link munging
     */
    private $basepath = '';

    /**
     * A cache of view titles. See replace_view_link()
     */
    private $viewtitles = array();

    /**
     * A cache of folder data. See get_path_for_file()
     */
    private $folderdata = null;

    /**
     * @param string $basepath The relative path to the root of the generated export
     */
    public function __construct($basepath) {
        $this->basepath = preg_replace('#/$#', '', $basepath);
    }

    /**
     * Filters the given HTML for HTML export purposes
     *
     * @param string $html The HTML to filter
     * @return string      The filtered HTML
     */
    public function filter($html) {
        $wwwroot = preg_quote(get_config('wwwroot'));
        $html = preg_replace(
            array(
                // We don't care about javascript
                '#<script[^>]*>.*?</script>#si',
                // Fix simlies from tinymce
                '#<img src="(' . $wwwroot . ')?/?js/tinymce/plugins/emotions/images/([^"]+)"([^>]+)>#',
                // No forms
                '#<form[^>]*>.*?</form>#si',
                // Gratuitous hack for the RSS blocktype
                '#<div id="blocktype_externalfeed_lastupdate">[^<]*</div>#',
            ),
            array(
                '',
                '<img src="' . $this->basepath . '/static/smilies/$2"$3>',
                '',
                '',
            ),
            $html
        );

        // Links to views
        $html = preg_replace_callback(
            '#' . $wwwroot . 'view/view\.php\?id=(\d+)#',
            array($this, 'replace_view_link'),
            $html
        );

        // Links to artefacts
        $html = preg_replace_callback(
            '#<a[^>]+href="(' . preg_quote(get_config('wwwroot')) . ')?/?view/artefact\.php\?artefact=(\d+)(&amp;view=\d+)?(&amp;page=\d+)?"[^>]*>([^<]*)</a>#',
            array($this, 'replace_artefact_link'),
            $html
        );

        // Links to download files
        $html = preg_replace_callback(
            '#(' . preg_quote(get_config('wwwroot')) . ')?/?artefact/file/download\.php\?file=(\d+)(&amp;view=\d+)?#',
            array($this, 'replace_download_link'),
            $html
        );

        return $html;
    }

    /**
     * Callback to replace links to views to point to the correct location in 
     * the HTML export
     */
    private function replace_view_link($matches) {
        $viewid = $matches[1];
        if (!isset($this->viewtitles[$viewid])) {
            $this->viewtitles[$viewid] = PluginExportHtml::text_to_path(get_field('view', 'title', 'id', $viewid));
        }
        return $this->basepath . '/views/' . $this->viewtitles[$viewid] . '/index.html';
    }

    /**
     * Callback to replace links to artefact to point to the correct location 
     * in the HTML export
     */
    private function replace_artefact_link($matches) {
        $artefactid = $matches[2];
        $artefact = artefact_instance_from_id($artefactid);

        switch ($artefact->get('artefacttype')) {
        case 'blog':
            $page = ($matches[4]) ? intval(substr($matches[4], strlen('&amp;page='))) : 1;
            $page = ($page == 1) ? 'index' : $page;
            return '<a href="' . $this->basepath . '/files/blog/' . PluginExportHtml::text_to_path($artefact->get('title')) . '/' . $page . '.html">' . $matches[5] . '</a>';
        case 'file':
        case 'image':
            $folderpath = $this->get_path_for_file($artefact);
            return '<a href="' . $this->basepath . '/files/file/' . $folderpath . PluginExportHtml::sanitise_path($artefact->get('title')) . '">' . $matches[5] . '</a>';
        default:
            return $matches[5];
        }
    }

    /**
     * Callback to replace links to artefact/file/download.php to point to the 
     * correct file in the HTML export
     */
    private function replace_download_link($matches) {
        $artefactid = $matches[2];
        $artefact = artefact_instance_from_id($artefactid);

        // If artefact type not something that would be served by download.php, 
        // replace link with nothing
        if ($artefact->get_plugin_name() != 'file') {
            return '';
        }

        $folderpath = $this->get_path_for_file($artefact);
        return $this->basepath . '/files/file/' . $folderpath . $artefact->get('title');
    }

    /**
     * Given a file, returns the folder path in the HTML export for it
     *
     * TODO: slash escaping in file/folder names
     *
     * @param ArtefactTypeFileBase $file The file to get the folder path for
     * @return string
     */
    private function get_path_for_file(ArtefactTypeFileBase $file) {
        if ($this->folderdata === null) {
            $this->folderdata = get_records_select_assoc('artefact', "artefacttype = 'folder' AND owner = ?", array($file->get('owner')));
            foreach ($this->folderdata as &$folder) {
                $folder->title = PluginExportHtml::sanitise_path($folder->title);
            }
        }
        $folderpath = ArtefactTypeFileBase::get_full_path($file->get('parent'), $this->folderdata);
        return $folderpath;
    }

}

?>
