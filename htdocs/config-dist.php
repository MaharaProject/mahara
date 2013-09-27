<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * MAHARA CONFIGURATION FILE
 *
 * INSTRUCTIONS:
 * 1. Copy this file from config-dist.php to config.php
 * 2. Change the values in it to suit your environment.
 *
 * Information about this file is available on the Mahara wiki:
 *     http://wiki.mahara.org/System_Administrator's_Guide/Installing_Mahara#Create_Mahara's_config.php
 *
 * This file includes only the most commonly used Mahara configuration directives. For more options
 * that can be placed in this file, see the Mahara lib file
 *     htdocs/lib/config-defaults.php
 */

$cfg = new stdClass();


/**
 * database connection details
 * valid values for dbtype are 'postgres' and 'mysql'
 */
$cfg->dbtype   = 'postgres';
$cfg->dbhost   = 'localhost';
$cfg->dbport   = null;
$cfg->dbname   = '';
$cfg->dbuser   = '';
$cfg->dbpass   = '';

/**
 * Note: database prefix is NOT required, you don't need to set one except if
 * you're installing Mahara into a database being shared with other
 * applications (this happens most often on shared hosting)
 */
$cfg->dbprefix = '';

/**
 * wwwroot - the web-visible path to your Mahara installation
 * Normally, this is automatically detected - if it doesn't work for you
 * then try specifying it here.
 * This value must end with a /
 *
 * Example: $cfg->wwwroot = 'http://myhost.com/mahara/';
 *
 * If you want to serve all of your Mahara content via HTTPS, just set
 * $cfg->wwwroot to use HTTPS.
 */
// $cfg->wwwroot = 'https://myhost.com/mahara/';

/**
 * If you are using a proxy to force HTTPS connections, you will need to
 * enable the next line. If you have set this to true, ensure your wwwroot
 * is a HTTPS address.
 */
// $cfg->sslproxy = true;

/**
 * dataroot - uploaded files are stored here
 * This is a ABSOLUTE FILESYSTEM PATH. This is NOT a URL.
 * For example, valid paths are:
 *  * /home/user/maharadata
 *  * /var/lib/mahara
 *  * c:\maharadata
 * INVALID paths:
 *  * http://yoursite/files
 *  * ~/files
 *  * ../data
 *
 * This path must be writable by the webserver and outside document root (the
 * place where the Mahara files like index.php have been installed).
 * Mahara will NOT RUN if this is inside your document root, because
 * this is a big security hole.
 */
$cfg->dataroot = '/path/to/uploaddir';

/**
 * If set, this email address will be displayed in the error message if a form
 * submission is suspected of being spam. This reduces the frustration for the
 * user in the event of a false positive.
 */
$cfg->emailcontact = '';

/**
 * Set this to enable a secondary hash that is only present in the config file
 */
// $cfg->passwordsaltmain = 'some long random string here with lots of characters';

/**
 * When changing the salt (or disabling it), you will need to set the current salt as an alternate salt
 * There are up to 20 alternate salts
 */
$cfg->passwordsaltalt1 = 'old salt value';

/**
 * Uncomment the following line if this server is not a production system.
 * This will put a line up the top of the page saying that it isn't a production
 * site, and that files may not be present.
 */
//$cfg->productionmode = false;

// closing php tag intentionally omitted to prevent whitespace issues
