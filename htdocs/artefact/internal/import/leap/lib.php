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
 * @subpackage artefact-internal-import-leap
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/**
 * Implements Leap2A import of profile related entries into Mahara
 *
 * For more information about Leap profile importing, see:
 * http://wiki.mahara.org/Developer_Area/Import%2f%2fExport/LEAP_Import/Internal_Artefact_Plugin
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
            'service' => 'msn',
            'uri'     => 'http://www.msn.com/',
            'artefact_type' => 'Msnnumber',
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

    public static function import_using_strategy(SimpleXMLElement $entry, PluginImportLeap $importer, $strategy, array $otherentries) {
        $artefactmapping = array();
        switch ($strategy) {
        case self::STRATEGY_IMPORT_AS_PROFILE_FIELD:
            // Based on the mahara:type, we might be able to import it as 
            // something useful - otherwise, there is nothing we can do. The 
            // entry already claimed it was mahara:plugin="internal", so it's 
            // perfectly fine for us to not import it if we don't recognise it
            $types = array(
                'occupation',
                'industry',
            );
            $typexpath = join('" or @mahara:type="', $types);
            $artefactpluginelement = $entry->xpath('mahara:artefactplugin[@mahara:type="' . $typexpath . '"]');
            if (count($artefactpluginelement) == 1) {
                $artefactpluginelement = $artefactpluginelement[0];

                $maharaattributes = PluginImportLeap::get_attributes($artefactpluginelement, PluginImportLeap::NS_MAHARA);
                if (isset($maharaattributes['type']) && in_array($maharaattributes['type'], $types)) {
                    $artefactmapping[(string)$entry->id] = array(self::create_artefact($importer, $maharaattributes['type'], PluginImportLeap::get_entry_content($entry, $importer)));
                }
            }
            break;
        case self::STRATEGY_IMPORT_AS_INTRODUCTION:
            // The introduction comes from the entry content
            $introduction = new ArtefactTypeIntroduction(0, array('owner' => $importer->get('usr')));
            $introduction->set('title', PluginImportLeap::get_entry_content($entry, $importer));
            $introduction->commit();
            break;
        default:
            throw new ImportException($importer, 'TODO: get_string: unknown strategy chosen for importing entry');
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
     * Attempts to import a persondata element
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
     * Attempts to import a persondata field with leap:field="id"
     */
    private static function import_persondata_id(PluginImportLeap $importer, SimpleXMLElement $item, array $leapattributes) {
        if ($leapattributes['field'] == 'id' && !isset($leapattributes['service'])) {
            // 'id' must have a service set
            // http://wiki.cetis.ac.uk/2009-03/Leap2A_personal_data#service
            throw new ImportException($importer, "TODO: get_string: persondata field was 'id' but had no service set");
        }

        // Lack of 'grep' and closures is annoying...
        foreach (self::$services as $service) {
            if ($service['service'] == $leapattributes['service'] || $service['uri']  == $leapattributes['service']) {
                self::create_artefact($importer, $service['artefact_type'], (string)$item);
                return;
            }
        }

        // TODO what do we do here?
        $importer->trace(" * Unrecognised service $attributes[service], ignored");
    }

    /**
     * Attempts to import a persondata field with leap:field="email"
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
     * Attempts to import a persondata field with leap:field="website"
     */
    private static function import_persondata_website(PluginImportLeap $importer, SimpleXMLElement $item, array $leapattributes) {
        // We've been given a 'website' field, but Mahara has three profile 
        // fields for website. So we need to examine it deeper to establish 
        // which field it should import into
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
     * Attempts to import a persondata field with leap:field="other"
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
    private static function import_addressdata(PluginImportLeap $importer, SimpleXMLElement $addressdata) {
        // TODO: this xpath doesn't respect the namespace prefix - we should 
        // look it up from $importer->namespaces[NS_LEAP]
        $namespaces = $importer->get_namespaces();
        $ns = $namespaces[$importer->get_leap2a_namespace()];
        $addresslines = $addressdata->xpath($ns.':addressline');

        // We look for 'town' and 'city' deliberately, Mahara has 
        // separate fields for those. The rest get thrown in the 
        // 'address' field
        $personaddress = '';
        foreach ($addresslines as $addressline) {
            $maharaattributes = PluginImportLeap::get_attributes($addressline, PluginImportLeap::NS_MAHARA);

            if (isset($maharaattributes['artefacttype'])) {
                switch ($maharaattributes['artefacttype']) {
                case 'address':
                case 'town':
                case 'city':
                    self::create_artefact($importer, $maharaattributes['artefacttype'], (string)$addressline);
                }
            }
            else {
                $personaddress .= (string)$addressline . "\n";
            }
        }

        if ($personaddress != '') {
            self::create_artefact($importer, 'address', substr($personaddress, 0, -1));
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

            if ($countrycode) {
                self::create_artefact($importer, 'country', $countrycode);
            }
        }
    }

    private static function import_namedata(PluginImportLeap $importer, array $persondata) {
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
        $firstname = trim($firstname);
        $lastname  = trim($lastname);
        $preferredname = trim($preferredname);

        self::create_artefact($importer, 'firstname', $firstname);
        self::create_artefact($importer, 'lastname', $lastname);
        self::create_artefact($importer, 'preferredname', $preferredname);
        $importer->get('usrobj')->firstname = $firstname;
        $importer->get('usrobj')->lastname = $lastname;
        $importer->get('usrobj')->preferredname = $preferredname;
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
     * @return int The ID of the artefact created
     */
    private static function create_artefact(PluginImportLeap $importer, $artefacttype, $title) {
        $classname = generate_artefact_class_name($artefacttype);
        $artefact = null;
        if ($artefacttype == 'email') {
            // this type is a bit special. just check if we have one with this value already
            if ($a = get_record('artefact', 'artefacttype', 'email', 'owner', $importer->get('usr'), 'title', $title)) {
                $artefact = new $classname($a->id, $a);
            }
        }
        if (empty($artefact)) {
            try {
                $artefact = artefact_instance_from_type($artefacttype, $importer->get('usr'));
            } catch (Exception $e) {
                $artefact = new $classname(0, array('owner' => $importer->get('usr')));
            }
        }
        $artefact->set('title', $title);
        $artefact->commit();
        return $artefact->get('id');
    }

}
