<?php
/**
 *
 * @package    mahara
 * @subpackage export-html
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
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
     * The name of the directory under which all the other directories
     * will be placed in the export
     */
    protected $rootdir;

    /**
    * The name of the directory under which shared folders such as files will
    * be placed in the export
    **/
    protected $infodir = 'export_info';

    /**
    * The name of the directory where files will be placed in the export
    **/
    protected $filedir = 'export_info/files/';

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
     * These javascript files will be included in index.html via the
     * export/html/templates/header.tpl
     */
    private $scripts = array('jquery', 'popper.min', 'bootstrap.min', 'dock', 'modal', 'lodash', 'gridstack', 'gridlayout', 'masonry.min', 'select2.full', 'theme');

    /**
    * constructor.  overrides the parent class
    * to set up smarty and the attachment directory
    */
    public function __construct(User $user, $views, $artefacts, $progresscallback=null) {
        global $THEME;
        parent::__construct($user, $views, $artefacts, $progresscallback);
        $this->rootdir = 'HTML';
        $this->exporttype = 'html';

        // Create basic required directories
        foreach (array('views', 'static', 'static/profileicons') as $directory) {
            $directory = "{$this->exportdir}/{$this->rootdir}/{$directory}/";
            if (!check_dir_exists($directory)) {
                throw new SystemException("Couldn't create the temporary export directory $directory");
            }
        }

        $directory = "{$this->exportdir}{$this->filedir}";
        if (!check_dir_exists($directory)) {
            throw new SystemException("Couldn't create the attachment export directory $directory");
        }

        $this->zipfile = 'mahara-export-user'
        . $this->get('user')->get('id') . '-' . date('Y-m-d_H-i', $this->exporttime) . '.zip';


        // Find what stylesheets need to be included
        $themedirs = $THEME->get_path('', true);
        foreach ($themedirs as $theme => $themedir) {
            if (is_readable($themedir . 'style/')) {
                $files = scandir($themedir . 'style/');
                foreach ($files as $stylesheet) {
                    if (substr_count($stylesheet, '.css') > 0) {
                        array_unshift($this->stylesheets[''], 'theme/' . $theme . '/static/style/' . $stylesheet);
                    }
                }
            }
        }
        // Find what export plugin stylesheets need to be included
        $exportthemedirs = $THEME->get_path('', true, 'export/html');
        foreach ($exportthemedirs as $theme => $themedir) {
            if (is_readable($themedir . 'style/')) {
                $files = scandir($themedir . 'style/');
                foreach ($files as $stylesheet) {
                    if (substr_count($stylesheet, '.css') > 0) {
                        array_unshift($this->stylesheets[''], 'theme/' . $theme . '/static/export/style/' . $stylesheet);
                    }
                }
            }
        }

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

    public static function get_title() {
        return get_string('title', 'export.html');
    }

    public static function get_description() {
        return get_string('description', 'export.html');
    }

    public function is_diskspace_available() {
        return true; // need to create a check here
    }

    public function get_root_path($depth=1, $type=null) {
        $parent = str_repeat('../', $depth);
        if ($this->exportingoneview && $type) {
            return $parent . $this->get('infodir') . '/' . $type . '/';
        }
        else if ($this->exportingoneview) {
            return $parent . 'HTML/';
        }
        else if ($type) {
            return $parent . $type;
        }
        return $parent;
    }

    /**
     * Main export routine
     * @param $createarchive Boolean specifies whether a zipfile will be created here
     * or later on, i.e. in PluginExportAll which creates a zipfile of all export formats.
     */
    public function export($createarchive=false) {
        global $THEME, $SESSION;
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
            $this->notify_progress_callback(intval($progressstart + (++$i / $plugincount) * ($progressend - $progressstart)), get_string('preparing', 'export.html', $plugin));

            if (safe_require('export', 'html/' . $plugin, 'lib.php', 'require_once', true)) {
                $exportplugins[] = $plugin;

                $classname = 'HtmlExport' . ucfirst($plugin);
                if (!is_subclass_of($classname, 'HtmlExportArtefactPlugin')) {
                    throw new SystemException("Class $classname does not extend HtmlExportArtefactPlugin as it should");
                }

                safe_require('artefact', $plugin);

                // Find out whether the plugin has static data for us
                $themestaticdirs = array_reverse($THEME->get_path('', true, 'artefact/' . $plugin . '/export/html'));
                foreach ($themestaticdirs as $dir) {
                    $staticdir = substr($dir, strlen(get_config('docroot') . 'artefact/'));
                    $this->pluginstaticdirs[] = $staticdir;
                    foreach (array('style.css') as $stylesheet) {
                        if (is_readable($dir . 'style/' . $stylesheet)) {
                            $this->stylesheets[$plugin][] = str_replace('export/html/', '', $staticdir) . 'style/' . $stylesheet;
                        }
                    }
                }
            }
        }

        // Second pass: actually dump data for active export plugins
        $progressstart = 25;
        $progressend   = 50;
        $i = 0;
        foreach ($exportplugins as $plugin) {
            $this->notify_progress_callback(intval($progressstart + (++$i / $plugincount) * ($progressend - $progressstart)), get_string('exportingdatafor', 'export.html', $plugin));
            $classname = 'HtmlExport' . ucfirst($plugin);
            $artefactexporter = new $classname($this);
            $artefactexporter->dump_export_data();
            // If just exporting a list of views, we don't care about the summaries for each artefact plugin
            if (!(($this->viewexportmode == PluginExport::EXPORT_LIST_OF_VIEWS || $this->viewexportmode == PluginExport::EXPORT_LIST_OF_COLLECTIONS)
                && $this->artefactexportmode == PluginExport::EXPORT_ARTEFACTS_FOR_VIEWS)) {
                $summaries[$plugin] = array($artefactexporter->get_summary_weight(), $artefactexporter->get_summary());
            }
        }

        // Views in collections
        if (!$this->exportingoneview && $this->collections) {
            $viewlist = join(',', array_keys($this->views));
            $collectionlist = join(',', array_keys($this->collections));
            $records = get_records_select_array(
                'collection_view',
                "view IN ($viewlist) AND collection IN ($collectionlist)"
            );
            if ($records) {
                foreach ($records as &$r) {
                    $this->collectionview[$r->collection][] = $r->view;
                    $this->viewcollection[$r->view] = $r->collection;
                }
            }
        }

        // Get the view data
        $this->notify_progress_callback(55, get_string('exportingviews', 'export'));
        $this->dump_view_export_data();

        $this->export_progresscompletion_pages();

        $this->export_artefact_metadata_modals();

        if (!$this->exportingoneview) {
            $viewcollectionsumary = $this->get_view_collection_summary();
            $summaries['view'] = array(100, $viewcollectionsumary['view']);
            $summaries['collection'] = array(110, $viewcollectionsumary['collection']);

            // Sort by weight (then drop the weight information)
            uasort($summaries, function ($a, $b) {
                return $a[0] > $b[0];
            });
            foreach ($summaries as &$summary) {
                $summary = $summary[1];
            }

            // Build index.html
            $this->notify_progress_callback(75, get_string('buildingindexpage', 'export.html'));
            $this->build_index_page($summaries);
        }

        // Copy all static files into the export
        $this->notify_progress_callback(80, get_string('copyingextrafiles', 'export.html'));
        $this->copy_static_files();

        // Copy all resized images that were found while rewriting the HTML
        $copyproxy = HtmlExportCopyProxy::singleton();
        $copydata = $copyproxy->get_copy_data();
        foreach ($copydata as $from => $to) {
            $to = ltrim($to, './');
            $to = $this->get('exportdir') . $to;
            if (!check_dir_exists(dirname($to))) {
                $SESSION->add_error_msg(get_string('couldnotcreatedirectory', 'export', $to));
            }
            if (!copy($from, $to)) {
                $SESSION->add_error_msg(get_string('couldnotcopystaticfile', 'export', $from));
            }
        }

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
            create_zip_archive($this->exportdir, $this->zipfile, array($this->rootdir));
        }
        catch (SystemException $e) {
            throw new SystemException('Failed to zip the export file: ' . $e->getMessage());
        }
        $this->notify_progress_callback(100, get_string('Done', 'export'));
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
        $smarty->assign('WWWROOT', get_config('wwwroot'));
        $htmldir = ($this->exportingoneview ? '' : 'HTML/');
        $scripts = [];
        foreach($this->scripts as $i => $script) {
            // theme should be in theme folder use,
            // the rest of the scripts don't depend on the theme
            if ($script == 'theme') {
                $scripts[$i] = $rootpath . $htmldir . $this->theme_path('js/') .  $script . '.js';
            }
            else {
                $scripts[$i] = $rootpath . $htmldir . 'static/theme/raw/static/js/' . $script . '.js';
            }
        }
        $smarty->assign('scripts', $scripts);

        if ($this->exportingoneview) {
            $smarty->assign('scriptspath', $rootpath . $this->theme_path('js/'));
        }
        else {
            $smarty->assign('scriptspath', $rootpath . 'HTML/' . $this->theme_path('js/'));
        }
        $smarty->assign('maharalogo', $this->theme_path('images/site-logo.png'));
        $smarty->assign('maharalogosmall', $this->theme_path('images/site-logo-small.png'));
        $smarty->assign('exportingoneview', $this->exportingoneview);

        return $smarty;
    }

    /**
     * Converts a relative path to a static file that the HTML export theme
     * should have, to a path in the static export where the file will reside.
     *
     * This returns the path in the most appropriate theme.
     */
    private function theme_path($path) {
        global $THEME;
        $themestaticdirs = $THEME->get_path('', true);
        foreach ($themestaticdirs as $theme => $dir) {
            if (is_readable($dir . $path)) {
                return 'static/theme/' . $theme . '/static/' . $path;
            }
        }
    }

    /**
     * Converts the UTF-8 passed text into a a form that could be used in a file name.
     *
     * @param string $text The text to convert
     * @return string      The converted text
     */
    public static function text_to_filename($text) {
        // truncates the text and replaces NOT allowed characters to hyphens
        return preg_replace('#["()*/:<>?\\| ]+#', '-', mb_substr($text, 0, parent::MAX_FILENAME_LENGTH, 'utf-8'));
    }

    /**
     * Converts the UTF-8 passed text into a a form that could be used in a URL.
     *
     * @param string $text The text to convert
     * @return string      The converted text
     */
    public static function text_to_URLpath($text) {
        $tab_text = str_split($text);
        $output = '';
        foreach ($tab_text as $id=>$char){
            $hex = dechex(ord($char));
            $output .= '%' . $hex;
        }
        return $output;
    }

    /**
     * Sanitises a string meant to be used as a filesystem path.
     *
     * Mahara allows file/folder artefact names to have slashes in them, which
     * aren't legal on most real filesystems.
     */
    public static function sanitise_path($path) {
        return trim(substr(str_replace('/', '_', $path), 0, 255));
    }


    private function build_index_page($summaries) {
        $smarty = $this->get_smarty($this->get_root_path());
        $smarty->assign('page_heading', full_name($this->get('user')));
        $smarty->assign('summaries', $summaries);
        $content = $smarty->fetch('export:html:index.tpl');
        if (!file_put_contents($this->exportdir . '/' . $this->rootdir . '/index.html', $content)) {
            throw new SystemException("Could not create index.html for the export");
        }
    }

    private function collection_menu($collectionid) {
        static $menus = array();
        if (!isset($menus[$collectionid])) {
            $menus[$collectionid] = array();
            if ($progresscompletion = $this->collections[$collectionid]->has_progresscompletion()) {
                $menus[$collectionid][] = array(
                    'id'   => 'progresscompletion',
                    'url'  => self::text_to_URLpath(self::text_to_filename($this->collections[$collectionid]->get('name') . '_progresscompletion')) . '/index.html',
                    'text' => get_string('progresscompletion', 'admin'),
                );
            }
            foreach ($this->collectionview[$collectionid] as $viewid) {
                $title = $this->views[$viewid]->get('title');
                $menus[$collectionid][] = array(
                    'id'   => $viewid,
                    'url'  => self::text_to_URLpath($viewid . '_' . self::text_to_filename($title)) . '/index.html',
                    'text' => $title,
                );
            }
        }
        return $menus[$collectionid];
    }

    /**
     * Dumps all collections progress completion pages into the HTML export
     */
    private function export_progresscompletion_pages() {
        $rootpath = ($this->exportingoneview) ? $this->get_root_path() : $this->get_root_path(3);
        $smarty = $this->get_smarty($rootpath);
        foreach ($this->collections as $collection) {
            if ($collection->has_progresscompletion()) {

                $directory = $this->exportdir . '/' . $this->rootdir . '/views/' . self::text_to_filename($collection->get('name') . '_progresscompletion');
                if (!check_dir_exists($directory)) {
                    throw new SystemException("Could not create directory for progress completion page from collection " . $collection->get('name'));
                }

                if ($this->viewexportmode == PluginExport::EXPORT_LIST_OF_COLLECTIONS
                        || $this->viewexportmode == PluginExport::EXPORT_ALL_VIEWS_COLLECTIONS) {
                    $smarty->assign('collectionname', $collection->get('name'));
                    $smarty->assign('collectionmenu', $this->collection_menu($collection->get('id')));
                }
                $views = $collection->get('views');
                $firstview = $views['views'][0];
                $view = new View($firstview->id);
                $smarty->assign('maintitle', $collection->get('name'));
                $smarty->assign('name', get_string('portfoliocompletion', 'collection'));
                $smarty->assign('author', $view->display_author());
                // progress bar
                $collectionowner = new User();
                $collectionowner->find_by_id($collection->get('owner'));
                $displayname = display_name($collectionowner);
                $smarty->assign('quotamessage', get_string('overallcompletion', 'collection', $displayname));
                list($completedactionspercentage, $totalactions) = $collection->get_signed_off_and_verified_percentage();
                $smarty->assign('completedactionspercentage', $completedactionspercentage);

                // table
                foreach ($views['views'] as &$view) {
                    $viewobj = new View($view->id);
                    $owneraction = $viewobj->get_progress_action('owner');
                    $manageraction = $viewobj->get_progress_action('manager');

                    $view->ownericonclass = $owneraction->get_icon();
                    $view->ownertitle = $owneraction->get_title();
                    $view->signedoff = ArtefactTypePeerassessment::is_signed_off($viewobj);

                    $view->managericonclass = $manageraction->get_icon();
                    $view->managertitle = $manageraction->get_title();
                    $view->verified = ArtefactTypePeerassessment::is_verified($viewobj);
                    $view->fullurl = '../' . $viewobj->get('id') . '_' . self::text_to_filename($viewobj->get('title')) . '/index.html';
                }
                $smarty->assign('page_heading', get_string('portfoliocompletion', 'collection'));
                $smarty->assign('views', $views['views']);

                $content = $smarty->fetch('export:html:progresscompletion.tpl');
                if (!file_put_contents("$directory/index.html", $content)) {
                    throw new SystemException("Could not write view page for view $viewid");
                }
            }
        }
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
        $rootpath = ($this->exportingoneview) ? $this->get_root_path() : $this->get_root_path(3);
        $smarty = $this->get_smarty($rootpath);
        foreach ($this->views as $viewid => $view) {
            $this->notify_progress_callback(intval($progressstart + (++$i / $viewcount) * ($progressend - $progressstart)), get_string('exportingviewsprogresshtml', 'export', $i, $viewcount));
            $smarty->assign('page_heading', $view->get('title'));
            $smarty->assign('viewdescription', $view->get('description'));
            $smarty->assign('viewinstructions', $view->get('instructions'));

            if ($this->exportingoneview) {
                $smarty->assign('nobreadcrumbs', true);
                $directory = $this->exportdir . '/' . $this->rootdir . '/';
            }
            else {
                $smarty->assign('breadcrumbs', array(
                    array('text' => get_string('Views', 'view')),
                    array('text' => $view->get('title'), 'path' => 'index.html'),
                ));
                $directory = $this->exportdir . '/' . $this->rootdir . '/views/' . $view->get('id') . '_' . self::text_to_filename($view->get('title'));
                if (!check_dir_exists($directory)) {
                    throw new SystemException("Could not create directory for view $viewid");
                }
            }

            // Collection menu data
            if (isset($this->viewcollection[$viewid])
                && ($this->viewexportmode == PluginExport::EXPORT_LIST_OF_COLLECTIONS
                    || $this->viewexportmode == PluginExport::EXPORT_ALL_VIEWS_COLLECTIONS)) {
                $smarty->assign('collectionname', $this->collections[$this->viewcollection[$viewid]]->get('name'));
                $smarty->assign('collectionmenu', $this->collection_menu($this->viewcollection[$viewid]));
                $smarty->assign('viewid', $viewid);
            }
            else {
                $smarty->assign('collectionname', false);
                $smarty->assign('collectionmenu', false);
                $smarty->assign('viewid', false);
            }

            $outputfilter = new HtmlExportOutputFilter($rootpath, $this);

            // Include comments
            if ($this->includefeedback) {
                $commentoptions = ArtefactTypeComment::get_comment_options();
                $commentoptions->view = $view;
                $commentoptions->limit = 0;
                $commentoptions->export = true;
                if ($feedback = ArtefactTypeComment::get_comments($commentoptions)) {
                    $feedback->tablerows = $outputfilter->filter($feedback->tablerows);
                }
                $smarty->assign('feedback', $feedback);
            }
            else {
                $smarty->assign('feedback', false);
            }

            if (!$view->uses_new_layout()) {
                $smarty->assign('view', $outputfilter->filter($view->build_rows(false, $this->exporttype)));
                $smarty->assign('newlayout', false);
                $smarty->assign('blocks', false);
            }
            else {
                $blocks = $view->get_blocks(false, $this->exporttype);
                if ($blocks) {
                    foreach ($blocks as $bk => $bv) {
                        if (isset($blocks[$bk]['content'])) {
                            $blocks[$bk]['content'] = $outputfilter->filter($blocks[$bk]['content']);
                        }
                    }
                }
                $smarty->assign('newlayout', true);
                $smarty->assign('blocks', $blocks);
                $smarty->assign('view', false);
            }

            $content = $smarty->fetch('export:html:view.tpl');
            if (!file_put_contents("$directory/index.html", $content)) {
                throw new SystemException("Could not write view page for view $viewid");
            }
        }
    }

    /**
     * Returns a summary about views and/or collections
     *
     * @return array(
     *      'view' => array(
     *          'title' => ...
     *          'description' => ...
     *      )
     *      'collection' => array(
     *          'title' => ...
     *          'description' => ...
     *      )
     */
    protected function get_view_collection_summary() {

        $list = array();
        foreach ($this->collections as $id => $collection) {
            $list['collections'][$id] = array(
                'title' => $collection->get('name'),
                'views' => array(),
            );
            if ($progresscompletion = $collection->has_progresscompletion()) {
                $list['collections'][$id]['progresscompletion'] = true;
                $list['collections'][$id]['progresscompletionfolder'] = self::text_to_filename($collection->get('name') . '_progresscompletion');
            }
        }

        $ncollections = count($this->collections);
        $nviews = 0;
        foreach ($this->views as $id => $view) {
            if ($view->get('type') != 'profile') {
                $item = array(
                    'id' => $view->get('id'),
                    'title' => $view->get('title'),
                    'folder' => $view->get('id') . '_' . self::text_to_filename($view->get('title')),
                );
                if (isset($this->viewcollection[$id])
                    && ($this->viewexportmode == self::EXPORT_ALL_VIEWS_COLLECTIONS
                        || $this->viewexportmode == self::EXPORT_LIST_OF_COLLECTIONS)) {
                    $list['collections'][$this->viewcollection[$id]]['views'][$id] = $item;
                }
                else {
                    $list['views'][$id] = $item;
                    $nviews++;
                }
            }
        }

        foreach (array_keys($this->collections) as $id) {
            usort($list['collections'][$id]['views'], 'sort_by_title');
        }
        if ($nviews) {
            usort($list['views'], 'sort_by_title');
        }
        if ($ncollections) {
            usort($list['collections'], 'sort_by_title');
        }

        // View summary
        $summary['view'] = array();
        $smarty = $this->get_smarty('../');
        if (!empty($list['views'])
            && ($this->viewexportmode == self::EXPORT_ALL_VIEWS_COLLECTIONS
                || $this->viewexportmode == self::EXPORT_LIST_OF_VIEWS)) {
            $smarty->assign('stryouhaveviews', get_string('youhavenviews', 'view', $nviews, $nviews));
            $smarty->assign('list', $list['views']);
            $summary['view'] = array(
                'title' => get_string('Views', 'view'),
                'description' => $smarty->fetch('export:html:viewsummary.tpl'),
            );
        }

        // Collection summary
        $summary['collection'] = array();
        $smarty = $this->get_smarty('../');
        if (!empty($list['collections'])
            && ($this->viewexportmode == self::EXPORT_ALL_VIEWS_COLLECTIONS
                || $this->viewexportmode == self::EXPORT_LIST_OF_COLLECTIONS)) {
            $smarty->assign('stryouhavecollections', get_string('youhavencollections', 'collection', $ncollections, $ncollections));
            $smarty->assign('list', $list['collections']);
            $summary['collection'] = array(
                'title' => get_string('Collections', 'collection'),
                'description' => $smarty->fetch('export:html:collectionsummary.tpl'),
            );
        }

        return $summary;
    }


/** Retrieves the comments for a particular artefact
 * @param artefact $artefact    The artefact containing the comments
 * @param View $view            The view where the artefact appears
 * @return html via comment.tpl Containing comments or '' if comments aren't allowed
 */
    private function get_comments_for_modal($artefact, $view) {
         safe_require('artefact', 'comment');
         if (!$artefact->get('allowcomments')) {
             return '';
         }
         $commentoptions = ArtefactTypeComment::get_comment_options();
         $commentoptions->view = $view;
         $commentoptions->artefact = $artefact;

         $owner = $artefact->get('owner');
         $threaded = $owner ? $threaded = get_user_institution_comment_threads($owner) : false;
         $commentoptions->threaded = $threaded;
         $feedback = ArtefactTypeComment::get_comments($commentoptions);
         $smarty = smarty_core();
         $smarty->assign('feedback', $feedback);
         return $smarty->fetch('blocktype:comment:comment.tpl');
    }

/**
*  Creates the hard-coded modals for blogs posts (Journal block)
*  @param BlockInstance $bi  The journal block containing the posts
*  @param array &$idarray     Existing array that stores ids of modals to be created
*/
    private function get_blog_posts_modals(&$idarray, BlockInstance $bi) {
        require_once(get_config('docroot') . 'artefact/blog/blocktype/blog/lib.php');
        $artefacts = PluginBlocktypeBlog::get_artefacts($bi);
        if (!empty($artefacts)) {
            $idarray = array_merge($idarray, $artefacts);
        }
    }

/**
*   Creates the hard-coded modals for tagged posts (Tagged journal entries)
*  @param BlockInstance $bi  The tagged journal entries block containing the posts
*  @param array &$idarray     Existing array that stores ids of modals to be created
*/
    private function get_tagged_posts_modals(&$idarray, BlockInstance $bi) {
        require_once(get_config('docroot') . 'artefact/blog/blocktype/taggedposts/lib.php');
        $taggedposts = PluginBlocktypeTaggedposts::get_blog_posts_in_block($bi);
        $postids = array();
        foreach ($taggedposts as $posts) {
            array_push($postids, $posts->{'id'});
        }
        if (!empty($postids)) {
            $idarray = array_merge($idarray, $postids);
        }
    }

/**
*  Creates the hard-coded modals for recent posts (Recent journal entries)
* @param BlockInstance $bi     The recent journal entries block containing the posts
* @param array &$idarray       Exisiting array that stores ids of modals to be created
*/
    private function get_recent_posts_modals(&$idarray, BlockInstance $bi) {
        require_once(get_config('docroot') . 'artefact/blog/blocktype/recentposts/lib.php');
        $recentposts = PluginBlocktypeRecentposts::get_blog_posts_in_block($bi);
        $recentpostsids = array();
        foreach ($recentposts as $rpids) {
            array_push($recentpostsids, $rpids->{'id'});
        }
        if (!empty($recentpostsids)) {
            $idarray = array_merge($idarray, $recentpostsids);
        }
    }

/**
*  Creates the hard-coded modals for all attachments in the entire resume block
* @param BlockInstance $bi     The entire resume block containing the attachments
* @param array &$idarray       The exisiting array that stores modal ids to be created
*/
    private function get_entire_resume_modals(&$idarray, BlockInstance $bi) {
        require_once(get_config('docroot') . 'artefact/resume/blocktype/entireresume/lib.php');
        $resume = PluginBlocktypeEntireresume::get_artefacts($bi);
        $attachmentids = array();
        foreach ($resume as $field) {
            $res = $bi->get_artefact_instance($field);
            if ($attachment = $res->get_attachments()) {
                foreach ($attachment as $a) {
                    array_push($attachmentids, $a->{'id'});
                }
            }
        }
        if (!empty($attachmentids)) {
            $idarray = array_merge($idarray, $attachmentids);
        }
    }

/**
*  Creates the hard-coded modals for all attachments in the one resume field block
* @param BlockInstance $bi      The resume field block containing the attachments
* @param array &$idarray        The exisiting array that stores modal ids to be created
*/
    private function get_resume_field_modals(&$idarray, BlockInstance $bi) {
        $configdata = $bi->get('configdata');
        if (isset($configdata['artefactid'])) {
            $field = $bi->get_artefact_instance($configdata['artefactid']);
            $attachmentids = array();
            if ($attachment = $field->get_attachments()) {
                foreach ($attachment as $a) {
                    array_push($attachmentids, $a->{'id'});
                }
            }
            if (!empty($attachmentids)) {
                $idarray = array_merge($idarray, $attachmentids);
            }
        }
    }

/**
*  Creates the hard-coded modals for File(s) to download block
* @param BlockInstance $bi      The File(s) to download block
* @param array  &$idarray       The exisiting array that stores modal ids to be created
*/
private function get_folder_modals(&$idarray, BlockInstance $bi) {
    $artefacts = PluginBlocktypeFolder::get_current_artefacts($bi);
    $i = 0;
    $allartefacts = array();
    while (count($artefacts) > 0) {
        if ($artefact = artefact_instance_from_id($artefacts[$i])) {
            if ($artefact->get('artefacttype') == 'folder') {
                $children = $artefact->get_children_instances();
                foreach ($children as $childid) {
                   array_push($artefacts, $childid->get('id'));
                }
            }
        }
        $allartefacts[] = $artefacts[$i];
        unset($artefacts[$i]);
        $i++;
    }
    if (!empty($allartefacts)) {
        $idarray = array_unique(array_merge($idarray, $allartefacts));
    }
}

/**
* Exports the hard-coded modals for the blocks into relevant pages.
* This will append to the index.html or any other relevant page created
* previously that needs to contain a modal (including the profile page).
*/
    private function export_artefact_metadata_modals() {
        foreach ($this->views as $view) {
            $content = '';
            $blocks = get_records_array('block_instance', 'view', $view->get('id'));
            if ($blocks) {
                $options = array(
                    'viewid' =>  $view->get('id'),
                    'details' => true,
                    'metadata' => 1,
                    'modal' => true,
                );
                $uniqueids = array();
                $smarty = $this->get_smarty();
                foreach ($blocks as $b) {
                    $bi = new BlockInstance($b->id);
                    $type = $bi->get('artefactplugin');
                    $configdata = unserialize($b->configdata);
                    $artefactidarray = array();
                    if ($b->blocktype == 'blog') {
                        $this->get_blog_posts_modals($artefactidarray, $bi);
                    }
                    else if ($b->blocktype == 'recentposts') {
                        $this->get_recent_posts_modals($artefactidarray, $bi);
                    }
                    else if ($b->blocktype == 'taggedposts') {
                        $this->get_tagged_posts_modals($artefactidarray, $bi);
                    }
                    else if ($b->blocktype == 'entireresume') {
                        $this->get_entire_resume_modals($artefactidarray, $bi);
                    }
                    else if ($b->blocktype == 'resumefield') {
                        $this->get_resume_field_modals($artefactidarray, $bi);
                    }
                    else if ($b->blocktype == 'folder') {
                        $this->get_folder_modals($artefactidarray, $bi);
                    }
                    else if (
                        //block contains any of these types or matches blocktype
                         $type == 'image' ||
                         $type == 'blog' ||
                         $type == 'audio' ||
                         $type == 'video' ||
                         $type == 'html' ||
                         $type == 'plans' ||
                         $type == 'file' ||
                         $type == 'internal' && $b->blocktype != 'profileinfo'||
                         $b->blocktype == 'image' ||
                         $b->blocktype == 'internalmedia' ||
                         $b->blocktype == 'filedownload'
                     ) {
                        if (isset($configdata['artefactids']) && !empty($configdata['artefactids'])) {
                            $artefactidarray = $configdata['artefactids'];
                        }
                        else if (isset($configdata['artefactid']) && !empty($configdata['artefactid'])) {
                            $artefactidarray = array($configdata['artefactid']);
                        }
                    }

                    //Create the modal content for each unique id found
                    if (!empty($artefactidarray)) {
                        foreach ($artefactidarray as $artefactid) {
                            //prevent duplicate modals in same page
                            if (!in_array($artefactid, $uniqueids)) {
                                array_push($uniqueids, $artefactid);
                                $artefact = $bi->get_artefact_instance($artefactid);
                                $options['blockid'] = $b->id;
                                $rendered = $artefact->render_self($options);
                                $html = '';
                                if (!empty($rendered['javascript'])) {
                                    $html = '<script>' . $rendered['javascript'] . '</script>';
                                }
                                $html .= $rendered['html'];
                                $html .= $this->get_comments_for_modal($artefact, $view);
                                $smarty->assign('artefactid', $artefactid);
                                $smarty->assign('content', $html);
                                $smarty->assign('title', $artefact->get('title'));
                                $content .= $smarty->fetch('export:html:modal.tpl');
                            }
                        }
                    }
                }
            }
            if (!empty($content)) {
                $rootpath = ($this->exportingoneview) ? $this->get_root_path() : $this->get_root_path(3, $this->infodir . '/');
                $outputfilter = new HtmlExportOutputFilter($rootpath, $this);
                $content = $outputfilter->filter($content);
                // The directories should already exist (see dump_view_export_data())
                if ($this->exportingoneview) {
                    if (!file_put_contents($this->exportdir . '/' . $this->rootdir . '/index.html', $content, FILE_APPEND)) {
                        throw new SystemException("Could not create artefact metadata for the export");
                    }
                }
                else {
                    if ($view->get('type') != 'profile') {
                        $folder = self::text_to_filename($view->get('title'));
                        if (!file_put_contents($this->exportdir . '/' . $this->rootdir . '/views/' . $view->get('id') . '_' . $folder . '/index.html', $content, FILE_APPEND)) {
                            throw new SystemException("Could not create artefact metadata for the export");
                        }
                    }
                }
            }
        }
    }

    /**
     * Copies the static files (stylesheets etc.) into the export
     */
    protected function copy_static_files() {
        global $THEME, $SESSION;
        require_once('file.php');
        $staticdir = $this->get('exportdir') . $this->get('rootdir') . '/static/';
        $directoriestocopy = array();
        $themestaticdirs = $THEME->get_path('', true);

        $statics = array('style', 'images', 'fonts', 'js');
        foreach ($themestaticdirs as $theme => $dir) {
            // Get static directories from each theme for HTML export
            foreach ($statics as $static) {
                $themedir = $staticdir . 'theme/' . $theme . '/static/' . $static;
                if (is_readable($dir . $static)) {
                    $directoriestocopy[$dir . $static] = $themedir;
                    if (!check_dir_exists($themedir)) {
                        throw new SystemException("Could not create theme directory for theme $theme");
                    }
                }
            }
        }
        $exportthemedirs = $THEME->get_path('', true, 'export/html');
        foreach ($exportthemedirs as $theme => $dir) {
            foreach ($statics as $static) {
                $themedir = $staticdir . 'theme/' . $theme . '/static/export/' . $static;
                if (is_readable($dir . $static)) {
                    $directoriestocopy[$dir . $static] = $themedir;
                    if (!check_dir_exists($themedir)) {
                        throw new SystemException("Could not create theme directory for theme $theme");
                    }
                }
            }
        }

        // Copy over bootstrap and jquery files
        $jsdir =  $staticdir . 'theme/' . $theme . '/static/js/';
        $directoriestocopy[get_config('docroot') . 'js/popper/popper.min.js'] = $jsdir . 'popper.min.js';
        $directoriestocopy[get_config('docroot') . 'lib/bootstrap/assets/javascripts/bootstrap.min.js'] = $jsdir . 'bootstrap.min.js';
        $directoriestocopy[get_config('docroot') . 'js/jquery/jquery.js'] = $jsdir . 'jquery.js';
        $directoriestocopy[get_config('docroot') . 'js/lodash/lodash.js'] = $jsdir . 'lodash.js';
        $directoriestocopy[get_config('docroot') . 'js/gridstack/gridstack.js'] = $jsdir . 'gridstack.js';
        $directoriestocopy[get_config('docroot') . 'js/gridlayout.js'] = $jsdir . 'gridlayout.js';
        $directoriestocopy[get_config('docroot') . 'js/masonry/masonry.min.js'] = $jsdir . 'masonry.min.js';
        $directoriestocopy[get_config('docroot') . 'js/select2/select2.full.js'] = $jsdir . 'select2.full.js';

        foreach ($this->pluginstaticdirs as $dir) {
            $destinationdir = str_replace('export/html/', '', $dir);
            if (!check_dir_exists($staticdir . $destinationdir)) {
                $SESSION->add_error_msg(get_string('couldnotcreatestaticdirectory', 'export', $destinationdir));
            }
            foreach ($themestaticdirs as $theme => $themedir) {
                if (file_exists(get_config('docroot') . 'theme/' . $theme . '/' . $dir)) {
                    $directoriestocopy[get_config('docroot') . 'theme/' . $theme . '/' . $dir] = $staticdir . $destinationdir;
                }
            }
        }

        foreach ($directoriestocopy as $from => $to) {
            if (!copyr($from, $to)) {
                $SESSION->add_error_msg(get_string('couldnotcopyfilesfromto', 'export', $from, $to));
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
        if ($pluginname != 'file') {
            $this->fileroot = $this->exporter->get('exportdir') . '/' . $this->exporter->get('rootdir') . '/content/' . $pluginname . '/';
            if (!check_dir_exists($this->fileroot)) {
                throw new SystemException("Could not create the temporary export directory $this->fileroot");
            }
        }
        else {
            $this->fileroot = $this->exporter->get('exportdir') . $this->exporter->get('filedir');
            if (!check_dir_exists($this->fileroot)) {
                throw new SystemException("Could not create the export directory $this->fileroot");
            }
        }

        $this->extrafileroot = $this->exporter->get('exportdir') . $this->exporter->get('filedir') . '/extra/';
        if (!check_dir_exists($this->extrafileroot)) {
            throw new SystemException("Could not create the temporary export directory $this->extrafileroot");
        }
    }

    abstract public function dump_export_data();

    abstract public function get_summary();

    abstract public function get_summary_weight();

    public function paginate($artefact) {

        // Create directory for storing the artefact
        $dirname = PluginExportHtml::text_to_filename(trim($artefact->get('title')));
        if (!check_dir_exists($this->fileroot . $dirname)) {
            throw new SystemException("Couldn't create artefact directory {$this->fileroot}{$dirname}");
        }

        // Get artefact-specific pagination options
        $options = $this->pagination_data($artefact);

        // Render the first page of the artefact (the only one if there aren't many children)
        $smarty = $this->exporter->get_smarty($this->exporter->get_root_path(4), $artefact->get('artefacttype'));
        $smarty->assign('page_heading', $artefact->get('title'));
        $smarty->assign('breadcrumbs', array(
            array('text' => $options['plural']),
            array('text' => $artefact->get('title'), 'path' => 'index.html'),
        ));
        $rendered = $artefact->render_self(array('hidetitle' => true));

        $outputfilter = new HtmlExportOutputFilter($this->exporter->get_root_path(3), $this->exporter);
        $smarty->assign('rendered', $outputfilter->filter($rendered['html']));
        $content = $smarty->fetch('export:html:page.tpl');

        if ($artefact instanceof ArtefactTypeBlog && get_config('licensemetadata')) {
            $blogid = $artefact->get('id');
            $idarray = array();
            $exportedmodals = '';
            $renderoptions = array(
                'details' => true,
                'metadata' => 1,
                'modal' => true,
            );
             require_once(get_config('docroot') . 'artefact/blog/lib.php');
             $from = "
                 FROM {artefact} a LEFT JOIN {artefact_blog_blogpost} bp ON a.id = bp.blogpost
                 WHERE a.artefacttype = 'blogpost' AND a.parent = ?";
             $from .= ' AND bp.published = 1';
             $count = count_records_sql('SELECT COUNT(*) ' . $from, array($blogid));
             $posts = ArtefactTypeBlogPost::get_posts($blogid, $count ? $count : 10, 0);
             if ($posts['data']) {
                 foreach ($posts['data'] as $pid) {
                     array_push($idarray, $pid->{'id'});
                 }
             }
             if (!empty($idarray)) {
                 foreach ($idarray as $id) {
                     $html = '';
                     $a = artefact_instance_from_id($id);
                     $modalcontent = $a->render_self($renderoptions);
                     if (!empty($modalcontent['javascript'])) {
                         $html = '<script>' . $modalcontent['javascript'] . '</script>';
                     }
                     $html .= $modalcontent['html'];
                     $smarty->assign('artefactid', $id);
                     $smarty->assign('content', $html);
                     $smarty->assign('title', $a->get('title'));
                     $exportedmodals .= $smarty->fetch('export:html:modal.tpl');
                 }
                 $exportedmodals = $outputfilter->filter($exportedmodals);
                 $content .= $exportedmodals;
             }
        }

        if (false === file_put_contents($this->fileroot . $dirname . '/index.html', $content)) {
            throw new SystemException("Unable to create index.html for artefact " . $artefact->get('id'));
        }

        // If the artefact has many children, we'll need to write out archive pages
        if ($options['childcount'] > $options['perpage']) {
            for ($i = $options['perpage']; $i <= $options['childcount']; $i += $options['perpage']) {
                $rendered = $artefact->render_self(array('limit' => $options['perpage'], 'offset' => $i));
                $smarty->assign('rendered', $outputfilter->filter($rendered['html']));
                $content = $smarty->fetch('export:html:page.tpl');
                if ($artefact instanceof ArtefactTypeBlog && get_config('licensemetadata')) {
                    $modalcontent = $artefact->render_self(array('limit' => $options['perpage'], 'offset' => $i, 'details' => true, 'metadata' => 1, 'modal' => true));
                    $html ='';
                    if (!empty($modalcontent['javascript'])) {
                        $html = '<script>' . $modalcontent['javascript'] . '</script>';
                    }
                    $html .= $modalcontent['html'];
                    $smarty->assign('artefactid', $artefact->get('id'));
                    $smarty->assign('content', $html);
                    $smarty->assign('title', $artefact->get('title'));
                    $exportedmodals .= $smarty->fetch('export:html:modal.tpl');
                    $exportedmodals = $outputfilter->filter($exportedmodals);
                    $content .= $exportedmodals;
                }

                if (false === file_put_contents($this->fileroot . $dirname . "/{$i}.html", $content)) {
                    throw new SystemException("Unable to create {$i}.html for artefact {$artefact->get('id')}");
                }
            }
        }
    }
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
     * A cache of folder data. See get_folder_path_for_file()
     */
    private $folderdata = null;

    /**
     */
    private $htmlexportcopyproxy = null;

    /**
     */
    private $exporter = null;

    /**
     */
    private $owner = null;

    /**
     * @param string $basepath The relative path to the root of the generated export
     */
    public function __construct($basepath, &$exporter=null) {
        $this->htmlexportcopyproxy = HtmlExportCopyProxy::singleton();
        $this->exporter = $exporter;
        $this->owner = $exporter->get('user')->get('id');
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
                // No forms
                '#<form[^>]*>.*?</form>#si',
                // Gratuitous hack for the RSS blocktype
                '#<div id="blocktype_externalfeed_lastupdate">[^<]*</div>#',
            ),
            array(
                '',
                '',
                '',
            ),
            $html
        );

        // Links to personal views
        $html = preg_replace_callback(
            '#' . $wwwroot . 'view/view\.php\?id=(\d+)#',
            array($this, 'replace_view_link'),
            $html
        );

        // Links to other views
        $html = preg_replace_callback(
            '#<a[^>]+href="(' . $wwwroot . ')?/?(group|user)/view\.php\?id=(\d+)?"[^>]*>([\S\s]*?)</a>#',
            array($this, 'replace_other_view_link'),
            $html
        );

        // Links to artefacts
        $html = preg_replace_callback(
            '#<a[^>]+href="(' . $wwwroot . ')?/?view/artefact\.php\?artefact=(\d+)(&amp;view=\d+)?(&amp;offset=\d+)?"[^>]*>([^<]*)</a>#',
            array($this, 'replace_artefact_link'),
            $html
        );

        // Links to image artefacts
        $html = preg_replace_callback(
            '#<a[^>]+href="(' . $wwwroot . ')?/?view/artefact\.php\?artefact=(\d+)(&amp;view=\d+)?(&amp;offset=\d+)?"[^>]*>(<img[^>]+>)</a>#',
            array($this, 'replace_artefact_link'),
            $html
        );

        // Links to download files
        $html = preg_replace_callback(
            '#(?<=[\'"])(' . $wwwroot . ')?/?artefact/file/download\.php\?file=(\d+)(?:(?:&amp;|&|%26)([a-z]+=[x0-9]+)+)*#',
            array($this, 'replace_download_link'),
            $html
        );

        // Replace inner links
        $html = preg_replace_callback(
            '#(?<=[\'"])(' . $wwwroot . ')?/?artefact/artefact\.php\?artefact=(\d+)(?:(?:&amp;|&|%26)([a-z]+=[x0-9]+)+)*#',
            array($this, 'replace_inner_link'),
            $html
        );

        // Links to pdf block files
        $html = preg_replace_callback(
            '#(?<=[\'"])(' . $wwwroot . ')?/?artefact/file/blocktype/pdf/viewer\.php\?.*?file=(\d+)(?:&amp;|&|%26).*?(?=[\'"])#',
            array($this, 'replace_pdf_link'),
            $html
        );

        // Thumbnails
        require_once('file.php');
        $html = preg_replace_callback(
            '#(?<=[\'"])(' . $wwwroot . ')?/?thumb\.php\?type=([a-z]+)((?:(?:&amp;|&|%26)[a-z]+=[x0-9]+)+)*#',
            array($this, 'replace_thumbnail_link'),
            $html
        );

        // Images out of the theme directory
        $html = preg_replace_callback(
            '#(?<=[\'"])(' . $wwwroot . '|/)?((?:[a-z]+/)*)theme/([a-zA-Z0-9_.-]+)/static/images/([a-z0-9_.-]+)#',
            array($this, 'replace_theme_image_link'),
            $html
        );

        // Tags
        $html = preg_replace_callback(
            '#<a[^>]+href="' . $wwwroot . 'tags.php\?tag=.*?">(.*?)<\/a>#',
            array($this, 'replace_tag_link'),
            $html
        );

        // Links to journals
        $html = preg_replace_callback(
            '#<a[^>]+href="(' . $wwwroot . ')?/?artefact/blog/view/index\.php\?=(\d+)?"[^>]*>([^<]*)</a>#',
            array($this, 'replace_journal_link'),
            $html
        );

        // Links to views
        $html = preg_replace_callback(
            '#' . $wwwroot . 'collection/progresscompletion\.php\?id=(\d+)#',
            array($this, 'replace_progresscompletion_link'),
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
        // Don't rewrite links to views that are not going to be included in the export
        if (!isset($this->exporter->views[$viewid])) {
            return $matches[0];
        }
        if (!isset($this->viewtitles[$viewid])) {
            $this->viewtitles[$viewid] = PluginExportHtml::text_to_URLpath($this->exporter->views[$viewid]->get('id') . '_' . PluginExportHtml::text_to_filename(get_field('view', 'title', 'id', $viewid)));
        }
        $filterpath = $this->exporter->get('exportingoneview') ? $this->exporter->get_root_path() : $this->exporter->get_root_path(2) . 'views/';
        return $filterpath . $this->viewtitles[$viewid] . '/index.html';
    }

    /**
     * Callback to remove links to group/user views in
     * the HTML export
     */
    private function replace_other_view_link($matches) {
        $viewid = $matches[3];
        // If the user view is in this export
        if (isset($this->exporter->views[$viewid])) {
            if (!isset($this->viewtitles[$viewid])) {
                $this->viewtitles[$viewid] = PluginExportHtml::text_to_URLpath($this->exporter->views[$viewid]->get('id') . '_' . PluginExportHtml::text_to_filename(get_field('view', 'title', 'id', $viewid)));
            }
            $filterpath = $this->exporter->get('exportingoneview') ? $this->exporter->get_root_path() : $this->exporter->get_root_path(2) . 'views/';
            return '<a href="' . $filterpath . $this->viewtitles[$viewid] . '/index.html">' . $matches[4] . '</a>';
        }
        return $matches[4];
    }

    /**
     * Callback to replace progress completion links to point to the correct location in
     * the HTML export
     */
    private function replace_progresscompletion_link($matches) {
        $collectionid = $matches[1];

        $collectionname = PluginExportHtml::text_to_URLpath(PluginExportHtml::text_to_filename(get_field('collection', 'name', 'id', $collectionid)));
        return $this->basepath . '/views/' . $collectionname . '_progresscompletion/index.html';
    }

    /**
     * Callback to replace links to artefact to point to the correct location
     * in the HTML export
     */
    private function replace_artefact_link($matches) {
        $artefactid = $matches[2];
        try {
            $artefact = artefact_instance_from_id($artefactid);
        }
        catch (ArtefactNotFoundException $e) {
            return $matches[5];
        }

        $artefacttype = $artefact->get('artefacttype');
        $filterpath = $this->exporter->get('exportingoneview') ? $this->exporter->get_root_path(1, $this->exporter->get('filedir')) : $this->exporter->get_root_path(3, $this->exporter->get('filedir'));
        switch ($artefacttype) {
        case 'blog':
        case 'plan':
            $dir = $artefacttype == 'plan' ? 'plans' : $artefacttype;
            $offset = ($matches[4]) ? intval(substr($matches[4], strlen('&amp;offset='))) : 0;
            $offset = ($offset == 0) ? 'index' : $offset;
            return '<a href="' . $this->basepath . "/files/$dir/" . PluginExportHtml::text_to_URLpath(PluginExportHtml::text_to_filename($artefact->get('title'))) . '/' . $offset . '.html">' . $matches[5] . '</a>';
        case 'file':
        case 'folder':
        case 'image':
        case 'profileicon':
        case 'archive':
        case 'video':
        case 'audio':
            return '<a href="' . $this->get_export_path_for_file($artefact, array(), $filterpath) . '">' . $matches[5] . '</a>';
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
        try {
            $artefact = artefact_instance_from_id($artefactid);
        }
        catch (ArtefactNotFoundException $e) {
            return '';
        }

        // If artefact type not something that would be served by download.php,
        // replace link with nothing
        if ($artefact->get_plugin_name() != 'file') {
            return '';
        }

        $options = array();
        for ($i = 3; $i < count($matches); $i++) {
            list($key, $value) = explode('=', $matches[$i]);
            $options[$key] = $value;
        }
        if (isset($matches[3]) && (substr($matches[3], 0, 4) === 'view')) {
            // file is in a view
            $distance = 3;
        }
        else {
            // we are not in a view, could be blog post attachement or Notes attachement
            $distance = 4;
        }
        $filterpath = $this->exporter->get('exportingoneview') ? $this->exporter->get_root_path(1, 'files') : $this->exporter->get_root_path($distance, $this->exporter->get('filedir'));
        return $this->get_export_path_for_file($artefact, $options, $filterpath);
    }

    private function replace_inner_link($matches) {
        $artefactid = $matches[2];
        try {
            $artefact = artefact_instance_from_id($artefactid);
        }
        catch (ArtefactNotFoundException $e) {
            return '';
        }

        // If artefact type not something that would be served by download.php,
        // replace link with nothing
        if ($artefact->get_plugin_name() != 'file') {
            return '';
        }

        $options = array();
        for ($i = 3; $i < count($matches); $i++) {
            list($key, $value) = explode('=', $matches[$i]);
            $options[$key] = $value;
        }

        if ($artefact instanceof ArtefactTypeFolder) {
            return $this->get_folder_path_for_file($artefact, $options);
        }

        $filterpath = $this->exporter->get('exportingoneview') ? $this->exporter->get_root_path(1, 'files') : $this->exporter->get_root_path(3, $this->exporter->get('filedir'));
        return $this->get_export_path_for_file($artefact, $options, $filterpath);
    }

    /**
     * Callback to replace links to artefact/file/blocktype/pdf/viewer.php to point to the
     * correct file in the HTML export
     */
    private function replace_pdf_link($matches) {
        $artefactid = $matches[2];
        try {
            $artefact = artefact_instance_from_id($artefactid);
        }
        catch (ArtefactNotFoundException $e) {
            return '';
        }

        // If artefact type not something that would be served by download.php,
        // replace link with nothing
        if ($artefact->get_plugin_name() != 'file') {
            return '';
        }

        $filterpath = $this->exporter->get('exportingoneview') ? $this->exporter->get_root_path(1, 'files') : $this->exporter->get_root_path(3, $this->exporter->get('filedir'));
        return $this->get_export_path_for_file($artefact, array(), $filterpath);
    }

    /**
     * Callback to replace links to thumb.php to point to the correct file in
     * the HTML export
     */
    private function replace_thumbnail_link($matches) {
        if (isset($matches[3])) {
            $type = $matches[2];

            $parts = preg_split('/(&amp;|&|%26)/', $matches[3]);
            array_shift($parts);
            foreach ($parts as $part) {
                list($key, $value) = explode('=', $part);
                $options[$key] = $value;
            }

            if (!isset($options['id'])) {
                return '';
            }

            switch ($type) {
            case 'profileicon':
                // Convert the user ID to a profile icon ID
                if (!$options['id'] = get_field_sql('SELECT profileicon FROM {usr} WHERE id = ?', array($options['id']))) {
                    // No profile icon, get the default one
                    list($size, $prefix) = $this->get_size_from_options($options);
                    if ($from = get_dataroot_image_path('artefact/file/profileicons/no_userphoto/' . get_config('theme'), 0, $size)) {
                        $to = '/static/profileicons/0-' . $prefix . 'no_userphoto.png';
                        $this->htmlexportcopyproxy->add($from, $to);
                    }
                    return $this->basepath . $to;
                }
            case 'profileiconbyid':
                try {
                    $icon = artefact_instance_from_id($options['id']);
                }
                catch (ArtefactNotFoundException $e) {
                    return '';
                }
                if ($icon->get_plugin_name() != 'file') {
                    return '';
                }
                $rootpath = ($this->exporter->get('exportingoneview')) ? $this->exporter->get_root_path(2) : $this->exporter->get_root_path(3);
                return $rootpath . $this->get_export_path_for_file($icon, $options, 'HTML/static/profileicons/');
            default:
                return '';
            }
        }

        return '';
    }

    /**
     * Callback
     */
    private function replace_theme_image_link($matches) {
        $file = $matches[2] . 'theme/' . $matches[3] . '/static/images/' . $matches[4];
        $this->htmlexportcopyproxy->add(
            get_config('docroot') . $file,
            '/static/' . $file
        );
        return $this->basepath . '/static/' . $file;
    }

    /**
     * Callback to replace links to tags static text in
     * the HTML export
     */
    function replace_tag_link($matches) {
        return $matches[1];
    }

    /**
     * Callback to replace links to journals static text in
     * the HTML export
     */
    function replace_journal_link($matches) {
        return $matches[3];
    }

    /**
     * Given a file, returns the folder path for it in the Mahara files area
     *
     * The path is pre-sanitised so it can be used when generating the export
     *
     * @param  $file The file or folder to get the folder path for
     * @return string
     */
    private function get_folder_path_for_file($file) {
        if ($this->folderdata === null) {
            $this->folderdata = get_records_select_assoc('artefact', "artefacttype = 'folder' AND owner = ?", array($file->get('owner')));
            if ($this->folderdata) {
                foreach ($this->folderdata as &$folder) {
                    $folder->title = PluginExportHtml::sanitise_path($folder->title);
                }
            }
        }
        $folderpath = ArtefactTypeFileBase::get_full_path($file->get('parent'), $this->folderdata);
        return $folderpath;
    }

    /**
     * Generates a path, relative to the root of the export, that the given
     * file will appear in the export.
     *
     * If the file is a thumbnail, the copy proxy is informed about it so that
     * the image can later be copied in to place.
     *
     * @param ArtefactTypeFileBase $file The file to get the exported path for
     * @param array $options             Options from the URL that was linking
     *                                   to the image - most importantly, size
     *                                   related options about how the image
     *                                   was thumbnailed, if it was.
     * @param string $basefolder         What folder in the export to dump the
     *                                   file in
     * @return string                    The relative path to where the file
     *                                   will be placed
     */
    private function get_export_path_for_file(ArtefactTypeFileBase $file, array $options, $basefolder=null) {
        global $SESSION;
        if (is_null($basefolder)) {
            if ($file->get('owner') == $this->owner) {
                $basefolder = '/files/' . $this->get_folder_path_for_file($file);
            }
        }

        unset($options['view']);
        if (!$this->exporter->get('user')->can_view_artefact($file) && $file->get('artefacttype') != 'profileicon') {
            $SESSION->add_info_msg(get_string('unabletocopyartefact', 'export', $file->get('title')));
            return '';
        }
        $prefix = '';
        $title = PluginExportHtml::sanitise_path($file->get('title'));
        if ($options) {
            list($size, $prefix) = $this->get_size_from_options($options);
            $from = $file->get_path($size);

            $to = $basefolder . $file->get('id') . '-' . $prefix . $title;
            $this->htmlexportcopyproxy->add($from, $to);
        }
        else {
            $title = $file->get('id') . '-' . $title;
            $to = $basefolder . $title;
        }

        return $this->basepath . $to;
    }

    /**
     * Helper method
     */
    private function get_size_from_options($options) {
        $prefix = '';
        foreach (array('size', 'width', 'height', 'maxsize', 'maxwidth', 'maxheight') as $param) {
            if (isset($options[$param])) {
                $$param = $options[$param];
                $prefix .= $param . '-' . $options[$param] . '-';
            }
            else {
                $$param = null;
            }
        }

        return array(imagesize_data_to_internal_form($size, $width, $height, $maxsize, $maxwidth, $maxheight), $prefix);
    }

}

/**
 * Gathers a list of files that need to be copied into the export, as they're
 * found by the HtmlExportOutputFilter
 */
class HtmlExportCopyProxy {

    private static $instance = null;
    private $copy = array();

    private function __construct() {
    }

    public static function singleton() {
        if (is_null(self::$instance)) {
            self::$instance = new HtmlExportCopyProxy();
        }
        return self::$instance;
    }

    public function add($from, $to) {
        $this->copy[$from] = $to;
    }

    public function get_copy_data() {
        return $this->copy;
    }
}
