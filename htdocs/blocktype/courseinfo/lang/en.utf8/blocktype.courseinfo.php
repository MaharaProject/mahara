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
$string['placeholdermessage'] = 'This block needs to be fully configured before it can be used. It can only be fully configured when it is on a personal portfolio page.';
$string['completeconfiguration'] = 'Complete configuring this block to activate it.';
$string['completeconfigurationnotpossible'] = 'The institution\'s web service connection for this block needs to be established. Please ask the site administrator to set it up.';
$string['unabletofetchdata'] = 'Unable to fetch data for this page owner';
$string['fromdate'] = 'From date';
$string['fromdatedescription'] = 'Only show courses started after this date. Use the format %s';
$string['todate'] = 'To date';
$string['todatedescription'] = 'Only show courses started before this date. Use the format %s';
$string['externaluserid'] = 'ID of external account';
$string['dateoutofsync'] = 'This needs to be older than the "To date".';
$string['nocourses'] = 'No course information to display.';
$string['hours'] = 'Hours';
$string['totalhours'] = 'Total hours';
$string['course'] = 'course';
$string['courses'] = 'courses';
$string['coursetype'] = 'Course type';
$string['connectedwithexternalaccount'] = 'External account found';
$string['coursesresultsfromto'] = 'Courses found between %s and %s';
$string['coursesresultsfrom'] = 'Courses found from %s';
$string['coursesresultsto'] = 'Courses found to %s';
$string['completedondate'] = 'Completed on';
$string['organisation'] = 'Organisation';
$string['plugininfo'] = '<p>To display information about a person\'s course completions from an external site you need to set up the following:</p>
<ol>
<li>Have the \'blocktype/courseinfo\' plugin active.</li>
<li>Have \'Allow outgoing web service requests\' turned on in \'Administration menu → Web services → Configuration\' and have the \'Rest\' protocol active.</li>
<li>Go to \'Administration menu → Web services → Connection manager\' and choose the institution for which you want to establish the connection. Then choose the \'PluginBlocktypeCourseinfo:courseinfo - Course completion\' option in the drop-down menu and click \'Add\'.</li>
<li>Fill in the form with the following:
<ul>
<li>Name: Give this instance a name, e.g. \'Institution A: Moodle\'.</li>
<li>Connection enabled: Set to \'Yes\'.</li>
<li>Web service type: Choose \'REST\'.</li>
<li>Auth type: Choose \'Token\'.</li>
<li>Web service URL: Set to the URL of the external source\'s REST server, e.g. https://moodle/webservice/rest/server.php.</li>
<li>Token: Set to the token generated on the external service\'s side that has access to the exteral functions that are required here.</li>
<li>Fixed parameters to pass: Add any special parameters that the URL needs to pass, e.g. for Moodle you need to add \'moodlewsrestformat=json\'.</li>
<li>JSON encoded: Set to \'Yes\'.</li>
<li>External function for account ID: Set this to the external service\'s function that can return an ID based on the email address, e.g. for Moodle \'core_user_get_users_by_field\'.</li>
<li>External function for course completion: Set this to the external service\'s function that can return course completion information based on the account ID supplied, e.g. for Moodle a custom function such as  \'local_client_get_course_completion_data\'.</li>
</ul>
<li>Save the form.</li>
</ol>
<p>Now when a person adds the \'Course completion\' block to their page, it will fetch their external account ID, save it against the block, and then when viewing the page, it will fetch the completed courses for that account ID.</p>';
// For webservices
$string['novalidconnections'] = 'No valid connection objects.';
$string['novalidconnectionauthtype'] = 'Not valid web service type. It needs to use the "REST" type.';
$string['connectionresultsinvalid'] = 'Unable to fetch results from external source.';
$string['userid_function_title'] = 'External function for account ID';
$string['coursecompletion_function_title'] = 'External function for course completion';
