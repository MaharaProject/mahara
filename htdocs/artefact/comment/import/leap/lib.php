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
    private static $savetempview = false;

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
            if (self::$savetempview) {
                $title = get_string('entriesimportedfromleapexport', 'artefact.comment');
                set_field('view', 'title', $title, 'id', self::$tempview);
            }
            else {
                delete_records('view', 'id', self::$tempview);
            }
        }
    }


    public static function get_import_strategies_for_entry(SimpleXMLElement $entry, PluginImportLeap $importer) {
        $strategies = array();

        if (PluginImportLeap::is_rdf_type($entry, $importer, 'entry')
            && $entry->xpath('mahara:artefactplugin[@mahara:type="comment"]')) {

            // Check that the entry 'reflects_on' something
            $otherentries = array();
            foreach ($entry->link as $link) {
                if ($importer->curie_equals($link['rel'], $importer->get_leap2a_namespace(), 'reflects_on') && isset($link['href'])) {
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
     * Get the id of the entry reflected on by a comment entry
     */
    public static function get_referent_entryid(SimpleXMLElement $entry, PluginImportLeap $importer) {
        foreach ($entry->link as $link) {
            if ($importer->curie_equals($link['rel'], $importer->get_leap2a_namespace(), 'reflects_on') && isset($link['href'])) {
                return (string)$link['href'];
            }
        }

        // Shouldn't happen -- this was checked when offering the strategy
        throw new ImportException($importer, 'TODO: get_string: cannot find an entry for a comment to comment on');
    }


    public static function get_comment_instance(SimpleXMLElement $entry, PluginImportLeap $importer) {
        $artefactids = $importer->get_artefactids_imported_by_entryid((string)$entry->id);
        return new ArtefactTypeComment($artefactids[0]);
    }


    /**
     * Relate comments to the artefacts they comment on
     * Attach files to comments
     */
    public static function setup_relationships(SimpleXMLElement $entry, PluginImportLeap $importer) {
        $comment = null;
        $newartefacts = array(); // save any newly created extra ones (eg enclosures)
        $referentid = self::get_referent_entryid($entry, $importer);

        // Link artefact comments; view comments are done later
        if ($artefactids = $importer->get_artefactids_imported_by_entryid($referentid)) {
            $comment = self::get_comment_instance($entry, $importer);
            $comment->set('onartefact', $artefactids[0]);
            $comment->set('onview', null);
        }

        // Attachments
        foreach ($entry->link as $link) {
            if (!$comment) {
                $comment = self::get_comment_instance($entry, $importer);
            }
            if ($id = $importer->create_attachment($entry, $link, $comment)) {
                $newartefacts[] = $id;
            }
        }

        if ($comment) {
            $comment->commit();
        }
        return $newartefacts;
    }

    /**
     * Fix comments to point to the right view.  Probably more
     * appropriate in setup_relationships.  To do that we would have
     * to change that call to happen after views are created.
     */
    public static function setup_view_relationships(SimpleXMLElement $entry, PluginImportLeap $importer) {
        $comment = self::get_comment_instance($entry, $importer);

        if ($comment->get('onartefact')) {
            return;
        }

        $referentid = self::get_referent_entryid($entry, $importer);
        if ($viewid = $importer->get_viewid_imported_by_entryid($referentid)) {
            $comment->set('onview', $viewid);
            $comment->commit();
        }
        else {
            // Nothing to link this comment to, so leave it in the temporary view.
            self::$savetempview = true;
        }
    }
}
