<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-courses
 * @author     Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeCourseinfo extends MaharaCoreBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.courseinfo');
    }

    public static function get_instance_title(BlockInstance $instance) {
        $ownerid = $instance->get_view()->get('owner');
        if ($ownerid) {
            return get_string('blocktitleforowner', 'blocktype.courseinfo', display_name($ownerid, null, true));
        }
        return get_string('title', 'blocktype.courseinfo');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.courseinfo');
    }

    public static function single_only() {
        return true;
    }

    public static function get_categories() {
        return array('general');
    }

    public static function get_viewtypes() {
        return array('portfolio');
    }

    public static function get_css_icon($blocktypename) {
        return 'book-reader';
    }

    private static function check_connection_for_user($id) {
        $user = new stdClass();
        $user->id = $id;
        if ($connections = Plugin::get_webservice_connections($user, 'fetch_userid')) {
            return true;
        }
        return false;
    }

    public static function render_instance(BlockInstance $instance, $editing=false, $versioning=false) {
        global $exporter;
        $owner = $instance->get_view()->get('owner');
        $configdata = $instance->get('configdata');
        $smarty = smarty_core();
        if (!array_key_exists('userid', $configdata) && !$owner) {
            // We are just displaying the default message as the page is not owned by a user
            $smarty->assign('message', get_string('placeholdermessage','blocktype.courseinfo'));
        }
        else if (!array_key_exists('userid', $configdata) && $owner) {
            if ($editing) {
                if (self::check_connection_for_user($owner)) {
                    // We display the complete configuration message if we are page owner
                    $smarty->assign('message', get_string('completeconfiguration', 'blocktype.courseinfo'));
                }
                else {
                    $smarty->assign('message', get_string('completeconfigurationnotpossible', 'blocktype.courseinfo'));
                }
            }
            else {
                $smarty->assign('message', get_string('nocourses','blocktype.courseinfo'));
            }
        }
        else if (is_null($configdata['userid'])) {
            // Unable to find external user related to page owner
            if ($editing) {
                if (self::check_connection_for_user($owner)) {
                    $smarty->assign('message', get_string('unabletofetchdata','blocktype.courseinfo'));
                }
                else {
                    $smarty->assign('message', get_string('completeconfigurationnotpossible','blocktype.courseinfo'));
                }
            }
            else {
                $smarty->assign('message', get_string('nocourses','blocktype.courseinfo'));
            }
        }
        else {
            $configdata['ownerid'] = $owner;
            if ($exporter) {
                $courses = self::get_data($configdata, 0, 0);
            }
            else {
                $courses = self::get_data($configdata, 0, 10);
            }
            $template = 'blocktype:courseinfo:courserows.tpl';
            $blockid = $instance->get('id');
            if ($exporter) {
                $pagination = false;
            }
            else {
                $baseurl = $instance->get_view()->get_url();
                $baseurl .= ((false === strpos($baseurl, '?')) ? '?' : '&') . 'block=' . $blockid;
                $pagination = array(
                    'baseurl'    => $baseurl,
                    'id'         => 'block' . $blockid . '_pagination',
                    'datatable'  => 'coursedata_' . $blockid,
                    'jsonscript' => 'blocktype/courseinfo/courses.json.php',
                );
            }
            $configdata['block'] = $blockid;
            self::render_courses($courses, $template, $configdata, $pagination, $editing, $versioning);
            $smarty->assign('blockid', $instance->get('id'));
            $from = !empty($configdata['from']) ? format_date($configdata['from'], 'strftimedate') : null;
            $to = !empty($configdata['to']) ? format_date($configdata['to'], 'strftimedate') : null;
            if ($from && $to) {
                $resultstr = get_string('coursesresultsfromto', 'blocktype.courseinfo', $from, $to);
            }
            else if ($from && !$to) {
                $resultstr = get_string('coursesresultsfrom', 'blocktype.courseinfo', $from);
            }
            else if (!$from && $to) {
                $resultstr = get_string('coursesresultsto', 'blocktype.courseinfo', $to);
            }
            else {
                $resultstr = '';
            }
            $smarty->assign('resultstr', $resultstr);
            $smarty->assign('course', $courses);
        }
        return $smarty->fetch('blocktype:courseinfo:courseinfo.tpl');
    }

    public static function has_instance_config() {
        return true;
    }

    public static function postinst($fromversion) {
        if ($fromversion == 0) {
            // Have install disabled by default
            set_field('blocktype_installed', 'active', 0, 'name', 'courseinfo');
        }
    }

    public static function instance_config_form(BlockInstance $instance) {
        require_once('pieforms/pieform/elements/calendar.php');
        $configdata = $instance->get('configdata');
        $from = !empty($configdata['from']) ? $configdata['from'] : false;
        $to = !empty($configdata['to']) ? $configdata['to'] : false;
        $elements = array(
            'from' => array(
                'type'  => 'calendar',
                'title' => get_string('fromdate', 'blocktype.courseinfo'),
                'description' => get_string('fromdatedescription', 'blocktype.courseinfo', pieform_element_calendar_human_readable_dateformat()),
                'defaultvalue' => $from,
                'caloptions' => array(
                    'showsTime' => false,
                ),
            ),
            'to' => array(
                'type'  => 'calendar',
                'title' => get_string('todate', 'blocktype.courseinfo'),
                'description' => get_string('todatedescription', 'blocktype.courseinfo', pieform_element_calendar_human_readable_dateformat()),
                'defaultvalue' => $to,
                'caloptions' => array(
                    'showsTime' => false,
                ),
            )
        );
        $ownerid = $instance->get_view()->get('owner');
        if ($ownerid && !empty($configdata['userid'])) {
            $elements['externaluser'] = array('type' => 'html',
                                              'title' => get_string('externaluserid', 'blocktype.courseinfo'),
                                              'value' => $configdata['userid'],
                                              'class' => 'htmldescription');
        }

        return $elements;
    }

    public static function instance_config_validate(Pieform $form, $values) {
        $viewid = $form->get_element_option('id', 'value');
        $view = new View($viewid);
        if (!empty($values['from']) && !empty($values['to']) && $values['from'] > $values['to']) {
            $form->set_error('from', get_string('dateoutofsync', 'blocktype.courseinfo'));
        }
    }

    public static function instance_config_save($values, BlockInstance $instance) {
        $ownerid = $instance->get_view()->get('owner');
        if ($ownerid) {
            $values['userid'] = self::fetch_external_userid($ownerid);
        }
        return $values;
    }

    public static function fetch_external_userid($ownerid) {
        $username = get_field_sql("SELECT email FROM {artefact_internal_profile_email} WHERE owner = ? AND principal = ?", array($ownerid, 1)); // Check on primary email address
        $owner = new stdClass();
        $owner->id = $ownerid;
        $configdata['username'] = $username;
        if ($connections = Plugin::get_webservice_connections($owner, 'fetch_userid')) {
            foreach ($connections as $connection) {
                $result = call_static_method($connection->connection->class, 'fetch_userid', $connection, $owner, $configdata);
                if (!empty($result)) {
                    return $result;
                }
            }
        }
        return null;
    }

    public static function has_config_info() {
        return true;
    }

    public static function get_config_info() {
        return array('header' => self::get_title(),
                     'body' => get_string('plugininfo', 'blocktype.courseinfo'));
    }

    public static function default_copy_type() {
        return 'shallow';
    }

    public static function rewrite_blockinstance_config(View $view, $configdata) {
        if ($view->get('owner') !== null) {
            $externalid = self::fetch_external_userid($view->get('owner'));
            if ($externalid) {
                $configdata['userid'] = $externalid;
            }
            else {
                unset($configdata['userid']);
            }
        }
        else {
            unset($configdata['userid']);
        }
        return $configdata;
    }

    public static function get_data($configdata, $offset=0, $limit=10) {
        $owner = new stdClass();
        $owner->id = $configdata['ownerid'];
        $content = false;
        $total = 0;
        if ($connections = Plugin::get_webservice_connections($owner, 'fetch_coursecompletion')) {
            foreach ($connections as $connection) {
                $content = call_static_method($connection->connection->class, 'fetch_coursecompletion', $connection, $owner, $configdata);
            }
        }

        $results = array();
        if ($content) {
            $total = count($content['courses']);
            foreach ($content['courses'] as $k => $course) {
                $data = new StdClass();
                $data->title = $course['name'];
                $data->rawdate = $course['completiondate'];
                $data->date = format_date($course['completiondate'], 'strftimedate');
                $data->courselength = floatval($course['courselength']) > 0 ? $course['courselength'] : '-';
                $data->hours = floatval($course['courselength']);
                $data->category = $course['coursecategory'];
                $data->cpdhours_display = floatval($course['cpdhours']) > 0 ? $course['cpdhours'] : '-';
                $data->cpdhours = floatval($course['cpdhours']);
                $data->type = $course['coursetype'];
                $data->historical = $course['historical'];
                $data->organisation = $course['courseorganisation'];
                $data->id = $course['courseid'];
                $data->uniqueid = $data->id . '_' . $data->rawdate;
                $results[] = $data;
            }
        }
        // Sort by latest course first
        usort($results, function ($a, $b) {
            if ($a->rawdate == $b->rawdate) { return 0; }
            if ($a->rawdate < $b->rawdate) { return 1; }
            if ($a->rawdate > $b->rawdate) { return -1; }
        });
        // calculate grand total of hours spent
        $grandtotalhours = 0;
        if (!empty($results)) {
            foreach ($results as $result) {
                $grandtotalhours = $grandtotalhours + $result->cpdhours;
            }
        }
        if ($limit > 0) {
            $results = array_slice($results, $offset, $limit);
        }
        $result = array(
            'grandtotalhours' => $grandtotalhours,
            'count'           => $total,
            'data'            => $results,
            'offset'          => $offset,
            'limit'           => $limit,
            'id'              => 'course',
        );
        return $result;
    }

    public static function render_courses(&$courses, $template, $options, $pagination, $editing = false, $versioning = false) {
        $smarty = smarty_core();
        $smarty->assign('courses', $courses);
        $smarty->assign('options', $options);
        $smarty->assign('block', (!empty($options['block']) ? $options['block'] : null));
        $smarty->assign('versioning', $versioning);
        $courses['tablerows'] = $smarty->fetch($template);
        if ($courses['limit'] && $pagination) {
            $pagination = build_pagination(array(
                'id'                      => $pagination['id'],
                'class'                   => 'center',
                'datatable'               => $pagination['datatable'],
                'url'                     => $pagination['baseurl'],
                'jsonscript'              => $pagination['jsonscript'],
                'count'                   => $courses['count'],
                'limit'                   => $courses['limit'],
                'offset'                  => $courses['offset'],
                'numbersincludefirstlast' => false,
                'resultcounttextsingular' => get_string('course', 'blocktype.courseinfo'),
                'resultcounttextplural'   => get_string('courses', 'blocktype.courseinfo'),
            ));
            $courses['pagination']    = $pagination['html'];
            $courses['pagination_js'] = $pagination['javascript'];
        }
    }

    public static function define_webservice_connections() {
        return array(
            array(
                'connection' => 'courseinfo',
                'name' => get_string('title', 'blocktype.courseinfo'),
                'notes' => get_string('description', 'blocktype.courseinfo'),
                'version' => '1.0',
                'type' => WEBSERVICE_TYPE_REST,
                'isfatal' => false,
                'config_fields' => array(
                    'userid_function' => array(
                        'type' => 'text',
                        'title' => get_string('userid_function_title', 'blocktype.courseinfo'),
                    ),
                    'coursecompletion_function' => array(
                        'type' => 'text',
                        'title' => get_string('coursecompletion_function_title', 'blocktype.courseinfo'),
                    ),
                ),
            ),
        );
    }

    public static function fetch_coursecompletion($connection, $user, $configdata) {
        $data = array('userid' => $configdata['userid']);
        if (!empty($configdata['from'])) {
            $data['datefrom'] = $configdata['from'];
        }
        if (!empty($configdata['to'])) {
            $data['dateto'] = $configdata['to'];
        }
        // check if we have any valid connection objects
        if ($function = get_field('client_connections_config', 'value', 'field', 'coursecompletion_function', 'connection', $connection->connection->id)) {
            $results = self::test_connection($connection, $user, $data, $function);
            if ($results['error'] === false && !empty($results['results']) && is_array($results['results'])) {
                return $results['results'];
            }
        }
        return false;
    }

    public static function fetch_userid($connection, $user, $configdata) {
        $data = array('field' => 'email',
                      'values' => array($configdata['username']));
        // check if we have any valid connection objects
        if ($function = get_field('client_connections_config', 'value', 'field', 'userid_function', 'connection', $connection->connection->id)) {
            $results = self::test_connection($connection, $user, $data, $function);
            if ($results['error'] === false && !empty($results['results']) && is_array($results['results'])) {
                return $results['results'][0]['id'];
            }
        }
        return null;
    }

    private function test_connection($connection, $user, $data, $functionname) {
        if (empty($connection)) {
            return array('error' => true,
                         'errormsg' => get_string('novalidconnections', 'blocktype.courseinfo'));
        }

        if ($connection->connection->type != 'rest' || ($connection->connection->authtype == 'rest' && $connection->connection->authtype != 'token')) {
            return array('error' => true,
                         'errormsg' => get_string('novalidconnectionauthtype', 'blocktype.courseinfo'));
        }
        try {
            $results = $connection->call($functionname, $data, 'GET');
            if (isset($results['errorcode'])) {
                return array('error' => true,
                             'errormsg' => get_string('connectionresultsinvalid', 'blocktype.courseinfo'),
                             'results' => $results,
                             );
            }
            else {
                return array('error' => false,
                             'results' => $results);
            }
        }
        catch (Exception $e) {
            return array('error' => true,
                         'errormsg' => get_string('novalidconnectionauthtype', 'blocktype.courseinfo') . ' - ' . $e->getMessage());
        }
    }
}
