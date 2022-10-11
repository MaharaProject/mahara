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

if (!defined('INTERNAL')) {
  die();
}

$string['admin'] = 'Administrator';
$string['all'] = 'All';
$string['analyzer'] = 'Elasticsearch analyzer';
$string['analyzerdescription'] = 'The Elasticsearch analyzer class to use. Default is "%s".';
$string['artefacttypes'] = 'Artefact types';
$string['artefacttypesdescription'] = 'Check the artefact types you want to include in the index. Only artefact types that have a hierarchy defined are valid. Reset artefacts in the queue for your changes to take effect.';
$string['artefacttypesmap'] = 'Artefact types hierarchy';
$string['artefacttypesmapdescription'] = 'Enter the hierarchy for each artefact type separated by | (one artefact type per row).';
$string['atoz'] = 'A to Z';
$string['blogpost'] = 'Journal entry';
$string['bypassindexname'] = 'Bypass index';
$string['bypassindexnamedescription'] = 'If provided, Mahara will load index data into this index name instead of the main index name (optional).';
$string['clusterconfig'] = 'Cluster configuration';
$string['clusterstatus'] = 'Cluster status: %s';
$string['cligetfailedqueuesizemessage'] = 'There are failed records in the Elasticsearch 7 queue older than 1 hour.';
$string['cliisqueueolderthanmessage'] = array(
  'There are unprocessed records in the Elasticsearch 7 queue older than %s hour.',
  'There are unprocessed records in the Elasticsearch 7 queue older than %s hours.',
);
$string['clicheckingsearchsucceededmessage'] = 'There are no unprocessed or failed records in the Elasticsearch 7 queue.';
$string['collection'] = 'Collection';
$string['confignotset'] = '(not set)';
$string['contains'] = 'Contains';
$string['connectionerror'] = 'Connection error';
$string['createdby'] = 'Created by %s';
$string['cronlimit'] = 'Cron record limit';
$string['cronlimitdescription'] = 'Maximum number of records to be passed from the queue to the Elasticsearch server on each cron run (empty or 0 for unlimited).';
$string['cronstatetitle'] = 'Indexing on cron';
$string['cronstatedescription'] = 'This allows you to enable or disable indexing of queued items when the cron runs.';
$string['dateoldestfirst'] = 'Date (oldest first)';
$string['daterecentfirst'] = 'Date (most recent first)';
$string['deleted'] = 'Deleted';
$string['deletedforumpost'] = 'Deleted forum post';
$string['document'] = 'Document';
$string['error'] = 'Error: ';
$string['errorunknown'] = 'Unknown error';
$string['filterresultsby'] = 'Filter results by';
$string['forum'] = 'Forum';
$string['forumpost'] = 'Forum post';
$string['forumpostedbylabel'] = 'Posted by';
$string['forumpostedby'] = '%s on %s';
$string['forumtopic'] = 'Forum topic';
$string['Group'] = 'Group';
$string['host'] = 'Host';
$string['hostdescription'] = 'Hostname of the Elasticsearch server. Default is %s.';
$string['indexingpassword'] = 'Authentication write password';
$string['indexingpassworddescription'] = 'The password to pass to Elasticsearch via HTTP basic authentication for writing to the index if it is different from the password for reading from index (optional).';
$string['indexingrunning'] = 'Indexing cron job is running. Please try again in a few minutes.';
$string['indexingrunningtry'] = 'If you know the cron is not actually running see ./mash search-reset-cron-lock';
$string['indexingusername'] = 'Authentication write username';
$string['indexingusernamedescription'] = 'Username to pass to Elasticsearch via HTTP basic authentication for writing to the index if it is different from the username for reading from the index (optional).';
$string['indexname'] = 'Index name';
$string['indexnamedescription'] = 'Name of the Elasticsearch index. Default is "%s".';
$string['indexstatusbad'] = "Index status (%s): %s";
$string['indexstatusunknown'] = 'The current index "%s" has status unknown due to HTTP response "%s".';
$string['license'] = 'License';
$string['Media'] = 'Media';
$string['monitorfailedqueuesize'] = 'Number of failed records for more than 1 hour';
$string['monitorqueuehasolditems'] = [
  'Unprocessed items in the Elasticsearch 7 queue older than %s hour',
  'Unprocessed items in the Elasticsearch 7 queue older than %s hours'
];
$string['monitorunprocessedqueuesize'] = 'Total number of unprocessed records';
$string['monitorqueuestatus'] = 'Elasticsearch 7 queue status';
$string['monitorsubnavtitle'] = 'Elasticsearch 7';
$string['none'] = 'none';
$string['noticeenabled'] = 'The Elasticsearch 7 plugin is currently active. To deactivate it, deselect it in the <a href="%s" class="elasticsearch-status">"Search settings"</a>.';
$string['noticenotactive'] = 'The Elasticsearch 7 server is unreachable on host %s and port %s. Please make sure it is running.';
$string['noticenotenabled'] = 'The Elasticsearch 7 plugin is not currently enabled. To activate it, select it in the <a href="%s">"Search settings"</a>.';
$string['owner'] = 'Owner';
$string['page'] = 'Page';
$string['pages'] = 'Pages';
$string['pagetitle'] = 'Search';
$string['password'] = 'Authentication password';
$string['passworddescription'] = 'Password to pass to Elasticsearch 7 via HTTP basic authentication (optional).';
$string['passwordlength'] = '(password length: %s)';
$string['pluginstatus'] = 'Plugin status: %s';
$string['pluginstatusmessageindex404'] = 'The index should have been created by now, but it was not. Try reloading the page or check your Elasticsearch 7 server.';
$string['pluginstatustitleaccess'] = 'Server access';
$string['pluginstatustitlecluster_health'] = 'Cluster health';
$string['pluginstatustitlecron'] = 'Cron';
$string['pluginstatustitleindexstatus'] = 'Index status';
$string['pluginstatustitleserver_health'] = 'Server health';
$string['pluginstatusignoresslerror'] = 'The <code>ignoressl</code> setting is ignored when <code>productionmode</code> is true. Either use a valid SSL certificate for Elasticsearch 7 or set <code>productionmode</code> to "false".';
$string['port'] = 'Elasticsearch port';
$string['portdescription'] = 'The port to contact Elasticsearch on. Default is %s.';
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
$string['resettype'] = 'Type';
$string['resetitemsinqueue'] = 'In queue';
$string['resetitemsinindex'] = 'In index';
$string['resume'] = 'Résumé';
$string['scheme'] = 'Scheme';
$string['schemedescription'] = 'Scheme of the Elasticsearch 7 server. Default is %s.';
$string['servererror'] = 'Problem connecting to the server: "%s"';
$string['shards'] = 'Shards';
$string['shardsdescription'] = 'The number of pieces (shards) of the index to be made.';
$string['sortby'] = 'Sort by';
$string['systemmessage'] = 'System message: ';
$string['tags'] = 'Tags';
$string['tagsonly'] = 'Tags only';
$string['Text'] = 'Text';
$string['types'] = 'Elasticsearch types';
$string['typesdescription'] = 'Comma-separated list of elements to index. Default is "%s".';
$string['unassignedshards'] = 'Unassigned shards: %s';
$string['usedonpage'] = 'Used on page';
$string['usedonpages'] = 'Used on pages';
$string['username'] = 'Authentication username';
$string['usernamedescription'] = 'Username to pass to Elasticsearch 7 via HTTP basic authentication (optional).';
$string['Users'] = 'People';
$string['xsearchresults'] = 'Displaying %s search results';
$string['xsearchresultsfory'] = 'Displaying %s search results for "%s"';
$string['ztoa'] = 'Z to A';
