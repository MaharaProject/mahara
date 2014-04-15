<?php
/**
 *
 * @package    mahara
 * @subpackage tests
 * @author     Andrew Nicols
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) 2009 Penny Leach
 *
 */

require_once(get_config('libroot') . 'view.php');

/**
 * Test the view class
 *
 * TODO this just sets basic creating, setting a fiew fields and deleting
 * need to test access and blocks and all sorts of other things.
 */
class ViewTest extends MaharaUnitTest {

    /** The id of the user created in setUp. */
    private $testuserid;
    /** the view created in setUp */
    private $view;
    /** the id of our shared view. kept for persistency after we delete the view */
    private $viewid;
    /** array of values to set in the view object. TODO: add more */
    private $fields = array(
        'title'       => array(
            'Test View',
            'Test View new title'
        ),
        'description' => array(
            'A view to test with!',
            'a new description to test with'
        ),
        // ....
    );

    /**
     * shared setUp method
     * require a test user, and
     * create a view to test with
     */
    public function setUp() {
        parent::setUp();
        $this->testuserid = $this->create_test_user();
        // set the owner of the view to the test user we created
        $this->fields['owner'] = array($this->testuserid, $this->testuserid);
        $this->view = View::create(array(
            'title'       => $this->fields['title'][0],
            'description' => $this->fields['description'][0],
        ), $this->testuserid);
        $this->viewid = $this->view->get('id');
    }

    /**
     * make sure what got created makes sense
     */
    public function testViewCreating() {
        $this->assertInternalType('int', (int) $this->viewid);
        $this->assertGreaterThan(0, $this->viewid);

        // now get it again and make sure it matches
        try {
            $createdview = new View($this->view->get('id'));
        }
        catch (Exception $e) {
            $this->fail("Couldn't find new view I created");
        }
        foreach ($this->fields as $field => $values) {
            // make sure both the values in the db and in the return object match what we said
            $this->assertEquals($values[0], $createdview->get($field));
            $this->assertEquals($values[0], $this->view->get($field));
        }
    }

    /**
     * test that the view setters work (without committing)
     */
    public function testViewSetters() {
        foreach ($this->fields as $field => $values) {
            $this->view->set($field, $values[1]);
            $this->assertEquals($values[1], $this->view->get($field));
        }
    }

    /**
     * test that the setters work and commit to the db
     * and when we get the view back it matches
     */
    public function testViewCommitting() {
        // now commit to db and test again
        foreach ($this->fields as $field => $values) {
            $this->view->set($field, $values[1]);
        }
        $this->view->commit();

        $createdview = new View($this->view->get('id'));
        foreach ($this->fields as $field => $values) {
            $this->assertEquals($values[1], $createdview->get($field));
        }
    }

    /**
     * Test that removing a column updates numcolumns
     */
    public function testRemovecolumn() {
        $before = $this->view->get_row_datastructure();

        $this->view->removecolumn(array('column' => 1, 'row' => 1));

        $after = $this->view->get_row_datastructure();

        $this->assertEquals(count($before), count($after));
        $this->assertEquals(count($before[1]) - 1, count($after[1]));
    }

    /**
     * test that when we delete a view,
     * it actually gets deleted from the database
     */
    public function testViewDeleting() {
        $todelete = View::create(array(
            'title'       => $this->fields['title'][0],
            'description' => $this->fields['description'][0],
        ), $this->testuserid);
        $todeleteid = $todelete->get('id');
        $todelete->delete();
        $todelete->commit();
        try {
            $deleted = new View($todeleteid);
            $this->fail("View wasn't deleted properly!");
        }
        catch (Exception $e) {}
    }

    /**
     * clean up after ourselves,
     * just delete the test view we made
     * and call the parent method
     */
    public function tearDown() {
        $this->view->delete();
        parent::tearDown();
    }
}
