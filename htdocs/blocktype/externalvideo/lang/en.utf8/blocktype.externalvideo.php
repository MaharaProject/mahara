<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-externalvideo
 * @author     Catalyst IT Ltd
 * @author     Gregor Anzelj
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

$string['title'] = 'External media';
$string['description'] = 'Embed external content';
$string['urlorembedcode'] = 'URL or embed code';
$string['videourldescription3'] = 'Paste the <strong>embed code</strong> or the <strong>URL</strong> of the page where the content is located.';
$string['validiframesites'] = '<strong>Embed code</strong> containing &lt;iframe&gt; tags is allowed from the following sites:';
$string['validurlsites'] = '<strong>URLs</strong> from the following sites are allowed:';
$string['width'] = 'Width';
$string['height'] = 'Height';
$string['widthheightdescription'] = 'Width and height fields are only used for URLs. If you have entered embed or iframe code above, you need to update the width and height within the code itself.';
$string['invalidurl'] = 'Invalid URL';
$string['invalidurlorembed'] = 'Invalid URL or embed code';

// Supported sites language strings
$string['googlevideo'] = 'Google Video';
$string['scivee'] = 'SciVee';
$string['youtube'] = 'YouTube';
$string['teachertube'] = 'TeacherTube';
$string['slideshare'] = 'SlideShare';
$string['prezi'] = 'Prezi';
$string['glogster'] = 'Glogster';
$string['vimeo'] = 'Vimeo';
$string['voki'] = 'Voki';
$string['voicethread'] = 'VoiceThread';
$string['wikieducator'] = 'WikiEducator';

// Embed services
$string['validembedservices'] = 'The following <strong>embed services</strong> for embedding content are supported:';
$string['enableservices'] = 'None, %senable embed services%s';
$string['embedly'] = 'Embedly';
