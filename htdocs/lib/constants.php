<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2007 Catalyst IT Ltd (http://www.catalyst.net.nz)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage core
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 * @copyright  (C) portions from Moodle, (C) Martin Dougiamas http://dougiamas.com
 */

defined('INTERNAL') || die();

// PARAMETER CLEANING

/**
 * PARAM_INT - integers only, use when expecting only numbers.
 */
define('PARAM_INT',      0x0002);

/**
 * PARAM_INTEGER - an alias for PARAM_INT
 */
define('PARAM_INTEGER',  0x0002);

/**
 * PARAM_ALPHA - contains only english letters.
 */
define('PARAM_ALPHA',    0x0004);

/**
 * PARAM_ACTION - an alias for PARAM_ALPHA, use for various actions in formas and urls
 * @TODO: should we alias it to PARAM_ALPHANUM ?
 */
define('PARAM_ACTION',   0x0004);

/**
 * PARAM_FORMAT - an alias for PARAM_ALPHA, use for names of plugins, formats, etc.
 * @TODO: should we alias it to PARAM_ALPHANUM ?
 */
define('PARAM_FORMAT',   0x0004);

/**
 * PARAM_NOTAGS - all html tags are stripped from the text. Do not abuse this type.
 */
define('PARAM_NOTAGS',   0x0008);

 /**
 * PARAM_MULTILANG - general plain text compatible with multilang filter, no other html tags.
 */
define('PARAM_MULTILANG',  0x0009);

/**
 * PARAM_FILE - safe file name, all dangerous chars are stripped, protects against XSS, SQL injections and directory traversals
 */
define('PARAM_FILE',     0x0010);

/**
 * PARAM_PATH - safe relative path name, all dangerous chars are stripped, protects against XSS, SQL injections and directory traversals
 * note: the leading slash is not removed, window drive letter is not allowed
 */
define('PARAM_PATH',     0x0020);

/**
 * PARAM_HOST - expected fully qualified domain name (FQDN) or an IPv4 dotted quad (IP address)
 */
define('PARAM_HOST',     0x0040);

/**
 * PARAM_URL - expected properly formatted URL.
 */
define('PARAM_URL',      0x0080);

/**
 * PARAM_LOCALURL - expected properly formatted URL as well as one that refers to the local server itself. (NOT orthogonal to the others! Implies PARAM_URL!)
 */
define('PARAM_LOCALURL', 0x0180);

/**
 * PARAM_CLEANFILE - safe file name, all dangerous and regional chars are removed,
 * use when you want to store a new file submitted by students
 */
define('PARAM_CLEANFILE',0x0200);

/**
 * PARAM_ALPHANUM - expected numbers and letters only.
 */
define('PARAM_ALPHANUM', 0x0400);

/**
 * PARAM_BOOL - converts input into 0 or 1, use for switches in forms and urls.
 */
define('PARAM_BOOL',     0x0800);

/**
 * PARAM_CLEANHTML - cleans submitted HTML code and removes slashes
 * note: do not forget to addslashes() before storing into database!
 */
define('PARAM_CLEANHTML',0x1000);

/**
 * PARAM_ALPHAEXT the same contents as PARAM_ALPHA plus the chars in quotes: "/-_." allowed,
 * suitable for include() and require()
 */
define('PARAM_ALPHAEXT', 0x2000);

/**
 * PARAM_SAFEDIR - safe directory name, suitable for include() and require()
 */
define('PARAM_SAFEDIR',  0x4000);

/**
 * PARAM_SEQUENCE - expects a sequence of numbers like 8 to 1,5,6,4,6,8,9.  Numbers and comma only.
 */
define('PARAM_SEQUENCE',  0x8000);


/**
 * REQUEST_POST - retrieve from $_POST
 */
define('REQUEST_POST', 2);

/** 
 * REQUEST_GET - retrieve from $_GET
 */
define('REQUEST_GET', 1);

/**
 * REQUEST_EITHER - retrieve from either $_POST or $_GET 
 * $_POST has preference.
 */
define('REQUEST_EITHER',0);



/**
 * Allowed tags - string of html tags that can be tested against for safe html tags
 * @global string $ALLOWED_TAGS
 */
$ALLOWED_TAGS =
'<p><br><b><i><u><font><table><tbody><span><div><tr><td><th><ol><ul><dl><li><dt><dd><h1><h2><h3><h4><h5><h6><hr><img><a><strong><emphasis><em><sup><sub><address><cite><blockquote><pre><strike><param><acronym><nolink><lang><tex><algebra><math><mi><mn><mo><mtext><mspace><ms><mrow><mfrac><msqrt><mroot><mstyle><merror><mpadded><mphantom><mfenced><msub><msup><msubsup><munder><mover><munderover><mmultiscripts><mtable><mtr><mtd><maligngroup><malignmark><maction><cn><ci><apply><reln><fn><interval><inverse><sep><condition><declare><lambda><compose><ident><quotient><exp><factorial><divide><max><min><minus><plus><power><rem><times><root><gcd><and><or><xor><not><implies><forall><exists><abs><conjugate><eq><neq><gt><lt><geq><leq><ln><log><int><diff><partialdiff><lowlimit><uplimit><bvar><degree><set><list><union><intersect><in><notin><subset><prsubset><notsubset><notprsubset><setdiff><sum><product><limit><tendsto><mean><sdev><variance><median><mode><moment><vector><matrix><matrixrow><determinant><transpose><selector><annotation><semantics><annotation-xml><tt><code>';

/**
 * Allowed protocols - array of protocols that are safe to use in links and so on
 * @global string $ALLOWED_PROTOCOLS
 */
$ALLOWED_PROTOCOLS = array('http', 'https', 'ftp', 'news', 'mailto', 'rtsp', 'teamspeak', 'gopher', 'mms',
                           'color', 'callto', 'cursor', 'text-align', 'font-size', 'font-weight', 'font-style',
                           'border', 'margin', 'padding', 'background');   // CSS as well to get through kses


/**
 * tag to use for <br> or <br />
 */
define('HTML_BR', '<br>');

?>
