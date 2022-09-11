<?php
/**
 *
 * @package    mahara
 * @subpackage lang
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

$string['parentlanguage'] = '';
// strftime() formats for date conversion
$string['pieform_calendar_dateformat'] = '%%Y/%%m/%%d';
$string['pieform_calendar_timeformat'] = '%%H:%%M';
$string['strftimedate'] = '%%e %%B %%Y';
$string['strftimedatemonth'] = '%%e %%B';
$string['strftimedateshort'] = '%%e %%b %%Y';
$string['strftimedatetime'] = '%%d %%B %%Y, %%k:%%M';
$string['strftimedatetimesuffix'] = '%%d %%B %%Y, %%l:%%M%%P';
$string['strftimedatetimeshort'] = '%%Y/%%m/%%d %%H:%%M';
$string['strftimedaydate'] = '%%A, %%d %%B %%Y';
$string['strftimedaydatetime'] = '%%A, %%d %%B %%Y, %%k:%%M';
$string['strftimedaydatetimesuffix'] = '%%A, %%d %%B %%Y, %%l:%%M%%P';
$string['strftimedayshort'] = '%%A, %%d %%B';
$string['strftimedaytime'] = '%%a, %%k:%%M';
$string['strftimedaytimesuffix'] = '%%a, %%l:%%M%%P';
$string['strftimemonthyear'] = '%%B %%Y';
$string['strftimenotspecified']  = 'Not specified';
$string['strftimerecent'] = '%%d %%b, %%k:%%M';
$string['strftimerecentsuffix'] = '%%d %%b, %%l:%%M%%P';
$string['strftimerecentyear'] = '%%d %%b %%Y, %%k:%%M';
$string['strftimerecentyearsuffix'] = '%%d %%b %%Y, %%l:%%M%%P';
$string['strftimerecentfull'] = '%%a, %%d %%b %%Y, %%k:%%M';
$string['strftimerecentfullsuffix'] = '%%a, %%d %%b %%Y, %%l:%%M%%P';
$string['strftimetime'] = '%%k:%%M';
$string['strftimetimesuffix'] = '%%l:%%M%%P';
$string['strfdaymonthyearshort'] = '%%d/%%m/%%Y';
$string['strfdateofbirth'] = '%%Y/%%m/%%d';
$string['strftimew3cdatetime'] = '%%Y-%%m-%%dT%%H:%%M:%%S%%z';
$string['strftimew3cdate'] = '%%Y-%%m-%%d';
// DateTime formats for date conversion
// The strftime() function is being removed from PHP 9.
$string['datetimeclassdaydate'] = 'l, d F Y';
$string['datetimeclassdaydatetime'] = 'l, j F Y, H:i';

$string['thislanguage'] = 'English';
$string['locales'] = 'en_US.utf8,en_GB.utf8,en,english-us,english-uk,english';
$string['fullname_format'] = 'firstname_lastname';

// Rule to choose from the language's plural forms.
// See the gettext manual, http://www.gnu.org/s/hello/manual/gettext/Plural-forms.html
// For language packs converted from PO format, the following strings and function will be
// automatically generated from the expression in the PO file's "Plural-Forms:" header.
$string['pluralrule'] = 'n != 1';
$string['pluralfunction'] = 'plural_en_utf8';
function plural_en_utf8($n) {
    return (int) $n != 1;
}
