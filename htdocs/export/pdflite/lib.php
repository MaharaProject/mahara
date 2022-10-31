<?php
/**
 *
 * @package    mahara
 * @subpackage export-pdf-lite
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();
global $pdfactive;
$pdfliteactive = false;
require_once(get_config('docroot') . 'export/pdf/lib.php');

if (db_table_exists('export_installed')) {
    $pdfliteactive = get_field('export_installed', 'active', 'name', 'pdflite');
}
if ($pdfliteactive && !$pdfactive) {
    execute_sql("UPDATE {export_installed} SET active = 0 WHERE name = ?", array('pdflite'));
}

use HeadlessChromium\BrowserFactory;

/**
 * PDF export plugin
 */
class PluginExportPdfLite extends PluginExportPdf {

    /**
     * The name of the directory where files will be placed in the export
     */
    protected $pdfdir = 'PDF_Lite';
    protected $validfiles = array('docx','sxw','pdf','txt','rtf','html','htm','wps','odt','pages','xls','xlsx','ps','hwp');

    /**
     * {@inheritDoc}
     */
    public function __construct(User $user, $views, $artefacts, $progresscallback=null, $loop=1, $looptotal=1, $exporttime=null) {
        global $THEME;
        parent::__construct($user, $views, $artefacts, $progresscallback,  $loop, $looptotal, $exporttime);
        $this->exporttype = 'pdflite';

        $this->zipfile = 'mahara-export-pdflite-user'
            . $this->get('user')->get('id') . '-' . $this->exporttime . '.zip';

        $pdfdirectory = "{$this->exportdir}/{$this->pdfdir}";
        if (!check_dir_exists($pdfdirectory)) {
            throw new SystemException("Couldn't create the temporary export directory $pdfdirectory");
        }
    }

    /**
     * A human-readable title for the export
     *
     * @return string
     */
    public static function get_title() {
        return get_string('title1', 'export.pdflite');
    }

    /**
     * A human-readable description for the export
     *
     * @return string
     */
    public static function get_description() {
        return get_string('description', 'export.pdflite');
    }

    /**
     * Is the plugin activated or not?
     *
     * @return boolean
     */
    public static function is_active() {
        $active = false;
        if (get_field('export_installed', 'active', 'name', 'pdflite') && parent::is_active()) {
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
        return 'PDF lite';
    }

    /**
     * Post install hook
     *
     * @param integer $fromversion The current version number
     */
    public static function postinst($fromversion) {
        if ($fromversion == 0) {
            set_field('export_installed', 'active', 0, 'name', 'pdflite');
        }
        return true;
    }

    /**
     * Asserts whether this plugin can be used.
     *
     * @return boolean
     */
    public static function is_usable() {
        $dependencies = self::has_plugin_dependencies();
        if (!empty($dependencies['requires'])) {
            return false;
        }
        return true;
    }

    /**
     * Check if plugin's contains dependencies before installing it.
     *
     * @return array
     */
    public static function has_plugin_dependencies() {
        $needs = get_string('isexperimental', 'export.pdflite');
        $requires = array();
        if (!parent::is_usable() || !get_field('export_installed', 'version', 'name', 'pdf')) {
            $requires[] = get_string('needsinstalledpdfexport', 'export.pdflite');
        }
        if (!parent::is_active()) {
            $requires[] = get_string('needsactivepdfexport', 'export.pdflite');
        }
        $out = array('needs' => $needs, 'requires' => implode('<br>', $requires));
        return $out;
    }

    /**
     * Main export routine
     *
     * We can specify whether to make a zipfile now or later on, i.e. in PluginExportAll
     * which creates a zipfile of all export formats. Note: If running pdf export and html
     * export together then this should be run first
     *
     * @param $createarchive boolean whether to create zipfile
     * @return array|string  Either an array of zipfile information or path string
     */
    public function export($createarchive=false) {
        parent::export(false);
        $this->pdf_view_export_data();
        if (!$createarchive) {
            return array(
                'exportdir' => $this->exportdir,
                'zipfile' => $this->zipfile,
                'dirs' => array($this->pdfdir, $this->infodir),
            );
        }

        // zip everything up
        $this->notify_progress_callback(90, get_string('creatingzipfile', 'export'));
        try {
            create_zip_archive($this->exportdir, $this->zipfile, array($this->pdfdir, $this->infodir));
        }
        catch (SystemException $e) {
            throw new SystemException('Failed to zip the export file: ' . $e->getMessage());
        }
        $this->notify_progress_callback(100, get_string('Done', 'export'));
        return $this->zipfile;
    }

    /**
     * Generates the PDF files from the HTML export dump
     */
    private function pdf_view_export_data() {
        global $pdfrun;
        static $browser;
        static $page;

        $this->notify_progress_callback(81, get_string('beginpdfliteviewexport', 'export.pdflite'));
        $progressstart = 85;
        $progressend   = 95;
        $i = 0;
        $viewcount = count($this->views);
        ob_start();
        if (system('command -v dpkg')) {
            // For Ubuntu
            $command = 'dpkg -l';
        }
        else if (system('rpm -q --quiet glib')) {
            // For RHEL / CentOS
            $command = 'rpm -qa';
        }
        else {
            throw new SystemException("Operating system not supported");
        }

        if (!isset($pdfrun) || $pdfrun == 'first' || $pdfrun == 'all') {
            $this->notify_progress_callback(82, get_string('startuppdfchrome', 'export.pdf'));
            $browsertype = 'chromium-browser';
            system($command . ' | grep ' . $browsertype, $error);
            if ($error) {
                $browsertype = 'chrome';
                system($command . ' | grep ' . $browsertype, $error2);
                if ($error2) {
                    throw new MaharaException('Need to have a Chrome browser installed to use the headless PDF lite option');
                }
            }

            // @phpstan-ignore-next-line
            $browserFactory = new BrowserFactory($browsertype);
            // starts headless chrome
            try {
                $browser = $browserFactory->createBrowser(['windowSize' => [960,600],
                                                           'ignoreCertificateErrors' => true,
                                                           'connectionDelay' => 0.8]);
            }
            catch (Exception $e) {
                $this->notify_progress_callback(83, get_string('pdfchromestartederror', 'export.pdf'));
                throw new MaharaException('Chrome browser unable to start: ' . $e->getMessage());
            }

            // creates a new page and navigate to an url
            $page = $browser->createPage();
            $this->notify_progress_callback(83, get_string('pdfchromestarted', 'export.pdf'));
        }

        $combiner = parent::has_pdf_combiner();

        // Map the view id order to their collection order if applicable
        $viewids = array_keys($this->views);
        $viewobjs = array();
        if (!empty($viewids)) {
            $colviews = get_column_sql("SELECT v.id FROM {view} v
                                        LEFT JOIN {collection_view} cv ON cv.view = v.id
                                        WHERE v.id IN (" . join(',', array_map('intval', $viewids)) . ")
                                        ORDER BY cv.collection, cv.displayorder, v.id");

            foreach ($colviews as $id) {
                $view = $this->views[$id];
                $cid = 0;
                if ($view->get_collection()) {
                    $cid = $view->get_collection()->get('id');
                }
                $viewobjs[$cid][$id] = $view;
            }
        }

        $colpdfs = $viewpdfs = array();
        foreach ($viewobjs as $collectionid => $views) {
            foreach ($views as $viewid => $view) {
                set_time_limit(120);
                $this->notify_progress_callback(intval($progressstart + (++$i / $viewcount) * ($progressend - $progressstart)), get_string('exportingviewsprogresspdf', 'export', $i, $viewcount));

                if ($this->exportingoneview) {
                    $directory = $this->exportdir . '/' . $this->rootdir;
                }
                else {
                    $directory = $this->exportdir . '/' . $this->rootdir . '/views/' . $view->get('id') . '_' . parent::text_to_filename($view->get('title'));
                }
                $filename = $directory . "/index.html";
                // Adjust the relative links to files to be textual to mention where the file lives within the zip file
                // Because we can't make relative links in a pdf export
                $filedata = file_get_contents($filename);
                $filedata = preg_replace('/<div class="breadcrumbs collection">.*?<\/div>/s', '', $filedata);
                if ($view->get('newlayout')) {
                    if (preg_match('/var blocks = (\[.*?\]);/', $filedata, $matches)) {
                        $content = json_decode($matches[1]);
                        foreach ($content as $c) {
                            // $1 = url, $2 = name
                            $c->content = preg_replace('/\<a href=\"\.\/(.*?)\".*?\>(.*?)\<\\/a\>/s', "$1", $c->content);
                            // Strip other links out - $1 = name
                            $c->content = preg_replace('/\<a.*? href=.*?\>(.*?)\<\\/a\>/s', "$1", $c->content);
                        }
                        $content = json_encode($content);
                        $filedata = preg_replace('/var blocks = \[.*?\];/', 'var blocks = ' . $content, $filedata);
                    }
                }
                else {
                    // $1 = url, $2 = name
                    $filedata = preg_replace('/\<a href=\"\.\/(.*?)\".*?\>(.*?)\<\/a\>/s', "$1", $filedata);
                    // Strip other links out - $1 = name
                    $filedata = preg_replace('/\<a.*? href=.*?\>(.*?)\<\/a\>/s', "$1", $filedata);

                }
                file_put_contents($filename, $filedata, LOCK_EX);

                // Navigate to the needed page
                $page->navigate('file://' . $filename)->waitForNavigation('networkIdle');
                $shortname = generate_urlid($view->get('title'), get_config('cleanurlviewdefault'), 3, 50);

                // Create the pdf file
                // Note: pdf is created in @media print mode
                $pdfname = $directory . '/' . $viewid . '_' . $shortname . '.pdf';
                if ($collectionid > 0) {
                    $colpdfs[$collectionid][] = $pdfname;
                }
                else {
                    $viewpdfs[] = $pdfname;
                }
                // Has 60s timeout
                $page->pdf(['printBackground' => true,
                            'preferCSSPageSize' => true])->saveToFile($pdfname, 60000);

                if (!file_exists($filename) || !is_readable($filename)) {
                    throw new SystemException("Could not read view page for creating pdf lite for $viewid");
                }
            }
        }

        if (!isset($pdfrun) || $pdfrun == 'last' || $pdfrun == 'all') {
            // Close the headlesss browser
            $page->close();
            $browser->close();
        }

        $output = array();
        $directory = $this->exportdir . '/' . $this->rootdir;
        $pdfdirectory = "{$this->exportdir}/{$this->pdfdir}";
        if ($combiner) {
            foreach ($colpdfs as $collectionid => $collection) {
                $collectionname = $this->collections[$collectionid]->get('name');
                $collectionname = parent::text_to_filename($collectionname);
                if ($combiner == 'pdfunite') {
                    exec('pdfunite ' . implode(' ', $collection) . ' ' . $pdfdirectory . '/' . $collectionid . '_' . $collectionname . '.pdf', $output);
                }
                else {
                    exec('gs -dSAFER -dNOPAUSE -sDEVICE=pdfwrite -sOUTPUTFILE=' .  $pdfdirectory . '/' . $collectionid . '_' . $collectionname . '.pdf -dBATCH ' . implode(' ', $collection), $output);
                }
                // remove the page pdfs that are now in collections
                foreach ($collection as $c) {
                    unlink($c);
                }
            }
        }
        // Move view PDF files to same place as collection files
        foreach ($viewpdfs as $view) {
            $path = explode('/', $view);
            $file = array_pop($path);
            rename($view, $pdfdirectory . $file);
        }

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
        foreach (array_keys($subdirpaths) as $subkey) {
            if (is_dir($subkey)) {
                rmdirr($subkey);
            }
        }

        ob_end_clean();
    }
}
