<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-file-import-leap
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

/**
 * Implements Leap2A import of resume related entries into Mahara
 *
 * For more information about Leap resume importing, see:
 * https://wiki.mahara.org/wiki/Developer_Area/Import//Export/LEAP_Import/Resume_Artefact_Plugin
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
     * Personal information map in Mahara to personal informations
     */
    const STRATEGY_IMPORT_AS_PERSONALINFORMATION = 9;

    /**
     * All users need one of these, but it's a "fake" artefact - it just
     * represents resume information. It's not exported. So we create one here
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

    public static function add_import_entry_request_using_strategy(SimpleXMLElement $entry, PluginImportLeap $importer, $strategy, array $otherentries) {
        $entrydata = self::get_entry_data_using_strategy($entry, $importer, $strategy, $otherentries);
        if (!empty($entrydata)) {
            PluginImportLeap::add_import_entry_request($importer->get('importertransport')->get('importid'), (string)$entry->id, $strategy, 'resume', $entrydata);
        }
    }

/**
 * Import from entry requests for Mahara user resume fields
 *
 * @param PluginImportLeap $importer
 * @return updated DB
 * @throw    ImportException
 */
    public static function import_from_requests(PluginImportLeap $importer) {
        if ($entry_requests = get_records_select_array('import_entry_requests', 'importid = ? AND plugin = ?', array($importer->get('importertransport')->get('importid'), 'resume'))) {
            foreach ($entry_requests as $entry_request) {
                $entrycontent = unserialize($entry_request->entrycontent);
                switch ($entry_request->strategy) {
                    case self::STRATEGY_IMPORT_AS_ENTRY:
                    case self::STRATEGY_IMPORT_AS_ABILITY:
                        self::create_artefact_from_request($importer, $entry_request);
                        break;
                    case self::STRATEGY_IMPORT_AS_ACHIEVEMENT:
                    case self::STRATEGY_IMPORT_AS_EMPLOYMENT:
                    case self::STRATEGY_IMPORT_AS_BOOK:
                    case self::STRATEGY_IMPORT_AS_EDUCATION:
                    case self::STRATEGY_IMPORT_AS_MEMBERSHIP:
                        self::create_composite_artefact_from_request($importer, $entry_request);
                        break;
                    case self::STRATEGY_IMPORT_AS_PERSONALINFORMATION:
                        self::create_personalinformation_artefact_from_request($importer, $entry_request);
                        break;
                    case self::STRATEGY_IMPORT_AS_SELECTION:
                        // This space intentionally left blank
                        break;
                    default:
                        throw new ImportException($importer, 'TODO: get_string: unknown strategy chosen for importing entry');
                }
            }
        }
    }

    public static function import_using_strategy(SimpleXMLElement $entry, PluginImportLeap $importer, $strategy, array $otherentries) {
        $values = self::get_entry_data_using_strategy($entry, $importer, $strategy, $otherentries);
        if (!$values) {
            return $values;
        }

        $artefactmapping = array();
        switch($strategy) {
            case self::STRATEGY_IMPORT_AS_ABILITY:
            case self::STRATEGY_IMPORT_AS_ENTRY:
                $artefactmapping[(string)$entry->id] = array(self::create_artefact(
                    $importer,
                    $values['type'],
                    $values['content']['title'],
                    PluginImportLeap::get_entry_content($entry, $importer)
                ));
                break;
            case self::STRATEGY_IMPORT_AS_ACHIEVEMENT:
            case self::STRATEGY_IMPORT_AS_EMPLOYMENT:
            case self::STRATEGY_IMPORT_AS_BOOK:
            case self::STRATEGY_IMPORT_AS_EDUCATION:
            case self::STRATEGY_IMPORT_AS_MEMBERSHIP:
                ArtefactTypeResumeComposite::ensure_composite_value($values['content'], $values['type'], $values['owner']);
                break;
        }
        return $artefactmapping;
    }

    /**
     * Get resume field data from import entry using strategy
     *
     * @param SimpleXMLElement $entry
     * @param PluginImportLeap $importer
     * @param $strategy
     * @param array $otherentries
     * @throws ImportException
     * @return array $values    resume data:
     *     array(
                'owner'   => <owner>,
                'type'    => <maharaartefacttype>,
                'content' => array(
                    'title'       => <title>,
                    'description' => <description>,
                    ...
            )
     */
    private static function get_entry_data_using_strategy(SimpleXMLElement $entry, PluginImportLeap $importer, $strategy, array $otherentries) {
        $values = array();
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
                        $values = array(
                            'owner'   => $importer->get('usr'),
                            'type'    => $maharaattributes['type'],
                            'content' => array(
                                'title'       => (string)$entry->title,
                                'description' => PluginImportLeap::get_entry_content($entry, $importer)
                            ),
                        );
                    }
                }
                break;
            case self::STRATEGY_IMPORT_AS_ACHIEVEMENT:
                $dates = PluginImportLeap::get_leap_dates($entry, $importer->get_namespaces(), $importer->get_leap2a_namespace());
                $enddate = (isset($dates['end'])) ? self::convert_leap_date_to_resume_date($dates['end']) : '';

                $values = array(
                    'owner' => $importer->get('usr'),
                    'type' => 'certification',
                    'content' => array(
                        'date'          => $enddate,
                        'title'         => (string)$entry->title,
                        'description'   => PluginImportLeap::get_entry_content($entry, $importer),
                        'displayorder'  => self::get_display_order_for_entry($entry, $importer, 'certification'),
                    ),
                );
                break;
            case self::STRATEGY_IMPORT_AS_EMPLOYMENT:
                $dates = PluginImportLeap::get_leap_dates($entry, $importer->get_namespaces(), $importer->get_leap2a_namespace());
                $startdate = (isset($dates['start'])) ? self::convert_leap_date_to_resume_date($dates['start']) : '';
                $enddate   = (isset($dates['end']))   ? self::convert_leap_date_to_resume_date($dates['end'])   : '';

                $employer = '';
                if (isset($otherentries['organization'])) {
                    $organization = $importer->get_entry_by_id($otherentries['organization']);
                    $employer = (string)$organization->title;
                }

                $values = array(
                    'owner' => $importer->get('usr'),
                    'type' => 'employmenthistory',
                    'content' => array(
                        'title'     => (string)$entry->title,
                        'startdate' => $startdate,
                        'enddate'   => $enddate,
                        'employer'  => $employer,
                        'jobtitle'  => (string)$entry->title,
                        'positiondescription' => PluginImportLeap::get_entry_content($entry, $importer),
                        'displayorder'  => self::get_display_order_for_entry($entry, $importer, 'employmenthistory'),
                    )
                );
                break;
            case self::STRATEGY_IMPORT_AS_BOOK:
                $dates = PluginImportLeap::get_leap_dates($entry, $importer->get_namespaces(), $importer->get_leap2a_namespace());
                $enddate   = (isset($dates['end']))   ? self::convert_leap_date_to_resume_date($dates['end'])   : '';

                $contribution = $description = '';
                if (count($otherentries)) {
                    $role = $importer->get_entry_by_id($otherentries[0]);
                    $contribution = (string)$role->title;
                    $description  = PluginImportLeap::get_entry_content($role, $importer);
                }
                // check if the import is of the version leap2a 2010-07. If it is then override the contribution and description
                if ($importer->get_leap2a_namespace() == PluginImportLeap::NS_LEAP) {
                    $myrole = PluginImportLeap::get_leap_myrole($entry, $importer->get_namespaces(), $importer->get_leap2a_namespace());
                    if ($myrole) {
                        $contribution = (string)$myrole;
                    }
                    $description  = PluginImportLeap::get_entry_content($entry, $importer);
                }

                $values = array(
                    'owner' => $importer->get('usr'),
                    'type' => 'book',
                    'content' => array(
                        'date' => $enddate,
                        'title'   => (string)$entry->title,
                        'contribution' => $contribution,
                        'description'  => $description,
                        'displayorder'  => self::get_display_order_for_entry($entry, $importer, 'book'),
                    )
                );
                break;
            case self::STRATEGY_IMPORT_AS_EDUCATION:
                $dates = PluginImportLeap::get_leap_dates($entry, $importer->get_namespaces(), $importer->get_leap2a_namespace());
                $startdate = (isset($dates['start'])) ? self::convert_leap_date_to_resume_date($dates['start']) : '';
                $enddate   = (isset($dates['end']))   ? self::convert_leap_date_to_resume_date($dates['end'])   : '';

                $qualtype = $qualname = '';
                if (isset($otherentries['achievement'])) {
                    $qualification = $importer->get_entry_by_id($otherentries['achievement']);
                    $qualtype      = (string)$qualification->title;
                    $qualname      = PluginImportLeap::get_entry_content($qualification, $importer);
                }

                $institution = '';
                if (isset($otherentries['organization'])) {
                    $organization = $importer->get_entry_by_id($otherentries['organization']);
                    $institution = (string)$organization->title;
                }

                if (!$qualname) {
                    $qualname = (string)$entry->title;
                }

                $values = array(
                    'owner' => $importer->get('usr'),
                    'type' => 'educationhistory',
                    'content' => array(
                        'title'     => $qualname,
                        'startdate' => $startdate,
                        'enddate'   => $enddate,
                        'qualtype'  => $qualtype,
                        'qualname'  => $qualname,
                        'institution' => $institution,
                        'qualdescription' => PluginImportLeap::get_entry_content($entry, $importer),
                        'displayorder'  => self::get_display_order_for_entry($entry, $importer, 'educationhistory'),
                    )
                );
                break;
            case self::STRATEGY_IMPORT_AS_MEMBERSHIP:
                $dates = PluginImportLeap::get_leap_dates($entry, $importer->get_namespaces(), $importer->get_leap2a_namespace());
                $startdate = (isset($dates['start'])) ? self::convert_leap_date_to_resume_date($dates['start']) : '';
                $enddate   = (isset($dates['end']))   ? self::convert_leap_date_to_resume_date($dates['end'])   : '';

                $values = array(
                    'owner' => $importer->get('usr'),
                    'type' => 'membership',
                    'content' => array(
                        'startdate' => $startdate,
                        'enddate'   => $enddate,
                        'title'  => (string)$entry->title,
                        'description' => PluginImportLeap::get_entry_content($entry, $importer),
                        'displayorder' => self::get_display_order_for_entry($entry, $importer, 'membership'),
                    )
                );
                break;
            case self::STRATEGY_IMPORT_AS_SELECTION:
                // This space intentionally left blank
                break;
            default:
                throw new ImportException($importer, 'TODO: get_string: unknown strategy chosen for importing entry');
        }
        // Composite types should always be appended or ignored
        if (isset($values['type']) && in_array($values['type'], ArtefactTypeResumeComposite::get_composite_artefact_types())) {
            $values['defaultdecision'] = PluginImportLeap::DECISION_APPEND;
        }
        return $values;
    }

    /**
     * Get personal information from author data entry
     * @param PluginImport     $importer
     * @param $persondataid    author ID of the import file
     * @return array
     */
    private static function get_personalinformation_from_author_data(PluginImport $importer, $persondataid) {
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
                $composites['title'] = get_string('personalinformation', 'artefact.resume');

                return array(
                        'owner'   => $importer->get('usr'),
                        'type'    => 'personalinformation',
                        'content' => $composites,
                );
            }
        }
        return false;
    }

    /**
     * Add import entry request for the personalinformation artefact type, by looking for
     * it in the persondata element
     */
    public static function add_import_entry_request_author_data(PluginImport $importer, $persondataid) {
        if ($data = self::get_personalinformation_from_author_data($importer, $persondataid)) {
            return PluginImportLeap::add_import_entry_request($importer->get('importertransport')->get('importid'), $persondataid, self::STRATEGY_IMPORT_AS_PERSONALINFORMATION, 'resume', $data);
        }
    }

    /**
     * Imports data for the personalinformation artefact type, by looking for
     * it in the persondata element
     */
    public static function import_author_data(PluginImport $importer, $persondataid) {
        if ($data = self::get_personalinformation_from_author_data($importer, $persondataid)) {
            $artefact = new ArtefactTypePersonalinformation(0, array('owner' => $importer->get('usr')));
            foreach ($data['content'] as $key => $value) {
                if ($key === 'title') {
                    continue;
                }
                $artefact->set_composite($key, $value);
            }
            $artefact->commit();
        }
    }

    /**
     * Imports data for the "Personal Information" section of the resume.
     * TODO: Currently the user has to make  one decision about all of it -- it would be nice if
     * they could make a separate decision about each field.
     * @param PluginImport $importer
     * @param array $entry_request
     * @return int The ID of the artefact created or updated, or 0 if none was touched
     */
    private static function create_personalinformation_artefact_from_request(PluginImport $importer, $entry_request) {
        global $USER;
        $aid = 0;
        $values = unserialize($entry_request->entrycontent);
        switch ($entry_request->decision) {
            case PluginImport::DECISION_IGNORE:
                $duplicatedids = unserialize($entry_request->duplicateditemids);
                if (!empty($duplicatedids)) {
                    $aid = $duplicatedids[0];
                }
                break;
            case PluginImport::DECISION_REPLACE:
                $existingids = unserialize($entry_request->existingitemids);
                if (!empty($existingids)) {
                    try {
                        $a = artefact_instance_from_id($existingids[0]);
                        if ($USER->get('id') != $a->get('owner')) {
                            return 0;
                        }
                    }
                    catch (Exception $e) {
                        return 0;
                    }
                }
                break;
            case PluginImport::DECISION_APPEND:
                // We will literally append the content of each text field to each existing text field
                // We ignore numeric and date fields
                $existingids = unserialize($entry_request->existingitemids);
                if (!empty($existingids)) {
                    try {
                        $a = artefact_instance_from_id($existingids[0]);
                        if ($USER->get('id') != $a->get('owner')) {
                            return 0;
                        }
                        foreach (array_keys(ArtefactTypePersonalinformation::get_composite_fields()) as $fieldname) {
                            if (!empty($values[$fieldname]) && !is_numeric($values[$fieldname]) && $fieldname !== 'dateofbirth') {
                                $values[$fieldname] = $a->get_composite($fieldname) . ' ' . $values[$fieldname];
                            }
                        }
                    }
                    catch (ArtefactNotFoundException $e) {
                        $a = new ArtefactTypePersonalinformation(
                                0,
                                array(
                                        'owner' => $importer->get('usr'),
                                        'title' => get_string($entry_request->entrytype, 'artefact.resume')
                                )
                        );
                        $a->commit();
                    }
                    catch (Exception $e) {
                        return 0;
                    }
                    break;
                }
                break;
            case PluginImport::DECISION_ADDNEW:
                try {
                    $a = artefact_instance_from_type('personalinformation', $USER->get('id'));
                    $a->set('mtime', time());
                }
                catch (ArtefactNotFoundException $e) {
                    $a = new ArtefactTypePersonalinformation(
                            0,
                            array(
                                    'owner' => $importer->get('usr'),
                                    'title' => get_string($entry_request->entrytype, 'artefact.resume')
                            )
                    );
                }
                catch (Exception $e) {
                    return 0;
                }
                break;
            default:
                break;
        }
        if (isset($a)) {
            foreach (array_keys(ArtefactTypePersonalinformation::get_composite_fields()) as $field) {
                if (!empty($values[$field])) {
                    $a->set_composite($field, $values[$field]);
                }
            }
            $a->commit();
            $aid = $a->get('id');
        }
        if ($aid) {
            $importer->add_artefactmapping($entry_request->entryid, $aid);
        }
        return $aid;
    }

    /**
     * Create or update a composite artefact for resume from an import entry request
     * @param PluginImport $importer
     * @param unknown_type $entry_request
     */
    private static function create_composite_artefact_from_request(PluginImport $importer, $entry_request) {
        global $USER;
        $aid = 0;
        $classname = generate_artefact_class_name($entry_request->entrytype);
        $values = unserialize($entry_request->entrycontent);
        switch ($entry_request->decision) {
            case PluginImport::DECISION_IGNORE:
                $duplicatedids = unserialize($entry_request->duplicateditemids);
                if (!empty($duplicatedids)) {
                    $aid = $duplicatedids[0];
                }
                break;
            // For composite artefacts, it only makes sense to ignore or replace them, and those are
            // the only options the form should have shown! To make things less crashy, though, we'll
            // just default to the same behavior as long as you specified anything other than
            // DECISION_IGNORE
            case PluginImport::DECISION_REPLACE:
            case PluginImport::DECISION_ADDNEW:
            case PluginImport::DECISION_APPEND:
            default:
                $result = ArtefactTypeResumeComposite::ensure_composite_value($values, $entry_request->entrytype, $USER->get('id'));
                $aid = isset($result['error']) ? 0 : $result['artefactid'];
                break;
        }
        if ($aid) {
            $importer->add_artefactmapping($entry_request->entryid, $aid);
            return $aid;
        }
        return null;
    }

    /**
     * Render import entry requests for Mahara user's resume fields
     * @param PluginImportLeap $importer
     * @return HTML code for displaying user's resume fields and choosing how to import them
     */
    public static function render_import_entry_requests(PluginImportLeap $importer) {
        safe_require('artefact', 'resume');
        $importid = $importer->get('importertransport')->get('importid');
        $resumefields = array(
            'introduction' => array(
                'legend' => get_string('introduction', 'artefact.resume'),
                'fields' => array('coverletter', 'personalinformation'),
            ),
            'employment' => array(
                'legend' => get_string('educationandemployment', 'artefact.resume'),
                'fields' => array('educationhistory', 'employmenthistory'),
            ),
            'achievements' => array(
                'legend' => get_string('achievements', 'artefact.resume'),
                'fields' => array('certification', 'book', 'membership'),
            ),
            'goals' => array(
                'legend' => get_string('goals', 'artefact.resume'),
                'fields' => array('personalgoal', 'academicgoal', 'careergoal'),
            ),
            'skills' => array(
                'legend' => get_string('skills', 'artefact.resume'),
                'fields' => array('personalskill', 'academicskill', 'workskill'),
            ),
            'interests' => array(
                'legend' => get_string('interests', 'artefact.resume'),
                'fields' => array('interests'),
            ),
        );
        $resumecompositefields = array('book', 'certificattion', 'educationhistory', 'employmenthistory', 'membership');
        // Get import entry requests for Mahara resume fields
        $resumegroups = array();
        foreach ($resumefields as $gr_key => $group) {
            $resumegroup = array();
            $resumegroup['id'] = $gr_key;
            $resumegroup['legend'] = $group['legend'];
            foreach ($group['fields'] as $f) {
                if ($iers = get_records_select_array('import_entry_requests', 'importid = ? AND entrytype = ?', array($importid, $f))) {
                    $resumefieldvalues = array();
                    $fieldname = ($f == 'url') ? get_string('bookurl', 'artefact.resume') : get_string($f, 'artefact.resume');
                    foreach ($iers as $ier) {
                        $resumefieldvalue = unserialize($ier->entrycontent);
                        $classname = generate_artefact_class_name($f);
                        $resumefieldvalue['id'] = $ier->id;
                        $resumefieldvalue['decision'] = $ier->decision;
                        $resumefieldvalue['html'] = $classname::render_import_entry_request($resumefieldvalue);
                        if (is_string($ier->duplicateditemids)) {
                            $ier->duplicateditemids = unserialize($ier->duplicateditemids);
                        }
                        if (is_string($ier->existingitemids)) {
                            $ier->existingitemids = unserialize($ier->existingitemids);
                        }
                        $resumefieldvalue['disabled'][PluginImport::DECISION_IGNORE] = false;
                        if (!empty($ier->duplicateditemids)) {
                            $duplicated_rfield = artefact_instance_from_id($ier->duplicateditemids[0]);
                            $resumefieldvalue['duplicateditem']['id'] = $duplicated_rfield->get('id');
                            $res = $duplicated_rfield->render_self(array());
                            $resumefieldvalue['duplicateditem']['html'] = $res['html'];
                            $resumefieldvalue['disabled'][PluginImport::DECISION_ADDNEW] = true;
                            $resumefieldvalue['disabled'][PluginImport::DECISION_APPEND] = true;
                            $resumefieldvalue['disabled'][PluginImport::DECISION_REPLACE] = true;
                        }
                        else if (!empty($ier->existingitemids)) {
                            if (in_array($f, $resumecompositefields)) {
                                $existing_rfield = artefact_instance_from_id($ier->existingitemids[0]);
                                $res = $existing_rfield->render_self(array());
                                $resumefieldvalue['existingitems'][] = array(
                                    'id'    => $existing_rfield->get('id'),
                                    'html'  => $res['html'],
                                );
                            }
                            else {
                                foreach ($ier->existingitemids as $id) {
                                    $existing_rfield = artefact_instance_from_id($id);
                                    $res = $existing_rfield->render_self(array());
                                    $resumefieldvalue['existingitems'][] = array(
                                        'id'    => $existing_rfield->get('id'),
                                        'html'  => $res['html'],
                                    );
                                }
                            }
                            // Composite artefacts: There's just one "artefact" and then multiple entries
                            // in an associated artefact_resume_* table. So, you want to disable everything
                            // except for "Append"
                            if (in_array($ier->entrytype, ArtefactTypeResumeComposite::get_composite_artefact_types())) {
                                $resumefieldvalue['disabled'][PluginImport::DECISION_ADDNEW] = true;
                                $resumefieldvalue['disabled'][PluginImport::DECISION_APPEND] = false;
                                $resumefieldvalue['disabled'][PluginImport::DECISION_REPLACE] = true;
                            }
                            else {
                                $is_singular = call_static_method(generate_artefact_class_name($ier->entrytype), 'is_singular');
                                $resumefieldvalue['disabled'][PluginImport::DECISION_ADDNEW] = $is_singular;
                                $resumefieldvalue['disabled'][PluginImport::DECISION_APPEND] = !$is_singular;
                                $resumefieldvalue['disabled'][PluginImport::DECISION_REPLACE] = !$is_singular;
                            }
                        }
                        else {
                            if (in_array($ier->entrytype, ArtefactTypeResumeComposite::get_composite_artefact_types())) {
                                $resumefieldvalue['disabled'][PluginImport::DECISION_ADDNEW] = true;
                                $resumefieldvalue['disabled'][PluginImport::DECISION_APPEND] = false;
                                $resumefieldvalue['disabled'][PluginImport::DECISION_REPLACE] = true;
                            }
                            else {
                                $resumefieldvalue['disabled'][PluginImport::DECISION_ADDNEW] = false;
                                $resumefieldvalue['disabled'][PluginImport::DECISION_APPEND] = true;
                                $resumefieldvalue['disabled'][PluginImport::DECISION_REPLACE] = true;
                            }
                        }
                        $resumefieldvalues[] = $resumefieldvalue;
                    }
                    $resumegroup['fields'][$fieldname] = $resumefieldvalues;
                }
            }
            $resumegroups[] = $resumegroup;
        }
        $smarty = smarty_core();
        $smarty->assign('displaydecisions', $importer->get('displaydecisions'));
        $smarty->assign('resumegroups', $resumegroups);
        return $smarty->fetch('artefact:resume:import/resumefields.tpl');
    }

    /**
     * Creates an artefact in the manner required to overwrite existing resume
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
     * Attach files to their resume composite
     * TODO: this is experimental and is not actually working correctly.
     * This may be due to the structure of the export for resume items or
     * due to this import function being wrong or both.
     */
    public static function setup_relationships(SimpleXMLElement $entry, PluginImportLeap $importer, $strategy, array $otherentries) {
        $newartefactmapping = array();
        $class = false;
        switch ($strategy) {
        case self::STRATEGY_IMPORT_AS_ENTRY:
        case self::STRATEGY_IMPORT_AS_ABILITY:
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
            // do stuff here
            break;
        case self::STRATEGY_IMPORT_AS_ACHIEVEMENT:
            $class = 'ArtefactTypeCertification';
            break;
        case self::STRATEGY_IMPORT_AS_EMPLOYMENT:
            $class = 'ArtefactTypeEmploymenthistory';
            break;
        case self::STRATEGY_IMPORT_AS_BOOK:
            $class = 'ArtefactTypeBook';
            break;
        case self::STRATEGY_IMPORT_AS_EDUCATION:
            $class = 'ArtefactTypeEducationhistory';
            break;
        case self::STRATEGY_IMPORT_AS_MEMBERSHIP:
            $class = 'ArtefactTypeMembership';
            break;
        case self::STRATEGY_IMPORT_AS_SELECTION:
            // This space intentionally left blank
            break;
        default:
            throw new ImportException($importer, 'TODO: get_string: unknown strategy chosen for importing entry');
        }

        foreach ($otherentries as $entryid) {
            $compositeentry = $importer->get_entry_by_id($entryid);
            $composite = null;
            foreach ($compositeentry->link as $compositelink) {
                if (class_exists($class)) {
                    if (!$composite) {
                        $artefactids = $importer->get_artefactids_imported_by_entryid((string)$compositeentry->id);
                        $composite = new $class($artefactids[0],array('owner' => $importer->get('usr')));
                    }
                    if ($id = $importer->create_attachment($entry, $compositelink, $composite)) {
                        $newartefactmapping[$link['href']][] = $id;
                    }
                    if ($composite) {
                        $composite->commit();
                    }
                }
            }
        }
        return $newartefactmapping;
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
