Local customizations directory
==============================
This directory is the recommended place for local customizations.
It can be used for customizations to your site that don't fit into any Mahara's
other plugin types, as well as for accessing various "Hooks" to override the
default behavior.

See also https://wiki.mahara.org/wiki/Developer_Area/Local_customizations


Function hooks
--------------
The file "local/lib.php" can contain various functions which allow your site
to override Mahara's default behavior. See the sample file local/lib-dist.php
for a full list.


Installation hooks
------------------
You can define definitions for the methods in "local/install.php" to execute
PHP code before or after the Mahara installation process.

You can place database definitions in a file called "local/db/install.xml",
and these will be executed during the Mahara installation process just like
the core or plugin install.xml files.


Upgrade hooks
-------------
You can use "local/version.php" and "local/upgrade.php" to run upgrade scripts,
the same as the core and plugin upgrade scripts.


Custom language strings
-----------------------
You can override language strings by placing lang files under "/local/lang/{language.name}".
You don't have to provide a replacement for every string in the file, only those strings
that you want to override. This is especially handy for if you want to customize only
one or two strings.

Examples:
* local/lang/en.utf8/mahara.php
* local/lang/en.utf8/blocktype.contactinfo.php
* local/lang/en.utf8/artefact.blog.php


Custom help files
-----------------
Similarly, you can provide custom help files by placing them under
/local/lang/{language}/help/{forms|pages|sections}/{filename}.html for core help files
or /local/lang/{language}/help/{forms|pages|sections}/{plugintype}.{pluginname}.{filename}.html
for plugins.

Examples:
* local/lang/en.utf8/help/forms/adduser.friendscontrol.html
* local/lang/en.utf8/help/forms/artefact.blog.addentry.draft.html


Custom theme files
------------------
You can override theme files by placing them under /local/theme. Files placed here will override
ALL themes in the site.

Examples:
* Core emplate file:    local/theme/templates/index.tpl
* Core stylesheet:      local/theme/style/style.css
* Core static file:     local/theme/images/site-logo.png
* Plugin template file: local/theme/artefact/file/templates/profileicons.tpl
* Plugin static file:   local/theme/blocktype/creativecommons/images/seal.png
