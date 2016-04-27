<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-annotation
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined ('INTERNAL') || die();

class PluginBlocktypeAnnotation extends MaharaCoreBlocktype {
    public static function single_only() {
        return false;
    }

    public static function get_title() {
        return get_string('title', 'blocktype.annotation/annotation');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.annotation/annotation');
    }

    public static function get_categories() {
        return array('general' => 14500);
    }

    public static function get_viewtypes() {
        return array('portfolio');
    }

    public static function has_title_link() {
        return false;  // true; // need to do more work on aretfact/artefact.php before this can be switched on.
    }

    public static function override_instance_title() {
        return get_string('Annotation', 'artefact.annotation');
    }

    public static function allowed_in_view(View $view) {
        // Annotations don't make sense in groups?
        return $view->get('group') == null;
    }

    /**
     * defines if the title should be shown if there is no content in the block
     *
     * If the title of the block should be hidden when there is no content,
     * override the the function in the blocktype class.
     *
     * @return boolean  whether the title of the block should be shown or not
     */
    public static function hide_title_on_empty_content() {
        return true;
    }

    /**
     * Returns a list of artefact IDs that are in this blockinstance.
     *
     * People may embed artefacts as images etc. They show up as links to the
     * download script, which isn't much to go on, but should be enough for us
     * to detect that the artefacts are therefore 'in' this blocktype.
     */
    public static function get_artefacts(BlockInstance $instance) {
        $configdata = $instance->get('configdata');
        $artefacts = array();
        if (isset($configdata['artefactid'])) {
            $artefacts[] = $configdata['artefactid'];

            // Add all artefacts found in the text
            $text = $instance->get_artefact_instance($configdata['artefactid'])->get('description');
            $artefacts = array_unique(array_merge($artefacts, artefact_get_references_in_html($text)));

            // Get all the feedback on this annotation
            // to retrieve all the artefacts found in their text
            // and to include the feedback as part of the view_artefact.
            // Please note that images owned by other users that are place on feedback
            // will not be part of the view_artefact because the owner of the
            // annotation does not own the image being placed on the feedback.
            // Therefore, when exported as Leap2A, these images will not come through.
            $sql = "SELECT a.id, a.description
                    FROM {artefact} a
                    INNER JOIN {artefact_annotation_feedback} af ON a.id = af.artefact
                    WHERE af.onannotation = ?";
            // Keep a list of the feedback ids.
            $artefactfeedback = array();
            if ($feedback = get_records_sql_array($sql, array($configdata['artefactid']))) {
                foreach ($feedback as $f) {
                    // Include the feedback artefact.
                    $artefactfeedback[] = $f->id;
                    // Include any artefacts found in its text.
                    // The BlockInstance::rebuild_artefact_list() will sort out the ownership.
                    $artefacts = array_unique(array_merge($artefacts, artefact_get_references_in_html($f->description)));
                }
                // Now merge the feedback artefacts as well.
                $artefacts = array_unique(array_merge($artefacts, $artefactfeedback));
            }
        }
        return $artefacts;
    }

    /**
     * Indicates whether this block can be loaded by Ajax after the page is done. This
     * improves page-load times by allowing blocks to be rendered in parallel instead
     * of in serial.
     *
     * You might want to disable this for:
     * - Blocks with particularly finicky Javascript contents
     * - Blocks that need to write to the session (the Ajax loader uses the session in read-only)
     * - Blocks that won't take long to render (static content, external content)
     *
     * @return boolean
     */
    public static function should_ajaxify() {
        // No, don't ajaxify this block. TinyMCE has issues.
        return false;
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        $smarty = smarty_core();
        $artefactid = '';
        $text = '';
        $feedbackcount = 0;
        $instance->set('artefactplugin', 'annotation');

        $configdata = $instance->get('configdata');
        if (!empty($configdata['artefactid'])) {
            safe_require('artefact', 'file');
            $artefactid = $configdata['artefactid'];
            $artefact = $instance->get_artefact_instance($artefactid);
            $viewid = $instance->get('view');
            $text = $artefact->get('description');
            require_once(get_config('docroot') . 'lib/view.php');
            $view = new View($viewid);
            list($feedbackcount, $annotationfeedback) = ArtefactTypeAnnotationfeedback::get_annotation_feedback_for_view($artefact, $view, $instance->get('id'), true, $editing);
            $smarty->assign('annotationfeedback', $annotationfeedback);
        }
        $smarty->assign('text', $text);
        $smarty->assign('artefactid', $artefactid);
        $smarty->assign('annotationfeedbackcount', $feedbackcount);
        $html = $smarty->fetch('blocktype:annotation:annotation.tpl');

        return $html;
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form(BlockInstance $instance) {
        global $USER;

        $instance->set('artefactplugin', 'annotation');
        // Get the saved configs in the artefact
        $configdata = $instance->get('configdata');

        if (!$height = get_config('blockeditorheight')) {
            $cfheight = param_integer('cfheight', 0);
            $height = $cfheight ? $cfheight * 0.7 : 150;
        }

        // Default annotation text.
        $text = '';
        $tags = '';
        $artefactid = '';
        $readonly = false;
        $textreadonly = false;
        $view = $instance->get_view();

        if (!empty($configdata['artefactid'])) {
            $artefactid = $configdata['artefactid'];
            try {
                $artefact = $instance->get_artefact_instance($artefactid);
                // Get the annotation record -> to get the artefact it's linked to.
                $annotation = new ArtefactTypeAnnotation($artefactid);
                // Get the total annotation feedback inserted so far by anyone.
                $totalannotationfeedback = ArtefactTypeAnnotationfeedback::count_annotation_feedback($artefactid, array($view->get('id')), array($annotation->get('artefact')));

                $readonly = $artefact->get('owner') !== $view->get('owner')
                    || $artefact->get('group') !== $view->get('group')
                    || $artefact->get('institution') !== $view->get('institution')
                    || $artefact->get('locked')
                    || !$USER->can_edit_artefact($artefact);

                if (isset($totalannotationfeedback[$view->get('id')])) {
                    $textreadonly = $totalannotationfeedback[$view->get('id')]->total > 0;
                }

                $text = $artefact->get('description');
                $tags = $artefact->get('tags');
            }
            catch (ArtefactNotFoundException $e) {
                unset($artefactid);
            }
        }

        $elements = array(
            'text' => array(
                'type' => ($textreadonly ? 'html' : 'wysiwyg'),
                'class' => '',
                'title' => get_string('Annotation', 'artefact.annotation'),
                'width' => '100%',
                'height' => $height . 'px',
                'defaultvalue' => $text,
                'rules' => array('maxlength' => 65536),
            ),
            'annotationreadonlymsg' => array(
                'type' => 'html',
                'class' => 'message info' . ($textreadonly ? '' : ' hidden'),
                'value' => get_string('annotationreadonlymessage', 'blocktype.annotation/annotation'),
                'help' => true,
            ),
            'allowfeedback' => array(
                'type'         => 'switchbox',
                'title'        => get_string('allowannotationfeedback', 'artefact.annotation'),
                'defaultvalue' => (!empty($artefact) ? $artefact->get('allowcomments') : 1),
            ),
            'tags' => array(
                'type' => 'tags',
                'class' => $readonly ? 'hidden' : '',
                'width' => '100%',
                'title' => get_string('tags'),
                'description' => get_string('tagsdescprofile'),
                'defaultvalue' => $tags,
            ),
            'tagsreadonly' => array(
                'type' => 'html',
                'class' => $readonly ? '' : 'hidden',
                'width' => '100%',
                'title' => get_string('tags'),
                'value' => '<div id="instconf_tagsreadonly_display">' . (is_array($tags) ? hsc(join(', ', $tags)) : '') . '</div>',
            ),
        );

        if ($textreadonly) {
            // The annotation is displayed as html, need to populate its value.
            $elements['text']['value'] = $text;
        }
        return $elements;
    }

    public static function delete_instance(BlockInstance $instance) {
        $configdata = $instance->get('configdata');
        if (!empty($configdata)) {
            $artefactid = $configdata['artefactid'];
            if (!empty($artefactid) && $artefactid) {
                // Delete the annotation and all its feedback.
                safe_require('artefact', 'annotation');
                $annotation = new ArtefactTypeAnnotation($artefactid);
                $annotation->delete();
            }
        }
    }

    public static function instance_config_save($values, $instance) {

        require_once('embeddedimage.php');
        safe_require('artefact', 'annotation');

        $data = array();
        $view = $instance->get_view();
        $configdata = $instance->get('configdata');
        foreach (array('owner', 'group', 'institution') as $f) {
            $data[$f] = $view->get($f);
        }

        // The title will always be Annotation.
        $title = get_string('Annotation', 'artefact.annotation');
        $data['title'] = $title;
        $values['title'] = $title;
        if (empty($configdata['artefactid'])) {
            // This is a new annotation.
            $artefact = new ArtefactTypeAnnotation(0, $data);
        }
        else {
            // The user is editing the annotation.
            $artefact = new ArtefactTypeAnnotation($configdata['artefactid']);
        }
        $artefact->set('title', $title);
        $artefact->set('description', $values['text']);
        $artefact->set('allowcomments', (!empty($values['allowfeedback']) ? $values['allowfeedback'] : 0));
        $artefact->set('tags', $values['tags']);
        $artefact->set('view', $view->get('id'));
        $artefact->commit();

        // Now fix up the text in case there were any embedded images.
        // Do this after saving because we may not have an artefactid yet.
        $newdescription = EmbeddedImage::prepare_embedded_images($values['text'], 'annotation', $artefact->get('id'), $view->get('group'));

        if ($newdescription !== false && $newdescription !== $values['text']) {
            $updatedartefact = new stdClass();
            $updatedartefact->id = $artefact->get('id');
            $updatedartefact->description = $newdescription;
            update_record('artefact', $updatedartefact, 'id');
        }

        $values['artefactid'] = $artefact->get('id');
        $instance->save_artefact_instance($artefact);

        unset($values['text']);
        unset($values['allowfeedback']);
        unset($values['annotationreadonlymsg']);

        // Pass back a list of any other blocks that need to be rendered
        // due to this change.
        $values['_redrawblocks'] = array_unique(get_column(
            'view_artefact', 'block',
            'artefact', $values['artefactid'],
            'view', $instance->get('view')
        ));

        return $values;
    }

    public static function default_copy_type() {
        return 'full';
    }

    public static function get_instance_javascript(BlockInstance $bi) {
        return array(
            array(
                'file' => 'js/annotation.js'
            )
        );
    }

    public static function jsstrings() {
        return array(
            'mahara' => array('Close')
        );
    }

    public static function postinst($fromversion) {
        if ($fromversion == 0) {
            set_field('blocktype_installed', 'active', 0, 'artefactplugin', 'annotation');
        }
    }
}
