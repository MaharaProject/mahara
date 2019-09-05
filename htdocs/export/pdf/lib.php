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
require_once(get_config('docroot') . 'export/html/lib.php');
$pdfactive = false;
if (db_table_exists('export_installed')) {
    $pdfactive = get_field('export_installed', 'active', 'name', 'pdf');
}
if ($pdfactive && !file_exists(get_config('docroot') . 'lib/chrome-php/headless-chromium-php-master/vendor/autoload.php')) {
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
    require_once(get_config('docroot') . 'lib/chrome-php/headless-chromium-php-master/vendor/autoload.php');
}
use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Cookies\Cookie;

/**
 * HTML export plugin
 */
class PluginExportPdf extends PluginExportHtml {

    /**
    * constructor.  overrides the parent class
    * to set up smarty and the attachment directory
    */
    public function __construct(User $user, $views, $artefacts, $progresscallback=null) {
        global $THEME;
        parent::__construct($user, $views, $artefacts, $progresscallback);
        $this->exporttype = 'pdf';

        $this->zipfile = 'mahara-export-pdf-user'
            . $this->get('user')->get('id') . '-' . $this->exporttime . '.zip';
    }

    public static function get_title() {
        return get_string('title', 'export.pdf');
    }

    public static function get_description() {
        return get_string('description', 'export.pdf');
    }

    public static function get_plugin_display_name() {
        return 'PDF';
    }

    public static function has_plugin_dependencies() {
        $needs = get_string('needschromeheadless', 'export.pdf');
        // make sure that composer has installed the headlessbrowser hook
        $requires = array();
        if (!file_exists(get_config('docroot') . 'lib/chrome-php/headless-chromium-php-master/src/BrowserFactory.php')) {
            $requires[] = get_string('needschromeheadlessphp', 'export.pdf');
        }
        if ($pdfunite = exec('apt -qq list poppler-utils')) {
            if (!preg_match('/\[installed\]/', $pdfunite)) {
                $requires[] = get_string('needspdfunite', 'export.pdf');
            }
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
     */
    public function export($createarchive=false) {
        // Only call parent if we do not do pdf export after html export
        if ($createarchive) {
            parent::export($createarchive);
        }
        $this->pdf_view_export_data();
        return true;
    }

    /**
     * Dumps all views into the HTML export
     */
    private function pdf_view_export_data() {
        global $pdfrun;
        static $browser;
        static $page;

        $progressstart = 85;
        $progressend   = 95;
        $i = 0;
        $viewcount = count($this->views);

        $browsertype = 'chromium-browser';
        system('dpkg -l | grep ' . $browsertype, $error);
        if ($error) {
            $browsertype = 'chrome';
            system('dpkg -l | grep ' . $browsertype, $error2);
            if ($error2) {
                throw new MaharaException('Need to have a Chrome browser installed to use the headless pdf option');
            }
        }
        if (!isset($pdfrun) || $pdfrun == 'first' || $pdfrun == 'all') {
            $browserFactory = new BrowserFactory($browsertype);
            // starts headless chrome
            $browser = $browserFactory->createBrowser(['windowSize' => [1280,800],
                                                       'ignoreCertificateErrors' => true,
                                                       'connectionDelay' => 0.8]);

            // creates a new page and navigate to an url
            $page = $browser->createPage();
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
                $this->notify_progress_callback(intval($progressstart + (++$i / $viewcount) * ($progressend - $progressstart)), get_string('exportingviewsprogresspdf', 'export', $i, $viewcount));

                if ($this->exportingoneview) {
                    $directory = $this->exportdir . '/' . $this->rootdir;
                }
                else {
                    $directory = $this->exportdir . '/' . $this->rootdir . '/views/' . parent::text_to_filename($view->get('title'));
                }
                $filename = $directory . "/index.html";
                // Adjust the relative links to files to be textual to mention where the file lives within the zip file
                // Because we can't make relative links in a pdf export
                $filedata = file_get_contents($filename);
                if ($view->get('newlayout')) {
                    if (preg_match('/var blocks = (\[.*?\]);/', $filedata, $matches)) {
                        $content = json_decode($matches[1]);
                        foreach ($content as $c) {
                            $c->content = preg_replace('/\<a href=\"\.\/(.*?)\".*?\>(.*?)\<\/a\>/s', "$1", $c->content); // $1 = url, $2 = name
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
                $page->navigate('file://' . $filename)->waitForNavigation();
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
                            'preferCSSPageSize' => true])->saveToFile($pdfname);

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
        foreach ($colpdfs as $collectionid => $collection) {
             $collectionname = $this->collections[$collectionid]->get('name');
             $collectionname = preg_replace('/\s+/', '_', $collectionname);
             exec('pdfunite ' . implode(' ', $collection) . ' ' . $directory . '/' . $collectionid . '_' . $collectionname . '.pdf', $output);
             // remove the page pdfs that are now in collections
             foreach ($collection as $c) {
                 unlink($c);
             }
        }
        // Move view PDF files to same place as collection files
        foreach ($viewpdfs as $view) {
            $path = explode('/', $view);
            $file = array_pop($path);
            rename($view, $directory . '/' . $file);
        }
    }
}