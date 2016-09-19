<?php
/**
 * @package    mahara
 * @subpackage test/generator
 * @author     Son Nguyen, Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  portions from Moodle 2012, Petr Skoda {@link http://skodak.org}
 *
 */

require_once(get_config('libroot') . 'institution.php');
require_once(get_config('libroot') . 'group.php');
require_once(get_config('libroot') . 'view.php');

/**
 * Data generator class for unit tests and other tools like behat that need to create fake test sites.
 *
 */
use Behat\Behat\Exception\UndefinedException as UndefinedException;

class TestingDataGenerator {

    protected $usercounter = 0;
    protected $groupcount = 0;
    protected $institutioncount = 0;
    protected $tagcount = 0;

    /** @var array list of plugin generators */
    protected $generators = array();

    /** @var array lis of common last names */
    public $lastnames = array(
            'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Miller', 'Davis', 'García', 'Rodríguez', 'Wilson',
            'Müller', 'Schmidt', 'Schneider', 'Fischer', 'Meyer', 'Weber', 'Schulz', 'Wagner', 'Becker', 'Hoffmann',
            'Novák', 'Svoboda', 'Novotný', 'Dvořák', 'Černý', 'Procházková', 'Kučerová', 'Veselá', 'Horáková', 'Němcová',
            'Смирнов', 'Иванов', 'Кузнецов', 'Соколов', 'Попов', 'Лебедева', 'Козлова', 'Новикова', 'Морозова', 'Петрова',
            '王', '李', '张', '刘', '陈', '楊', '黃', '趙', '吳', '周',
            '佐藤', '鈴木', '高橋', '田中', '渡辺', '伊藤', '山本', '中村', '小林', '斎藤',
    );

    /** @var array lis of common first names */
    public $firstnames = array(
            'Jacob', 'Ethan', 'Michael', 'Jayden', 'William', 'Isabella', 'Sophia', 'Emma', 'Olivia', 'Ava',
            'Lukas', 'Leon', 'Luca', 'Timm', 'Paul', 'Leonie', 'Leah', 'Lena', 'Hanna', 'Laura',
            'Jakub', 'Jan', 'Tomáš', 'Lukáš', 'Matěj', 'Tereza', 'Eliška', 'Anna', 'Adéla', 'Karolína',
            'Даниил', 'Максим', 'Артем', 'Иван', 'Александр', 'София', 'Анастасия', 'Дарья', 'Мария', 'Полина',
            '伟', '伟', '芳', '伟', '秀英', '秀英', '娜', '秀英', '伟', '敏',
            '翔', '大翔', '拓海', '翔太', '颯太', '陽菜', 'さくら', '美咲', '葵', '美羽',
    );

    public $loremipsum = <<<EOD
Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Nulla non arcu lacinia neque faucibus fringilla. Vivamus porttitor turpis ac leo. Integer in sapien. Nullam eget nisl. Aliquam erat volutpat. Cras elementum. Mauris suscipit, ligula sit amet pharetra semper, nibh ante cursus purus, vel sagittis velit mauris vel metus. Integer malesuada. Nullam lectus justo, vulputate eget mollis sed, tempor sed magna. Mauris elementum mauris vitae tortor. Aliquam erat volutpat.
Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Pellentesque ipsum. Cras pede libero, dapibus nec, pretium sit amet, tempor quis. Aliquam ante. Proin in tellus sit amet nibh dignissim sagittis. Vivamus porttitor turpis ac leo. Duis bibendum, lectus ut viverra rhoncus, dolor nunc faucibus libero, eget facilisis enim ipsum id lacus. In sem justo, commodo ut, suscipit at, pharetra vitae, orci. Aliquam erat volutpat. Nulla est.
Vivamus luctus egestas leo. Aenean fermentum risus id tortor. Mauris dictum facilisis augue. Aliquam erat volutpat. Aliquam ornare wisi eu metus. Aliquam id dolor. Duis condimentum augue id magna semper rutrum. Donec iaculis gravida nulla. Pellentesque ipsum. Etiam dictum tincidunt diam. Quisque tincidunt scelerisque libero. Etiam egestas wisi a erat.
Integer lacinia. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Mauris tincidunt sem sed arcu. Nullam feugiat, turpis at pulvinar vulputate, erat libero tristique tellus, nec bibendum odio risus sit amet ante. Aliquam id dolor. Maecenas sollicitudin. Et harum quidem rerum facilis est et expedita distinctio. Mauris suscipit, ligula sit amet pharetra semper, nibh ante cursus purus, vel sagittis velit mauris vel metus. Nullam dapibus fermentum ipsum. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Pellentesque sapien. Duis risus. Mauris elementum mauris vitae tortor. Suspendisse nisl. Integer rutrum, orci vestibulum ullamcorper ultricies, lacus quam ultricies odio, vitae placerat pede sem sit amet enim.
In laoreet, magna id viverra tincidunt, sem odio bibendum justo, vel imperdiet sapien wisi sed libero. Proin pede metus, vulputate nec, fermentum fringilla, vehicula vitae, justo. Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur? Maecenas lorem. Etiam posuere lacus quis dolor. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Curabitur ligula sapien, pulvinar a vestibulum quis, facilisis vel sapien. Nam sed tellus id magna elementum tincidunt. Suspendisse nisl. Vivamus luctus egestas leo. Nulla non arcu lacinia neque faucibus fringilla. Etiam dui sem, fermentum vitae, sagittis id, malesuada in, quam. Etiam dictum tincidunt diam. Etiam commodo dui eget wisi. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Proin pede metus, vulputate nec, fermentum fringilla, vehicula vitae, justo. Duis ante orci, molestie vitae vehicula venenatis, tincidunt ac pede. Pellentesque sapien.
EOD;

    /**
     * To be called from data reset code only,
    * do not use in tests.
    * @return void
    */
    public function reset() {
        $this->usercounter = 0;
        $this->$groupcount = 0;
        $this->$institutioncount = 0;

        foreach ($this->generators as $generator) {
            $generator->reset();
        }
    }

    /**
     * Return generator for given plugin.
     * @param string $plugintype the plugin type, e.g. 'artefact' or 'blocktype'.
     * @param string $pluginname the plugin name, e.g. 'blog' or 'file'.
     * @return an instance of a plugin generator extending from CoreGenerator.
     */
    public function get_plugin_generator($plugintype, $pluginname) {
        $pluginfullname = "{$plugintype}.{$pluginname}";
        if (isset($this->generators[$pluginfullname])) {
            return $this->generators[$pluginfullname];
        }
        safe_require($plugintype, $pluginname, 'tests/generator/lib.php');

        $classname =  generate_generator_class_name($plugintype, $pluginname);

        if (!class_exists($classname)) {
            throw new UndefinedException("The plugin $pluginfullname does not support " .
                            "data generators yet. Class {$classname} not found.");
        }

        $this->generators[$pluginfullname] = new $classname($this);
        return $this->generators[$pluginfullname];

    }

    /**
     * Gets the user id from it's username.
     * @param string $username
     * @return int the user id
     *     = false if not exists
     */
    protected function get_user_id($username) {
        if (($res = get_records_sql_array('SELECT id FROM {usr} WHERE LOWER(TRIM(username)) = ?', array(strtolower(trim($username)))))
            && count($res) === 1) {
            return $res[0]->id;
        }
        return false;
    }

    /**
     * Gets the group id from it's name.
     * @param string $groupname
     * @return int the group id
     *     = false if not exists
     */
    protected function get_group_id($groupname) {
        if (($res = get_records_sql_array('SELECT id FROM {group} WHERE LOWER(TRIM(name)) = ?', array(strtolower(trim($groupname)))))
            && count($res) === 1) {
            return $res[0]->id;
        }
        return false;
    }

    /**
     * Gets the institution id from it's name.
     * @param string $instname
     * @return int the institution id
     *     = false if not exists
     */
    protected function get_institution_id($instname) {
        if (($res = get_records_sql_array('SELECT id FROM {institution} WHERE name = ?', array($instname)))
            && count($res) === 1) {
            return $res[0]->id;
        }
        return false;
    }

    /**
     * Gets the view id from it's title.
     * @param string $viewtitle
     * @return int the view id
     *     = false if not exists
     */
    protected function get_view_id($viewtitle) {
        if ($res = get_record('view', 'title', $viewtitle)) {
            return $res->id;
        }
        return false;
    }

    /**
     * Gets the id of one site administrator.
     * @return int the admin id
     *     = false if not exists
     */
    protected function get_first_site_admin_id() {
        if ($admins = get_records_sql_array('
            SELECT u.id
            FROM {usr} u
            WHERE u.admin = 1 AND u.active = 1', array())) {
            return $admins[0]->id;
        }
        return false;
    }

    /**
     * Gets the id of one administrator of the institution given by name.
     * @param string $instname
     * @return int the admin id
     *     = false if not exists
     */
    protected function get_first_institution_admin_id($instname) {
        if ($admins = get_records_sql_array('
            SELECT u.id
            FROM {usr} u
                INNER JOIN {usr_institution} ui ON ui.usr = u.id
            WHERE ui.institution = ?
                AND ui.admin = 1
                AND u.active = 1', array($instname))) {
            return $admins[0]->id;
        }
        return false;
    }

    /**
     * Gets the id of one administrator of the group given by ID.
     * @param int $groupid
     * @return int the group admin id
     *     = false if not exists
     */
    protected function get_first_group_admin_id($groupid) {
        if ($admins = get_records_sql_array('
            SELECT u.id
            FROM {usr} u
                INNER JOIN {group_member} gm ON gm.member = u.id
            WHERE  gm.group = ?
                AND gm.role = ?
                AND u.active = 1', array($groupid, 'admin'))) {
            return $admins[0]->id;
        }
        return false;
    }

    /**
     * Create a test user
     * @param array $record
     * @throws SystemException if creating failed
     * @return int new user id
     */
    public function create_user($record) {
        // Data validation
        // Set default auth method for a new user is 'internal' for 'No institution' if not set
        if (empty($record['institution']) || empty($record['authname'])) {
            $record['institution'] = 'mahara';
            $record['authname'] = 'internal';
        }
        if (!$auth = get_record('auth_instance', 'institution', $record['institution'], 'authname', $record['authname'])) {
            throw new SystemException("The authentication method authname" . $record['authname'] . " for institution '" . $record['institution'] . "' does not exist.");
        }
        $record['authinstance'] = $auth->id;
        // Don't exceed max user accounts for the institution
        $institution = new Institution($record['institution']);
        if ($institution->isFull()) {
            throw new SystemException("Can not add new users to the institution '" . $record['institution'] . "' as it is full.");
        }

        $record['firstname'] = sanitize_firstname($record['firstname']);
        $record['lastname']  = sanitize_lastname($record['lastname']);
        $record['email']     = sanitize_email($record['email']);

        $authobj = AuthFactory::create($auth->id);
        if (method_exists($authobj, 'is_username_valid_admin') && !$authobj->is_username_valid_admin($record['username'])) {
            throw new SystemException("New username'" . $record['username'] . "' is not valid.");
        }
        if (method_exists($authobj, 'is_username_valid') && !$authobj->is_username_valid($record['username'])) {
            throw new SystemException("New username'" . $record['username'] . "' is not valid.");
        }
        if (record_exists_select('usr', 'LOWER(username) = ?', array(strtolower($record['username'])))) {
            throw new ErrorException("The username'" . $record['username'] . "' has been taken.");
        }
        if (method_exists($authobj, 'is_password_valid') && !$authobj->is_password_valid($record['password'])) {
            throw new ErrorException("The password'" . $record['password'] . "' is not valid.");
        }
        if (record_exists('usr', 'email', $record['email'])
                        || record_exists('artefact_internal_profile_email', 'email', $record['email'])) {
            throw new ErrorException("The email'" . $record['email'] . "' has been taken.");
        }

        // Create new user
        db_begin();
        raise_time_limit(180);

        $user = (object)array(
                'authinstance'   => $record['authinstance'],
                'username'       => $record['username'],
                'firstname'      => $record['firstname'],
                'lastname'       => $record['lastname'],
                'email'          => $record['email'],
                'password'       => $record['password'],
                'passwordchange' => 0,
        );
        if ($record['institution'] == 'mahara') {
            if ($record['role'] == 'admin') {
                $user->admin = 1;
            }
            else if ($record['role'] == 'staff') {
                $user->staff = 1;
            }
        }

        $remoteauth = $record['authname'] != 'internal';
        if (!isset($record['remoteusername'])) {
            $record['remoteusername'] = null;
        }

        $user->id = create_user($user, array(), $record['institution'], $remoteauth, $record['remoteusername'], $record);

        if (isset($user->admin) && $user->admin) {
            require_once('activity.php');
            activity_add_admin_defaults(array($user->id));
        }

        if ($record['institution'] != 'mahara') {
            if ($record['role'] == 'admin') {
                set_field('usr_institution', 'admin', 1, 'usr', $user->id, 'institution', $record['institution']);
            }
            else if ($record['role'] == 'staff') {
                set_field('usr_institution', 'staff', 1, 'usr', $user->id, 'institution', $record['institution']);
            }
        }

        db_commit();
        $this->usercounter++;
        return $user->id;
    }

    /**
     * Create a test group
     * @param array $record
     * @throws ErrorException if creating failed
     * @return int new group id
     */
    public function create_group($record) {
        // Data validation
        $record['name'] = trim($record['name']);
        if ($ids = get_records_sql_array('SELECT id FROM {group} WHERE LOWER(TRIM(name)) = ?', array(strtolower($record['name'])))) {
            if (count($ids) > 1 || $ids[0]->id != $group_data->id) {
                throw new SystemException("Invalid group name '" . $record['name'] . "'. " . get_string('groupalreadyexists', 'group'));
            }
        }
        $record['owner'] = trim($record['owner']);
        $ids = get_records_sql_array('SELECT id FROM {usr} WHERE LOWER(TRIM(username)) = ?', array(strtolower($record['owner'])));
        if (!$ids || count($ids) > 1) {
            throw new SystemException("Invalid group owner '" . $record['owner'] . "'. The username does not exist or duplicated");
        }
        $members = array($ids[0]->id => 'admin');
        if (!empty($record['members'])) {
            foreach (explode(',', $record['members']) as $membername) {
                $ids = get_records_sql_array('SELECT id FROM {usr} WHERE LOWER(TRIM(username)) = ?', array(strtolower(trim($membername))));
                if (!$ids || count($ids) > 1) {
                    throw new SystemException("Invalid group member '" . $membername . "'. The username does not exist or duplicated");
                }
                $members[$ids[0]->id] = 'member';
            }
        }
        if (!empty($record['staff']) && !empty($record['grouptype'])) {
            foreach (explode(',', $record['staff']) as $membername) {
                $ids = get_records_sql_array('SELECT id FROM {usr} WHERE LOWER(TRIM(username)) = ?', array(strtolower(trim($membername))));
                if (!$ids || count($ids) > 1) {
                    throw new SystemException("Invalid group staff '" . $membername . "'. The username does not exist or duplicated");
                }
                if ($record['grouptype'] == 'course') {
                    $members[$ids[0]->id] = 'tutor';
                }
                else {
                    $members[$ids[0]->id] = 'admin';
                }
            }
        }
        if (!empty($record['admins'])) {
            foreach (explode(',', $record['admins']) as $membername) {
                $ids = get_records_sql_array('SELECT id FROM {usr} WHERE LOWER(TRIM(username)) = ?', array(strtolower(trim($membername))));
                if (!$ids || count($ids) > 1) {
                    throw new SystemException("Invalid group admin '" . $membername . "'. The username does not exist or duplicated");
                }
                $members[$ids[0]->id] = 'admin';
            }
        }
        $availablegrouptypes = group_get_grouptypes();
        if (!in_array($record['grouptype'], $availablegrouptypes)) {
            throw new SystemException("Invalid grouptype '" . $record['grouptype'] . "'. This grouptype does not exist.\n"
                            . "The available grouptypes are " . join(', ', $availablegrouptypes));
        }
        $availablegroupeditroles = array_keys(group_get_editroles_options());
        if (!in_array($record['editroles'], $availablegroupeditroles)) {
            throw new SystemException("Invalid group editroles '" . $record['editroles'] . "'. This edit role does not exist.\n"
                            . "The available group editroles are " . join(', ', $availablegroupeditroles));
        }
        if (!empty($record['open'])) {
            if (!empty($record['controlled'])) {
                throw new SystemException('Invalid group membership setting. ' . get_string('membershipopencontrolled', 'group'));
            }
            if (!empty($record['request'])) {
                throw new SystemException('Invalid group membership setting. ' . get_string('membershipopenrequest', 'group'));
            }
        }
        if (!empty($record['invitefriends']) && !empty($record['suggestfriends'])) {
            throw new SystemException('Invalid friend invitation setting. ' . get_string('suggestinvitefriends', 'group'));
        }
        if (!empty($record['suggestfriends']) && empty($record['open']) && empty($record['request'])) {
            throw new SystemException('Invalid friend invitation setting. ' . get_string('suggestfriendsrequesterror', 'group'));
        }
        if (!empty($record['editwindowstart']) && !empty($record['editwindowend']) && ($record['editwindowstart'] >= $record['editwindowend'])) {
            throw new SystemException('Invalid group editability setting. ' . get_string('editwindowendbeforestart', 'group'));
        }
        $group_data = array(
                'id'             => null,
                'name'           => $record['name'],
                'description'    => isset($record['description']) ? $record['description'] : null,
                'grouptype'      => $record['grouptype'],
                'open'           => isset($record['open']) ? $record['open'] : 1,
                'controlled'     => isset($record['controlled']) ? $record['controlled'] : 0,
                'request'        => isset($record['request']) ? $record['request'] : 0,
                'invitefriends'  => isset($record['invitefriends']) ? $record['invitefriends'] : 0,
                'suggestfriends' => isset($record['suggestfriends']) ? $record['suggestfriends'] : 0,
                'category'       => null,
                'public'         => 0,
                'usersautoadded' => 0,
                'viewnotify'     => GROUP_ROLES_ALL,
                'submittableto'  => isset($record['submittableto']) ? $record['submittableto'] : 0,
                'allowarchives'  =>  isset($record['allowarchives']) ? $record['allowarchives'] : 0,
                'editroles'      => isset($record['editroles']) ? $record['editroles'] : 'all',
                'hidden'         => 0,
                'hidemembers'    => 0,
                'hidemembersfrommembers' => 0,
                'groupparticipationreports' => 0,
                'urlid'          => null,
                'editwindowstart' => isset($record['editwindowstart']) ? $record['editwindowstart'] : null,
                'editwindowend'  => isset($record['editwindowend']) ? $record['editwindowend'] : null,
                'sendnow'        => 0,
                'feedbacknotify' => GROUP_ROLES_ALL,
                'members'        => $members,
        );

        // Create a new group
        db_begin();
        $group_data['id'] = group_create($group_data);
        db_commit();

        $this->groupcount++;
        return $group_data['id'];
    }

    /**
     * Create a test institution
     * @param array $record
     * @throws ErrorException if creating failed
     * @return int new institution id
     */
    public function create_institution($record) {
        // Data validation
        if (empty($record['name']) || !preg_match('/^[a-zA-Z]{1,255}$/', $record['name'])) {
            throw new SystemException("Invalid institution name '" . $record['name'] .
                         "'. The institution name is entered for system database identification only and must be a single text word without numbers or symbols.");
        }
        if (!empty($record['name']) && record_exists('institution', 'name', $record['name'])) {
            throw new SystemException("Invalid institution name '" . $record['name'] . "'. " . get_string('institutionnamealreadytaken', 'admin'));
        }

        if (get_config('licensemetadata') && !empty($record['licensemandatory']) &&
                        (isset($record['licensedefault']) && $record['licensedefault'] == '')) {
            throw new SystemException("Invalid institution license setting. " . get_string('licensedefaultmandatory', 'admin'));
        }

        if (!empty($record['lang']) && $record['lang'] != 'sitedefault' && !array_key_exists($record['lang'], get_languages())) {
            throw new SystemException("Invalid institution language setting: '" . $record['lang'] . "'. This language is not installed for the site.");
        }
        // Create a new institution
        db_begin();
        // Update the basic institution record...
        $newinstitution = new Institution();
        $newinstitution->initialise($record['name'], $record['displayname']);
        $institution = $newinstitution->name;

        $newinstitution->showonlineusers = !isset($record['showonlineusers']) ? 2 : $record['showonlineusers'];
        if (get_config('usersuniquebyusername')) {
            // Registering absolutely not allowed when this setting is on, it's a
            // security risk. See the documentation for the usersuniquebyusername
            // setting for more information
            $newinstitution->registerallowed = 0;
        }
        else {
            $newinstitution->registerallowed = !empty($record['registerallowed']) ? 1 : 0;
            $newinstitution->registerconfirm  = !empty($record['registerconfirm']) ? 1 : 0;
        }

        if (!empty($record['lang'])) {
            if ($record['lang'] == 'sitedefault') {
                $newinstitution->lang = null;
            }
            else {
                $newinstitution->lang = $record['lang'];
            }
        }

        $newinstitution->theme = (empty($record['theme']) || $record['theme'] == 'sitedefault') ? null : $record['theme'];
        $newinstitution->dropdownmenu = (!empty($record['dropdownmenu'])) ? 1 : 0;
        $newinstitution->skins = (!empty($record['skins'])) ? 1 : 0;
        $newinstitution->style = null;

        if (get_config('licensemetadata')) {
            $newinstitution->licensemandatory = (!empty($record['licensemandatory'])) ? 1 : 0;
            $newinstitution->licensedefault = (isset($record['licensedefault'])) ? $record['licensedefault'] : '';
        }

        $newinstitution->defaultquota = empty($record['defaultquota']) ? get_config_plugin('artefact', 'file', 'defaultquota') : $record['defaultquota'];

        $newinstitution->defaultmembershipperiod  = !empty($record['defaultmembershipperiod']) ? intval($record['defaultmembershipperiod']) : null;
        $newinstitution->maxuseraccounts = !empty($record['maxuseraccounts']) ? intval($record['maxuseraccounts']) : null;
        $newinstitution->expiry = !empty($record['expiry']) ? db_format_timestamp($record['expiry']) : null;

        $newinstitution->allowinstitutionpublicviews  = (isset($record['allowinstitutionpublicviews']) && $record['allowinstitutionpublicviews']) ? 1 : 0;

        // Save the changes to the DB
        $newinstitution->commit();

        // Automatically create an internal authentication authinstance
        $authinstance = (object)array(
                'instancename' => 'internal',
                'priority'     => 0,
                'institution'  => $newinstitution->name,
                'authname'     => 'internal',
        );
        insert_record('auth_instance', $authinstance);

        // We need to add the default lines to the site_content table for this institution
        // We also need to set the institution to be using default static pages to begin with
        // so that using custom institution pages is an opt-in situation
        $pages = site_content_pages();
        $now = db_format_timestamp(time());
        foreach ($pages as $name) {
            $page = new stdClass();
            $page->name = $name;
            $page->ctime = $now;
            $page->mtime = $now;
            $page->content = get_string($page->name . 'defaultcontent', 'install', get_string('staticpageconfiginstitution', 'install'));
            $page->institution = $newinstitution->name;
            insert_record('site_content', $page);

            $institutionconfig = new stdClass();
            $institutionconfig->institution = $newinstitution->name;
            $institutionconfig->field = 'sitepages_' . $name;
            $institutionconfig->value = 'mahara';
            insert_record('institution_config', $institutionconfig);
        }

        if (isset($record['commentthreaded'])) {
            set_config_institution($newinstitution->name, 'commentthreaded', (bool) $record['commentthreaded']);
        }

        db_commit();
    }

    /**
     * Create an empty view
     * @param array $record
     * @throws SystemException if creating failed
     * @return int new view id
     */
    public function create_view($record) {
        switch ($record['ownertype']) {
            case 'institution':
                if (empty($record['ownername'])) {
                    $record['institution'] = 'mahara';
                    break;
                }
                if ($institutionid = $this->get_institution_id($record['ownername'])) {
                    $record['institution'] = $record['ownername'];
                    // Find one of the institution admins
                    if (!$userid = $this->get_first_institution_admin_id($record['ownername'])) {
                        // Find one of site admins
                        $userid = $this->get_first_site_admin_id();
                    }
                }
                else {
                    throw new SystemException("The institution '" . $record['ownername'] . "' does not exist.");
                }
                break;
            case 'group':
                if ($groupid = $this->get_group_id($record['ownername'])) {
                    $record['group'] = $groupid;
                    // Find one of the group admins
                    if (!$userid = $this->get_first_group_admin_id($groupid)) {
                        throw new SystemException("The group '" . $record['ownername'] . "' must have at least one administrator.");
                    }
                }
                else {
                    throw new SystemException("The group '" . $record['ownername'] . "' does not exist.");
                }
                break;
            case 'user':
            default:
                if ($ownerid = get_field('usr', 'id', 'username', $record['ownername'])) {
                    $record['owner'] = $ownerid;
                    // Find one of the site admins
                    $userid = $this->get_first_site_admin_id();
                }
                else {
                    throw new SystemException("The user '" . $record['ownername'] . "' does not exist.");
                }
                break;
        }
        if (empty($userid)) {
            $userid = $this->get_first_site_admin_id();
        }
        require_once('view.php');
        $view = View::create($record, $userid);
    }

    /**
     * Create a collection of pages
     * @param array $record
     * @throws SystemException if creating failed
     * @return int new collection id
     */
    public function create_collection($record) {
        // Validation
        switch ($record['ownertype']) {
            case 'institution':
                if (empty($record['ownername'])) {
                    $record['institution'] = 'mahara';
                    break;
                }
                if ($institutionid = $this->get_institution_id($record['ownername'])) {
                    $record['institution'] = $record['ownername'];
                }
                else {
                    throw new SystemException("The institution '" . $record['ownername'] . "' does not exist.");
                }
                break;
            case 'group':
                if ($groupid = $this->get_group_id($record['ownername'])) {
                    $record['group'] = $groupid;
                }
                else {
                    throw new SystemException("The group '" . $record['ownername'] . "' does not exist.");
                }
                break;
            case 'user':
            default:
                if ($ownerid = get_field('usr', 'id', 'username', $record['ownername'])) {
                    $record['owner'] = $ownerid;
                }
                else {
                    throw new SystemException("The user '" . $record['ownername'] . "' does not exist.");
                }
                break;
        }
        // Check if the given pages exist and belong to the collection's owner
        $addviews = array();
        if (!empty($record['pages'])) {
            $record['pages'] = trim($record['pages']);
            $viewtitles = !empty($record['pages']) ?
                                  explode(',', $record['pages'])
                                : false;
            if (!empty($viewtitles)) {
                foreach ($viewtitles as $viewtitle) {
                    if (!empty($viewtitle) &&
                        ! $view = get_record_sql('
                            SELECT v.id
                            FROM {view} v
                                INNER JOIN {usr} u ON u.id = v.owner
                            WHERE v.title = ?
                                AND u.username = ?'
                            , array(trim($viewtitle), $record['ownername']))
                        ) {
                        throw new SystemException("The page '" . $viewtitle
                            . "' does not exist or not belong to the user '" . $record['ownername'] . "'.");
                    }
                    $addviews['view_' . $view->id] = true;
                }
            }
        }

        // Create a new collection
        require_once('collection.php');
        $data = new StdClass;
        $data->name = $record['title'];
        $data->description = $record['description'];
        if (!empty($record['group'])) {
            $data->group = $record['group'];
        }
        else if (!empty($record['institution'])) {
            $data->institution = $record['institution'];
        }
        else if (!empty($record['owner'])) {
            $data->owner = $record['owner'];
        }
        $collection = new Collection(0, $data);
        $collection->commit();
        // Add views to the collection
        if (!empty($addviews)) {
            $collection->add_views($addviews);
        }
    }

    /**
     * A fixture to set up page & collection permissions. Currently it only supports setting a blanket permission of
     * "public", "loggedin", "friends", or "private", and allowcomments & approvecomments
     *
     * Example:
     * Given the following "permissions" exist:
     * | title | accesstype | accessname | allowcomments |
     * | Page 1 | loggedin | loggedin | 1 |
     * | Collection 1 | public | public | 1 |
     * | Page 2 | user | userA | 0 |
     * @param unknown $record
     * @throws SystemException
     */
    public function create_permission($record) {
        $sql = "SELECT id, 'view' AS \"type\" FROM {view} WHERE LOWER(TRIM(title))=?
                UNION
                SELECT id, 'collection' AS \"type\" FROM {collection} WHERE LOWER(TRIM(name))=?";
        $title = strtolower(trim($record['title']));
        $ids = get_records_sql_array($sql, array($title, $title));
        if (!$ids || count($ids) > 1) {
            throw new SystemException("Invalid page/collection name '" . $record['title'] . "'. The page/collection title does not exist, or is duplicated.");
        }
        $id = $ids[0];
        $viewids = array();
        if ($id->type == 'view') {
            $viewids[] = $id->id;
        }
        else {
            $records = get_records_array('collection_view', 'collection', $id->id, 'displayorder', 'view');
            if (!$records) {
                throw new SystemException("Can't set permissions on empty collection named '" . $record['title'] . "'.");
            }
            foreach ($records as $view) {
                $viewids[] = $view->view;
            }
        }

        if ($record['accesstype'] == 'private') {
            $accesslist = array();
        }
        else {
            switch ($record['accesstype']) {
                case 'user':
                    $ids = get_records_sql_array('SELECT id FROM {usr} WHERE LOWER(TRIM(username)) = ?', array(strtolower(trim($record['accessname']))));
                    if (!$ids || count($ids) > 1) {
                        throw new SystemException("Invalid access user '" . $record['accessname'] . "'. The username does not exist or duplicated");
                    }
                    $id = $ids[0]->id;
                    $type = 'user';
                    break;
                case 'public':
                case 'friends':
                case 'loggedin':
                    $type = $id = $record['accesstype'];
                    break;
            }
            // TODO: This only supports one access record at a time per page
            $accesslist = array(
                array(
                    'startdate' => null,
                    'stopdate' => null,
                    'type' => $type,
                    'id' => $id,
                )
            );
        }
        $viewconfig = array(
            'startdate'       => null,
            'stopdate'        => null,
            'template'        => 0,
            'retainview'      => (int) (isset($record['retainview']) ? $record['retainview'] : 0),
            'allowcomments'   => (int) (isset($record['allowcomments']) ? $record['allowcomments'] : 1),
            'approvecomments' => (int) (isset($record['approvecomments']) ? $record['approvecomments'] : 0),
            'accesslist'      => $accesslist,
        );

        require_once('view.php');
        View::update_view_access($viewconfig, $viewids);
    }

    /**
     * A fixture to set up messages in bulk.
     * Currently it only supports setting friend request / accept internal notifications
     * @TODO allow for other types of messages
     *
     * Example:
     * Given the following "messages" exist:
     * | emailtype | to | from | subject | messagebody | read | url | urltext |
     * | friendrequest | userA | userB | New friend request | This is a friend request | 1 | user/view.php?id=[from] | Requests |
     * | friendaccept  | userB | userA | Friend request accepted | This is a friend request acceptance | 1 | user/view.php?id=[to] |  |
     * @param unknown $record
     * @throws SystemException
     */
    public function create_message($record) {
        $record['to'] = trim($record['to']);
        $to = get_records_sql_array('SELECT id FROM {usr} WHERE LOWER(TRIM(username)) = ?', array(strtolower($record['to'])));
        if (!$to || count($to) > 1) {
            throw new SystemException("Invalid user '" . $record['to'] . "'. The username does not exist or duplicated");
        }
        $to = $to[0]->id;
        $from = null;
        if (strtolower($record['from']) != 'system') {
            $from = get_records_sql_array('SELECT id FROM {usr} WHERE LOWER(TRIM(username)) = ?', array(strtolower($record['from'])));
            if (!$from || count($from) > 1) {
                throw new SystemException("Invalid user '" . $record['from'] . "'. The username does not exist or duplicated");
            }
            $from = $from[0]->id;
        }
        $emailtype = strtolower(trim($record['emailtype']));
        if (!in_array($emailtype, array('friendrequest', 'friendaccept'))) {
            throw new SystemException("Invalid emailtype '" . $emailtype . "'. The email type does not exist or is not yet set up");
        }
        $subject = !empty(trim($record['subject'])) ? trim($record['subject']) : 'Message subject';
        $messagebody= !empty(trim($record['messagebody'])) ? trim($record['messagebody']) : 'Message body';
        $read = !empty($record['read']) ? 1 : 0;
        $url = null;
        if (!empty(trim($record['url']))) {
            $url = trim($record['url']);
            // See if the url needs to have a correct id added to it. This works in the following way:
            // the behat writer specifies the url and places the id var in [ ] and indicates where to
            // get the id, eg 'view/user.php?id=[to]' means to fetch the id for the user specified in
            // the 'to' column, which will be set above as variable $to
            if (preg_match_all('/\[(?P<id>\w+)\]/', $url, $matches)) {
                // replace the matched ids with their id number and set up replacement patterns
                foreach ($matches['id'] as $k => $v) {
                    if (in_array($v, array('from', 'to'))) {
                        $matches['id'][$k] = $$v;
                        $matches[1][$k] = '/\[' . $v . '\]/';
                    }
                }
                $url = preg_replace($matches[1], $matches['id'], $url);
            }
        }
        $urltext = !empty(trim($record['urltext'])) ? trim($record['urltext']) : null;

        $users = array($to);
        $data = new stdClass();
        $data->url = $url;
        $data->users = $users;
        $data->fromuser = $from;
        $data->strings = (object) array('urltext' => (object) array('key' => $urltext));
        $data->subject = $subject;
        $data->message = $messagebody;

        $activity =  new ActivityTypeMaharamessage($data, false);
        $activity->notify_users();
    }
}
