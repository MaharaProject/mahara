<?php
/**
 * @package    mahara
 * @subpackage test/behat
 * @author     Son Nguyen
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  portions from Moodle Behat, 2013 David Monllaó
 *
 */

require_once(__DIR__ . '/BehatBase.php');

use Behat\Gherkin\Node\TableNode as TableNode;
// use Behat\Behat\Exception\PendingException as PendingException;
use Behat\Behat\Tester\Exception\PendingException;

/**
 * Class to set up quickly a Given environment.
 *
 */
class BehatDataGenerators extends BehatBase {

    /**
     * @var TestingDataGenerator
     */
    protected $datagenerator;

    /**
     * Each element specifies:
     * - The data generator suffix used.
     * - The available fields array(fieldname=>fieldtype)
     * - The required fields.
     * - The mapping between other elements references and database field names.
     * @var array
     */
    protected static $elements = array(
        'users' => array(
            'datagenerator' => 'user',
            'available' => array(
                'username'     => 'text',
                'password'     => 'text',
                'email'        => 'text',
                'firstname'    => 'text',
                'lastname'     => 'text',
                'institution'  => 'text',
                'role'         => 'text',
                'authname'     => 'text',
                'remoteusername' => 'text',
                'studentid'    => 'text',
                'preferredname' => 'text',
                'town'         => 'text',
                'country'      => 'text',
                'occupation'   => 'text',
            ),
            'required' => array('username', 'password', 'email', 'firstname', 'lastname')
        ),
        'groups' => array(
            'datagenerator' => 'group',
            'available' => array(
                'name'             => 'text',
                'owner'            => 'text',
                'description'      => 'text',
                'grouptype'        => 'text',
                'open'             => 'bool',
                'controlled'       => 'bool',
                'request'          => 'bool',
                'invitefriends'    => 'bool',
                'suggestfriends'   => 'bool',
                'editroles'        => 'text',
                'submittableto'    => 'bool',
                'allowarchives'    => 'bool',
                'editwindowstart'  => 'text',
                'editwindowend'    => 'text',
                'members'          => 'text',
                'staff'            => 'text',
                'admins'           => 'text',
                'institution'      => 'text',
                'public'           => 'bool',
                'attachments'      => 'text'
            ),
            'required' => array('name', 'owner')
        ),
        'institutions' => array(
            'datagenerator' => 'institution',
            'available' => array(
              'name'                       => 'text',
              'displayname'                => 'text',
              'showonlineusers'            => 'number',
              'registerallowed'            => 'bool',
              'registerconfirm'            => 'bool',
              'lang'                       => 'text',
              'theme'                      => 'text',
              'dropdownmenu'               => 'bool',
              'skins'                      => 'bool',
              'licensemandatory'           => 'bool',
              'licensedefault'             => 'text',
              'defaultquota'               => 'text',
              'defaultmembershipperiod'    => 'number',
              'maxuseraccounts'            => 'number',
              'expiry'                     => 'text',
              'allowinstitutionpublicviews'=> 'bool',
              'progresscompletion'         => 'bool',
              'commentsortorder'           => 'text',
              'commentthreaded'            => 'bool',
              'members'                    => 'text',
              'staff'                      => 'text',
              'admins'                     => 'text',
              'authname'                   => 'string',
              'tags'                       => 'bool',
            ),
            'required' => array('name', 'displayname')
        ),
        'pages' => array(
            'datagenerator' => 'view',
            'available' => array(
                'title'            => 'text',
                'description'      => 'text',
                'ownertype'        => 'text',
                'ownername'        => 'text',
                'layout'           => 'text',
                'tags'             => 'text',
                'instructions'     => 'text',
            ),
            'required' => array('title', 'ownertype', 'ownername')
        ),
        'blocks' => array(
            'datagenerator' => 'block',
            'available' => array(
                'title'            => 'text',
                'type'             => 'text',
                'data'             => 'text',
                'page'             => 'text',
                'retractable'      => 'text',
                'updateonly'       => 'bool',
            ),
            'required' => array('title', 'type', 'page')
        ),
        'collections' => array(
            'datagenerator' => 'collection',
            'available' => array(
                'title'              => 'text',
                'description'        => 'text',
                'ownertype'          => 'text',
                'ownername'          => 'text',
                'pages'              => 'text',
                'lock'               => 'bool',
                'progresscompletion' => 'bool',
                'outcomeportfolio'   => 'bool',
                'outcomecategory'    => 'text',

            ),
            'required' => array('title', 'ownertype', 'ownername')
        ),
        'permissions' => array(
            'datagenerator' => 'permission',
            'available' => array(
                'title'            => 'text',
                'accesstype'       => 'text',
                'accessname'       => 'text',
                'allowcomments'    => 'bool',
                'approvecomments'  => 'bool',
                'role'             => 'text',
                'multiplepermissions'   => 'bool', // Set to true if wanting to add multiple access rules to a view
            ),
            'required' => array('title', 'accesstype')
        ),
        'group memberships' => array(
            'datagenerator' => 'group_membership',
            'required' => array('username', 'groupname', 'role')
        ),
        'institution memberships' => array(
            'datagenerator' => 'institution_membership',
            'required' => array('username', 'institutionname', 'role')
        ),
        'messages' => array(
            'datagenerator' => 'message',
            'available' => array(
                'emailtype'        => 'text',
                'to'               => 'text',
                'from'             => 'text',
                'subject'          => 'text',
                'messagebody'      => 'text',
                'read'             => 'bool',
                'url'              => 'text',
                'urltext'          => 'text',
            ),
           'required' => array('emailtype', 'to', 'subject')
        ),
        'journals' => array(
            'datagenerator' => 'blog',
            'available' => array(
                'owner'            => 'text',
                'ownertype'        => 'text',
                'title'            => 'text',
                'description'      => 'text',
                'tags'             => 'text',
            ),
           'required' => array('owner', 'ownertype', 'title')
        ),
        'journalentries' => array(
            'datagenerator' => 'blogpost',
            'available' => array(
                'owner'            => 'text',
                'ownertype'        => 'text',
                'title'            => 'text',
                'entry'            => 'text',
                'blog'             => 'text',
                'tags'             => 'text',
                'draft'            => 'bool',
            ),
           'required' => array('owner', 'ownertype', 'title', 'entry')
        ),
        'plans' => array(
            'datagenerator' => 'plan',
            'available' => array(
                'owner'            => 'text',
                'ownertype'        => 'text',
                'title'            => 'text',
                'description'      => 'text',
                'tags'             => 'text',
            ),
           'required' => array('owner', 'ownertype', 'title')
        ),
        'tasks' => array(
          'datagenerator' => 'task',
          'available' => array(
            'owner'                => 'text',
            'ownertype'            => 'text',
            'plan'                 => 'text',
            'title'                => 'text',
            'description'          => 'text',
            'completiondate'       => 'text',
            'completed'            => 'bool',
            'tags'                 => 'text'
          ),
          'required' => array('owner', 'ownertype', 'plan', 'title', 'completiondate', 'completed')
        ),
        'forums' => array(
          'datagenerator' => 'forum',
          'available' => array(
            'title'                => 'text',
            'description'          => 'text',
            'group'                => 'text',
            'creator'              => 'text',
            'config'               => 'text'
          ),
          'required' => array('title','description','group','creator')
        ),
        'forumposts' => array(
          'datagenerator' => 'forumpost',
          'available' => array(
            'group'                => 'text',
            'forum'                => 'text',
            'subject'              => 'text',
            'message'              => 'text',
            'user'                 => 'text',
            'topic'                => 'text',
            'attachments'          => 'text'
          ),
          'required' => array('group', 'message', 'user')
        ),
        'personalinformation' => array(
          'datagenerator' => 'resume_personalinformation',
          'available' => array(
            'user'                 => 'text',
            'dateofbirth'          => 'text',
            'placeofbirth'         => 'text',
            'citizenship'          => 'text',
            'visastatus'           => 'text',
            'gender'               => 'text',
            'maritalstatus'        => 'text'
          ),
          'required' => array('user', 'dateofbirth')
        ),
        'goals and skills' => array(
          'datagenerator' => 'resume_goalsandskills',
          'available' => array(
            'user'                 => 'text',
            'goaltype/skilltype'   => 'text',
            'title'                => 'text',
            'description'          => 'text',
            'attachment'           => 'text'
          ),
          'required' => array('user','goaltype/skilltype','title')
        ),
        'interests' => array(
          'datagenerator' => 'resume_interests',
          'available' => array(
            'user'                 => 'text',
            'interest'             => 'text',
            'description'          => 'text'
          ),
          'required' => array('user','interest','description')
        ),
        'coverletters' => array(
          'datagenerator' => 'resume_coverletter',
          'available' => array(
            'user'                 => 'text',
            'content'              => 'text'
          ),
          'required' => array('user','content')
        ),
        'educationhistory' => array(
          'datagenerator' => 'resume_educationhistory',
          'available' => array(
            'user'                   => 'text',
            'startdate'              => 'text',
            'enddate'                => 'text',
            'institution'            => 'text',
            'institutionaddress'     => 'text',
            'qualtype'               => 'text',
            'qualname'               => 'text',
            'qualdescription'        => 'text',
            'attachment'             => 'text',
            'displayorder'           => 'text'
          ),
          'required' => array('user','startdate','institution')
        ),
        'employmenthistory' => array(
          'datagenerator' => 'resume_employmenthistory',
          'available' => array(
            'user'                   => 'text',
            'startdate'              => 'text',
            'enddate'                => 'text',
            'employer'               => 'text',
            'employeraddress'        => 'text',
            'jobtitle'               => 'text',
            'positiondescription'    => 'text',
            'attachment'             => 'text',
            'displayorder'           => 'text'
          ),
          'required' => array ('user','startdate','employer','jobtitle')
        ),
        'contactinformation' => array(
          'datagenerator' => 'resume_contactinformation',
          'available' => array(
            'user'                     => 'text',
            'email'                    => 'text',
            'officialwebsite'          => 'text',
            'personalwebsite'          => 'text',
            'blogaddress'              => 'text',
            'town'                     => 'text',
            'city/region'              => 'text',
            'country'                  => 'text',
            'homenumber'               => 'text',
            'businessnumber'           => 'text',
            'mobilenumber'             => 'text',
            'faxnumber'                => 'text',
          ),
          'required' => array('user','email')
        ),
        'achievements' => array(
          'datagenerator' => 'resume_certification',
          'available' => array(
            'user'                    => 'text',
            'date'                    => 'text',
            'title'                   => 'text',
            'description'             => 'text',
            'attachment'              => 'text'
          ),
          'required' => array('user','title')
        ),
        'books and publications' => array(
          'datagenerator' => 'resume_book',
          'available' => array(
            'user'                     => 'text',
            'date'                     => 'text',
            'title'                    => 'text',
            'contribution'             => 'text',
            'description'              => 'text',
            'url'                      => 'text',
            'attachment'               => 'text'
          ),
          'required' => array('user','date','title','contribution')
        ),
        'professionalmemberships' => array(
          'datagenerator' => 'resume_membership',
          'available' => array(
            'user'                     => 'text',
            'startdate'                => 'text',
            'enddate'                  => 'text',
            'title'                    => 'text',
            'description'              => 'text',
            'attachment'               => 'text'
          ),
          'required' => array('user','startdate','title')
        ),
        'pagecomments' => array(
          'datagenerator' => 'page_comment',
          'available' => array(
            'user'                     => 'text',
            'comment'                  => 'text',
            'attachment'               => 'text',
            'private'                  => 'bool',
            'page'                     => 'text',
            'group'                    => 'text' // compulsory for comments on group pages
          ),
          'required' => array('user', 'comment', 'page')
    ),
    'outcometypes' => [
      'datagenerator' => 'outcome_type',
      'available' => [
        'abbreviation' => 'text',
        'title'        => 'text',
        'styleclass'   => 'text',
        'outcome_category' => 'text',
        'institution' => 'text',
        'css_colour' => 'text'
      ],
      'required' => [
        'abbreviation',
        'title',
        'styleclass',
        'outcome_category',
        'institution',
        'abbreviation',
      ]
    ],
    'outcomesubjects' => [
      'datagenerator' => 'outcome_subject',
      'available' => [
        'abbreviation' => 'text',
        'title'        => 'text',
        'subject_category' => 'text',
        'institution' => 'text'
      ],
      'required' => ['abbreviation', 'title', 'subject_category', 'institution']
    ]
      );


    /**
     * Normalise values in a given record
     * For example, 'ON' -> 1, 'OFF' -> 0
     * @param array ('field' => 'values', ...) $record
     * @return $record
     */
     public function normalise($availablefields, &$record) {
         foreach ($record as $fieldname => &$value) {
             if ($availablefields[$fieldname] == 'bool') {
                 $value = trim($value);
                 // Normalise boolean values
                 if (strtolower($value) == 'on' || $value == '1' || $value == 'yes' || $value == 'true') {
                     $value = true;
                 }
                 else if (strtolower($value) == 'off' || $value == '0' || $value == 'no' || $value == 'false') {
                     $value = false;
                 }
             }
         }
     }

    /**
     * Validate field values in a given record
     *
     * @param array ('fieldname' => 'fieldtype', ...) $availablefields
     * @param array ('fieldname' => 'values', ...) $record
     * @return void
     * @throws MaharaBehatTestException
     */
    public function validate_fields($availablefields, $record) {
        foreach ($record as $fieldname => $fieldvalue) {
            if (!in_array($fieldname, array_keys($availablefields))) {
                throw new MaharaBehatTestException("The field '" . $fieldname . "' is not available.\n".
                                "All available fields are " . implode(',', array_keys($availablefields)));
            }
            if ($availablefields[$fieldname] == 'bool' && !is_bool($fieldvalue)) {
                throw new MaharaBehatTestException("The value '" . $fieldvalue . "' of the field '" . $fieldname . "' must be a boolean ('ON'|'OFF', '1'|'0', 'true'|'false', 'yes'|'no' are accepted boolean values).");
            }
            if ($availablefields[$fieldname] == 'number' && !is_numeric($fieldvalue)) {
                throw new MaharaBehatTestException("The value '" . $fieldvalue . "' of the field '" . $fieldname . "' must be a number.");
            }
        }
    }

    /**
     * Creates the specified element.
     *
     * @Given /^the following "(?P<element_string>(?:[^"]|\\")*)" exist:$/
     *
     * @throws MaharaBehatTestException
     * @throws PendingException
     * @param string    $elementname The name of the entity to add
     * @param TableNode $data
     */
    public function the_following_exist($elementname, TableNode $data) {

        // Now that we need them require the data generators.
        require_once(get_config('docroot') . '/testing/classes/generator/lib.php');

        if (empty(self::$elements[$elementname])) {
            throw new PendingException($elementname . ' data generator is not implemented');
        }

        $this->datagenerator = TestingUtil::get_data_generator();

        $elementdatagenerator = self::$elements[$elementname]['datagenerator'];
        $availablefields = self::$elements[$elementname]['available'];
        $requiredfields = self::$elements[$elementname]['required'];
        if (!empty(self::$elements[$elementname]['switchids'])) {
            $switchids = self::$elements[$elementname]['switchids'];
        }

        foreach ($data->getHash() as $elementdata) {

            // Normalise field values
            $this->normalise($availablefields, $elementdata);
            // Validate available fields for given element
            $this->validate_fields($availablefields, $elementdata);
            // Check if all the required fields are there.
            foreach ($requiredfields as $requiredfield) {
                if (!isset($elementdata[$requiredfield])) {
                    throw new MaharaBehatTestException($elementname . ' requires the field ' . $requiredfield . ' to be specified');
                }
            }

            // Preprocess the entities that requires a special treatment.
            if (method_exists($this, 'preprocess_' . $elementdatagenerator)) {
                $elementdata = $this->{'preprocess_' . $elementdatagenerator}($elementdata);
            }

            // Creates element.
            $methodname = 'create_' . $elementdatagenerator;
            if (method_exists($this->datagenerator, $methodname)) {
                // Using data generators directly.
                $this->datagenerator->{$methodname}($elementdata);
            }
            else if (method_exists($this, 'process_' . $elementdatagenerator)) {
                // Using an alternative to the direct data generator call.
                $this->{'process_' . $elementdatagenerator}($elementdata);
            }
            else {
                throw new PendingException($elementname . ' the create_ or process_ method is not implemented');
            }
        }

    }

}
