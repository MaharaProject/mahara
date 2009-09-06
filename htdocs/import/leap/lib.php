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
 * @subpackage import-leap
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/**
 * Implements import of LEAP2A files
 *
 * For more documentation, please see:
 * http://wiki.mahara.org/index.php?title=Developer_Area/Import%2F%2FExport/LEAP_Import
 */
class PluginImportLeap extends PluginImport {

    private $xml = null;
    private $namespaces = array();
    private $strategylisting = array();
    private $loadmapping = array();
    private $artefactids = array();

    protected $persondataid = null;

    protected $loglevel = 0;
    protected $logtargets = LOG_TARGET_ERRORLOG;
    protected $profile = false;

    private $snapshots = array();

    const LOG_LEVEL_STANDARD = 1;
    const LOG_LEVEL_VERBOSE  = 2;

    const NS_ATOM       = 'http://www.w3.org/2005/Atom';
    const NS_RDF        = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
    const NS_LEAP       = 'http://wiki.cetis.ac.uk/2009-03/LEAP2A_predicates#';
    const NS_LEAPTYPE   = 'http://wiki.cetis.ac.uk/2009-03/LEAP2A_types#';
    const NS_CATEGORIES = 'http://wiki.cetis.ac.uk/2009-03/LEAP2A_categories/';
    const NS_MAHARA     = 'http://wiki.mahara.org/Developer_Area/Import%2F%2FExport/LEAP_Extensions#';

    public static function validate_import_data($importdata) {
    }

    public function get($field) {
        if ($field == 'xml') {
            return $this->xml;
        }
        return parent::get($field);
    }

    public function process() {
        db_begin();

        $data = $this->get('data');
        $filename = get_config('dataroot') . $data['filename'];
        $this->trace('Loading import from ' . $filename);
        $this->snapshot('begin');

        $options =
            LIBXML_COMPACT |    // Reported to greatly speed XML parsing
            LIBXML_NONET        // Disable network access - security check
        ;
        if (!$this->xml = simplexml_load_file($filename, 'SimpleXMLElement', $options)) {
            // TODO: bail out in a much nicer way...
            throw new ImportException($this, "FATAL: XML file is not well formed! Please consult Mahara's error log for more information");
        }
        $this->namespaces = array_flip($this->xml->getDocNamespaces());
        $this->registerXpathNamespaces($this->xml);
        $this->trace("Document loaded, entries: " . count($this->xml->entry));
        $this->snapshot('loaded XML');

        $this->ensure_document_valid();

        $this->create_strategy_listing();
        $this->snapshot('created strategy listing');
        $this->strategy_listing_to_load_mapping();
        $this->snapshot('converted strategy listing to load mapping');
        $this->import_from_load_mapping();
        $this->snapshot('imported data based on load mapping');
        $this->import_completed();

        db_commit();
    }

    private function ensure_document_valid() {
        // TODO: http://wiki.mahara.org/Developer_Area/Import%2f%2fExport/Import%3a_Implementation_Plan#beginning
        //
        // Do a bunch of checks that will ensure the feed is valid, and thus 
        // allow future code to make assumptions that the feed is valid

        // Things to check:
        // - all content src="X": make sure the src actually exists
        // - feed has a <feed> element with an <author> and <entry>s
        // - feed element has correct namespaces (keep in mind we need to be 
        // able to import raw ATOM feeds too)

        //throw new ImportException($this, "Import wasn't valid. TODO: error reporting");
    }

    /**
     * http://wiki.mahara.org/Developer_Area/Import%2f%2fExport/Import%3a_Implementation_Plan#first_pass_-_get_scores_from_plugins_for_each_entry
     *
     * Each list of strategies for each entry should be sorted from highest to lowest score once this is done
     */
    private function create_strategy_listing() {
        $this->trace("-------------------------\ncreate_strategy_listing()");

        // First, try to establish whether there is an element representing the 
        // author of the feed
        // TODO: also check this element has the right leaptype (person)
        if (is_null($this->persondataid)) {
            $author = $this->get('xml')->xpath('//a:feed/a:author[1]');
            $author = $author[0];
            if (isset($author->uri) && $this->get_entry_by_id((string)$author->uri)) {
                $this->persondataid = (string)$author->uri;
            }
            else {
                $this->persondataid = false;
            }
        }

        // Generate strategy listing
        foreach ($this->xml->xpath('//a:feed/a:entry') as $entry) {
            $this->registerXpathNamespaces($entry);
            $entryid = (string)$entry->id;
            $this->trace(" * $entryid ({$entry->title})");

            if ($this->persondataid && $entryid == $this->persondataid) {
                // We don't offer this element to any plugin to be imported
                continue;
            }

            foreach (plugins_installed('artefact') as $plugin) {
                $plugin = $plugin->name;
                if (safe_require('import', 'leap/' . $plugin, 'lib.php', 'require_once', true)) {
                    $classname = 'LeapImport' . ucfirst($plugin);
                    if (!is_subclass_of($classname, 'LeapImportArtefactPlugin')) {
                        throw new SystemException("Class $classname does not extend LeapImportArtefactPlugin as it should");
                    }
                    $strategies = call_static_method($classname, 'get_import_strategies_for_entry', $entry, $this);
                    $this->trace("   artefact.$plugin strategies: " . count($strategies));
                    if ($strategies) {
                        $this->trace($strategies, self::LOG_LEVEL_VERBOSE);
                    }

                    foreach ($strategies as $strategy) {
                        // Verify they're in valid form
                        if (!array_key_exists('strategy', $strategy)
                            || !array_key_exists('score', $strategy)
                            || !array_key_exists('other_required_entries', $strategy)) {
                            throw new SystemException("$classname::get_import_strategies_for_entry returned a strategy missing "
                                . "one or more of the strategy, score or other_required_entries keys");
                        }
                        $strategy['artefactplugin'] = $plugin;
                        $this->strategylisting[$entryid][] = $strategy;
                    }
                }
            }

            if (!isset($this->strategylisting[$entryid])) {
                $this->trace(" ** Could not find any strategies for $entryid **");
                // TODO: we might need to expose this information later
                continue;
            }

            // Sort by score descending
            usort($this->strategylisting[$entryid], create_function('$a, $b', 'return $a["score"] < $b["score"];'));
        }
        $this->trace('*** Strategy listing: ***');
        $this->trace($this->strategylisting);
    }

    /**
     * Converts a strategy listing into the "best" load mapping that can be 
     * gleaned from the strategy listing.
     *
     * The best mapping is decided by sorting the strategy listing by score and 
     * importing all entries on a first-met, first used basis. The algorithm is 
     * described at:
     * http://wiki.mahara.org/Developer_Area/Import%2f%2fExport/Import%3a_Implementation_Plan#non-interactive_import
     */
    private function strategy_listing_to_load_mapping() {
        $this->trace("----------------------------------\nstrategy_listing_to_load_mapping()");
        $usedlist = array();

        // Each entry has its strategies sorted from best to worst score, then 
        // we sort the entire list of entries by the best scores they have 
        // available. This is so we get the "best" plan for importing
        uasort($this->strategylisting, array($this, 'strategy_listing_sort'));
        $this->trace('*** Sorted strategy listing ***', self::LOG_LEVEL_VERBOSE);
        $this->trace($this->strategylisting, self::LOG_LEVEL_VERBOSE);

        // We're only interested in the first listing really. It's the strategy 
        // with the highest score for this entry. Most of the loop mechanics is 
        // so that if we choose to import an entry that requires other entries, 
        // we don't import those other entries separately as well.
        foreach($this->strategylisting as $entryid => $strategies) {
            foreach ($strategies as $strategydata) {
                $this->trace("entry: $entryid");
                if (in_array($entryid, $usedlist)) {
                    $this->trace(" * already in used list, skipped");
                    continue;
                }
                if (isset($strategydata['other_required_entries'])) {
                    foreach ($strategydata['other_required_entries'] as $otherentryid) {
                        if (in_array($otherentryid, $usedlist)) {
                            $this->trace(" * other required entry $otherentryid already in used list, skipped");
                            continue;
                        }
                    }
                }

                $this->trace(" * using strategy $strategydata[strategy] from plugin $strategydata[artefactplugin]");
                $this->loadmapping[$entryid] = $strategydata;

                $usedlist[] = $entryid;

                if (isset($strategydata['other_required_entries'])) {
                    foreach ($strategydata['other_required_entries'] as $otherentryid) {
                        $usedlist[] = $otherentryid;
                    }
                }

                // We've found how we're going to import this entry now
                // TODO: we should check the next strategy here and see if it 
                // has the same score, if it does we should note that for later 
                // for debugging purposes
                break;
            }
        }
        $this->trace("*** Load mapping: ***");
        $this->trace($this->loadmapping);
    }

    private function strategy_listing_sort($a, $b) {
        // NOTE: the strategy listing should already have the strategies for 
        // each entry sorted from highest to lowest score. If it doesn't, these 
        // two sorts fix that, but this should never happen.
        //usort($a, create_function('$a, $b', 'return $a["score"] < $b["score"];'));
        //usort($b, create_function('$a, $b', 'return $a["score"] < $b["score"];'));
        return $a[0]['score'] < $b[0]['score'];
    }

    /**
     * Import entries using our load mapping. See:
     * http://wiki.mahara.org/Developer_Area/Import%2f%2fExport/Import%3a_Implementation_Plan#second_pass.3a_load_all_entries_into_mahara_as_per_load_mapping
     */
    private function import_from_load_mapping() {
        $this->trace("--------------------------\nimport_from_load_mapping()");
        // TODO: do both usedlists as by key instead of by value for faster checks
        $usedlist = $loadedentries = array();
        uksort($this->loadmapping, create_function('$a, $b', 'return $a["score"] < $b["score"];'));
        foreach ($this->loadmapping as $entryid => $strategydata) {
            if (in_array($entryid, $usedlist)) {
                // TODO: what should we do in this case?
                $this->trace("WARNING: $entryid has already been imported as part of a previous entry");
                continue;
            }
            if (isset($strategydata['other_required_entries'])) {
                foreach ($strategydata['other_required_entries'] as $otherentryid) {
                    if (in_array($otherentryid, $usedlist)) {
                        $this->trace("WARNING: $entryid has already been imported as part of a previous entry");
                        continue(2);
                    }
                }
            }

            $this->trace("Importing $entryid using strategy $strategydata[strategy] of plugin $strategydata[artefactplugin]");
            safe_require('artefact', $strategydata['artefactplugin']);
            $entry = $this->get_entry_by_id($entryid);
            $classname = 'LeapImport' . ucfirst($strategydata['artefactplugin']);
            // TODO: this throws ImportException if it can't be imported, need 
            // to decide if this exception can bubble up or whether it should 
            // be caught here
            $artefactmapping = call_static_method($classname, 'import_using_strategy',
                $entry, $this, $strategydata['strategy'], $strategydata['other_required_entries']);

            if (!is_array($artefactmapping)) {
                throw new SystemException("import_from_load_mapping(): $classname::import_using_strategy has not return a list");
            }

            $this->artefactids = array_merge($this->artefactids, $artefactmapping);

            $usedlist[] = $entryid;
            if (isset($strategydata['other_required_entries'])) {
                foreach ($strategydata['other_required_entries'] as $otherentryid) {
                    $usedlist[] = $otherentryid;
                }
            }

            $loadedentries[] = $entryid;
        }

        // Give plugins a chance to import stuff about the feed author from the 
        // persondata entry
        // TODO: this should return an artefact mapping so things can create 
        // links to profile fields, but nothing actually needs it yet
        foreach (plugins_installed('artefact') as $plugin) {
            $classname = 'LeapImport' . ucfirst($plugin->name);
            $strategies = call_static_method($classname, 'import_author_data', $this, $this->persondataid);
        }

        // Now all artefacts are loaded, allow each plugin to load 
        // relationships for them if they need to
        foreach ($loadedentries as $entryid) {
            $strategydata = $this->loadmapping[$entryid];
            $classname = 'LeapImport' . ucfirst($strategydata['artefactplugin']);
            $entry = $this->get_entry_by_id($entryid);
            call_static_method($classname, 'setup_relationships',
                $entry, $this, $strategydata['strategy'], $strategydata['other_required_entries']);
        }
    }

    private function import_completed() {
        $this->trace("------------------\nimport_completed()");

        unset($this->loadmapping);
        unset($this->strategylisting);
        unset($this->xpath);
        unset($this->dom);
        $this->snapshot('end');

        if ($this->profile) {
            $this->trace("k: abstime\ttime\tmemafter\tmemdiff");
            $firstdata = $lastdata = $this->snapshots['begin'];
            $i = 0;
            foreach ($this->snapshots as $identifier => $data) {
                $i++;
                $timetaken = $data['time'] - $lastdata['time'];
                $abstime   = $data['time'] - $firstdata['time'];
                $memoryafter = $data['ram'] / 1024;
                $memorygrowth = ($data['ram'] - $lastdata['ram']) / 1024;
                $this->trace(sprintf("$i: %.3f\t%.3f\t%.3fK\t%8.3fK", $abstime, $timetaken, $memoryafter, $memorygrowth));
                $lastdata = $data;
            }

            $i = 0;
            foreach (array_keys($this->snapshots) as $identifier) {
                $i++;
                $this->trace("$i = $identifier");
            }
            $this->trace("abstime = time after beginning step of import that this step completed\n"
                . "time = time taken to complete this step\n"
                . "memafter = script memory usage after this step\n"
                . "memdiff = difference in memory usage compared with the last step");
        }
    }

    /**
     * Logs a message for debugging purposes
     *
     * The user can configure the amount of logging, and where it goes, by 
     * setting the 'loglevel' and 'logtargets' fields.
     *
     * loglevel is one of {0, PluginImportLeap::LOG_LEVEL_STANDARD, PluginImportLeap::LOG_LEVEL_VERBOSE}
     * logtargets is one of the LOG_TARGET_* constants
     *
     * @param string $message The message to log
     * @param int    $level   The level at which to log this error
     */
    public function trace($message, $level=self::LOG_LEVEL_STANDARD) {
        if ($level <= $this->loglevel) {
            // Use our logging system temporarily, which provides us with 
            // access to its nice features. We use log_message instead of
            // log_debug because that will retain the the line/file where 
            // trace() was called, rather than saying all of the messages came 
            // from trace() itself
            global $CFG;
            $old = $CFG->log_dbg_targets;
            $CFG->log_dbg_targets = $this->logtargets;
            $file = $line = $trace = null;
            log_message ($message, LOG_LEVEL_DBG, true, true, $file, $line, $trace);
            $CFG->log_dbg_targets = $old;
        }
    }

    private function snapshot($identifier) {
        if ($this->profile) {
            $this->snapshots[$identifier] = array(
                'time' => microtime(true),
                'ram'  => memory_get_usage(), // TODO: http://php.net/memory_get_usage suggests this isn't available everywhere
            );
        }
    }

    /**
     * Register all namespaces on an element that have been declared on the 
     * document
     *
     * TODO: we should probably do this by examining $this->namespaces
     */
    public function registerXpathNamespaces(SimpleXMLElement $element) {
        $element->registerXpathNamespace('a', PluginImportLeap::NS_ATOM);
        $element->registerXpathNamespace('rdf', PluginImportLeap::NS_RDF);
        $element->registerXpathNamespace('mahara', PluginImportLeap::NS_MAHARA);
    }

    public function get_entry_by_id($entryid) {
        static $cache = array();
        if (isset($cache[$entryid])) {
            return $cache[$entryid];
        }
        // TODO: entryid injection? Probably not worth worrying about
        $matches = $this->xml->xpath("//a:feed/a:entry/a:id[.='$entryid']/..");
        // TODO: there had better be only one...
        $entry = $matches[0];
        if ($entry) {
            $this->registerXpathNamespaces($entry);
        }
        return ($cache[$entryid] = $entry);
    }

    public function get_artefactids_imported_by_entryid($entryid) {
        if (!isset($this->artefactids[$entryid])) {
            return null;
        }
        return $this->artefactids[$entryid];
    }

    /**
     * Returns xpath for an attribute matching a given curie
     *
     * See http://wiki.cetis.ac.uk/2009-03/LEAP2A_elements#Use_of_CURIEs
     *
     * @param string $attribute The attribute to match
     * @param string $namespace The namespace for the attribute value
     * @param string $term      The term for the attribute value
     * @return string
     */
    public function curie_xpath($attribute, $namespace, $term) {
        $xpath = '';
        if (isset($this->namespaces[$namespace])) {
            $abbreviation = $this->namespaces[$namespace];

            // Simple case
            $xpath .= $attribute . '="' . $abbreviation . ':' . $term . '" or ';
            // "correct" curie syntax has them surrounded by [ ]
            $xpath .= $attribute . '="[' . $abbreviation . ':' . $term . ']" or ';
        }

        // The full URL for the curie
        $xpath .= $attribute . '="' . $namespace . $term . '"';

        return $xpath;
    }

    /**
     * Returns whether a curie matches a given namespace/term, checking all 
     * allowed forms of the curie
     *
     * @param string $curie     The curie to check
     * @param string $namespace The namespace to check the curie against
     * @param string $term      The term to check the curie against
     * @return boolean
     */
    public function curie_equals($curie, $namespace, $term) {
        if (isset($this->namespaces[$namespace])) {
            $abbreviation = $this->namespaces[$namespace];
            return
                $curie == "$abbreviation:$term"
                || $curie == "[$abbreviation:$term]"
                || $curie == $namespace . $term;
        }

        return $curie == $namespace . $term;
    }

    /**
     * TODO: document
     * TODO: make sure we are rawurlencoding our file paths in our export
     */
    public static function get_entry_content(SimpleXMLElement $entry, PluginImportLeap $importer) {
        // Entries have content, and that content can be of different types. So we want to make sure we grab it in the right type
        // - if it's xhtml, we need asXML(), plus removing the <content> tags
        // - if it's text, what do we need???
        $type = isset($entry->content['type']) ? (string)$entry->content['type'] : 'text';
        switch ($type) {
        case 'xhtml':
            if (isset($entry->content->div)) {
                // TODO: using asXML() gives us the content, but does things 
                // like squashing the space between <br and />. This might need 
                // to be "fixed" (turned back into browser-happy xhtml) if it 
                // causes problems.
                $content = (string)$entry->content->div->asXML();
                if (substr($content, 0, 42) == '<div xmlns="http://www.w3.org/1999/xhtml">') {
                    $content = substr($content, 42, -6);
                    return $content;
                }
            }
            log_debug("ERROR: <content> tag declared to be type xhtml but didn't wrap its content in a div with xmlns=http://www.w3.org/1999/xhtml");

            $starttaglength = strlen('<content type="xhtml">');
            $endtaglength   = strlen('</content>');
            return substr((string)$entry->content->asXML(), $starttaglength, -$endtaglength);
        case 'html':
        case 'text':
            return (string)$entry->content;
        default:
            throw new SystemException("Unrecognised content type for entry '$entry->id'");
        }
    }

    public static function get_entry_tags(SimpleXMLElement $entry) {
        $tags = array();
        foreach ($entry->category as $category) {
            if (!isset($category['scheme'])) {
                if (isset($category['label'])) {
                    $tags[] = (string)$category['label'];
                }
                else {
                    $tags[] = (string)$category['term'];
                }
            }
        }
        return array_unique($tags);
    }

    /**
     * Look for leap:date elements that are part of an entry (if any) and 
     * return the values we parse from them
     *
     * Returned in a structure like so:
     * array(
     *     'start' => array(
     *         'value' => 'w3c compliant date/time format, as allowed by LEAP2A',
     *         'label' => 'label attribute, which is a plaintext version of the date',
     *     ),
     *     'end' => array(
     *         'value' => 'maybe only the value is set on some elements',
     *     ),
     *     'target' => array(
     *         'label' => 'sometimes, only the label will be set'
     *     )
     * )
     *
     * The only keys in the return value are those allowed by the LEAP2A spec, 
     * and only if they exist on the entry.
     *
     * The values may have the 'value', 'label' or both keys, depending on what 
     * each element has.
     *
     * Try and use the 'value' first, if you have a choice. Quite a few places 
     * in Mahara currently just store dates as plaintext though.
     *
     * Spec reference: http://wiki.cetis.ac.uk/2009-03/LEAP2A_literals#date
     */
    public static function get_leap_dates(SimpleXMLElement $entry) {
        $dates = array();
        foreach (array('start', 'end', 'target') as $point) {
            $dateelement = $entry->xpath('leap:date[@leap:point="' . $point . '"]');
            if (count($dateelement) == 1) {
                $dateelement = $dateelement[0];
            }

            if ($dateelement instanceof SimpleXMLElement) {
                $date = (string)$dateelement;
                if ($date) {
                    $dates[$point]['value'] = $date;
                }

                // Parse for leap:label
                $leapattributes = array();
                foreach ($dateelement->attributes(PluginImportLeap::NS_LEAP) as $key => $value) {
                    $leapattributes[$key] = (string)$value;
                }
                if (isset($leapattributes['label'])) {
                    $dates[$point]['label'] = $leapattributes['label'];
                }
            }
        }
        return $dates;
    }

    /**
     * Waffer thin helper to grab all attributes in a namespace.
     *
     * It's often much easier to work with them in this form. SimpleXML doesn't 
     * provide a nice property to get at them with.
     *
     * @param SimpleXMLElement $element The element to get attributes from
     * @param string $ns                The namespace to get the attributes for
     * @return array                    The attributes in the namespace
     */
    public static function get_attributes(SimpleXMLElement $item, $ns) {
        $attributes = array();
        foreach ($item->attributes($ns) as $key => $value) {
            $attributes[$key] = (string)$value;
        }
        return $attributes;
    }

}


/**
 * Base class for artefact plugin implementations of LEAP import
 *
 * Any artefact plugin wanting to implement import needs to extend this class 
 * in the file artefact/<plugin>/import/leap/lib.php.
 *
 * TODO: link to wiki docs for more info
 */
abstract class LeapImportArtefactPlugin {

    /**
     * Given an entry, should return a list of the possible ways that it could 
     * be imported by this plugin.
     *
     * The return result is in the form:
     * array(
     *     array(
     *         strategy => [strategy:int],
     *         score    => [score:int],
     *         other_required_entries => array(
     *             [entryid:string],
     *             [entryid:string],
     *             ...
     *         ),
     *     ),
     *     [...],
     * )
     *
     * This can be described as a list of strategies. Each strategy has a 
     * unique (to this class) identifier ([strategy:int]), a score, and a list 
     * of IDs of other entries required to implement this strategy.
     *
     * The strategy is just an identifier for the internal use of the class, to 
     * distinguish between strategies. Most implementors should define class 
     * constants for them, e.g.:
     *
     *     const STRATEGY_IMPORT_AS_FILE = 1;
     *     const STRATEGY_IMPORT_AS_FOLDER = 1;
     *
     * The score represents how well this strategy applies to this entry. 100 
     * is considered an extremely high score (use this for 'I think this is a 
     * perfect match' type strategies).
     *
     * The other required entries is a list of entries this strategy will 
     * require to be implemented. It's a list of entry IDs - a.k.a the contents 
     * of the <id> element of an <entry>.
     *
     * The return result is a list of strategies, which means that you can 
     * provide more than one if you think you have two possible matches. This 
     * method should return everything that is _possible_, even if it's not the 
     * best match, as the user may choose the less obvious method of importing 
     * for some reason.
     *
     * @param SimpleXMLElement $entry The entry to find import strategies for
     * @param PluginImport $importer  The importer
     * @return array A list of strategies that could be used to import this entry
     */
    abstract public static function get_import_strategies_for_entry(SimpleXMLElement $entry, PluginImport $importer);

    /**
     * Converts an entry into the appropriate artefacts using the given 
     * strategy.
     *
     * The strategy will be one of ones this plugin previously said would be
     * possible for this entry. This method may throw an ImportException if it 
     * is not.
     *
     * This method is quite tied to get_import_strategies_for_entry: if that 
     * method exports a certain strategy (with a certain list of other required 
     * entries), then if that strategy is chosen, this method will be invoked 
     * with that strategy and that list of other required entries. HOWEVER, you 
     * cannot assume that both method calls will happen in the same request - a 
     * UI may be presented to the user to make them choose strategies in 
     * between these steps, for example. So don't store state between them!
     *
     * Regarding other entries: based on the previous statement, this class 
     * said it required them to import this entry, so they should be necessary 
     * to complete the import of the entry. Alternatively, perhaps you 
     * recognise that importing them makes no sense when you import this entry. 
     * But be aware that your class is denying these entries to other classes 
     * if you do this!
     *
     * This method should return a list of entry ID => (list of artefact IDs):
     *
     * array(
     *     [entryid:string] => array([artefactid:int], [artefactid:int], ...),
     *     [entryid:string] => array([artefactid:int], [artefactid:int], ...),
     *     ...
     * )
     *
     * This list informs the importer of how each entry was converted into 
     * artefact(s). Often, an entry will be converted into just one artefact, 
     * but there's no reason why it might not be convereted into more.
     *
     * This information is used by setup_relationships() hooks to work out how 
     * entries were converted to artefacts, so for example, files can be 
     * attached to blog posts even though the files and blog posts were 
     * imported by different plugins.
     *
     * @param SimpleXMLElement $entry The entry to import
     * @param PluginImport $importer  The importer
     * @param int $strategy           The strategy to use (should be a class 
     *                                constant on your class, see the 
     *                                documentation of get_import_strategies_for_entry
     *                                for more information)
     * @param array $otherentries     A list of entry IDs that this class 
     *                                previously said were required to import 
     *                                the entry
     * @throws ImportException If the strategy is unrecognised
     * @return array A list describing what artefacts were created by the 
     *               import of each entry
     */
    abstract public static function import_using_strategy(SimpleXMLElement $entry, PluginImport $importer, $strategy, array $otherentries);

    /**
     * Gives plugins a chance to import author data
     */
    public static function import_author_data(PluginImport $importer, $persondataid) {
    }

    /**
     * Gives plugins a chance to construct relationships between the newly 
     * created artefacts.
     *
     * This hook is optional. If implemented, plugins get access to the entries 
     * they imported, and the strategy they used to import them. It is 
     * guaranteed that all other plugins have created the artefacts they wanted 
     * to create, and implementors of this hook can use 
     * $importer->get_artefactids_imported_by_entryid to get access to the 
     * artefacts they need.
     *
     * This method has no return value.
     *
     * @param SimpleXMLElement $entry The entry previously imported
     * @param PluginImport $importer  The importer
     * @param int $strategy           The strategy to use (should be a class 
     *                                constant on your class, see the 
     *                                documentation of get_import_strategies_for_entry
     *                                for more information)
     * @param array $otherentries     A list of entry IDs that this class 
     *                                previously said were required to import 
     *                                the entry
     * @throws ImportException If the strategy is unrecognised
     */
    public static function setup_relationships(SimpleXMLElement $entry, PluginImport $importer, $strategy, array $otherentries) {
    }

}

?>
