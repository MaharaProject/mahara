<?php
/**
 *
 * @package    mahara
 * @subpackage local
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

$string['reportdescuserdetails'] = '<ul>
<li>First name</li>
<li>Last name</li>
<li>Email address</li>
<li>ID number</li>
<li>Display name</li>
<li>Username</li>
<li>Remote username</li>
<li>Quota used</li>
<li>Last login</li>
<li>Number of probation points (if enabled)</li>
<li>Registration number</li>
<li>Registration status</li>
<li>APC start date</li>
</ul><p>People whose accounts were created during the selected time period are displayed.</p>';

$string['blocktype_myfriends'] = 'Block: My contacts';
$string['count_usr_friend'] = 'Number of contacts';
$string['plugin_blocktype_myfriends_version'] = 'Block type plugin My contacts version';

$string['reportdesccollaboration'] = '<ul>
<li>Number of comments</li>
<li>Number of annotation feedback</li>
<li>Number of portfolios (pages or collections) shared with individuals</li>
<li>Number of portfolios shared with groups</li>
<li>Number of portfolios shared with an institution</li>
<li>Number of portfolios shared with all registered people</li>
<li>Number of portfolios shared publicly</li>
<li>Number of portfolios shared via secret URLs</li>
<li>Number of portfolios shared with contacts</li>
</ul>';
$string['addfriend'] = 'Add a contact';
$string['removefriendrequest'] = 'Delete a contact request';
$string['removefriend'] = 'Remove a contact';
$string['addfriendrequest'] = 'Send a contact request';
$string['friendshare'] = 'Contacts';
$string['verifieroptions_current'] = 'Show authors with current verifiers';
$string['verifieroptions_none'] = 'Show authors without a current verifier';
$string['reportportfolioswithverifiers'] = 'Portfolios with verifiers';
$string['reportcompletionverification'] = 'Completion and verification';
$string['reportdesccompletionverification'] = '<ul>
<li>Personal information of the portfolio author</li>
<li>Email address of portfolio author</li>
<li>APC start date</li>
<li>Portfolio title</li>
<li>Portfolio creation date</li>
<li>Template title with link to it</li>
<li>Personal information of the verifier</li>
<li>Email address of the verifier</li>
<li>Date on which the verifier received access to the portfolio</li>
<li>Date on which the verifier confirmed the primary statement on the portfolio</li>
<li>Date on which the verifier removed their access to the portfolio</li>
<li>Date when the verifier\'s access to the portfolio was removed by the system</li>
<li>The Percentage of completions per portfolio per individual</li>
</ul>';
$string['reportdescportfolioswithverifiers'] = '<ul>
<li>Date of the week commencing for time period</li>
<li>Total number of portfolios with verifiers</li>
<li>Total number of portfolios without verifiers</li>
</ul>';
$string['withverifier'] = 'With verifiers';
$string['withoutverifier'] = 'Without verifiers';
$string['currentverifiersinfo'] = 'Current verifiers';
$string['reportverifiersummary'] = 'Verifier summary';
$string['userscompletionverificationreports'] = 'Completion and verification';
$string['usersportfolioswithverifiersreports'] = 'Portfolios with verifiers';
$string['accessrevokedbyaccessordate'] = 'Access revoked by verifier';
$string['portfolioveriferfilterdescription'] = 'Filter the results based on whether they have a verifier assigned or not';
$string['verifierfirstname'] = 'Verifier first name';
$string['verifierlastname'] = 'Verifier last name';
$string['verifierdisplayname'] = 'Verifier display name ';
$string['verifierusername'] = 'Verifier username';
$string['verifierregistrationnumber'] = 'Verifier registration number';
$string['verifieremail'] = 'Verifier email';
$string['dateverified'] = 'Verifier confirmed primary statement';
$string['portfolioverifierfilter'] = 'Verifier assigned';