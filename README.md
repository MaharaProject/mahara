@TODO This is only a draft. Review before the final .0 release and update the information accordingly.

# Mahara Readme

Mahara is an open source ePortfolio system. An ePortfolio is a type of
web application that allows users to record and share evidence of lifelong learning.

For more information about Mahara and ePortfolios, see the Mahara user manual's
introduction chapter:

 * https://manual.mahara.org/en/21.04/intro/introduction.html#what-is-mahara

# SUPPORT

The best ways of obtaining support are:

 * https://manual.mahara.org - the Mahara online user manual
 * https://mahara.org/forums - the Mahara project community forums
 * https://wiki.mahara.org - the Mahara wiki
 * #mahara on freenode (IRC) - live chat with other users and developers

# INSTALLATION

For detailed installation instructions see the installation page on our wiki:

 * https://wiki.mahara.org/wiki/System_Administrator%27s_Guide/Installing_Mahara

The following is a check list of the steps you need to take. Please refer to the installation instructions for the details:

 1. Create a Postgres or MySQL database for Mahara.
 2. Copy the Mahara files under "htdocs" into your web root.
 3. Create a Mahara "dataroot" directory outside of your web root.
 4. Copy htdocs/config-dist.php to config.php.
 5. Edit config.php with the correct details for your installation.

# UPGRADING

Mahara 21.04 supports direct upgrades from previous Mahara versions 18.04.0 and later.

If you are upgrading from an earlier version you will need to upgrade
in either two or three steps:

if site version begins with 1.X first upgrade to 15.04

 * Upgrade version 'X' -> 15.04.X (latest 15.04 version)

then/else upgrade version to 17.04

 * Upgrade version 'X' -> 17.04.X (latest 17.04 version)

then/else

 * Upgrade version 'X' -> 18.04.X

then

 * Upgrade version 18.04.X -> 'Y'

Note: Older versions of Mahara are not compatible with latest versions
of PHP so you will need to do intermediate upgrade steps before
updating your server's PHP

To upgrade an existing Mahara installation, follow the instructions here:

 * https://wiki.mahara.org/wiki/System_Administrator%27s_Guide/Upgrading_Mahara

If you are upgrading from Mahara 15.10 or earlier, you will need to add a "urlsecret"
value to your config.php file if you wish to use the web-based upgrade and/or cron
scripts. See:

 * https://wiki.mahara.org/wiki/System_Administrator%27s_Guide/Upgrading_Mahara#Q:_I.27m_getting_an_error_about_a_.22urlsecret.22

# SYSTEM REQUIREMENTS

Here are the system requirements needed to run Mahara 21.04.

### OS

Mahara is only officially supported on Debian (8.0/"Jessie" LTS or later) and
Ubuntu (16.04/"Xenial Xerus" LTS or later), however it will probably run in any
OS with a suitable web server.

Note: This version of Mahara has NOT been tested on Debian 10+ / Ubuntu 19.04+

### Web Server

Mahara is only officially supported on Apache 2 or later, although it will
probably run in any web server with the proper PHP extensions.

### Database

Mahara requires either Postgres or MySQL. It would require extensive
modification to support other databases.
 * Postgres 9.4 or later
 * MySQL 5.6 or later
 * MariaDB 10.1 or later

Note: This version of Mahara has NOT been tested on Postgres 12+,
MariaDB 10.3+ versions.

### PHP

Mahara requires PHP version 7.2.X. The "magic_quotes" and "register_globals"
settings should be turned *off* (which is the default on modern PHP installations).

The following PHP extensions are also required:
 * curl
 * gd (including Freetype support)
 * json
 * ldap
 * libxml
 * mbstring
 * mcrypt
 * mime_magic; or fileinfo
 * pgsql; or mysqli; or mysql
 * session
 * SimpleXML
 * bz2 (optional)
 * imagick (optional)
 * openssl and xmlrpc (optional; for networking support)
 * memcache (optional; for SAML auth plugin)
 * zlib (optional)
 * adodb (optional; improves performance)
 * enchant or pspell (optional; for TinyMCE spellcheck button)

Note: This version of Mahara has not been fully tested on all latest versions of PHP.
Some aspects of Mahara may not function as expected

### Web Browser

Mahara should be accessible in any modern web browser with Javascript support.
However, it is only actively tested in the most recent versions of Firefox,
Chrome (also for Android), Safari (also for iOS), and Opera. Microsoft browsers,
i.e. Microsoft Edge, are supported to a maximum of the
three most recent versions that are officially supported by Microsoft.

You can still use Mahara on older browsers, but may not have all functionality
available.

For Mahara 21.04, the supported browser versions are:
 * Firefox - 75.0
 * Chrome - 81.0
 * Safari - 13.1
 * Opera - 67.0
 * MS Edge - 81.0
 * Safari for iOS
 * Chrome for Android

# TRANSLATIONS

Mahara has been translated into many languages. You can get language packs from:

* https://wiki.mahara.org/wiki/Language_Packs


------------------
# Copyright notice

Copyright (C) 2006-2020 Catalyst IT and others; see:
https://wiki.mahara.org/wiki/Contributors

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, version 3 or later of the License.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.

Additional permission under GNU GPL version 3 section 7:

If you modify this program, or any covered work, by linking or
combining it with the OpenSSL project's OpenSSL library (or a
modified version of that library), containing parts covered by the
terms of the OpenSSL or SSLeay licenses, the Mahara copyright holders
grant you additional permission to convey the resulting work.
Corresponding Source for a non-source form of such a combination
shall include the source code for the parts of OpenSSL used as well
as that of the covered work.
