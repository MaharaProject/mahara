<?php
/**
 * @package    mahara
 * @subpackage tests
 * @author     Nathan Lewis <nathan.lewis@totaralms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */

require_once(get_config('docroot') . '/blocktype/activitystream/lib.php');

class HomeStreamTest extends MaharaUnitTest {

    private $other;
    private $friend12;
    private $friend21;
    private $viewer;
    private $group;
    private $institution;

    private $conditions;
    private $individualstreamuser;

    const ACTIVITY_ID_MINE = 1001;
    const ACTIVITY_ID_MY_ARTEFACT = 1002;
    const ACTIVITY_ID_MY_VIEW = 1003;
    const ACTIVITY_ID_INSTITUTION = 1004;
    const ACTIVITY_ID_INSTITUTION_ARTEFACT = 1005;
    const ACTIVITY_ID_INSTITUTION_VIEW = 1006;
    const ACTIVITY_ID_GROUP = 1007;
    const ACTIVITY_ID_GROUP_ARTEFACT = 1008;
    const ACTIVITY_ID_GROUP_VIEW = 1009;
    const ACTIVITY_ID_WATCHED_VIEW = 1011;
    const ACTIVITY_ID_FRIEND12_ARTEFACT = 1012;
    const ACTIVITY_ID_FRIEND21_ARTEFACT = 1013;
    const ACTIVITY_ID_FRIEND12_VIEW = 1014;
    const ACTIVITY_ID_FRIEND21_VIEW = 1015;
    const ACTIVITY_ID_FRIEND12_SYSTEM = 1016;
    const ACTIVITY_ID_FRIEND21_SYSTEM = 1017;
    const ACTIVITY_ID_GROUP_INTERACTION = 1018;
    const ACTIVITY_ID_GROUP_FORUMTOPIC = 1019;

    const ARTEFACT_ID_MINE = 2001;
    const ARTEFACT_ID_OTHER = 2002;
    const ARTEFACT_ID_FRIEND12 = 2003;
    const ARTEFACT_ID_FRIEND21 = 2004;
    const ARTEFACT_ID_INSTITUTION = 2005;
    const ARTEFACT_ID_GROUP = 2006;

    const VIEW_ID_MINE = 3001;
    const VIEW_ID_OTHER = 3002;
    const VIEW_ID_FRIEND12 = 3003;
    const VIEW_ID_FRIEND21 = 3004;
    const VIEW_ID_INSTITUTION = 3005;
    const VIEW_ID_GROUP = 3006;
    const VIEW_ID_WATCHED = 3007;

    const INTERACTION_ID_INTERACTION = 4001;
    const INTERACTION_ID_FORUMTOPIC = 4002;

    private $artefactids = array(
            self::ARTEFACT_ID_MINE,
            self::ARTEFACT_ID_OTHER,
            self::ARTEFACT_ID_FRIEND12,
            self::ARTEFACT_ID_FRIEND21,
            self::ARTEFACT_ID_INSTITUTION,
            self::ARTEFACT_ID_GROUP,
        );

    private $viewids = array(
            self::VIEW_ID_MINE,
            self::VIEW_ID_OTHER,
            self::VIEW_ID_FRIEND12,
            self::VIEW_ID_FRIEND21,
            self::VIEW_ID_INSTITUTION,
            self::VIEW_ID_GROUP,
            self::VIEW_ID_WATCHED,
        );

    private $interactionids = array(
            self::INTERACTION_ID_INTERACTION,
            self::INTERACTION_ID_FORUMTOPIC,
        );

    private $activityids = array(
            self::ACTIVITY_ID_MINE,
            self::ACTIVITY_ID_MY_ARTEFACT,
            self::ACTIVITY_ID_MY_VIEW,
            self::ACTIVITY_ID_INSTITUTION,
            self::ACTIVITY_ID_INSTITUTION_ARTEFACT,
            self::ACTIVITY_ID_INSTITUTION_VIEW,
            self::ACTIVITY_ID_GROUP,
            self::ACTIVITY_ID_GROUP_ARTEFACT,
            self::ACTIVITY_ID_GROUP_VIEW,
            self::ACTIVITY_ID_WATCHED_VIEW,
            self::ACTIVITY_ID_FRIEND12_ARTEFACT,
            self::ACTIVITY_ID_FRIEND21_ARTEFACT,
            self::ACTIVITY_ID_FRIEND12_VIEW,
            self::ACTIVITY_ID_FRIEND21_VIEW,
            self::ACTIVITY_ID_FRIEND12_SYSTEM,
            self::ACTIVITY_ID_FRIEND21_SYSTEM,
            self::ACTIVITY_ID_GROUP_INTERACTION,
            self::ACTIVITY_ID_GROUP_FORUMTOPIC,
        );

    private $testcases = array(
            array('mine', self::ACTIVITY_ID_MINE),
            array('my_artefact', self::ACTIVITY_ID_MY_ARTEFACT),
            array('my_view', self::ACTIVITY_ID_MY_VIEW),
            array('institution', self::ACTIVITY_ID_INSTITUTION),
            array('institution_artefact', self::ACTIVITY_ID_INSTITUTION_ARTEFACT),
            array('institution_view', self::ACTIVITY_ID_INSTITUTION_VIEW),
            array('group', self::ACTIVITY_ID_GROUP),
            array('group_artefact', self::ACTIVITY_ID_GROUP_ARTEFACT),
            array('group_view', self::ACTIVITY_ID_GROUP_VIEW),
            array('watched_view', self::ACTIVITY_ID_WATCHED_VIEW),
            array('friend12_artefact_visible', self::ACTIVITY_ID_FRIEND12_ARTEFACT),
            array('friend21_artefact_visible', self::ACTIVITY_ID_FRIEND21_ARTEFACT),
            array('friend12_view_visible', self::ACTIVITY_ID_FRIEND12_VIEW),
            array('friend21_view_visible', self::ACTIVITY_ID_FRIEND21_VIEW),
            array('friend12', self::ACTIVITY_ID_FRIEND12_SYSTEM),
            array('friend21', self::ACTIVITY_ID_FRIEND21_SYSTEM),
            array('group_interaction', self::ACTIVITY_ID_GROUP_INTERACTION),
            array('group_forumtopic', self::ACTIVITY_ID_GROUP_FORUMTOPIC),
        );

    /**
     * Shared setUp method.
     */
    public function setUp() {
        parent::setUp();

        $this->institution = $this->create_test_institution(array('name' => 'testinstitution'));

        // Other.
        $this->other = $this->create_test_user((object)array(
            'username'      => 'other',
            'email'         => 'other@localhost',
            'firstname'     => 'other',
            'lastname'      => 'other',
        ));

        // Friend12.
        $this->friend12 = $this->create_test_user((object)array(
            'username'      => 'friend12',
            'email'         => 'friend12@localhost',
            'firstname'     => 'friend12',
            'lastname'      => 'friend12',
        ));

        // Friend21.
        $this->friend21 = $this->create_test_user((object)array(
            'username'      => 'friend21',
            'email'         => 'friend21@localhost',
            'firstname'     => 'friend21',
            'lastname'      => 'friend21',
        ));

        // Viewer.
        $this->viewer = $this->create_test_user((object)array(
            'username'      => 'viewer',
            'email'         => 'viewer@localhost',
            'firstname'     => 'viewer',
            'lastname'      => 'viewer',
        ), 'testinstitution');

        // The viewer is a member of the group.
        $this->group = $this->create_test_group(array(
            'name' => 'test',
            'grouptype' => 'standard',
            'members' => array($this->viewer => 'member')
        ));

        // Clean the activity table before we begin. create_test_user and other tests may have
        // performed actions that triggered activities and may not have cleaned them up.
        execute_sql("DELETE FROM {activity}");

        // Create two friend records so that they can be tested individually.
        execute_sql("INSERT INTO {usr_friend} (usr1, usr2, ctime) VALUES ({$this->viewer}, {$this->friend21}, NOW())");
        execute_sql("INSERT INTO {usr_friend} (usr2, usr1, ctime) VALUES ({$this->viewer}, {$this->friend12}, NOW())");

        execute_sql("INSERT INTO {artefact} (id, artefacttype, owner, title, ctime, mtime, atime, authorname)
                VALUES (" . self::ARTEFACT_ID_OTHER . ", 'html', {$this->other}, '', NOW(), NOW(), NOW(), '')");

        execute_sql("INSERT INTO {view} (id, type, owner, title, ctime, mtime, atime, numcolumns)
                VALUES (" . self::VIEW_ID_OTHER . ", 'portfolio', {$this->other}, '', NOW(), NOW(), NOW(), 0)");

        // Activities I have performed.
        execute_sql("INSERT INTO {activity} (id, usr, activitytype, activitysubtype, objecttype, objectid, ctime)
                VALUES (" . self::ACTIVITY_ID_MINE . ", {$this->viewer}, 0, 0, 0, 0, NOW())");

        // System activities performed by my friends.
        execute_sql("INSERT INTO {activity} (id, usr, activitytype, activitysubtype, objecttype, objectid, ctime)
                VALUES (" . self::ACTIVITY_ID_FRIEND12_SYSTEM . ", {$this->friend12}, 0, 0, " .
                        ActivityType::OBJECTTYPE_SYSTEM . ", 0, NOW())");
        execute_sql("INSERT INTO {activity} (id, usr, activitytype, activitysubtype, objecttype, objectid, ctime)
                VALUES (" . self::ACTIVITY_ID_FRIEND21_SYSTEM . ", {$this->friend21}, 0, 0, " .
                        ActivityType::OBJECTTYPE_SYSTEM . ", 0, NOW())");

        // Activities on artefacts that I own.
        execute_sql("INSERT INTO {artefact} (id, artefacttype, owner, title, ctime, mtime, atime, authorname)
                VALUES (" . self::ARTEFACT_ID_MINE . ", 'html', {$this->viewer}, '', NOW(), NOW(), NOW(), '')");
        execute_sql("INSERT INTO {activity} (id, usr, activitytype, activitysubtype, objecttype, objectid, ctime)
                VALUES (" . self::ACTIVITY_ID_MY_ARTEFACT . ", {$this->other}, 0, 0, " .
                        ActivityType::OBJECTTYPE_ARTEFACT . ", " . self::ARTEFACT_ID_MINE . ", NOW())");

        // Activities on views that I own.
        execute_sql("INSERT INTO {view} (id, type, owner, title, ctime, mtime, atime, numcolumns)
                VALUES (" . self::VIEW_ID_MINE . ", 'portfolio', {$this->viewer}, '', NOW(), NOW(), NOW(), 0)");
        execute_sql("INSERT INTO {activity} (id, usr, activitytype, activitysubtype, objecttype, objectid, ctime)
                VALUES (" . self::ACTIVITY_ID_MY_VIEW . ", {$this->other}, 0, 0, " .
                        ActivityType::OBJECTTYPE_VIEW . ", " . self::VIEW_ID_MINE . ", NOW())");

        // Activities that belong to my institutions.
        execute_sql("INSERT INTO {activity} (id, usr, activitytype, activitysubtype, objecttype, objectid, ctime)
               VALUES (" . self::ACTIVITY_ID_INSTITUTION . ", {$this->other}, 0, 0, " .
                        ActivityType::OBJECTTYPE_INSTITUTION . ", {$this->institution}, NOW())");

        // Activities on artefacts that belong to my institutions.
        execute_sql("INSERT INTO {artefact} (id, artefacttype, institution, title, ctime, mtime, atime, authorname)
                VALUES (" . self::ARTEFACT_ID_INSTITUTION . ", 'html', 'testinstitution', '', NOW(), NOW(), NOW(), '')");
        execute_sql("INSERT INTO {activity} (id, usr, activitytype, activitysubtype, objecttype, objectid, ctime)
                VALUES (" . self::ACTIVITY_ID_INSTITUTION_ARTEFACT . ", {$this->other}, 0, 0, " .
                        ActivityType::OBJECTTYPE_ARTEFACT . ", " . self::ARTEFACT_ID_INSTITUTION . ", NOW())");

        // Activities on views that belong to my institutions.
        execute_sql("INSERT INTO {view} (id, type, institution, title, ctime, mtime, atime, numcolumns)
                VALUES (" . self::VIEW_ID_INSTITUTION . ", 'portfolio', 'testinstitution', '', NOW(), NOW(), NOW(), 0)");
        execute_sql("INSERT INTO {activity} (id, usr, activitytype, activitysubtype, objecttype, objectid, ctime)
                VALUES (" . self::ACTIVITY_ID_INSTITUTION_VIEW . ", {$this->other}, 0, 0, " .
                        ActivityType::OBJECTTYPE_VIEW . ", " . self::VIEW_ID_INSTITUTION . ", NOW())");

        // Activities that belong to my groups.
        execute_sql("INSERT INTO {activity} (id, usr, activitytype, activitysubtype, objecttype, objectid, ctime)
                VALUES (" . self::ACTIVITY_ID_GROUP . ", {$this->other}, 0, 0, " .
                        ActivityType::OBJECTTYPE_GROUP . ", {$this->group}, NOW())");

        // Activities on artefacts that belong to my groups.
        execute_sql("INSERT INTO {artefact} (id, artefacttype, \"group\", title, ctime, mtime, atime, authorname)
                VALUES (" . self::ARTEFACT_ID_GROUP . ", 'html', {$this->group}, '', NOW(), NOW(), NOW(), '')");
        execute_sql("INSERT INTO {activity} (id, usr, activitytype, activitysubtype, objecttype, objectid, ctime)
                VALUES (" . self::ACTIVITY_ID_GROUP_ARTEFACT . ", {$this->other}, 0, 0, " .
                        ActivityType::OBJECTTYPE_ARTEFACT . ", " . self::ARTEFACT_ID_GROUP . ", NOW())");

        // Activities on views that belong to my groups.
        execute_sql("INSERT INTO {view} (id, type, \"group\", title, ctime, mtime, atime, numcolumns)
                VALUES (" . self::VIEW_ID_GROUP . ", 'portfolio', {$this->group}, '', NOW(), NOW(), NOW(), 0)");
        execute_sql("INSERT INTO {activity} (id, usr, activitytype, activitysubtype, objecttype, objectid, ctime)
                VALUES (" . self::ACTIVITY_ID_GROUP_VIEW . ", {$this->other}, 0, 0, " .
                        ActivityType::OBJECTTYPE_VIEW . ", " . self::VIEW_ID_GROUP . ", NOW())");

        // Activities on interactions that belong to my groups.
        execute_sql("INSERT INTO {interaction_instance} (id, plugin, \"group\", title, ctime, creator, deleted)
                VALUES (" . self::INTERACTION_ID_INTERACTION . ", 'forum', {$this->group}, '', NOW(), {$this->other}, 0)");
        execute_sql("INSERT INTO {activity} (id, usr, activitytype, activitysubtype, objecttype, objectid, ctime)
                VALUES (" . self::ACTIVITY_ID_GROUP_INTERACTION . ", {$this->other}, 0, 0, " .
                        ActivityType::OBJECTTYPE_INTERACTION . ", " . self::INTERACTION_ID_INTERACTION . ", NOW())");

        // Activities on forum topics that belong to my groups.
        execute_sql("INSERT INTO {interaction_instance} (id, plugin, \"group\", title, ctime, creator, deleted)
                VALUES (" . self::INTERACTION_ID_FORUMTOPIC . ", 'forum', {$this->group}, '', NOW(), {$this->other}, 0)");
        execute_sql("INSERT INTO {interaction_forum_topic} (id, forum)
                VALUES (" . self::INTERACTION_ID_FORUMTOPIC . ", " . self::INTERACTION_ID_FORUMTOPIC . ")");
        execute_sql("INSERT INTO {activity} (id, usr, activitytype, activitysubtype, objecttype, objectid, ctime)
                VALUES (" . self::ACTIVITY_ID_GROUP_FORUMTOPIC . ", {$this->other}, 0, 0, " .
                        ActivityType::OBJECTTYPE_FORUMTOPIC . ", " . self::INTERACTION_ID_FORUMTOPIC . ", NOW())");

        // Activities on views that I am watching (users can only watch items that they have permission to access).
        execute_sql("INSERT INTO {view} (id, type, owner, title, ctime, mtime, atime, numcolumns)
                VALUES (" . self::VIEW_ID_WATCHED . ", 'portfolio', {$this->other}, '', NOW(), NOW(), NOW(), 0)");
        execute_sql("INSERT INTO {usr_watchlist_view} (usr, view, ctime)
                VALUES ({$this->viewer}, " . self::VIEW_ID_WATCHED . ", NOW())");
        execute_sql("INSERT INTO {activity} (id, usr, activitytype, activitysubtype, objecttype, objectid, ctime)
                VALUES (" . self::ACTIVITY_ID_WATCHED_VIEW . ", {$this->other}, 0, 0, " .
                        ActivityType::OBJECTTYPE_VIEW . ", " . self::VIEW_ID_WATCHED . ", NOW())");

        // Activities on artefacts that are owned by my connections and that I can see.
        execute_sql("INSERT INTO {artefact} (id, artefacttype, owner, title, ctime, mtime, atime, authorname)
                VALUES (" . self::ARTEFACT_ID_FRIEND12 . ", 'html', {$this->friend12}, '', NOW(), NOW(), NOW(), '')");
        execute_sql("INSERT INTO {activity} (id, usr, activitytype, activitysubtype, objecttype, objectid, ctime)
                VALUES (" . self::ACTIVITY_ID_FRIEND12_ARTEFACT . ", {$this->other}, 0, 0, " .
                        ActivityType::OBJECTTYPE_ARTEFACT . ", " . self::ARTEFACT_ID_FRIEND12 . ", NOW())");
        execute_sql("INSERT INTO {artefact} (id, artefacttype, owner, title, ctime, mtime, atime, authorname)
                VALUES (" . self::ARTEFACT_ID_FRIEND21 . ", 'html', {$this->friend21}, '', NOW(), NOW(), NOW(), '')");
        execute_sql("INSERT INTO {activity} (id, usr, activitytype, activitysubtype, objecttype, objectid, ctime)
                VALUES (" . self::ACTIVITY_ID_FRIEND21_ARTEFACT . ", {$this->other}, 0, 0, " .
                        ActivityType::OBJECTTYPE_ARTEFACT . ", " . self::ARTEFACT_ID_FRIEND21 . ", NOW())");

        // Activities on views that are owned by my connections and that I can see.
        execute_sql("INSERT INTO {view} (id, type, owner, title, ctime, mtime, atime, numcolumns)
                VALUES (" . self::VIEW_ID_FRIEND12 . ", 'portfolio', {$this->friend12}, '', NOW(), NOW(), NOW(), 0)");
        execute_sql("INSERT INTO {activity} (id, usr, activitytype, activitysubtype, objecttype, objectid, ctime)
                VALUES (" . self::ACTIVITY_ID_FRIEND12_VIEW . ", {$this->other}, 0, 0, " .
                        ActivityType::OBJECTTYPE_VIEW . ", " . self::VIEW_ID_FRIEND12 . ", NOW())");
        execute_sql("INSERT INTO {view} (id, type, owner, title, ctime, mtime, atime, numcolumns)
                VALUES (" . self::VIEW_ID_FRIEND21 . ", 'portfolio', {$this->friend21}, '', NOW(), NOW(), NOW(), 0)");
        execute_sql("INSERT INTO {activity} (id, usr, activitytype, activitysubtype, objecttype, objectid, ctime)
                VALUES (" . self::ACTIVITY_ID_FRIEND21_VIEW . ", {$this->other}, 0, 0, " .
                        ActivityType::OBJECTTYPE_VIEW . ", " . self::VIEW_ID_FRIEND21 . ", NOW())");

        // Mock the get access conditions static functions so that they return a 'visible' condition.
        $class = $this->getMockClass(
            'PluginBlocktypeActivitystream',
            array('get_view_access_conditions', 'get_artefact_access_conditions')
        );
        $class::staticExpects($this->any())
                ->method('get_view_access_conditions')
                ->will($this->returnValue(array('visible' => array('sql' => '', 'params' => array()))));
        $class::staticExpects($this->any())
                ->method('get_artefact_access_conditions')
                ->will($this->returnValue(array('visible' => array('sql' => '', 'params' => array()))));

        // Get the things that are going to be tested.
        $this->conditions = $class::get_homestream_subqueries($this->viewer);
        $this->individualstreamuser = false;
    }

    // Check that the individual stream user returned is the owner of the stream.
    public function testIndividualStreamUser() {
        $this->assertEmpty($this->individualstreamuser);
    }

    // See if any conditions returned from the stream conditions function are untested.
    public function testUntestedStreamSubqueries() {
        foreach ($this->conditions as $conditionkey => $condition) {
            $found = "subquery untested";
            foreach ($this->testcases as $testcase) {
                if ($testcase[0] == $conditionkey) {
                    $found = "subquery tested";
                }
            }
            $this->assertEquals("subquery tested", $found, "conditionkey: " . $conditionkey . "\n");
        }
    }

    /**
     * Test each subquery returned by the stream subquery function.
     */
    public function testGetStreamSubqueries() {
        foreach ($this->testcases as $testcase) {
            $subquery = $testcase[0];
            $expectedactivityid = $testcase[1];

            $condition = $this->conditions[$subquery];
            $sql = $condition['sql'];
            $params = $condition['params'];
            $result = get_records_sql_array($sql, $params);

            // Should be exactly one result.
            $this->assertEquals(1, count($result),
                    "condition: " . $subquery ."\nsql: " . $sql . "\nparams: " . implode(", ", $params));

            if (count($result) == 1) {
                $firstresult = reset($result);
                $this->assertEquals($expectedactivityid, $firstresult->id,
                        "condition: " . $subquery ."\nsql: " . $sql . "\nparams: " . implode(", ", $params));
            }
        }
    }

    public function tearDown() {
        foreach ($this->activityids as $id) {
            execute_sql("DELETE FROM {activity} WHERE id = ?", array($id));
        }
        foreach ($this->artefactids as $id) {
            execute_sql("DELETE FROM {artefact} WHERE id = ?", array($id));
        }
        foreach ($this->viewids as $id) {
            execute_sql("DELETE FROM {usr_watchlist_view} WHERE view = ?", array($id));
            execute_sql("DELETE FROM {view} WHERE id = ?", array($id));
        }
        foreach ($this->interactionids as $id) {
            execute_sql("DELETE FROM {interaction_forum_topic} WHERE forum = ?", array($id));
            execute_sql("DELETE FROM {interaction_instance} WHERE id = ?", array($id));
        }
        parent::tearDown();
    }
}
