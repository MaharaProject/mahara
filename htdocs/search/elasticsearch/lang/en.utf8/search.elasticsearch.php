<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage lang
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

$string['admin'] = 'Administrator';
$string['all'] = 'All';
$string['analyzer'] = 'Elastic Search Analyzer';
$string['analyzerdescription'] = 'The elasticsearch analyzer class to use. Default is mahara_analyzer.';
$string['artefacttypedescription'] = 'Check the artefact types you want to include to the index. Only artefact types that have a hierarchy defined are valid. You will need to reset artefacts in the queue for your changes to take effect.';
$string['artefacttypelegend'] = 'Artefact types';
$string['artefacttypemapdescription'] = 'Enter the hierarchy for each artefact type separated by | (one artefact type per row).';
$string['artefacttypemaplegend'] = 'Artefact types hierarchy';
$string['atoz'] = 'A to Z';
$string['blogpost'] = 'Journal entry';
$string['bypassindexname'] = 'Bypass Index';
$string['bypassindexnamedescription'] = '(Optional) If provided, Mahara will load index data into this index name instead of the main index name';
$string['collection'] = 'Collection';
$string['confignotset'] = '(not set)';
$string['createdby'] = 'Created by %s';
$string['cronlimit'] = 'Cron record limit';
$string['cronlimitdescription'] = 'Maximum number of records to be passed from the queue to the elasticsearch server on each cron run. (Empty or 0 for unlimited)';
$string['dateoldestfirst'] = 'Date (oldest first)';
$string['daterecentfirst'] = 'Date (most recent first)';
$string['deleted'] = 'Deleted';
$string['deletedforumpost'] = 'Deleted forum post';
$string['filterresultsby'] = 'Filter results by';
$string['forum'] = 'Forum';
$string['forumpost'] = 'Forum post';
$string['forumtopic'] = 'Forum topic';
$string['Group'] = 'Group';
$string['host'] = 'Host';
$string['hostdescription'] = 'Hostname of elasticsearch server. Default is 127.0.0.1.';
$string['indexname'] = 'Index Name';
$string['indexnamedescription'] = 'Name of the elasticsearch index. Default is mahara.';
$string['license'] = 'License';
$string['Media'] = 'Media';
$string['noticeenabled'] = 'The elasticsearch plugin is currently active. To deactivate it, deselect it in the <a href="%s">site options search settings</a>.';
$string['noticenotenabled'] = 'The elasticsearch plugin is not currently enabled. To activate it, select it in the <a href="%s">site options search settings</a>.';
$string['noticepostgresrequired'] = 'The elasticsearch plugin only works with the Postgresql database at this time.';
$string['noticepostgresrequiredtitle'] = 'Feature not available';
$string['owner'] = 'Owner';
$string['page'] = 'Page';
$string['pages'] = 'Pages';
$string['pagetitle'] = 'Search';
$string['password'] = 'Auth password';
$string['passworddescription'] = '(Optional) Password to pass to elasticsearch via HTTP Basic auth';
$string['passwordlength'] = '(password length: %s)';
$string['port'] = 'Elastic Search Port';
$string['portdescription'] = 'The port to contact elasticsearch on. Default is 9200.';
$string['Portfolio'] = 'Portfolio';
$string['record'] = 'record';
$string['records'] = 'records';
$string['relevance'] = 'Relevance';
$string['resetallindexes'] = 'reset ALL indexes';
$string['resetdescription'] = 'This table shows the number of records of each type currently in the queue to be sent to the elasticsearch server. Items are sent to
the elasticsearch server each time the search plugin\'s cron task runs (every 5 minutes). Click on the button at the bottom to reset the search index, deleting all
records and requeuing them.';
$string['resetlegend'] = 'Index reset';
$string['resume'] = 'Résumé';
$string['sortby'] = 'Sort by';
$string['tags'] = 'Tags';
$string['tagsonly'] = 'Tags only';
$string['Text'] = 'Text';
$string['types'] = 'Elastic Search Types';
$string['typesdescription'] = 'Comma-separated list of elements to index. Default is usr,interaction_instance,interaction_forum_post,group,view,artefact.';
$string['usedonpage'] = 'Used on page';
$string['usedonpages'] = 'Used on pages';
$string['username'] = 'Auth username';
$string['usernamedescription'] = '(Optional) Username to pass to elasticsearch via HTTP Basic auth';
$string['Users'] = 'Users';
$string['wallpost'] = 'Wall post';
$string['xsearchresultsfory'] = '%s search results for %s';
$string['ztoa'] = 'Z to A';
