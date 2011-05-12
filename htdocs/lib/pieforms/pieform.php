<?php
/**
 * Pieforms: Advanced web forms made easy
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @package    pieform
 * @subpackage core
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

$GLOBALS['_PIEFORM_REGISTRY'] = array();

/** The form was processed successfully */
define('PIEFORM_OK', 0);
/** The form failed processing/validating */
define('PIEFORM_ERR', -1);
/** A cancel button was pressed */
define('PIEFORM_CANCEL', -2);

/**
 * Builds, validates and processes a form.
 *
 * Given a form definition, and as long as one or two functions are implemented
 * by the caller, this function will handle everything else.
 *
 * USAGE:
 *
 * <pre>
 * $form = array(
 *     'name' => 'myform',
 *     'method' => 'post',
 *     'elements' => array(
 *         // definition of elements in the form
 *     )
 * );
 *
 * $smarty->assign('myform', pieform($form));
 *
 * function myform_validate(Pieform $form, $values) {
 *     // perform validation agains form elements here
 *     // some types of validation are conveniently available already as
 *     // as part of the form definition hash
 * }
 *
 * function myform_submit(Pieform $form, $values) {
 *     // perform action knowing that the values are valid, e.g. DB insert.
 * }
 * </pre>
 *
 * Please see http://pieforms.sourceforge.net/doc/html/ for
 * more information on creating and using forms.
 *
 */
function pieform($data) {/*{{{*/
    return Pieform::process($data);
}/*}}}*/

/**
 * Pieforms throws PieformExceptions when things go wrong
 */
class PieformException extends Exception {}

/**
 * Represents an HTML form. Forms created using this class have a lot of the
 * legwork for forms abstracted away.
 *
 * Pieforms makes it really easy to build complex HTML forms, simply by
 * building a hash describing your form, and defining one or two callback
 * functions.
 *
 * For more information on how Pieforms works, please see the documentation
 * at http://pieforms.sourceforge.net/doc/html/
 */
class Pieform {/*{{{*/

    /*{{{ Fields */

    /**
     * Maintains a tab index across all created forms, to make it easy for
     * people to forget about it and have it just work for all of their forms.
     *
     * @var int
     */
    public static $formtabindex = 1;

    /**
     * The form name. This is required.
     *
     * @var string
     */
    private $name = '';

    /**
     * Data for the form
     *
     * @var array
     */
    private $data = array();

    /**
     * A hash of references to the elements of the form, not including 
     * fieldsets (although including all elements inside any fieldsets. Used 
     * internally for simplifying looping over elements
     *
     * @var array
     */
    private $elementrefs = array();

    /**
     * Whether this form includes a file element. If so, the enctype attribute
     * for the form will be specified as "multipart/mixed" as required. This
     * is auto-detected by the Pieform class.
     *
     * @var bool
     */
    private $fileupload = false;

    /**
     * Whether the form has been submitted. Available through the
     * {@link is_submitted} method.
     *
     * @var bool
     */
    private $submitted = false;

    /**
     * Whether the form has been submitted by javasccript. Available through 
     * the {@link submitted_by_js} method.
     *
     * @var bool
     */
    private $submitted_by_js = false;

    /*}}}*/

    /**
     * Processes the form. Called by the {@link pieform} function. It simply
     * builds the form (processing it if it has been submitted), and returns
     * the HTML to display the form.
     *
     * @param array $data The form description hash
     * @return string     The HTML representing the form
     */
    public static function process($data) {/*{{{*/
        $form = new Pieform($data);
        return $form->build();
    }/*}}}*/

    /**
     * Sets the attributes of the form according to the passed data, performing
     * validation on the way. If the form is submitted, this checks and processes
     * the form.
     *
     * @param array $data The form description hash
     */
    public function __construct($data) {/*{{{*/
        $GLOBALS['_PIEFORM_REGISTRY'][] = $this;

        if (!isset($data['name']) || !preg_match('/^[a-z_][a-z0-9_]*$/', $data['name'])) {
            throw new PieformException('Forms must have a name, and that name must be valid (validity test: could you give a PHP function the name?)');
        }
        $this->name = $data['name'];

        // If the form has global configuration, get it now
        if (function_exists('pieform_configure')) {
            $formconfig = pieform_configure();
            $defaultelements = (isset($formconfig['elements'])) ? $formconfig['elements'] : array();
            foreach ($defaultelements as $name => $element) {
                if (!isset($data['elements'][$name])) {
                    $data['elements'][$name] = $element;
                }
            }
        }
        else {
            $formconfig = array();
        }

        // Assign defaults for the form
        $this->defaults = self::get_pieform_defaults();
        $this->data = array_merge($this->defaults, $formconfig, $data);

        // Set the method - only get/post allowed
        $this->data['method'] = strtolower($this->data['method']);
        if ($this->data['method'] != 'post') {
            $this->data['method'] = 'get';
        }

        // Make sure that the javascript callbacks are valid
        if ($this->data['jsform']) {
            $this->validate_js_callbacks();
        }

        if (!$this->data['validatecallback']) {
            $this->data['validatecallback'] = $this->name . '_validate';
        }

        if (!$this->data['successcallback']) {
            $this->data['successcallback'] = $this->name . '_submit';
        }

        if (!$this->data['replycallback']) {
            $this->data['replycallback'] = $this->name . '_reply';
        }

        $this->data['configdirs'] = array_map(
            create_function('$a', 'return substr($a, -1) == "/" ? substr($a, 0, -1) : $a;'),
            (array) $this->data['configdirs']);


        if (empty($this->data['tabindex'])) {
            $this->data['tabindex'] = self::$formtabindex++;
        }

        if (!is_array($this->data['elements']) || count($this->data['elements']) == 0) {
            throw new PieformException('Forms must have a list of elements');
        }

        if (isset($this->data['spam'])) {
            // Enable form tricks to make it harder for bots to fill in the form.
            // This was moved from lib/antispam.php, see:
            // http://wiki.mahara.org/Developer_Area/Specifications_in_Development/Anti-spam#section_7
            //
            // Use the spam_error() method in your _validate function to check whether a submitted form
            // has failed any of these checks.
            //
            // Available options:
            //  - hash:    An array of element names to be hashed.  Currently ids of input elements
            //             are also hashed, so you need to be careful if you include 'elementname' in
            //             the hash array, and make sure you rewrite any css or js so it doesn't rely on
            //             an id like 'formname_elementname'.
            //  - secret:  String used to hash the fields.
            //  - mintime: Minimum number of seconds that must pass between page load & form submission.
            //  - maxtime: Maximum number of seconds that must pass between page load & form submission.
            //  - reorder: Array of element names to be reordered at random.
            if (empty($this->data['spam']['secret']) || !isset($this->data['elements']['submit'])) {
                // @todo don't rely on submit element
                throw new PieformException('Forms with spam config must have a secret and submit element');
            }
            $this->time = isset($_POST['__timestamp']) ? $_POST['__timestamp'] : time();
            $spamelements1 = array(
                '__invisiblefield' => array(
                    'type'         => 'text',
                    'title'        => get_string('spamtrap'),
                    'defaultvalue' => '',
                    'class'        => 'dontshow',
                ),
            );
            $spamelements2 = array(
                '__timestamp' => array(
                    'type' => 'hidden',
                    'value' => $this->time,
                ),
                '__invisiblesubmit' => array(
                    'type'  => 'submit',
                    'value' => get_string('spamtrap'),
                    'class' => 'dontshow',
                ),
            );
            $insert = rand(0, count($this->data['elements']));
            $this->data['elements'] = array_merge(
                array_slice($this->data['elements'], 0, $insert, true),
                $spamelements1,
                array_slice($this->data['elements'], $insert, count($this->data['elements']) - $insert, true),
                $spamelements2
            );

            // Min & max number of seconds between page load & submission
            if (!isset($this->data['spam']['mintime'])) {
                $this->data['spam']['mintime'] = 0.01;
            }
            if (!isset($this->data['spam']['maxtime'])) {
                $this->data['spam']['maxtime'] = 86400;
            }

            if (empty($this->data['spam']['hash'])) {
                $this->data['spam']['hash'] = array();
            }
            $this->data['spam']['hash'][] = '__invisiblefield';
            $this->data['spam']['hash'][] = '__invisiblesubmit';
            $this->hash_fieldnames();

            if (isset($this->data['spam']['reorder'])) {
                // Reorder form fields randomly
                $order = $this->data['spam']['reorder'];
                shuffle($order);
                $order = array_combine($this->data['spam']['reorder'], $order);
                $temp = array();
                foreach (array_keys($this->data['elements']) as $k) {
                    if (isset($order[$k])) {
                        $temp[$order[$k]] = $this->data['elements'][$order[$k]];
                    }
                    else {
                        $temp[$k] = $this->data['elements'][$k];
                    }
                }
                $this->data['elements'] = $temp;
            }

            $this->spamerror = false;
        }

        // Get references to all the elements in the form, excluding fieldsets
        foreach ($this->data['elements'] as $name => &$element) {
            // The name can be in the element itself. This is compatibility for 
            // the perl version
            if (isset($element['name'])) {
                $name = $element['name'];
            }

            if (isset($element['type']) && $element['type'] == 'fieldset') {
                // Load the fieldset plugin as we know this form has one now
                $this->include_plugin('element', 'fieldset');
                if ($this->get_property('template')) {
                    self::info("Your form '$this->name' has a fieldset, but is using a template. Fieldsets make no sense when using templates");
                }

                foreach ($element['elements'] as $subname => &$subelement) {
                    if (isset($subelement['name'])) {
                        $subname = $subelement['name'];
                    }
                    $this->elementrefs[$subname] = &$subelement;
                    $subelement['name'] = $subname;
                }
                unset($subelement);
            }
            else {
                $this->elementrefs[$name] = &$element;
            }

            $element['name'] = isset($this->hashedfields[$name]) ? $this->hashedfields[$name] : $name;

        }
        unset($element);

        // Check that all elements have names compliant to PHP's variable naming policy 
        // (otherwise things get messy later)
        foreach (array_keys($this->elementrefs) as $name) {
            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name)) {
                throw new PieformException('Element "' . $name . '" is badly named (validity test: could you give a PHP variable the name?)');
            }
        }

        // Remove elements to ignore
        // This can't be done using $this->elementrefs, because you can't unset 
        // an entry in there and have it unset the entry in $this->data['elements']
        foreach ($this->data['elements'] as $name => $element) {
            if (isset($element['type']) && $element['type'] == 'fieldset') {
                foreach ($element['elements'] as $subname => $subelement) {
                    if (!empty($subelement['ignore'])) {
                        unset ($this->data['elements'][$name]['elements'][$subname]);
                        unset($this->elementrefs[$subname]);
                    }
                }
            }
            else {
                if (!empty($element['ignore'])) {
                    unset($this->data['elements'][$name]);
                    unset($this->elementrefs[$name]);
                }
            }
        }

        // Set some attributes for all elements
        $autofocusadded = false;
        foreach ($this->elementrefs as $name => &$element) {
            if (count($element) == 0) {
                throw new PieformException('An element in form "' . $this->name . '" has no data (' . $name . ')');
            }

            if (!isset($element['type']) || $element['type'] == 'markup') {
                $element['type'] = 'markup';
                if (!isset($element['value'])) {
                    throw new PieformException('The markup element "'
                        . $name . '" has no value');
                }
            }
            else {
                // Now we know what type the element is, we can load the plugin for it
                $this->include_plugin('element',  $element['type']);

                // All elements should have at least the title key set
                if (!isset($element['title'])) {
                    $element['title'] = '';
                }

                // This function can be defined by the application using Pieforms,
                // and applies to all elements of this type
                $function = 'pieform_element_' . $element['type'] . '_configure';
                if (function_exists($function)) {
                    $element = $function($element);
                }

                // vvv --------------------------------------------------- vvv
                // After this point Pieforms can set or override attributes 
                // without fear that the developer will be able to change them.

                // This function is defined by the plugin itself, to set
                // fields on the element that need to be set but should not 
                // be set by the application
                $function = 'pieform_element_' . $element['type'] . '_set_attributes';
                if (function_exists($function)) {
                    $element = $function($element);
                }

                // Force the form method to post if there is a file to upload
                if (!empty($element['needsmultipart'])) {
                    $this->fileupload = true;
                    if ($this->data['method'] == 'get') {
                        $this->data['method'] = 'post';
                        self::info("Your form '$this->name' had the method 'get' and also a file element - it has been converted to 'post'");
                    }
                }

                // Add the autofocus flag to the element if required
                if (!$autofocusadded && $this->data['autofocus'] === true && empty($element['nofocus'])) {
                    $element['autofocus'] = true;
                    $autofocusadded = true;
                }
                elseif (!empty($this->data['autofocus']) && $this->data['autofocus'] !== true
                    && $name == $this->data['autofocus']) {
                    $element['autofocus'] = true;
                }

                if (!empty($element['autofocus']) && $element['type'] == 'text' && !empty($this->data['autoselect'])
                    && $name == $this->data['autoselect']) {
                    $element['autoselect'] = true;
                }

                // All elements inherit the form tabindex
                $element['tabindex'] = $this->data['tabindex'];
            }
        }
        unset($element);

        // Check if the form was submitted, and if so, validate and process it
        $global = ($this->data['method'] == 'get') ? $_GET: $_POST;
        if ($this->data['validate'] && isset($global['pieform_' . $this->name] )) {
            if ($this->data['submit']) {
                $this->submitted = true;

                // If the hidden value the JS code inserts into the form is 
                // present, then the form was submitted by JS
                if (!empty($global['pieform_jssubmission'])) {
                    $this->submitted_by_js = true;
                }

                // Check if the form has been cancelled
                if ($this->data['iscancellable']) {
                    foreach ($global as $key => $value) {
                        if (substr($key, 0, 7) == 'cancel_') {
                            // Check for and call the cancel function handler, if defined
                            $function = $this->name . '_' . $key;
                            if (function_exists($function)) {
                                $function($this);
                            }

                            // Redirect the user to where they should go, if the cancel handler didn't already
                            $element = $this->get_element(substr($key, 7));
                            if (!isset($element['goto'])) {
                                throw new PieformException('Cancel element "' . $element['name'] . '" has no page to go to');
                            }
                            if ($this->submitted_by_js) {
                                $this->json_reply(PIEFORM_CANCEL, array('location' => $element['goto']), false);
                            }
                            header('HTTP/1.1 303 See Other');
                            header('Location:' . $element['goto']);
                            exit;
                        }
                    }
                }
            }

            // Get the values that were submitted
            $values = $this->get_submitted_values();
            // Perform general validation first
            $this->validate($values);

            // Submit the form if things went OK
            if ($this->data['submit'] && !$this->has_errors()) {
                $submitted = false;
                foreach ($this->elementrefs as $name => $element) {
                    if (!empty($element['submitelement']) && isset($global[$element['name']])) {
                        $function = "{$this->data['successcallback']}_{$name}";
                        if (function_exists($function)) {
                            $function($this, $values);
                            $submitted = true;
                            break;
                        }
                    }
                }
                $function = $this->data['successcallback'];
                if (!$submitted && is_callable($function)) {
                    // Call the user defined function for processing a submit
                    // This function should really redirect/exit after it has
                    // finished processing the form.
                    call_user_func_array($function, array($this, $values));
                    if ($this->data['dieaftersubmit']) {
                        if ($this->data['jsform']) {
                            $message = 'Your ' . $this->name . '_submit function should use $form->reply to send a response, which should redirect or exit when it is done. Perhaps you want to make your reply callback do this?';
                        }
                        else {
                            $message = 'Your ' . $this->name . '_submit function should redirect or exit when it is done';
                        }
                        throw new PieformException($message);
                    }
                    else {
                        // Successful submission, and the user doesn't care about replying, so...
                        return;
                    }
                }
                else if (!$submitted) {
                    throw new PieformException('No function registered to handle form submission for form "' . $this->name . '"');
                }
            }

            // If we get here, the form was submitted but failed validation

            // Auto focus the first element with an error if required
            if ($this->data['autofocus'] !== false) {
                $this->auto_focus_first_error();
            }

            // Call the user-defined PHP error function, if it exists
            $function = $this->data['errorcallback'];
            if (is_callable($function)) {
                call_user_func_array($function, array($this));
            }
            
            // If the form has been submitted by javascript, return json
            if ($this->submitted_by_js) {
                // TODO: get error messages in a 'third person' type form to 
                // use here maybe? Would have to work for non js forms too. See 
                // the TODO file
                //$errors = $this->get_errors();
                //$json = array();
                //foreach ($errors as $element) {
                //    $json[$element['name']] = $element['error'];
                //}
                $message = $this->get_property('jserrormessage');
                $this->json_reply(PIEFORM_ERR, array('message' => $message));
            }
        }
    }/*}}}*/

    /**
     * Returns the form name
     *
     * @return string
     */
    public function get_name() {/*{{{*/
        return $this->name;
    }/*}}}*/

    /**
     * Returns a generic property. This can be used to retrieve any property
     * set in the form data array, so developers can pass in random stuff and
     * get access to it.
     *
     * @param string The key of the property to return. If the property doesn't 
     *               exist, null is returned
     * @return mixed
     */
    public function get_property($key) {/*{{{*/
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }
        return null;
    }/*}}}*/

    /**
     * Returns whether the form has been submitted
     *
     * @return bool
     */
    public function is_submitted() {/*{{{*/
        return $this->submitted;
    }/*}}}*/

    /**
     * Returns whether the form has been submitted by javascript
     *
     * @return bool
     */
    public function submitted_by_js() {/*{{{*/
        return $this->submitted_by_js;
    }/*}}}*/

    /**
     * Returns the HTML for the <form...> tag
     *
     * @return string
     */
    public function get_form_tag() {/*{{{*/
        $result = '<form class="pieform';
        if ($this->has_errors()) {
            $result .= ' error';
        }
        if (isset($this->data['class'])) {
            $result .= ' ' . self::hsc($this->data['class']);
        }
        $result .= '"';
        foreach (array('name', 'method', 'action') as $attribute) {
            $result .= ' ' . $attribute . '="' . self::hsc($this->data[$attribute]) . '"';
        }
        $result .= ' id="' . $this->name . '"';
        if ($this->fileupload) {
            $result .= ' enctype="multipart/form-data"';
        }
        $result .= '>';
        if (!empty($this->error)) {
            $result .= '<div class="error">' . $this->error . '</div>';
        }
        return $result;
    }/*}}}*/

    /**
     * Builds and returns the HTML for the form, using the chosen renderer or 
     * template
     *
     * Note that the "action" attribute for the form tag is NOT HTML escaped
     * for you. This allows you to build your own URLs, should you require. On
     * the other hand, this means you must be careful about escaping the URL,
     * especially if it has data from an external source in it.
     *
     * @param boolean Whether to include the <form...></form> tags in the output
     * @return string The form as HTML
     */
    public function build($outputformtags=true) {/*{{{*/
        $result = '';

        // Builds the HTML each element (see the build_element_html method for 
        // more information)
        foreach ($this->data['elements'] as &$element) {
            if ($element['type'] == 'fieldset') {
                foreach ($element['elements'] as &$subelement) {
                    $this->build_element_html($subelement);
                }
                unset($subelement);
            }
            else {
                $this->build_element_html($element);
            }
        }
        unset($element);

        // If a template is to be used, use it instead of a renderer
        if (!empty($this->data['template'])) {
            $form_tag = $this->get_form_tag();

            // $elements is a convenience variable that contains all of the form elements (minus fieldsets and 
            // hidden elements)
            $elements = array();
            foreach ($this->elementrefs as $element) {
                if ($element['type'] != 'hidden') {
                    $elements[$element['name']] = $element;
                }
            }

            // Hidden elements
            $this->include_plugin('element', 'hidden');
            $hidden_elements = '';
            foreach ($this->elementrefs as $element) {
                if ($element['type'] == 'hidden') {
                    $hidden_elements .= pieform_element_hidden($this, $element);
                }
            }
            $element = array(
                'type'  => 'hidden',
                'name'  => 'pieform_' . $this->get_name(),
                'value' => ''
            );
            $hidden_elements .= pieform_element_hidden($this, $element);

            ob_start();

            if ($this->get_property('ignoretemplatenotices')) {
                $old_level = error_reporting(E_ALL & ~E_NOTICE);
            }

            $templatepath = $this->get_property('templatedir');
            $templatepath = ($templatepath && substr($templatepath, -1) != '/') ? $templatepath . '/' : $templatepath;
            $templatepath .= $this->get_property('template');
            require($templatepath);

            if ($this->get_property('ignoretemplatenotices')) {
                error_reporting($old_level);
            }

            $result = ob_get_contents();
            ob_end_clean();
        }
        else {
            // No template being used - instead use a renderer
            if ($outputformtags) {
                $result = $this->get_form_tag() . "\n";
            }

            $this->include_plugin('renderer',  $this->data['renderer']);

            // Form header
            $function = 'pieform_renderer_' . $this->data['renderer'] . '_header';
            if (function_exists($function)) {
                $result .= $function();
            }

            // Render each element
            foreach ($this->data['elements'] as $name => $element) {
                if ($element['type'] != 'hidden') {
                    $result .= pieform_render_element($this, $element);
                }
            }

            // Form footer
            $function = 'pieform_renderer_' . $this->data['renderer'] . '_footer';
            if (function_exists($function)) {
                $result .= $function();
            }

            // Hidden elements
            $this->include_plugin('element', 'hidden');
            foreach ($this->elementrefs as $element) {
                if ($element['type'] == 'hidden') {
                    $result .= pieform_element_hidden($this, $element);
                }
            }
            $element = array(
                'type'  => 'hidden',
                'name'  => 'pieform_' . $this->name,
                'value' => ''
            );
            $result .= pieform_element_hidden($this, $element);
            if ($outputformtags) {
                $result .= "</form>\n";
            }
        }

        // Output the javascript to wire things up, but only if it is needed. 
        // The two cases where it is needed is when:
        // 1) The form is a JS form that hasn't been submitted yet. When the 
        // form has been submitted the javascript from the first page load is 
        // still active in the document
        // 2) The form is NOT a JS form, but has a presubmitcallback
        if ($outputformtags &&
            (($this->data['jsform'] && !$this->submitted)
             || (!$this->data['jsform'] && $this->data['presubmitcallback']))) {
            // Establish which buttons in the form are submit buttons. This is 
            // used to detect which button was pressed to cause the form 
            // submission
            $submitbuttons = array();
            foreach ($this->elementrefs as $element) {
                if (!empty($element['submitelement'])) {
                    // TODO: might have to deal with cancel elements here too
                    $submitbuttons[] = $element['name'];
                }
            }

            $data = json_encode(array(
                'name'                  => $this->name,
                'jsForm'                => $this->data['jsform'],
                'submitButtons'         => $submitbuttons,
                'preSubmitCallback'     => $this->data['presubmitcallback'],
                'jsSuccessCallback'     => $this->data['jssuccesscallback'],
                'jsErrorCallback'       => $this->data['jserrorcallback'],
                'globalJsErrorCallback' => $this->data['globaljserrorcallback'],
                'postSubmitCallback'    => $this->data['postsubmitcallback'],
                'newIframeOnSubmit'     => $this->data['newiframeonsubmit'],
            ));
            $result .= "<script type=\"text/javascript\">new Pieform($data);</script>\n";
        }

        return $result;
    }/*}}}*/

    /**
     * Given an element, gets the value for it from this form
     *
     * @param  array $element The element to get the value for
     * @return mixed          The element's value. <kbd>null</kbd> if no value
     *                        is available for the element.
     */
    public function get_value($element) {/*{{{*/
        $function = 'pieform_element_' . $element['type'] . '_get_value';
        if (function_exists($function)) {
            return $function($this, $element);
        }
        $global = ($this->data['method'] == 'get') ? $_GET : $_POST;
        // If the element is a submit element and has its value in the request, return it
        // Otherwise, we don't return the value if the form has been submitted, as they
        // aren't normally returned using a standard form.
        if (isset($element['value'])) {
            return $element['value'];
        }
        else if ($this->submitted && isset($global[$element['name']]) && $element['type'] != 'submit') {
            return $global[$element['name']];
        }
        else if (isset($element['defaultvalue'])) {
            return $element['defaultvalue'];
        }
        return null;
    }/*}}}*/

    /**
     * Retrieves a list of elements in the form.
     *
     * This flattens fieldsets, and ignores the actual fieldset elements
     *
     * @return array The elements of the form
     */ 
    public function get_elements() {/*{{{*/
        $elements = array();
        foreach ($this->data['elements'] as $name => $element) {
            if ($element['type'] == 'fieldset') {
                foreach ($element['elements'] as $subelement) {
                    $elements[] = $subelement;
                }
            }
            else {
                $elements[] = $element;
            }
        }
        return $elements;
    }/*}}}*/

    /**
     * Returns the element with the given name. Throws a PieformException if the
     * element cannot be found.
     *
     * Fieldset elements are ignored. This might change if a valid case for
     * needing them is found.
     *
     * @param  string $name     The name of the element to find
     * @return array            The element
     * @throws PieformException If the element could not be found
     */
    public function get_element($name) {/*{{{*/
        if (isset($this->elementrefs[$name])) {
            return $this->elementrefs[$name];
        }

        throw new PieformException('Element "' . $name . '" cannot be found');
    }/*}}}*/

    /**
     * Sends a message back to a form
     */
    public function reply($returncode, $message) {
        if ($this->submitted_by_js()) {
            $this->json_reply($returncode, $message);
        }

        $function = $this->get_property('replycallback');
        if (function_exists($function)) {
            call_user_func_array($function, array($returncode, $message));
        }
    }

    /**
     * Sends a message back to a jsform.
     *
     * The message can contain almost any data, although how it is used is up to 
     * the javascript callbacks.  The message must contain a return code (the 
     * first parameter of this method.
     *
     * - The return code of the result. Either one of the PIEFORM_OK, 
     *   PIEFORM_ERR or PIEFORM_CANCEL codes, or a custom error code at the 
     *   choice of the application using pieforms
     * - A message. This is just a string that can be used as a status message, 
     *   e.g. 'Form failed submission'
     * - HTML to replace the form with. By default, the form is built and used, 
     *   but for example, you could replace the form with a "thank you" message 
     *   after successful submission if you want
     */
    public function json_reply($returncode, $data=array(), $replacehtml=null) {/*{{{*/
        if (is_string($data)) {
            $data = array(
                'message' => $data,
            );
        }
        $data['returnCode'] = intval($returncode);
        if ($replacehtml === null) {
            $data['replaceHTML'] = $this->build();
        }
        else if (is_string($replacehtml)) {
            $data['replaceHTML'] = $replacehtml;
        }
        if (isset($this->hashedfields)) {
            $data['fieldnames'] = $this->hashedfields;
        }

        $result = json_encode($data);

        echo <<<EOF
<html><head><script type="text/javascript">function sendResult() { parent.pieformHandlers["{$this->name}"]($result); }</script></head><body onload="sendResult(); "></body></html>
EOF;
        exit;
    }/*}}}*/

    /**
     * Returns whether a field has an error marked on it.
     *
     * This method should be used in the custom validation functions, to see if
     * there is an error on an element before checking for any more validation.
     *
     * Example:
     *
     * <code>
     * if (!$form->get_error('name') && /* condition {@*}) {
     *     $form->set_error('name', 'error message');
     * }
     * </code>
     *
     * @param  string $name  The name of the element to check
     * @return bool          Whether the element has an error
     * @throws PieformException If the element could not be found
     */
    public function get_error($name) {/*{{{*/
        $element = $this->get_element($name);
        return isset($element['error']);
    }/*}}}*/

    /**
     * Marks a field has having an error.
     *
     * This method should be used to set an error on an element in a custom
     * validation function, if one has occured.
     *
     * @param string $name    The name of the element to set an error on
     * @param string $message The error message
     * @throws PieformException  If the element could not be found
     */
    public function set_error($name, $message) {/*{{{*/
        if (is_null($name) && !empty($message)) {
            $this->error = $message;
            return;
        }
        foreach ($this->data['elements'] as $key => &$element) {
            if ($element['type'] == 'fieldset') {
                foreach ($element['elements'] as &$subelement) {
                    if ($subelement['name'] == $name) {
                        $subelement['error'] = $message;
                        return;
                    }
                }
            }
            else {
                if ($key == $name) {
                    $element['error'] = $message;
                    return;
                }
            }
        }
        throw new PieformException('Element "' . $name . '" could not be found');
    }/*}}}*/

    /**
     * Checks if there are errors on any of the form elements.
     *
     * @return bool Whether there are errors with the form
     */
    public function has_errors() {/*{{{*/
        foreach ($this->elementrefs as $element) {
            if (isset($element['error'])) {
                return true;
            }
        }
        return isset($this->error);
    }/*}}}*/

    /**
     * Returns elements with errors on them
     *
     * @return array An array of elements with errors on them, the empty array
     *               in the result of no errors.
     */
    public function get_errors() {/*{{{*/
        $result = array();
        foreach ($this->elementrefs as $element) {
            if (isset($element['error'])) {
                $result[] = $element;
            }
        }
        return $result;
    }/*}}}*/

    /**
     * Makes an ID for an element.
     *
     * Element IDs are used for <label>s, so use this method to ensure that
     * an element gets an ID.
     *
     * The element's existing 'id' and 'name' attributes are checked first. If
     * they are not specified, a random ID is created
     *
     * @param array $element The element to make an ID for
     * @return string        The ID for the element
     */
    public function make_id($element) {/*{{{*/
        if (isset($element['id'])) {
            return self::hsc($element['id']);
        }
        if (isset($element['name'])) {
            return self::hsc($element['name']);
        }
        return 'a' . substr(md5(mt_rand()), 0, 4);
    }/*}}}*/

    /**
     * Makes a class for an element.
     *
     * Elements can have several classes set on them depending on their state.
     * The classes are useful for (among other things), styling elements
     * differently if they are in these states.
     *
     * Currently, the states an element can be in are 'required' and 'error'.
     *
     * @param array $element The element to make a class for
     * @return string        The class for an element
     */
    public function make_class($element) {/*{{{*/
        $classes = array();
        if (isset($element['class'])) {
            $classes[] = self::hsc($element['class']);
        }
        if (!empty($element['rules']['required'])) {
            $classes[] = 'required';
        }
        if (!empty($element['error'])) {
            $classes[] = 'error';
        }
        if ($this->data['elementclasses']) {
            $classes[] = $element['type'];
        }
        if (!empty($element['autoselect'])) {
            $classes[] = 'autoselect';
        }
        // Please make sure that 'autofocus' is the last class added in this
        // method. Otherwise, improve the logic for removing 'autofocus' from
        // the element class string in pieform_render_element
        if (!empty($element['autofocus'])) {
            $classes[] = 'autofocus';
        }
        return implode(' ', $classes);
    }/*}}}*/

    /**
     * Given an element, returns a string representing the basic attribute
     * list for the element.
     *
     * This EXCLUDES the "value" attribute, as various form elements set
     * their value in different ways.
     *
     * This allows each element to have most of the standard HTML attributes
     * that you can normally set on a form element.
     *
     * The attributes generated by this method will include (if set for the
     * element itself), are <kbd>accesskey, class, dir, id, lang, maxlength,
     * name, size, style</kbd> and <kbd>tabindex</kbd>.
     *
     * The <kbd>class</kbd> and <kbd>id</kbd> attributes are typically built
     * beforehand with {@link make_class} and {@link make_id} respectively.
     * The <kbd>maxlength</kbd> attribute is only set if the element has a
     * "maxlength" rule on it.
     *
     * @param array $element The element to make attributes for
     * @param array $exclude Any attributes to explicitly exclude from adding
     * @return string        The attributes for the element
     */
    public function element_attributes($element, $exclude=array()) {/*{{{*/
        static $attributes = array('accesskey', 'autocomplete', 'class', 'dir', 'id', 'lang', 'name', 'onclick', 'size', 'style', 'tabindex');
        $elementattributes = array_diff($attributes, $exclude);
        $result = '';
        foreach ($elementattributes as $attribute) {
            if (isset($element[$attribute]) && $element[$attribute] !== '') {
                if ($attribute == 'id') {
                    $element[$attribute] = $this->name . '_' . $element[$attribute];
                }
                $result .= ' ' . $attribute . '="' . self::hsc($element[$attribute]) . '"';
            }
        }

        if (isset($element['elementtitle'])) {
            $result .= ' title="' . self::hsc($element['elementtitle']) . '"';
        }

        if (!in_array('maxlength', $exclude) && isset($element['rules']['maxlength'])) {
            $result .= ' maxlength="' . intval($element['rules']['maxlength']) . '"';
        }

        foreach (array_diff(array('disabled', 'readonly'), $exclude) as $attribute) {
            if (!empty($element[$attribute])) {
                $result .= " $attribute=\"$attribute\"";
            }
        }
        
        return $result;
    }/*}}}*/

    /**
     * Includes a plugin file, checking any configured plugin directories.
     *
     * @param string $type The type of plugin to include: 'element', 'renderer' or 'rule'
     * @param string $name The name of the plugin to include
     * @throws PieformException If the given type or plugin could not be found
     */
    public function include_plugin($type, $name) {/*{{{*/
        if (!in_array($type, array('element', 'renderer', 'rule'))) {
            throw new PieformException("The type \"$type\" is not allowed for an include plugin");
        }

        if (!isset($name) || !preg_match('/^[a-z_][a-z0-9_]*$/', $name)) {
            throw new PieformException("The name \"$name\" is not valid (validity test: could you give a PHP function the name?)");
        }

        // Check the configured include paths if they are specified
        foreach ($this->data['configdirs'] as $directory) {
            $file = "$directory/{$type}s/$name.php";
            if (is_readable($file)) {
                include_once($file);
                return;
            }
        }

        // Check the default include path
        $file = dirname(__FILE__) . "/pieform/{$type}s/{$name}.php";
        if (is_readable($file)) {
            include_once($file);
            return;
        }

        throw new PieformException("Could not find $type \"$name\"");
    }/*}}}*/

    /**
     * Return an internationalised string based on the passed input key
     *
     * Returns English by default.
     *
     * @param string $plugin     The type of plugin (element, renderer, rule)
     * @param string $pluginname The name of the plugin to get the language
     *                           strings for
     * @param string $key        The language key to look up
     * @param array  $element    The element to get the string for. Elements
     *                           can specify there own i18n strings for rules
     * @return string            The internationalised string
     */
    public function i18n($plugin, $pluginname, $key, $element=null) {/*{{{*/
        if (!in_array($plugin, array('element', 'renderer', 'rule'))) {
            throw new PieformException("Invalid plugin name '$plugin'");
        }

        if (!isset($pluginname) || !preg_match('/^[a-z_][a-z0-9_]*$/', $pluginname)) {
            throw new PieformException("The pluginname \"$pluginname\" is not valid (validity test: could you give a PHP function the name?)");
        }

        if (!isset($key) || !preg_match('/^[a-z_][a-z0-9_]*$/', $key)) {
            throw new PieformException("The key \"$key\" is not valid (validity test: could you give a PHP function the name?)");
        }

        // Check the element itself for the language string
        if ($plugin == 'rule' && isset($element['rulei18n'][$key])) {
            return $element['rulei18n'][$key];
        }

        // Check to see if a default was configured for the form
        if ($plugin == 'rule' && isset($this->data['rulei18n'][$key])) {
            return $this->data['rulei18n'][$key];
        }

        return get_raw_string($plugin . '.' . $pluginname . '.' . $key, 'pieforms');

    }/*}}}*/

    /**
     * HTML-escapes the given value
     *
     * @param string $text The text to escape
     * @return string      The text, HTML escaped
     */
    public static function hsc($text) {/*{{{*/
        return htmlspecialchars($text, ENT_COMPAT, 'UTF-8');
    }/*}}}*/

    /**
     * Hook for giving information back to the developer
     *
     * @param string $message The message to give to the developer
     */
    public static function info($message) {/*{{{*/
        $function = 'pieform_info';
        if (function_exists($function)) {
            $function($message);
        }
        else {
            trigger_error($message, E_USER_NOTICE);
        }
    }/*}}}*/

    /**
     * Makes sure that the javascript callbacks for this form are valid javascript
     * function names.
     */
    private function validate_js_callbacks() {/*{{{*/
        foreach (array('presubmitcallback', 'postsubmitcallback', 'jssuccesscallback',
            'jserrorcallback', 'globaljserrorcallback') as $callback) {
            if ($this->data[$callback] != '' && !preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $this->data[$callback])) {
                throw new PieformException("'{$this->data[$callback]}' is not a valid javascript callback name for callback '$callback'");
            }
        }
    }/*}}}*/

    /**
     * Retrieves submitted values from the request for the elements of this form.
     *
     * This takes into account that some elements may not even have been set,
     * for example if they were check boxes that were not checked upon
     * submission.
     *
     * A value is returned for every element (except fieldsets of course). If
     * an element was not set, the value set is <kbd>null</kbd>.
     *
     * @return array The submitted values
     */
    private function get_submitted_values() {/*{{{*/
        $result = array();
        $global = ($this->data['method'] == 'get') ? $_GET : $_POST;
        foreach ($this->elementrefs as $name => $element) {
            if ($element['type'] != 'markup') {
                if (
                    (empty($element['submitelement']) && empty($element['cancelelement'])) ||
                    (
                        (!empty($element['submitelement']) || !empty($element['cancelelement']))
                        && isset($global[$element['name']])
                    )
                ) {
                    $result[$name] = $this->get_value($element);
                }
            }
        }
        return $result;
    }/*}}}*/

    /**
     * Performs simple validation based off the definition array.
     *
     * Rules can be added to <kbd>pieform/rules/</kbd> directory, and then
     * re-used in the 'rules' index of each element in the form definition
     * hash.
     *
     * More complicated validation is possible by defining an optional
     * callback with the name {$form->name}_validate. See the documentation for
     * more information.
     *
     * @param array $values The submitted values from the form
     */
    private function validate($values) {/*{{{*/
        // Call the overall validation function if it is available
        if (function_exists('pieform_validate')) {
            pieform_validate($this, $values);
        }

        // Perform rule validation
        foreach ($this->elementrefs as $name => $element) {
            if (isset($element['rules']) && is_array($element['rules'])) {
                foreach ($element['rules'] as $rule => $data) {
                    if (!$this->get_error($name)) {
                        // See if this element has a function that describes
                        // how this rule should apply to it
                        $function = 'pieform_element_' . $element['type'] . '_rule_' . $rule;
                        if (!function_exists($function)) {
                            // Try instead the default rule function
                            $function = 'pieform_rule_' . $rule;
                            if (!function_exists($function)) {
                                $this->include_plugin('rule', $rule);
                                if (!function_exists($function)) {
                                    throw new PieformException('No such form rule "' . $rule . '"');
                                }
                            }
                        }
                        if ($error = $function($this, $values[$name], $element, $data)) {
                            $this->set_error($name, $error);
                        }
                    }
                }
            }
        }

        if (isset($this->data['spam'])) {
            // make sure the user waited long enough but not too long before submitting the form
            $elapsed = time() - $values['__timestamp'];
            if ($elapsed < $this->data['spam']['mintime'] || $elapsed > $this->data['spam']['maxtime']) {
                $this->spamerror = true;
            }
            // make sure the real submit button was used. If it wasn't, it won't exist.
            else if (!isset($values['submit']) || isset($values['__invisiblesubmit'])) {
                $this->spamerror = true;
            }
            // make sure the invisible field is empty
            else if (!isset($values['__invisiblefield']) || $values['__invisiblefield'] != '') {
                $this->spamerror = true;
            }
        }

        // Then user specific validation if a function is available for that
        $function = $this->data['validatecallback'];
        if (is_callable($function)) {
            call_user_func_array($function, array($this, $values));
        }
    }/*}}}*/

    /**
     * Sets the 'autofocus' property on the first element encountered that has
     * an error on it
     */
    private function auto_focus_first_error() {/*{{{*/
        foreach ($this->data['elements'] as &$element) {
            if ($element['type'] == 'fieldset') {
                foreach ($element['elements'] as &$subelement) {
                    if (isset($subelement['error'])) {
                        $subelement['autofocus'] = true;
                        return;
                    }
                    unset($subelement['autofocus']);
                }
            }
            else {
                if (isset($element['error'])) {
                    $element['autofocus'] = true;
                    return;
                }
                unset($element['autofocus']);
            }
        }
    }/*}}}*/

    /**
     * Given an element, builds all of the HTML for it - for example, the label 
     * and the raw HTML of the element itself
     *
     * The element is passed by reference, and various properties are set 
     * directly on the element, namely:
     *
     * * 'html' - The element in its built, HTML form
     * * 'labelhtml' - The HTML for the element label
     * * 'helphtml' - The HTML for the help icon
     *
     * @param array &$element The element to build the HTML for
     */
    private function build_element_html(&$element) {/*{{{*/
        // Set ID and class for elements
        $element['id']    = $this->make_id($element);
        $element['class'] = $this->make_class($element);

        // If the element is pure markup, don't pass it to the renderer
        if ($element['type'] == 'markup') {
            return $element['value'] . "\n";
        }

        // Build the element html
        $function = 'pieform_element_' . $element['type'];
        $element['html'] = $function($this, $element);

        // Element title
        if (isset($element['title']) && $element['title'] !== '') {
            $title = (!empty($element['labelescaped'])) ? $element['title'] : self::hsc($element['title']);
            if (!empty($element['nolabel'])) {
                // Don't bother with a label for the element
                $element['labelhtml'] = $title;
            }
            else {
                $element['labelhtml'] = '<label for="' . $this->name . '_' . $element['id'] . '">' . $title . '</label>';
            }
            if ($this->get_property('requiredmarker') && !empty($element['rules']['required'])) {
                $element['labelhtml'] .= ' <span class="requiredmarker">*</span>';
            }
        }

        // Help icon
        if (!empty($element['help'])) {
            $function = $this->get_property('helpcallback');
            if (function_exists($function)) {
                $element['helphtml'] = $function($this, $element);
            } 
            else {
                $element['helphtml'] = '<span class="help"><a href="" title="' . Pieform::hsc($element['help']) . '" onclick="return false;">?</a></span>';
            }
        }
    }/*}}}*/

    /**
     * Returns the default values for pieform data.
     *
     * Used in the constructor when setting up the pieform.
     *
     * @return array
     * {@internal {PHP5 doesn't support private static const arrays, so this is a method}}
     */
    private static function get_pieform_defaults() {/*{{{*/
        return array(
            // The method used to submit the form, should always be 'get' or 'post'
            'method' => 'get',

            // The form target. The vast majority of the time this should be blank,
            // as the functions that handle the submit should be in the same script
            // as the form definition
            'action' => '',

            // The form elements
            'elements' => array(),

            // The form renderer (see the pieform/renderers directory)
            'renderer' => 'table',

            // The directory (relative to the include path) to search for templates
            'templatedir' => '',

            // Whether to ignore E_NOTICE messages in templates
            'ignoretemplatenotices' => true,

            // Whether to validate the form. Non validated forms have none of the
            // validate, success or error callbacks called on them
            'validate' => true,

            // Whether to process the submission of this form. The form will still
            // be validated. Handy if the code handling the submission is elsewhere
            'submit' => true,

            // The PHP callback called to validate the form. Optional
            'validatecallback' => '',

            // The PHP callback called to process the submission of the form.
            // Required, unless a success function is provided for each submit
            // button in the form
            'successcallback' => '',

            // The PHP callback called to handle replying to the form after 
            // either a success or fail. Optional
            'replycallback' => '',

            // The PHP callback called if there is any validation error. Optional
            'errorcallback' => '',

            // Whether this form should submit to a hidden iframe and use DOM
            // manipulation to insert error messages (faster than a normal submit,
            // supported in less browsers. Most modern browsers should be fine)
            'jsform' => false,

            // Whether the form will target a new hidden iframe every time it's
            // submitted.
            'newiframeonsubmit' => false,

            // The URL where pieforms.js and other related pieforms javascript 
            // files can be accessed. Best specified as an absolute path in 
            // pieform_configure()
            'jsdirectory' => '',

            // The javascript function called before submission of a form
            // (regardless of whether the form is a jsform)
            'presubmitcallback' => '',

            // The javascript function called after submission of a form. As non-js
            // forms will trigger a page load on submit, this has no effect for them. 
            'postsubmitcallback' => '',

            // The javascript function called if the form submission was successful
            'jssuccesscallback' => '',

            // The javascript function called if the form submission was unsuccessful
            'jserrorcallback' => '',

            // The javascript function called if the form submission returned an
            // unknown error code
            'globaljserrorcallback' => '',

            // The message to pass back as a reason for the form submission failing
            // if the form is a jsform. This can be used by your application however
            // you choose.
            'jserrormessage' => '',

            // Whether this form can be cancelled, regardless of the presence of
            // 'cancel' buttons or form inputs mischeviously named as to behave
            // like cancel buttons
            'iscancellable' => true,

            // Whether Pieforms should die after calling a submit function. Generally
            // this is a good idea, as it forces the user to reply to the form
            // submission. However, there are occasions where you might want to let
            // it continue, so this behaviour can be turned off
            'dieaftersubmit' => true,

            // Whether to auto-focus either the first field (if the value is true,
            // or the named field (if the value is a string) when the form is
            // displayed. If this has any value other than false and the form has
            // been submitted with an error, the first field with an error will
            // be focussed.
            'autofocus'  => false,

            // The directories to search for additional elements, renderers and
            // rules
            'configdirs' => array(),

            // The language to use for any form strings, such as those found in
            // rules.
            'language'   => 'en.utf8',

            // Any overriding language strings for rules
            'rulei18n'   => array(),

            // The tabindex for the form (managed automatically by Pieforms)
            'tabindex'   => false,

            // Whether to add a class of the type of the element to each element
            'elementclasses' => false,

            // Whether to add * markers after each required field
            'requiredmarker' => false,

            // Whether to show the description as well as the error message 
            // when displaying errors
            'showdescriptiononerror' => true,
        );
    }/*}}}*/

    private function hash_fieldnames() {/*{{{*/
        // Mess with field names to make it harder for bots to fill in the form
        $ip = self::get_ip();
        $secret = $this->data['spam']['secret'];
        $this->hashedfields = array();
        foreach ($this->data['spam']['hash'] as $name) {
            // prefix the hash with an underscore to ensure it is always a valid pieforms element name
            $this->hashedfields[$name] = '_' . sha1($name . $this->time . $ip . $secret);
        }
    }/*}}}*/

    private static function get_ip() {/*{{{*/
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return $_SERVER['REMOTE_ADDR'];
    }/*}}}*/

    public function spam_error() {/*{{{*/
        return $this->spamerror;
    }/*}}}*/

}/*}}}*/


/**
 * Renders an element, and returns the result.
 *
 * This function looks in <kbd>pieform/renderers</kbd> for available overall form
 * renderers, and in <kbd>pieform/elements</kbd> for renderers for each form
 * element.
 *
 * If any of the renderers are not available, this function will throw a
 * PieformException.
 *
 * {@internal This is separate so that child element types can nest other
 * elements inside them (like the fieldset element does for example).}}
 *
 * NOTE: This function is SCHEDULED FOR REMOVAL. Nicer ways of getting built 
 * elements are available
 *
 * @param Pieform  $form    The form to render the element for
 * @param array    $element The element to render
 * @return string           The rendered element
 */
function pieform_render_element(Pieform $form, $element) {/*{{{*/
    // If the element is pure markup, don't pass it to the renderer
    if ($element['type'] == 'markup') {
        return $element['value'] . "\n";
    }

    // Make sure that the function to render the element type is available
    $rendererfunction = 'pieform_renderer_' . $form->get_property('renderer');
    if (!function_exists($rendererfunction)) {
        throw new PieformException('No such form renderer function: "' . $rendererfunction . '"');
    }

    // Remove the 'autofocus' class, because we only want it on the form input
    // itself, not the wrapping HTML
    if (isset($element['class'])) {
        $element['class'] = preg_replace('/\s?autofocus/', '', $element['class']);
    }

    // Render fieldsets now
    if ($element['type'] == 'fieldset') {
        $element['html'] = pieform_element_fieldset($form, $element);
    }

    return $rendererfunction($form, $element);
}/*}}}*/

/**
 * Returns an array of HTML elements to be placed in the <head> section of the 
 * page.
 *
 * This works for all forms that have been built at the time this function is 
 * called - so call this function after all forms are built!
 *
 * @return array
 */
function pieform_get_headdata() {/*{{{*/
    $htmlelements = array();
    foreach ($GLOBALS['_PIEFORM_REGISTRY'] as $form) {
        foreach ($form->get_elements() as $element) {
            $function = 'pieform_element_' . $element['type'] . '_get_headdata';
            if (function_exists($function)) {
                $elems = $function($element);
                $htmlelements = array_merge($htmlelements, $elems);
            }
        }
    }
    // Fieldsets don't appear in $form->get_elements(), so get their headdata
    if (!empty($GLOBALS['_PIEFORM_FIELDSETS'])) {
        $htmlelements = array_merge($htmlelements, pieform_element_fieldset_get_headdata());
    }

    // TODO: jsdirectory should be independent of ANY form
    if ($GLOBALS['_PIEFORM_REGISTRY']) {
        array_unshift($htmlelements, '<script type="text/javascript" src="'
            . Pieform::hsc($form->get_property('jsdirectory')) . 'pieforms.js"></script>');
        array_unshift($htmlelements, '<script type="text/javascript">pieformPath = "'
            . Pieform::hsc($form->get_property('jsdirectory')) . '";</script>');
    }

    return array_unique($htmlelements);
}/*}}}*/
