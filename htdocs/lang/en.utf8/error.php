<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package    mahara
 * @subpackage lang
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

// @todo<nigel>: most likely need much better descriptions here for these environment issues
$string['phpversion'] = 'Mahara will not run on PHP < 5.1.0. Please upgrade your PHP version, or move Mahara to a different host.';
$string['jsonextensionnotloaded'] = 'Your server configuration does not include the JSON extension. Mahara requires this in order to send some data to and from the browser. Please make sure that it is loaded in php.ini, or install it if it is not installed.';
$string['pgsqldbextensionnotloaded'] = 'Your server configuration does not include the pgsql extension. Mahara requires this in order to store data in a relational database. Please make sure that it is loaded in php.ini, or install it if it is not installed.';
$string['mysqldbextensionnotloaded'] = 'Your server configuration does not include the mysql extension. Mahara requires this in order to store data in a relational database. Please make sure that it is loaded in php.ini, or install it if it is not installed.';
$string['unknowndbtype'] = 'Your server configuration references an unknown database type. Valid values are "postgres8" and "mysql5". Please change the database type setting in config.php.';
$string['libxmlextensionnotloaded'] = 'Your server configuration does not include the libxml extension. Mahara requires this in order to parse XML data for the installer and for backups. Please make sure that it is loaded in php.ini, or install it if it is not installed.';
$string['gdextensionnotloaded'] = 'Your server configuration does not include the gd extension. Mahara requires this in order to perform resizes and other operations on uploaded images. Please make sure that it is loaded in php.ini, or install it if it is not installed.';
$string['sessionextensionnotloaded'] = 'Your server configuration does not include the session extension. Mahara requires this in order to support users logging in. Please make sure that it is loaded in php.ini, or install it if it is not installed.';
$string['opensslextensionnotloaded'] = 'Your server configuration does not include the openssl extension. Mahara does not require this, but it is necessary if you wish to enable networking support.';
$string['curlextensionnotloaded'] = 'Your server configuration does not include the curl extension. Mahara does not require this, but it is necessary if you wish to enable networking support.';
$string['xmlrpcextensionnotloaded'] = 'Your server configuration does not include the xmlrpc extension. Mahara does not require this, but it is necessary if you wish to enable networking support.';
$string['registerglobals'] = 'You have dangerous PHP settings, register_globals is on. Mahara is trying to work around this, but you should really fix it';
$string['magicquotesgpc'] = 'You have dangerous PHP settings, magic_quotes_gpc is on. Mahara is trying to work around this, but you should really fix it';
$string['magicquotesruntime'] = 'You have dangerous PHP settings, magic_quotes_runtime is on. Mahara is trying to work around this, but you should really fix it';
$string['magicquotessybase'] = 'You have dangerous PHP settings, magic_quotes_sybase is on. Mahara is trying to work around this, but you should really fix it';

$string['safemodeon'] = 'Your server appears to be running safe mode. Mahara does not support running in safe mode. You must turn this off in either the php.ini file, or in your apache config for the site.

If you are on shared hosting, it is likely that there is little you can do to get safe_mode turned off, other than ask your hosting provider. Perhaps you could consider moving to a different host.';
$string['datarootinsidedocroot'] = 'You have set up your data root to be inside your document root. This is a large security problem, as then anyone can directly request session data (in order to hijack other peoples\' sessions), or files that they are not allowed to access that other people have uploaded. Please configure the data root to be outside of the document root.';
$string['datarootnotwritable'] = 'Your defined data root directory, %s, is not writable. This means that neither session data, user files nor anything else that needs to be uploaded can be saved on your server. Please make the directory if it does not exist, or give ownership of the directory to the web server user if it does.';
$string['couldnotmakedatadirectories'] = 'For some reason some of the core data directories could not be created. This should not happen, as Mahara previously detected that the dataroot directory was writable. Please check the permissions on the dataroot directory.';

$string['dbconnfailed'] = 'Mahara could not connect to the application database.

 * If you are using Mahara, please wait a minute and try again
 * If you are the administrator, please check your database settings and make sure your database is available

The error received was:
';

// general exception error messages
$string['blocktypenametaken'] = "Block type %s is already taken by another plugin (%s)";
$string['artefacttypenametaken'] = "Artefact type %s is already taken by another plugin (%s)";
$string['classmissing'] = "class %s for type %s in plugin %s was missing";
$string['artefacttypeclassmissing'] = "Artefact types must all implement a class.  Missing %s";
$string['artefactpluginmethodmissing'] =  "Artefact plugin %s must implement %s and doesn't";
$string['blocktypelibmissing'] = 'Missing lib.php for block %s in artefact plugin %s';
$string['blocktypemissingconfigform'] = 'Block type %s must implement instance_config_form';
$string['versionphpmissing'] = 'Plugin %s %s is missing version.php!';
$string['blocktypeprovidedbyartefactnotinstallable'] = 'This will be installed as part of the installation of artefact plugin %s';
$string['blockconfigdatacalledfromset'] = 'Configdata should not be set directly, use PluginBlocktype::instance_config_save instead';
$string['invaliddirection'] = 'Invalid direction %s';

// if you change these next two , be sure to change them in libroot/errors.php
// as they are duplicated there, in the case that get_string was not available.
$string['unrecoverableerror'] = 'A nonrecoverable error occurred.  This probably means that you have encountered a bug in the system.';
$string['unrecoverableerrortitle'] = '%s - Site Unavailable';
$string['parameterexception'] = 'A required parameter was missing';
$string['accessdeniedexception'] = 'You do not have access to view this page';

$string['viewnotfoundexceptiontitle'] = 'View not found';
$string['viewnotfoundexceptionmessage'] = 'You tried to access a view that didn\'t exist!';

$string['artefactnotfoundmaybedeleted'] = "Artefact with id %s not found (maybe it has been deleted already?)";
$string['notartefactowner'] = 'You do not own this artefact';

$string['blockinstancednotfound'] = 'Block instance with id %s not found';
$string['interactioninstancenotfound'] = 'Interaction instance with id %s not found';

$string['invalidviewaction'] = 'Invalid view control action: %s';

$string['missingparamblocktype'] = 'Try selecting a block type to add first';
$string['missingparamcolumn'] = 'Missing column specification';
$string['missingparamorder'] = 'Missing order specification';
$string['missingparamid'] = 'Missing id';
?>
