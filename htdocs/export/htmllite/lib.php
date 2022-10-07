<?php
/**
 * HTML Lite plugin
 *
 * @package    mahara
 * @subpackage export-html-lite
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

require_once(get_config('docroot') . 'export/html/lib.php');

/**
 * HtmlLite export plugin
 */
class PluginExportHtmlLite extends PluginExportHtml {

    /**
     * The list of valid files to include in the export as additional files
     */
    protected $validfiles = array('doc','docx','sxw','pdf','txt','rtf','html','htm','wps','odt','pages','xls','xlsx','ps','hwp');

   /**
    * These javascript files will be included in index.html via the
    * export/html/templates/header.tpl
    */
   protected $scripts = array();


    /**
     * The array of Collections.
     */
    protected $collections = array();

    /**
     * {@inheritDoc}
     */
    public function __construct(User $user, $views, $artefacts, $progresscallback=null, $loop=1, $looptotal=1, $exporttime=null) {
        global $THEME;
        parent::__construct($user, $views, $artefacts, $progresscallback, $loop, $looptotal, $exporttime);
        $this->rootdir = 'HTML_Lite';
        $this->exporttype = 'htmllite';

        // Create basic required directories
        foreach (array('views') as $directory) {
            $directory = "{$this->exportdir}/{$this->rootdir}/{$directory}/";
            if (!check_dir_exists($directory)) {
                throw new SystemException("Couldn't create the temporary export directory $directory");
            }
        }

        $directory = "{$this->exportdir}{$this->filedir}";
        if (!check_dir_exists($directory)) {
            throw new SystemException("Couldn't create the attachment export directory $directory");
        }

        $this->zipfile = 'mahara-export-htmllite-user'
        . $this->get('user')->get('id') . '-' . $this->exporttime . '.zip';

        // Don't export the dashboard
        foreach (array_keys($this->views) as $i) {
            if ($this->views[$i]->get('type') == 'dashboard') {
                unset($this->views[$i]);
            }
        }

        $this->exportingoneview = (
            $this->viewexportmode == PluginExport::EXPORT_LIST_OF_VIEWS &&
            $this->artefactexportmode == PluginExport::EXPORT_ARTEFACTS_FOR_VIEWS &&
            count($this->views) == 1
        );

        $this->notify_progress_callback(15, get_string('setupcomplete', 'export'));
    }

    /**
     * A human-readable title for the export
     *
     * @return string
     */
    public static function get_title() {
        return get_string('title1', 'export.htmllite');
    }

    /**
     * A human-readable description for the export
     *
     * @return string
     */
    public static function get_description() {
        return get_string('description', 'export.htmllite');
    }

    /**
     * Is the plugin activated or not?
     *
     * @return boolean
     */
    public static function is_active() {
        $active = false;
        if (get_field('export_installed', 'active', 'name', 'htmllite') && parent::is_active()) {
            $active = true;
        }
        return $active;
    }

    /**
     * Fetch plugin's display name rather than plugin name that is based on dir name.
     *
     * @return string
     */
    public static function get_plugin_display_name() {
        return 'HTML lite';
    }

    /**
     * Post install hook
     */
    public static function postinst($fromversion) {
        if ($fromversion == 0) {
            set_field('export_installed', 'active', 0, 'name', 'htmllite');
        }
        return true;
    }

    /**
     * Main export routine
     *
     * We can specify whether to make a zipfile now or later on, i.e. in PluginExportAll
     * which creates a zipfile of all export formats. Note: If running pdf export and html
     * export together then this should be run first
     *
     * @param $createarchive boolean whether to create zipfile
     */
    public function export($createarchive=false) {
        parent::export(false);
        $this->htmllite_view_export_data();
        if (!$createarchive) {
            return array(
                'exportdir' => $this->exportdir,
                'zipfile' => $this->zipfile,
                'dirs' => array($this->rootdir, $this->infodir),
            );
        }
        // zip everything up
        $this->notify_progress_callback(90, get_string('creatingzipfile', 'export'));
        try {
            create_zip_archive($this->exportdir, $this->zipfile, array($this->rootdir, $this->infodir));
        }
        catch (SystemException $e) {
            throw new SystemException('Failed to zip the export file: ' . $e->getMessage());
        }
        $this->notify_progress_callback(100, get_string('Done', 'export'));
        return $this->zipfile;
    }

    /**
     * Build the smarty call for the page
     *
     * This is for building the pages we need for the export
     *
     * @param   string $rootpath the relative path to assets
     * @param   string $section the artefact type
     *
     * @return Dwoo $smarty object
     */
    public function get_smarty($rootpath='', $section='') {
        $smarty = smarty_core();
        $smarty->assign('user', $this->get('user'));
        $smarty->assign('rootpath', $rootpath);
        $smarty->assign('export_time', $this->exporttime);
        $smarty->assign('sitename', get_config('sitename'));
        $smarty->assign('WWWROOT', get_config('wwwroot'));
        $smarty->assign('nobreadcrumbs', true);
        $smarty->assign('htmllite', true);
        $htmldir = ($this->exportingoneview ? '' : $this->rootdir . '/');
        $smarty->assign('htmldir', $htmldir);
        if ($this->exportingoneview) {
            $smarty->assign('scriptspath', $rootpath . $this->theme_path('js/'));
        }
        else {
            $smarty->assign('scriptspath', $rootpath . $this->rootdir . '/' . $this->theme_path('js/'));
        }
        $smarty->assign('exportingoneview', $this->exportingoneview);
        return $smarty;
    }

    /**
     * Dumps all views into the HTML export
     */
    protected function dump_view_export_data() {
        safe_require('artefact', 'comment');
        $progressstart = 55;
        $progressend   = 75;
        $i = 0;
        $viewcount = count($this->views);
        $rootpath = $this->get_root_path();
        $content = '';
        $smarty = $this->get_smarty($rootpath);
        // override this so that HtmlExportOutputFilter can do what we want
        $exportoneview = $this->exportingoneview;
        $this->exportingoneview = true;
        foreach ($this->views as $viewid => $view) {
            if ($view->get('type') != 'portfolio') {
                continue;
            }
            $this->notify_progress_callback(intval($progressstart + (++$i / $viewcount) * ($progressend - $progressstart)), get_string('exportingviewsprogresshtml', 'export', $i, $viewcount));
            $smarty->assign('page_heading', ''); // we only set this for a collection - see below
            $smarty->assign('subpage_heading', $view->get('title'));
            $smarty->assign('viewdescription', $view->get('description'));
            $smarty->assign('nobreadcrumbs', true);
            // Collection menu data
            $exportingcollections = ($this->viewexportmode == PluginExport::EXPORT_LIST_OF_COLLECTIONS);
            $exportingviews = ($this->viewexportmode == PluginExport::EXPORT_ALL_VIEWS_COLLECTIONS);
            if (isset($this->viewcollection[$viewid])
                && ($exportingcollections || $exportingviews)
                ) {
                // @phpstan-ignore-next-line
                $smarty->assign('page_heading', $this->collections[$this->viewcollection[$viewid]]->get('name'));
            }
            $outputfilter = new HtmlExportOutputFilter($this);
            // Include comments
            if ($this->includefeedback) {
                $commentoptions = ArtefactTypeComment::get_comment_options();
                $commentoptions->view = $view;
                $commentoptions->limit = 0;
                $commentoptions->export = true;
                if (!$this->includeprivatefeedback) {
                    $commentoptions->privatefeedback = false;
                }
                if ($feedback = ArtefactTypeComment::get_comments($commentoptions)) {
                    $feedback->tablerows = $outputfilter->filter($feedback->tablerows);
                }
                $smarty->assign('viewfeedback', $feedback);
                $smarty->assign('viewartefactsfeedback', $this->get_blocks_artefacts_feedback($view, $commentoptions, $outputfilter));
            }
            if (!$view->uses_new_layout()) {
                $smarty->assign('blockcontent', $outputfilter->filter($view->build_rows(false, $this->exporttype)));
            }
            else {
                $blockcontent = '';
                $blocks = $view->get_blocks(false, $this->exporttype);
                if ($blocks) {
                    foreach ($blocks as $bk => $bv) {
                        if (isset($blocks[$bk]['content'])) {
                            $blocks[$bk]['content'] = $outputfilter->filter($blocks[$bk]['content']);
                            $blockcontent .= $blocks[$bk]['content'];
                        }
                    }
                }
                $smarty->assign('blockcontent', $blockcontent);
            }
            $content .= $smarty->fetch('export:html:htmllite_view_content.tpl');
        }
        $directory = $this->exportdir . '/' . $this->rootdir . '/';
        $smarty->assign('content', $content);
        $pagecontent = $smarty->fetch('export:html:htmllite_view.tpl');
        if (!file_put_contents("$directory/index.html", $pagecontent)) {
            throw new SystemException("Could not write view page");
        }
        $this->exportingoneview = $exportoneview; // set the override back to original state
    }

    protected function build_index_page($summaries) {
        // ignore saving the index page
    }

    protected function collection_menu($collectionid) {
        // ignore making collection nav
        return array();
    }

    protected function export_progresscompletion_pages() {
        // ignore saving the progress page
    }

    protected function export_artefact_metadata_modals() {
        // ignore saving the modal popups
    }

    protected function copy_static_files() {
        // ignore saving the static theme files
    }

    protected function htmllite_view_export_data() {
        // Sort out the files we want to keep
        $subdirpaths = array();
        $exportfilesdir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->exportdir . $this->filedir), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($exportfilesdir as $name => $contentdir) {
            if ($contentdir->getFilename() === '.' || $contentdir->getFilename() === '..') continue;
            if ($contentdir->getFilename() === 'index.html' || !in_array($contentdir->getExtension(), $this->validfiles)) {
                if (is_dir($name)) {
                    $subdirpaths[$name] = 1;
                    continue;
                }
                unlink($name);
            }
            else {
                rename($name, $this->exportdir . $this->infodir . '/' . $contentdir->getFilename());
            }
        }
        // now deal with any empty directories
        $subdirpaths[$this->exportdir . $this->filedir] = 1;
        foreach ($subdirpaths as $subkey => $subpath) {
            if (file_exists($subkey) && is_dir($subkey)) {
                rmdirr($subkey);
            }
        }
        // now deal with directories we don't need
        $excludedirs = array('content', 'views');
        foreach ($excludedirs as $dir) {
            $dirpath = $this->exportdir . $this->rootdir . '/' . $dir;
            rmdirr($dirpath);
        }
    }
}
