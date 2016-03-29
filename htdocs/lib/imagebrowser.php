<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Mike Kelly UAL <m.f.kelly@arts.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

/**
 * Class for building the TinyMCE imagebrowser plugin interface
 */
class ImageBrowser {

    private $artefactplugin;
    private $title;
    private $view;
    private $view_obj;
    private $group;
    private $institution;
    private $post;
    private $blocktype;
    private $selected;
    private $configdata = array();

    public function __construct($data) {
        if (empty($data)) {
            $data = array();
        }
        foreach ((array)$data as $field => $value) {
            if (property_exists($this, $field)) {
                $this->{$field} = $value;
            }
        }
        $this->blocktype = 'image';
        $this->artefactplugin = blocktype_artefactplugin($this->blocktype);
    }

    public function get($field) {
        if (!property_exists($this, $field)) {
            throw new InvalidArgumentException("Field $field wasn't found in class " . get_class($this));
        }
        if ($field == 'configdata') {
            // make sure we unserialise it
            if (!is_array($this->configdata)) {
                $this->configdata = unserialize($this->configdata);
            }
        }
        return $this->{$field};
    }

    public function set($field, $value) {
        if (property_exists($this, $field)) {
            if ($field == 'configdata') {
                $value = serialize($value);
            }
            if ($this->{$field} !== $value) {
                // only set it to dirty if it's changed
                $this->dirty = true;
                $this->{$field} = $value;
            }
            return true;
        }
        throw new ParamOutOfRangeException("Field $field wasn't found in class " . get_class($this));
    }

    /**
     * Builds the HTML for the image browser
     *
     * @return array Array with two keys: 'html' for raw html, 'javascript' for
     *               javascript to run
     */
    public function render_image_browser() {
        $title = get_string('imagebrowsertitle');
        $description = get_string('imagebrowserdescription');
        list($content, $js) = array_values($this->build_image_browser_form());
        $smarty = smarty_core();
        $smarty->assign('title',  $title);
        $smarty->assign('description',  $description);
        $smarty->assign('content', $content);
        $smarty->assign('configurable', true);
        $smarty->assign('configure', true); // Used by the javascript to rewrite the block, wider.
        $smarty->assign('javascript', defined('JSON'));
        return array('html' => $smarty->fetch('view/imagebrowser.tpl'), 'javascript' => $js);
    }

    /**
     * Builds the configuration pieform for the image browser
     *
     * @return array Array with two keys: 'html' for raw html, 'javascript' for
     *               javascript to run
     */
    public function build_image_browser_form() {

        require_once ('view.php');
        static $renderedform;
        if (!empty($renderedform)) {
            return $renderedform;
        }

        safe_require('artefact', 'file');
        $this->set('artefactplugin', 'file');

        $elements['url']           =  array(
                                    'type' => 'text',
                                    'title' => get_string('url'),
                                    'size' => 50,
                                    );
        $selected = !empty($this->selected) ? array($this->selected) : null;
        $elements['artefactfieldset'] = array(
                'type'         => 'fieldset',
                'collapsible'  => true,
                'collapsed'    => true,
                'legend'       => get_string('image'),
                'class'        => 'select-file with-formgroup',
                'elements'     => array(
                    'artefactid' => self::config_filebrowser_element($this, $selected)
                )
            );
        $configdata = $this->get('configdata');
        $elements['sure']          = array('type' => 'hidden', 'value' => 1);
        // use these to determine which space to display to upload files to
        $elements['post']          = array('type' => 'hidden', 'value' => $this->post);
        $elements['group']         = array('type' => 'hidden', 'value' => $this->group);
        $elements['institution']   = array('type' => 'hidden', 'value' => $this->institution);
        $elements['view']          = array('type' => 'hidden', 'value' => $this->view);

        // tinymce specific elements
        $alignoptions = array(
            'none' => '--',
            'top'      => 'Top',
            'middle'   => 'Middle',
            'bottom'   => 'Bottom',
            'left'    => 'Left',
            'right'   => 'Right'
        );
        $elements['toggleformatting'] = array(
            'type' => 'fieldset',
            'collapsible' => true,
            'collapsed' => true,
            'legend' => get_string('imageformattingoptions'),
            'class' => 'last',
            'elements' => array(
                'width' => array(
                    'type' => 'text',
                    'title' => get_string ( 'width' ),
                    'size' => 3,
                    'rules' => array (
                        'maxlength' => 4
                    )
                ),
                'vspace' => array (
                    'type' => 'text',
                    'title' => get_string ( 'vspace' ),
                    'size' => 3,
                    'rules' => array (
                            'maxlength' => 2
                    )
                ),
                'hspace' => array (
                    'type' => 'text',
                    'title' => get_string ( 'hspace' ),
                    'size' => 3,
                    'rules' => array (
                            'maxlength' => 2
                    )
                ),
                'border' => array (
                    'type' => 'text',
                    'title' => get_string ( 'border' ),
                    'size' => 3,
                    'rules' => array (
                            'maxlength' => 2
                    )
                ),
                'align' => array (
                    'defaultvalue' => 'none',
                    'type' => 'select',
                    'title' => get_string ( 'alignment' ),
                    'options' => $alignoptions
                ),
                'style' => array (
                    'type' => 'text',
                    'title' => get_string ( 'style' ),
                    'size' => 50
                )
            ),
        );
        $wwwroot = get_config('wwwroot');
        $goto = "";
        if ($this->view) {
            $goto = $wwwroot. 'view/blocks.php' . View::make_base_url();
            // editing forum topic
        } else if ($this->post) {
            $goto = $wwwroot . "interaction/forum/edittopic.php?id=" . $this->post;
        } else if ($this->group) {
            // editing forum itself
            $goto = $wwwroot . "interaction/edit.php?id=" . $this->group;
        }

        // Add submit/cancel buttons
        // goto should not be used by those with javascript - cancel is handled by js function which simply removes the image browser
        $elements['action_submitimage'] = array(
                        'type' => 'submitcancel',
                        'class' => 'btn-default',
                        'value' => array(get_string('submit'), get_string('cancel')),
                        'goto' => $goto,
        );

        $configdirs = array(get_config('libroot') . 'form/');
        if ($this->get('artefactplugin')) {
            $configdirs[] = get_config('docroot') . 'artefact/' . $this->get('artefactplugin') . '/form/';
        }

        $form = array(
                        'name' => 'imgbrowserconf',
                        'action' => get_config('wwwroot') . 'json/imagebrowser.json.php',
                        'renderer' => 'div',
                        'validatecallback' => array($this, 'config_validate'),
                        'successcallback'  => array($this, 'config_success'),
                        'jsform' => true,
                        'jssuccesscallback' => 'imageBrowserConfigSuccess',
                        'jserrorcallback'   => 'imageBrowserConfigError',
                        'elements' => $elements,
                        'viewgroup' => $this->get('view') ? $this->get_view()->get('group') : null,
                        'group' => $this->get('group'),
                        'viewinstitution' => $this->get('view') ? $this->get_view()->get('institution') : null,
                        'institution' => $this->get('institution'),
                        'configdirs' => $configdirs,
                        'plugintype' => 'blocktype',
                        'pluginname' => $this->get('blocktype'),
        );

        $pieform = pieform_instance($form);

        if ($pieform->is_submitted()) {
            global $SESSION;
            $SESSION->add_error_msg(get_string('errorprocessingform'));
        }

        $html = $pieform->build();
        // We probably need a new version of $pieform->build() that separates out the js
        // Temporary evil hack:
        if (preg_match('/<script type="(text|application)\/javascript">(new Pieform\(.*\);)<\/script>/', $html, $matches)) {
            $js = "var pf_{$form['name']} = " . $matches[2] . "pf_{$form['name']}.init();";
        }
        else {
            $js = '';
        }

        // We need to load any javascript required for the pieform. We do this
        // by checking for an api function that has been added especially for
        // the purpose, but that is not part of Pieforms. Maybe one day later
        // it will be though
        // $js = '';
        foreach ($elements as $key => $element) {
            $element['name'] = $key;
            $function = 'pieform_element_' . $element['type'] . '_views_js';
            if (is_callable($function)) {
                $js .= call_user_func_array($function, array($pieform, $element));
            }
        }

        $renderedform = array('html' => $html, 'javascript' => $js);
        return $renderedform;
    }

    /*
     * Build the artefactid element in the imagebrowser form
     */
    public function config_filebrowser_element(&$instance, $default=array()) {
        global $USER;
        $resizeonuploaduserdefault = $USER->get_account_preference('resizeonuploaduserdefault');
        $currenttab = array('type' => 'user', 'id' => $USER->get('id'));

        if ($this->institution) {
            $currenttab = array('type' => 'institution', 'id' => $this->institution);
        }
        if ($this->group) {
            $currenttab = array('type' => 'group', 'id' => $this->group);
        }
        $element = array(
                'name'         => 'artefactid',
                'type'         => 'filebrowser',
                'folder'       => (int) param_variable('folder', 0),
                'highlight'    => null,
                'browse'       => true,
                'page'         => get_config('wwwroot') . 'json/imagebrowser.json.php',
                'config'       => array(
                        'upload'          => true,
                        'resizeonuploaduseroption' => get_config_plugin('artefact', 'file', 'resizeonuploaduseroption'),
                        'resizeonuploaduserdefault' => $resizeonuploaduserdefault,
                        'uploadagreement' => get_config_plugin('artefact', 'file', 'uploadagreement'),
                        'createfolder'    => false,
                        'edit'            => false,
                        'tag'             => true,
                        'select'          => true,
                        'alwaysopen'      => true,
                        'publishing'      => true,
                        'selectone'       => true,
                        'selectmodal'     => true,
                        'showbrowsertoggle' => true,
                        'showlicensetoggle' => true
                ),
                'tabs'         => $currenttab,
                'defaultvalue' => $default,
                'selectlistcallback' => 'artefact_get_records_by_id',
                'filters' => array(
                        'artefacttype'    => array('image', 'profileicon'),
                        ),
        );
        return $element;
    }

    /**
     * @return View the view object this block instance is in
     */
    public function get_view() {
        if (empty($this->view_obj)) {
            require_once('view.php');
            $this->view_obj = new View($this->get('view'));
            // stop view being committed - we just want to pass a file url to tinymce
            $this->view_obj->set('dirty', false);
        }
        return $this->view_obj;
    }

    /**
     * Returns javascript to grab & eval javascript from files on the web
     *
     * @param array $jsfiles Each element of $jsfiles is either a url, a local filename,
     *                       or an array of the form
     *                       array(
     *                           'file'   => string   // url or local js filename
     *                           'initjs' => string   // js to be executed once the file's
     *                                                // contents have been loaded
     *                       )
     *
     * @return string
     */
    public function get_get_javascript_javascript($jsfiles) {
        $js = '';
        foreach ($jsfiles as $jsfile) {

            $file = (is_array($jsfile) && isset($jsfile['file'])) ? $jsfile['file'] : $jsfile;

            if (stripos($file, 'http://') === false && stripos($file, 'https://') === false) {
                $file = 'blocktype/' . $this->blocktype . '/' . $file;
                if ($this->artefactplugin) {
                    $file = 'artefact/' . $this->artefactplugin . '/' . $file;
                }
                $file = get_config('wwwroot') . $file;
            }

            $js .= '$j.getScript("' . $file . '"';
            if (is_array($jsfile) && !empty($jsfile['initjs'])) {
                // Pass success callback to getScript
                $js .= ', function(data) {' . $jsfile['initjs'] . '}';
            }
            $js .= ");\n";
        }
        return $js;
    }

    public static function config_success(Pieform $form, $values) {
        $result = array();
        $form->json_reply(PIEFORM_OK, $result, false);
    }

    public static function config_validate(Pieform $form, $values) {
        return;
    }

}
