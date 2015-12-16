<?php
/**
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
//define('MENUITEM', 'configsite/behatsteps');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'behatsteps');

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
require_once('pieforms/pieform.php');
define('TITLE', get_string('behatvariables', 'admin'));

$dirpath = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/test/behat/features';

function read_dir_files(&$output, $dirpath) {

    $dir = new DirectoryIterator($dirpath);
    foreach ($dir as $file) {
        if ($file == '.' || $file == '..') {
            continue;
        }
        if (is_dir($dirpath . '/' . $file)) {
            read_dir_files($output, $dirpath . '/' . $file);
        }
        $gitroot = dirname(get_config('docroot'));
        $filename = str_replace($gitroot, '', str_replace('.', '_', $file->getPathname()));
        $filename = preg_replace('#/test/behat/features#', '', $filename); // trim of initial path
        $filename = preg_replace('/_feature$/', '', $filename); // trim off _feature
        $content = file_get_contents($file->getPathname());
        if (preg_match_all('/(Given|When|And|Then) I.*/', $content, $matches, PREG_OFFSET_CAPTURE) !== false) {
            if (!empty($matches[0])) {
                foreach ($matches[0] as $origmatch) {
                    $match = preg_replace('/\\\"/', "'", $origmatch[0]);
                    $match = preg_replace('/"[^"]+"/', '"?"', $match);
                    // make them all start with 'And' as that will be the most used
                    // so we don't have different rows in array for 'Given' vs 'And'
                    $match = preg_replace('/(Given|When|And|Then) /', 'And ', $match);
                    // Basic way to guess the line the match is on
                    $line_number = 1 + substr_count($content, "\n", 0, $origmatch[1]);
                    $output[$match][$filename][$line_number] = $origmatch[0];
                }
            }
        }
    }
    return $output;
}
$data = false;
if (file_exists($dirpath)) {
    read_dir_files($data, $dirpath);
    ksort($data);
}
$coredata = array();
$hascore = false;
// Get the core features
if (get_config('behat_dataroot')) {
    // Find the behat.yml file
    require_once(get_config('docroot') . 'testing/frameworks/behat/classes/util.php');
    $behatyml = BehatTestingUtil::get_behat_config_path();
    if (is_readable($behatyml)) {
        $hascore = true;
        // Run the behat config command to get list of core features
        $behatconfig = 'export BEHAT_PARAMS=\'{"gherkin" : {"cache" : null}}\' && php ' . dirname(get_config('docroot')) . '/external/vendor/behat/behat/bin/behat --config ' . $behatyml . ' -dl 2>&1';
        $corefeatures = shell_exec($behatconfig);
        $corefeatures = explode(PHP_EOL, trim($corefeatures));
        if (!empty($corefeatures)) {
            // Need to merge the core features with the used features
            foreach ($corefeatures as $feature) {
                if (preg_match('/\^(.*?)\$/', $feature, $match)) {
                    $submatch = preg_replace('/^\(\?\:\|I \)/', 'And I ', $match[1]);
                    $submatch = preg_replace('/\(\?\:.*?\)/', '', $submatch);
                    $submatch = preg_replace('/\(.*?\)\"/', '?"', $submatch);
                    $submatch = preg_replace('/\(\?P.*?\)/', '"?"', $submatch);
                    $submatch = preg_replace('/\(the \)\?/', '', $submatch);
                    $submatch = preg_replace('/\:/', ' "?"', $submatch);
                    $submatch = preg_replace('/^I/', 'And I', $submatch);
                    $coredata[$submatch] = true;
                }
            }
        }
    }
    if (!empty($coredata)) {
        foreach ($coredata as $k => $v) {
            if (!array_key_exists($k, $data)) {
                $data[$k] = 'notused';
            }
        }
        ksort($data);
    }
}

$smarty = smarty(array());
setpageicon($smarty, 'icon-cogs');
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('data', $data);
$smarty->assign('hascore', $hascore);
$smarty->display('testing/behatvariables.tpl');
