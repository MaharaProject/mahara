<?php

/**
 *
 * @package    mahara
 * @subpackage lib
 * @author     Andrew Nicols
 * @author     Petr Skoda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  2009 Petr Skoda (http://skodak.org)
 */

/**
 * Command Line Interface Class for mahara
 *
 * Two methods of use are currently supported:
 * * basic; and
 * * extended.
 *
 * In basic use, the CLI can be used to retrieve parameters passed on the
 * command line. For example, when called as:
 *
 *      php htdocs/admin/cli/example.php --argument=value --argument2 -x -q=n pony
 *
 * the library could be used to determine the argument options as follows:
 *
 * <?php
 *
 * define('CLI', true);
 * define('INTERNAL', true);
 * include(dirname(dirname(__FILE__)) . '/init.php');
 *
 * $cli = get_cli();
 * $cli->set_cli_shortoptions(array('x' => 'argumentx', 'q' => 'question'));
 * $argument = $cli->get_cli_param('argument');
 * $argument = $cli->get_cli_param('argument2');
 * $argument = $cli->get_cli_param('argumentx');
 * $argument = $cli->get_cli_param('question');
 * $argument = $cli->get_cli_unmatched();
 *
 *
 * In the extended version, a greater degree of setup is required, but a
 * number of other benefits are available as a result, including:
 * * help and usage generation;
 * * built-in verbosity support; and
 * * built-in argument validation.
 *
 * The following sample code demonstrates how to use the extended version:
 * <?php
 *
 * define('CLI', true);
 * define('INTERNAL', true);
 * include(dirname(dirname(__FILE__)) . '/init.php');
 *
 * $cli = get_cli();
 *
 * $options = array();
 * $options['argument'] = new stdClass();
 * $options['argument']->exampleValue = 'value';
 * $options['argument']->description = 'This is an example description for argument';
 *
 * $options['argument2'] = new stdClass();
 * $options['argument2']->description = 'This is an example description for argument2 - it takes no value';
 *
 * $options['argumentx'] = new stdClass();
 * $options['argumentx']->description = 'This is an example description for argumentx - it takes no value and has an alias';
 * $options['argumentx']->shortoptions = array('x');
 *
 * $options['question'] = new stdClass();
 * $options['question']->exampleValue = 'value';
 * $options['question']->description = 'This is an example description for question - it typicaly takes an argument and has an alias of q';
 * $options['question']->shortoptions = array('q');
 *
 * $settings = new stdClass();
 * $settings->options = $options;
 * $settings->allowunmatched = true;
 * $settings->info = 'Some information about what this script does';
 *
 * $cli->setup($settings);
 */
class cli {

    /**
     * Store the short option mapping information
     */
    private $shortoptions = array();

    /**
     * Store default option values in a readily available format
     */
    private $defaultvalues = array();

    /**
     * Store the arguments given on the CLI
     */
    private $arguments = null;

    /**
     * Store any unmatched entries not recognised as valid arguments
     */
    private $unmatched = null;

    /**
     * By default, allow unmatched text in the data stream to allow for
     * simple use. This will be turned off by anyone calling setup()
     */
    private $allowunmatched = true;

    /**
     * Store the settings passed in
     */
    private $settings = null;

    /**
     * Set up the CLI interface correctly
     *
     * @param object settings The settings to work with
     * @return void
     */
    public function setup($settings) {
        // Handle various options
        $this->allowunmatched = (isset($settings->allowunmatched)) ? $settings->allowunmatched : false;

        // Add verbosity and help options
        $help = new stdClass();
        $help->shortoptions = array('h');
        $help->description = 'Display this help and usage information';
        $settings->options['help'] = $help;

        $verbose = new stdClass();
        $verbose->shortoptions = array('v');
        $verbose->description = 'Increase verbosity of the CLI script to show more information';
        $settings->options['verbose'] = $verbose;

        // Process longoption configuraiton
        foreach ($settings->options as $name => $optionsettings) {
            // Store the default value
            $this->defaultvalues[$name] = (isset($optionsettings->defaultvalue)) ? $optionsettings->defaultvalue : false;

            // By default this value isn't required
            $optionsettings->required = (isset($optionsettings->required)) ? $optionsettings->required : false;

            // Set the default description
            if (!isset($optionsettings->description)) {
                $optionsettings->description = '';
            }

            // Check all short options
            if (isset($optionsettings->shortoptions)) {
                foreach ($optionsettings->shortoptions AS $k => $shortoption) {
                    $this->shortoptions[$shortoption] = $name;
                }
            }
            else {
                $optionsettings->shortoptions = array();
            }
        }

        // Store all settings for any access required later
        $this->settings = $settings;

        // Validate the options given
        $this->validate_options();

        // Process default arguments
        $this->process_default_arguments();
    }

    /**
     * Process the default arguments supplied if this script was called by
     * using the extended method.
     *
     * If the verbose option is called, verbosity is increased to screen
     * for all targets - dbg, info, warn, and environ.
     * If the help option is called, then the help and usage information is
     * printed using {@see cli_print_help}.
     * @return void
     */
    private function process_default_arguments() {
        global $CFG;

        // Check for verbosity
        $verbose = $this->get_cli_param('verbose');
        if ($verbose) {
            $CFG->log_dbg_targets     = LOG_TARGET_SCREEN | LOG_TARGET_ERRORLOG;
            $CFG->log_info_targets    = LOG_TARGET_SCREEN | LOG_TARGET_ERRORLOG;
            $CFG->log_warn_targets    = LOG_TARGET_SCREEN | LOG_TARGET_ERRORLOG;
            $CFG->log_environ_targets = LOG_TARGET_SCREEN | LOG_TARGET_ERRORLOG;
        }

        // Check for usage/help request
        $help = $this->get_cli_param('help');
        if ($help) {
            $this->cli_print_help();
        }
    }

    /**
     * Set the Short Option to Long Option mapping for basic usage
     *
     * @param array $shortoptions An associative array mapping the short
     * option to the long option name
     * @return void
     */
    public function set_cli_shortoptions($shortoption) {
        $this->shortoptions = $shortoption;
    }

    /**
     * Compile the list of CLI arguments
     *
     * The following options are valid:
     *
     * --foo=bar
     * -foo=bar
     * --example-flag-without-content
     * -example-flag-without-content
     *
     * It is not possible to have any whitespace between the = and any values
     *
     * Any other values are ignored and no warnings are issued
     *
     * @return array of specified variables
     */
    public function _get_cli_params() {
        global $argv;

        if ($this->arguments && $this->unmatched) {
            return array($this->arguments, $this->unmatched);
        }

        // We want to manipulate the arguments. Doing so on $argv would be
        // pretty rude
        $options = $argv;

        // Remove the script name
        unset ($options[0]);

        $this->arguments = array();
        $this->unmatched = array();
        if (!empty($options)) {
            // Trim off anything after a -- with no arguments
            if (($key = array_search('--', $options)) !== false) {
                $options = array_slice($options, 0, $key);
            }

            foreach ($options as $argument) {
                // Attempt to match arguments
                preg_match('/^(-(-)?)([^=]*)(=(.*))?$/', $argument, $matches);
                if (count($matches) && !empty($matches[3])) {
                    $argname = $matches[3];
                    if ($matches[1] == '-' && isset($this->shortoptions[$argname])) {
                        $argname = $this->shortoptions[$argname];
                    }
                    $argdata = isset($matches[5]) ? $matches[5] : true;
                    $this->arguments[$argname] = $argdata;
                }
                else {
                    // The argument didn't match a known setting so store it in
                    // case this was expected
                    $this->unmatched[] = $argument;
                }
            }
        }

        return array($this->arguments, $this->unmatched);
    }

    /**
     * Retrieve the specified CLI argument
     *
     * @param string $name The name of the argument to retrieve
     * @param mixed $default The default value for the parameter
     * @return mixed the value of that parameter, or true if the value has no
     * paramter but is set
     */
    public function _get_cli_param($name) {
        list($cliparams) = $this->_get_cli_params();

        if (isset($cliparams[$name])) {
            $value = $cliparams[$name];
        }
        else if (isset($this->defaultvalues[$name])) {
            return array($this->defaultvalues[$name], true);
        }
        else if (func_num_args() == 2) {
            $php_work_around = func_get_arg(1);
            return array($php_work_around, true);
        }
        else {
            throw new ParameterException("Missing parameter '$name' and no default supplied");
        }
        return array($value, false);
    }

    /**
     * Retrieve the value of the command line argument for the specified
     * setting.
     *
     * @param string $name The name of the argument to retrieve
     * @param mixed $default The default value to use if the argument was not
     * specified
     * @return mixed
     */
    public function get_cli_param($name) {
        $args = func_get_args();
        list ($value) = call_user_func_array(array($this, '_get_cli_param'), $args);
        return $value;
    }

    /**
     * Retrieve the value of a boolean parameter. Essentially the same as get_cli_param(),
     * except that the string "false" will be interpreted as boolean false. @todo This is
     * basically a workaround until we can implement a proper type system on CLI params.
     *
     * @param string $name Name of the param
     * @return boolean
     */
    public function get_cli_param_boolean($name) {
        $value = $this->get_cli_param($name);
        if (strtolower($value) == 'false') {
            return false;
        }
        else {
            return (boolean) $value;
        }
    }

    /**
     * Retrieve all data supplied on the command line which was not
     * specified as an argument
     *
     * @return array All arguments specified, split on whitespace
     */
    public function get_cli_unmatched() {
        list($cliparams, $unmatched) = $this->_get_cli_params();
        return $unmatched;
    }

    /**
     * Validate all arguments supplied on the command line
     *
     * @return void
     */
    function validate_options() {
        $this->_get_cli_params();

        // Check for unmatched data when allowunmatched is not set
        if (count($this->unmatched) && !$this->allowunmatched) {
            $this->cli_print_help(true);
        }

        // Check for invalid arguments
        foreach ($this->arguments as $argument => $value) {
            if (!isset($this->settings->options[$argument])) {
                log_info('An invalid argument was specified: ' . $argument);
                $this->cli_print_help(true);
            }
        }

        // Check for missing arguments
        foreach ($this->settings->options as $argument => $settings) {
            if ($settings->required && !isset($this->arguments[$argument])) {
                if (isset($settings->required_callback)) {
                    call_user_func($settings->required_callback);
                    $this->cli_print();
                    $this->cli_print_help(true);
                }
                else {
                    $this->cli_print('Missing option ' . $argument);
                    $this->cli_print();
                    $this->cli_print_help(true);
                }
            }
        }
    }

    /**
     * Exit the program with a message and set the exit status appropriately
     *
     * @param string $message The message to output
     * @param int|bool $error (default boolean false) Indicates whether this is an error exit.
     *     If boolean false, exit code 0 (success).
     *     If boolean true, exit code 127 (failure)
     *     If an integer value is passed, use the integer as the exit code.
     * @return void
     */
    public function cli_exit($message = null, $error = false) {
        if ($message) {
            print($message . "\n");
        }

        if (is_int($error)) {
            $exitcode = $error;
        }
        else if ($error === false) {
            $exitcode = 0;
        }
        else {
            $exitcode = 127;
        }
        exit($exitcode);
    }

    /**
     * Print out a message formatted for the command line
     *
     * @param string $message The message to output
     * @return void
     */
    public function cli_print($message = '') {
        print($message . "\n");
    }


    /**
     * Print the help and usage information for this CLI script
     *
     * If a description is supplied for an argument, then this is
     * word-wrapped to standard terminal lengths. All available options are
     * also displayed.
     *
     * @param integer $exitcode The exit code to use, or true to indicate
     * an error - {@see cli_exit}
     */
    public function cli_print_help($exitcode = 0) {
        // Display usage information
        global $argv;
        printf ("Usage: %s ", basename($argv[0]));

        $options = array();
        foreach ($this->settings->options as $option => $settings) {
            $optiondisplay = '--' . $option;
            if (isset($settings->examplevalue)) {
                $optiondisplay .= '=' . $settings->examplevalue;
            }

            if (!$settings->required) {
                $optiondisplay = '[' . $optiondisplay . ']';
            }
            $options[] = $optiondisplay;
        }
        print implode(' ', $options);
        print "\n\n";

        print $this->settings->info . "\n\n";

        foreach ($this->settings->options as $option => $settings) {
            // Line-wrap the description
            $wrapped = wordwrap($settings->description, 48, '|||');
            $lined = preg_split('/\|\|\|/', $wrapped);

            // Merge the long option and short options
            $alloptions = array('--' . $option);
            foreach ($settings->shortoptions as $shortoption) {
                $alloptions[] = '-' . $shortoption;
            }

            if (isset($settings->examplevalue)) {
                foreach ($alloptions as &$option) {
                    $option = $option . '=' . $settings->examplevalue;
                }
            }

            // Pad the arrays to make the loop easier
            $total = max(count($alloptions), count($lined));
            $lined = array_pad($lined, $total, '');
            $alloptions = array_pad($alloptions, $total, '');

            for ($i = 0; $i < $total; $i++) {
                printf("  %-20s\t%s\n", $alloptions[$i], $lined[$i]);
            }
            print "\n";
        }

        $this->cli_exit(null, $exitcode);
    }


    /**
     * Get input from user
     * (This method adapted from Moodle 2.8's "cli_input()" method)
     *
     * @param string $prompt text prompt, should include possible options
     * @param bool $silent Whether to attempt to prevent the user's input from echoing on the CLI
     * @param string $default default value when enter pressed
     * @param array $options list of allowed options, empty means any text
     * @param bool $casesensitive true if options are case sensitive
     * @return string entered text
     */
    function cli_prompt($prompt, $silent = false, $default='', array $options=null, $casesensitiveoptions=false) {
        print($prompt . ': ');
        if ($silent) {
            // This won't work on Windows, and it will cause some display issues if the user
            // terminates the program before entering something. But it's the best we can
            // do with PHP.
            @exec('stty -echo');
        }
        $input = fread(STDIN, 2048);
        if ($silent) {
            @exec('stty echo');
        }
        print("\n");
        $input = trim($input);
        if ($input === '') {
            $input = $default;
        }
        if ($options) {
            if (!$casesensitiveoptions) {
                $input = strtolower($input);
            }
            if (!in_array($input, $options)) {
                $this->cli_print(get_string('cli_incorrect_value') . "\n");
                return $this->cli_prompt($prompt, $default, $options, $casesensitiveoptions);
            }
        }
        return $input;
    }
}

/**
 * Return a single CLI object
 *
 * This is stored in a static cache to ensure that only one instance of the
 * CLI object is called
 *
 * @return CLI object
 */
function get_cli() {
    static $cli = null;
    if ($cli === null) {
        $cli = new cli();
    }

    return $cli;
}
