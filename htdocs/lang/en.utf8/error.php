<?php
/**
 *
 * @package    mahara
 * @subpackage lang
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

// @todo<nigel>: most likely need much better descriptions here for these environment issues
$string['phpversion'] = 'Mahara will not run on PHP < %s. Please upgrade your PHP version or move Mahara to a different host.';
$string['jsonextensionnotloaded'] = 'Your server configuration does not include the JSON extension. Mahara requires this in order to send some data to and from the browser. Please make sure that it is loaded in php.ini or install it if it is not installed.';
$string['pgsqldbextensionnotloaded'] = 'Your server configuration does not include the pgsql extension. Mahara requires this in order to store data in a relational database. Please make sure that it is loaded in php.ini or install it if it is not installed.';
$string['mysqldbextensionnotloaded'] = 'Your server configuration does not include the mysqli or mysql extension. Mahara requires this in order to store data in a relational database. Please make sure that it is loaded in php.ini or install it if it is not installed.';
$string['unknowndbtype'] = 'Your server configuration references an unknown database type. Valid values are "postgres" and "mysql". Please change the database type setting in config.php.';
$string['domextensionnotloaded'] = 'Your server configuration does not include the dom extension. Mahara requires this in order to parse XML data from a variety of sources.';
$string['xmlextensionnotloaded'] = 'Your server configuration does not include the %s extension. Mahara requires this in order to parse XML data from a variety of sources. Please make sure that it is loaded in php.ini or install it if it is not installed.';
$string['gdextensionnotloaded'] = 'Your server configuration does not include the gd extension. Mahara requires this in order to perform resizes and other operations on uploaded images. Please make sure that it is loaded in php.ini or install it if it is not installed.';
$string['gdfreetypenotloaded'] = 'Your server configuration of the gd extension does not include Freetype support. Please make sure that gd is configured with it.';
$string['sessionextensionnotloaded'] = 'Your server configuration does not include the session extension. Mahara requires this in order to support users logging in. Please make sure that it is loaded in php.ini or install it if it is not installed.';
$string['curllibrarynotinstalled'] = 'Your server configuration does not include the curl extension. Mahara requires this for Moodle integration and to retrieve external feeds. Please make sure that curl is loaded in php.ini or install it if it is not installed.';
$string['registerglobals'] = 'You have dangerous PHP settings: register_globals is on. Mahara is trying to work around this, but you should really fix it. If you are using shared hosting and your host allows for it, you should include the following line in your .htaccess file:
php_flag register_globals off';
$string['magicquotesgpc'] = 'You have dangerous PHP settings: magic_quotes_gpc is on. Mahara is trying to work around this, but you should really fix it. If you are using shared hosting and your host allows for it, you should include the following line in your .htaccess file:
php_flag magic_quotes_gpc off';
$string['magicquotesruntime'] = 'You have dangerous PHP settings: magic_quotes_runtime is on. Mahara is trying to work around this, but you should really fix it. If you are using shared hosting and your host allows for it, you should include the following line in your .htaccess file:
php_flag magic_quotes_runtime off';
$string['magicquotessybase'] = 'You have dangerous PHP settings: magic_quotes_sybase is on. Mahara is trying to work around this, but you should really fix it. If you are using shared hosting and your host allows for it, you should include the following line in your .htaccess file:
php_flag magic_quotes_sybase off';

$string['safemodeon'] = 'Your server appears to be running safe mode. Mahara does not support running in safe mode. You must turn this off in either the php.ini file or in your apache config for the site.

If you are on shared hosting, it is likely that there is little you can do to get safe mode turned off other than ask your hosting provider. Perhaps you could consider moving to a different host.';
$string['apcstatoff'] = 'Your server appears to be running APC with apc.stat=0. Mahara does not support this configuration. You must set apc.stat=1 in the php.ini file.

If you are on shared hosting, it is likely that there is little you can do to get apc.stat turned on other than ask your hosting provider. Perhaps you could consider moving to a different host.';
$string['datarootinsidedocroot'] = 'You have set up your data root to be inside your document root. This is a large security problem as then anyone can directly request session data (in order to hijack other people\'s sessions) or files that they are not allowed to access that other people have uploaded. Please configure the data root to be outside of the document root.';
$string['datarootnotwritable'] = 'Your defined data root directory, %s, is not writable. This means that neither session data, user files nor anything else that needs to be uploaded can be saved on your server. Please make the directory if it does not exist or give ownership of the directory to the web server user if it does.';
$string['sessionpathnotwritable'] = 'Your session data directory, %s, is not writable. Please create the directory if it does not exist or give ownership of the directory to the web server user if it does.';
$string['wwwrootnothttps'] = 'Your defined wwwroot, %s, is not HTTPS. However, other settings (such as sslproxy) for your installation require that your wwwroot is a HTTPS address.

Please update your wwwroot setting to be a HTTPS address or fix the incorrect setting.';
$string['couldnotmakedatadirectories'] = 'For some reason some of the core data directories could not be created. This should not happen as Mahara previously detected that the dataroot directory was writable. Please check the permissions on the dataroot directory.';

$string['dbconnfailed'] = 'Mahara could not connect to the application database.

 * If you are using Mahara, please wait a minute and try again
 * If you are the administrator, please check your database settings and make sure your database is available

The error received was:
';
$string['dbnotutf8'] = 'You are not using a UTF-8 database. Mahara stores all data as UTF-8 internally. Please drop and re-create your database using UTF-8 encoding.';
$string['dbversioncheckfailed'] = 'Your database server version is not new enough to successfully run Mahara. Your server is %s %s, but Mahara requires at least version %s.';
$string['plpgsqlnotavailable'] = 'The PL/pgSQL language is not enabled in your Postgres installation, and Mahara cannot enable it. Please install PL/pgSQL in your database manually. For instructions on how to do this, see https://wiki.mahara.org/wiki/System_Administrator\'s_Guide/Enabling_Plpgsql';
$string['mysqlnotriggerprivilege'] = 'Mahara requires permission to create database triggers, but is unable to do so. Please ensure that the trigger privilege has been granted to the appropriate user in your MySQL installation. For instructions on how to do this, see https://wiki.mahara.org/wiki/System_Administrator\'s_Guide/Granting_Trigger_Privilege';
$string['mbstringneeded'] = 'Please install the mbstring extension for php. This is needed if you have UTF-8 characters in usernames. Otherwise, users might not be able to login.';
$string['cssnotpresent'] = 'CSS files are not present in your htdocs/theme/raw/style directory. If you are running Mahara from a git checkout, run "make css" to build the CSS files. If you are running Mahara from a ZIP download, try downloading and unzipping again.';

// general exception error messages
$string['blocktypenametaken'] = "Block type %s is already taken by another plugin (%s).";
$string['artefacttypenametaken'] = "Artefact type %s is already taken by another plugin (%s).";
$string['artefacttypemismatch'] = "Artefact type mismatch. You are trying to use this %s as a %s.";
$string['classmissing'] = "class %s for type %s in plugin %s was missing.";
$string['artefacttypeclassmissing'] = "Artefact types must all implement a class. Missing %s.";
$string['artefactpluginmethodmissing'] =  "Artefact plugin %s must implement %s and does not.";
$string['blocktypelibmissing'] = 'Missing lib.php for block %s in artefact plugin %s.';
$string['unabletosetmultipleblogs'] = 'Enabling multiple journals for the user %s when copying page %s has failed. This can be set manually on the <a href="%s">account</a> page.';
$string['pleaseloginforjournals'] = 'You need to log out and log back in before you will see all your journals and posts.';
$string['blocktypemissingconfigform'] = 'Block type %s must implement instance_config_form.';
$string['versionphpmissing'] = 'Plugin %s %s is missing version.php.';
$string['blocktypeprovidedbyartefactnotinstallable'] = 'This will be installed as part of the installation of artefact plugin %s.';
$string['blockconfigdatacalledfromset'] = 'Configdata should not be set directly. Use PluginBlocktype::instance_config_save instead.';
$string['invaliddirection'] = 'Invalid direction %s.';
$string['onlyoneprofileviewallowed'] = 'You are only allowed one profile page.';
$string['onlyoneblocktypeperview'] = 'Cannot put more than one %s block type into a page.';

// if you change these next two , be sure to change them in libroot/errors.php
// as they are duplicated there, in the case that get_string was not available.
$string['unrecoverableerror'] = 'A nonrecoverable error occurred. This probably means that you have encountered a bug in the system.';
$string['unrecoverableerrortitle'] = '%s - Site unavailable';
$string['parameterexception'] = 'A required parameter was missing.';

$string['notfound'] = 'Not found';
$string['notfoundexception'] = 'The page you are looking for could not be found.';

$string['accessdenied'] = 'Access denied';
$string['accessdeniedobjection'] = 'Access denied. The objection has already been resolved by another administrator.';
$string['accessdeniedexception'] =  'You do not have access to view this page.';
$string['accessdeniednourlsecret'] =  'You do not have access to this functionality. Please provide the value for "urlsecret" from your config.php file as part of the URL.';
$string['accessdeniedbadge'] =  'You do not have access to view this badge.';

$string['viewnotfoundexceptiontitle'] = 'Page not found';
$string['viewnotfoundexceptionmessage'] = 'You tried to access a page that does not exist.';
$string['viewnotfound'] = 'Page with id %s not found.';
$string['viewnotfoundbyname'] = 'Page %s by %s not found.';
$string['youcannotviewthisusersprofile'] = 'You cannot view this user\'s profile.';
$string['invalidlayoutselection'] = 'You tried to select a layout that doesn\'t exist.';
$string['invalidnumrows'] = 'You have tried to create a layout with more than the allowed maximum number of rows. (This should not be possible; please notify your site\'s administrator.)';
$string['previewimagegenerationfailed'] = 'Sorry, there was a problem generating the preview image.';

$string['artefactnotfoundmaybedeleted'] = "Artefact with id %s not found (maybe it has been deleted already?)";
$string['artefactnotfound'] = 'Artefact with id %s not found';
$string['artefactsnotfound'] = 'No artefact(s) found with the id(s): %s';
$string['artefactnotinview'] = 'Artefact %s not in page %s';
$string['artefactonlyviewableinview'] = 'Artefacts of this type are only viewable within a page.';
$string['notartefactowner'] = 'You do not own this artefact.';

$string['blockinstancenotfound'] = 'Block instance with id %s not found.';
$string['interactioninstancenotfound'] = 'Activity instance with id %s not found.';

$string['invalidviewaction'] = 'Invalid page control action: %s';

$string['missingparamblocktype'] = 'Try selecting a block type to add first.';
$string['missingparamcolumn'] = 'Missing column specification';
$string['missingparamrow'] = 'Missing row specification';
$string['missingparamorder'] = 'Missing order specification';
$string['missingparamid'] = 'Missing id';

$string['themenameinvalid'] = "The name of the theme '%s' contains invalid characters.";

$string['timezoneidentifierunusable'] = 'PHP on your website host does not return a useful value for the timezone identifier (%%z) - certain date formatting, such as the Leap2A export, will be broken. %%z is a PHP date formatting code. This problem is usually due to a limitation in running PHP on Windows.';
$string['postmaxlessthanuploadmax'] = 'Your PHP post_max_size setting (%s) is smaller than your upload_max_filesize setting (%s). Uploads larger than %s will fail without displaying an error. Usually, post_max_size should be much larger than upload_max_filesize.';
$string['smallpostmaxsize'] = 'Your PHP post_max_size setting (%s) is very small. Uploads larger than %s will fail without displaying an error.';
$string['notenoughsessionentropy'] = 'Your PHP session.entropy_length setting is too small. Set it to at least 16 in your php.ini to ensure that generated session IDs are random and unpredictable enough.';
$string['switchtomysqli'] = 'The <strong>mysqli</strong> PHP extension is not installed on your server. Thus, Mahara is falling back to the deprecated original <strong>mysql</strong> PHP extension. We recommend installing <a href="http://php.net/manual/en/book.mysqli.php">mysqli</a>.';
$string['noreplyaddressmissingorinvalid'] = 'The noreply address setting is either empty or has an invalid email address. Please check the configuration in the <a href="%s">site options in the email settings</a>.';
$string['openbasedirenabled'] = 'Your server has the php open_basedir restriction enabled.';
$string['openbasedirpaths'] = 'Mahara can only open files within the following path(s): %s.';
$string['openbasedirwarning'] = 'Some requests for external sites may fail to complete. This could stop certain feeds from updating among other things.';

$string['gdlibrarylacksgifsupport'] = 'The installed PHP GD library does not support both creating and reading GIF images. Full support is needed to upload GIF images.';
$string['gdlibrarylacksjpegsupport'] = 'The installed PHP GD library does not support JPEG/JPG images. Full support is needed to upload JPEG/JPG images.';
$string['gdlibrarylackspngsupport'] = 'The installed PHP GD library does not support PNG images. Full support is needed to upload PNG images.';

$string['nopasswordsaltset'] = 'No sitewide password salt has been set. Edit your config.php and set the "passwordsaltmain" parameter to a reasonable secret phrase.';
$string['passwordsaltweak'] = 'Your sitewide password salt is not strong enough. Edit your config.php and set the "passwordsaltmain" parameter to a longer secret phrase.';
$string['urlsecretweak'] = 'The $cfg->urlsecret set for this site has not been changed from the default value. Edit your config.php and set the $cgf->urlsecret parameter to a different string (or null if you do not wish to use a urlsecret).';
$string['notproductionsite'] = 'This site is not in production mode. Some data may not be available and/or may be out of date.';
