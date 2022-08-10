<?php
/**
 * @package    mahara
 * @subpackage test/generator
 * @author     Son Nguyen
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  portions from Moodle 2013, The Open University
 *
 */

/**
 * Generator base class.
 *
 * A plugin can implement a class which extend this to generate its data for testing
 * For example, the plugin artefact.blog can have the file
 * artefact/blog/tests/generator/lib.php which implement the
 * class DataGeneratorArtefactBlog extends DataGeneratorBase
 *
 */
abstract class DataGeneratorBase {

    /** @var number of created instances */
    protected $instancecount = 0;

    /**
     * @var TestingDataGenerator
     */
    protected $datagenerator;

    /**
     * Constructor.
     * @param TestingDataGenerator $datagenerator
     */
    public function __construct(TestingDataGenerator $datagenerator) {
        $this->datagenerator = $datagenerator;
    }

    /**
     * To be called from data reset code only,
     * do not use in tests.
     * @return void
     */
    public function reset() {
        $this->instancecount = 0;
    }

    /**
     * Create a test data record
     * @param array|stdClass $record
     * @param array $options
     * @return stdClass the created record
     */
    abstract public function create_instance($record = null, array $options = null);
}
