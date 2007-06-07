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
            'personalgoal',
            'academicgoal',
            'careergoal',
            'personalskill',
            'academicskill',
            'workskill'
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

    public function __construct($id=0, $data=array()) {
        if (empty($id)) {
            $data['title'] = get_string($this->get_artefact_type(), 'artefact.resume');
        }
        parent::__construct($id, $data);
    }
    
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

    public function __construct($id=0, $data=array()) {
        if (empty($id)) {
            $data['title'] = get_string($this->get_artefact_type(), 'artefact.resume');
        }
        parent::__construct($id, $data);
    }

    public static function get_render_list() {
        return array(
            FORMAT_ARTEFACT_LISTSELF,
            FORMAT_ARTEFACT_RENDERFULL,
            FORMAT_ARTEFACT_RENDERMETADATA,
        );
    }

    public function render_full($options) {
        return array('html' => $this->description);
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
        if ($field == 'dateofbirth' && !empty($value)) {
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
            'dateofbirth' => null,
            'placeofbirth' => null, 
            'citizenship' => null,
            'visastatus' => null,
            'gender' => null,
            'maritalstatus' => null,
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



abstract class ArtefactTypeResumeComposite extends ArtefactTypeResume {

    public static function get_render_list() {
        return array(
            FORMAT_ARTEFACT_LISTSELF,
/*            FORMAT_ARTEFACT_LISTCHILDREN, */
            FORMAT_ARTEFACT_RENDERFULL,
            FORMAT_ARTEFACT_RENDERMETADATA
        );
    }

    public static function is_singular() {
        return true;
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

    public static function get_order_field() {
        return 'startdate';
    }

    /**
    * This function should return a snippet of javascript
    * to be plugged into a table renderer instantiation
    * it comprises the cell function definition
    */
    public static abstract function get_tablerenderer_js();

    public static abstract function get_tablerenderer_title_js_string();

    public static abstract function get_tablerenderer_body_js_string();

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

        try {
            $a = artefact_instance_from_type($values['compositetype']);
            $a->set('mtime', time());
        }
        catch (Exception $e) {
            global $USER;
            $classname = generate_artefact_class_name($values['compositetype']);
            $a = new $classname(0, array(
                'owner' => $USER->get('id'),
                'title' => get_string($values['compositetype'], 'artefact.resume'),
                )
            );
        }

        $a->commit();

        foreach (array('date', 'startdate', 'enddate') as $k) {
            if (array_key_exists($k, $values)) {
                $values[$k] = db_format_timestamp($values[$k]);
            }
        }

        $values['artefact'] = $a->get('id');

        $table = 'artefact_resume_' . $values['compositetype'];
        if (!empty($values['id'])) {
            update_record($table, (object)$values, 'id');
        }
        else {
            insert_record($table, (object)$values);
        }
    }

    public function delete() {
        $table = $this->get_other_table_name();
        db_begin();

        delete_records($table, 'artefact', $this->id);
        parent::delete();

        db_commit();
    }


    /**
    * Takes a pieform that's been set up by all the 
    * subclass get_addform_elements functions
    * and puts the default values in (and hidden id field)
    * ready to be an edit form
    * 
    * @param $form pieform structure (before calling pieform() on it
    * passed by _reference_
    */
    public static function populate_form(&$form, $id, $type) {
        if (!$composite = get_record('artefact_resume_' . $type, 'id', $id)) {
            throw new InvalidArgumentException("Couldn't find composite of type $type with id $id");
        }
        $datetypes = array('date', 'startdate', 'enddate');
        foreach ($form['elements'] as $k => $element) {
            if ($k == 'submit' || $k == 'compositetype') {
                continue;
            }
            if (isset($composite->{$k})) {
                if (in_array($k, $datetypes)) {
                    $form['elements'][$k]['defaultvalue'] = strtotime($composite->{$k});
                }
                else {
                    $form['elements'][$k]['defaultvalue'] = $composite->{$k};
                }
            }
        }
        $form['elements']['id'] = array(
            'type' => 'hidden',
            'value' => $id,
        );
        $form['elements']['artefact'] = array(
            'type' => 'hidden',
            'value' => $composite->artefact,
        );
    }


    /** 
    * call the parent constructor
    * and then load up the stuff from the supporting table
    */
    public function __construct($id=0, $data=array()) {
        if (empty($id)) {
            $data['container'] = 0;
            $data['title'] = get_string($this->get_artefact_type(), 'artefact.resume');
        }
        parent::__construct($id, $data);
    }    

    /** 
    * returns the name of the supporting table
    */
    public function get_other_table_name() {
        return 'artefact_resume_' . $this->get_artefact_type();
    }

    public function render_full($options) {
        $smarty = smarty();
        $type = $this->get('artefacttype'); 
        $content = array(
            'html'         => $smarty->fetch('artefact:resume:fragments/' . $type . '.tpl'),
            'javascript'   => 
                $this->get_showhide_composite_js()
                ."
                var {$type}list = new TableRenderer(
                   '{$type}list',
                   '" . get_config('wwwroot') . "artefact/resume/composite.json.php',
                   [ 
                   " . call_static_method(generate_artefact_class_name($type), 'get_tablerenderer_js') ."
                   ]
                );
                    
                {$type}list.type = '{$type}';
                {$type}list.statevars.push('type');
                " . 
                (( array_key_exists('viewid', $options)) 
                    ? "{$type}list.view = " . $options['viewid'] . ";
                       {$type}list.statevars.push('view');"
                    : ""
                ) . "
                {$type}list.updateOnLoad();
            ");
        return $content;
    }

    static function get_tablerenderer_title_js($titlestring, $bodystring) {
        return "
                function (r, d) {
                    if (!{$bodystring}) {
                        return TD(null, {$titlestring});
                    }
                    var link = A({'href': ''}, {$titlestring});
                    connect(link, 'onclick', function (e) {
                        e.stop();
                        return showhideComposite(r, {$bodystring});
                    });
                    return TD({'id': 'composite-' + r.artefact + '-' + r.id}, link);
                },
                ";
    }

    static function get_showhide_composite_js() {
        return "
            function showhideComposite(r, content) {
                // get the reference for the title we just clicked on
                var titleTD = $('composite-' + r.artefact + '-' + r.id);
                var theRow = titleTD.parentNode;
                var bodyRow = $('composite-body-' + r.artefact +  '-' + r.id);
                if (bodyRow) {
                    if (hasElementClass(bodyRow, 'hidden')) {
                        removeElementClass(bodyRow, 'hidden');
                    }
                    else {
                        addElementClass(bodyRow, 'hidden');
                    }
                    return false;
                }
                // we have to actually create the dom node too
                var colspan = theRow.childNodes.length;
                var newRow = TR({'id': 'composite-body-' + r.artefact + '-' + r.id}, 
                    TD({'colspan': colspan}, content)); 
                insertSiblingNodesAfter(theRow, newRow);
            }
        ";
    }
}

class ArtefactTypeEmploymenthistory extends ArtefactTypeResumeComposite { 

    protected $startdate;
    protected $enddate;
    protected $employer;

    public static function get_tablerenderer_js() {
        return "
                'startdate',
                'enddate',
                " . ArtefactTypeResumeComposite::get_tablerenderer_title_js(
                    self::get_tablerenderer_title_js_string(),
                    self::get_tablerenderer_body_js_string()
                ) . "
        ";
    }

    public static function get_tablerenderer_title_js_string() {
        $at = get_string('at');
        return " r.jobtitle + ' {$at} ' + r.employer";
    }

    public static function get_tablerenderer_body_js_string() {
        return " r.positiondescription";
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
                'help'  => true,
            ),
            'enddate' => array(
                'type' => 'calendar', 
                'caloptions' => array(
                    'showsTime'     => false,
                    'ifFormat'      => '%Y/%m/%d',
                ),
                'title' => get_string('enddate', 'artefact.resume'),
                'help'  => true,
            ),
            'employer' => array(
                'type' => 'text',
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('employer', 'artefact.resume'),
                'help'  => true,
            ),
            'jobtitle' => array(
                'type' => 'text',
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('jobtitle', 'artefact.resume'),
                'help'  => true,
            ),
            'positiondescription' => array(
                'type' => 'textarea',
                'rows' => 10,
                'cols' => 50,
                'resizable' => false,
                'title' =>  get_string('jobdescription', 'artefact.resume'),
                'help'  => true,
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

        return "
                'startdate',
                'enddate',
                " . ArtefactTypeResumeComposite::get_tablerenderer_title_js(
                    self::get_tablerenderer_title_js_string(),
                    self::get_tablerenderer_body_js_string()
                ) . "
        ";
    }

    public static function get_tablerenderer_title_js_string() {
        $at = get_string('at');
        return " r.qualname + ' (' + r.qualtype + ') {$at} ' + r.institution";
    }

    public static function get_tablerenderer_body_js_string() {
        return " r.qualdescription"; 
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
                'help'  => true,
            ),
            'enddate' => array(
                'type' => 'calendar', 
                'caloptions' => array(
                    'showsTime'     => false,
                    'ifFormat'      => '%Y/%m/%d',
                ),
                'title' => get_string('enddate', 'artefact.resume'),
                'help'  => true,
            ),
            'institution' => array(
                'type' => 'text',
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('institution', 'artefact.resume'),
                'help'  => true,
            ),
            'qualtype' => array(
                'type' => 'text',
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('qualtype', 'artefact.resume'),
                'help'  => true,
            ),
            'qualname' => array(
                'type' => 'text',
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('qualname', 'artefact.resume'),
                'help'  => true,
            ),
            'qualdescription' => array(
                'type' => 'textarea',
                'rows' => 10,
                'cols' => 50,
                'resizable' => false,
                'title' => get_string('qualdescription', 'artefact.resume'),
                'help'  => true,
            ),
        );
    }
}

class ArtefactTypeCertification extends ArtefactTypeResumeComposite { 

    protected $date;

    public static function get_tablerenderer_js() {
        return "
                'date',
                " . ArtefactTypeResumeComposite::get_tablerenderer_title_js(
                    self::get_tablerenderer_title_js_string(),
                    self::get_tablerenderer_body_js_string()
                ) . "
        ";
    }

    public static function get_tablerenderer_title_js_string() {
        return "r.title";
    }

    public static function get_tablerenderer_body_js_string() {
        return "r.description";
    }

    public static function get_order_field() {
        return 'date';
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
                'help'  => true,
            ),
            'title' => array(
                'type' => 'text',
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('title', 'artefact.resume'),
                'help'  => true,
            ),
            'description' => array(
                'type' => 'textarea',
                'rows' => 10,
                'cols' => 50,
                'resizable' => false,
                'title' => get_string('description'),
                'help'  => true,
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
                " . ArtefactTypeResumeComposite::get_tablerenderer_title_js(
                    self::get_tablerenderer_title_js_string(),
                    self::get_tablerenderer_body_js_string()
                ) . "
        ";
    }

    public static function get_tablerenderer_title_js_string() {
        return "r.title + ' (' + r.contribution + ')'";
    }

    public static function get_tablerenderer_body_js_string() {
        return "r.description";
    }

    public static function get_order_field() {
        return 'date';
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
                'help'  => true,
            ),
            'title' => array(
                'type' => 'text',
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('title', 'artefact.resume'),
                'help'  => true,
            ),
            'contribution' => array(
                'type' => 'text',
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('contribution', 'artefact.resume'),
                'help'  => true,
            ),
            'description' => array(
                'type' => 'textarea',
                'rows' => 10,
                'cols' => 50,
                'resizable' => false,
                'title' => get_string('description', 'artefact.resume'),
                'help'  => true,
            ),
        );
    }
}

class ArtefactTypeMembership extends ArtefactTypeResumeComposite { 

    protected $startdate;
    protected $enddate;

    public static function get_tablerenderer_js() {
        return "
                'startdate',
                'enddate',
                " . ArtefactTypeResumeComposite::get_tablerenderer_title_js(
                    self::get_tablerenderer_title_js_string(),
                    self::get_tablerenderer_body_js_string()
                ) . "
        ";
    }

    public static function get_tablerenderer_title_js_string() {
        return "r.title";
    }
   
    public static function get_tablerenderer_body_js_string() {
        return "r.description";
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
                'help'  => true,
            ),
            'enddate' => array(
                'type' => 'calendar', 
                'caloptions' => array(
                    'showsTime'     => false,
                    'ifFormat'      => '%Y/%m/%d',
                ),
                'title' => get_string('enddate', 'artefact.resume'),
                'help'  => true,
            ),
            'title' => array(
                'type' => 'text',
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('title', 'artefact.resume'),
                'help'  => true,
            ),
            'description' => array(
                'type' => 'textarea',
                'rows' => 10,
                'cols' => 50,
                'resizable' => false,
                'title' => get_string('description', 'artefact.resume'),
                'help'  => true,
            ),
        );
    }
}

class ArtefactTypeResumeGoalAndSkill extends ArtefactTypeResume {

    public static function is_singular() {
        return true;
    }

    public static function get_goalandskill_artefact_types() {
        return array('personalgoal', 'academicgoal', 'careergoal',
            'personalskill', 'academicskill', 'workskill');
    }

    public static function get_render_list() {
        return array(
            FORMAT_ARTEFACT_LISTSELF,
            FORMAT_ARTEFACT_RENDERFULL,
            FORMAT_ARTEFACT_RENDERMETADATA,
        );
    }

    public function render_full($options) {
        $smarty = smarty();
        
        $smarty->assign('type', get_string($this->get_artefact_type(), 'artefact.resume'));
        $smarty->assign('content', $this->get('description'));

        return array('html' => $smarty->fetch('artefact:resume:fragments/goalandskillrenderfull.tpl'));
    }

    public function __construct($id=0, $data=array()) {
        if (empty($id)) {
            $data['container'] = 0;
            $data['title'] = get_string($this->get_artefact_type(), 'artefact.resume');
        }
        parent::__construct($id, $data);
    }

}

class ArtefactTypePersonalgoal extends ArtefactTypeResumeGoalAndSkill { }
class ArtefactTypeAcademicgoal extends ArtefactTypeResumeGoalAndSkill { }
class ArtefactTypeCareergoal extends ArtefactTypeResumeGoalAndSkill { }
class ArtefactTypePersonalskill extends ArtefactTypeResumeGoalAndSkill { }
class ArtefactTypeAcademicskill extends ArtefactTypeResumeGoalAndSkill { }
class ArtefactTypeWorkskill extends ArtefactTypeResumeGoalAndSkill { }

function compositeform_submit(Pieform $form, $values) {
    try {
        call_static_method(generate_artefact_class_name($values['compositetype']), 
            'process_compositeform', $form, $values);
    }
    catch (Exception $e) {
        $form->json_reply(PIEFORM_ERR, $e->getMessage());
    }
    $form->json_reply(PIEFORM_OK, get_string('compositesaved', 'artefact.resume'));
}

function goalandskillform_submit(Pieform $form, $values) {
    foreach ($values as $key => $value) {
        if (!in_array($key, ArtefactTypeResumeGoalAndSkill::get_goalandskill_artefact_types())) {
            continue;
        }
        try {
            $a = artefact_instance_from_type($key);
            $a->set('description', $value);
        }
        catch (Exception $e) {
            global $USER;
            $classname = generate_artefact_class_name($key);
            $a = new $classname(0, array(
                'owner' => $USER->get('id'),
                'title' => get_string($key),
                'description' => $value,
           )); 
        }
        $a->commit();
    }
    $form->json_reply(PIEFORM_OK, get_string('goalandskillsaved', 'artefact.resume'));
}   

?>
