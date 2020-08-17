<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 * Minimal config file suitable for test purposes. Most config items have a useful
 * default and all can be easily changed by reading a value from an environment
 * variable.
 */

/**
 * Gets a value from the named environment variable, if it exists, else
 * default is returned.
 */
function getenv_or_default($varname, $default = null) {
  $result = getenv($varname);
  if ($result) {
    return $result;
  }
  return $default;
}

$cfg = new stdClass();


/**
 * database connection details
 * valid values for dbtype are 'postgres' and 'mysql'
 */
$cfg->dbtype   = getenv_or_default('MAHARA_DB_TYPE', 'postgres');
$cfg->dbhost   = getenv_or_default('MAHARA_DB_HOST', 'localhost');
$cfg->dbport   = getenv_or_default('MAHARA_DB_PORT'); // Change if you are using a non-standard port number for your database
$cfg->dbname   = getenv_or_default('MAHARA_DB_NAME', 'mahara');
$cfg->dbuser   = getenv_or_default('MAHARA_DB_USER', 'mahara');
$cfg->dbpass   = getenv_or_default('MAHARA_DB_PASSWD', 'mahara');

$cfg->dataroot = getenv_or_default('MAHARA_DATA_ROOT', '/mahara/data');
$cfg->wwwroot = getenv_or_default('MAHARA_WWW_ROOT');

$cfg->sendemail            = getenv_or_default('MAHARA_SEND_EMAIL', false);
$cfg->sendallemailto       = getenv_or_default('MAHARA_SEND_ALL_EMAIL_TO');

// Behat config
$cfg->behat_dbprefix  = getenv_or_default('MAHARA_BEHAT_DB_PREFIX', 'behat_');
$cfg->behat_dataroot  = getenv_or_default('MAHARA_BEHAT_DATA_ROOT', '/mahara/data/behat');
$cfg->behat_wwwroot   = getenv_or_default('MAHARA_BEHAT_WWW_ROOT', 'http://localhost:8000');
$cfg->behat_selenium2 = getenv_or_default('MAHARA_BEHAT_SELENIUM2', 'http://127.0.0.1:4444/wd/hub');
