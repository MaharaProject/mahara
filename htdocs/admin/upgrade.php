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
define('INSTALLER', 1);

require(dirname(dirname(__FILE__)).'/init.php');
require(get_config('libroot') . 'upgrade.php');

if (param_integer('finished', 0)) {
    foreach (site_warnings() as $w) {
        $SESSION->add_error_msg($w, FALSE);
    }
    if ($missing = check_installed_plugins()) {
        $message = get_string('installedpluginsmissing', 'admin') . ': ';
        $message .= join(', ', $missing) . '.';
        $message .= '  ' . get_string('ensurepluginsexist', 'admin', get_config('docroot'));
        $SESSION->add_error_msg($message);
    }
    redirect();
}

$smarty = smarty();

$upgrades = check_upgrades();
if (!empty($upgrades['core']->install)) {
    define('TITLE', get_string('installation', 'admin'));
    $smarty->assign('upgradeheading', get_string('performinginstallation', 'admin'));
    log_info('Starting Mahara installation...');
}
else {
    define('TITLE', get_string('upgrades', 'admin'));
    if (!db_is_utf8()) {
        global $SESSION;
        $SESSION->add_error_msg(get_string('dbnotutf8warning', 'admin'));
    }
    ensure_upgrade_sanity();
    $smarty->assign('upgradeheading', get_string('performingupgrades', 'admin'));
}

if (empty($upgrades['settings']['disablelogin'])) {
    auth_setup();
}
// Remove the "settings" component, which is not a real component (see check_upgrades())
unset($upgrades['settings']);

if (!$upgrades) {
    die_info(get_string('noupgrades', 'admin'));
}

$start = time();
if (empty($upgrades['core']->install)) {
    // Insert a record into config before the upgrade starts, to prevent subsequent hits
    // on this page from starting a second simultaneous upgrade.

    // But let the admin run a second one if they really want to.
    if (param_integer('rerun', 0)) {
        delete_records('config', 'field', '_upgrade');
    }

    if (!$lastupgrade = get_field('config', 'value', 'field', '_upgrade')) {
        try {
            insert_record('config', (object) array('field' => '_upgrade', 'value' => $start));
        }
        catch (SQLException $e) {
            if (!$lastupgrade = get_field('config', 'value', 'field', '_upgrade')) {
                $lastupgrade = '???';
            }
        }
    }

    if (!empty($lastupgrade)) {
        $laststart = format_date($lastupgrade, 'strftimedatetimeshort');
        log_debug('Not upgrading; unfinished upgrade from ' . $laststart . ' still in progress');
        die_info(get_string('upgradeinprogress', 'admin', $laststart));
    }
}

$loadingicon = $THEME->get_image_url('loading');
$successicon = $THEME->get_image_url('success');
$failureicon = $THEME->get_image_url('failure');

// Remove all files in the smarty and dwoo caches
// TODO post 1.2 remove the smarty part
require_once('file.php');
$basedir = get_config('dataroot') . 'smarty/compile/';
$dh = new DirectoryIterator($basedir);
foreach ($dh as $themedir) {
    if ($themedir->isDot()) continue;
    $themedirname = $basedir . $themedir->getFilename();
    rmdirr($themedirname);
    clearstatcache();
    check_dir_exists($themedirname);
}
$basedir = get_config('dataroot') . 'dwoo/compile/';
$dh = new DirectoryIterator($basedir);
foreach ($dh as $themedir) {
    if ($themedir->isDot()) continue;
    $themedirname = $basedir . $themedir->getFilename();
    rmdirr($themedirname);
    clearstatcache();
    check_dir_exists($themedirname);
}


$loadingstring = json_encode(get_string('upgradeloading', 'admin'));
$installsuccessstring = json_encode(get_string('installsuccess', 'admin'));
$successstring = json_encode(get_string('upgradesuccesstoversion', 'admin'));
$failurestring = json_encode(get_string('upgradefailure', 'admin'));
$coresuccess   = json_encode(get_string('coredatasuccess', 'admin'));
$localsuccess  = json_encode(get_string('localdatasuccess', 'admin'));

// Check if Mahara is being installed. An extra hook is required to insert core
// data if so.
if (!empty($upgrades['core']->install)) {
    raise_time_limit(120);
    raise_memory_limit('256M');
    $upgrades['firstcoredata'] = true;
    $upgrades['localpreinst'] = true;
    $upgrades['lastcoredata'] = true;
    $upgrades['localpostinst'] = true;
    $smarty->assign('install', true);
}
foreach ($upgrades as $key => $upgrade) {
    if (isset($upgrade->newinstall)) {
        unset($upgrades[$key]);
    }
}

$js = <<< EOJS
            function processNext() {
                var element = todo.shift();

                if (!element) {
                    $('finished').style.visibility = 'visible';
                    window.scrollTo(0, 5000000);
                    return; // done
                }

                $(element).innerHTML = '<img src="{$loadingicon}" alt="' + {$loadingstring} + '" />';

                sendjsonrequest('upgrade.json.php', { 'name': element, 'last': todo.length == 0 }, 'GET', function (data) {
                    if ( !data.error ) {
                        var message;
                        if (data.coredata) {
                            message = {$coresuccess};
                            $(data.key).innerHTML = '<img src="{$successicon}" alt=":)" />  ' + message;
                        }
                        else if (data.localdata) {
                            message = {$localsuccess};
                            $(data.key).innerHTML = '<img src="{$successicon}" alt=":)" />  ' + message;
                        }
                        else if (data.install || data.upgrade) {
                            if (data.install) {
                                message = {$installsuccessstring};
                            }
                            else {
                                if (data.message) {
                                    message = data.message;
                                }
                                else {
                                    message = {$successstring};
                                }
                            }
                            message += data.newversion ? data.newversion : '';
                            $(data.key).innerHTML = '<img src="{$successicon}" alt=":)" />  ' + message;
                        }
                        else {
                            message = data.message;
                            $(data.key).innerHTML = '<img src="{$failureicon}" alt=":(" /> ' + message;
                        }
                        if (data.feedback) {
                            var feedback_element = DIV();
                            feedback_element.innerHTML = data.feedback;
                            appendChildNodes('messages', feedback_element);
                        }
                        processNext();
                    }
                    else {
                        var message = '';
                        if (data.errormessage) {
                            message = data.errormessage;
                        }
                        else {
                            message = {$failurestring};
                        }
                        $(data.key).innerHTML = '<img src="{$failureicon}" alt=":(" /> ' + message;
                    }
                },
                function () {
                    $(element).innerHTML = '<img src="{$failureicon}" alt=":(" /> ' + {$failurestring};
                },
                true);
            }

            addLoadEvent( processNext );
EOJS;

uksort($upgrades, 'sort_upgrades');
$js .= "\n" . 'var todo = ' . json_encode(array_keys($upgrades)) . ";\n";
$smarty->assign('INLINEJAVASCRIPT', $js);

$smarty->assign_by_ref('upgrades', $upgrades);
if (isset($upgrades['core'])) {
    $smarty->assign('releaseargs', array($upgrades['core']->torelease, $upgrades['core']->to));
}
$smarty->display('admin/upgrade.tpl');

function check_installed_plugins() {
    $missing = array();

    foreach (plugin_types() as $plugintype) {
        if ($installed = plugins_installed($plugintype, true)) {
            foreach ($installed as $i) {
                $key = $i->name;
                if ($plugintype == 'blocktype') {
                    $key = blocktype_single_to_namespaced($i->name, $i->artefactplugin);
                }
                try {
                    safe_require($plugintype, $key);
                }
                catch (SystemException $e) {
                    $missing[] = "$plugintype:$key";
                }
            }
        }
    }

    return $missing;
}
