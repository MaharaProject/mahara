<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-courseinfo
 * @author     Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

$string['title'] = 'Course completion';
$string['name'] =  'Courseinfo';
$string['description'] = 'Display information about course completion from an external source';
$string['defaulttitledescription'] = 'A default title will be generated if you leave this blank.';
$string['blocktitleforowner'] = 'Course completion for %s';
$string['placeholdermessage'] = 'This block needs to be fully configured before it can be used. It can only be fully configured when it is on a user\'s portfolio page.';
$string['completeconfiguration'] = 'Complete configuring this block to activate it.';
$string['completeconfigurationnotpossible'] = 'The institution web service for this block needs to be established. Please ask you administrator to set it up.';
$string['unabletofetchdata'] = 'Unable to fetch data for this page owner';
$string['fromdate'] = 'From date';
$string['fromdatedescription'] = 'Only show courses started after this date. Use the format %s';
$string['todate'] = 'To date';
$string['todatedescription'] = 'Only show courses started before this date. Use the format %s';
$string['externaluserid'] = 'ID of external person';
$string['dateoutofsync'] = 'This needs to be older than the "To date".';
$string['nocourses'] = 'No course info to display.';
$string['hours'] = 'Hours';
$string['totalhours'] = 'Total hours';
$string['course'] = 'course';
$string['courses'] = 'courses';
$string['coursetype'] = 'Course type';
$string['connectedwithexternalaccount'] = 'External userid found';
$string['coursesresultsfromto'] = 'Courses found between %s and %s';
$string['coursesresultsfrom'] = 'Courses found from %s';
$string['coursesresultsto'] = 'Courses found to %s';
$string['completedondate'] = 'Completed on';
$string['organisation'] = 'Organisation';
$string['plugininfo'] = '<p>To display information about a person\'s course completion from an external site you need to set up the following:</p>
<ol>
<li>Have the \'blocktype/courseinfo\' plugin active</li>
<li>Have \'Allow outgoing web service requests\' turned on in "Administration" →  "Webservices" →  "Configuration" and have the \'Rest\' protocol active</li>
<li>Go to "Administration" →  "Webservices" →  "Connection manager" and choose the institution you want to make the connection for and then choose \'PluginBlocktypeCourseinfo:courseinfo - Course completion\' option in dropdown and then click \'Add\'</li>
<li>Fill in form with the following:
<ul>
<li>Name - give this instance a name, eg Institution A: Moodle</li>
<li>Connection enabled - set to \'Yes\'</li>
<li>Web service type - choose \'REST\'</li>
<li>Auth type - choose \'Token\'</li>
<li>Web service URL - set to the URL of the external source\'s REST server, eg http://moodle/webservice/rest/server.php</li>
<li>Token - set to the token generated on external source\'s side that has access to the exteral functions we require</li>
<li>Fixed parameters to pass - Add in any special parameters the URL needs to pass, eg for Moodle we need to add \'moodlewsrestformat=json\'</li>
<li>JSON encoded - set to \'Yes\'</li>
<li>External function for user ID - set this to the external service\'s function that can return a userid based on email address supplied, eg for Moodle \'core_user_get_users_by_field\'</li>
<li>External function for course completion - set this to the external service\'s function that can return course completion information based userid supplied, eg for Moodle \'local_wdhb_get_course_completion_data\'</li>
</ul>
<li>Save the form</li>
</ol>
<p>Now when a person adds the \'Course info\' block to their page it will fetch their external user ID and save it against the block and then when viewing the page it will fetch the completed courses of that user ID.</p>';
// For webservices
$string['novalidconnections'] = 'No valid connection objects.';
$string['novalidconnectionauthtype'] = 'No valid web service type. Needs to use the "REST" type.';
$string['connectionresultsinvalid'] = 'Unable to fetch results from external source.';
$string['userid_function_title'] = 'External function for user ID';
$string['coursecompletion_function_title'] = 'External function for course completion';
