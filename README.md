# Mahara Readme

**This is the old repository for Mahara. It is not updated any more as of April 2023. The latest code is available via a [subscription](https://mahara.org/subscription).**

## What is Mahara?

Mahara is an open source ePortfolio system. An ePortfolio is a type of
web application that allows learners to record and share evidence of their
learning and reflect on their learning. Mahara can be used to create different
types of portfolios, including learning portfolio, development portfolio,
presentation / showcase portfolio, employability portfolio.

## SUPPORT

The best ways for obtaining support are:

 * [Mahara online manual](https://manual.mahara.org)
 * [Mahara community forums](https://mahara.org/forums)
 * [Mahara wiki](https://wiki.mahara.org)
 * [Live chat](https://matrix.to/#/#mahara:matrix.org) with others, in particular developers

## INSTALLATION

For detailed installation instructions see the [installation page](https://wiki.mahara.org/wiki/System_Administrator%27s_Guide/Installing_Mahara) on our wiki.

The following is a check list of the high level installation and setup steps.
Please refer to the installation instructions for the details:

 1. Create a PostgreSQL or MySQL database for Mahara.
 2. Copy the Mahara files under 'htdocs' into your web root.
 3. Create a Mahara 'dataroot' directory outside of your web root.
 4. Copy htdocs/config-dist.php to config.php.
 5. Edit config.php with the correct details for your installation.

# UPGRADING

Mahara 22.10 supports direct upgrades from previous Mahara versions 20.04.0 and later.

If you are upgrading from an earlier version, you will need to upgrade
in steps:

* if site version begins with 1.X, first upgrade to 15.04
* then/else upgrade version to 17.04.10
* then/else upgrade version to 18.04.6
* then/else upgrade version to 20.04.5
* then upgrade to 22.10.X (latest stable release)

Note: Older versions of Mahara are not compatible with latest versions
of PHP. You will need to do intermediate upgrade steps before
updating your server's PHP.

To upgrade an existing Mahara installation, follow the [upgrade instructions](https://wiki.mahara.org/wiki/System_Administrator%27s_Guide/Upgrading_Mahara).

If you upgrade from Mahara 15.10 or earlier, you will need to [add a 'urlsecret'
value to your config.php file](https://manual.mahara.org/en/22.10/administration/config_php.html#urlsecret-run-the-cron-or-upgrade-only-when-you-are-authorised) if you wish to use the web-based upgrade and/or
cron scripts. See:

# SYSTEM REQUIREMENTS

Here are the system requirements needed to run Mahara 22.10.

### Operating system for the server

Mahara is officially supported on Ubuntu (18.04/"Bionic Beaver" LTS or later)
and Debian (9.0/"Stretch" LTS or later). However, it will run on other Linux-
based operating systems or even Windows servers. You may run into issues though
that the Mahara core project team may not be able to fix. Patches are welcome
to.

Note: This version of Mahara has **not** been tested on Debian 12+ or Ubuntu 22.04+.

Any operating system that supports modern web browsers with JavaScript can be
used to interact with Mahara.

### Web server

Mahara is supported on Apache 2 or later and tested on Nginx,
although it will probably run on any web server with the proper PHP extensions.

### Database

Mahara requires either PostgreSQL or MySQL/MariaDB. It would require extensive
modification to support other databases.
 * PostgreSQL 9.4 or later
 * MySQL 5.7 or later
 * MariaDB 10.1 or later

Note: This version of Mahara has **not** been tested on PostgreSQL 13+ and
MariaDB 10.8+ versions.

### PHP

Mahara 22.10 can be used with PHP version 7.2.X, 7.3.X, or 7.4.X. The 'magic_quotes'
and 'register_globals' settings should be turned *off* (which is the default on
modern PHP installations).

Note: Mahara has **not** been fully tested with PHP 8.1. Basic support is available,
but at the moment we do not recommend using PHP 8.1 on a production site. We invite
feedback though from any testing. We will focus on PHP 8.1 and skip PHP 8.0.

The following PHP extensions are required:
 * curl
 * gd (including Freetype support)
 * json
 * ldap
 * libxml
 * mbstring
 * mime_magic; or fileinfo
 * pgsql; or mysqli; or mysql
 * session
 * SimpleXML
 * intl - for language internationalisation
 * bz2 (optional)
 * imagick (optional)
 * openssl and xmlrpc (optional; for networking support)
 * memcached (optional; for SAML auth plugin)
 * zlib (optional)
 * adodb (optional; improves performance)
 * enchant or pspell (optional; for TinyMCE spellcheck button)

### Web browser

Mahara should be accessible in any modern web browser with JavaScript support.
However, it is only actively tested in the most recent versions of Firefox and Chrome
(also on Android). Testing on Safari (also for iOS) is done on occasion,
Microsoft browsers, i.e. Microsoft Edge, are supported to a maximum of the
three most recent versions that are officially supported by Microsoft.

You can still use Mahara on older browsers, but may not have all functionality
available.

For Mahara 22.10, the supported browser versions are:
 * Firefox 105.0
 * Chrome 106.0
 * Safari 15.6
 * Safari for iOS
 * Chrome for Android

# TRANSLATIONS

Mahara has been translated into many languages. You can download [language packs](https://langpacks.mahara.org/),
[install and update them via CLI](https://manual.mahara.org/en/22.10/administration/cli.html#install-and-update-language-packs),
or [install and update them via the administration area](https://manual.mahara.org/en/22.10/administration/development.html#languages) in Mahara.

# DOCUMENT YOUR CUSTOMISATIONS

Document your customisations for easy record keeping in CUSTOMISATIONS.md by copying
CUSTOMISATIONS-example.md. The file sits outside of the `htdocs` directory and should
not be uploaded to a server.

------------------
# COPYRIGHT NOTICE

Copyright (C) 2006-2022 Catalyst IT Limited and others

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, version 3 or later of the License.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License 3.0+
along with this program (see the file 'COPYING'). If not, [view the GNU GLP
v.3+ online](https://www.gnu.org/licenses/gpl-3.0.html).

Additional permission under GNU GPL version 3 section 7:

If you modify this program, or any covered work, by linking or
combining it with the OpenSSL project's OpenSSL library (or a
modified version of that library), containing parts covered by the
terms of the OpenSSL or SSLeay licenses, the Mahara copyright holders
grant you additional permission to convey the resulting work.
Corresponding Source for a non-source form of such a combination
shall include the source code for the parts of OpenSSL used as well
as that of the covered work.
