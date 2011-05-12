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
 * @subpackage export-leap
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/**
* LEAP export plugin.  See http://wiki.cetis.ac.uk/2009-03/Leap2A_specification and
* http://wiki.mahara.org/Developer_Area/Import%2f%2fExport/LEAP_Export
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

        $outputfilter = LeapExportOutputFilter::singleton();
        $outputfilter->set_artefactids(array_keys($this->artefacts));

        $this->notify_progress_callback(5, get_string('setupcomplete', 'export'));
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
        $this->setup_links();
        $this->notify_progress_callback(10, get_string('exportingviews', 'export'));
        $this->export_collections();
        $this->export_views();
        $this->notify_progress_callback(50, get_string('exportingartefacts', 'export'));
        $this->export_artefacts();

        $this->notify_progress_callback(80, get_string('exportingartefactplugindata', 'export'));
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
        $this->notify_progress_callback(85, get_string('exportingfooter', 'export'));

        $this->export_footer();
        $this->notify_progress_callback(90, get_string('writingfiles', 'export'));

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
        $this->notify_progress_callback(95, get_string('creatingzipfile', 'export'));

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
        $this->notify_progress_callback(100, get_string('Done', 'export'));
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
     * Export the collections
     */
    private function export_collections() {
        foreach ($this->collections as $id => $collection) {
            $this->smarty->assign('title',       $collection->get('name'));
            $this->smarty->assign('id',          'portfolio:collection' . $id);
            $this->smarty->assign('updated',     self::format_rfc3339_date(strtotime($collection->get('mtime'))));
            $this->smarty->assign('created',     self::format_rfc3339_date(strtotime($collection->get('ctime'))));
            $this->smarty->assign('summarytype', 'text');
            $this->smarty->assign('summary',     $collection->get('description'));
            $this->smarty->assign('contenttype', 'text');
            $this->smarty->assign('content',     $collection->get('description'));
            $this->smarty->assign('leaptype',    'selection');

            $this->smarty->assign('categories', array(
                array(
                    'scheme' => 'selection_type',
                    'term' => 'Website',
                )
            ));

            $links = array();
            if (!empty($this->links->collectionview[$id])) {
                foreach (array_keys($this->links->collectionview[$id]) as $viewid) {
                    $links[] = (object)array(
                        'type' => 'has_part',
                        'id'   => 'portfolio:view' . $viewid,
                    );
                }
            }
            $this->smarty->assign('links', $links);
            $this->xml .= $this->smarty->fetch("export:leap:entry.tpl");
        }
    }


    /**
     * Export the views
     */
    private function export_views() {
        $layouts = get_records_assoc('view_layout');

        $progressstart = 10;
        $progressend   = 50;
        $views = $this->get('views');
        $viewcount = count($views);
        $i = 0;
        foreach ($views as $view) {
            $percent = intval($progressstart + ($i++ / $viewcount) * ($progressend - $progressstart));
            $this->notify_progress_callback($percent, get_string('exportingviewsprogress', 'export', $i, $viewcount));

            $config = $this->rewrite_artefact_ids($view->export_config('leap'));
            $this->smarty->assign('title',       $config['title']);
            $this->smarty->assign('id',          'portfolio:view' . $view->get('id'));
            $this->smarty->assign('updated',     self::format_rfc3339_date(strtotime($view->get('mtime'))));
            $this->smarty->assign('created',     self::format_rfc3339_date(strtotime($view->get('ctime'))));
            $content = $config['description'];
            if ($newcontent = self::parse_xhtmlish_content($content)) {
                $this->smarty->assign('summarytype', 'xhtml');
                $this->smarty->assign('summary',     $newcontent);
            } else {
                $this->smarty->assign('summarytype', 'text');
                $this->smarty->assign('summary',     $content);
            }
            $this->smarty->assign('contenttype', 'xhtml');
            if ($viewcontent = self::parse_xhtmlish_content($view->build_columns(), $view->get('id'))) {
                $this->smarty->assign('content', $viewcontent);
            }
            $this->smarty->assign('viewdata',    $config['columns']);
            $this->smarty->assign('layout',      $view->get_layout()->widths);
            $this->smarty->assign('type',        $config['type']);
            $ownerformat = ($config['ownerformat']) ? $config['ownerformat'] : FORMAT_NAME_DISPLAYNAME;
            $this->smarty->assign('ownerformat', $ownerformat);
            $this->smarty->assign('leaptype',    'selection');

            $tags = array();
            if ($config['tags']) {
                $tags = array_map(create_function('$a',
                    'return array(
                        \'term\' => LeapExportElement::normalise_tag($a),
                        \'label\' => $a
                    );'), $config['tags']);
            }
            $this->smarty->assign('categories', array_merge(array(
                array(
                    'scheme' => 'selection_type',
                    'term' => 'Webpage',
                )
            ), $tags));

            $this->smarty->assign('links', $this->get_links_for_view($view->get('id')));
            $this->xml .= $this->smarty->fetch("export:leap:view.tpl");
        }
    }


    // Some links can be determined in advance
    private function setup_links() {
        if (empty($this->views) || empty($this->artefacts)) {
            return;
        }

        $viewlist = join(',', array_keys($this->views));
        $artefactlist = join(',', array_keys($this->artefacts));

        // Views in collections
        if ($this->collections) {
            $collectionlist = join(',', array_keys($this->collections));
            $records = get_records_select_array(
                'collection_view',
                "view IN ($viewlist) AND collection IN ($collectionlist)"
            );
            if ($records) {
                foreach ($records as &$r) {
                    $this->links->collectionview[$r->collection][$r->view] = 1;
                    $this->links->viewcollection[$r->view][$r->collection] = 1;
                }
            }
        }

        // Artefacts directly in views
        $records = get_records_select_array(
            'view_artefact',
            "view IN ($viewlist) OR artefact IN ($artefactlist)"
        );
        if ($records) {
            foreach ($records as &$r) {
                $this->links->viewcontents[$r->view][$r->artefact] = 1;
                $this->links->artefactinview[$r->artefact][$r->view] = 1;
            }
        }

        // Artefact parent-child relationships
        $records = get_records_select_array(
            'artefact',
            "parent IN ($artefactlist) AND id IN ($artefactlist)",
            array(),
            '',
            'id,parent'
        );
        if ($records) {
            foreach ($records as &$r) {
                $this->links->children[$r->parent][$r->id] = 1;
                $this->links->parent[$r->id] = $r->parent;
            }
        }

        // Artefact-attachment relationships
        $records = get_records_select_array(
            'artefact_attachment',
            "artefact IN ($artefactlist) AND attachment IN ($artefactlist)"
        );
        if ($records) {
            foreach ($records as &$r) {
                $this->links->attachments[$r->artefact][$r->attachment] = 1;
            }
        }

        // Other leap2a relationships
        $this->links->viewartefact = array();
        $this->links->artefactview = array();
        $this->links->artefactartefact = array();
        foreach (require_artefact_plugins() as $plugin) {
            safe_require('export', 'leap/' . $plugin->name, 'lib.php', 'require_once', true);
        }
        foreach (plugins_installed('artefact') as $plugin) {
            $classname = 'LeapExportElement' . ucfirst($plugin->name);
            if (is_callable($classname . '::setup_links')) {
                // You must explicitly pass variables by reference when calling
                // call_user_func, or else they get copied automatically.
                // Using a dummy variable here to avoid the "Call time pass by reference
                // is deprecated" warning that php displays on the screen.
                $dummyref =& $this->links;
                call_user_func(
                    array($classname, 'setup_links'),
                    $dummyref,
                    array_keys($this->views),
                    array_keys($this->artefacts)
                );
            }
        }
    }

    public function artefact_in_view_links($artefactid) {
        if (isset($this->links->artefactinview[$artefactid])) {
            return array_keys($this->links->artefactinview[$artefactid]);
        }
    }

    public function artefact_parent_link($artefactid) {
        if (isset($this->links->parent[$artefactid])) {
            return $this->artefacts[$this->links->parent[$artefactid]];
        }
    }

    public function artefact_child_links($artefactid) {
        if (isset($this->links->children[$artefactid])) {
            return array_intersect_key($this->artefacts, $this->links->children[$artefactid]);
        }
    }

    public function artefact_attachment_links($artefactid) {
        if (isset($this->links->attachments[$artefactid])) {
            return array_intersect_key($this->artefacts, $this->links->attachments[$artefactid]);
        }
    }

    public function artefact_artefact_links($artefactid) {
        if (isset($this->links->artefactartefact[$artefactid])) {
            return $this->links->artefactartefact[$artefactid];
        }
    }

    public function artefact_view_links($artefactid) {
        if (isset($this->links->artefactview[$artefactid])) {
            return $this->links->artefactview[$artefactid];
        }
    }

    /**
     * Looks at all blockinstance configurations, and rewrites the artefact IDs
     * found to be IDs in the generated export.
     *
     * This only works for the 'artefactid' and 'artefactids' fields, which is
     * somewhat of a limitation, as it makes it hard for blocks that want to
     * store artefact ids in other configdata fields. We might have to address
     * this limitation later.
     */
    private function rewrite_artefact_ids($config) {
        foreach ($config['columns'] as &$column) {
            foreach ($column as &$blockinstance) {
                if (isset($blockinstance['config']['artefactid'])) {
                    $id = json_decode($blockinstance['config']['artefactid']);
                    if ($id[0] != null) {
                        $blockinstance['config']['artefactid'] = json_encode(array('portfolio:artefact' . $id[0]));
                    }
                    else {
                        $blockinstance['config']['artefactid'] = null;
                    }
                }
                else if (isset($blockinstance['config']['artefactids'])) {
                    $ids = json_decode($blockinstance['config']['artefactids']);
                    $blockinstance['config']['artefactids'] = json_encode(array(array_map(array($this, 'prepend_artefact_identifier'), $ids[0])));
                }
            }
        }
        return $config;
    }

    private function prepend_artefact_identifier($artefactid) {
        return 'portfolio:artefact' . $artefactid;
    }

    private function get_links_for_view($viewid) {
        $links = array();

        if (!empty($this->links->viewcollection[$viewid])) {
            foreach (array_keys($this->links->viewcollection[$viewid]) as $collectionid) {
                $links[] = (object)array(
                    'type' => 'is_part_of',
                    'id'   => 'portfolio:collection' . $collectionid,
                );
            }
        }

        if (!empty($this->links->viewcontents[$viewid])) {
            foreach (array_keys($this->links->viewcontents[$viewid]) as $artefactid) {
                $links[] = (object)array(
                    'type' => 'leap2:has_part',
                    'id'   => 'portfolio:artefact' . $artefactid,
                );
            }
        }

        if (!empty($this->links->viewartefact[$viewid])) {
            foreach ($this->links->viewartefact[$viewid] as $artefactid => $linktypes) {
                foreach ($linktypes as $linktype) {
                    $links[] = (object)array(
                        'type' => $linktype,
                        'id'   => 'portfolio:artefact' . $artefactid,
                    );
                }
            }
        }

        return $links;
    }

    /**
     * Export the artefacts
     */
    private function export_artefacts() {
        $progressstart = 50;
        $progressend   = 80;
        $artefacts     = $this->get('artefacts');
        $artefactcount = count($artefacts);
        $i = 0;
        foreach ($artefacts as $artefact) {
            if ($i++ % 3 == 0) {
                $percent = intval($progressstart + ($i / $artefactcount) * ($progressend - $progressstart));
                $this->notify_progress_callback($percent, get_string('exportingartefactsprogress', 'export', $i, $artefactcount));
            }
            $element = null;
            // go see if we have to do anything special for this artefact type.
            if (safe_require('export', 'leap/' . $artefact->get_plugin_name(), 'lib.php', 'require_once', true)) {
                $classname = 'LeapExportElement' . ucfirst($artefact->get('artefacttype'));
                if (class_exists($classname)) {
                    $element = new $classname($artefact, $this);
                }

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
    * @return filename string use this to pass to add_enclosure_link
    */
    public function add_attachment($filepath, $newname) {
        if (!file_exists($filepath) || empty($newname)) {
            throw new ParamOutOfRangeException(get_string('nonexistentfile', 'export', $newname));
        }
        $newname = substr(str_replace('/', '_', $newname), 0, 245);
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


    /**
     * given some content that might be html or xhtml, try to coerce it to xhtml and return it.
     *
     * @param string $content some html or xhtmlish content
     *
     * @return xhtml content or false for unmodified text content
     */
    public static function parse_xhtmlish_content($content, $viewid=null) {
        $dom = new DomDocument();
        $topel = $dom->createElement('tmp');
        $tmp = new DomDocument();
        if (strpos($content, '<') === false && strpos($content, '>') === false) {
            return false;
        }
        if (@$tmp->loadXML('<div>' . $content . '</div>')) {
            $topel->setAttribute('type', 'xhtml');
            $content = $dom->importNode($tmp->documentElement, true);
            $content->setAttribute('xmlns', 'http://www.w3.org/1999/xhtml');
            $topel->appendChild($content);
            // if that fails, it could still be html
            // DomDocument::loadHTML() parses the input as iso-8859-1 if no
            // encoding is declared. Since we are only loading a HTML fragment
            // there is no  encoding declared which results in garbled output
            // since the content is actually in utf-8. To work around this
            // we force the encoding by appending an xml declaration.
            // see http://php.net/manual/de/domdocument.loadhtml.php#95251
        } else if (@$tmp->loadHTML('<?xml encoding="UTF-8"><div>' . $content . '</div>')) {
            $xpath = new DOMXpath($tmp);
            $elements = $xpath->query('/html/body/div');
            if ($elements->length != 1) {
                if ($viewid) {
                    log_warn("Leap2a export: invalid html found in view $viewid");
                }
                if ($elements->length < 1) {
                    return false;
                }
            }
            $ourelement = $elements->item(0);
            $content = $dom->importNode($ourelement, true);
            $content->setAttribute('xmlns', 'http://www.w3.org/1999/xhtml');
            $topel->appendChild($content);
        } else {
            return false; // wtf is it then?
        }
        $dom->appendChild($topel->firstChild);
        return $dom->saveXML($dom->documentElement);
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
    * @param PluginExportLeap $exporter the exporter object
    */
    public function __construct(ArtefactType $artefact=null, PluginExportLeap $exporter=null) {
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
        if (!($this->artefact instanceof ArtefactType)) {
            // If you're seeing this error, this means you have subclassed
            // LeapExportElement and are using it to represent something more
            // than just one artefact. In this case, you must override this
            // method.
            throw new ExportException($this, "LeapExportElement::assign_smarty_vars was called with null artefact. "
                . "If you are using LeapExportElement as a dummy class for exporting more than one artefact, "
                . "you must override assign_smarty_vars yourself.");
        }
        $this->smarty->assign('artefacttype', $this->artefact->get('artefacttype'));
        $this->smarty->assign('artefactplugin', $this->artefact->get_plugin_name());
        $this->smarty->assign('title', $this->artefact->get('title'));
        $this->smarty->assign('id', 'portfolio:artefact' . $this->artefact->get('id'));
        $this->smarty->assign('updated', PluginExportLeap::format_rfc3339_date($this->artefact->get('mtime')));
        $this->smarty->assign('created', PluginExportLeap::format_rfc3339_date($this->artefact->get('ctime')));
        // these are the ones we really need to override
        $this->add_links();
        $content = $this->get_content();
        // try to coerce it to xhtml
        if ($this->get_content_type() != 'text' && $newcontent = PluginExportLeap::parse_xhtmlish_content($content)) {
            $this->smarty->assign('contenttype', 'xhtml');
            $this->smarty->assign('content', $newcontent);
        } else {
            $this->smarty->assign('contenttype', 'text');
            $this->smarty->assign('content', $content);
        }
        $this->smarty->assign('leaptype', $this->get_leap_type());
        $this->smarty->assign('author', $this->get_entry_author());
        $this->smarty->assign('dates', $this->get_dates());

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
    public function add_view_link($viewid, $rel=null) {
        if (is_null($rel)) {
            $rel = $this->get_view_relationship($viewid);
        }
        if (array_key_exists($viewid, $this->exporter->get('views'))) {
            $this->add_generic_link('view' . $viewid, $rel);
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
        if (!in_array($rel, array('related', 'alternate', 'enclosure'))) {
            $rel = 'leap2:' . $rel;
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
        $id = $this->artefact->get('id');
        if ($views = $this->exporter->artefact_in_view_links($id)) {
            foreach ($views as $view) {
                $this->add_view_link($view);
            }
        }
        if ($parent = $this->exporter->artefact_parent_link($id)) {
            $this->add_artefact_link($parent, $this->get_parent_relationship($parent));
        }
        if ($children = $this->exporter->artefact_child_links($id)) {
            foreach ($children as $child) {
                $this->add_artefact_link($child, $this->get_child_relationship($child));
            }
        }
        if ($attachments = $this->exporter->artefact_attachment_links($id)) {
            foreach ($attachments as $a) {
                $this->add_artefact_link($a, 'related');
            }
        }
        if ($views = $this->exporter->artefact_view_links($id)) {
            foreach ($views as $viewid => $linktypes) {
                foreach ($linktypes as $linktype) {
                    $this->add_view_link($viewid, $linktype);
                }
            }
        }
        if ($artefacts = $this->exporter->artefact_artefact_links($id)) {
            foreach ($artefacts as $artefactid => $linktypes) {
                foreach ($linktypes as $linktype) {
                    $this->add_artefact_link($this->exporter->artefacts[$artefactid], $linktype);
                }
            }
        }
    }

    /**
     * add an enclosure link to the export
     * for where we previously used the src attribute of the content tag.
     * this does not attach the file to the expot, you have to use the
     * {@link add_attachment} method on the exporter object.
     *
     * @param string $filename the relative path of the file (NOT including the filesdir)
     * @param string $mimetype the (optional) mimetype of the file (according to atom
     *                          spec the type attribute on an enclosure is optional)
     */
    public function add_enclosure_link($filename, $mimetype = '') {
        $this->links[$filename] = (object)array(
            'id' => $this->exporter->get('filedir') . $filename,
            'type' => 'enclosure',
            'file' => true,
            'mimetype' => $mimetype
        );
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
    * See http://wiki.cetis.ac.uk/2009-03/Leap2A_types
    *
    * @return string
    */
    public function get_leap_type() {
        return 'entry'; // default base type that everything inherits from
    }

    /**
     * The content type of whatever is going in the <content> element.
     *
     * Can only be one of 'text', 'html' or 'xhtml', and we're currently not 
     * using XHTML in Mahara.
     *
     * @return string
     */
    public function get_content_type() {
        return 'text';
    }


    /**
    * The main content of the element, which goes between <content> tags.
    *
    * Escaping of this value happens in the template, depending on the content 
    * type, which can be set with {@link get_content_type()}.
    *
    * The default is to use the artefact description.
    *
    * @return string
    */
    public function get_content() {
        switch ($this->get_content_type()) {
        case 'text':
        case 'html':
        case 'xhtml':
            return $this->artefact->get('description');
        default:
            throw new SystemException("Unrecognised content type");
        }
    }

    /**
    * The name of the entry's author
    *
    * @return string
    */
    public function get_entry_author() {
        if ($author = $this->artefact->get('author')) {
            if ($author != $this->artefact->get('owner')) {
                return display_name($author);
            }
            return;
        }
        return $this->artefact->get('authorname');
    }

    /**
    * Get leap:date items for the entry
    *
    * @return array
    */
    public function get_dates() {
        return array();
    }

    /**
    * The relationship this artefact has to a view.
    * Almost always is_part_of, but could also be supports or anything else.
    *
    * @return string
    */
    public function get_view_relationship($viewid) {
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
     * http://wiki.cetis.ac.uk/2009-03/Leap2A_categories#Plain_tags
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

    public function __construct(PluginExportLeap $exporter, array $artefacts) {
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
    * @return XML string
    */
    abstract public function get_export_xml();

}

function export_leap_rewrite_links($html) {
    $outputfilter = LeapExportOutputFilter::singleton();
    return $outputfilter->filter($html);
}
/**
 * Provides a mechanism for converting the HTML generated by views and 
 * artefacts for the LEAP export.
 *
 * This is primarily so that the content of view blocktypes and links to 
 * artefacts get rewritten to point to entries within the export.
 */
class LeapExportOutputFilter {

    private static $instance = null;

    private $artefactids = array();

    private function __construct() {
    }

    public static function singleton() {
        if (is_null(self::$instance)) {
            self::$instance = new LeapExportOutputFilter();
        }
        return self::$instance;
    }

    public function set_artefactids(array $artefactids) {
        $this->artefactids = $artefactids;
    }

    public function filter($html) {
        // Links to artefacts
        $html = preg_replace_callback(
            '#<a[^>]+href="(' . preg_quote(get_config('wwwroot')) . ')?/?view/artefact\.php\?artefact=(\d+)(&amp;view=\d+)?(&amp;page=\d+)?"([^>]*)>#',  //  ([^<]*)</a>
            array($this, 'replace_artefact_link'),
            $html
        );

        // Links to download files
        $html = preg_replace_callback(
            array(
                '#<(a[^>]+)href="(' . preg_quote(get_config('wwwroot')) . ')?/?artefact/file/download\.php\?file=(\d+)(&amp;view=\d+)?"([^>]*)>#',
                '#<(img[^>]+)src="(' . preg_quote(get_config('wwwroot')) . ')?/?artefact/file/download\.php\?file=(\d+)([^"]*)?"([^>]*)>#',
            ),
            array($this, 'replace_download_link'),
            $html
        );

        return $html;
    }

    /**
     * Callback to replace links to artefact to point to the correct entry 
     * in the LEAP export
     */
    private function replace_artefact_link($matches) {
        $artefactid = $matches[2];
        if (in_array($artefactid, $this->artefactids)) {
            return '<a rel="leap2:has_part" href="portfolio:artefact' . hsc($artefactid) . '"' . $matches[5] . '>';
        }

        // If the artefact isn't in the export, then we can't provide an 
        // export-relative link to it
        log_debug("Not providing an export-relative link for $artefactid");
        return $matches[0];
    }

    /**
     * Callback to replace links to artefact/file/download.php to point to the 
     * correct entry in the LEAP export
     */
    private function replace_download_link($matches) {
        $artefactid = $matches[3];
        if (in_array($artefactid, $this->artefactids)) {
            return '<' . $matches[1] . 'rel="leap2:has_part" href="portfolio:artefact' . hsc($artefactid) . '"' . $matches[5] . ($matches[1] == 'img' ? '/' : '') . '>';
        }

        log_debug("Not providing an export-relative link for $artefactid");
        return $matches[0];
    }

}
