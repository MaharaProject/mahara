<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-text
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

$string['title']                = 'Text';
$string['description']          = 'Add text snippets to your page';
$string['blockcontent']         = 'Block content';
$string['optionlegend']         = 'Convert "Note" blocks';
$string['convertdescriptionfeatures'] = 'You can convert all re-usable "Note" blocks that do not use any advanced features into simple "Text" blocks. These blocks only exist on the page on which they were created and cannot be chosen for use on other pages. Advanced features include:
    <ul>
        <li>re-use in another block</li>
        <li>use of a license</li>
        <li>use of tags</li>
        <li>attachments</li>
        <li>comments on the displayed note artefact</li>
    </ul>';
$string['convertdescription'] = array(
    0 => 'There is %s note that can be considered for conversion. If you select the option to convert this note, please be aware that this may take some time. Once the conversion is finished, you will see a success message on this page.',
    1 => 'There are %d notes that can be considered for conversion. If you select the option to convert these notes, please be aware that this may take some time. Once the conversion is finished, you will see a success message on this page.'
);
$string['convertibleokmessage'] = array(
    0 => 'Successfully converted 1 "Note" block to "Text" block.',
    1 => 'Successfully converted %d "Note" blocks to "Text" blocks.'
);
$string['switchdescription']     = 'Convert all "Note" blocks that do not use any advanced features into simple "Text" blocks.';
