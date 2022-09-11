<?php
/**
 * The component library/map is information which can be pulled from the code
 * to help developers find things.
 * Currently this gets information about third party libraries.
 * @TODO Add other components.
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'development/componentmap');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'site');
define('SECTION_PAGE', 'map');

require('../init.php');

define('TITLE', get_string('componentmap', 'admin'));

$path = get_config('docroot');

// Get names of all the files
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
$files = array();
foreach ($rii as $file) {
    if ($file->isDir()) {
        continue;
    }
    $files[] = $file->getPathname();
}
// Get third party libraries by finding the README.Mahara files
$thirdparty = preg_grep ("/.*README\.[Mm]ahara$/", $files);

//pull the needed info from the READMEs.
$plugins = array();
$count = 0;
foreach ($thirdparty as $plugin) {
    $pluginobj = new stdClass();
    //saml doesn't live in a folder called saml, so set it manually
    if (strpos($plugin, 'auth/saml')) {
        $pluginobj->name = 'saml';
    }
    else {
        $pluginobj->name = preg_replace('/.*\/(.*?)\/README\.(M|m)ahara/', '$1', $plugin);
    }
    $pluginobj->path = preg_replace('%.*'.preg_quote($path).'(.*?)\/README\.(M|m)ahara%', '$1', $plugin);
    $file = file($plugin);
    $pluginobj->versions = array_values(preg_replace('/Version:\s/', '', preg_grep('/.*Version:?.*[0-9\.]*.*/', $file)));
    $pluginobj->websites = array_values(preg_replace('/Website:\s/', '', preg_grep('/.*Website.*http.*\n.*/', $file)));
    array_push($plugins, $pluginobj);
    $count++;
}

//sort alphabetically on plugin name
usort($plugins, function ($plugin1, $plugin2) {
    if ($plugin1->name === $plugin2->name) {
        return strcasecmp($plugin1->path, $plugin2->path);
    }
    return strcasecmp($plugin1->name, $plugin2->name);
});

//set up csv file
$csv_array = array();
foreach ($plugins as $plugin) {
    $csv = clone $plugin;
    $csv->versions = trim(implode('', $plugin->versions));
    $csv->websites = trim(implode('', $plugin->websites));
    array_push($csv_array, $csv);
 }
$csvfields = array('name', 'path', 'versions', 'websites');
$USER->set_download_file(generate_csv($csv_array, $csvfields), 'thirdpartyplugins.csv', 'text/csv');

$smarty = smarty();
setpageicon($smarty, 'icon-cubes');
$smarty->assign('plugins', $plugins);
$smarty->assign('SIDEBARS', false);
$smarty->assign('wwwroot', get_config('wwwroot'));
$smarty->display('map/componentmap.tpl');
