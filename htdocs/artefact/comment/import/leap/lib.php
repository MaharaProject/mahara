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
 * @subpackage artefact-comment-import-leap
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/**
 * Implements LEAP2A import of comment entries into Mahara
 *
 */
class LeapImportComment extends LeapImportArtefactPlugin {

    /**
     * Import an entry as a comment (with associated attachments)
     * on an artefact or view
     */
    const STRATEGY_IMPORT_AS_COMMENT = 1;


    /**
     * Hack to allow comments to be created before the thing they
     * comment on (a view or another artefact) gets created.
     *
     * On creation, point comments at this temporary view until the
     * setup_relationships stage of the import, at which time the
     * correct comment relationship will be restored.
     */
    private static $tempview = null;

    public static function create_temporary_view($user) {
        $time = db_format_timestamp(time());
        $viewdata = (object) array(
            'owner'      => $user,
            'title'      => '--',
            'type'       => 'portfolio',
            'numcolumns' => 1,
            'ctime'      => $time,
            'mtime'      => $time,
            'atime'      => $time,
        );
        return self::$tempview = insert_record('view', $viewdata, 'id', true);
    }

    /**
     * Delete the temporary view
     */
    public static function cleanup(PluginImportLeap $importer) {
        if (self::$tempview) {
            delete_records('view', 'id', self::$tempview);
        }
    }


    public static function get_import_strategies_for_entry(SimpleXMLElement $entry, PluginImportLeap $importer) {
        $strategies = array();

        if (PluginImportLeap::is_rdf_type($entry, $importer, 'entry')
            && $entry->xpath('mahara:artefactplugin[@mahara:type="comment"]')) {

            // Check that the entry 'reflects_on' something
            $otherentries = array();
            foreach ($entry->link as $link) {
                if ($importer->curie_equals($link['rel'], PluginImportLeap::NS_LEAP, 'reflects_on') && isset($link['href'])) {
                    $otherentries[] = (string)$link['href'];
                }
            }
            if (count($otherentries) == 1) {
                $strategies[] = array(
                    'strategy' => self::STRATEGY_IMPORT_AS_COMMENT,
                    'score'    => 100,
                    'other_required_entries' => array(),
                );
            }
        }

        return $strategies;
    }

    public static function import_using_strategy(SimpleXMLElement $entry, PluginImportLeap $importer, $strategy, array $otherentries) {

        if ($strategy != self::STRATEGY_IMPORT_AS_COMMENT) {
            throw new ImportException($importer, 'TODO: get_string: unknown strategy chosen for importing entry');
        }

        $artefactmapping = array();
        $artefactmapping[(string)$entry->id] = self::create_comment($entry, $importer);
        return $artefactmapping;
    }


    /**
     * Creates a comment from the given entry
     *
     * @param SimpleXMLElement $entry    The entry to create the comment from
     * @param PluginImportLeap $importer The importer
     * @return array A list of artefact IDs created, to be used with the artefact mapping.
     */
    private static function create_comment(SimpleXMLElement $entry, PluginImportLeap $importer) {
        $createdartefacts = array();

        $comment = new ArtefactTypeComment();
        $comment->set('title', (string)$entry->title);
        $description = PluginImportLeap::get_entry_content($entry, $importer);
        $type = isset($entry->content['type']) ? (string)$entry->content['type'] : 'text';
        if ($type == 'text') {
            $description = format_whitespace($description);
        }
        $comment->set('description', $description);
        if ($published = strtotime((string)$entry->published)) {
            $comment->set('ctime', $published);
        }
        if ($updated = strtotime((string)$entry->updated)) {
            $comment->set('mtime', $updated);
        }

        $private = PluginImportLeap::is_correct_category_scheme($entry, $importer, 'audience', 'Private');
        $comment->set('private', (int) $private);
        $comment->set('owner', $importer->get('usr'));

        if (isset($entry->author->name) && strlen($entry->author->name)) {
            $comment->set('authorname', $entry->author->name);
        }
        else {
            $comment->set('author', $importer->get('usr'));
        }

        if (empty(self::$tempview)) {
            self::create_temporary_view($importer->get('usr'));
        }
        $comment->set('onview', self::$tempview);

        $comment->set('tags', PluginImportLeap::get_entry_tags($entry));
        $comment->commit();
        array_unshift($createdartefacts, $comment->get('id'));

        return $createdartefacts;
    }

    /**
     * Relate comments to the artefacts they comment on
     * Attach files to comments
     */
    public static function setup_relationships(SimpleXMLElement $entry, PluginImportLeap $importer) {
        self::link_comment($entry, $importer, 'artefacts');

        // Attachments
        $comment = null;
        foreach ($entry->link as $link) {
            if ($importer->curie_equals($link['rel'], '', 'enclosure') && isset($link['href'])) {
                if (!$comment) {
                    $artefactids = $importer->get_artefactids_imported_by_entryid((string)$entry->id);
                    $comment = new ArtefactTypeComment($artefactids[0]);
                }
                $importer->trace("Attaching file $link[href] to comment $entry->id", PluginImportLeap::LOG_LEVEL_VERBOSE);
                $artefactids = $importer->get_artefactids_imported_by_entryid((string)$link['href']);
                if (isset($artefactids[0])) {
                    $comment->attach($artefactids[0]);
                }
            }
        }
        if ($comment) {
            $comment->commit();
        }

    }

    /**
     * Fix comments to point to the right view.  Probably more
     * appropriate in setup_relationships.  To do that we would have
     * to change that call to happen after views are created.
     */
    public static function setup_view_relationships(SimpleXMLElement $entry, PluginImportLeap $importer) {
        self::link_comment($entry, $importer, 'views');
    }

    public static function link_comment(SimpleXMLElement $entry, PluginImportLeap $importer, $linkto) {
        $artefactids = $importer->get_artefactids_imported_by_entryid((string)$entry->id);
        $comment = new ArtefactTypeComment($artefactids[0]);

        foreach ($entry->link as $link) {
            // Find the entry this comment comments on
            $commenton = null;
            foreach ($entry->link as $link) {
                if ($importer->curie_equals($link['rel'], PluginImportLeap::NS_LEAP, 'reflects_on') && isset($link['href'])) {
                    $commenton = (string)$link['href'];
                    break;
                }
            }
            if (empty($commenton)) {
                // Shouldn't happen -- was checked when offering the strategy
                throw new ImportException($importer, 'TODO: get_string: cannot find an entry for a comment to comment on');
            }

            if ($linkto == 'artefacts') {
                if (!$artefactids = $importer->get_artefactids_imported_by_entryid($commenton)) {
                    // It's probably a comment on a view; set it up later after view import.
                    continue;
                }
                $comment->set('onartefact', $artefactids[0]);
                $comment->set('onview', null);
                $comment->commit();
            }
            else if ($linkto == 'views' && $comment->get('onview') == self::$tempview) {
                if (!$viewid = $importer->get_viewid_imported_by_entryid($commenton)) {
                    throw new ImportException($importer, hsc("TODO: get_string: view corresponding to $commenton not found when linking comment $entry->id"));
                }
                $comment->set('onview', $viewid);
                $comment->commit();
            }
        }
    }
}

?>
