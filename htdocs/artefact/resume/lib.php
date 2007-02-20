<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package    mahara
 * @subpackage artefact-resume
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

class PluginArtefactResume extends Plugin {
    
    public static function get_artefact_types() {
        return array(
            'coverletter', 
            'contactinformation',
            'personalinformation',
            'employmenthistory',
            'educationhistory',
            'certification',
            'book',
            'membership',
            'interest',
        );
    }

    public static function get_plugin_name() {
        return 'resume';
    }

    public static function menu_items() {
        return array(
            array(
                'name' => 'myresume',
                'link' => '',
                'submenu' => array(
                    array(
                        'name' => 'myresume', 
                        'link' => 'index.php'
                    ),
                    array(
                        'name' => 'mygoals',
                        'link' => 'goals.php',
                    ),
                    array(
                        'name' => 'myskills',
                        'link' => 'skills.php',
                    ),
                ),
            )
        );
    }

    public static function get_toplevel_artefact_types() {
        return array('resume'); 
    }
}

class ArtefactTypeResume extends ArtefactType {

    public function get_icon() {}
    
    public static function is_singular() {
        return false;
    }

    public static function format_child_data($artefact, $pluginname) {
        $a = new StdClass;
        $a->id         = $artefact->id;
        $a->isartefact = true;
        $a->title      = '';
        $a->text       = get_string($artefact->artefacttype, 'artefact.resume'); // $artefact->title;
        $a->container  = (bool) $artefact->container;
        $a->parent     = $artefact->id;
        return $a;
    }

    public static function get_links($id) {
        // @todo penny
    }
}

class ArtefactTypeCoverletter extends ArtefactTypeResume {
    
    public static function is_singular() {
        return true;
    }

    public static function get_render_list() {
        return array(
            FORMAT_ARTEFACT_LISTSELF,
            FORMAT_ARTEFACT_RENDERFULL,
            FORMAT_ARTEFACT_RENDERMETADATA,
        );
    }

    public function listself($options) {
        return array('html' => get_string('coverletter', 'artefact.resume'));
    }

    public function render_full($options) {
        return array('html' => $this->title);
    }
}

class ArtefactTypeContactinformation extends ArtefactTypeResume {

    public function get_html($editing=true) {
        $smarty = smarty();
        $fields = ArtefactTypeContactinformation::get_profile_fields();
        foreach ($fields as $f) {
            try {
                $$f = artefact_instance_from_type($f);
                $rendered = $$f->render(FORMAT_ARTEFACT_RENDERFULL, array());
                $smarty->assign($f, $rendered['html']);
            }
            catch (Exception $e) { }
        }

        $template = 'artefact:resume:fragments/contactinformation.';
        if (!empty($editing)) {
            $template .= 'editing.';
        }
        $template .= 'tpl';
        return $smarty->fetch($template);
    }

    public static function is_singular() {
        return true;
    }

    public static function setup_new($userid) {
        $artefact = new ArtefactTypeContactinformation(null, array(
            'owner' => $userid,
            'title' => get_string('contactinformation', 'artefact.resume')
        ));
        $artefact->commit();
        return $artefact;
    }

    public static function get_profile_fields() {
        static $fields = array(
            'address', 
            'town',
            'city', 
            'country', 
            'faxnumber',
            'businessnumber',
            'homenumber',
            'mobilenumber'
        );
        return $fields;
    }

    public static function get_render_list() {
        return array(
            FORMAT_ARTEFACT_LISTSELF,
            FORMAT_ARTEFACT_LISTCHILDREN,
            FORMAT_ARTEFACT_RENDERFULL,
            FORMAT_ARTEFACT_RENDERMETADATA,
        );
    }

    public function listchildren($options) {
        return array('html' => $this->get_html(false));
    }

    public function render_full($options) {
        log_debug( array('html' => $this->get_html(false)));
        return array('html' => $this->get_html(false));
    }
}

class ArtefactTypePersonalinformation extends ArtefactTypeResume {
    
    private $composites;

    public function __construct($id=0, $data=null) {
        if (empty($id)) {
            $data['title'] = get_string('personalinformation', 'artefact.resume');
        }
        parent::__construct($id, $data);
        $this->composites = ArtefactTypePersonalinformation::get_composite_fields();
        if (!empty($id)) {
            $this->composites = (array)get_record('artefact_resume_personal_information', 'artefact', $id);
        }
    }

    public function set_composite($field, $value) {
        if (!array_key_exists($field, $this->composites)) {
            throw new InvalidArgumentException("Tried to set a non existant composite, $field");
        }
        if ($this->composites[$field] == $value) {
            return true;
        }
        // only set it to dirty if it's changed
        $this->dirty = true;
        $this->mtime = time();
        if ($field == 'dateofbirth') {
            $value = db_format_timestamp($value);
        }
        $this->composites[$field] = $value;
    }   

    public function get_composite($field) {
        return $this->composites[$field];
    }

    public function commit() {
        if (empty($this->dirty)) {
            return true;
        }

        db_begin(); 

        $data = new StdClass;
        foreach ($this->composites as $field => $value) {
            $data->{$field} = $value;
        }   
        $inserting = empty($this->id);
        parent::commit();
        $data->artefact = $this->id;
        if ($inserting) {
            insert_record('artefact_resume_personal_information', $data);
        }
        else {
            update_record('artefact_resume_personal_information', $data, 'artefact');
        }

        db_commit();
    }

    public static function get_composite_fields() {
        static $composites = array(
            'dateofbirth' => '',
            'placeofbirth' => '', 
            'citizenship' => '',
            'visastatus' => '',
            'gender' => '',
            'maritalstatus' => '',
        );
        return $composites;
    }

    public static function is_singular() {
        return true;
    }

    public static function get_render_list() {
        return array(
            FORMAT_ARTEFACT_LISTSELF,
            FORMAT_ARTEFACT_LISTCHILDREN,
            FORMAT_ARTEFACT_RENDERFULL,
            FORMAT_ARTEFACT_RENDERMETADATA,
        );
    }

    public function listchildren($options) {
        $html = '';
        $link = get_config('wwwroot') . 'view/view.php?';
        if (array_key_exists('viewid', $options)) {
            $link .= 'view=' . $options['viewid'] . '&artefact=';
        }
        else {
            $link .= 'artefact=';
        }
        foreach (array_keys(ArtefactTypePersonalinformation::get_composite_fields()) as $field) {
            $html .= '<a href="' . $link . $this->id . '">'
                . get_string($field, 'artefact.resume') . '</a><br>';
        }
        return array('html' => $html);
    }

    public function render_full($options) {
        $smarty = smarty();
        $fields = array();
        foreach (array_keys(ArtefactTypePersonalInformation::get_composite_fields()) as $field) {
            $fields[get_string($field, 'artefact.resume')] = $this->get_composite($field);
        }
        $smarty->assign('fields', $fields);
        return array('html' => $smarty->fetch('artefact:resume:fragments/personalinformation.tpl'));
    }

}

class ArtefactTypeInterest extends ArtefactTypeResume {

    public static function is_singular() {
        return true;
    }

    public static function get_render_list() {
        return array(
            FORMAT_ARTEFACT_LISTSELF,
            FORMAT_ARTEFACT_RENDERFULL,
            FORMAT_ARTEFACT_RENDERMETADATA,
        );
    }

    public function listself($options) {
        return array('html' => get_string('interest', 'artefact.resume'));
    }

    public function render_full($options) {
        return array('html' => $this->title);
    }
}


abstract class ArtefactTypeResumeComposite extends ArtefactTypeResume {

    public static function get_render_list() {
        return array(
            FORMAT_ARTEFACT_LISTSELF,
            FORMAT_ARTEFACT_LISTCHILDREN,
            FORMAT_ARTEFACT_RENDERFULL,
            FORMAT_ARTEFACT_RENDERMETADATA
        );
    }

    public static function get_composite_artefact_types() {
        return array(
            'employmenthistory',
            'educationhistory',
            'certification',
            'book',
            'membership'
        );
    }

    /**
    * This function should return a snippet of javascript
    * to be plugged into a table renderer instantiation
    * it comprises the cell function definition
    */
    public static abstract function get_tablerenderer_js();

    /** 
    * This function should return an array suitable to 
    * put into the 'elements' part of a pieform array
    * to generate a form to add an instance
    */
    public static abstract function get_addform_elements();

    /**
    * This function processes the form for the composite
    * @throws Exception
    */
    public static function process_compositeform(Pieform $form, $values) {
        $a = null;
        $classname = generate_artefact_class_name($values['compositetype']);
        if (empty($values['id'])) {
            $id = 0;
        }
        $a = new $classname($id, $values);
        $a->commit();
    }

    public function commit() {
        $table = 'artefact_resume_' . $this->get('artefacttype');
        log_debug($table);
        $data = (object)$this;
        if (empty($this->id)) {
            db_begin();
            log_debug('before parent commit');
            parent::commit();
            log_debug('after parent commit');
            $data->artefact = $this->id;
            insert_record($table, $data);
            log_debug('after this commit');
            db_commit();
            log_debug('after COMMIT;');
        }
        else {
            $data->artefact = $this->id;
            db_begin();
            parent::commit();
            update_record($table, $data, 'artefact');
            db_commit();
        }
    }
}

class ArtefactTypeEmploymenthistory extends ArtefactTypeResumeComposite { 

    protected $startdate;
    protected $enddate;
    protected $employer;

    public static function get_tablerenderer_js() {
        $at = get_string('at');
        return "
                'startdate',
                'enddate',
                function (r) {
                    return TD(null, r.title + '{$at}' + r.employer);
                }
        ";
    }

    public static function get_addform_elements() {
        return array(
            'startdate' => array(
                'type' => 'calendar',
                'caloptions' => array(
                    'showsTime'      => false,
                    'ifFormat'       => '%Y/%m/%d'
                ),
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('startdate', 'artefact.resume'),
            ),
            'enddate' => array(
                'type' => 'calendar', 
                'caloptions' => array(
                    'showsTime'     => false,
                    'ifFormat'      => '%Y/%m/%d',
                ),
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('enddate', 'artefact.resume'),
            ),
            'employer' => array(
                'type' => 'text',
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('employer', 'artefact.resume'),
            ),
            'title' => array(
                'type' => 'text',
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('jobtitle', 'artefact.resume'),
            ),
            'description' => array(
                'type' => 'textarea',
                'rows' => 10,
                'cols' => 50,
                'resizable' => false,
                'title' =>  get_string('jobdescription', 'artefact.resume'),
            ),
        );
    }
}

class ArtefactTypeEducationhistory extends ArtefactTypeResumeComposite {

    protected $startdate;
    protected $enddate;
    protected $qualtype;
    protected $institution;

    public static function get_tablerenderer_js() {
        $at = get_string('at');
        return "
                'startdate',
                'enddate',
                function (r) {
                    return TD(null, r.qualtype + '{$at}' + r.institution);
                }
        ";
    }
    public static function get_addform_elements() {
        return array(
            'startdate' => array(
                'type' => 'calendar',
                'caloptions' => array(
                    'showsTime'      => false,
                    'ifFormat'       => '%Y/%m/%d'
                ),
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('startdate', 'artefact.resume'),
            ),
            'enddate' => array(
                'type' => 'calendar', 
                'caloptions' => array(
                    'showsTime'     => false,
                    'ifFormat'      => '%Y/%m/%d',
                ),
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('enddate', 'artefact.resume'),
            ),
            'institution' => array(
                'type' => 'text',
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('institution', 'artefact.resume'),
            ),
            'qualtype' => array(
                'type' => 'text',
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('qualtype', 'artefact.resume'),
            ),
            'title' => array(
                'type' => 'text',
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('qualname', 'artefact.resume'),
            ),
            'description' => array(
                'type' => 'textarea',
                'rows' => 10,
                'cols' => 50,
                'resizable' => false,
                'title' => get_string('qualdescription', 'artefact.resume'),
            ),
        );
    }
}

class ArtefactTypeCertification extends ArtefactTypeResumeComposite { 

    protected $date;

    public static function get_tablerenderer_js() {
        return "
                'date',
                'description',
        ";
    }
    public static function get_addform_elements() {
        return array(
            'date' => array(
                'type' => 'calendar',
                'caloptions' => array(
                    'showsTime'      => false,
                    'ifFormat'       => '%Y/%m/%d'
                ),
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('date', 'artefact.resume'),
            ),
            'title' => array(
                'type' => 'text',
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('title', 'artefact.resume'),
            ),
            'description' => array(
                'type' => 'textarea',
                'rows' => 10,
                'cols' => 50,
                'resizable' => false,
                'title' => get_string('description'),
            ),
        );
    }
}

class ArtefactTypeBook extends ArtefactTypeResumeComposite {

    protected $date;
    protected $contribution;

    public static function get_tablerenderer_js() {
        return "
                'date',
                'title', 
        ";
    }
    public static function get_addform_elements() {
        return array(
            'date' => array(
                'type' => 'calendar',
                'caloptions' => array(
                    'showsTime'      => false,
                    'ifFormat'       => '%Y/%m/%d'
                ),
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('date', 'artefact.resume'),
            ),
            'title' => array(
                'type' => 'text',
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('title', 'artefact.resume'),
            ),
            'contribution' => array(
                'type' => 'text',
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('contribution', 'artefact.resume'),
            ),
            'description' => array(
                'type' => 'textarea',
                'rows' => 10,
                'cols' => 50,
                'resizable' => false,
            ),
        );
    }
}

class ArtefactTypeMembership extends ArtefactTypeResumeComposite { 

    protected $date;

    public static function get_tablerenderer_js() {
        return "
                'startdate',
                'enddate',
                'title'
        ";
    }
    public static function get_addform_elements() {
        return array(
            'startdate' => array(
                'type' => 'calendar',
                'caloptions' => array(
                    'showsTime'      => false,
                    'ifFormat'       => '%Y/%m/%d'
                ),
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('startdate', 'artefact.resume'),
            ),
            'enddate' => array(
                'type' => 'calendar', 
                'caloptions' => array(
                    'showsTime'     => false,
                    'ifFormat'      => '%Y/%m/%d',
                ),
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('enddate', 'artefact.resume'),
            ),
            'title' => array(
                'type' => 'text',
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('title', 'artefact.resume'),
            ),
            'description' => array(
                'type' => 'textarea',
                'rows' => 10,
                'cols' => 50,
                'resizable' => false,
                'title' => get_string('description', 'artefact.resume'),
            ),
        );
    }
}


?>
