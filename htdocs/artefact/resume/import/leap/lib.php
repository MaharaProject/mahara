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
 * @subpackage artefact-file-import-leap
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/**
 * Implements Leap2A import of resume related entries into Mahara
 *
 * For more information about Leap resume importing, see:
 * http://wiki.mahara.org/Developer_Area/Import//Export/LEAP_Import/Resume_Artefact_Plugin
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
     * Activities in category life_area:Work map to employment history
     */
    const STRATEGY_IMPORT_AS_EMPLOYMENT = 4;

    /**
     * Resources of resource_type:Printed map to books/publications
     */
    const STRATEGY_IMPORT_AS_BOOK = 5;

    /**
     * Activities in category life_area:Education map to education history
     */
    const STRATEGY_IMPORT_AS_EDUCATION = 6;

    /**
     * Activities using some mapping to be decided map to professional memberships
     *
     * It doesn't look like the spec provides a way to represent these, so for 
     * now we look at the Mahara plugin element
     */
    const STRATEGY_IMPORT_AS_MEMBERSHIP = 7;

    /**
     * Selections made for grouping resume fields are blackholed
     */
    const STRATEGY_IMPORT_AS_SELECTION = 8;

    /**
     * All users need one of these, but it's a "fake" artefact - it just
     * represents profile information. It's not exported. So we create one here
     * for imported users.
     */
    public static function setup(PluginImportLeap $importer) {
        try {
            ArtefactTypeContactinformation::setup_new($importer->get('usr'));
        } catch (ParamOutOfRangeException $e) {} // probably already has one
    }

    /**
     * Description of strategies used
     */
    public static function get_import_strategies_for_entry(SimpleXMLElement $entry, PluginImportLeap $importer) {
        $strategies = array();

        $correctplugintype = count($entry->xpath('mahara:artefactplugin[@mahara:plugin="resume"]')) == 1;
        $isentry       = PluginImportLeap::is_rdf_type($entry, $importer, 'entry');
        $isability     = PluginImportLeap::is_rdf_type($entry, $importer, 'ability');
        $isachievement = PluginImportLeap::is_rdf_type($entry, $importer, 'achievement');
        $isactivity    = PluginImportLeap::is_rdf_type($entry, $importer, 'activity');
        $isaffiliation = PluginImportLeap::is_rdf_type($entry, $importer, 'affiliation');
        $isresource    = PluginImportLeap::is_rdf_type($entry, $importer, 'resource');
        $ispublication = PluginImportLeap::is_rdf_type($entry, $importer, 'publication');

        // Goals, cover letter & interests
        if ($isentry && $correctplugintype) {
            return array(array(
                'strategy' => self::STRATEGY_IMPORT_AS_ENTRY,
                'score'    => 100,
                'other_required_entries' => array(),
            ));
        }

        // Skills
        if ($isability && $correctplugintype) {
            return array(array(
                'strategy' => self::STRATEGY_IMPORT_AS_ABILITY,
                'score'    => 100,
                'other_required_entries' => array(),
            ));
        }

        // Achievements
        if ($isachievement) {
            if ($correctplugintype && count($entry->xpath('mahara:artefactplugin[@mahara:plugin="resume" and @mahara:type="pseudo:certification"]')) == 1) {
                // We know for certain these are meant to be certifications within Mahara
                $score = 100;
            }
            else {
                // Some things are achievements, but are wrapped up in other things within Mahara, 
                // so these don't get the full score. Of course, if nothing 
                // else claims them, they'll be imported as certifications
                $score = 50;
            }
            return array(array(
                'strategy' => self::STRATEGY_IMPORT_AS_ACHIEVEMENT,
                'score'    => $score,
                'other_required_entries' => array(),
            ));
        }

        // Employment
        $other_required_entries = array();
        if (($isactivity || $isentry) &&
            (PluginImportLeap::is_correct_category_scheme($entry, $importer, 'life_area', 'Work')
            || PluginImportLeap::is_correct_category_scheme($entry, $importer, 'life_area', 'Placement'))
            ) {
            foreach ($entry->link as $link) {
                if (!isset($other_required_entries['organization'])
                    && $organization = self::check_for_supporting_organization($importer, $link)) {
                    $other_required_entries['organization'] = $organization;
                }
            }

            return array(array(
                'strategy' => self::STRATEGY_IMPORT_AS_EMPLOYMENT,
                'score'    => 100,
                'other_required_entries' => $other_required_entries,
            ));
        }

        // Books
        $other_required_entries = array();
        if (($ispublication || $isresource || $isentry) && PluginImportLeap::is_correct_category_scheme($entry, $importer, 'resource_type', 'Printed')) {
            // If it exists, the related achievement will be the user's role in 
            // relation to the book
            foreach ($entry->link as $link) {
                if ($importer->curie_equals($link['rel'], '', 'related') && isset($link['href'])) {
                    if ($potentialrole = $importer->get_entry_by_id((string)$link['href'])) {
                        if (PluginImportLeap::is_rdf_type($potentialrole, $importer, 'achievement')) {
                            // We have a related achievement!
                            $other_required_entries[] = (string)$link['href'];
                            break;
                        }
                    }
                }
            }

            return array(array(
                'strategy' => self::STRATEGY_IMPORT_AS_BOOK,
                'score'    => 100,
                'other_required_entries' => $other_required_entries,
            ));
        }

        // Education
        $other_required_entries = array();
        if (($isactivity || $isentry) && PluginImportLeap::is_correct_category_scheme($entry, $importer, 'life_area', 'Education')) {
            // If this entry supports an achievement, that achievement will be 
            // the qualification the user gained in relation to this entry
            foreach ($entry->link as $link) {
                if (!isset($other_required_entries['achievement'])
                    && $importer->curie_equals($link['rel'], $importer->get_leap2a_namespace(), 'supports') && isset($link['href'])) {
                    if ($potentialqualification = $importer->get_entry_by_id((string)$link['href'])) {
                        if (PluginImportLeap::is_rdf_type($potentialqualification, $importer, 'achievement')) {
                            // We have a related achievement!
                            $other_required_entries['achievement'] = (string)$link['href'];
                        }
                    }
                }

                if (!isset($other_required_entries['organization'])
                    && $organization = self::check_for_supporting_organization($importer, $link)) {
                    $other_required_entries['organization'] = $organization;
                }
            }

            return array(array(
                'strategy' => self::STRATEGY_IMPORT_AS_EDUCATION,
                'score'    => 100,
                'other_required_entries' => $other_required_entries,
            ));
        }

        // Professional Membership
        $correctmaharatype = count($entry->xpath('mahara:artefactplugin[@mahara:plugin="resume" and @mahara:type="pseudo:membership"]')) == 1;
        if (($isactivity && $correctmaharatype) || $isaffiliation) {
            return array(array(
                'strategy' => self::STRATEGY_IMPORT_AS_MEMBERSHIP,
                'score'    => 100,
                'other_required_entries' => array(),
            ));
        }

        // Special Mahara selections made for grouping resume entries
        $correctmaharaplugin = count($entry->xpath('mahara:artefactplugin[@mahara:plugin="resume"]')) == 1;
        if ($correctmaharaplugin
            && PluginImportLeap::is_rdf_type($entry, $importer, 'selection')
            && PluginImportLeap::is_correct_category_scheme($entry, $importer, 'selection_type', 'Grouping')) {
            return array(array(
                'strategy' => self::STRATEGY_IMPORT_AS_SELECTION,
                'score'    => 100,
                'other_required_entries' => array(),
            ));
        }

        return array();
    }

    public static function import_using_strategy(SimpleXMLElement $entry, PluginImportLeap $importer, $strategy, array $otherentries) {
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

                $maharaattributes = PluginImportLeap::get_attributes($artefactpluginelement, PluginImportLeap::NS_MAHARA);
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
            $dates = PluginImportLeap::get_leap_dates($entry, $importer->get_namespaces(), $importer->get_leap2a_namespace());
            $enddate = (isset($dates['end'])) ? self::convert_leap_date_to_resume_date($dates['end']) : '';

            $values = array(
                'date'          => $enddate,
                'title'         => $entry->title,
                'description'   => PluginImportLeap::get_entry_content($entry, $importer),
                'displayorder'  => self::get_display_order_for_entry($entry, $importer, 'certification'),
            );
            ArtefactTypeResumeComposite::ensure_composite_value($values, 'certification', $importer->get('usr'));
            break;
        case self::STRATEGY_IMPORT_AS_EMPLOYMENT:
            $dates = PluginImportLeap::get_leap_dates($entry, $importer->get_namespaces(), $importer->get_leap2a_namespace());
            $startdate = (isset($dates['start'])) ? self::convert_leap_date_to_resume_date($dates['start']) : '';
            $enddate   = (isset($dates['end']))   ? self::convert_leap_date_to_resume_date($dates['end'])   : '';

            $employer = '';
            if (isset($otherentries['organization'])) {
                $organization = $importer->get_entry_by_id($otherentries['organization']);
                $employer = $organization->title;
            }

            $values = array(
                'startdate' => $startdate,
                'enddate'   => $enddate,
                'employer'  => $employer,
                'jobtitle'  => $entry->title,
                'positiondescription' => PluginImportLeap::get_entry_content($entry, $importer),
                'displayorder'  => self::get_display_order_for_entry($entry, $importer, 'employmenthistory'),
            );
            ArtefactTypeResumeComposite::ensure_composite_value($values, 'employmenthistory', $importer->get('usr'));
            break;
        case self::STRATEGY_IMPORT_AS_BOOK:
            $dates = PluginImportLeap::get_leap_dates($entry, $importer->get_namespaces(), $importer->get_leap2a_namespace());
            $enddate   = (isset($dates['end']))   ? self::convert_leap_date_to_resume_date($dates['end'])   : '';

            $contribution = $description = '';
            if (count($otherentries)) {
                $role = $importer->get_entry_by_id($otherentries[0]);
                $contribution = $role->title;
                $description  = PluginImportLeap::get_entry_content($role, $importer);
            }
            // check if the import is of the version leap2a 2010-07. If it is then override the contribution and description
            if($importer->get_leap2a_namespace() == PluginImportLeap::NS_LEAP) {
                $myrole = PluginImportLeap::get_leap_myrole($entry, $importer->get_namespaces(), $importer->get_leap2a_namespace());
                if($myrole) {
                    $contribution = $myrole;
                }
                $description  = PluginImportLeap::get_entry_content($entry, $importer);
            }

            $values = array(
                'date' => $enddate,
                'title'   => $entry->title,
                'contribution' => $contribution,
                'description'  => $description,
                'displayorder'  => self::get_display_order_for_entry($entry, $importer, 'book'),
            );
            ArtefactTypeResumeComposite::ensure_composite_value($values, 'book', $importer->get('usr'));
            break;
        case self::STRATEGY_IMPORT_AS_EDUCATION:
            $dates = PluginImportLeap::get_leap_dates($entry, $importer->get_namespaces(), $importer->get_leap2a_namespace());
            $startdate = (isset($dates['start'])) ? self::convert_leap_date_to_resume_date($dates['start']) : '';
            $enddate   = (isset($dates['end']))   ? self::convert_leap_date_to_resume_date($dates['end'])   : '';

            $qualtype = $qualname = '';
            if (isset($otherentries['achievement'])) {
                $qualification = $importer->get_entry_by_id($otherentries['achievement']);
                $qualtype      = $qualification->title;
                $qualname      = PluginImportLeap::get_entry_content($qualification, $importer);
            }

            $institution = '';
            if (isset($otherentries['organization'])) {
                $organization = $importer->get_entry_by_id($otherentries['organization']);
                $institution = $organization->title;
            }

            if (!$qualname) {
                $qualname = $entry->title;
            }

            $values = array(
                'startdate' => $startdate,
                'enddate'   => $enddate,
                'qualtype'  => $qualtype,
                'qualname'  => $qualname,
                'institution' => $institution,
                'qualdescription' => PluginImportLeap::get_entry_content($entry, $importer),
                'displayorder'  => self::get_display_order_for_entry($entry, $importer, 'educationhistory'),
            );
            ArtefactTypeResumeComposite::ensure_composite_value($values, 'educationhistory', $importer->get('usr'));
            break;
        case self::STRATEGY_IMPORT_AS_MEMBERSHIP:
            $dates = PluginImportLeap::get_leap_dates($entry, $importer->get_namespaces(), $importer->get_leap2a_namespace());
            $startdate = (isset($dates['start'])) ? self::convert_leap_date_to_resume_date($dates['start']) : '';
            $enddate   = (isset($dates['end']))   ? self::convert_leap_date_to_resume_date($dates['end'])   : '';

            $values = array(
                'startdate' => $startdate,
                'enddate'   => $enddate,
                'title'  => $entry->title,
                'description' => PluginImportLeap::get_entry_content($entry, $importer),
                'displayorder' => self::get_display_order_for_entry($entry, $importer, 'membership'),
            );
            ArtefactTypeResumeComposite::ensure_composite_value($values, 'membership', $importer->get('usr'));
            break;
        case self::STRATEGY_IMPORT_AS_SELECTION:
            // This space intentionally left blank
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
            $namespaces = $importer->get_namespaces();
            $ns = $importer->get_leap2a_namespace();
            $persondata = $person->xpath($namespaces[$ns].':persondata');
            foreach ($persondata as $item) {
                $leapattributes = PluginImportLeap::get_attributes($item, $ns);

                if (!isset($leapattributes['field'])) {
                    // 'Field' is required
                    // http://wiki.cetis.ac.uk/2009-03/Leap2A_personal_data#field
                    $importer->trace('WARNING: persondata element did not have leap2:field attribute');
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

                $maharaattributes = PluginImportLeap::get_attributes($item, PluginImportLeap::NS_MAHARA);

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
     * @param PluginImportLeap $importer The importer
     * @param string $artefacttype       The type of artefact to create
     * @param string $title              The title for the artefact
     * @param string $content            The content for the artefact
     * @return int The ID of the artefact created
     */
    private static function create_artefact(PluginImportLeap $importer, $artefacttype, $title, $content) {
        $classname = 'ArtefactType' . ucfirst($artefacttype);
        $artefact = new $classname(0, array('owner' => $importer->get('usr')));
        $artefact->set('title', $title);
        $artefact->set('description', $content);
        $artefact->commit();
        return $artefact->get('id');
    }

    /**
     * Converts a Leap2A date point to a plain text version for resume date 
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

    /**
     * Given an entry link, see whether it's a relationship referring to a 
     * supporting organization, and if so, returns the ID of the organization
     *
     * @param PluginImportLeap $importer The importer
     * @param array            $link     The link to check
     * @return string The ID of the organization if there is one, else an empty string
     */
    private static function check_for_supporting_organization(PluginImportLeap $importer, $link) {
        if (($importer->curie_equals($link['rel'], $importer->get_leap2a_namespace(), 'supported_by') ||
            // this is necessary for backwards compatibility. For LEAP2A 2009-03 exports the relationship
            // value 'is_supported_by' was used instead of the correct 'supported_by'.
            $importer->curie_equals($link['rel'], $importer->get_leap2a_namespace(), 'is_supported_by'))
            && isset($link['href'])) {
            if ($potentialorganization = $importer->get_entry_by_id((string)$link['href'])) {
                if (PluginImportLeap::is_rdf_type($potentialorganization, $importer, 'organization')) {
                    return (string)$link['href'];
                }
            }
        }
        return '';
    }

    /**
     * Given an entry, see if it's attached to one of the special selections 
     * representing a Mahara resume group. If so, return the display order it 
     * should have in that group.
     *
     * We look for the special Mahara selections only, because entries could be 
     * in more than one selection, with different display orders in each.
     *
     * @param SimpleXMLElement $entry    The entry to check
     * @param PluginImportLeap $importer The importer
     * @param string $selectiontype      The type of selection we're checking to 
     *                                   see if the entry is part of - one of the 
     *                                   special Mahara resume selections
     * @return int The display order of the element in the selection, should it 
     *             be in one - else null
     */
    private static function get_display_order_for_entry(SimpleXMLElement $entry, PluginImportLeap $importer, $selectiontype) {
        static $cache = array();
        $found = false;

        foreach ($entry->link as $link) {
            if ($importer->curie_equals($link['rel'], $importer->get_leap2a_namespace(), 'is_part_of') && isset($link['href'])) {
                $href = (string)$link['href'];
                if (isset($cache[$href])) {
                    $found = true;
                }
                else if ($potentialselection = $importer->get_entry_by_id($href)) {
                    if (PluginImportLeap::is_rdf_type($potentialselection, $importer, 'selection')) {
                        if (PluginImportLeap::is_correct_category_scheme($potentialselection, $importer, 'selection_type', 'Grouping')) {
                            if (count($potentialselection->xpath('mahara:artefactplugin[@mahara:type="' . $selectiontype . '"]')) == 1) {
                                $cache[$href] = true;
                                $found = true;
                            }
                        }
                    }
                }

                if ($found) {
                    $leapattributes = $importer->get_attributes($link, $importer->get_leap2a_namespace());
                    $displayorder = (isset($leapattributes['display_order']) && intval($leapattributes['display_order']) > 0)
                        ? $leapattributes['display_order']
                        : '';
                    return $displayorder;
                }
            }
        }
    }

}
