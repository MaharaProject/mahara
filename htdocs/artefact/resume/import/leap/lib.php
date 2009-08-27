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
     * Achievements map in Mahara to certifications/accreditations
     */
    const STRATEGY_IMPORT_AS_ACHIEVEMENT = 3;

    /**
     * Description of strategies used
     */
    public static function get_import_strategies_for_entry(SimpleXMLElement $entry, PluginImport $importer) {
        $strategies = array();

        $correctplugintype = count($entry->xpath('mahara:artefactplugin[@mahara:plugin="resume"]')) == 1;

        // Goals, cover letter & interests
        $correctrdftype = count($entry->xpath('rdf:type['
            . $importer->curie_xpath('@rdf:resource', PluginImportLeap::NS_LEAPTYPE, 'entry') . ']')) == 1;
        if ($correctrdftype && $correctplugintype) {
            $strategies[] = array(
                'strategy' => self::STRATEGY_IMPORT_AS_ENTRY,
                'score'    => 100,
                'other_required_entries' => array(),
            );
        }

        // Skills
        $correctrdftype = count($entry->xpath('rdf:type['
            . $importer->curie_xpath('@rdf:resource', PluginImportLeap::NS_LEAPTYPE, 'ability') . ']')) == 1;
        if ($correctrdftype && $correctplugintype) {
            $strategies[] = array(
                'strategy' => self::STRATEGY_IMPORT_AS_ABILITY,
                'score'    => 100,
                'other_required_entries' => array(),
            );
        }

        // Achievements
        $correctrdftype = count($entry->xpath('rdf:type['
            . $importer->curie_xpath('@rdf:resource', PluginImportLeap::NS_LEAPTYPE, 'achievement') . ']')) == 1;
        if ($correctrdftype && $correctplugintype) {
            if (count($entry->xpath('mahara:artefactplugin[@mahara:plugin="resume" and @mahara:type="pseudo:certification"]')) == 1) {
                // We know for certain these are meant to be certifications within Mahara
                $score = 100;
            }
            else {
                // Some things are achievements, but are wrapped up in other things within Mahara, 
                // so these don't get the full score. Of course, if nothing 
                // else claims them, they'll be imported as certifications
                $score = 50;
            }
            $strategies[] = array(
                'strategy' => self::STRATEGY_IMPORT_AS_ACHIEVEMENT,
                'score'    => $score,
                'other_required_entries' => array(),
            );
        }

        // Employment

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
        case self::STRATEGY_IMPORT_AS_ACHIEVEMENT:
            $dates = PluginImportLeap::get_leap_dates($entry);
            $enddate = (isset($dates['end'])) ? self::convert_leap_date_to_resume_date($dates['end']) : '';

            $values = array(
                'date'          => $enddate,
                'title'         => $entry->title,
                'description'   => PluginImportLeap::get_entry_content($entry, $importer),
                'displayorder'  => '', // TODO: if it's part of a selection_type#Grouping  of mahara:type=certification, get ordering from there
            );
            ArtefactTypeResumeComposite::ensure_composite_value($values, 'certification', $importer->get('usr'));
            break;
        default:
            throw new ImportException($importer, 'TODO: get_string: unknown strategy chosen for importing entry');
        }
        return $artefactmapping;
    }

    /**
     * Imports data for the personalinformation artefact type, by looking for 
     * it in the persondata element
     */
    public static function import_author_data(PluginImport $importer, $persondataid) {
        if ($persondataid) {
            $composites = array();

            $person = $importer->get_entry_by_id($persondataid);
            $persondata = $person->xpath('leap:persondata');
            foreach ($persondata as $item) {
                $leapattributes = array();
                foreach ($item->attributes(PluginImportLeap::NS_LEAP) as $key => $value) {
                    $leapattributes[$key] = (string)$value;
                }

                if (!isset($leapattributes['field'])) {
                    // 'Field' is required
                    // http://wiki.cetis.ac.uk/2009-03/LEAP2A_personal_data#field
                    $importer->trace('WARNING: persondata element did not have leap:field attribute');
                    continue;
                }

                if ($leapattributes['field'] == 'dob') {
                    $composites['dateofbirth'] = (string)$item;
                }
                if ($leapattributes['field'] == 'gender') {
                    $gender = (string)$item;
                    if ($gender == '1') {
                        $composites['gender'] = 'male';
                    }
                    else if ($gender == '2') {
                        $composites['gender'] = 'female';
                    }
                    else {
                        $importer->trace('WARNING: gender found but not male or female - no gender stored for this user');
                    }
                }

                $maharaattributes = array();
                foreach ($item->attributes(PluginImportLeap::NS_MAHARA) as $key => $value) {
                    $maharaattributes[$key] = (string)$value;
                }

                if (isset($maharaattributes['field'])) {
                    if (in_array($maharaattributes['field'], array('placeofbirth', 'citizenship', 'visastatus', 'maritalstatus'))) {
                        $composites[$maharaattributes['field']] = (string)$item;
                    }
                }
            }

            if ($composites) {
                $importer->trace('Resume personal information:');
                $importer->trace($composites);

                $artefact = new ArtefactTypePersonalinformation(0, array('owner' => $importer->get('usr')));
                foreach ($composites as $key => $value) {
                    $artefact->set_composite($key, $value);
                }
                $artefact->commit();
            }
        }
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

    /**
     * Converts a LEAP2A date point to a plain text version for resume date 
     * purposes.
     *
     * @param array $date The date - expected to come from {PluginImportLeap::....()}
     * @return string     The date in string form for resume composites
     */
    private static function convert_leap_date_to_resume_date($date) {
        if (isset($date['value'])) {
            return strftime(get_string_from_language(/* TODO: user's language */'en.utf8', 'strftimedaydatetime'), strtotime($date['value']));
        }
        if (isset($date['label'])) {
            return $date['label'];
        }
        return '';
    }

}

?>
