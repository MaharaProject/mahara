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
 * @subpackage artefact-resume-export-leap
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

// all the special case 'composite' types are handled the same
class LeapExportElementBook extends LeapExportElementResumeComposite {}
class LeapExportElementCertification extends LeapExportElementResumeComposite {}
class LeapExportElementMembership extends LeapExportElementResumeComposite {}
class LeapExportElementEducationhistory extends LeapExportElementResumeComposite {
    public function get_categories() {
        return array_merge(parent::get_categories(), array(
            'life_area' => array(
                'scheme' => 'life_area',
                'term'   => 'Education',
            )
        ));
    }
}
class LeapExportElementEmploymenthistory extends LeapExportElementResumeComposite {
    public function get_categories() {
        return array_merge(parent::get_categories(), array(
            'life_area' => array(
                'scheme' => 'life_area',
                'term'   => 'Work',
            )
        ));
    }
}

class LeapExportElementContactinformation extends LeapExportElement {
    public function get_categories() {
        return array(
            'selection_type' => array(
                'scheme' => 'selection_type',
                'term'   => 'Grouping',
            ),
        );
    }

    public function get_leap_type() {
        return 'selection';
    }

    public function add_links() {
        $fields = ArtefactTypeContactinformation::get_profile_fields();
        foreach ($fields as $f) {
            try {
                $$f = artefact_instance_from_type($f, $this->artefact->get('owner'));
                $this->add_artefact_link($$f, 'has_part');
            }
            catch (MaharaException $e) { } // might not exist which is ok
        }
    }

    public function get_content() {
        return '';
    }

    public function is_leap() {
        return false;
    }
}

class LeapExportElementPersonalinformation extends LeapExportElement {

    public function __construct(LeapExporter $exporter, ArtefactType $artefact) {
        parent::__construct($exporter, $artefact);
        $c = $this->artefact->get('composites');
        $persondata = array();
        foreach ($c as $k => $v) {
            if ($k == 'artefact') {
                continue;
            }
            if (empty($v)) {
                continue;
            }
            $persondata[] = (object)$this->format_persondata($k, $v);
        }
        $this->exporter->inject_persondata($persondata);
    }

    private function format_persondata($key, $value) {
        $basics = array(
            'artefactplugin' => 'resume',
            'artefacttype'   => 'personalinformation/' . $key,
            'field'          => $key,
            'label'          => get_string($key, 'artefact.resume'),
            'value'          => $value,
        );
        switch ($key) {
            case 'dateofbirth':
                return array_merge($basics, array(
                    'field'          => 'dob',
                    'value'          => PluginExportLeap::format_rfc3339_date($value),
                ));
            case 'gender':
                return array_merge($basics, array(
                    'value'          => (($key == 'male') ? 1 : 2),
                ));
            default:
                return array_merge($basics, array(
                    'mahara'  => true,
                ));
        }
    }

    /*
    // remove these for now - we can't link to the persondata fields individually
    // and linking to the person entry itself makes no sense
    public function get_leap_type() {
        return 'selection';
    }

    public function get_categories() {
        return array(
            'selection_type' => array(
                'scheme' => 'selection_type',
                'term'   => 'grouping',
            ),
        );
    }
    */
    public function is_leap() {
        return false;
    }
}

class LeapExportElementResumeWysiwygField extends LeapExportElement {
    public function get_content_type() {
        return 'html';
    }
}

class LeapExportElementInterest extends LeapExportElementResumeWysiwygField {}
class LeapExportElementCoverletter extends LeapExportElementResumeWysiwygField {}
class LeapExportElementCareergoal extends LeapExportElementResumeWysiwygField {}
class LeapExportElementAcademicgoal extends LeapExportElementResumeWysiwygField {}
class LeapExportElementPersonalgoal extends LeapExportElementResumeWysiwygField {}
class LeapExportElementWorkskill extends LeapExportElementResumeWysiwygField {}
class LeapExportElementAcademicskill extends LeapExportElementResumeWysiwygField {}
class LeapExportElementPersonalskill extends LeapExportElementResumeWysiwygField {}

/**
* Base class for the composite artefacts
* Just a normal element except they return multiple <entry> elements using LeapExportElementResumeCompositeChild
*/
class LeapExportElementResumeComposite extends LeapExportElement {

    protected $composites;
    protected $children = array();

    public function __construct(ArtefactType $artefact, LeapExporter $exporter) {
        parent::__construct($artefact, $exporter);
        $this->set_composites();
    }

    public function set_composites() {
        $this->composites = get_records_array($this->artefact->get_other_table_name(), 'artefact', $this->artefact->get('id'));
    }

    public function get_leap_type() {
        return 'selection';
    }

    public function get_export_xml() {
        // also get composite children content
        $xml = '';
        foreach ($this->composites as $c) {
            $classname = 'LeapExportElementResumeCompositeChild' . $this->artefact->get('artefacttype');
            $child = new $classname($this->exporter, $this->artefact, $c);
            $xml .= $child->get_export_xml();
            if ($siblings = $child->get_siblings()) {
                foreach ($siblings as $sibling) {
                    $xml .= $sibling->get_export_xml();
                }
            }
            $this->children[$child->get_id()] = array('type' => 'has_part', 'display_order' => $c->displayorder+1); // LEAP starts at 1, we start at 0
        }
        $this->assign_smarty_vars();
        $this->add_links();
        $parentxml = parent::get_export_xml();
        return $parentxml . $xml;
    }

    public function get_content() {
        return '';

    }

    public function add_links() {
        foreach ($this->children as $childid => $reldata) {
            $type = array_shift($reldata); // shift off type and don't pass it to the helper method in extras
            $this->add_generic_link($childid, $type, $reldata);
        }
    }

    public function get_categories() {
        return array(
            'selection_type' => array(
                'scheme' => 'selection_type',
                'term'   => 'Grouping'
            ),
            'life_area' => array(
                'scheme' => 'life_area',
                'term'   => 'Development',
            )
        );
    }
}

/**
* Element to create pseudo-elements for composite children which aren't really artefacts in Mahara
* but do need to map to LEAP elements
*/
abstract class LeapExportElementResumeCompositeChild extends LeapExportElement {

    protected $entrydata;
    protected $originalrecord;
    protected $parentartefact;

    public function __construct(LeapExporter $exporter, ArtefactTypeResumeComposite $parentartefact, $child) {
        $this->originalrecord = $child;
        $this->entrydata = $this->record_to_entrydata($child);
        $this->parentartefact = $parentartefact;
        parent::__construct(null, $exporter);
        $this->assign_smarty_vars();
    }

    public function assign_smarty_vars() {
        $this->smarty->assign('artefacttype', 'pseudo:' . $this->parentartefact->get('artefacttype'));
        $this->smarty->assign('artefactplugin', 'resume');
        $this->smarty->assign('id', 'portfolio:' . $this->get_id());
        foreach ($this->entrydata as $field => $value) {
            $this->smarty->assign($field, $value);
        }
        $this->smarty->assign('leaptype', $this->get_leap_type());
        $this->smarty->assign('contenttype', 'text');
        $this->add_links();
        $this->smarty->assign('links', $this->links);
    }

    public function add_links() {
        $extras = null;
        if ($this->is_sibling()) {
            $this->add_generic_link($this->sibling->get_id(), $this->get('siblingrel'));
        } else {
            $this->add_generic_link('artefact' . $this->parentartefact->get('id'), 'is_part_of', array('display_order' => $this->originalrecord->displayorder+1));
        }
        if ($siblings = $this->get_siblings()) {
            foreach ($siblings as $rel => $sibling) {
                $this->add_generic_link($sibling->get_id(), $rel);
            }
        }
    }

    public function get_template_path() {
        return 'export:leap/resume:composite.tpl';
    }

    public function get_siblings(){
        return false;
    }

    abstract function record_to_entrydata($record);

    public function get_id() {
        return 'resumecomposite-' . $this->parentartefact->get('id') . '-child-' . $this->originalrecord->id;
    }
    public function is_sibling() {
        return false;
    }
}

// these two are the simple ones that just translate directly
class LeapExportElementResumeCompositeChildCertification extends LeapExportElementResumeCompositeChild {

    public function record_to_entrydata($record) {
        return array(
            'end'     => $record->date,
            'title'   => $record->title,
            'content' => $record->description,
        );
    }

    public function get_leap_type() {
        return 'achievement';
    }
}
class LeapExportElementResumeCompositeChildMembership extends LeapExportElementResumeCompositeChild {

    public function record_to_entrydata($record) {
        return array(
            'start'   => $record->startdate,
            'end'     => $record->enddate,
            'title'   => $record->title,
            'content' => $record->description,
        );
    }

    public function get_leap_type() {
        return 'activity';
    }
}

abstract class LeapExportElementResumeCompositeChildWithSiblings extends LeapExportElementResumeCompositeChild {

    protected $siblings;

    public function get_siblings() {
        if (!isset($this->siblings)) {
            $this->ensure_siblings();
        }
        return $this->siblings;
    }
}

// these three are harder and have siblings.
class LeapExportElementResumeCompositeChildEducationhistory extends LeapExportElementResumeCompositeChildWithSiblings {

    public function ensure_siblings() {
        $this->siblings = array(
            'is_supported_by' => new LeapExportElementResumeCompositeSibling($this->exporter, $this->parentartefact, $this, array(
                'title' => $this->originalrecord->institution,
            ), 'organisation', 'supports'),
            'supports' => new LeapExportElementResumeCompositeSibling($this->exporter, $this->parentartefact, $this, array(
                'title' => $this->originalrecord->qualtype,
                'content' => $this->originalrecord->qualname,
            ), 'achievement', 'is_supported_by')
        );
    }

    public function record_to_entrydata($record) {
        return array(
            'start'   => $record->startdate,
            'end'     => $record->enddate,
            'title'   => $record->qualname . ' (' . $record->qualtype . ')',
            'content' => $record->qualdescription,
        );
    }

    public function get_leap_type() {
        return 'activity';
    }
}

class LeapExportElementResumeCompositeChildEmploymenthistory extends LeapExportElementResumeCompositeChildWithSiblings {

    public function ensure_siblings() {
        $this->siblings = array(
            'is_supported_by' => new LeapExportElementResumeCompositeSibling($this->exporter, $this->parentartefact, $this, array(
                'title' => $this->originalrecord->employer,
            ), 'organisation', 'supports')
        );
    }

    public function record_to_entrydata($record) {
        return array(
            'start'   => $record->startdate,
            'end'     => $record->enddate,
            'title'   => $record->jobtitle,
            'content' => $record->positiondescription,
        );
    }

    public function get_leap_type() {
        return 'activity';
    }
}
class LeapExportElementResumeCompositeChildBook extends LeapExportElementResumeCompositeChildWithSiblings {

    public function ensure_siblings() {
        $this->siblings = array(
            'relation' => new LeapExportElementResumeCompositeSibling($this->exporter, $this->parentartefact, $this, array(
                'title' => $this->originalrecord->contribution
            ), 'achievement', 'relation')
        );
    }

    public function record_to_entrydata($record) {
        return array(
            'end'     => $record->date,
            'title'   => $record->title,
            'content' => $record->description,
        );
    }

    public function get_leap_type() {
        return 'resource';
    }
}

/**
* fake sibling class - just provides contract methods for fake entries to support those with siblings.
*/
class LeapExportElementResumeCompositeSibling extends LeapExportElementResumeCompositeChild {

    protected $siblingrel;
    protected $leaptype;
    protected $sibling;

    public function __construct(LeapExporter $exporter,
        ArtefactTypeResumeComposite $parentartefact,
        LeapExportElementResumeCompositeChild $sibling, $record, $leaptype, $siblingrel) {

        $this->leaptype   = $leaptype;
        $this->siblingrel = $siblingrel;
        $this->sibling    = $sibling;
        parent::__construct($exporter, $parentartefact, $record);
    }

    public function get_siblings() {
        return false;
    }

    public function record_to_entrydata($record) {
        return $record;
    }

    public function get_leap_type() {
        return $this->leaptype;
    }

    public function get_id() {
        return 'resumesibling-' . $this->parentartefact->get('id') . '-sibling-' . $this->sibling->get('originalrecord')->id . '-rel-' . $this->siblingrel;
    }

    public function is_sibling() {
        return true;
    }
}

?>
