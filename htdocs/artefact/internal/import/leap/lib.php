<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-internal-import-leap
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

/**
 * Implements Leap2A import of profile related entries into Mahara
 *
 * For more information about Leap profile importing, see:
 * https://wiki.mahara.org/wiki/Developer_Area/Import//Export/LEAP_Import/Internal_Artefact_Plugin
 *
 * TODO:
 * - how do we want to handle potentially overwriting data?
 * - Address for person (leap:spatial) - our export might have to be modified
 *   to output them in a more "correct" order for other systems
 * - Validate the values of profile fields coming in? Especially email
 *
 * - Refactor the bunches of duplicate code
 */
class LeapImportInternal extends LeapImportArtefactPlugin {

    private static $personcontentblank = null;

    /**
     * For grabbing entries representing profile data that can't be exported as
     * persondata
     */
    const STRATEGY_IMPORT_AS_PROFILE_FIELD = 1;

    /**
     * Entries with common_item:Personalstatement are introductions
     */
    const STRATEGY_IMPORT_AS_INTRODUCTION = 2;

    /**
     * Lookup table for some of the persondata fields.
     *
     * Info based on the table here:
     * http://wiki.cetis.ac.uk/2009-03/Leap2A_personal_data#Persondata_fields
     *
     * The fields here that are not listed there are either not supported, or
     * imported a different way by this plugin. For example, name related
     * fields are handled by import_namedata().
     */
    private static $persondatafields = array(
        'country' => array(
            // TODO: we use leap:country inside leap:spatial, but should fall back to this
            'unique' => true,
        ),
        'website' => array(
            'helper_method' => true,
        ),
        'id' => array(
            'helper_method' => true,
        ),
        'email' => array(
            // TODO: validation
            'helper_method' => true,
        ),
        'homephone' => array(
            'mahara_fieldname' => 'homenumber',
        ),
        'workphone' => array(
            'mahara_fieldname' => 'businessnumber',
        ),
        'mobile' => array(
            'mahara_fieldname' => 'mobilenumber',
        ),
        'fax' => array(
            'mahara_fieldname' => 'faxnumber',
        ),
        'other' => array(
            'helper_method' => true,
        ),
    );

    /**
     * This list taken from
     * http://wiki.cetis.ac.uk/2009-03/Leap2A_personal_data#Service_abbreviations
     *
     * We are only including a list of the ones we can import, so some from the
     * list will be missing
     */
    private static $services = array(
        array(
            'service' => 'icq',
            'uri'     => 'http://www.icq.com/',
            'artefact_type' => 'Icqnumber',
        ),
        array(
            'service' => 'aim',
            'uri'     => 'http://www.aim.com/',
            'artefact_type' => 'Aimscreenname',
        ),
        array(
            'service' => 'yahoo',
            'uri'     => 'http://www.yahoo.com/',
            'artefact_type' => 'Yahoochat',
        ),
        array(
            'service' => 'skype',
            'uri'     => 'http://www.skype.com/',
            'artefact_type' => 'Skypeusername',
        ),
        array(
            'service' => 'jabber',
            'uri'     => 'http://www.jabber.org/',
            'artefact_type' => 'Jabberusername',
        ),
    );

    /**
     * The profile importer tries to import raw profile fields using the
     * strategy mechanism, but most of the useful profile information is stored
     * in the person entry corresponding to the author.
     *
     * The persondata entry is not actually imported using a strategy, because
     * we need to be able to import basic data from the <author> element if
     * it's not present too. So all the person importing is handled in import_author_data()
     */
    public static function get_import_strategies_for_entry(SimpleXMLElement $entry, PluginImportLeap $importer) {

        if (self::$personcontentblank === null) {
            self::$personcontentblank = true;
            if ($persondataid = $importer->get('persondataid')) {
                self::$personcontentblank = !(string)$importer->get_entry_by_id($persondataid)->content;
            }
        }

        $isentry = PluginImportLeap::is_rdf_type($entry, $importer, 'entry');

        if ($isentry && self::$personcontentblank
            && PluginImportLeap::is_correct_category_scheme($entry, $importer, 'common_item', 'Personalstatement')) {
            return array(array(
                'strategy' => self::STRATEGY_IMPORT_AS_INTRODUCTION,
                'score'    => 100,
                'other_required_entries' => array(),
            ));
        }

        // If it's a raw entry with the right mahara:plugin and mahara:type
        // we should be able to import it
        $correctplugintype = count($entry->xpath('mahara:artefactplugin[@mahara:plugin="internal"]')) == 1;
        if ($isentry && $correctplugintype) {
            return array(array(
                'strategy' => self::STRATEGY_IMPORT_AS_PROFILE_FIELD,
                'score'    => 100,
                'other_required_entries' => array(),
            ));
        }

        return array();
    }

    /**
     * Find and update duplicates of imported data about the feed author.
     * TODO: Refactor this to combine it with import_author_data()
     *
     * @param PluginImportLeap $importer The importer
     * @param string $persondataid       The ID of the person entry corresponding
     *                                   to the author, if there is one
     * @return updated DB table 'import_authordata_requests'
     */
    public static function add_import_entry_request_author_data(PluginImportLeap $importer, $persondataid) {

        $namespaces = $importer->get_namespaces();
        $ns = $namespaces[$importer->get_leap2a_namespace()];
        if ($persondataid) {
            // Grab all the leap:persondata elements and import them
            $person = $importer->get_entry_by_id($persondataid);
            $importid = $importer->get('importertransport')->get('importid');
            $ownerid = $importer->get('usr');

            // The introduction comes from the entry content
            if (!self::$personcontentblank) {
                PluginImportLeap::add_import_entry_request($importer->get('importertransport')->get('importid'), $persondataid, self::STRATEGY_IMPORT_AS_PROFILE_FIELD, 'internal', array(
                    'owner'   => $importer->get('usr'),
                    'type'    => 'introduction',
                    'content' => array(
                        'title'       => PluginImportLeap::get_entry_content($person, $importer),
                    ),
                ));
            }

            // Most of the rest of the profile data comes from leap:persondata elements
            $persondata = $person->xpath($ns.':persondata');
            foreach ($persondata as $item) {
                $leapattributes = PluginImportLeap::get_attributes($item, $importer->get_leap2a_namespace());

                if (isset($leapattributes['field'])) {
                    self::add_import_entry_request_persondata($importer, $persondataid, $item, $leapattributes);
                }
            }

            // The information about someone's name is much more comprehensive
            // in Leap than what Mahara has, so we have to piece it together
            self::add_import_entry_request_namedata($importer, $persondataid, $persondata);

            // People can have address info associated with them
            $addressdata = $person->xpath($ns.':spatial');
            if (is_array($addressdata) && count($addressdata) == 1) {
                self::add_import_entry_request_addressdata($importer, $persondataid, $addressdata[0]);
            }
        }
        else {
            $author = $importer->get('xml')->xpath('//a:feed/a:author[1]');
            $author = $author[0];

            if (!isset($author->name)) {
                throw new ImportException($importer, 'TODO: get_string: <author> must include <name> - http://wiki.cetis.ac.uk/2009-03/Leap2A_relationships#Author');
            }

            $name = (string)$author->name;
            if (false !== strpos($name, ' ')) {
                list($firstname, $lastname) = explode(' ', $name, 2);
                PluginImportLeap::add_import_entry_request($importer->get('importertransport')->get('importid'), $persondataid, self::STRATEGY_IMPORT_AS_PROFILE_FIELD, 'internal', array(
                    'owner'   => $importer->get('usr'),
                    'type'    => 'firstname',
                    'content' => array(
                        'title'       => trim($firstname),
                    ),
                ));
                PluginImportLeap::add_import_entry_request($importer->get('importertransport')->get('importid'), $persondataid, self::STRATEGY_IMPORT_AS_PROFILE_FIELD, 'internal', array(
                    'owner'   => $importer->get('usr'),
                    'type'    => 'lastname',
                    'content' => array(
                        'title'       => trim($lastname),
                    ),
                ));
            }
            else {
                // Blatant assumption that the <name> is a first name
                PluginImportLeap::add_import_entry_request($importer->get('importertransport')->get('importid'), $persondataid, self::STRATEGY_IMPORT_AS_PROFILE_FIELD, 'internal', array(
                    'owner'   => $importer->get('usr'),
                    'type'    => 'firstname',
                    'content' => array(
                        'title'       => trim($name),
                    ),
                ));
            }

            if (isset($author->email)) {
                PluginImportLeap::add_import_entry_request($importer->get('importertransport')->get('importid'), $persondataid, self::STRATEGY_IMPORT_AS_PROFILE_FIELD, 'internal', array(
                    'owner'   => $importer->get('usr'),
                    'type'    => 'email',
                    'content' => array(
                        'title'       => (string)$author->email,
                    ),
                ));
            }

            if (isset($author->uri)) {
                $uri = (string)$author->uri;
                if (preg_match('#^https?://#', $uri)) {
                    PluginImportLeap::add_import_entry_request($importer->get('importertransport')->get('importid'), $persondataid, self::STRATEGY_IMPORT_AS_PROFILE_FIELD, 'internal', array(
                        'owner'   => $importer->get('usr'),
                        'type'    => 'officialwebsite',
                        'content' => array(
                            'title'       => (string)$author->uri,
                        ),
                    ));
                }
            }
        }
    }


    /**
     * get data of the imported entry using strategy
     *
     * @param SimpleXMLElement $entry
     * @param PluginImportLeap $importer
     * @param int              $strategy
     * @param array            $otherentries
     */
    protected static function get_entry_data_using_strategy(SimpleXMLElement $entry, PluginImportLeap $importer, $strategy, array $otherentries) {
        switch ($strategy) {
            case self::STRATEGY_IMPORT_AS_PROFILE_FIELD:
                // Based on the mahara:type, we might be able to import it as
                // something useful - otherwise, there is nothing we can do. The
                // entry already claimed it was mahara:plugin="internal", so it's
                // perfectly fine for us to not import it if we don't recognise it
                $types = array(
                    'occupation',
                    'industry',
                    'socialprofile',
                );
                $typexpath = join('" or @mahara:type="', $types);
                $artefactpluginelement = $entry->xpath('mahara:artefactplugin[@mahara:type="' . $typexpath . '"]');
                if (count($artefactpluginelement) == 1) {
                    $artefactpluginelement = $artefactpluginelement[0];

                    $maharaattributes = PluginImportLeap::get_attributes($artefactpluginelement, PluginImportLeap::NS_MAHARA);
                    if (isset($maharaattributes['type']) && in_array($maharaattributes['type'], $types)) {
                        $type = $maharaattributes['type'];
                        $title = PluginImportLeap::get_entry_content($entry, $importer);
                        break;
                    }
                }

                // Try importing as a Note
                try {
                    $content = PluginImportLeap::get_entry_content($entry, $importer);
                }
                catch (SystemException $e) {
                    $content = false;
                }
                if ($content) {
                    return array (
                            'owner' => $importer->get('usr'),
                            'type' => 'html',
                            'content' => array (
                                    'title' => isset($entry->title) ? (string) $entry->title : '',
                                    'ctime' => (string)$entry->published,
                                    'mtime' => (string)$entry->updated,
                                    'description' => $content
                            )
                    );
                    // TODO: Make this work in interactive import
//                     if (isset($entry->link)) {
//                         foreach($entry->link as $link) {
//                             if ($id = $importer->create_attachment($entry, $link, $note)) {
//                                 $artefactmapping[$link['href']][] = $id;
//                             }
//                         }
//                         $note->commit();
//                     }
                }
                break;
            case self::STRATEGY_IMPORT_AS_INTRODUCTION:
                // The introduction comes from the entry content
                $type = 'introduction';
                $title = PluginImportLeap::get_entry_content($entry, $importer);
                break;
            default:
                throw new ImportException($importer, 'TODO: get_string: unknown strategy chosen for importing entry');
        }
        return isset($type) ?
            array(
                'owner'   => $importer->get('usr'),
                'type'    => $type,
                'content' => array(
                    'title'    => $title,
                ),
            )
            : null;
    }

    /**
     * find and update existing artefacts of the imported entry
     *
     * @param SimpleXMLElement $entry
     * @return updated DB table 'import_entry_requests'
     */
    public static function add_import_entry_request_using_strategy(SimpleXMLElement $entry, PluginImportLeap $importer, $strategy, array $otherentries) {
        $entrydata = self::get_entry_data_using_strategy($entry, $importer, $strategy, $otherentries);
        // Add individual socialprofile descriptions...
        if ($entrydata['type'] == 'socialprofile') {
             $entrydata['content']['description'] = (string)$entry->summary;
        }
        if (!empty($entrydata)) {
            PluginImportLeap::add_import_entry_request($importer->get('importertransport')->get('importid'), (string)$entry->id, $strategy, 'internal', $entrydata);
        }
    }

    /**
     * Import from entry requests for Mahara user profile fields
     *
     * @param PluginImportLeap $importer
     * @return updated DB
     * @throw    ImportException
     */
    public static function import_from_requests(PluginImportLeap $importer) {
        if ($entry_requests = get_records_select_array('import_entry_requests', 'importid = ? AND plugin = ?', array($importer->get('importertransport')->get('importid'), 'internal'))) {
            foreach ($entry_requests as $entry_request) {
                self::create_artefact_from_request($importer, $entry_request);
            }
        }
    }

    public static function import_using_strategy(SimpleXMLElement $entry, PluginImportLeap $importer, $strategy, array $otherentries) {
        $artefactmapping = array();
        $entrydata = self::get_entry_data_using_strategy($entry, $importer, $strategy, $otherentries);
        if (!empty($entrydata)) {
            switch ($entrydata['type']) {
                case 'introduction':
                    $introduction = new ArtefactTypeIntroduction(0, array('owner' => $importer->get('usr')));
                    $introduction->set('title', 'introduction');
                    $introduction->set('description', $entrydata['content']['title']);
                    $introduction->commit();
                    $artefactmapping[(string)$entry->id] = array($introduction->get('id'));
                    break;
                case 'html':
                    $note = new ArtefactTypeHtml();
                    $note->set('title', $entrydata['content']['title']);
                    $note->set('description', $entrydata['content']['description']);
                    $note->set('ctime', strtotime($entrydata['content']['ctime']));
                    $note->set('mtime', strtotime($entrydata['content']['mtime']));
                    $note->set('owner', $entrydata['owner']);
                    $note->commit();
                    $artefactmapping[(string)$entry->id] = array($note->get('id'));

                        // Check for note's attachments
                        if (isset($entry->link)) {
                            foreach($entry->link as $link) {
                                if ($id = $importer->create_attachment($entry, $link, $note)) {
                                    $artefactmapping[$link['href']][] = $id;
                                }
                            }
                            $note->commit();
                        }
                    break;
                default:
                    $artefactmapping[(string)$entry->id] = array(self::create_artefact($importer, $entrydata['type'], $entrydata['content']['title']));
                    break;
            }
        }
        return $artefactmapping;
    }

    /**
     * Import data about the feed author.
     *
     * If we have a persondata element for them, we can import lots of
     * different information about them into Mahara's profile section.
     * Otherwise, we can only import some very basic information from the
     * <author> element.
     *
     * TODO: Refactor this to combine it with add_import_entry_request_author_data()
     *
     * @param PluginImportLeap $importer The importer
     * @param string $persondataid       The ID of the person entry corresponding
     *                                   to the author, if there is one
     */
    public static function import_author_data(PluginImportLeap $importer, $persondataid) {
        $namespaces = $importer->get_namespaces();
        $ns = $namespaces[$importer->get_leap2a_namespace()];
        if ($persondataid) {
            // Grab all the leap:persondata elements and import them
            $person = $importer->get_entry_by_id($persondataid);

            // The introduction comes from the entry content
            if (!self::$personcontentblank) {
                $introduction = new ArtefactTypeIntroduction(0, array('owner' => $importer->get('usr')));
                $introduction->set('title', PluginImportLeap::get_entry_content($person, $importer));
                $introduction->commit();
            }

            // Most of the rest of the profile data comes from leap:persondata elements
            $persondata = $person->xpath($ns.':persondata');
            foreach ($persondata as $item) {
                $leapattributes = PluginImportLeap::get_attributes($item, $importer->get_leap2a_namespace());

                if (isset($leapattributes['field'])) {
                    self::import_persondata($importer, $item, $leapattributes);
                }
                else {
                    // 'Field' is required
                    // http://wiki.cetis.ac.uk/2009-03/Leap2A_personal_data#field
                    $importer->trace('WARNING: persondata element did not have leap:field attribute');
                    continue;
                }
            }

            // The information about someone's name is much more comprehensive
            // in Leap than what Mahara has, so we have to piece it together
            self::import_namedata($importer, $persondata);

            // People can have address info associated with them
            $addressdata = $person->xpath($ns.':spatial');
            if (is_array($addressdata) && count($addressdata) == 1) {
                self::import_addressdata($importer, $addressdata[0]);
            }

            // Set default profile icon. We look at rel="related" links on this
            // element, and take the first one that we turned into a profile
            // icon to be the default. In future versions of the spec, we may use
            // a "depicts" type relationship to explicitly identify them.
            foreach ($person->link as $link) {
                if ($importer->curie_equals($link['rel'], '', 'related') && isset($link['href'])) {
                    $artefactids = $importer->get_artefactids_imported_by_entryid((string)$link['href']);
                    if (count($artefactids) == 1 && $potentialicon = artefact_instance_from_id($artefactids[0])) {
                        if ($potentialicon->get('artefacttype') == 'profileicon') {
                            $importer->get('usrobj')->profileicon = $potentialicon->get('id');
                            $importer->get('usrobj')->commit();
                            // The first one we find in the export is the profile icon
                            break;
                        }
                    }
                }
            }
        }
        else {
            $author = $importer->get('xml')->xpath('//a:feed/a:author[1]');
            $author = $author[0];

            if (!isset($author->name)) {
                throw new ImportException($importer, 'TODO: get_string: <author> must include <name> - http://wiki.cetis.ac.uk/2009-03/Leap2A_relationships#Author');
            }

            $name = (string)$author->name;
            if (false !== strpos($name, ' ')) {
                list($firstname, $lastname) = explode(' ', $name, 2);
                self::create_artefact($importer, 'firstname', trim($firstname));
                self::create_artefact($importer, 'lastname', trim($lastname));
            }
            else {
                // Blatant assumtion that the <name> is a first name
                self::create_artefact($importer, 'firstname', trim($name));
            }

            if (isset($author->email)) {
                self::create_artefact($importer, 'email', (string)$author->email);
            }

            if (isset($author->uri)) {
                $uri = (string)$author->uri;
                if (preg_match('#^https?://#', $uri)) {
                    self::create_artefact($importer, 'officialwebsite', (string)$author->uri);
                }
            }
        }
    }

    /**
     * Find and update duplicates with imported persondata
     * TODO: Refactor this to combine it with import_persondata()
     *  NB: This function should only be called when importing one single Leap2A file
     */
    private static function add_import_entry_request_persondata(PluginImportLeap $importer, $persondataid, SimpleXMLElement $item, array $leapattributes) {
        $field = $leapattributes['field'];

        if (isset(self::$persondatafields[$field]['mahara_fieldname'])) {
            static $seen = array();
            if (isset($seen[$field])) {
                return;
            }
            $seen[$field] = true;

            PluginImportLeap::add_import_entry_request($importer->get('importertransport')->get('importid'), $persondataid, self::STRATEGY_IMPORT_AS_PROFILE_FIELD, 'internal', array(
                'owner'   => $importer->get('usr'),
                'type'    => self::$persondatafields[$field]['mahara_fieldname'],
                'content' => array(
                    'title'       => (string)$item,
                ),
            ));
            return;
        }

        if (!empty(self::$persondatafields[$field]['helper_method'])) {
            $method = 'add_import_entry_request_persondata_' . $field;
            self::$method($importer, $persondataid, $item, $leapattributes);
        }
    }

    /**
     * Attempts to import a persondata element
     * TODO: Refactor this to combine it with add_import_entry_request_persondata()
     */
    private static function import_persondata(PluginImportLeap $importer, SimpleXMLElement $item, array $leapattributes) {
        $field = $leapattributes['field'];

        if (isset(self::$persondatafields[$field]['mahara_fieldname'])) {
            // Basic case - imports straight into a Mahara field. Mahara only
            // allows you to keep one of each of these values, so we throw away
            // any more if they're seen, on the assumption that they are
            // ordered from most to least important:
            // http://wiki.cetis.ac.uk/2009-03/Leap2A_personal_data#Ordering
            static $seen = array();
            if (isset($seen[$field])) {
                return;
            }
            $seen[$field] = true;

            self::create_artefact($importer, self::$persondatafields[$field]['mahara_fieldname'], (string)$item);
            return;
        }

        if (!empty(self::$persondatafields[$field]['helper_method'])) {
            $method = 'import_persondata_' . $field;
            self::$method($importer, $item, $leapattributes);
        }
    }

    /**
     * Find duplicates with imported persondata with leap:field="id"
     * TODO: Refactor this to combine it with import_persondata_id()
     */
    private static function add_import_entry_request_persondata_id(PluginImportLeap $importer, $persondataid, SimpleXMLElement $item, array $leapattributes) {
        if ($leapattributes['field'] == 'id' && !isset($leapattributes['service'])) {
            // 'id' must have a service set
            // http://wiki.cetis.ac.uk/2009-03/Leap2A_personal_data#service
            throw new ImportException($importer, "TODO: get_string: persondata field was 'id' but had no service set");
        }

        if (in_array($leapattributes['service'], ArtefactTypeSocialprofile::$socialnetworks)) {
            PluginImportLeap::add_import_entry_request($importer->get('importertransport')->get('importid'), $persondataid, self::STRATEGY_IMPORT_AS_PROFILE_FIELD, 'internal', array(
                'owner'   => $importer->get('usr'),
                'type'    => 'socialprofile',
                'content' => array(
                    'title'       => (string)$item,
                    'description' => (isset($leapattributes['label'])) ? (string)$leapattributes['label'] : null,
                    'note'        => $leapattributes['service'],
                ),
            ));
            return;
        }
    }

    /**
     * Attempts to import a persondata field with leap:field="id"
     * TODO: Refactor this to combine it with add_import_entry_request_persondata_id()
     */
    private static function import_persondata_id(PluginImportLeap $importer, SimpleXMLElement $item, array $leapattributes) {
        if ($leapattributes['field'] == 'id' && !isset($leapattributes['service'])) {
            // 'id' must have a service set
            // http://wiki.cetis.ac.uk/2009-03/Leap2A_personal_data#service
            throw new ImportException($importer, "TODO: get_string: persondata field was 'id' but had no service set");
        }

        if (in_array($leapattributes['service'], ArtefactTypeSocialprofile::$socialnetworks)) {
            // we have the old messaging profiles so we need to adjust them to allow for importing
            self::create_artefact($importer, 'socialprofile', (string)$item, array(
                'description' => (!empty($leapattributes['label']) ? (string)$leapattributes['label'] : null),
                'note'        => (!empty($leapattributes['service']) ? (string)$leapattributes['service'] : 'website'),
                ));
            return;
        }

        // TODO what do we do here?
        $importer->trace(" * Unrecognised service " . $leapattributes['service'] . ", ignored");
    }

    /**
     * Find duplicates with imported persondata with leap:field="email"
     * TODO: refactor this to combine it with import_persondata_email()
     */
    private static function add_import_entry_request_persondata_email(PluginImportLeap $importer, $persondataid, SimpleXMLElement $item, array $leapattributes) {
        PluginImportLeap::add_import_entry_request($importer->get('importertransport')->get('importid'), $persondataid, self::STRATEGY_IMPORT_AS_PROFILE_FIELD, 'internal', array(
            'owner'   => $importer->get('usr'),
            'type'    => 'email',
            'content' => array(
                'title'       => (string)$item,
            ),
        ));
    }

    /**
     * Attempts to import a persondata field with leap:field="email"
     * TODO: Refactor this to combine it with add_import_entry_request_persondata_email()
     */
    private static function import_persondata_email(PluginImportLeap $importer, SimpleXMLElement $item, array $leapattributes) {
        static $firstdone = false;
        static $seen = array();

        if (count($seen) >= 5) {
            $importer->trace("WARNING: users cannot have more than 5 e-mail addresses");
            return;
        }

        $email = (string)$item;

        if (!in_array($email, $seen)) {
            $id = self::create_artefact($importer, 'email', $email);
            if (!$firstdone) {
                // The first email address will be primary
                update_record('artefact_internal_profile_email', (object)array(
                    'principal' => 1,
                ), (object)array(
                    'artefact'  => $id,
                ));
                $importer->get('usrobj')->email = $email;
                $importer->get('usrobj')->commit();
                $firstdone = true;
            }
        }
        else {
            $importer->trace("WARNING: export file had the same e-mail address listed more than once ($email)");
        }
    }

    /**
     * Find duplicates with imported persondata with leap:field="website"
     * TODO: Refactor this to combine it with import_persondata_website()
     */
    private static function add_import_entry_request_persondata_website(PluginImportLeap $importer, $persondataid, SimpleXMLElement $item, array $leapattributes) {
        // We've been given a 'website' field, but Mahara can have many profile
        // fields for website (including via socialprofile). So we need to examine
        // it deeper to establish which field it should import into
        $maharaattributes = PluginImportLeap::get_attributes($item, PluginImportLeap::NS_MAHARA);

        if (isset($maharaattributes['artefactplugin'])
            && isset($maharaattributes['artefacttype'])
            && $maharaattributes['artefactplugin'] == 'internal') {
            switch ($maharaattributes['artefacttype']) {
                case 'blogaddress':
                case 'personalwebsite':
                case 'officialwebsite':
                    PluginImportLeap::add_import_entry_request($importer->get('importertransport')->get('importid'), $persondataid, self::STRATEGY_IMPORT_AS_PROFILE_FIELD, 'internal', array(
                        'owner'   => $importer->get('usr'),
                        'type'    => $maharaattributes['artefacttype'],
                        'content' => array(
                            'title'       => (string)$item,
                        ),
                    ));
                    return;
                case 'socialprofile':
                    PluginImportLeap::add_import_entry_request($importer->get('importertransport')->get('importid'), $persondataid, self::STRATEGY_IMPORT_AS_PROFILE_FIELD, 'internal', array(
                        'owner'   => $importer->get('usr'),
                        'type'    => $maharaattributes['artefacttype'],
                        'content' => array(
                            'title'       => (string)$item,
                            'description' => (isset($leapattributes['label'])) ? (string)$leapattributes['label'] : null,
                            'note'        => (!empty($leapattributes['service']) ? $leapattributes['service'] : 'website'),
                        ),
                    ));
                    return;
            }
        }

        // No mahara: namespaced attributes to help us :(
        // For now, just import as officialwebsite. Later, we might import into
        // the other fields as well based on the order we encounter them in the
        // import file
        static $seen = false;
        if (!$seen) {
            $seen = true;
            PluginImportLeap::add_import_entry_request($importer->get('importertransport')->get('importid'), $persondataid, self::STRATEGY_IMPORT_AS_PROFILE_FIELD, 'internal', array(
                'owner'   => $importer->get('usr'),
                'type'    => 'officialwebsite',
                'content' => array(
                    'title'       => (string)$item,
                ),
            ));
        }
    }

    /**
     * Attempts to import a persondata field with leap:field="website"
     * TODO: Refactor this to combine it with add_import_entry_request_persondata_website()
     */
    private static function import_persondata_website(PluginImportLeap $importer, SimpleXMLElement $item, array $leapattributes) {
        // We've been given a 'website' field, but Mahara can have many profile
        // fields for website (including via socialprofile). So we need to examine
        // it deeper to establish which field it should import into
        $maharaattributes = PluginImportLeap::get_attributes($item, PluginImportLeap::NS_MAHARA);

        if (isset($maharaattributes['artefactplugin'])
            && isset($maharaattributes['artefacttype'])
            && $maharaattributes['artefactplugin'] == 'internal') {
            switch ($maharaattributes['artefacttype']) {
                case 'blogaddress':
                case 'personalwebsite':
                case 'officialwebsite':
                    self::create_artefact($importer, $maharaattributes['artefacttype'], (string)$item);
                    return;
                case 'socialprofile':
                    self::create_artefact($importer, $maharaattributes['artefacttype'], (string)$item, array(
                            'description' => (!empty($leapattributes['label']) ? (string)$leapattributes['label'] : null),
                            'note'        => (!empty($leapattributes['service']) ? (string)$leapattributes['service'] : 'website'),
                        )
                    );
                    return;
            }
        }

        // No mahara: namespaced attributes to help us :(
        // For now, just import as officialwebsite. Later, we might import into
        // the other fields as well based on the order we encounter them in the
        // import file
        static $seen = false;
        if (!$seen) {
            $seen = true;
            self::create_artefact($importer, 'officialwebsite', (string)$item);
        }
    }

    /**
     * Find duplicates with imported persondata with leap:field="other"
     * TODO: Refactor this to combine it with import_persondata_other()
     */
    private static function add_import_entry_request_persondata_other(PluginImportLeap $importer, $persondataid, SimpleXMLElement $item, array $leapattributes) {
        // The only 'other' field we can actually import is one we recognise as
        // 'student ID'
        $maharaattributes = PluginImportLeap::get_attributes($item, PluginImportLeap::NS_MAHARA);

        if (isset($maharaattributes['artefactplugin'])
            && isset($maharaattributes['artefacttype'])
            && $maharaattributes['artefactplugin'] == 'internal') {
            switch ($maharaattributes['artefacttype']) {
            case 'studentid':
                PluginImportLeap::add_import_entry_request($importer->get('importertransport')->get('importid'), $persondataid, self::STRATEGY_IMPORT_AS_PROFILE_FIELD, 'internal', array(
                    'owner'   => $importer->get('usr'),
                    'type'    => 'studentid',
                    'content' => array(
                        'title'       => (string)$item,
                    ),
                ));
                return;
            }
        }
    }

    /**
     * Attempts to import a persondata field with leap:field="other"
     * TODO: Refactor this to combine it with add_import_entry_request_persondata_other()
     */
    private static function import_persondata_other(PluginImportLeap $importer, SimpleXMLElement $item, array $leapattributes) {
        // The only 'other' field we can actually import is one we recognise as
        // 'student ID'
        $maharaattributes = PluginImportLeap::get_attributes($item, PluginImportLeap::NS_MAHARA);

        if (isset($maharaattributes['artefactplugin'])
            && isset($maharaattributes['artefacttype'])
            && $maharaattributes['artefactplugin'] == 'internal') {
            switch ($maharaattributes['artefacttype']) {
            case 'studentid':
                self::create_artefact($importer, $maharaattributes['artefacttype'], (string)$item);
                $importer->get('usrobj')->studentid = (string)$item;
                $importer->get('usrobj')->commit();
                return;
            }
        }

        $importer->trace("NOTICE: skipped persondata 'other' field");
    }

    /**
     * Imports info from a leap:spatial element as a user's address-related
     * profile fields
     */
    private static function get_addressfields(PluginImportLeap $importer, SimpleXMLElement $addressdata) {
        // TODO: this xpath doesn't respect the namespace prefix - we should
        // look it up from $importer->namespaces[NS_LEAP]
        $namespaces = $importer->get_namespaces();
        $ns = $namespaces[$importer->get_leap2a_namespace()];
        $addresslines = $addressdata->xpath($ns.':addressline');

        // We look for 'town' and 'city' deliberately, Mahara has
        // separate fields for those. The rest get thrown in the
        // 'address' field
        $personaddress = '';
        $address = array();
        foreach ($addresslines as $addressline) {
            $maharaattributes = PluginImportLeap::get_attributes($addressline, PluginImportLeap::NS_MAHARA);

            if (isset($maharaattributes['artefacttype'])) {
                switch ($maharaattributes['artefacttype']) {
                case 'address':
                case 'town':
                case 'city':
                    $address[$maharaattributes['artefacttype']] = (string)$addressline;
                }
            }
            else {
                $personaddress .= (string)$addressline . "\n";
            }
        }

        if ($personaddress != '') {
            $address['address'] = substr($personaddress, 0, -1);
        }

        // Now deal with country
        $country = $addressdata->xpath($ns.':country');

        if (count($country) == 1) {
            $country = $country[0];
            $leapattributes = PluginImportLeap::get_attributes($country, $importer->get_leap2a_namespace());

            // Try using countrycode attribute first, but fall back to name if it's not present or
            // doesn't represent a country
            require_once('country.php');
            $countrycode = null;
            if (isset($leapattributes['countrycode'])) {
                $countrycode = Country::iso3166_alpha3_to_iso3166_alpha2($leapattributes['countrycode']);
            }

            if (!$countrycode) {
                $countrycode = Country::countryname_to_iso3166_alpha2((string)$country);
            }
        }
        return array(
            'address' => $address,
            'country' => isset($countrycode) ? $countrycode : null,
        );
    }

    /**
     * Find and update duplicates with imported addressdata
     * TODO: Refactor this to combine it with import_addressdata()
     */
    private static function add_import_entry_request_addressdata(PluginImportLeap $importer, $persondataid, SimpleXMLElement $addressdata) {

        $addressfields = self::get_addressfields($importer, $addressdata);
        foreach ($addressfields['address'] as $addresstype => $addressvalue) {
            PluginImportLeap::add_import_entry_request($importer->get('importertransport')->get('importid'), $persondataid, self::STRATEGY_IMPORT_AS_PROFILE_FIELD, 'internal', array(
                'owner'   => $importer->get('usr'),
                'type'    => $addresstype,
                'content' => array(
                    'title'       => $addressvalue,
                ),
            ));
        }

        if (!empty($addressfields['country'])) {
            PluginImportLeap::add_import_entry_request($importer->get('importertransport')->get('importid'), $persondataid, self::STRATEGY_IMPORT_AS_PROFILE_FIELD, 'internal', array(
                'owner'   => $importer->get('usr'),
                'type'    => 'country',
                'content' => array(
                    'title'       => $addressfields['country'],
                ),
            ));
        }
    }
    /**
     * Imports info from a leap:spatial element as a user's address-related
     * profile fields
     * TODO: Refactor this to combine it with add_import_entry_request_addressdata()
     */
    private static function import_addressdata(PluginImportLeap $importer, SimpleXMLElement $addressdata) {

        $addressfields = self::get_addressfields($importer, $addressdata);
        foreach ($addressfields['address'] as $addresstype => $addressvalue) {
            self::create_artefact($importer, $addresstype, $addressvalue);
        }

        if (!empty($addressfields['country'])) {
            self::create_artefact($importer, 'country', $addressfields['country']);
        }
    }

    /**
     * Get name fields from imported namedata
     */
    private static function get_namefields(PluginImportLeap $importer, array $persondata) {
        $namefields = array(
            'full_name' => false,
            'legal_family_name' => false,
            'legal_given_name' => false,
            'preferred_family_name' => false,
            'preferred_given_name' => false,
            'family_name_first' => false,
            'name_prefix' => false,
            'name_suffix' => false,
        );

        foreach ($persondata as $item) {
            $leapattributes = PluginImportLeap::get_attributes($item, $importer->get_leap2a_namespace());
            if (isset($leapattributes['field'])) {
                if (in_array($leapattributes['field'], array_keys($namefields))) {
                    // legal_given_name is allowed to occur any number of times
                    if ($leapattributes['field'] == 'legal_given_name'
                        && $namefields['legal_given_name'] != '') {
                        $namefields['legal_given_name'] .= ' ' . (string)$item;
                    }
                    else {
                        $namefields[$leapattributes['field']] = (string)$item;
                    }
                }
            }
            else {
                // 'Field' is required
                // http://wiki.cetis.ac.uk/2009-03/Leap2A_personal_data#field
                $importer->trace('WARNING: persondata element did not have leap:field attribute');
                continue;
            }
        }

        $familynamefirst = $namefields['family_name_first'] == 'yes' ? true : false;

        // Try to guess reasonable values for first/last names if they're not set
        if ($namefields['legal_given_name'] === false && $namefields['preferred_given_name'] !== false) {
            $namefields['legal_given_name'] = $namefields['preferred_given_name'];
        }
        if ($namefields['legal_family_name'] === false && $namefields['preferred_family_name'] !== false) {
            $namefields['legal_family_name'] = $namefields['preferred_family_name'];
        }

        // This is _an_ algorithm for parsing this info, I'm not saying it's the _best_ one ;)
        if ($familynamefirst) {
            $firstname = (string)$namefields['legal_given_name'] . ' ' . (string)$namefields['name_suffix'];
            $lastname  = (string)$namefields['name_prefix'] . (string)$namefields['legal_family_name'];
            $preferredname = (string)$namefields['preferred_family_name'] . ' ' . (string)$namefields['preferred_given_name'];
        }
        else {
            $firstname = (string)$namefields['name_prefix'] . ' ' . (string)$namefields['legal_given_name'];
            $lastname  = (string)$namefields['legal_family_name'] . ' ' . (string)$namefields['name_suffix'];
            $preferredname = (string)$namefields['preferred_given_name'] . ' ' . (string)$namefields['preferred_family_name'];
        }
        return array(
            'firstname'     => trim($firstname),
            'lastname'      => trim($lastname),
            'preferredname' => trim($preferredname),
        );

    }
    /**
     * Find and update duplicates with imported namedata
     * TODO: Refactor this to combine it with import_namedata();
     */
    private static function add_import_entry_request_namedata(PluginImportLeap $importer, $persondataid, array $persondata) {

        $namefields = self::get_namefields($importer, $persondata);
        if (!empty($namefields['firstname'])) {
            PluginImportLeap::add_import_entry_request($importer->get('importertransport')->get('importid'), $persondataid, self::STRATEGY_IMPORT_AS_PROFILE_FIELD, 'internal', array(
                'owner'   => $importer->get('usr'),
                'type'    => 'firstname',
                'content' => array(
                    'title'       => $namefields['firstname'],
                ),
            ));
        }
        if (!empty($namefields['lastname'])) {
            PluginImportLeap::add_import_entry_request($importer->get('importertransport')->get('importid'), $persondataid, self::STRATEGY_IMPORT_AS_PROFILE_FIELD, 'internal', array(
                'owner'   => $importer->get('usr'),
                'type'    => 'lastname',
                'content' => array(
                    'title'       => $namefields['lastname'],
                ),
            ));
        }
        if (!empty($namefields['preferredname'])) {
            PluginImportLeap::add_import_entry_request($importer->get('importertransport')->get('importid'), $persondataid, self::STRATEGY_IMPORT_AS_PROFILE_FIELD, 'internal', array(
                'owner'   => $importer->get('usr'),
                'type'    => 'preferredname',
                'content' => array(
                    'title'       => $namefields['preferredname'],
                ),
            ));
        }
    }

    /**
     * TODO: Refactor this to combine it with add_import_entry_request_namedata()
     * @param PluginImportLeap $importer
     * @param array $persondata
     */
    private static function import_namedata(PluginImportLeap $importer, array $persondata) {

        $namefields = self::get_namefields($importer, $persondata);
        self::create_artefact($importer, 'firstname', $namefields['firstname']);
        self::create_artefact($importer, 'lastname', $namefields['lastname']);
        self::create_artefact($importer, 'preferredname', $namefields['preferredname']);
        $importer->get('usrobj')->firstname = $namefields['firstname'];
        $importer->get('usrobj')->lastname = $namefields['lastname'];
        $importer->get('usrobj')->preferredname = $namefields['preferredname'];
        $importer->get('usrobj')->commit();
    }

    /**
     * Creates an artefact in the manner required to overwrite existing profile
     * artefacts
     *
     * @param PluginImportLeap $importer The importer
     * @param string $artefacttype        The type of artefact to create
     * @param string $title               The title for the artefact (with profile
     *                                    fields, this is the main data)
     * @param array $extradata            An array containing extra data (used for socialprofile artefacts)
     * @return int The ID of the artefact created
     */
    private static function create_artefact(PluginImportLeap $importer, $artefacttype, $title, $extradata = null) {
        $classname = generate_artefact_class_name($artefacttype);
        if (($artefacttype == 'email')
            && ($a = get_record('artefact', 'artefacttype', 'email', 'owner', $importer->get('usr'), 'title', $title))) {
            // email is a bit special. just check if we have one with this value already
            // User may have several email addresses but they must be UNIQUE
            return $a->id;
        }
        try {
            $artefact = artefact_instance_from_type($artefacttype, $importer->get('usr'));
        }
        catch (Exception $e) {
            $artefact = new $classname(0, array('owner' => $importer->get('usr')));
        }
        $artefact->set('title', $title);
        if (!empty($extradata)) {
            foreach ($extradata as $field => $value) {
                $artefact->set($field, $value);
            }
        }
        $artefact->commit();
        return $artefact->get('id');
    }

    /**
     * Render import entry requests for Mahara user's profile fields
     * @param PluginImportLeap $importer
     * @return HTML code for displaying user's profile fields and choosing how to import them
     */
    public static function render_import_entry_requests(PluginImportLeap $importer) {
        global $USER;

        $importid = $importer->get('importertransport')->get('importid');
        $profilefields = array(
            'profile' => array(
                'legend' => get_string('aboutme', 'artefact.internal'),
                'fields' => array('firstname', 'lastname', 'studentid', 'preferredname', 'introduction'),
            ),
            'contact' => array(
                'legend' => get_string('contact', 'artefact.internal'),
                'fields' => array('email', 'maildisabled', 'officialwebsite', 'personalwebsite', 'blogaddress', 'address', 'town', 'city', 'country', 'homenumber', 'businessnumber', 'mobilenumber', 'faxnumber'),
            ),
            'messaging' => array(
                'legend' => get_string('messaging', 'artefact.internal'),
                'fields' => array('socialprofile'),
            ),
            'general' => array(
                'legend' => get_string('general'),
                'fields' => array('occupation', 'industry'),
            ),
        );
        // Get current user's institutions
        $institutions = empty($USER->get('institutions')) ? array('mahara') : array_keys($USER->get('institutions'));
        // Get import entry requests for Mahara profile fields
        $profilegroups = array();
        foreach ($profilefields as $gr_key => $group) {
            $profilegroup = array();
            $profilegroup['id'] = $gr_key;
            $profilegroup['legend'] = $group['legend'];
            foreach ($group['fields'] as $f) {
                if ($iers = get_records_select_array('import_entry_requests', 'importid = ? AND entrytype = ?', array($importid, $f))) {
                    $profilefieldvalues = array();
                    foreach ($iers as $ier) {
                        $profilefieldvalue = unserialize($ier->entrycontent);
                        $profilefieldvalue['id'] = $ier->id;
                        $profilefieldvalue['decision'] = $ier->decision;
                        $classname = generate_artefact_class_name($f);
                        $profilefieldvalue['html'] = $classname::render_import_entry_request($profilefieldvalue);
                        if (is_string($ier->duplicateditemids)) {
                            $ier->duplicateditemids = unserialize($ier->duplicateditemids);
                        }
                        if (is_string($ier->existingitemids)) {
                            $ier->existingitemids = unserialize($ier->existingitemids);
                        }
                        $profilefieldvalue['disabled'][PluginImport::DECISION_IGNORE] = false;
                        if (get_field_sql('SELECT profilefield FROM {institution_locked_profile_field}
                                          WHERE name IN (' . join(',', array_map('db_quote', $institutions)) . ')
                                          AND profilefield = ?', array($f))) {
                            $profilefieldvalue['disabled'][PluginImport::DECISION_ADDNEW] = true;
                            $profilefieldvalue['disabled'][PluginImport::DECISION_APPEND] = true;
                            $profilefieldvalue['disabled'][PluginImport::DECISION_REPLACE] = true;
                            $profilefieldvalue['decision'] = PluginImport::DECISION_IGNORE;
                        }
                        else if (!empty($ier->duplicateditemids)) {
                            $duplicated_pfield = artefact_instance_from_id($ier->duplicateditemids[0]);
                            $profilefieldvalue['duplicateditem']['id'] = $duplicated_pfield->get('id');
                            $res = $duplicated_pfield->render_self(array());
                            $profilefieldvalue['duplicateditem']['html'] = $res['html'];
                            $profilefieldvalue['disabled'][PluginImport::DECISION_ADDNEW] = true;
                            $profilefieldvalue['disabled'][PluginImport::DECISION_APPEND] = true;
                            $profilefieldvalue['disabled'][PluginImport::DECISION_REPLACE] = true;
                        }
                        else if (!empty($ier->existingitemids)) {
                            foreach ($ier->existingitemids as $id) {
                                $existing_pfield = artefact_instance_from_id($id);
                                $res = $existing_pfield->render_self(array());
                                $profilefieldvalue['existingitems'][] = array(
                                    'id'    => $existing_pfield->get('id'),
                                    'html'  => $res['html'],
                                );
                            }
                            if ($f == 'email') {
                                $profilefieldvalue['disabled'][PluginImport::DECISION_ADDNEW] = false;
                                $profilefieldvalue['disabled'][PluginImport::DECISION_APPEND] = true;
                                $profilefieldvalue['disabled'][PluginImport::DECISION_REPLACE] = true;
                            }
                            else {
                                $is_singular = call_static_method(generate_artefact_class_name($ier->entrytype), 'is_singular');
                                $profilefieldvalue['disabled'][PluginImport::DECISION_ADDNEW] = $is_singular;
                                $profilefieldvalue['disabled'][PluginImport::DECISION_APPEND] = !$is_singular;
                                $profilefieldvalue['disabled'][PluginImport::DECISION_REPLACE] = !$is_singular;
                            }
                        }
                        else {
                            $profilefieldvalue['disabled'][PluginImport::DECISION_ADDNEW] = false;
                            $profilefieldvalue['disabled'][PluginImport::DECISION_APPEND] = true;
                            $profilefieldvalue['disabled'][PluginImport::DECISION_REPLACE] = true;
                        }
                        $profilefieldvalues[] = $profilefieldvalue;
                    }
                    $profilegroup['fields'][$f] = $profilefieldvalues;
                }
            }
            $profilegroups[] = $profilegroup;
        }
        $smarty = smarty_core();
        $smarty->assign('displaydecisions', $importer->get('displaydecisions'));
        $smarty->assign('profilegroups', $profilegroups);
        return $smarty->fetch('artefact:internal:import/profilefields.tpl');
    }

}
