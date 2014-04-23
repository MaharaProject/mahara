<?php
/**
 *
 * @package    Mahara
 * @subpackage tests
 * @author     Yuliya Bozhko <yuliya.bozhko@totaralms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

require_once(get_config('docroot') . 'artefact/lib.php');

/**
 * Test the ArtefactType class
 *
 */
class ArtefactTest extends MaharaUnitTest {
    /** The id of the user created in setUp. */
    private $testuserid;

    /**
     * Shared setUp method.
     * Requires a test user, and create artefacts to test with.
     */
    public function setUp() {
        parent::setUp();
        $this->testuserid = $this->create_test_user();
    }

    /**
     * Clean up after ourselves.
     */
    public function tearDown() {
        parent::tearDown();
    }

    /**
     * Test that an artefact gets created and stored.
     */
    public function testArtefactCreate() {
        $artefact = new ArtefactTypePlan();
        $data = array('owner' => $this->testuserid,
                      'title' => 'Test artefact',
                      'description' => 'Test artefact description');
        $artefact->set('owner', $data['owner']);
        $artefact->set('title', $data['title']);
        $artefact->set('description', $data['description']);

        try {
            $artefact->commit();
            $artefactid = $artefact->get('id');
        }
        catch (Exception $e) {
            $this->fail("Couldn't find new artefact I created");
        }
        // Check record from DB.
        $fromdb = new ArtefactTypePlan($artefactid);
        foreach ($data as $field => $value) {
            $this->assertEquals($value, $fromdb->get($field));
        }
    }

    /**
     * Test that an artefact gets deleted.
     */
    public function testArtefactDelete() {
        $todelete = new ArtefactTypePlan();
        $data = array('owner' => $this->testuserid,
                      'title' => 'Test artefact',
                      'description' => 'Test artefact description');

        $todelete->set('owner', $data['owner']);
        $todelete->set('title', $data['title']);
        $todelete->set('description', $data['description']);
        $todelete->commit();
        $todeleteid = $todelete->get('id');
        $todelete->delete();
        try {
            $deleted = new ArtefactTypePlan($todeleteid);
            $this->fail("Artefact wasn't deleted properly!");
        }
        catch (Exception $e) {}
    }

    /**
     * Test that an artefact gets a new path when moved.
     */
    public function testArtefactHierarchyMove() {
        // Create folder.
        $folderdata = array('owner' => $this->testuserid,
                      'title' => 'Test folder',
                      'description' => 'Test folder description');
        $folder = new ArtefactTypeFolder(0, $folderdata);
        $folder->commit();

        // Create a file.
        $filedata = array('owner' => $this->testuserid,
                      'title' => 'Test file',
                      'description' => 'Test file description');
        $file = new ArtefactTypeFile(0, $filedata);
        $file->commit();

        // Check that path is root.
        $fileid = $file->get('id');
        $this->assertEquals('/'. $fileid, $file->get('path'));

        // "Move" file to a folder.
        $folderid = $folder->get('id');
        $file = new ArtefactTypeFile($fileid);
        $file->move($folderid);
        $newpath = "/$folderid/$fileid";
        $this->assertEquals($newpath, $file->get('path'));
    }

    // TODO: MORE TESTS!!!!
}
