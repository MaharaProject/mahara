<?php
/**
 * @package    mahara
 * @subpackage test/core
 * @author     David Monllaó 2013; Son Nguyen, Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  portions from Moodle 2013 David Monllaó


/**
 * Nasty strings to use in tests.
 *
 */

defined('INTERNAL') || die;

/**
 * Nasty strings manager.
 *
 * Responds to nasty strings requests with a random string of the list
 * to try with different combinations in different places.
 *
 */
class NastyStrings {

    /**
     * List of different strings to fill fields and assert against them
     *
     * Non of these strings can be a part of another one, this would not be good
     * when using more one string at the same time and asserting results.
     *
     * @static
     * @var array
     */
    protected static $strings = array(
        '< > & &lt; &gt; &amp; \' \\" \ \'$@NULL@$ @@TEST@@ \\\" \\ , ; : . 日本語­% %%',
        '&amp; \' \\" \ \'$@NULL@$ < > & &lt; &gt; @@TEST@@ \\\" \\ , ; : . 日本語­% %%',
        '< > & &lt; &gt; &amp; \' \\" \ \\\" \\ , ; : . \'$@NULL@$ @@TEST@@ 日本語­% %%',
        '< > & &lt; &gt; &amp; \' \\" \ \'$@NULL@$ 日本語­% %%@@TEST@@ \. \\" \\ , ; :',
        '< > & &lt; &gt; \\\" \\ , ; : . 日本語&amp; \' \\" \ \'$@NULL@$ @@TEST@@­% %%',
        '\' \\" \ \'$@NULL@$ @@TEST@@ < > & &lt; &gt; &amp; \\\" \\ , ; : . 日本語­% %%',
        '\\\" \\ , ; : . 日本語­% < > & &lt; &gt; &amp; \' \\" \ \'$@NULL@$ @@TEST@@ %%',
        '< > & &lt; &gt; &amp; \' \\" \ \'$@NULL@$ 日本語­% %% @@TEST@@ \\\" \\ . , ; :',
        '. 日本語&amp; \' \\" < > & &lt; &gt; \\ , ; : \ \'$@NULL@$ \\\" @@TEST@@­% %%',
        '&amp; \' \\" \ < > & &lt; &gt; \\\" \\ , ; : . 日本語\'$@NULL@$ @@TEST@@­% %%',
    );

    /**
     * Already used nasty strings.
     *
     * This array will be cleaned before each scenario.
     *
     * @static
     * @var array
     */
    protected static $usedstrings = array();

    /**
     * Returns a nasty string and stores the key mapping.
     *
     * @static
     * @param string $key The key
     * @return string
     */
    public static function get($key) {

        // If have been used during the this tests return it.
        if (isset(self::$usedstrings[$key])) {
            return self::$strings[self::$usedstrings[$key]];
        }

        // Getting non-used random string.
        do {
            $index = self::random_index();
        } while (in_array($index, self::$usedstrings));

        // Mark the string as already used.
        self::$usedstrings[$key] = $index;

        return self::$strings[$index];
    }

    /**
     * Resets the used strings var.
     * @static
     * @return void
     */
    public static function reset_used_strings() {
        self::$usedstrings = array();
    }

    /**
     * Returns a random index.
     * @static
     * @return int
     */
    protected static function random_index() {
        return mt_rand(0, count(self::$strings) - 1);
    }

}
