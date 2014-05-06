<?php
/**
 * @package    mahara
 * @subpackage tests
 * @author     Nathan Lewis <nathan.lewis@totaralms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */

require_once(get_config('docroot') . '/blocktype/activitystream/lib.php');

class HomeStreamAdditionalConditionsTest extends MaharaUnitTest {

    private $homestream;
    private $nothomestream;

    const ACTIVITY_ID_NEW_ARTEFACT_WATCHED_HOMESTREAM = 1001;
    const ACTIVITY_ID_NEW_ARTEFACT_WATCHED_NOTHOMESTREAM = 1002;
    const ACTIVITY_ID_NEW_ARTEFACT_UNWATCHED_HOMESTREAM = 1003;
    const ACTIVITY_ID_NEW_ARTEFACT_UNWATCHED_NOTHOMESTREAM = 1004;
    const ACTIVITY_ID_NEW_VIEW_WATCHED_HOMESTREAM = 1005;
    const ACTIVITY_ID_NEW_VIEW_WATCHED_NOTHOMESTREAM = 1006;
    const ACTIVITY_ID_NEW_VIEW_UNWATCHED_HOMESTREAM = 1007;
    const ACTIVITY_ID_NEW_VIEW_UNWATCHED_NOTHOMESTREAM = 1008;
    const ACTIVITY_ID_CHANGED_ARTEFACT_WATCHED_HOMESTREAM = 1009;
    const ACTIVITY_ID_CHANGED_ARTEFACT_WATCHED_NOTHOMESTREAM = 1010;
    const ACTIVITY_ID_CHANGED_ARTEFACT_UNWATCHED_HOMESTREAM = 1011;
    const ACTIVITY_ID_CHANGED_ARTEFACT_UNWATCHED_NOTHOMESTREAM = 1012;
    const ACTIVITY_ID_CHANGED_VIEW_WATCHED_HOMESTREAM = 1013;
    const ACTIVITY_ID_CHANGED_VIEW_WATCHED_NOTHOMESTREAM = 1014;
    const ACTIVITY_ID_CHANGED_VIEW_UNWATCHED_HOMESTREAM = 1015;
    const ACTIVITY_ID_CHANGED_VIEW_UNWATCHED_NOTHOMESTREAM = 1016;

    // Expect none that are NOTHOMESTREAM, none that are CHANGED UNWATCHED.
    private $expectedids = array();
    private $activityids = array();
    private $artefactids = array();
    private $viewids = array();
    private $useractivityids = array('homestream' => array(), 'nothomestream' => array());

    private $testnewaccess;
    private $testchange;

    /**
     * Shared setUp method.
     */
    public function setUp() {
        parent::setUp();

        // Homestreamuser.
        $this->homestream = $this->create_test_user((object)array(
            'username'      => 'homestream',
            'email'         => 'homestream@localhost',
            'firstname'     => 'homestream',
            'lastname'      => 'homestream',
        ));

        // Nothomestreamuser.
        $this->nothomestream = $this->create_test_user((object)array(
            'username'      => 'nothomestream',
            'email'         => 'nothomestream@localhost',
            'firstname'     => 'nothomestream',
            'lastname'      => 'nothomestream',
        ));

        $this->testnewaccess = insert_record('activity_type',
                (object)array('name' => 'testnewaccess', 'defaultmethod' => 'homestream'));
        $this->testchange = insert_record('activity_type',
                (object)array('name' => 'testchange', 'defaultmethod' => 'homestream', 'onlyapplyifwatched' => 0));

        $users = array('homestream', 'nothomestream');
        $activitytypes = array($this->testnewaccess, $this->testchange);
        $objecttypes = array('artefact', 'view');
        $watchstatuses = array(true, false);

        $id = 1000;
        foreach ($activitytypes as $activitytype) {
            foreach ($users as $user) {
                foreach ($objecttypes as $objecttype) {
                    foreach ($watchstatuses as $watched) {
                        $id++;
                        $this->activityids[] = $id;
                        if ($objecttype == 'view') {
                            execute_sql("INSERT INTO {view} (id, type, owner, title, ctime, mtime, atime, numcolumns)
                                    VALUES ({$id} + 1000, 'portfolio', {$this->$user}, '', NOW(), NOW(), NOW(), 0)");
                            $this->viewids[] = $id + 1000;
                            if ($watched) {
                                execute_sql("INSERT INTO {usr_watchlist_view} (usr, view, ctime)
                                        VALUES ({$this->$user}, {$id} + 1000, NOW())");
                            }
                            execute_sql("INSERT INTO {activity} (id, usr, activitytype, activitysubtype, objecttype,
                                    objectid, ctime) VALUES ({$id}, {$this->$user}, {$activitytype}, 0, " .
                                    ActivityType::OBJECTTYPE_VIEW . ", {$id} + 1000, NOW())");
                        }
                        else {
                            execute_sql("INSERT INTO {artefact} (id, artefacttype, owner, title, ctime, mtime, atime, authorname)
                                    VALUES ({$id} + 2000, 'html', {$this->$user}, '', NOW(), NOW(), NOW(), '')");
                            $this->artefactids[] = $id + 2000;
                            execute_sql("INSERT INTO {activity} (id, usr, activitytype, activitysubtype, objecttype,
                                    objectid, ctime) VALUES ({$id}, {$this->$user}, {$activitytype}, 0, " .
                                    ActivityType::OBJECTTYPE_ARTEFACT . ", {$id} + 2000, NOW())");
                        }
                        if ($user == 'homestream' && ($activitytype == $this->testnewaccess || $watched)) {
                            $this->expectedids[$id] = 1;
                        }
                        $this->useractivityids[$user][] = $id;
                    }
                }
            }
        }

        execute_sql("UPDATE {usr_activity_preference} SET method = 'homestream' WHERE usr = ?", array($this->homestream));
    }

    // Check.
    public function testHomeStreamAdditionalConditions() {
        $homestreamactivityids = implode(", ", $this->useractivityids['homestream']);
        $homestreamsql = "SELECT activity.id FROM {activity} activity WHERE activity.id IN ({$homestreamactivityids})";
        list($homestreamadditionalsql, $homestreamparams) =
                PluginBlocktypeActivitystream::get_homestream_additional_conditions($homestreamsql, array(), $this->homestream);

        $homestreamresults = get_column_sql($homestreamadditionalsql, $homestreamparams);
        $this->assertEquals(count($this->expectedids), count($homestreamresults));

        foreach ($this->useractivityids['homestream'] as $activityid) {
            if (!empty($this->expectedids[$activityid])) {
                $this->assertContains($activityid, $homestreamresults);
            }
            else {
                $this->assertNotContains($activityid, $homestreamresults);
            }
        }

        $nothomestreamactivityids = implode(", ", $this->useractivityids['nothomestream']);
        $nothomestreamsql = "SELECT activity.id FROM {activity} activity WHERE activity.id IN ({$nothomestreamactivityids})";
        list($nothomestreamadditionalsql, $nothomestreamparams) =
                PluginBlocktypeActivitystream::get_homestream_additional_conditions($nothomestreamsql, array(), $this->nothomestream);

        $nothomestreamresults = get_column_sql($nothomestreamadditionalsql, $nothomestreamparams);
        $this->assertEquals(0, count($nothomestreamresults));
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
        parent::tearDown();
    }
}
