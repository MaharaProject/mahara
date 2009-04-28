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
 * @subpackage export-leap
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/**
* LEAP export plugin.  See http://wiki.cetis.ac.uk/LEAP2A_specification
*/
class PluginExportLeap extends PluginExport {

    /**
    * xml string to build up.  Maybe later
    * this could change to an open file buffer
    * for performance.
    */
    protected $xml;

    /**
    * smarty object for main xml object
    * at the moment individual entries have their own
    * as well, because I can't figure out whether to unset
    * each entry template vars each time, or to create a new one
    * is more performant.
    */
    protected $smarty;

    /**
    * array of attachment objects
    * like this: (object)array('file' => '/path/to/file', 'name' => 'something.jpg');
    */
    protected $attachments = array();

    /**
    * filename to use for the feed
    */
    protected $leapfile    = 'leap2a.xml';

    /**
    * attachment directory for files
    */
    protected $filedir     = 'files/';

    /**
    * name of resultant zipfile
    */
    protected $zipfile;

    /**
    * special cases - artefact plugins that want to override stuff PER PLUGIN
    * rather than per type or per instance.
    */
    protected $specialcases = array();

    /**
    * extra person data injected by plugins other than internal
    */
    protected $extrapersondata = array();

    /**
    * constructor.  overrides the parent class
    * to set up smarty and the attachment directory
    */
    public function __construct(User $user, $views, $artefacts, $progresshandler=null) {
        parent::__construct($user, $views, $artefacts, $progresshandler);
        $this->smarty = smarty_core();

        if (!check_dir_exists($this->exportdir . '/' . $this->filedir)) {
            throw new SystemException("Couldn't create the temporary export directory $this->exportdir");
        }
        $this->zipfile = 'mahara-export-leap-user'
            . $this->get('user')->get('id') . '-' . $this->exporttime . '.zip';
        // some plugins might want to do their own special thing
        foreach (plugins_installed('artefact', true) as $plugin) {
            $plugin = $plugin->name;
            if (safe_require('export', 'leap/' . $plugin, 'lib.php', 'require_once', true)) {
                $classname = 'LeapExport' . ucfirst($plugin);
                if (class_exists($classname) && call_static_method($classname, 'override_entire_export')) {
                    $this->specialcases[$plugin] = array();
                }
            }
        }
        $this->notify_progress_callback(15, 'Setup complete');
    }

    public static function get_title() {
        return get_string('title', 'export.leap');
    }

    public static function get_description() {
        return get_string('description', 'export.leap');
    }

    /**
    * main export routine
    */
    public function export() {
        // the xml stuff
        $this->export_header();
        $this->notify_progress_callback(20, 'Exporting Views');
        $this->export_views();
        $this->notify_progress_callback(30, 'Exporting artefacts');
        $this->export_artefacts();

        $this->notify_progress_callback(70, 'Exporting artefact plugin data');
        $internal = null;
        foreach ($this->specialcases as $plugin => $artefacts) {
            if ($plugin == 'internal') {
                $internal = $artefacts;
                continue; // do it last so other plugins can inject persondata
            }
            $classname = 'LeapExport' . ucfirst($plugin);
            $pluginexport = new $classname($this, $artefacts);
            $this->xml .= $pluginexport->get_export_xml();
        }

        if (!empty($internal)) {
            $pluginexport = new LeapExportInternal($this, $internal);
            $this->xml .= $pluginexport->get_export_xml();
        }
        $this->notify_progress_callback(75, 'Exporting footer');

        $this->export_footer();
        $this->notify_progress_callback(80, 'Writing files');

        // write out xml to a file
        if (!file_put_contents($this->exportdir . $this->leapfile, $this->xml)) {
            throw new SystemException("Couldn't write LEAP data to the file");
        }

        // copy attachments over
        foreach ($this->attachments as $id => $fileinfo) {
            $existingfile = $fileinfo->file;
            $desiredname  = $fileinfo->name;
            copy($existingfile, $this->exportdir . $this->filedir . $id . '-' . $desiredname);
        }
        $this->notify_progress_callback(85, 'Creating zipfile');

        // zip everything up
        $cwd = getcwd();
        $command = sprintf('%s %s %s %s %s',
            get_config('pathtozip'),
            get_config('ziprecursearg'),
            escapeshellarg($this->exportdir .  $this->zipfile),
            escapeshellarg($this->leapfile),
            escapeshellarg($this->filedir)
        );
        $output = array();
        chdir($this->exportdir);
        exec($command, $output, $returnvar);
        chdir($cwd);
        if ($returnvar != 0) {
            throw new SystemException('Failed to zip the export file: return code ' . $returnvar);
        }
        $this->notify_progress_callback(100, 'Done');
        return $this->zipfile;
    }

    public function cleanup() {
        // @todo remove temporary files and directories
        // @todo maybe move the zip file somewhere else - like to files/export or something
    }
    /**
    * create the feed header and author info
    */
    private function export_header() {
        $this->smarty->assign('userid', $this->get('user')->get('id'));
        $this->smarty->assign('name', full_name($this->get('user')));
        $this->smarty->assign('email', $this->get('user')->get('email'));
        $this->smarty->assign('export_time', $this->exporttime);
        $this->smarty->assign('export_time_rfc3339', PluginExportLeap::format_rfc3339_date($this->exporttime));
        require(get_config('docroot') . 'export/leap/version.php');
        $this->smarty->assign('leap_export_version', $config->version);
        $this->xml .= $this->smarty->fetch('export:leap:header.tpl');
    }

    /**
    * export all the views
    * @todo later
    */
    private function export_views() {
        foreach ($this->get('views') as $view) {
            $this->smarty->assign('title', $view->get('title'));
            $this->smarty->assign('id', 'portfolio:view' . $view->get('id'));
            $this->smarty->assign('updated', self::format_rfc3339_date(strtotime($view->get('mtime'))));
            $this->smarty->assign('created', self::format_rfc3339_date(strtotime($view->get('ctime'))));
            // TODO this is wrong - view description is HTML, summary should be text
            //$this->smarty->assign('summary', $view->get('description'));
            $this->smarty->assign('contenttype', 'xhtml');
            $this->smarty->assign('content', $view->build_columns());
            $this->smarty->assign('type', 'selection');
            $this->xml .= $this->smarty->fetch("export:leap:entry.tpl");
        }
    }

    /**
    * export all the artefacts
    */
    private function export_artefacts() {
        $progressstart = 30;
        $progressend   = 70;
        $artefacts     = $this->get('artefacts');
        $artefactcount = count($artefacts);
        $i = 0;
        foreach ($artefacts as $artefact) {
            if ($i++ % 10 == 1) {
                $percent = intval($progressstart + ($i / $artefactcount) * ($progressend - $progressstart));
                $this->notify_progress_callback($percent, "Exporting artefacts: $i/$artefactcount");
            };
            $element = null;
            // go see if we have to do anything special for this artefact type.
            try {
                safe_require('export', 'leap/' . $artefact->get_plugin_name());
                $classname = 'LeapExportElement' . ucfirst($artefact->get('artefacttype'));
                if (class_exists($classname)) {
                    $element = new $classname($artefact, $this);
                }
            }
            catch (Exception $e) { }// overriding this is not required.

            if (is_null($element)) {
                $element = new LeapExportElement($artefact, $this);
            }
            if (array_key_exists($artefact->get_plugin_name(), $this->specialcases) && !$element->override_plugin_specialcase()) {
                $this->specialcases[$artefact->get_plugin_name()][] = $artefact;
                continue;
            }
            if (!$element->is_leap()) {
                continue;
            }
            $element->add_attachments();
            $element->assign_smarty_vars();
            $this->xml .= $element->get_export_xml();
        }
    }

    /**
    * somewhat hacky way for other plugins to inject data into persondata
    * which is what artefact/internal creates
    *
    * @param array $data array of of keyed arrays. required keys are:
    *                     artefacttype, artefactplugin field, label,  value.
    *                     optional keys are service, displayorder and mahara (non leap persondata)
    */
    public function inject_persondata($data) {
        $this->extrapersondata = array_merge($this->extrapersondata, $data);
    }

    /**
    * export the feed footer
    */
    private function export_footer() {
        $this->xml .= $this->smarty->fetch('export:leap:footer.tpl');
    }


    /**
    * entry point for adding attachments into this export
    * USE THIS FUNCTION, and keep the return variable for the filename
    *
    * @param string $filepath path to file to add
    * @param string $newname proper resulting filename
    *
    * @return filename string use this to substitute into <content src="">
    */
    public function add_attachment($filepath, $newname) {
        if (!file_exists($filepath)) {
            throw new ParamOutOfRangeException("Tried to add non existant file $filepath");
        }
        if (empty($newname)) {
            throw new ParamOutOfRangeException("Tried to add non existant file $filepath");
        }
        $this->attachments[] = (object)array('file' => $filepath, 'name' => $newname);
        return (count($this->attachments) -1) . '-' . $newname;
    }

    /**
     * format a date to the w3 datetime format
     *
     * @param integer unix timestamp to format
     * @return string W3 Date format
     */
    public static function format_rfc3339_date($date) {
        $d = format_date($date, 'strftimew3cdatetime');
        return substr($d, 0, -2) . ':' . substr($d, -2);
    }
}

/**
* LEAP Element class
* one per entry
*/
class LeapExportElement {

    /**
    * the artefact this element represents
    */
    protected $artefact;

    /**
    * the exporter object
    */
    protected $exporter;

    /**
    * smarty object to assign variables to
    */
    protected $smarty;

    /**
    * the links this element has to other elements
    */
    protected $links;

    /**
    * constructor.
    *
    * @param ArtefactType $artefact artefact this element represents
    * @param LeapExporter $exporter the exporter object
    */
    public function __construct(ArtefactType $artefact, LeapExporter $exporter) {
        $this->artefact = $artefact;
        $this->exporter = $exporter;
        $this->smarty   = smarty_core();
    }

    /**
    * Return the xml for this element
    *
    * @return string XML
    */
    public function get_export_xml() {
        return $this->smarty->fetch($this->get_template_path());
    }

    /**
    * assign the smarty vars used in this template
    */
    public function assign_smarty_vars() {
        $this->smarty->assign('artefacttype', $this->artefact->get('artefacttype'));
        $this->smarty->assign('artefactplugin', $this->artefact->get_plugin_name());
        $this->smarty->assign('title', $this->artefact->get('title'));
        $this->smarty->assign('id', 'portfolio:artefact' . $this->artefact->get('id'));
        $this->smarty->assign('updated', PluginExportLeap::format_rfc3339_date($this->artefact->get('mtime')));
        $this->smarty->assign('created', PluginExportLeap::format_rfc3339_date($this->artefact->get('ctime')));
        // these are the ones we really need to override
        $this->add_links();
        $this->smarty->assign('content', $this->get_content());
        $this->smarty->assign('type', $this->get_leap_type());

        if ($tags = $this->artefact->get('tags')) {
            $tags = array_map(create_function('$a',
                'return array(
                    \'term\' => LeapExportElement::normalise_tag($a),
                    \'label\' => $a
                );'), $tags);
        }
        if (!$categories = $this->get_categories()) {
            $categories = array();
        }
        $this->smarty->assign('categories', array_merge($tags, $categories));
        $this->smarty->assign('links', $this->links);
    }

    /**
    * add a link to a view
    * uses get_view_relationship to figure out which LEAP relationship to use
    * use this function, as it makes sure the view we're linking to is included
    * in the feed.
    *
    * @param View $view to link to
    */
    public function add_view_link(View $view) {
        if (array_key_exists($view->id, $this->exporter->get('views'))) {
            $this->add_generic_link('view' . $view->id, $this->get_view_relationship($view));
        }
    }

    /**
    * add a link to another artefact
    * use this function, as it makes sure the artefact we're linking to is included
    * in the feed.
    *
    * @param ArtefactType $artefact artefact to link to
    * @param string rel the LEAP relationship to use
    */
    public function add_artefact_link(ArtefactType $artefact, $rel) {
        if (array_key_exists($artefact->get('id'), $this->exporter->get('artefacts'))) {
            $this->add_generic_link('artefact' . $artefact->get('id'), $rel);
        }
    }

    /**
    * Adds a link to this element that isn't necessarily to a view or artefact
    *
    * @param string $id id to link to, not including portfolio: ns
    *                                  eg resumecomposite6
    * @param string $rel the LEAP relationship to use
    * @param keyed array $extras any extra bits to go in (eg display_order => 1)
    */
    public function add_generic_link($id, $rel, $extras=null) {
        if ($rel != 'relation') {
            $rel = 'leap:' . $rel;
        }
        $link = array(
            'id'   => 'portfolio:' . $id,
            'type' => $rel,
        );
        if (is_array($extras)) {
            $link = array_merge($extras, $link);
        }
        $this->links['portfolio:' . $id] = (object)$link;
    }

    /**
    * Add links to other artefacts and views
    * By default just the parents, children, and views.
    * You can override this to add extra links, eg files/blogposts
    *
    * The resulting array is keyed on the LEAP portfolio:id (eg portfolio:artefact2)
    */
    public function add_links() {
        if ($views = $this->artefact->get_views_metadata()) {
            foreach ($views as $view) {
                $this->add_view_link($view);
            }
        }
        if ($parent = $this->artefact->get_parent_instance()) {
            $this->add_artefact_link($parent, $this->get_parent_relationship($parent));
        }
        if ($children = $this->artefact->get_children_instances()) {
            foreach ($children as $child) {
                $this->add_artefact_link($child, $this->get_child_relationship($child));
            }
        }
    }

    /**
    * Path to main entry template.
    * this can be overridden per artefact plugin... eg
    * export:leap/file:imageentry.tpl
    * export:leap/plugin:artefacttypeentry.tpl
    *
    * @return string
    */
    public function get_template_path() {
        return 'export:leap:entry.tpl';
    }

    /**
    * The LEAP element type
    * See http://wiki.cetis.ac.uk/2009-03/LEAP2A_types
    *
    * @return string
    */
    public function get_leap_type() {
        return 'entry'; // default base type that everything inherits from
    }

    /**
    * The main content of the element.
    * Goes between <content> tags.
    * The default is the artefact description
    * But this can be an XHTML representation
    *
    * @return XHTML string
    */
    public function get_content() {
        //TODO replace this with non-js content
        // can use $this->artefact->get('description'); in most cases to avoid this (for testing) (and the appropriate |escape in the entry template content tag)
        $rendered = $this->artefact->render_base(array());
        $rendered = $rendered['html'];
        return $this->replace_content_placeholders(clean_html($rendered));
    }

    public function replace_content_placeholders($content, $placeholder='ARTEFACTVIEWLINK') {
        $pattern = '/href="' . $placeholder . '\/\d+/';
        $offset = 0;
        $matches = array();
        $result = '';
        while (preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE, $offset) === 1) {
            $matches = $matches[0];
            $newoffset = $matches[1];
            $result .= substr($content, $offset, ($newoffset - $offset));

            $bits = explode('/', $matches[0]);
            $entryid = $bits[1];
            $id = 'artefact' . $entryid;
            if (!array_key_exists('portfolio:' . $id, $this->links)) {
                log_warn("Tried to link to non-related item with id $id");
                $result .= 'href="' . $entryid;
            }
            else {
                $rel = $this->links['portfolio:' . $id]->type;
                $result .= 'src="portfolio:' . $id . '" rel="' . $rel . '" href="portfolio:' . $id;
            }
            $offset = $newoffset + strlen($matches[0]);
            $matches = array();
        }
        $result .= substr($content, $offset);
        return $result;
    }

    /**
    * The relationship this artefact has to a view.
    * Almost always is_part_of, but could also be supports or anything else.
    *
    * @return string
    */
    public function get_view_relationship(View $view) {
        return 'is_part_of';
    }

    /**
    * The relationship this artefact has to the given child.
    * By default, has_part
    *
    * @return string
    */
    public function get_child_relationship(ArtefactType $child) {
        return 'has_part';
    }

    /**
    * The relationship this artefact has to the given parent.
    * By default, is_part_of
    *
    * @return string
    */
    public function get_parent_relationship(ArtefactType $parent) {
        return 'is_part_of';
    }

    /**
    * Add the attachments this element has to the export
    * Use the add_attachment method on the exporter object
    * For each attachments
    */
    public function add_attachments() { }

    /**
    * If the entire plugin overrides export
    * but there are individual artefact types that should be exported
    * override this to return true.
    *
    * For example, internal gets overridden to do persondata
    * but industry, introduction & occupation are entries in their own right
    */
    public function override_plugin_specialcase() {
        return false;
    }

    /**
    * is this element even a LEAP element.
    * return false to have this artefact skipped
    * (this is essentially opt-out for artefact types)
    */
    public function is_leap() {
        return true;
    }

    public function get_categories() {
        return array();
    }

    /**
    * Getter
    *
    * @param String key
    */
    public function get($field) {
        if (!property_exists($this, $field)) {
            throw new ParamOutOfRangeException("Field $field wasn't found in class " . get_class($this));
        }
        return $this->{$field};
    }

    /**
     * Converts a tag to a 'normalised' tag, as per 
     * http://wiki.cetis.ac.uk/2009-03/LEAP2A_categories#Plain_tags
     *
     * The method of normalisation isn't specified at
     * the time of this being written.
     */
    public static function normalise_tag($tag) {
        $tag = preg_replace('#[^a-zA-Z0-9-]#', '-', $tag);
        $tag = preg_replace('#\-{2,}#', '-', $tag);
        return $tag;
    }
}

/**
* Class to extend for entire plugin exports
*
* when each artefact isn't just an ordinary entry
* eg internal & resume
*/
abstract class LeapExportArtefactPlugin {

    protected $exporter;
    protected $artefacts;

    public function __construct(LeapExporter $exporter, array $artefacts) {
        $this->exporter = $exporter;
        $this->artefacts = $artefacts;
    }

    /**
    * contract method used to detect whether the entire plugin should override the export
    * here for consistency but there's really no point overriding this to return false
    * the existance of a subclass kind of implies overriding.
    */
    public static function override_entire_export() {
        return true;
    }

    /**
    * export xml for the subclass.
    *
    * @param LeapExporter $exporter the exporter object. Can be used to fetch smarty object.
    * @param array $artefacts the array of selected artefacts that belong to this plugin
    *
    * @return XML string
    */
    abstract public function get_export_xml();

}

?>
