<?php
/**
 *
 * @package    mahara
 * @subpackage auth-saml
 * @author     Francis Devine <francis@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */

$finalsources = array();
$sources = Metarefresh::get_metadata_urls();
//Pull through each idp's fetch url
foreach($sources as $identityid => $src) {
    $finalsources[] = array('src' => $src);
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
