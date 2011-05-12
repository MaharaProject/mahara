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
 * @subpackage artefact-resume
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
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
    
    public static function get_block_types() {
        return array(); 
    }

    public static function get_plugin_name() {
        return 'resume';
    }

    public static function menu_items() {
        return array(
            'content/resume' => array(
                'path' => 'content/resume',
                'title' => get_string('resume', 'artefact.resume'),
                'url' => 'artefact/resume/',
                'weight' => 50,
            ),
        );
    }

    public static function get_artefact_type_content_types() {
        return array(
            'coverletter'   => array('text'),
            'interest'      => array('text'),
            'personalgoal'  => array('text'),
            'academicgoal'  => array('text'),
            'careergoal'    => array('text'),
            'personalskill' => array('text'),
            'academicskill' => array('text'),
            'workskill'     => array('text'),
        );
    }

    public static function submenu_items() {
        $tabs = array(
            'index' => array(
                'page'  => 'index',
                'url'   => 'artefact/resume',
                'title' => get_string('introduction', 'artefact.resume'),
            ),
            'employment' => array(
                'page'  => 'employment',
                'url'   => 'artefact/resume/employment.php',
                'title' => get_string('educationandemployment', 'artefact.resume'),
            ),
            'achievements' => array(
                'page'  => 'achievements',
                'url'   => 'artefact/resume/achievements.php',
                'title' => get_string('achievements', 'artefact.resume'),
            ),
            'goals' => array(
                'page'  => 'goals',
                'url'   => 'artefact/resume/goals.php',
                'title' => get_string('goals', 'artefact.resume'),
            ),
            'skills' => array(
                'page'  => 'skills',
                'url'   => 'artefact/resume/skills.php',
                'title' => get_string('skills', 'artefact.resume'),
            ),
            'interests' => array(
                'page'  => 'interests',
                'url'   => 'artefact/resume/interests.php',
                'title' => get_string('interests', 'artefact.resume'),
            ),
        );
        if (defined('RESUME_SUBPAGE') && isset($tabs[RESUME_SUBPAGE])) {
            $tabs[RESUME_SUBPAGE]['selected'] = true;
        }
        return $tabs;
    }

    public static function composite_tabs() {
        return array(
            'educationhistory'  => 'employment',
            'employmenthistory' => 'employment',
            'certification'     => 'achievements',
            'book'              => 'achievements',
            'membership'        => 'achievements',
        );
    }
}

class ArtefactTypeResume extends ArtefactType {

    public static function get_icon($options=null) {}

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
        // @todo Catalyst IT Ltd
    }

    /**
     * Default render method for resume fields - show their description
     */
    public function render_self($options) {
        return array('html' => clean_html($this->description));
    }

    /**
     * Overrides the default commit to make sure that any 'entireresume' blocks 
     * in views the user have know about this artefact - but only if necessary. 
     * Goals and skills are not in the entireresume block
     *
     * @param boolean $updateresumeblocks Whether to update any resume blockinstances
     */
    public function commit() {
        parent::commit();

        if ($blockinstances = get_records_sql_array('
            SELECT id, "view", configdata
            FROM {block_instance}
            WHERE blocktype = \'entireresume\'
            AND "view" IN (
                SELECT id
                FROM {view}
                WHERE "owner" = ?)', array($this->owner))) {
            foreach ($blockinstances as $blockinstance) {
                $whereobject = (object)array(
                    'view' => $blockinstance->view,
                    'artefact' => $this->get('id'),
                    'block' => $blockinstance->id,
                );
                ensure_record_exists('view_artefact', $whereobject, $whereobject);
            }
        }
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

}

class ArtefactTypeInterest extends ArtefactTypeResume {

    public static function is_singular() {
        return true;
    }

}

class ArtefactTypeContactinformation extends ArtefactTypeResume {

    public function render_self($options) {
        $smarty = smarty_core();
        $fields = ArtefactTypeContactinformation::get_profile_fields();
        foreach ($fields as $f) {
            try {
                $$f = artefact_instance_from_type($f, $this->get('owner'));
                $rendered = $$f->render_self(array());
                $smarty->assign($f, $rendered['html']);
                $smarty->assign('hascontent', true);
            }
            catch (Exception $e) { }
        }

        return array('html' => $smarty->fetch('artefact:resume:fragments/contactinformation.tpl'));
    }

    public static function is_singular() {
        return true;
    }

    public static function setup_new($userid) {
        try {
            return artefact_instance_from_type('contactinformation', $userid);
        } catch (ArtefactNotFoundException $e) {
            $artefact = new ArtefactTypeContactinformation(null, array(
                'owner' => $userid,
                'title' => get_string('contactinformation', 'artefact.resume')
            ));
            $artefact->commit();
        }
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

}

class ArtefactTypePersonalinformation extends ArtefactTypeResume {
    
    protected $composites;

    public function __construct($id=0, $data=null) {
        if (empty($id)) {
            $data['title'] = get_string('personalinformation', 'artefact.resume');
        }
        parent::__construct($id, $data);
        $this->composites = ArtefactTypePersonalinformation::get_composite_fields();
        if (!empty($id)) {
            $this->composites = (array)get_record('artefact_resume_personal_information', 'artefact', $id,
                null, null, null, null, '*, ' . db_format_tsfield('dateofbirth'));
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
            if ($field == 'dateofbirth' && !empty($value)) {
                $value = db_format_timestamp($value);
            }
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

    public function render_self($options) {
        $smarty = smarty_core();
        $fields = array();
        foreach (array_keys(ArtefactTypePersonalInformation::get_composite_fields()) as $field) {
            $value = $this->get_composite($field);
            if ($field == 'gender' && !empty($value)) {
                $value = get_string($value, 'artefact.resume');
            }
            if ($field == 'dateofbirth' && !empty($value)) {
                $value = strftime(get_string('strftimedate'), $value+3600);
            }
            $fields[get_string($field, 'artefact.resume')] = $value;
        }
        $smarty->assign('fields', $fields);
        return array('html' => $smarty->fetch('artefact:resume:fragments/personalinformation.tpl'));
    }

    public function delete() {
        db_begin();

        delete_records('artefact_resume_personal_information', 'artefact', $this->id);
        parent::delete();

        db_commit();
    }

    public static function bulk_delete($artefactids) {
        if (empty($artefactids)) {
            return;
        }

        $idstr = join(',', array_map('intval', $artefactids));

        db_begin();
        delete_records_select('artefact_resume_personal_information', 'artefact IN (' . $idstr . ')');
        parent::bulk_delete($artefactids);
        db_commit();
    }
}



abstract class ArtefactTypeResumeComposite extends ArtefactTypeResume {

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

    /**
    * This function should return a snippet of javascript
    * to be plugged into a table renderer instantiation
    * it comprises the cell function definition
    */
    public static abstract function get_tablerenderer_js();

    public static abstract function get_tablerenderer_title_js_string();

    public static abstract function get_tablerenderer_body_js_string();

    /**
     * Can be overridden to format data retrieved from artefact tables for 
     * display of the resume artefact by render_self
     */
    public static function format_render_self_data($data) {
        return $data;
    }

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
        global $USER;
        self::ensure_composite_value($values, $values['compositetype'], $USER->get('id'));
    }

    /**
     * Ensures that the given value for the given composite is present
     * TODO: expand on these docs.
     */
    public static function ensure_composite_value($values, $compositetype, $owner) {
        if (!in_array($compositetype, self::get_composite_artefact_types())) {
            throw new SystemException("ensure_composite_value called with invalid composite type");
        }
        try {
            $a = artefact_instance_from_type($compositetype, $owner);
            $a->set('mtime', time());
        }
        catch (Exception $e) {
            $classname = generate_artefact_class_name($compositetype);
            $a = new $classname(0, array(
                'owner' => $owner,
                'title' => get_string($compositetype, 'artefact.resume'),
                )
            );
        }

        $a->commit();

        $values['artefact'] = $a->get('id');

        $table = 'artefact_resume_' . $compositetype;
        if (!empty($values['id'])) {
            update_record($table, (object)$values, 'id');
        }
        else {
            if (isset($values['displayorder'])) {
                $values['displayorder'] = intval($values['displayorder']);
            }
            else {
                $max = get_field($table, 'MAX(displayorder)', 'artefact', $values['artefact']);
                $values['displayorder'] = is_numeric($max) ? $max + 1 : 0;
            }
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

    public static function bulk_delete_composite($artefactids, $compositetype) {
        $table = 'artefact_resume_' . $compositetype;
        if (empty($artefactids)) {
            return;
        }

        $idstr = join(',', array_map('intval', $artefactids));

        db_begin();
        delete_records_select($table, 'artefact IN (' . $idstr . ')');
        parent::bulk_delete($artefactids);
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
                $form['elements'][$k]['defaultvalue'] = $composite->{$k};
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

    public function render_self($options) {
        global $USER;
        $suffix = '_' . substr(md5(microtime()), 0, 4);
        $smarty = smarty_core();
        $smarty->assign('hidetitle', true);
        $smarty->assign('suffix', $suffix);
        $type = $this->get('artefacttype');
        $othertable = 'artefact_resume_' . $type;
        $owner = $USER->get('id');

        $sql = 'SELECT ar.*, a.owner
            FROM {artefact} a 
            JOIN {' . $othertable . '} ar ON ar.artefact = a.id
            WHERE a.owner = ? AND a.artefacttype = ?
            ORDER BY ar.displayorder';

        if (!empty($options['viewid'])) { 
            require_once('view.php');
            $v = new View($options['viewid']);
            $owner = $v->get('owner');
        }

        if (!$data = get_records_sql_array($sql, array($owner, $type))) {
            $data = array();
        }

        // Give the artefact type a chance to format the data how it sees fit
        $data = call_static_method(generate_artefact_class_name($type), 'format_render_self_data', $data);
        $smarty->assign('rows', $data);

        $content = array(
            'html'         => $smarty->fetch('artefact:resume:fragments/' . $type . '.tpl'),
            'javascript'   => $this->get_showhide_composite_js()
        );
        return $content;
    }

    public static function get_js(array $compositetypes) {
        $js = self::get_common_js();
        foreach ($compositetypes as $compositetype) {
            $js .= call_static_method(
                generate_artefact_class_name($compositetype),
                'get_artefacttype_js',
                $compositetype
            );
        }
        return $js;
    }

    public static function get_common_js() {
        $cancelstr = get_string('cancel');
        $addstr = get_string('add');
        $confirmdelstr = get_string('compositedeleteconfirm', 'artefact.resume');
        $js = <<<EOF
var tableRenderers = {};

function toggleCompositeForm(type) {
    var elemName = '';
    elemName = type + 'form';
    if (hasElementClass(elemName, 'hidden')) {
        removeElementClass(elemName, 'hidden');
        $('add' + type + 'button').innerHTML = '{$cancelstr}';
    }
    else {
        $('add' + type + 'button').innerHTML = '{$addstr}';
        addElementClass(elemName, 'hidden');
    }
}

function compositeSaveCallback(form, data) {
    key = form.id.substr(3);
    tableRenderers[key].doupdate(); 
    toggleCompositeForm(key);
    // Can't reset() the form here, because its values are what were just submitted, 
    // thanks to pieforms
    forEach(form.elements, function(element) {
        if (hasElementClass(element, 'text') || hasElementClass(element, 'textarea')) {
            element.value = '';
        }
    });
}

function deleteComposite(type, id, artefact) {
    if (confirm('{$confirmdelstr}')) {
        sendjsonrequest('compositedelete.json.php',
            {'id': id, 'artefact': artefact},
            'GET',
            function(data) {
                tableRenderers[type].doupdate();
            },
            function() {
                // @todo error
            }
        );
    }
    return false;
}

function moveComposite(type, id, artefact, direction) {
    sendjsonrequest('compositemove.json.php',
        {'id': id, 'artefact': artefact, 'direction':direction},
        'GET',
        function(data) {
            tableRenderers[type].doupdate();
        },
        function() {
            // @todo error
        }
    );
    return false;
}
EOF;
        $js .= self::get_showhide_composite_js();
        return $js;
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

    static function get_artefacttype_js($compositetype) {
        global $THEME;
        $editstr = get_string('edit');
        $delstr = get_string('delete');
        $imagemoveblockup   = json_encode($THEME->get_url('images/move-up.gif'));
        $imagemoveblockdown = json_encode($THEME->get_url('images/move-down.gif'));
        $upstr = get_string('moveup', 'artefact.resume');
        $downstr = get_string('movedown', 'artefact.resume');

        $js = call_static_method(generate_artefact_class_name($compositetype), 'get_composite_js');

        $js .= <<<EOF
tableRenderers.{$compositetype} = new TableRenderer(
    '{$compositetype}list',
    'composite.json.php',
    [
EOF;

        $js .= <<<EOF

        function (r, d) {
            var buttons = [];
            if (r._rownumber > 1) {
                var up = A({'href': ''}, IMG({'src': {$imagemoveblockup}, 'alt':'{$upstr}'}));
                connect(up, 'onclick', function (e) {
                    e.stop();
                    return moveComposite(d.type, r.id, r.artefact, 'up');
                });
                buttons.push(up);
            }
            if (!r._last) {
                var down = A({'href': '', 'class':'movedown'}, IMG({'src': {$imagemoveblockdown}, 'alt':'{$downstr}'}));
                connect(down, 'onclick', function (e) {
                    e.stop();
                    return moveComposite(d.type, r.id, r.artefact, 'down');
                });
                buttons.push(' ');
                buttons.push(down);
            }
            return TD({'class':'movebuttons'}, buttons);
        },
EOF;

        $js .= call_static_method(generate_artefact_class_name($compositetype), 'get_tablerenderer_js');

        $js .= <<<EOF
        function (r, d) {
            var editlink = A({'href': 'editcomposite.php?id=' + r.id + '&artefact=' + r.artefact, 'title': '{$editstr}'}, IMG({'src': config.theme['images/edit.gif'], 'alt':'{$editstr}'}));
            var dellink = A({'href': '', 'title': '{$delstr}'}, IMG({'src': config.theme['images/icon_close.gif'], 'alt': '[x]'}));
            connect(dellink, 'onclick', function (e) {
                e.stop();
                return deleteComposite(d.type, r.id, r.artefact);
            });
            return TD({'class':'right'}, null, editlink, ' ', dellink);
        }
    ]
);

tableRenderers.{$compositetype}.type = '{$compositetype}';
tableRenderers.{$compositetype}.statevars.push('type');
tableRenderers.{$compositetype}.emptycontent = '';
tableRenderers.{$compositetype}.updateOnLoad();

EOF;
        return $js;
    }

    static function get_composite_js() {
        return '';
    }

    static function get_forms(array $compositetypes) {
        require_once(get_config('libroot') . 'pieforms/pieform.php');
        $compositeforms = array();
        foreach ($compositetypes as $compositetype) {
            $elements = call_static_method(generate_artefact_class_name($compositetype), 'get_addform_elements');
            $elements['submit'] = array(
                'type' => 'submit',
                'value' => get_string('save'),
            );
            $elements['compositetype'] = array(
                'type' => 'hidden',
                'value' => $compositetype,
            );
            $cform = array(
                'name' => 'add' . $compositetype,
                'plugintype' => 'artefact',
                'pluginname' => 'resume',
                'elements' => $elements,
                'jsform' => true,
                'successcallback' => 'compositeform_submit',
                'jssuccesscallback' => 'compositeSaveCallback',
            );
            $compositeforms[$compositetype] = pieform($cform);
        }
        return $compositeforms;
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
        return " r.jobtitle + ': ' + r.employer";
    }

    public static function get_tablerenderer_body_js_string() {
        return " r.positiondescription";
    }

    public static function get_addform_elements() {
        return array(
            'startdate' => array(
                'type' => 'text',
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('startdate', 'artefact.resume'),
                'size' => 20,
                'help' => true,
            ),
            'enddate' => array(
                'type' => 'text', 
                'title' => get_string('enddate', 'artefact.resume'),
                'size' => 20,
            ),
            'employer' => array(
                'type' => 'text',
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('employer', 'artefact.resume'),
                'size' => 50,
            ),
            'employeraddress' => array(
                'type' => 'text',
                'title' => get_string('employeraddress', 'artefact.resume'),
                'size' => 50,
            ),
            'jobtitle' => array(
                'type' => 'text',
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('jobtitle', 'artefact.resume'),
                'size' => 50,
            ),
            'positiondescription' => array(
                'type' => 'textarea',
                'rows' => 10,
                'cols' => 50,
                'resizable' => false,
                'title' =>  get_string('jobdescription', 'artefact.resume'),
            ),
        );
    }

    public static function bulk_delete($artefactids) {
        ArtefactTypeResumeComposite::bulk_delete_composite($artefactids, 'employmenthistory');
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
        return " formatQualification(r.qualname, r.qualtype, r.institution)";
    }

    public static function format_render_self_data($data) {
        $at = get_string('at');
        foreach ($data as &$row) {
            $row->qualification = '';
            if (strlen($row->qualname) && strlen($row->qualtype)) {
                $row->qualification = $row->qualname. ' (' . $row->qualtype . ') ' . $at . ' ';
            }
            else if (strlen($row->qualtype)) {
                $row->qualification = $row->qualtype . ' ' . $at . ' ';
            }
            else if (strlen($row->qualname)) {
                $row->qualification = $row->qualname . ' ' . $at . ' ';
            }
            $row->qualification .= $row->institution;
        }
        return $data;
    }

    public static function get_tablerenderer_body_js_string() {
        return " r.qualdescription"; 
    }

    public static function get_addform_elements() {
        return array(
            'startdate' => array(
                'type' => 'text',
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('startdate', 'artefact.resume'),
                'size' => 20,
                'help' => true,
            ),
            'enddate' => array(
                'type' => 'text', 
                'title' => get_string('enddate', 'artefact.resume'),
                'size' => 20,
            ),
            'institution' => array(
                'type' => 'text',
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('institution', 'artefact.resume'),
                'size' => 50,
            ),
            'institutionaddress' => array(
                'type' => 'text',
                'title' => get_string('institutionaddress', 'artefact.resume'),
                'size' => 50,
            ),
            'qualtype' => array(
                'type' => 'text',
                'title' => get_string('qualtype', 'artefact.resume'),
                'size' => 50,
            ),
            'qualname' => array(
                'type' => 'text',
                'title' => get_string('qualname', 'artefact.resume'),
                'size' => 50,
            ),
            'qualdescription' => array(
                'type' => 'textarea',
                'rows' => 10,
                'cols' => 50,
                'resizable' => false,
                'title' => get_string('qualdescription', 'artefact.resume'),
            ),
        );
    }

    static function get_composite_js() {
        $at = get_string('at');
        return <<<EOF
function formatQualification(name, type, institution) {
    var qual = '';
    if (name && type) {
        qual = name + ' (' + type + ') {$at} ';
    }
    else if (type) {
        qual = type + ' {$at} ';
    }
    else if (name) {
        qual = name + ' {$at} ';
    }
    qual += institution;
    return qual;
}
EOF;
    }

    public static function bulk_delete($artefactids) {
        ArtefactTypeResumeComposite::bulk_delete_composite($artefactids, 'educationhistory');
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

    public static function get_addform_elements() {
        return array(
            'date' => array(
                'type' => 'text',
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('date', 'artefact.resume'),
                'size' => 20,
                'help' => true,
            ),
            'title' => array(
                'type' => 'text',
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('title', 'artefact.resume'),
                'size' => 20,
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

    public static function bulk_delete($artefactids) {
        ArtefactTypeResumeComposite::bulk_delete_composite($artefactids, 'certification');
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

    public static function get_addform_elements() {
        return array(
            'date' => array(
                'type' => 'text',
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('date', 'artefact.resume'),
                'help' => true,
                'size' => 20,
            ),
            'title' => array(
                'type' => 'text',
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('title', 'artefact.resume'),
                'size' => 50,
            ),
            'contribution' => array(
                'type' => 'text',
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('contribution', 'artefact.resume'),
                'size' => 50,
            ),
            'description' => array(
                'type' => 'textarea',
                'rows' => 10,
                'cols' => 50,
                'resizable' => false,
                'title' => get_string('detailsofyourcontribution', 'artefact.resume'),
            ),
        );
    }

    public static function bulk_delete($artefactids) {
        ArtefactTypeResumeComposite::bulk_delete_composite($artefactids, 'book');
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
                'type' => 'text',
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('startdate', 'artefact.resume'),
                'help' => true,
                'size' => 20,
            ),
            'enddate' => array(
                'type' => 'text', 
                'title' => get_string('enddate', 'artefact.resume'),
                'size' => 20,
            ),
            'title' => array(
                'type' => 'text',
                'rules' => array(
                    'required' => true,
                ),
                'title' => get_string('title', 'artefact.resume'),
                'size' => 50,
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

    public static function bulk_delete($artefactids) {
        ArtefactTypeResumeComposite::bulk_delete_composite($artefactids, 'membership');
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

function compositeformedit_submit(Pieform $form, $values) {
    global $SESSION;

    $tabs = PluginArtefactResume::composite_tabs();
    $goto = get_config('wwwroot') . '/artefact/resume/';
    if (isset($tabs[$values['compositetype']])) {
        $goto .= $tabs[$values['compositetype']] . '.php';
    }

    try {
        call_static_method(generate_artefact_class_name($values['compositetype']),
            'process_compositeform', $form, $values);
    }
    catch (Exception $e) {
        $SESSION->add_error_msg(get_string('compositesavefailed', 'artefact.resume'));
        redirect($goto);
    }
    $SESSION->add_ok_msg(get_string('compositesaved', 'artefact.resume'));
    redirect($goto);
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
