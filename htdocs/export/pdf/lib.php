<?php
/**
 *
 * @package    mahara
 * @subpackage export-pdf
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();
require_once(get_config('docroot') . 'export/html/lib.php');
global $pdfactive;
$pdfactive = false;
if (db_table_exists('export_installed')) {
    $pdfactive = get_field('export_installed', 'active', 'name', 'pdf');
}

$chromephpexists = file_exists(get_config('docroot') . 'lib/chrome-php/chrome-0.11/vendor/autoload.php');
if (($pdfactive && !$chromephpexists) ||
    ($pdfactive && $chromephpexists && !get_config('usepdfexport'))) {
    global $SESSION;
    // need to disable the PDF export option
    execute_sql("UPDATE {export_installed} SET active = 0 WHERE name = ?", array('pdf'));
    $SESSION->add_info_msg(get_string('exportpdfdisabled', 'export.pdf', get_config('wwwroot') . 'admin/extensions/plugins.php'), false);
    if (defined('INSTALLER')) {
        redirect();
    }
    else {
        redirect($_SERVER['SCRIPT_NAME']);
    }
}
else if ($pdfactive) {
    require_once(get_config('docroot') . 'lib/chrome-php/chrome-0.11/vendor/autoload.php');
}
use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Cookies\Cookie;

/**
 * PDF export plugin
 */
class PluginExportPdf extends PluginExportHtml {

    /**
    * The name of the directory where files will be placed in the export
    **/
    protected $pdfdir = 'PDF';

    /**
    * constructor.  overrides the parent class
    * to set up smarty and the attachment directory
    */
    public function __construct(User $user, $views, $artefacts, $progresscallback=null, $loop=1, $looptotal=1, $exporttime=null) {
        global $THEME;
        parent::__construct($user, $views, $artefacts, $progresscallback,  $loop, $looptotal, $exporttime);
        $this->exporttype = 'pdf';

        $this->zipfile = 'mahara-export-pdf-user'
            . $this->get('user')->get('id') . '-' . $this->exporttime . '.zip';

        $pdfdirectory = "{$this->exportdir}/{$this->pdfdir}";
        if (!check_dir_exists($pdfdirectory)) {
            throw new SystemException("Couldn't create the temporary export directory $pdfdirectory");
        }
    }

    public static function get_title() {
        return get_string('title1', 'export.pdf');
    }

    public static function get_description() {
        return get_string('description', 'export.pdf');
    }

    /**
     * Is the plugin activated or not?
     *
     * @return boolean
     */
    public static function is_active() {
        $active = false;
        if (get_field('export_installed', 'active', 'name', 'pdf')) {
            $active = true;
        }
        return $active;
    }

    /**
     * Fetch the human readable name for the plugin
     *
     * @return string
     */
    public static function get_plugin_display_name() {
        return 'PDF';
    }

    /**
     * Post install hook
     */
    public static function postinst($fromversion) {
        if ($fromversion == 0) {
            set_field('export_installed', 'active', 0, 'name', 'pdf');
        }
        return true;
    }

    public static function has_pdf_combiner() {
        // Check we have a valid way to combine pdfs
        $combiner = false;

        if ($pdfunite = exec('apt-cache policy poppler-utils | grep Installed')) { // Ubuntu
            if (!preg_match('/Installed\: \(none\)/', $pdfunite)) {
                $combiner = 'pdfunite';
            }
        }
        else if ($pdfunite = exec('rpm -q poppler-utils')) { // RHEL / CentOS
            if (!preg_match('/is not installed/', $pdfunite)) {
                $combiner = 'pdfunite';
            }
        }

        if ($ghostscript = exec('apt-cache policy ghostscript | grep Installed')) { // Ubuntu
            if (!preg_match('/Installed\: \(none\)/', $ghostscript)) {
                $combiner = 'ghostscript';
            }
        }
        else if ($ghostscript = exec('rpm -q ghostscript')) { // RHEL / CentOS
            if (!preg_match('/is not installed/', $ghostscript)) {
                $combiner = 'ghostscript';
            }
        }
        return $combiner;
    }

    public static function is_usable() {
        $dependencies = self::has_plugin_dependencies();
        if (!empty($dependencies['requires'])) {
            return false;
        }
        return true;
    }

    public static function has_plugin_dependencies() {
        $needs = get_string('needschromeheadless', 'export.pdf');
        // make sure that composer has installed the headlessbrowser hook
        $requires = array();
        if (!file_exists(get_config('docroot') . 'lib/chrome-php/chrome-0.11/src/BrowserFactory.php')) {
            $requires[] = get_string('needschromeheadlessphp', 'export.pdf');
        }

        $combiner = self::has_pdf_combiner();
        if (!$combiner) {
            $requires[] = get_string('needspdfcombiner', 'export.pdf');
        }
        if (!get_config('usepdfexport')) {
            $requires[] = get_string('needspdfconfig', 'export.pdf');
        }
        $out = array('needs' => $needs, 'requires' => implode('<br>', $requires));
        return $out;
    }

    /**
     * Main export routine
     * @param $createarchive Boolean specifies whether a zipfile will be created here
     * or later on, i.e. in PluginExportAll which creates a zipfile of all export formats.
     * Note: If running pdf export and html export together then this should be run first
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

        $this->notify_progress_callback(81, get_string('beginpdfviewexport', 'export.pdf'));
        $progressstart = 85;
        $progressend   = 95;
        $i = 0;
        $viewcount = count($this->views);
        ob_start();
        if (system('command -v dpkg')) { // Ubuntu
            $command = 'dpkg -l';
        }
        else { // RHEL / CentOS
            $command = 'rpm -qa';
        }

        if (!isset($pdfrun) || $pdfrun == 'first' || $pdfrun == 'all') {
            $this->notify_progress_callback(82, get_string('startuppdfchrome', 'export.pdf'));
            $browsertype = 'chromium-browser';
            system($command . ' | grep ' . $browsertype, $error);
            if ($error) {
                $browsertype = 'chrome';
                system($command . ' | grep ' . $browsertype, $error2);
                if ($error2) {
                    throw new MaharaException('Need to have a Chrome browser installed to use the headless pdf option');
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

            // creates a new page
            $page = $browser->createPage();
            $this->notify_progress_callback(83, get_string('pdfchromestarted', 'export.pdf'));
        }

        $combiner = self::has_pdf_combiner();
        if ($combiner) {
            $this->notify_progress_callback(84, get_string('pdffoundcombiner', 'export.pdf', $combiner));
        }

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
                            $c->content = preg_replace('/\<a href=\"\.\/(.*?)\".*?\>(.*?)\<\\/a\>/s', "$1", $c->content); // $1 = url, $2 = name
                            // Strip other links out
                            $c->content = preg_replace('/\<a.*? href=.*?\>(.*?)\<\\/a\>/s', "$1", $c->content); // $1 = name
                        }
                        $content = json_encode($content);
                        $filedata = preg_replace('/var blocks = \[.*?\];/', 'var blocks = ' . $content, $filedata);
                    }
                }
                else {
                    $filedata = preg_replace('/\<a href=\"\.\/(.*?)\".*?\>(.*?)\<\/a\>/s', "$1", $filedata); // $1 = url, $2 = name
                    // Strip other links out
                    $filedata = preg_replace('/\<a.*? href=.*?\>(.*?)\<\/a\>/s', "$1", $filedata); // $1 = name
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

                $page->pdf(['printBackground' => true,
                            'preferCSSPageSize' => true])->saveToFile($pdfname, 60000); // 60s timeout

                if (!file_exists($filename) || !is_readable($filename)) {
                    throw new SystemException("Could not read view page for creating pdf for $viewid");
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
            rename($view, $pdfdirectory . '/' . $file);
        }
        ob_end_clean();
    }
}
