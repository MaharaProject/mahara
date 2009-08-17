<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @subpackage artefact-file-import-leap
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/**
 * Implements LEAP2A import of resume related entries into Mahara
 *
 * For more information about LEAP resume importing, see:
 * http://wiki.mahara.org/Developer_Area/Import%2f%2fExport/LEAP_Import/Resume_Artefact_Plugin
 */
class LeapImportResume extends LeapImportArtefactPlugin {

    /**
     * Some resume data is simply entries
     */
    const STRATEGY_IMPORT_AS_ENTRY = 1;

    /**
     * Skills count as "abilities" according to the spec
     */
    const STRATEGY_IMPORT_AS_ABILITY = 2;

    /**
     * Description of strategies used
     */
    public static function get_import_strategies_for_entry(SimpleXMLElement $entry, PluginImport $importer) {
        $strategies = array();

        $correctplugintype = count($entry->xpath('mahara:artefactplugin[@mahara:plugin="resume"]')) == 1;

        $correctrdftype = count($entry->xpath('rdf:type['
            . $importer->curie_xpath('@rdf:resource', PluginImportLeap::NS_LEAPTYPE, 'entry') . ']')) == 1;
        if ($correctrdftype && $correctplugintype) {
            // Goals, cover letter, interests match here
            $strategies[] = array(
                'strategy' => self::STRATEGY_IMPORT_AS_ENTRY,
                'score'    => 100,
                'other_required_entries' => array(),
            );
        }

        $correctrdftype = count($entry->xpath('rdf:type['
            . $importer->curie_xpath('@rdf:resource', PluginImportLeap::NS_LEAPTYPE, 'ability') . ']')) == 1;
        if ($correctrdftype && $correctplugintype) {
            // Skills match here
            $strategies[] = array(
                'strategy' => self::STRATEGY_IMPORT_AS_ABILITY,
                'score'    => 100,
                'other_required_entries' => array(),
            );
        }

        return $strategies;
    }

    public static function import_using_strategy(SimpleXMLElement $entry, PluginImport $importer, $strategy, array $otherentries) {
        $artefactmapping = array();
        switch ($strategy) {
        case self::STRATEGY_IMPORT_AS_ENTRY:
        case self::STRATEGY_IMPORT_AS_ABILITY:
            // Based on the mahara:type, we might be able to import it as 
            // something useful - otherwise, there is nothing we can do. The 
            // entry already claimed it was mahara:plugin="resume", so it's 
            // perfectly fine for us to not import it if we don't recognise it
            if ($strategy == self::STRATEGY_IMPORT_AS_ENTRY) {
                $types = array(
                    'careergoal',
                    'academicgoal',
                    'personalgoal',
                    'interest',
                    'coverletter',
                );
            }
            else {
                $types = array(
                    'workskill',
                    'academicskill',
                    'personalskill',
                );
            }

            $typexpath = join('" or @mahara:type="', $types);
            $artefactpluginelement = $entry->xpath('mahara:artefactplugin[@mahara:type="' . $typexpath . '"]');
            if (count($artefactpluginelement) == 1) {
                $artefactpluginelement = $artefactpluginelement[0];

                $maharaattributes = array();
                foreach ($artefactpluginelement->attributes(PluginImportLeap::NS_MAHARA)
                    as $key => $value) {
                    $maharaattributes[$key] = (string)$value;
                }

                if (isset($maharaattributes['type']) && in_array($maharaattributes['type'], $types)) {
                    $artefactmapping[(string)$entry->id] = array(self::create_artefact(
                        $importer,
                        $maharaattributes['type'],
                        $entry->title,
                        PluginImportLeap::get_entry_content($entry, $importer)
                    ));
                }
            }
            break;
        default:
            throw new ImportException($importer, 'TODO: get_string: unknown strategy chosen for importing entry');
        }
        return $artefactmapping;
    }

    /**
     * Creates an artefact in the manner required to overwrite existing profile 
     * artefacts
     *
     * @param PluginImport $importer The importer
     * @param string $artefacttype   The type of artefact to create
     * @param string $title          The title for the artefact
     * @param string $content        The content for the artefact
     * @return int The ID of the artefact created
     */
    private static function create_artefact(PluginImport $importer, $artefacttype, $title, $content) {
        $classname = 'ArtefactType' . ucfirst($artefacttype);
        $artefact = new $classname(0, array('owner' => $importer->get('usr')));
        $artefact->set('title', $title);
        $artefact->set('description', $content);
        $artefact->commit();
        return $artefact->get('id');
    }

}

?>
