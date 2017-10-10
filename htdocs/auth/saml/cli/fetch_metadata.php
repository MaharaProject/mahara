<?php
/**
 *
 * @package    mahara
 * @subpackage auth-saml
 * @author     Francis Devine <francis@catalyst.net.nz>
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) 2011 INSA de Lyon France
 *
 *
 * This will manually trigger the metarefresh hook outside of the cron context
 * useful for debugging any issues that you might find
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('INSTALLER', 1);
define('CLI', 1);

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
require(get_config('libroot') . 'cli.php');
require(get_config('docroot') . 'auth/saml/lib.php');
$cli = get_cli();

$options = array();

$settings = new stdClass();
$settings->options = $options;

$cli->setup($settings);

try {
    Metarefresh::metadata_refresh_hook();
}
// we catch any unexpected errors (inner hook also has a try catch since it runs in cron)
catch (Exception $e) {
    cli::cli_exit($e->getMessage(), true);
}

cli::cli_exit('---------- ended at ' . date('r', time()) . ' ----------', true);
