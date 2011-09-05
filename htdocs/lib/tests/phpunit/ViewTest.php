<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2009 Penny Leach
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage tests
 * @author     Andrew Nicols
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

require_once(get_config('libroot') . 'view.php');

/**
 * Test the view class
 *
 * TODO this just sets basic creating, setting a fiew fields and deleting
 * need to test access and blocks and all sorts of other things.
 */
class ViewTest extends MaharaUnitTest {

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
        $this->require_test_user();
        // set the owner of the view to the test user we created
        $this->fields['owner'] = array($this->users['test'], $this->users['test']);
        $this->view = View::create(array(
            'title'       => $this->fields['title'][0],
            'description' => $this->fields['description'][0],
        ), $this->users['test']);
        $this->viewid = $this->view->get('id');
    }

    /**
     * make sure what got created makes sense
     */
    public function testViewCreating() {
        $this->assertType('int', (int) $this->viewid);
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
     * test that when we delete a view,
     * it actually gets deleted from the database
     */
    public function testViewDeleting() {
        $todelete = View::create(array(
            'title'       => $this->fields['title'][0],
            'description' => $this->fields['description'][0],
        ), $this->users['test']);
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
        $this->view->commit();
        parent::tearDown();
    }
}
