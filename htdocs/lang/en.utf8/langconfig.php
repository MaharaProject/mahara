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

$string['parentlanguage'] = '';
$string['pieform_calendar_dateformat'] = '%%Y/%%m/%%d';
$string['pieform_calendar_timeformat'] = '%%H:%%M';
$string['strftimedate'] = '%%e %%B %%Y';
$string['strftimedateshort'] = '%%d %%B';
$string['strftimedatetime'] = '%%d %%B %%Y, %%l:%%M %%p';
$string['strftimedatetimeshort'] = '%%Y/%%m/%%d %%H:%%M';
$string['strftimedaydate'] = '%%A, %%d %%B %%Y';
$string['strftimedaydatetime'] = '%%A, %%d %%B %%Y, %%l:%%M %%p';
$string['strftimedayshort'] = '%%A, %%d %%B';
$string['strftimedaytime'] = '%%a, %%k:%%M';
$string['strftimemonthyear'] = '%%B %%Y';
$string['strftimenotspecified']  = 'Not specified';
$string['strftimerecent'] = '%%d %%b, %%k:%%M';
$string['strftimerecentyear'] = '%%d %%b %%Y, %%k:%%M';
$string['strftimerecentfull'] = '%%a, %%d %%b %%Y, %%l:%%M %%p';
$string['strftimetime'] = '%%l:%%M %%p';
$string['strfdaymonthyearshort'] = '%%d/%%m/%%Y';
$string['strfdateofbirth'] = '%%Y/%%m/%%d';
$string['strftimew3cdatetime'] = '%%Y-%%m-%%dT%%H:%%M:%%S%%z';
$string['strftimew3cdate'] = '%%Y-%%m-%%d';
$string['thislanguage'] = 'English';
$string['locales'] = 'en_US.utf8,en_GB.utf8,en,english-us,english-uk,english';

// Rule to choose from the language's plural forms.
// See the gettext manual, http://www.gnu.org/s/hello/manual/gettext/Plural-forms.html
// For language packs converted from PO format, the following strings and function will be
// automatically generated from the expression in the PO file's "Plural-Forms:" header.
$string['pluralrule'] = 'n != 1';
$string['pluralfunction'] = 'plural_en_utf8';
function plural_en_utf8($n) {
    return (int) $n != 1;
}
