<?php
/**
 *
 * @package    mahara
 * @subpackage tests
 * @author     Aaron Wells, Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * Test functions in lib/mahara.php
 */
class LibmaharaTest extends MaharaUnitTest {

    /**
     * The original value of wwwroot
     */
    private $realwwwroot;

    /**
     * shared setUp method.
     */
    public function setUp() {
        // To test get_mahara_install_subdirectory() we'll need to change $CFG->wwwroot.
        // Record its original value so we can change it back when we're done.
        $this->realwwwroot = get_config('wwwroot');
        parent::setUp();
    }

    /**
     * Sample data for the test of get_mahara_install_subdirectory.
     * First column is the input, second column is the expected output.
     *
     * @return array
     */
    public function wwwrootProvider() {
        return array(
            array('https://www.example.com', '/'),
            array('https://www.example.com/', '/'),
            array('https://www.example.com/mahara', '/mahara/'),
            array('https://www.example.com/mahara/', '/mahara/'),
            array(null, '/'),
        );
    }

    /**
     * Test the get_mahara_install_subdirectory() method
     * @dataProvider wwwrootProvider
     *
     * @param string $wwwroot An input value of $CFG->wwwroot
     * @param string $expectedpath The expected return value of get_mahara_install_subdirectory() for the provided wwwroot
     */
    public function testGetMaharaInstallSubdirectory($wwwroot, $expectedpath) {
        set_config('wwwroot', $wwwroot);
        $this->assertEquals($expectedpath, get_mahara_install_subdirectory());
    }

    public function tearDown() {
        set_config('wwwroot', $this->realwwwroot);
        parent::tearDown();
    }
}
