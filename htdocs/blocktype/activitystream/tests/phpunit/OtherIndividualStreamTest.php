<?php
/**
 * @package    mahara
 * @subpackage tests
 * @author     Nathan Lewis <nathan.lewis@totaralms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */

require_once(get_config('docroot') . '/blocktype/activitystream/lib.php');

class OtherIndividualStreamTest extends MaharaUnitTest {

    private $other;
    private $owner;
    private $viewer;
    private $group;
    private $institution;
    private $interaction;
    private $forumtopic;

    private $conditions;
    private $individualstreamuser;

    const ACTIVITY_ID_GROUP = 1001;
    const ACTIVITY_ID_INSTITUTION = 1002;
    const ACTIVITY_ID_ARTEFACT = 1003;
    const ACTIVITY_ID_VIEW = 1004;
    const ACTIVITY_ID_SYSTEM = 1005;
    const ACTIVITY_ID_INTERACTION = 1006;
    const ACTIVITY_ID_FORUMTOPIC = 1007;

    const ARTEFACT_ID = 2001;

    const VIEW_ID = 3001;

    private $activityids = array(
            self::ACTIVITY_ID_GROUP,
            self::ACTIVITY_ID_INSTITUTION,
            self::ACTIVITY_ID_ARTEFACT,
            self::ACTIVITY_ID_VIEW,
            self::ACTIVITY_ID_SYSTEM,
            self::ACTIVITY_ID_INTERACTION,
            self::ACTIVITY_ID_FORUMTOPIC,
        );

    private $testcases = array(
            array('group', self::ACTIVITY_ID_GROUP),
            array('institution', self::ACTIVITY_ID_INSTITUTION),
            array('artefact_visible', self::ACTIVITY_ID_ARTEFACT),
            array('view_visible', self::ACTIVITY_ID_VIEW),
            array('system', self::ACTIVITY_ID_SYSTEM),
            array('interaction', self::ACTIVITY_ID_INTERACTION),
            array('forumtopic', self::ACTIVITY_ID_FORUMTOPIC),
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

        // Owner of the stream.
        $this->owner = $this->create_test_user((object)array(
            'username'      => 'owner',
            'email'         => 'owner@localhost',
            'firstname'     => 'owner',
            'lastname'      => 'owner',
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

        // The interaction (forum) belongs to the group.
        $data = new stdClass();
        $data->plugin = 'forum';
        $data->group = $this->group;
        $data->title = 'interaction_instance test';
        $data->ctime = db_format_timestamp(time());
        $data->creator = $this->owner;
        $this->interaction = insert_record('interaction_instance', $data, 'id', true);

        // The forum topic belongs to the interaction.
        $data = new stdClass();
        $data->forum = $this->interaction;
        $this->forumtopic = insert_record('interaction_forum_topic', $data, 'id', true);

        // Create a friend record.
        execute_sql("INSERT INTO {usr_friend} (usr1, usr2, ctime) VALUES ({$this->viewer}, {$this->owner}, NOW())");

        // All system activities that the target performed where the viewer is a friend.
        execute_sql("INSERT INTO {activity} (id, usr, activitytype, activitysubtype, objecttype, objectid, ctime)
                VALUES (" . self::ACTIVITY_ID_SYSTEM . ", {$this->owner}, 0, 0, " .
                        ActivityType::OBJECTTYPE_SYSTEM . ", 0, NOW())");

        // All group activities that the target performed where the viewer is a member.
        execute_sql("INSERT INTO {activity} (id, usr, activitytype, activitysubtype, objecttype, objectid, ctime)
                VALUES (" . self::ACTIVITY_ID_GROUP . ", {$this->owner}, 0, 0, " .
                        ActivityType::OBJECTTYPE_GROUP . ", {$this->group}, NOW())");

        // All institution activities that the target performed where the viewer is a member.
        execute_sql("INSERT INTO {activity} (id, usr, activitytype, activitysubtype, objecttype, objectid, ctime)
                VALUES (" . self::ACTIVITY_ID_INSTITUTION . ", {$this->owner}, 0, 0, " .
                        ActivityType::OBJECTTYPE_INSTITUTION . ", {$this->institution}, NOW())");

        // All interaction activities that the target performed where the viewer is a member of the group.
        execute_sql("INSERT INTO {activity} (id, usr, activitytype, activitysubtype, objecttype, objectid, ctime)
                VALUES (" . self::ACTIVITY_ID_INTERACTION . ", {$this->owner}, 0, 0, " .
                        ActivityType::OBJECTTYPE_INTERACTION . ", {$this->interaction}, NOW())");

        // All forum topic activities that the target performed where the viewer is a member of the group.
        execute_sql("INSERT INTO {activity} (id, usr, activitytype, activitysubtype, objecttype, objectid, ctime)
                VALUES (" . self::ACTIVITY_ID_FORUMTOPIC . ", {$this->owner}, 0, 0, " .
                        ActivityType::OBJECTTYPE_FORUMTOPIC . ", {$this->forumtopic}, NOW())");

        // Activities on artefacts that are performed by the target and that I can see.
        execute_sql("INSERT INTO {artefact} (id, artefacttype, owner, title, ctime, mtime, atime, authorname)
                VALUES (" . self::ARTEFACT_ID . ", 'html', {$this->other}, '', NOW(), NOW(), NOW(), '')");
        execute_sql("INSERT INTO {activity} (id, usr, activitytype, activitysubtype, objecttype, objectid, ctime)
                VALUES (" . self::ACTIVITY_ID_ARTEFACT . ", {$this->owner}, 0, 0, " .
                        ActivityType::OBJECTTYPE_ARTEFACT . ", " . self::ARTEFACT_ID . ", NOW())");

        // Activities on views that are performed by the target and that I can see.
        execute_sql("INSERT INTO {view} (id, type, owner, title, ctime, mtime, atime, numcolumns)
                VALUES (" . self::VIEW_ID . ", 'portfolio', {$this->other}, '', NOW(), NOW(), NOW(), 0)");
        execute_sql("INSERT INTO {activity} (id, usr, activitytype, activitysubtype, objecttype, objectid, ctime)
                VALUES (" . self::ACTIVITY_ID_VIEW . ", {$this->owner}, 0, 0, " .
                        ActivityType::OBJECTTYPE_VIEW . ", " . self::VIEW_ID . ", NOW())");

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
        $this->conditions = $class::get_otherindividualstream_subqueries($this->viewer, $this->owner);
        $this->individualstreamuser = $this->owner;
    }

    // Check that the individual stream user returned is the owner of the stream.
    public function testIndividualStreamUser() {
        $this->assertEquals($this->owner, $this->individualstreamuser);
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
        execute_sql("DELETE FROM {interaction_forum_topic} WHERE id = ?", array($this->forumtopic));
        execute_sql("DELETE FROM {interaction_instance} WHERE id = ?", array($this->interaction));
        execute_sql("DELETE FROM {artefact} WHERE id = ?", array(self::ARTEFACT_ID));
        execute_sql("DELETE FROM {view} WHERE id = ?", array(self::VIEW_ID));
        parent::tearDown();
    }
}
