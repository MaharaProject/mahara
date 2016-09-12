<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype.blog/taggedposts
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

$string['title'] = 'Tagged journal entries';
$string['description'] = 'Display journal entries with particular tags (see Content ➞ Journal)';
$string['blockheadingtags'] = array(
    0 => 'Journal entries with tag %2$s',
    1 => 'Journal entries with tags %2$s'
);
$string['blockheadingtagsomitboth'] = array(
    0 => ' but not tag %2$s',
    1 => ' but not tags %2$s'
);
$string['blockheadingtagsomitonly'] = array(
    0 => 'Journal entries without tag %2$s',
    1 => 'Journal entries without tags %2$s'
);
$string['defaulttitledescription'] = 'If you leave this blank, the title of the journal will be used';
$string['postsperpage'] = 'Entries per page';
$string['taglist'] = 'Display entries tagged with';
$string['taglistdesc1'] = 'Type a minus sign before each tag that you want to exclude. These tags are shown with a red background.';
$string['excludetag'] = 'exclude tag: ';
$string['notags'] = 'There are no journal entries tagged "%s"';
$string['notagsomit'] = 'There are no journal entries without tags "%s"';
$string['notagsboth'] = 'There are no journal entries tagged "%s" and not "%s"';
$string['notagsavailable'] = 'You have not created any tags';
$string['notagsavailableerror'] = 'No tag selected. You need to add tags to your journal entries before being able to select them here.';
$string['postedin'] = 'in';
$string['postedon'] = 'on';
$string['itemstoshow'] = 'Items to show';
$string['configerror'] = 'Error during block configuration';
$string['showjournalitemsinfull'] = 'Show journal entries in full';
$string['showjournalitemsinfulldesc1'] = 'Switch this setting on to display the journal entries. Otherwise only the titles of the journal entries will be shown.';
$string['tag'] = 'Tag';
