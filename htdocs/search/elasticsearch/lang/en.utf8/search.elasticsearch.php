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
$string['blog'] = 'Journal';
$string['blogpost'] = 'Journal entry';
$string['bypassindexname'] = 'Bypass index';
$string['bypassindexnamedescription'] = '(Optional) If provided, Mahara will load index data into this index name instead of the main index name.';
$string['clusterstatus'] = 'There is a problem with the Elasticsearch cluster. The status is "%s" and unallocated shards are "%s".';
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
$string['elasticsearchtooold'] = 'Your version of Elasticsearch %s is too old. It needs to be %s or higher.';
$string['filterresultsby'] = 'Filter results by';
$string['forum'] = 'Forum';
$string['forumpost'] = 'Forum post';
$string['forumpostedbylabel'] = 'Posted by';
$string['forumpostedby'] = '%s on %s';
$string['forumtopic'] = 'Forum topic';
$string['Group'] = 'Group';
$string['host'] = 'Host';
$string['hostdescription'] = 'Hostname of the Elasticsearch server. Default is 127.0.0.1.';
$string['html'] = 'Text';
$string['indexingusername'] = 'Authentication write username';
$string['indexingusernamedescription'] = '(Optional) Username to pass to Elasticsearch via HTTP basic auth for writing to index if different from reading from index';
$string['indexingpassword'] = 'Authentication write password';
$string['indexingpassworddescription'] = '(Optional) Password to pass to Elasticsearch via HTTP basic auth for writing to index if different from reading from index';
$string['indexingrunning'] = 'Indexing cron job is running. Please try again in a few minutes.';
$string['indexname'] = 'Index name';
$string['indexnamedescription'] = 'Name of the Elasticsearch index. Default is "mahara".';
$string['indexstatusok'] = 'The current index "%s" has status "green". Elasticsearch is running.';
$string['indexstatusbad'] = 'The current index "%s" has status "%s" and will need to be fixed up.';
$string['indexstatusunknown'] = 'The current index "%s" has status unknown due to HTTP response "%s".';
$string['license'] = 'License';
$string['Media'] = 'Media';
$string['newindextype'] = 'A new index type "%s" has been added to your elasticsearch settings. For this to take effect you will need to reindex your site';
$string['newversion'] = 'A new Elasticsearch PHP version %s has been added to Mahara that is compatible with Elasticsearch server %s and above. For this to take effect, you will need to reindex your site.';
$string['none'] = 'none';
$string['noticeenabled'] = 'The Elasticsearch plugin is currently active. To deactivate it, deselect it in the <a href="%s" class="elasticsearch-status">"Search settings"</a>.';
$string['noticenotactive'] = 'The Elasticsearch server is unreachable on host %s and port %s. Please make sure it is running.';
$string['noticenotenabled'] = 'The Elasticsearch plugin is not currently enabled. To activate it, select it in the <a href="%s">"Search settings"</a>.';
$string['owner'] = 'Owner';
$string['page'] = 'Page';
$string['pages'] = 'Pages';
$string['pagetitle'] = 'Search';
$string['password'] = 'Authentication password';
$string['passworddescription'] = '(Optional) Password to pass to Elasticsearch via HTTP basic authentication.';
$string['passwordlength'] = '(password length: %s)';
$string['port'] = 'Elasticsearch port';
$string['portdescription'] = 'The port to contact Elasticsearch on. Default is 9200.';
$string['Portfolio'] = 'Portfolio';
$string['record'] = 'record';
$string['records'] = 'records';
$string['relevance'] = 'Relevance';
$string['replicashards'] = 'Replica shards';
$string['replicashardsdescription'] = 'The number of copies of shards to be made. Note: If you only have 1 node, set replicas to 0.';
$string['reset'] = 'Reset';
$string['resetallindexes'] = 'Reset ALL indexes';
$string['resetdescription'] = 'This table shows the number of records of each type currently in the queue to be sent to the Elasticsearch server. Items are sent to the Elasticsearch server each time the search plugin\'s cron task runs (every 5 minutes). Click on the button at the bottom to reset the search index, deleting all records and requeuing them.';
$string['resetlegend'] = 'Index reset';
$string['resume'] = 'Résumé';
$string['scheme'] = 'Scheme';
$string['schemedescription'] = 'Scheme of the Elasticsearch server. Default is http.';
$string['servererror'] = 'Problem connecting to the server: "%s"';
$string['shards'] = 'Shards';
$string['shardsdescription'] = 'The number of pieces (shards) of the index to be made.';
$string['sortby'] = 'Sort by';
$string['tags'] = 'Tags';
$string['tagsonly'] = 'Tags only';
$string['Text'] = 'Text';
$string['types'] = 'Elasticsearch types';
$string['typesdescription'] = 'Comma-separated list of elements to index. Default is usr,interaction_instance,interaction_forum_post,group,view,artefact.';
$string['usedonpage'] = 'Used on page';
$string['usedonpages'] = 'Used on pages';
$string['username'] = 'Authentication username';
$string['usernamedescription'] = '(Optional) Username to pass to Elasticsearch via HTTP basic authentication.';
$string['Users'] = 'Users';
$string['wallpost'] = 'Wall post';
$string['xsearchresults'] = 'Displaying %s search results';
$string['xsearchresultsfory'] = 'Displaying %s search results for "%s"';
$string['ztoa'] = 'Z to A';
