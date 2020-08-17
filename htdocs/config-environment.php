<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 * Mahara configuration file that gets the configuration values from
 * environment variables or default values if the variable is not defined.
 * This configuration is intended for when running Mahara in a dockerized
 * container.
 *
 * For descriptions of what these config items are and what they should be set to
 * refer to:
 * - config-dist.php
 * - lib/config-defaults.php
 *
 * Note that boolean values should be set with either:
 * - 0, false
 * - 1, true
 */

/**
 * Sets $cfg->$cfg_key with either:
 * - value of environment variable $varname if that is set
 * - else $default if specified
 *
 * If neither $varname or $default are set then $cfg->$cfg_key is left
 * unset.
 *
 */
function set_from_env(&$cfg, $cfg_key, $varname, $default = null) {
  $value = getenv($varname);
  if ($value !== false) {
    $cfg->$cfg_key = $value;
  }
  else if ($default) {
    $cfg->$cfg_key = $default;
  }
}

$cfg = new stdClass();

// database connection details
// valid values for dbtype are 'postgres8' and 'mysql5'
set_from_env($cfg, 'dbtype', 'MAHARA_DB_TYPE', 'postgres');
set_from_env($cfg, 'dbhost', 'MAHARA_DB_HOST', '127.0.0.1');
set_from_env($cfg, 'dbport', 'MAHARA_DB_PORT');
set_from_env($cfg, 'dbuser', 'MAHARA_DB_USER', 'mahara');
set_from_env($cfg, 'dbname', 'MAHARA_DB_NAME', 'mahara');
set_from_env($cfg, 'dbpass', 'MAHARA_DB_PASSWD', 'mahara');

set_from_env($cfg, 'dataroot', 'MAHARA_DATA_ROOT', '/mahara/data');
set_from_env($cfg, 'wwwroot', 'MAHARA_WWW_ROOT');

set_from_env($cfg, 'sslproxy', 'MAHARA_SSL_PROXY');

set_from_env($cfg, 'smtphosts', 'MAHARA_SMTP_HOSTS');
set_from_env($cfg, 'smtpport', 'MAHARA_SMTP_PORT');
set_from_env($cfg, 'smtpuser', 'MAHARA_SMTP_USER');
set_from_env($cfg, 'smtppass', 'MAHARA_SMTP_PASS');
set_from_env($cfg, 'smtpverifypeer', 'MAHARA_SMTP_VERIFY_PEER');
set_from_env($cfg, 'smtpallowselfsigned', 'MAHARA_SMTP_ALLOW_SELF_SIGNED');
set_from_env($cfg, 'sendemail', 'MAHARA_SEND_EMAIL');
set_from_env($cfg, 'sendallemailto', 'MAHARA_SEND_ALL_EMAIL_TO');

set_from_env($cfg, 'productionmode', 'MAHARA_PRODUCTION_MODE');;
set_from_env($cfg, 'perftofoot', 'MAHARA_PERF_TO_FOOT');

set_from_env($cfg, 'usepdfexport', 'MAHARA_USE_PDF_EXPORT');

set_from_env($cfg, 'skins', 'MAHARA_SKINS');

set_from_env($cfg, 'isolatedinstitutions', 'MAHARA_ISOLOATED_INSTITUTIONS');

set_from_env($cfg, 'dbprefix', 'MAHARA_DB_PREFIX');

set_from_env($cfg, 'sitethemeprefs', 'MAHARA_SITE_THEME_PREFS');
set_from_env($cfg, 'cleanurls', 'MAHARA_CLEAN_URLS');
set_from_env($cfg, 'publicsearchallowed', 'MAHARA_PUBLIC_SEARCH_ALLOWED');
set_from_env($cfg, 'probationenabled', 'MAHARA_PROBATION_ENABLED');
set_from_env($cfg, 'showloginsideblock', 'MAHARA_SHOW_LOGIN_INSIDE_BLOCK');

set_from_env($cfg, 'externallogin', 'MAHARA_EXTERNAL_LOGIN');

set_from_env($cfg, 'urlsecret', 'MAHARA_URL_SECRET');
set_from_env($cfg, 'passwordsaltmain', 'MAHARA_PASSWORD_SALT_MAIN');
set_from_env($cfg, 'passwordsaltalt1', 'MAHARA_PASSWORD_SALT_ALT1');

set_from_env($cfg, 'sessionhandler', 'MAHARA_SESSION_HANDLER');

set_from_env($cfg, 'redisserver', 'MAHARA_REDIS_SERVER');
set_from_env($cfg, 'redissentinelservers', 'MAHARA_REDIS_SENTINEL_SERVERS');
set_from_env($cfg, 'redismastergroup', 'MAHARA_REDIS_MASTER_GROUP');
set_from_env($cfg, 'redisprefix', 'MAHARA_REDIS_PREFIX');

set_from_env($cfg, 'plugin_search_elasticsearch_host', 'MAHARA_ELASTICSEARCH_HOST');
set_from_env($cfg, 'plugin_search_elasticsearch_port', 'MAHARA_ELASTICSEARCH_PORT');
set_from_env($cfg, 'plugin_search_elasticsearch_scheme', 'MAHARA_ELASTICSEARCH_SCHEME');
set_from_env($cfg, 'plugin_search_elasticsearch_username', 'MAHARA_ELASTICSEARCH_USERNAME');
set_from_env($cfg, 'plugin_search_elasticsearch_password', 'MAHARA_ELASTICSEARCH_PASSWD');
set_from_env($cfg, 'plugin_search_elasticsearch_indexingusername', 'MAHARA_ELASTICSEARCH_INDEXING_USERNAME');
set_from_env($cfg, 'plugin_search_elasticsearch_indexingpassword', 'MAHARA_ELASTICSEARCH_INDEXING_PASSWD');
set_from_env($cfg, 'plugin_search_elasticsearch_indexname', 'MAHARA_ELASTICSEARCH_INDEX_NAME');
set_from_env($cfg, 'plugin_search_elasticsearch_bypassindexname', 'MAHARA_ELASTICSEARCH_BYPASS_INDEX_NAME');
set_from_env($cfg, 'plugin_search_elasticsearch_analyzer', 'MAHARA_ELASTICSEARCH_ANALYZER');
set_from_env($cfg, 'plugin_search_elasticsearch_types', 'MAHARA_ELASTICSEARCH_TYPES');
set_from_env($cfg, 'plugin_search_elasticsearch_ignoressl', 'MAHARA_ELASTICSEARCH_IGNORE_SSL');
set_from_env($cfg, 'plugin_search_elasticsearch_requestlimit', 'MAHARA_ELASTICSEARCH_REQUEST_LIMIT');
set_from_env($cfg, 'plugin_search_elasticsearch_redolimit', 'MAHARA_ELASTICSEARCH_REDO_LIMIT');


// Behat config: This is only used for testing
set_from_env($cfg, 'behat_dbprefix', 'MAHARA_BEHAT_DB_PREFIX', 'behat_');
set_from_env($cfg, 'behat_dataroot', 'MAHARA_BEHAT_DATA_ROOT', '/mahara/data/behat');
set_from_env($cfg, 'behat_wwwroot', 'MAHARA_BEHAT_WWW_ROOT', 'http://localhost:8000');
set_from_env($cfg, 'behat_selenium2', 'MAHARA_BEHAT_SELENIUM2', 'http://127.0.0.1:4444/wd/hub');
