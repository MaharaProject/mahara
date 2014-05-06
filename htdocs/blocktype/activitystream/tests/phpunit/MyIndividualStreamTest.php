<?php
/**
 * @package    mahara
 * @subpackage tests
 * @author     Nathan Lewis <nathan.lewis@totaralms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */

require_once(get_config('docroot') . '/blocktype/activitystream/lib.php');

class MyIndividualStreamTest extends MaharaUnitTest {

    private $other;
    private $viewer;

    private $conditions;
    private $individualstreamuser;

    const ACTIVITY_ID_MINE = 1001;
    const ACTIVITY_ID_OTHER = 1002;

    private $ids = array(
            self::ACTIVITY_ID_MINE,
            self::ACTIVITY_ID_OTHER
        );

    private $testcases = array(
            array('mine', self::ACTIVITY_ID_MINE)
        );

    /**
     * Shared setUp method.
     */
    public function setUp() {
        parent::setUp();

        // Other user.
        $this->other = $this->create_test_user((object)array(
            'username'      => 'other',
            'email'         => 'other',
            'firstname'     => 'other',
            'lastname'      => 'other',
        ));

        // Viewer.
        $this->viewer = $this->create_test_user((object)array(
            'username'      => 'viewer',
            'email'         => 'viewer@localhost',
            'firstname'     => 'viewer',
            'lastname'      => 'viewer',
        ));

        // My activity.
        execute_sql("INSERT INTO {activity} (id, usr, activitytype, activitysubtype, objecttype, objectid, ctime)
                VALUES (" . self::ACTIVITY_ID_MINE . ", {$this->viewer}, 0, 0, 0, 0, NOW())");

        // Not my activity.
        execute_sql("INSERT INTO {activity} (id, usr, activitytype, activitysubtype, objecttype, objectid, ctime)
                VALUES (" . self::ACTIVITY_ID_OTHER . ", {$this->other}, 0, 0, 0, 0, NOW())");

        // Get the things that are going to be tested.
        $this->conditions = PluginBlocktypeActivitystream::get_myindividualstream_subqueries($this->viewer);
        $this->individualstreamuser = $this->viewer;
    }

    // Check that the individual stream user returned is the owner of the stream.
    public function testIndividualStreamUser() {
        $this->assertEquals($this->viewer, $this->individualstreamuser);
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
            $this->assertEquals("subquery tested", $found, "conditionkey: " . $conditionkey);
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
        foreach ($this->ids as $id) {
            execute_sql("DELETE FROM {activity} WHERE id = ?", array($id));
        }
        parent::tearDown();
    }
}
