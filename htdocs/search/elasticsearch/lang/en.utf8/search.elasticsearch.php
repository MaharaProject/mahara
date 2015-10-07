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

$string['admin'] = 'Administrator';
$string['all'] = 'All';
$string['analyzer'] = 'Elasticsearch analyzer';
$string['analyzerdescription'] = 'The Elasticsearch analyzer class to use. Default is mahara_analyzer.';
$string['artefacttypedescription'] = 'Check the artefact types you want to include in the index. Only artefact types that have a hierarchy defined are valid. You will need to reset artefacts in the queue for your changes to take effect.';
$string['artefacttypelegend'] = 'Artefact types';
$string['artefacttypemapdescription'] = 'Enter the hierarchy for each artefact type separated by | (one artefact type per row).';
$string['artefacttypemaplegend'] = 'Artefact types hierarchy';
$string['atoz'] = 'A to Z';
$string['blogpost'] = 'Journal entry';
$string['bypassindexname'] = 'Bypass index';
$string['bypassindexnamedescription'] = '(Optional) If provided, Mahara will load index data into this index name instead of the main index name.';
$string['collection'] = 'Collection';
$string['confignotset'] = '(not set)';
$string['contains'] = 'Contains';
$string['createdby'] = 'Created by %s';
$string['createdbyanon'] = 'Created by (author\'s name hidden)';
$string['cronlimit'] = 'Cron record limit';
$string['cronlimitdescription'] = 'Maximum number of records to be passed from the queue to the Elasticsearch server on each cron run (Empty or 0 for unlimited).';
$string['dateoldestfirst'] = 'Date (oldest first)';
$string['daterecentfirst'] = 'Date (most recent first)';
$string['deleted'] = 'Deleted';
$string['deletedforumpost'] = 'Deleted forum post';
$string['document'] = 'Document';
$string['filterresultsby'] = 'Filter results by';
$string['forum'] = 'Forum';
$string['forumpost'] = 'Forum post';
$string['forumpostedbylabel'] = 'Posted by';
$string['forumpostedby'] = '%s on %s';
$string['forumtopic'] = 'Forum topic';
$string['Group'] = 'Group';
$string['host'] = 'Host';
$string['hostdescription'] = 'Hostname of the Elasticsearch server. Default is 127.0.0.1.';
$string['indexingrunning'] = 'Indexing cron job is running. Please try again in a few minutes.';
$string['indexname'] = 'Index name';
$string['indexnamedescription'] = 'Name of the Elasticsearch index. Default is mahara.';
$string['license'] = 'License';
$string['Media'] = 'Media';
$string['newindextype'] = 'A new index type "%s" has been added to your elasticsearch settings. For this to take effect you will need to reindex your site';
$string['none'] = 'none';
$string['noticeenabled'] = 'The Elasticsearch plugin is currently active. To deactivate it, deselect it in the <a href="%s">site options search settings</a>.';
$string['noticenotactive'] = 'The ElasticSearch Server is unreachable on host: %s and port %s. Please make sure it is running.';
$string['noticenotenabled'] = 'The Elasticsearch plugin is not currently enabled. To activate it, select it in the <a href="%s">site options in the search settings</a>.';
$string['owner'] = 'Owner';
$string['page'] = 'Page';
$string['pages'] = 'Pages';
$string['pagetitle'] = 'Search';
$string['password'] = 'Auth password';
$string['passworddescription'] = '(Optional) Password to pass to Elasticsearch via HTTP basic auth';
$string['passwordlength'] = '(password length: %s)';
$string['port'] = 'Elasticsearch port';
$string['portdescription'] = 'The port to contact Elasticsearch on. Default is 9200.';
$string['Portfolio'] = 'Portfolio';
$string['record'] = 'record';
$string['records'] = 'records';
$string['relevance'] = 'Relevance';
$string['reset'] = 'Reset';
$string['resetallindexes'] = 'Reset ALL indexes';
$string['resetdescription'] = 'This table shows the number of records of each type currently in the queue to be sent to the Elasticsearch server. Items are sent to the Elasticsearch server each time the search plugin\'s cron task runs (every 5 minutes). Click on the button at the bottom to reset the search index, deleting all records and requeuing them.';
$string['resetlegend'] = 'Index reset';
$string['resume'] = 'Résumé';
$string['sortby'] = 'Sort by';
$string['tags'] = 'Tags';
$string['tagsonly'] = 'Tags only';
$string['Text'] = 'Text';
$string['types'] = 'Elasticsearch types';
$string['typesdescription'] = 'Comma-separated list of elements to index. Default is usr,interaction_instance,interaction_forum_post,group,view,artefact.';
$string['usedonpage'] = 'Used on page';
$string['usedonpages'] = 'Used on pages';
$string['username'] = 'Auth username';
$string['usernamedescription'] = '(Optional) Username to pass to Elasticsearch via HTTP basic auth';
$string['Users'] = 'Users';
$string['wallpost'] = 'Wall post';
$string['xsearchresultsfory'] = '%s search results for %s';
$string['ztoa'] = 'Z to A';
