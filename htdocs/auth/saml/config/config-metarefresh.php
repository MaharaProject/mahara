<?php
/**
 *
 * @package    mahara
 * @subpackage auth-saml
 * @author     Francis Devine <francis@catalyst.net.nz>
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */

$finalsources = array();
$sources = Metarefresh::get_metadata_urls();
//Pull through each idp's fetch url
$x = 0;
foreach($sources as $identityid => $source) {
    $finalsources[$x] = array('src' => $source['src']);
    if (!empty($source['validateFingerprint'])) {
        $finalsources[$x]['validateFingerprint'] = $source['validateFingerprint'];
    }
    $x++;
}
$config = array(
    'conditionalGET' => TRUE,
    //We only have one set of automatic idps, theoretically someone could add more sets if they have unique requirements
    //around templating and so forth
    'sets' => array(
        'remote-idp' => array(
            'cron'      => array('hourly'),
            'sources'   => $finalsources,
            'expireAfter'       => 60*60*24*4, // Maximum 4 days cache time.
            'outputDir'     => Metarefresh::get_metadata_path(),
            'outputFormat' => 'flatfile',
        ),
    ),
);
