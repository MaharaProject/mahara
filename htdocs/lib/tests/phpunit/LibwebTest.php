<?php
/**
 *
 * @package    mahara
 * @subpackage tests
 * @author     Yuliya Bozhko <yuliya.bozhko@totaralearning.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * Test functions in lib/web.php
 */
class LibwebTest extends MaharaUnitTest {
    public function testFixUtf8() {
        // Make sure valid data including other types is not changed.
        $this->assertSame(null, fix_utf8(null));
        $this->assertSame(1, fix_utf8(1));
        $this->assertSame(1.1, fix_utf8(1.1));
        $this->assertSame(true, fix_utf8(true));
        $this->assertSame('', fix_utf8(''));
        $this->assertSame('abc', fix_utf8('abc'));
        $array = array('do', 're', 'mi');
        $this->assertSame($array, fix_utf8($array));
        $object = new stdClass();
        $object->a = 'aa';
        $object->b = 'bb';
        $this->assertEquals($object, fix_utf8($object));

        // valid utf8 string
        $this->assertSame("žlutý koníček přeskočil potůček \n\t\r", fix_utf8("žlutý koníček přeskočil potůček \n\t\r\0"));

        // Invalid utf8 string.
        $this->assertSame('aš', fix_utf8('a' . chr(130) . 'š'), 'This fails with buggy iconv() when mbstring extenstion is not available as fallback.');
    }

    public function testFixUtf8Keys() {
        $obj = new stdClass();
        $obj->{"a\0"} = "b\0";
        // Make sure the looping of weird object property names works as expected.
        foreach ($obj as $k => $v) {
            $this->assertTrue("a\0" === $k); // Better use === comparison here instead of the same value assert.
            $this->assertTrue("b\0" === $v); // Better use === comparison here instead of the same value assert.
        }

        $data = array(
            "x\0"                => $obj,
            'a' . chr(130) . 'š' => 'a' . chr(130) . 'š',
        );

        $result = fix_utf8($data);
        $this->assertSame(array('x', 'aš'), array_keys($result));
        $this->assertTrue('aš' === $result['aš']); // Better use === comparison here instead of the same value assert.
        $this->assertSame(array('a' => 'b'), get_object_vars($result['x']));
        $this->assertTrue('b' === $result['x']->a); // Better use === comparison here instead of the same value assert.
        $this->assertTrue(array("a\0" => "b\0") === get_object_vars($obj)); // Better use === comparison here instead of the same value assert.
    }
}
