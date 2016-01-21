<?php
/**
 * @package    mahara
 * @subpackage form-element
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * Defines a form element that adds a CAPTCHA to a form. To use, simply add an element of
 * type "captcha" to your form, in the point where you want the CAPTCHA field to display:
 *
 *     $elements['captcha'] = array(
 *            'type' => 'captcha',
 *     );
 *
 * You can optionally also fill in the title and description. If not supplied, default
 * values will be used.
 *
 * The CAPTCHA element will only be displayed if a CAPTCHA method is configured and enabled
 * in the sitewide settings. If it is displayed, it will validate the user's input
 * automagically.
 *
 * @param Pieform $form
 * @param array $element
 */
function pieform_element_captcha(Pieform $form, &$element) {
    // TODO: Make this a pluggable system for other CAPTCHA providers?
    return '
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        <div class="g-recaptcha" data-sitekey="' . clean_html(get_config('recaptchapublickey')) . '"></div>
    ';
}

/**
 * The CAPTCHA element returns no value. Its only purpose is validation.
 *
 * @param Pieform $form
 * @param array $element
 */
function pieform_element_captcha_get_value(Pieform $form, $element) {
    return null;
}

/**
 * Hide the element if captcha is not enabled and configured.
 *
 * If we are showing the element, add a "rule" to it in order to trigger validation.
 *
 * @param array $element
 * @return mixed Boolean FALSE if the element shouldn't be displayed; the modified element if it should be displayed
 */
function pieform_element_captcha_set_attributes($element) {
    if (
            get_config('recaptchaonregisterform')
            && get_config('recaptchapublickey')
            && get_config('recaptchaprivatekey')
    ) {
        require_once(get_config('libroot').'recaptcha/autoload.php');
        if (array_key_exists('rules', $element)) {
            $element['rules'] = array();
        }
        $element['rules']['validate'] = array();
        if (empty($element['description'])) {
            $element['description'] = get_string('recaptcharegisterdesc', 'auth.internal');
        }
        if (empty($element['title'])) {
            $element['title'] = get_string('recaptcharegistertitle', 'auth.internal');
        }
    }
    else {
        // Don't display this element if captcha not configured
        return false;
    }
    return $element;
}

/**
 * Validate the CAPTCHA.
 *
 * @param Pieform $form
 * @param string $value
 * @param array $element
 * @param array $data
 * @return mixed An error string if validation failed; boolean FALSE if validation passed
 */
function pieform_element_captcha_rule_validate(Pieform $form, $value, $element, $data) {
    $recaptcha = new \ReCaptcha\ReCaptcha(get_config('recaptchaprivatekey'));
    $resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
    if (!$resp->isSuccess()) {
        $errors = $resp->getErrorCodes();
        return get_string('recaptchanotpassed', 'admin');
    }

    return false;
}
