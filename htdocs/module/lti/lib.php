<?php
/**
 *
 * @package    mahara
 * @subpackage module.lti
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

/**
 * This plugin supports the webservices provided by the LTI module
 */
class PluginModuleLti extends PluginModule {

    public static $grading_roles = array('Instructor');
    public static $graded_roles = array('Learner');

    private static $default_config = array(
        'autocreateusers'   => false,
        'parentauth'        => null,
    );

    public static function postinst($fromversion) {
        if ($fromversion < 2018100100) {
            // Add indexes to the lit_assessment table on install
            log_debug('Add indexes to lti_assessment table');
            $mysqlsuffix = is_mysql() ? '(255)' : '';
            $table = new XMLDBTable('lti_assessment');
            $field = new XMLDBField('resourcelinkid');
            if (field_exists($table, $field)) {
                $index = new XMLDBIndex('resourcelinkididx');
                $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('resourcelinkid'));
                if (!index_exists($table, $index)) {
                    $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('resourcelinkid' . $mysqlsuffix));
                    add_index($table, $index);
                }
            }
            $field = new XMLDBField('contextid');
            if (field_exists($table, $field)) {
                $index = new XMLDBIndex('contextididx');
                $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('contextid'));
                if (!index_exists($table, $index)) {
                    $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('contextid' . $mysqlsuffix));
                    add_index($table, $index);
                }
            }
            $field = new XMLDBField('listresultsourceid');
            if (field_exists($table, $field)) {
                $index = new XMLDBIndex('listresultsourceididx');
                $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('listresultsourceid'));
                if (!index_exists($table, $index)) {
                    $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('listresultsourceid' . $mysqlsuffix));
                    add_index($table, $index);
                }
            }
        }

        require_once(get_config('docroot') . 'webservice/lib.php');
        external_reload_component('module/lti', false);
    }

    public static function has_config() {
        return true;
    }

    public static function has_oauth_service_config() {
        return true;
    }

    /**
     * Check the status of each configuration element needed for the LTI API
     *
     * @param boolean $clearcache Whether to clear the cached results of the check
     * @return array Information about the status of each config step needed.
     */
    public static function check_service_status($clearcache = false) {
        static $statuslist = null;
        if (!$clearcache && $statuslist !== null) {
            return $statuslist;
        }

        require_once(get_config('docroot') . 'webservice/lib.php');

        // Check all the configs needed for the LTI API to work.
        $statuslist = array();
        $statuslist[] = array(
            'name' => get_string('webserviceproviderenabled', 'module.lti'),
            'status' => (bool) get_config('webservice_provider_enabled')
        );
        $statuslist[] = array(
            'name' => get_string('oauthprotocolenabled', 'module.lti'),
            'status' => webservice_protocol_is_enabled('oauth')
        );
        $statuslist[] = array(
            'name' => get_string('restprotocolenabled', 'module.lti'),
            'status' => webservice_protocol_is_enabled('rest')
        );

        $servicerec = get_record('external_services', 'shortname', 'maharalti', 'component', 'module/lti', null, null, 'enabled, restrictedusers, tokenusers');
        $statuslist[] = array(
            'name' => get_string('ltiserviceexists', 'module.lti'),
            'status' => (bool) $servicerec
        );

        return $statuslist;
    }

    /**
     * Determine whether the LTI webservice, as a whole, is fully configured
     * @param boolean $clearcache Whether to clear cached results from a previous check
     * @return boolean
     */
    public static function is_service_ready($clearcache = false) {
        return array_reduce(
            static::check_service_status($clearcache),
            function($carry, $item) {
                return $carry && $item['status'];
            },
            true
        );
    }

    public static function get_config_options() {

        $statuslist = static::check_service_status(true);
        $ready = static::is_service_ready();

        $smarty = smarty_core();
        $smarty->assign('statuslist', $statuslist);
        if ($ready) {
            $smarty->assign('notice', get_string('noticeenabled', 'module.lti'));
        }
        else {
            $smarty->assign('notice', get_string('noticenotenabled', 'module.lti'));
        }
        $statushtml = $smarty->fetch('module:lti:statustable.tpl');
        unset($smarty);

        $elements = array();
        $elements['statustable'] = array(
            'type' => 'html',
            'value' => $statushtml
        );

        if (!$ready) {
            $elements['activate'] = array(
                'type' => 'switchbox',
                'title' => get_string('autoconfiguretitle', 'module.lti'),
                'description' => get_string('autoconfiguredesc', 'module.lti'),
                'switchtext' => 'yesno',
            );
        }

        $form = array('elements' => $elements);

        if (!$ready) {
            // HACK: Reload the page after form submission, so that the status
            // table gets updated.
            $form['jssuccesscallback'] = 'module_lti_reload_page';
        }

        return $form;
    }

    public static function save_config_options(Pieform $form, $values) {

        if (!empty($values['activate'])) {
            set_config('webservice_provider_enabled', true);
            set_config('webservice_provider_oauth_enabled', true);
            set_config('webservice_provider_rest_enabled', true);

            require_once(get_config('docroot') . 'webservice/lib.php');
            external_reload_component('module/lti', false);
            set_field('external_services', 'enabled', 1, 'shortname', 'lti', 'component', 'module/lti');
        }
        return true;
    }

    public static function get_oauth_service_config_options($serverid) {
        $rawdbconfig = get_records_sql_array('SELECT c.field, c.value, r.institution FROM {oauth_server_registry} r
                                           LEFT JOIN {oauth_server_config} c ON c.oauthserverregistryid = r.id
                                           WHERE r.id = ?', array($serverid));
        $dbconfig = new stdClass();
        foreach ($rawdbconfig as $raw) {
            $dbconfig->institution = $raw->institution;
            if (!empty($raw->field)) {
                $dbconfig->{$raw->field} = $raw->value;
            }
        }

        $elements = array(
            'institution' => array(
                'type'  => 'html',
                'title' => get_string('institution'),
                'value' => institution_display_name($dbconfig->institution),
            ),
            'autocreateusers' => array(
                'type'  => 'switchbox',
                'title' => get_string('autocreateusers', 'module.lti'),
                'defaultvalue' => isset($dbconfig->autocreateusers) ? $dbconfig->autocreateusers : self::$default_config['autocreateusers'],
            ),
        );

        // Get the active auth instances for this institution that are not webservices
        if ($instances = get_records_sql_array("SELECT ai.* FROM {oauth_server_registry} osr
                                                JOIN {auth_instance} ai ON ai.institution = osr.institution
                                                WHERE osr.id = ? AND ai.active = 1 AND ai.authname != 'webservice'", array($serverid))) {
            $options = array('' => get_string('None', 'admin'));
            foreach ($instances as $instance) {
                $options[$instance->id] = get_string('title', 'auth.' . $instance->authname);
            }
            $elements['parentauth'] = array(
                'type' => 'select',
                'title' => get_string('parentauthforlti', 'module.lti'),
                'defaultvalue' => isset($dbconfig->parentauth) ? $dbconfig->parentauth : self::$default_config['parentauth'],
                'options' => $options,
                'help' => true,
            );
        }

        return $elements;

    }

    public static function save_oauth_service_config_options($serverid, $values) {
        $options = array('autocreateusers', 'parentauth');
        foreach ($options as $option) {
            $fordb = isset($values[$option]) ? $values[$option] : null;
            update_oauth_server_config($serverid, $option, $fordb);
        }
        return true;
    }

    // Disable form fields that are not needed by this plugin
    // @return array of fields not needed with key the field name
    public static function disable_webservice_fields() {
        $fields = array (
            'application_uri' => 1,
            'callback_uri' => 1
        );
        return $fields;
    }


    /**
     * Form for submitting collections/pages for lti assessessment
     */
    public static function submit_for_grading_form() {
        global $USER;

        require_once(get_config('libroot') . 'view.php');

        list($collections, $views) = View::get_views_and_collections($USER->get('id'));

        $viewoptions = $collectionoptions = array();

        foreach ($collections as $c) {
            if (empty($c['submittedgroup']) && empty($c['submittedhost'])) {
                $collectionoptions['c:' . $c['id']] = $c['name'];
            }
        }

        foreach ($views as $v) {
            if ($v['type'] != 'profile' && empty($v['submittedgroup']) && empty($v['submittedhost'])) {
                $viewoptions['v:' . $v['id']] = $v['name'];
            }
        }

        $options = $optgroups = null;

        if (!empty($collectionoptions) && !empty($viewoptions)) {
            $optgroups = array(
                'collections' => array(
                    'label'   => get_string('Collections', 'collection'),
                    'options' => $collectionoptions,
                ),
                'views'       => array(
                    'label'   => get_string('Views', 'view'),
                    'options' => $viewoptions,
                ),
            );
        }
        else if (!empty($collectionoptions)) {
            $options = $collectionoptions;
        }
        else if (!empty($viewoptions)) {
            $options = $viewoptions;
        }


        if (empty($options) && empty($optgroups)) {
            return get_string('nocollections', 'module.lti');
        }

        return pieform(array(
            'name' => 'lti_submit_for_grading',
            'method' => 'post',
            'renderer' => 'div',
            'class' => 'form-inline',
            'autofocus' => false,
            'successcallback' => array('PluginModuleLti', 'submit_for_grading_form_submit'),
            'elements' => array(
                'inputgroup' => array(
                    'type' => 'fieldset',
                    'class' => 'input-group',
                    'elements' => array(
                        'options' => array(
                            'type' => 'select',
                            'title' => get_string('forassessment1', 'view'),
                            'collapseifoneoption' => false,
                            'optgroups' => $optgroups,
                            'options' => $options,
                            'class' => 'forassessment text-inline text-small',
                        ),
                        'submit' => array(
                            'type' => 'button',
                            'usebuttontag' => true,
                            'class' => 'btn-primary input-group-btn',
                            'value' => get_string('submit')
                        ),
                    ),
                ),
            ),
        ));
    }

    /**
     * Save grading submission form
     */
    public static function submit_for_grading_form_submit(Pieform $form, $values) {
        global $USER, $SESSION;

        $viewid = $collectionid = null;

        if (substr($values['options'], 0, 2) == 'v:') {
            $viewid = substr($values['options'], 2);

            if (!$view = get_record('view', 'id', $viewid, 'owner', $USER->get('id'))) {
                throw new AccessDeniedException("You do not own this view");
            }
        }
        if (substr($values['options'], 0, 2) == 'c:') {
            $collectionid = substr($values['options'], 2);

            if (!$colleciton = get_record('collection', 'id', $collectionid, 'owner', $USER->get('id'))) {
                throw new AccessDeniedException("You do not own this collection");
            }
        }


        if (!$assessment = get_record('lti_assessment', 'id', $SESSION->get('lti.assessment'))) {
            throw new MaharaException("Missing assessment record");
        }

        if (!empty($collectionid)) {
            $collection = new Collection($collectionid);

            $collection->submit(get_group_by_id($assessment->group), null, null, false);
            $submissionname = $collection->get('name');
        }
        else {
            $view = new View($viewid);

            $view->submit(get_group_by_id($assessment->group), false);
            $submissionname = $view->get('title');
        }

        $sub = new stdClass();
        $sub->usr = $USER->get('id');
        $sub->ltiassessment = $SESSION->get('lti.assessment');
        $sub->lisresultsourceid = $SESSION->get('lti.lis_result_sourcedid');
        $sub->timesubmitted = db_format_timestamp(time());
        $sub->collectionid = $collectionid;
        $sub->viewid = $viewid;

        insert_record('lti_assessment_submission', $sub);


        $group = get_record('group', 'id', $assessment->group);
        $grouproles = get_column('grouptype_roles', 'role', 'grouptype', 'standard', 'see_submitted_views', 1);

        if ($assessment->emailnotifications) {
            activity_occurred(
                'groupmessage',
                array(
                    'group'         => $assessment->group,
                    'roles'         => $grouproles,
                    'strings'       => (object) array(
                        'subject' => (object) array(
                            'key'     => 'viewsubmittedsubject1',
                            'section' => 'module.lti',
                            'args'    => array($group->name),
                        ),
                        'message' => (object) array(
                            'key'     => 'viewsubmittedmessage1',
                            'section' => 'module.lti',
                            'args'    => array(
                                display_name($USER, null, false, true),
                                $submissionname,
                                $group->name,
                                $assessment->contexttitle,
                            ),
                        ),
                    ),
                )
            );
        }

        redirect('/module/lti/submission.php');
    }



    public static function submit_from_view_or_collection_form(View $view) {
        global $SESSION, $USER;

        $sql = "SELECT a.*
                FROM {lti_assessment} a
                    LEFT JOIN {lti_assessment_submission} s ON a.id = s.ltiassessment AND s.usr = ?
                WHERE s.id IS NULL AND a.id = ?";

        if (!$assessment = get_record_sql($sql, array($USER->get('id'), $SESSION->get('lti.assessment')))) {
            return false;
        }

        if ($collection  = $view->get('collection')) {
            $value = 'c:' . $collection->get('id');
        }
        else {
            $value = 'v:' . $view->get('id');
        }

        return pieform(array(
            'name' => 'lti_submit_for_grading',
            'method' => 'post',
            'renderer' => 'div',
            'class' => 'form-inline',
            'autofocus' => false,
            'successcallback' => array('PluginModuleLti', 'submit_for_grading_form_submit'),
            'elements' => array(
                'text1' => array(
                    'type' => 'html',
                    'class' => 'text-inline',
                    'value' => get_string('submitto', 'module.lti', $assessment->resourcelinktitle, $assessment->contexttitle),
                ),
                'inputgroup' => array(
                    'type' => 'fieldset',
                    'class' => 'input-group',
                    'elements' => array(
                        'submit' => array(
                            'type' => 'button',
                            'usebuttontag' => true,
                            'class' => 'btn-primary input-group-btn',
                            'value' => get_string('submit')
                        ),
                    ),
                ),
                'options' => array(
                    'type' => 'hidden',
                    'value' => $value,
                ),
            ),
        ));
    }

    public static function can_grade() {
        global $SESSION;

        if ($rolescsv = $SESSION->get('lti.roles')) {
            $roles = explode(',', $rolescsv);

            if (count(array_intersect($roles, self::$grading_roles)) > 0) {
                return true;
            }
        }

        return false;
    }

    public static function can_submit_for_grading() {
        global $SESSION;

        if (!$rolescsv = $SESSION->get('lti.roles')) {
            return false;
        }

        $roles = explode(',', $rolescsv);

        if (count(array_intersect($roles, self::$graded_roles)) == 0) {
            return false;
        }

        return true;
    }


    public static function activity_configured() {
        global $SESSION;

        if (!$activity = get_record('lti_assessment', 'id', $SESSION->get('lti.assessment'))) {
            return false;
        }

        if (empty($activity->timeconfigured)) {
            return false;
        }

        return true;
    }

    public static function get_submission() {
        global $SESSION, $USER;

        return new ModuleLtiSubmission($SESSION->get('lti.assessment'), $USER->get('id'));
    }

    public static function get_all_submissions() {
        global $SESSION;

        $userfields = array('firstname', 'lastname', 'preferredname', 'username');

        $sql = "SELECT s.*, c.name AS collectionname, v.title AS viewtitle, u.firstname, u.lastname, u.preferredname, u.username,
                    gu.firstname AS gu_firstname, gu.lastname AS gu_lastname, gu.preferredname AS gu_preferredname, gu.username AS gu_username
                FROM {lti_assessment_submission} s
                    INNER JOIN {usr} u ON u.id = s.usr
                    LEFT JOIN {collection} c ON c.id = s.collectionid
                    LEFT JOIN {view} v ON v.id = s.viewid
                    LEFT JOIN {usr} gu ON gu.id = s.gradedbyusr
                WHERE s.ltiassessment = ?
                ORDER BY s.timesubmitted DESC
                ";

        if (!$subs = get_records_sql_assoc($sql, array($SESSION->get('lti.assessment')))) {
            return array();
        }

        $return = array();

        foreach ($subs as $id => $sub) {

            $return[$id] = new stdClass;

            $return[$id]->timesubmitted = $sub->timesubmitted;
            $return[$id]->timegraded = $sub->timegraded;
            $return[$id]->grade = $sub->grade;
            $return[$id]->name = !empty($sub->collectionid) ? $sub->collectionname : $sub->viewtitle;
            $return[$id]->collectionid = $sub->collectionid;
            $return[$id]->viewid = $sub->viewid;

            // Create user object
            $return[$id]->user = new stdClass;
            $return[$id]->user->id = $sub->usr;
            foreach ($userfields as $field) {
                $return[$id]->user->$field = $sub->$field;
            }

            // Create user object for grader
            $return[$id]->grader = new stdClass;
            $return[$id]->grader->id = $sub->gradedbyusr;
            foreach ($userfields as $field) {
                $return[$id]->grader->$field = $sub->{"gu_$field"};
            }
        }

        return $return;
    }


    public function get_grade_dialogue($collectionid, $viewid) {
        global $SESSION;

        if (empty($SESSION->get('lti.assessment'))) {
            return false;
        }

        if (!self::can_grade()) {
            return false;
        }

        if (!$sub = new ModuleLtiSubmission($SESSION->get('lti.assessment'), null, $collectionid, $viewid)) {
            return false;
        }

        if (!$sub->is_submitted()) {
            return false;
        }

        $form = array(
            'name' => 'assessmentgrading',
            'successcallback' => 'PluginModuleLti::assessmentgrading_submit',
            'method' => 'post',
            'action' => '',
            'plugintype' => 'module',
            'pluginname' => 'lti',
            'elements' => array(
                'grade' => array(
                    'type' => 'select',
                    'title' => get_string('grade', 'module.lti'),
                    'description' => get_string('grade_description', 'module.lti'),
                    'help' => true,
                    'rules' => array(
                        'required' => true
                    ),
                    'defaultvalue' => "",
                    'options' => array("" => get_string('grade', 'module.lti')) + array_combine(range(100, 0), range(100, 0)),
                ),
                'collectionid' => array(
                    'type' => 'hidden',
                    'value' => $collectionid,
                ),
                'viewid' => array(
                    'type' => 'hidden',
                    'value' => $viewid,
                ),
                'submit' => array(
                    'type'  => 'submit',
                    'value' => get_string('submit'),
                    'class' => 'btn btn-primary',
                )
            ),
        );

        return (pieform($form));
    }

    public function assessmentgrading_submit(Pieform $form, $values) {
        global $SESSION;

        $sub = new ModuleLtiSubmission($SESSION->get('lti.assessment'), null, $values['collectionid'], $values['viewid']);

        if (!$sub->assign_grade($values['grade'])) {
            $SESSION->add_error_msg(get_string('ltioutcomesubmissionfailure', 'module.lti', $sub->lti_error));
            if (!empty($sub->collectionid)) {
                $collection = new Collection($sub->collectionid);
                redirect($collection->get_url());
            }
            else {
                $view = new View($sub->viewid);
                redirect($view->get_url());
            }
        }
        else {
            $SESSION->add_ok_msg(get_string('gradesubmitted', 'module.lti'));
            redirect('/module/lti/submission.php');
        }

    }

    public static function config_form() {
        global $SESSION;

        $data = get_record('lti_assessment', 'id', $SESSION->get('lti.assessment'));

        $form = array(
            'name' => 'assessmentgrading',
            'successcallback' => 'PluginModuleLti::config_submit',
            'method' => 'post',
            'action' => '',
            'elements' => array(
                'emailnotifications' => array(
                    'type' => 'switchbox',
                    'defaultvalue' => $data->emailnotifications,
                    'title' => get_string('emailtutors', 'module.lti'),
                    'description' => get_string('emailtutorsdescription', 'module.lti'),
                ),
                'lock' => array(
                    'type' => 'switchbox',
                    'defaultvalue' => $data->lock,
                    'title' => get_string('lock', 'module.lti'),
                    'description' => get_string('lockdescription', 'module.lti'),
                ),
                'archive' => array(
                    'type' => 'switchbox',
                    'defaultvalue' => $data->archive,
                    'title' => get_string('archive', 'module.lti'),
                    'description' => get_string('archivedescription', 'module.lti'),
                ),
                'submit' => array(
                    'type'  => 'submit',
                    'class' => 'btn btn-primary',
                    'value' => empty($data->timeconfigured) ? get_string('saveandrelease', 'module.lti') : get_string('save')
                )
            ),
        );

        return (pieform($form));
    }

    public static function config_submit($form, $values) {
        global $SESSION;

        $rec = new stdClass;
        $rec->id = $SESSION->get('lti.assessment');
        $rec->emailnotifications = $values['emailnotifications']  ? 1 : 0;
        $rec->lock = $values['lock'] ? 1 : 0;
        $rec->archive = $values['archive']  ? 1 : 0;
        $rec->timeconfigured = db_format_timestamp(time());

        update_record('lti_assessment', $rec);

        $assessment = get_record('lti_assessment', 'id', $SESSION->get('lti.assessment'));

        $group = new stdClass;
        $group->id = $assessment->group;
        $group->allowarchives = $values['archive']  ? 1 : 0;

        update_record('group', $group);

        $SESSION->add_ok_msg(get_string('configurationsaved', 'module.lti'));

        redirect('/module/lti/submission.php');
    }

}

class ModuleLtiSubmission {

    public $assessment;
    public $lisresultsourceqid;


    public $id;
    public $userid;
    public $collectionid;
    public $viewid;
    public $grade;
    public $gradedbyusr;
    public $timesubmitted;

    protected $submitted = false;

    public function __construct($assessment, $userid = null, $collectionid = null, $viewid = null) {

        if (!$this->assessment = get_record('lti_assessment', 'id', $assessment)) {
            throw new MaharaException('Unknown lti_assessment.id');
        }

        $this->userid = $userid;
        if (!is_null($userid)) {
            $sub = get_record('lti_assessment_submission',  'ltiassessment', $this->assessment->id, 'usr', $userid);
        }
        else if (!is_null($collectionid)) {
            $sub = get_record('lti_assessment_submission',  'ltiassessment', $this->assessment->id, 'collectionid', $collectionid);
        }
        else if (!is_null($viewid)) {
            $sub = get_record('lti_assessment_submission',  'ltiassessment', $this->assessment->id, 'viewid', $viewid);
        }

        if (!empty($sub)) {
            $this->id = $sub->id;
            $this->submitted = true;
            $this->collectionid = $sub->collectionid;
            $this->viewid = $sub->viewid;
            $this->grade = $sub->grade;
            $this->timegraded = $sub->timegraded;
            $this->gradedbyusr = $sub->gradedbyusr;
            $this->timesubmitted = $sub->timesubmitted;
            $this->lisresultsourceid = $sub->lisresultsourceid;
        }
        else {
            return false;
        }

        return true;
    }

    /**
     * Has the portfolio been submitted for grading
     *
     * @return boolean
     */
    public function is_submitted() {
        return $this->submitted;
    }

    /**
     * Get basic information about a submitted portfolio
     *
     * @return stdClass
     */
    public function get_portfolio_info() {
        $info = new stdClass;

        if (!empty($this->collectionid)) {
            $info->title = get_field('collection', 'name', 'id', $this->collectionid);
            $info->link  = get_config('wwwroot') . 'collection/views.php?id=' . $this->collectionid;
        }
        else if (!empty($this->viewid)) {
            $info->title = get_field('view', 'title', 'id', $this->viewid);
            $info->link  = get_config('wwwroot') . 'view/view.php?id=' . $this->viewid;
        }
        else {
            return false;
        }

        return $info;
    }

    /**
     * Get info about the user who graded the submission
     */
    public function get_grader() {
        if (!empty($this->gradedbyusr)) {
            $userobj = new User();
            $userobj->find_by_id($this->gradedbyusr);

            return $userobj;
        }
        return false;
    }

    /**
     * Assign grade
     *
     * @var int $grade 0..100
     */
    public function assign_grade($grade) {
        global $USER;

        db_begin();

        $sub = new stdClass;

        $sub->id = $this->id;
        $sub->grade = $grade;
        $sub->timegraded = db_format_timestamp(time());
        $sub->gradedbyusr = $USER->get('id');

        update_record('lti_assessment_submission', $sub);

        $this->timegraded = $sub->timegraded;
        $this->gradedbyusr = $sub->gradedbyusr;
        $this->grade = $sub->grade;

        if (!$this->publish_lti_outcome()) {
            db_rollback();
            return false;
        }
        db_commit();

        // Archive/Unlock if required by settings
        if (!empty($this->collectionid)) {
            $portfolio = new Collection($this->collectionid);
        }

        if (!empty($this->viewid)) {
            $portfolio = new View($this->viewid);
        }

        if ($this->assessment->lock) {
            if ($this->assessment->archive) {
                require_once(get_config('docroot') . 'export/lib.php');
                add_submission_to_export_queue($portfolio, $USER);
            }
        }
        else {
            if ($this->assessment->archive) {
                $portfolio->pendingrelease($USER);
            }
            else {
                $portfolio->release($USER);
            }
        }

        return true;
    }

    private function publish_lti_outcome() {

        require_once(get_config('docroot') . 'webservice/libs/oauth-php/OAuthRequester.php');

        $smarty = smarty();
        $smarty->assign('sourceid', $this->lisresultsourceid);
        $smarty->assign('messageidentifier', sha1(uniqid(time(), true)));
        $smarty->assign('score', $this->grade / 100);
        $body = $smarty->fetch('module:lti:xmlreplaceresult.tpl');
        $bodyhash = base64_encode(sha1($body, true));

        $consumer = get_record('oauth_server_registry', 'id', $this->assessment->oauthserver);

        $oauth_options = array(
            'consumer_key' => $consumer->consumer_key,
            'consumer_secret' => $consumer->consumer_secret,
        );

        OAuthStore::instance("2Leg", $oauth_options );

        $request = new OAuthRequester($this->assessment->lisoutcomeserviceurl, 'POST', array('oauth_body_hash' => $bodyhash), $body);
        $rawresponse = $request->doRequest(0, array(CURLOPT_HTTPHEADER => array('Content-type: application/xml')));

        if (!$response = new SimpleXMLElement($rawresponse['body'])) {
            return false;
        }

        if ($response->imsx_POXHeader->imsx_POXResponseHeaderInfo->imsx_statusInfo->imsx_codeMajor != 'success') {
            $this->lti_error = (string) $response->imsx_POXHeader->imsx_POXResponseHeaderInfo->imsx_statusInfo->imsx_description;
            return false;
        }

        return true;

    }
}