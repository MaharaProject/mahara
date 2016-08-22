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

    /**
     * Test emails for the sanitize_email function.
     * Email addresses mostly taken from the PHPMailer Project:
     * https://github.com/PHPMailer/PHPMailer/blob/master/test/phpmailerTest.php#L329
     * ... which in turn mostly obtained them from http://isemail.info
     * See htdocs/lib/phpmailer for PHPMailer copyright and license information.
     * @return array
     */
    public function sanitizeEmailProvider() {
        $validaddresses = array(
            'first@iana.org',
            'first.last@iana.org',
            '1234567890123456789012345678901234567890123456789012345678901234@iana.org',
            '"first\"last"@iana.org',
            '"first@last"@iana.org',
            '"first\last"@iana.org',
            'first.last@[12.34.56.78]',
            'first.last@[IPv6:::12.34.56.78]',
            'first.last@[IPv6:1111:2222:3333::4444:12.34.56.78]',
            'first.last@[IPv6:1111:2222:3333:4444:5555:6666:12.34.56.78]',
            'first.last@[IPv6:::1111:2222:3333:4444:5555:6666]',
            'first.last@[IPv6:1111:2222:3333::4444:5555:6666]',
            'first.last@[IPv6:1111:2222:3333:4444:5555:6666::]',
            'first.last@[IPv6:1111:2222:3333:4444:5555:6666:7777:8888]',
            'first.last@x23456789012345678901234567890123456789012345678901234567890123.iana.org',
            'first.last@3com.com',
            'first.last@123.iana.org',
            '"first\last"@iana.org',
            'first.last@[IPv6:1111:2222:3333::4444:5555:12.34.56.78]',
            'first.last@[IPv6:1111:2222:3333::4444:5555:6666:7777]',
            'first.last@example.123',
            'first.last@com',
            '"Abc\@def"@iana.org',
            '"Fred\ Bloggs"@iana.org',
            '"Joe.\Blow"@iana.org',
            '"Abc@def"@iana.org',
            'user+mailbox@iana.org',
            'customer/department=shipping@iana.org',
            '$A12345@iana.org',
            '!def!xyz%abc@iana.org',
            '_somename@iana.org',
            'dclo@us.ibm.com',
            'peter.piper@iana.org',
            '"Doug \"Ace\" L."@iana.org',
            'test@iana.org',
            'TEST@iana.org',
            '1234567890@iana.org',
            'test+test@iana.org',
            'test-test@iana.org',
            't*est@iana.org',
            '+1~1+@iana.org',
            '{_test_}@iana.org',
            '"[[ test ]]"@iana.org',
            'test.test@iana.org',
            '"test.test"@iana.org',
            'test."test"@iana.org',
            '"test@test"@iana.org',
            'test@123.123.123.x123',
            'test@123.123.123.123',
            'test@[123.123.123.123]',
            'test@example.iana.org',
            'test@example.example.iana.org',
            '"test\test"@iana.org',
            'test@example',
            '"test\blah"@iana.org',
            '"test\blah"@iana.org',
            '"test\"blah"@iana.org',
            'customer/department@iana.org',
            '_Yosemite.Sam@iana.org',
            '~@iana.org',
            '"Austin@Powers"@iana.org',
            'Ima.Fool@iana.org',
            '"Ima.Fool"@iana.org',
            '"Ima Fool"@iana.org',
            '"first"."last"@iana.org',
            '"first".middle."last"@iana.org',
            '"first".last@iana.org',
            'first."last"@iana.org',
            '"first"."middle"."last"@iana.org',
            '"first.middle"."last"@iana.org',
            '"first.middle.last"@iana.org',
            '"first..last"@iana.org',
            '"first\"last"@iana.org',
            'first."mid\dle"."last"@iana.org',
            '"test blah"@iana.org',
            '(foo)cal(bar)@(baz)iamcal.com(quux)',
            'cal@iamcal(woo).(yay)com',
            'cal(woo(yay)hoopla)@iamcal.com',
            'cal(foo\@bar)@iamcal.com',
            'cal(foo\)bar)@iamcal.com',
            'first().last@iana.org',
            'pete(his account)@silly.test(his host)',
            'c@(Chris\'s host.)public.example',
            'jdoe@machine(comment). example',
            '1234 @ local(blah) .machine .example',
            'first(abc.def).last@iana.org',
            'first(a"bc.def).last@iana.org',
            'first.(")middle.last(")@iana.org',
            'first(abc\(def)@iana.org',
            'first.last@x(1234567890123456789012345678901234567890123456789012345678901234567890).com',
            'a(a(b(c)d(e(f))g)h(i)j)@iana.org',
            'name.lastname@domain.com',
            'a@b',
            'a@bar.com',
            'aaa@[123.123.123.123]',
            'a@bar',
            'a-b@bar.com',
            '+@b.c',
            '+@b.com',
            'a@b.co-foo.uk',
            '"hello my name is"@stutter.com',
            '"Test \"Fail\" Ing"@iana.org',
            'valid@about.museum',
            'shaitan@my-domain.thisisminekthx',
            'foobar@192.168.0.1',
            '"Joe\Blow"@iana.org',
            'HM2Kinsists@(that comments are allowed)this.is.ok',
            'user%uucp!path@berkeley.edu',
            'first.last @iana.org',
            'cdburgess+!#$%&\'*-/=?+_{}|~test@gmail.com',
            'first.last@[IPv6:::a2:a3:a4:b1:b2:b3:b4]',
            'first.last@[IPv6:a1:a2:a3:a4:b1:b2:b3::]',
            'first.last@[IPv6:::]',
            'first.last@[IPv6:::b4]',
            'first.last@[IPv6:::b3:b4]',
            'first.last@[IPv6:a1::b4]',
            'first.last@[IPv6:a1::]',
            'first.last@[IPv6:a1:a2::]',
            'first.last@[IPv6:0123:4567:89ab:cdef::]',
            'first.last@[IPv6:0123:4567:89ab:CDEF::]',
            'first.last@[IPv6:::a3:a4:b1:ffff:11.22.33.44]',
            'first.last@[IPv6:::a2:a3:a4:b1:ffff:11.22.33.44]',
            'first.last@[IPv6:a1:a2:a3:a4::11.22.33.44]',
            'first.last@[IPv6:a1:a2:a3:a4:b1::11.22.33.44]',
            'first.last@[IPv6:a1::11.22.33.44]',
            'first.last@[IPv6:a1:a2::11.22.33.44]',
            'first.last@[IPv6:0123:4567:89ab:cdef::11.22.33.44]',
            'first.last@[IPv6:0123:4567:89ab:CDEF::11.22.33.44]',
            'first.last@[IPv6:a1::b2:11.22.33.44]',
            'test@test.com',
            'test@xn--example.com',
            'test@example.com'
        );

        $invalidaddresses = array(
            'first.last@sub.do,com',
            'first\@last@iana.org',
            '123456789012345678901234567890123456789012345678901234567890' .
                '@12345678901234567890123456789012345678901234 [...]',
            'first.last',
            '12345678901234567890123456789012345678901234567890123456789012345@iana.org',
            '.first.last@iana.org',
            'first.last.@iana.org',
            'first..last@iana.org',
            '"first"last"@iana.org',
            '"""@iana.org',
            '"\"@iana.org',
            //'""@iana.org',
            'first\@last@iana.org',
            'first.last@',
            'x@x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.' .
                'x23456789.x23456789.x23456789.x23 [...]',
            'first.last@[.12.34.56.78]',
            'first.last@[12.34.56.789]',
            'first.last@[::12.34.56.78]',
            'first.last@[IPv5:::12.34.56.78]',
            'first.last@[IPv6:1111:2222:3333:4444:5555:12.34.56.78]',
            'first.last@[IPv6:1111:2222:3333:4444:5555:6666:7777:12.34.56.78]',
            'first.last@[IPv6:1111:2222:3333:4444:5555:6666:7777]',
            'first.last@[IPv6:1111:2222:3333:4444:5555:6666:7777:8888:9999]',
            'first.last@[IPv6:1111:2222::3333::4444:5555:6666]',
            'first.last@[IPv6:1111:2222:333x::4444:5555]',
            'first.last@[IPv6:1111:2222:33333::4444:5555]',
            'first.last@-xample.com',
            'first.last@exampl-.com',
            'first.last@x234567890123456789012345678901234567890123456789012345678901234.iana.org',
            'abc\@def@iana.org',
            'abc\@iana.org',
            'Doug\ \"Ace\"\ Lovell@iana.org',
            'abc@def@iana.org',
            'abc\@def@iana.org',
            'abc\@iana.org',
            '@iana.org',
            'doug@',
            '"qu@iana.org',
            'ote"@iana.org',
            '.dot@iana.org',
            'dot.@iana.org',
            'two..dot@iana.org',
            '"Doug "Ace" L."@iana.org',
            'Doug\ \"Ace\"\ L\.@iana.org',
            'hello world@iana.org',
            //'helloworld@iana .org',
            'gatsby@f.sc.ot.t.f.i.tzg.era.l.d.',
            'test.iana.org',
            'test.@iana.org',
            'test..test@iana.org',
            '.test@iana.org',
            'test@test@iana.org',
            'test@@iana.org',
            '-- test --@iana.org',
            '[test]@iana.org',
            '"test"test"@iana.org',
            '()[]\;:,><@iana.org',
            'test@.',
            'test@example.',
            'test@.org',
            'test@12345678901234567890123456789012345678901234567890123456789012345678901234567890' .
                '12345678901234567890 [...]',
            'test@[123.123.123.123',
            'test@123.123.123.123]',
            'NotAnEmail',
            '@NotAnEmail',
            '"test"blah"@iana.org',
            '.wooly@iana.org',
            'wo..oly@iana.org',
            'pootietang.@iana.org',
            '.@iana.org',
            'Ima Fool@iana.org',
            'phil.h\@\@ck@haacked.com',
            'foo@[\1.2.3.4]',
            //'first."".last@iana.org',
            'first\last@iana.org',
            'Abc\@def@iana.org',
            'Fred\ Bloggs@iana.org',
            'Joe.\Blow@iana.org',
            'first.last@[IPv6:1111:2222:3333:4444:5555:6666:12.34.567.89]',
            '{^c\@**Dog^}@cartoon.com',
            //'"foo"(yay)@(hoopla)[1.2.3.4]',
            'cal(foo(bar)@iamcal.com',
            'cal(foo)bar)@iamcal.com',
            'cal(foo\)@iamcal.com',
            'first(12345678901234567890123456789012345678901234567890)last@(1234567890123456789' .
                '01234567890123456789012 [...]',
            'first(middle)last@iana.org',
            'first(abc("def".ghi).mno)middle(abc("def".ghi).mno).last@(abc("def".ghi).mno)example' .
                '(abc("def".ghi).mno). [...]',
            'a(a(b(c)d(e(f))g)(h(i)j)@iana.org',
            '.@',
            '@bar.com',
            '@@bar.com',
            'aaa.com',
            'aaa@.com',
            'aaa@.123',
            'aaa@[123.123.123.123]a',
            'aaa@[123.123.123.333]',
            'a@bar.com.',
            'a@-b.com',
            'a@b-.com',
            '-@..com',
            '-@a..com',
            'invalid@about.museum-',
            'test@...........com',
            '"Unicode NULL' . chr(0) . '"@char.com',
            'Unicode NULL' . chr(0) . '@char.com',
            'first.last@[IPv6::]',
            'first.last@[IPv6::::]',
            'first.last@[IPv6::b4]',
            'first.last@[IPv6::::b4]',
            'first.last@[IPv6::b3:b4]',
            'first.last@[IPv6::::b3:b4]',
            'first.last@[IPv6:a1:::b4]',
            'first.last@[IPv6:a1:]',
            'first.last@[IPv6:a1:::]',
            'first.last@[IPv6:a1:a2:]',
            'first.last@[IPv6:a1:a2:::]',
            'first.last@[IPv6::11.22.33.44]',
            'first.last@[IPv6::::11.22.33.44]',
            'first.last@[IPv6:a1:11.22.33.44]',
            'first.last@[IPv6:a1:::11.22.33.44]',
            'first.last@[IPv6:a1:a2:::11.22.33.44]',
            'first.last@[IPv6:0123:4567:89ab:cdef::11.22.33.xx]',
            'first.last@[IPv6:0123:4567:89ab:CDEFF::11.22.33.44]',
            'first.last@[IPv6:a1::a4:b1::b4:11.22.33.44]',
            'first.last@[IPv6:a1::11.22.33]',
            'first.last@[IPv6:a1::11.22.33.44.55]',
            'first.last@[IPv6:a1::b211.22.33.44]',
            'first.last@[IPv6:a1::b2::11.22.33.44]',
            'first.last@[IPv6:a1::b3:]',
            'first.last@[IPv6::a2::b4]',
            'first.last@[IPv6:a1:a2:a3:a4:b1:b2:b3:]',
            'first.last@[IPv6::a2:a3:a4:b1:b2:b3:b4]',
            'first.last@[IPv6:a1:a2:a3:a4::b1:b2:b3:b4]',
            //This is a valid RCC5322 address, but we don't want to allow it for obvious reasons!
            "(\r\n RCPT TO:user@example.com\r\n DATA \\\nSubject: spam10\\\n\r\n Hello,\r\n".
                " this is a spam mail.\\\n.\r\n QUIT\r\n ) a@example.net"
        );

// TODO: Support for Unicode in domain names
//        // IDNs in Unicode and ASCII forms.
//         $unicodeidnaddresses = array(
//             'first.last@bücher.ch',
//             'first.last@кто.рф',
//             'first.last@phplíst.com',
//         );

// TODO: Support for international email addresses
//         // https://en.wikipedia.org/wiki/International_email
//         $intladdresses = array(
//             '用户@例子.广告',
//             'उपयोगकर्ता@उदाहरण.कॉम',
//             'юзер@екзампл.ком',
//             'θσερ@εχαμπλε.ψομ',
//             'Dörte@Sörensen.example.com'
//         );

        // Puny-encoded international domains
        $idnasciiaddresses = array(
            'first.last@xn--bcher-kva.ch',
            'first.last@xn--j1ail.xn--p1ai',
            'first.last@xn--phplst-6va.com',
        );

        $values = array();
        foreach ($validaddresses as $address) {
            $values[] = [$address, true];
        }
        foreach ($invalidaddresses as $address) {
            $values[] = [$address, false];
        }
        foreach ($idnasciiaddresses as $address) {
            $values[] = [$address, true];
        }
        return $values;
    }

    /**
     * Test the sanitize_email() function
     * @dataProvider sanitizeEmailProvider
     * @param string $testemail An email address to test for validity
     * @param boolean $expectedresult Whether the test email is valid or not
     */
    public function testSanitizeEmail($testemail, $expectedresult) {
        $result = sanitize_email($testemail);
        $this->assertEquals((bool) $result, $expectedresult);
        // The function is meant to either return an empty string, or the original
        // unchanged email address.
        if ((bool) $result) {
            $this->assertEquals($result, $testemail);
        }
        else {
            $this->assertEquals($result, '');
        }
    }

    public function tearDown() {
        set_config('wwwroot', $this->realwwwroot);
        parent::tearDown();
    }
}
