<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
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
 * @package    mahara
 * @subpackage blocktype
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();


/**
 * Base blocktype plugin class
 * @abstract
 */
abstract class PluginBlocktype extends Plugin {

    public static function extra_xmldb_substitution($xml) {
        return str_replace(
        '<!-- PLUGINTYPE_INSTALLED_EXTRAFIELDS -->', 
        ' <FIELD NAME="artefactplugin" TYPE="char" LENGTH="255" NOTNULL="false" />',
        str_replace(
            '<!-- PLUGINTYPE_INSTALLED_EXTRAKEYS -->', 
            '<KEY NAME="artefactpluginfk" TYPE="foreign" FIELDS="artefactplugin" REFTABLE="artefact_installed" REFFIELDS="name" />',
            $xml
            )
        );
    }

    /** 
     * override this to return true if the blocktype 
     * can only reasonably be placed once in a view
    */
    public static function single_only() {
        return false;
    }

    public static abstract function get_title();

    /**
     * Allows block types to override the instance's title.
     *
     * For example: My Views, My Groups, My Friends, Wall
     */
    public static function override_instance_title(BlockInstance $instance) {
    }

    public static abstract function get_description();

    public static abstract function get_categories();

    public static function get_viewtypes() {
        static $viewtypes = null;

        if (is_null($viewtypes)) {
            $viewtypes = get_column('view_type', 'type');
            if (!$viewtypes) {
                $viewtypes = array();
            }
        }

        return $viewtypes;
    }

    public static abstract function render_instance(BlockInstance $instance, $editing=false);

    /**
    * This function must be implemented in the subclass if it requires
    * javascript. It returns an array of javascript files, either local
    * or remote.
    */
    public static function get_instance_javascript(BlockInstance $instance) {
        return array();
    }

    /**
     * If this blocktype contains artefacts, and uses the artefactchooser 
     * Pieform element to choose them, this method must return the definition 
     * for the element.
     *
     * This is used in view/artefactchooser.json.php to build pagination for 
     * the element.
     *
     * The element returned MUST have the name key set to either 'artefactid' 
     * or 'artefactids', depending on whether 'selectone' is true or false.
     *
     * The element must also have the 'blocktype' key set to the name of the 
     * blocktype that the form is for.
     *
     * @param mixed $default The default value for the element
     */
    public static abstract function artefactchooser_element($default=null);

    /**
    * subclasses can override this if they need to do something a bit special
    * eg more than just what the BlockInstance->delete function does.
    * 
    * @param BlockInstance $instance
    */
    public static function delete_instance(BlockInstance $instance) { }

    /**
    * This function must be implemented in the subclass if it has config
    */
    public static function instance_config_form(BlockInstance $instance) {
        throw new SystemException(get_string('blocktypemissingconfigform', 'error', $instance->get('blocktype')));
    }

    /**
    * Thus function must be implemented in the subclass is it has an
    * instance config form that requires javascript. It returns an
    * array of javascript files, either local or remote.
    */
    public static function get_instance_config_javascript(BlockInstance $instance) {
        return array();
    }

    /**
     * Blocktype plugins can implement this to perform custom pieform 
     * validation, should they need it
     */
    public static function instance_config_validate(Pieform $form, $values) { }

    /**
    * Most blocktype plugins will attach to artefacts.
    * They should implement this function to keep a list of which ones. The 
    * result of this method is used to populate the view_artefact table, and 
    * thus decide whether an artefact is in a view for the purposes of access. 
    * See {@link artefact_in_view} for more information about this.
    *
    * Note that it should just handle top level artefacts.
    * The cache rebuilder will figure out the children.
    *
    * @return array ids of artefacts in this block instance
    */
    public static function get_artefacts(BlockInstance $instance) {
        $configdata = $instance->get('configdata');
        if (isset($configdata['artefactids']) && is_array($configdata['artefactids'])) {
            return $configdata['artefactids'];
        }
        if (!empty($configdata['artefactid'])) {
            return array($configdata['artefactid']);
        }
        return false;
    }

    /** 
    * this is different to has_config - has_config is plugin wide config settings
    * this is specific to this TYPE of plugin and relates to whether individual instances
    * can be configured within a view
    */
    public static function has_instance_config() {
        return false;
    }

    public static function category_title_from_name($name) {
        $title = get_string('blocktypecategory.'. $name, 'view');
        if (strpos($title, '[[') !== 0) {
            return $title;
        }
        // else we're an artefact
        return get_string('pluginname', 'artefact.' . $name);
    }

    public static function get_blocktypes_for_category($category, View $view) {
        $sql = 'SELECT bti.name, bti.artefactplugin
            FROM {blocktype_installed} bti 
            JOIN {blocktype_installed_category} btic ON btic.blocktype = bti.name
            JOIN {blocktype_installed_viewtype} btiv ON btiv.blocktype = bti.name
            WHERE btic.category = ? AND bti.active = 1 AND btiv.viewtype = ?
            ORDER BY bti.name';
        if (!$bts = get_records_sql_array($sql, array($category, $view->get('type')))) {
            return false;
        }

        $blocktypes = array();

        if (function_exists('local_get_allowed_blocktypes')) {
            $localallowed = local_get_allowed_blocktypes($category, $view);
        }

        foreach ($bts as $bt) {
            $namespaced = blocktype_single_to_namespaced($bt->name, $bt->artefactplugin);
            if (isset($localallowed) && is_array($localallowed) && !in_array($namespaced, $localallowed)) {
                continue;
            }
            safe_require('blocktype', $namespaced); 
            // Note for later: this is Blocktype::allowed_in_view, which 
            // returns true if the blocktype should be insertable into the 
            // given view.
            // e.g. for blogs it returns false when view owner is not set, 
            // because blogs can't be inserted into group views.
            // This could be different from whether a blockinstance is allowed 
            // to be copied into a View (see the other place in this file where 
            // allowed_in_view is called)
            //
            // Note also that if we want templates to be able to have all 
            // blocktypes, we can add $view->get('template') here as part of 
            // the condition, and also to View::addblocktype and 
            // View::get_category_data
            if (call_static_method(generate_class_name('blocktype', $namespaced), 'allowed_in_view', $view)) {
                $blocktypes[] = array(
                    'name'           => $bt->name,
                    'title'          => call_static_method(generate_class_name('blocktype', $namespaced), 'get_title'),
                    'description'    => call_static_method(generate_class_name('blocktype', $namespaced), 'get_description'),
                    'singleonly'     => call_static_method(generate_class_name('blocktype', $namespaced), 'single_only'),
                    'artefactplugin' => $bt->artefactplugin,
                    'thumbnail_path' => get_config('wwwroot') . 'thumb.php?type=blocktype&bt=' . $bt->name . ((!empty($bt->artefactplugin)) ? '&ap=' . $bt->artefactplugin : ''),
                );
            }
        }
        return $blocktypes;
    }

    /**
     * Takes config data for an existing blockinstance of this class and rewrites it so 
     * it can be used to configure a block instance being put in a new view
     *
     * This is used at view copy time, to give blocktypes the chance to change 
     * the configuration for a block based on aspects about the new view - for 
     * example, who will own it.
     *
     * As an example - when the profile information blocktype is copied, we 
     * want it so that all the fields that were configured previously are 
     * pointing to the new owner's versions of those fields.
     *
     * The base method clears out any artefact IDs that are set.
     *
     * @param View $view The view that the blocktype will be placed into (e.g. 
     *                   the View being created as a result of the copy)
     * @param array $configdata The configuration data for the old blocktype
     * @return array            The new configuration data.
     */
    public static function rewrite_blockinstance_config(View $view, $configdata) {
        if (isset($configdata['artefactid'])) {
            $configdata['artefactid'] = null;
        }
        if (isset($configdata['artefactids'])) {
            $configdata['artefactids'] = array();
        }
        return $configdata;
    }

    /*
     * The copy_type of a block affects how it should be copied when its view gets copied.
     * nocopy:    The block doesn't appear in the new view at all.
     * shallow:   A new block of the same type is created in the new view with a configuration as specified by the
     *            rewrite_blockinstance_config method
     * reference: Block configuration is copied as-is.  If the block contains artefacts, the original artefact ids are
     *            retained in the new block's configuration even though they may have a different owner from the view.
     * full:      All artefacts referenced by the block are copied to the new owner's portfolio, and ids in the new
     *            block are updated to point to the copied artefacts.
     *
     * If the old owner and the new owner are the same, reference is always used.
     * If a block contains no artefacts, reference and full are equivalent.
     */
    public static function default_copy_type() {
        return 'shallow';
    }

    /**
     * Whether this blocktype is allowed in the given View.
     *
     * Some blocktypes may wish to limit whether they're allowed in a View if, 
     * for example, they make no sense when the view is owned by a certain type 
     * of owner.
     *
     * For example, the 'profile information' blocktype makes no sense in a 
     * group View.
     *
     * Of course, blocktypes could implement stranger rules - e.g. only allow 
     * when the view has 'ponies' in its description (BTW: such blocktypes 
     * would be totally awesome).
     *
     * @param View     The View to check
     * @return boolean Whether blocks of this blocktype are allowed in the 
     *                 given view.
     */
    public static function allowed_in_view(View $view) {
        return true;
    }

    /**
     * Given a block instance, returns a hash with enough information so that 
     * we could reconstruct it if given this information again.
     *
     * Import/Export routines can serialise this information appropriately, and 
     * unserialise it on the way back in, where it is passed to {@link 
     * import_create_blockinstance()} for creation of a new block instance.
     *
     * @param BlockInstance $bi The block instance to export config for
     * @return array The configuration required to import the block again later
     */
    public static function export_blockinstance_config(BlockInstance $bi) {
        $configdata = $bi->get('configdata');

        if (is_array($configdata)) {
            // Unset a bunch of stuff that we don't want to export. These fields 
            // weren't being cleaned up before blockinstances were being saved 
            // previously, so we make sure they're not going to be in the result
            unset($configdata['blockconfig']);
            unset($configdata['id']);
            unset($configdata['change']);
            unset($configdata['new']);
        }
        else {
            $configdata = array();
        }

        return $configdata;
    }

    /**
     * Exports configuration data the format required for Leap2A export.
     *
     * This format is XML, and as the exporter can't generate complicated XML
     * structures, we have to json_encode all the values.
     *
     * Furthermore, because of how json_encode and json_decode "work" in PHP,
     * we make double sure that our values are all inside arrays. See the
     * craziness that is PHP bugs 38680 and 46518 for more information.
     *
     * The array is assumed to be there when importing, so if you're overriding
     * this method and don't wrap any values in an array, you can expect import
     * to growl at you and not import your config.
     *
     * @param BlockInstance $bi The block instance to export config for
     * @return array The configuration required to import the block again later
     */
    public static function export_blockinstance_config_leap(BlockInstance $bi) {
        $configdata = call_static_method(generate_class_name('blocktype', $bi->get('blocktype')), 'export_blockinstance_config', $bi);
        foreach ($configdata as $key => &$value) {
            $value = json_encode(array($value));
        }
        return $configdata;
    }

    /**
     * Creates a block instance from a given configuration.
     *
     * The configuration is whatever was generated by {@link
     * export_blockinstance_config()}. This method doesn't have to worry about
     * setting the block title, or the position in the View.
     *
     * @param array $biconfig   The config to use to create the blockinstance
     * @param array $viewconfig The configuration for the view being imported
     * @return BlockInstance The new block instance
     */
    public static function import_create_blockinstance(array $biconfig, array $viewconfig) {
        $bi = new BlockInstance(0,
            array(
                'blocktype'  => $biconfig['type'],
                'configdata' => $biconfig['config'],
            )
        );

        return $bi;
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
        return false;
    }

    /**
     * Defines if the title should be linked to an artefact view (if possible)
     * when viewing the block
     *
     * This method should be overridden in the child class, if a title link
     * is not desired.
     *
     * @return boolean whether to link the title or not
     */
    public static function has_title_link() {
        return true;
    }

}

abstract class SystemBlockType extends PluginBlockType {

    public static function get_artefacts(BlockInstance $instance) {
        return array();
    }

    public final static function artefactchooser_element($default=null) {
    }

}


class BlockInstance {

    private $id;
    private $blocktype;
    private $artefactplugin;
    private $title;
    private $configdata = array();
    private $dirty;
    private $view;
    private $view_obj;
    private $column;
    private $order;
    private $canmoveleft;
    private $canmoveright;
    private $canmoveup;
    private $canmovedown;
    private $maxorderincolumn;
    private $artefacts = array();
    private $temp = array();

    public function __construct($id=0, $data=null) {
         if (!empty($id)) {
            if (empty($data)) {
                if (!$data = get_record('block_instance','id',$id)) {
                    // TODO: 1) doesn't need get string here if this is the 
                    // only place the exception is used - can be done in the 
                    // class itself. 2) String needs to be defined, or taken 
                    // from lang/*/view.php where there is already one for it
                    throw new BlockInstanceNotFoundException(get_string('blockinstancenotfound', 'error', $id));
                }
            }
            $this->id = $id;
        }
        else {
            $this->dirty = true;
        }
        if (empty($data)) {
            $data = array();
        }
        foreach ((array)$data as $field => $value) {
            if (property_exists($this, $field)) {
                $this->{$field} = $value;
            }
        }
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
        if (strpos($field, 'canmove') === 0) {
            return $this->can_move(substr($field, strlen('canmove'))); // needs to be calculated.
        }
        if ($field == 'maxorderincolumn') {
            // only fetch this when we're asked, it's a db query.
            if (empty($this->maxorderincolumn)) {
                $this->maxorderincolumn = get_field(
                    'block_instance', 
                    'max("order")', 
                    'view', $this->view, 'column', $this->column);
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

    public function instance_config_store(Pieform $form, $values) {
        global $SESSION;

        // Destroy form values we don't care about
        unset($values['sesskey']);
        unset($values['blockinstance']);
        unset($values['action_configureblockinstance_id_' . $this->get('id')]);
        unset($values['blockconfig']);
        unset($values['id']);
        unset($values['change']);
        unset($values['new']);

        if (is_callable(array(generate_class_name('blocktype', $this->get('blocktype')), 'instance_config_save'))) {
            $values = call_static_method(generate_class_name('blocktype', $this->get('blocktype')), 'instance_config_save', $values);
        }

        $title = (isset($values['title'])) ? $values['title'] : '';
        unset($values['title']);
        $this->set('configdata', $values);
        $this->set('title', $title);

        try {
            $rendered = $this->render_editing(false, false, $form->submitted_by_js());
        }
        catch (HTMLPurifier_Exception $e) {
            $message = get_string('blockconfigurationrenderingerror', 'view') . ' ' . $e->getMessage();
            $form->reply(PIEFORM_ERR, array('message' => $message));
        }

        $this->commit();

        $result = array(
            'error'   => false,
            'message' => get_string('blockinstanceconfiguredsuccessfully', 'view'),
            'data'    => $rendered,
            'blockid' => $this->get('id'),
            'viewid'  => $this->get('view'),
        );

        $redirect = '/view/blocks.php?id=' . $this->get('view');
        if (param_boolean('new', false)) {
            $redirect .= '&new=1';
        }
        if ($category = param_alpha('c', '')) {
            $redirect .= '&c='. $category;
        }
        $result['goto'] = $redirect;
        $form->reply(PIEFORM_OK, $result);
    }

    public function get_title() {
        $blocktypeclass = generate_class_name('blocktype', $this->get('blocktype'));
        if ($override = call_static_method($blocktypeclass, 'override_instance_title', $this)) {
            return $override;
        }
        if ($title = $this->get('title') and $title != '') {
            return $title;
        }
        if (method_exists($blocktypeclass, 'get_instance_title')) {
            return call_static_method($blocktypeclass, 'get_instance_title', $this);
        }
        return '';
    }

    /**
     * Builds the HTML for the block, inserting the blocktype content at the 
     * appropriate place
     *
     * @param bool $configure Whether to render the block instance in configure 
     *                        mode
     * @return array Array with two keys: 'html' for raw html, 'javascript' for
     *               javascript to run
     */
    public function render_editing($configure=false, $new=false, $jsreply=false) {
        safe_require('blocktype', $this->get('blocktype'));
        $js = '';
        $movecontrols = array();

        $blocktypeclass = generate_class_name('blocktype', $this->get('blocktype'));
        $title = $this->get_title();

        if ($configure) {
            list($content, $js) = array_values($this->build_configure_form($new));
        }
        else {
            try {
                $content = call_static_method(generate_class_name('blocktype', $this->get('blocktype')), 'render_instance', $this, true);
            }
            catch (ArtefactNotFoundException $e) {
                // Whoops - where did the image go? There is possibly a bug 
                // somewhere else that meant that this blockinstance wasn't 
                // told that the image was previously deleted. But the block 
                // instance is not allowed to treat this as a failure
                log_debug('Artefact not found when rendering a block instance. '
                    . 'There might be a bug with deleting artefacts of this type? '
                    . 'Original error follows:');
                log_debug($e->getMessage());
                $content = '';
            }

            if (!defined('JSON') && !$jsreply) {
                if ($this->get('canmoveleft')) {
                    $movecontrols[] = array(
                        'column' => $this->get('column') - 1,
                        'order'  => $this->get('order'),
                        'title'  => $title == '' ? get_string('movethisblockleft', 'view') : get_string('moveblockleft', 'view', "'$title'"),
                        'arrow'  => '&larr;',
                        'dir'    => 'left',
                    );
                }
                if ($this->get('canmovedown')) {
                    $movecontrols[] = array(
                        'column' => $this->get('column'),
                        'order'  => $this->get('order') + 1,
                        'title'  => $title == '' ? get_string('movethisblockdown', 'view') : get_string('moveblockdown', 'view', "'$title'"),
                        'arrow'  => '&darr;',
                        'dir'    => 'down',
                    );
                }
                if ($this->get('canmoveup')) {
                    $movecontrols[] = array(
                        'column' => $this->get('column'),
                        'order'  => $this->get('order') - 1,
                        'title'  => $title == '' ? get_string('movethisblockup', 'view') : get_string('moveblockup', 'view', "'$title'"),
                        'arrow'  => '&uarr;',
                        'dir'    => 'up',
                    );
                }
                if ($this->get('canmoveright')) {
                    $movecontrols[] = array(
                        'column' => $this->get('column') + 1,
                        'order'  => $this->get('order'),
                        'title'  => $title == '' ? get_string('movethisblockright', 'view') : get_string('moveblockright', 'view', "'$title'"),
                        'arrow'  => '&rarr;',
                        'dir'    => 'right',
                    );
                }
            }
        }

        $configtitle = $title == '' ? call_static_method($blocktypeclass, 'get_title') : $title;

        $smarty = smarty_core();

        $smarty->assign('id',     $this->get('id'));
        $smarty->assign('viewid', $this->get('view'));
        $smarty->assign('title',  $title);
        $smarty->assign('column', $this->get('column'));
        $smarty->assign('order',  $this->get('order'));

        $smarty->assign('movecontrols', $movecontrols);
        $smarty->assign('configurable', call_static_method($blocktypeclass, 'has_instance_config'));
        $smarty->assign('configure', $configure); // Used by the javascript to rewrite the block, wider.
        $smarty->assign('configtitle',  $configtitle);
        $smarty->assign('content', $content);
        $smarty->assign('javascript', defined('JSON'));
        $smarty->assign('strnotitle', get_string('notitle', 'view'));
        $smarty->assign('strconfigtitletext', $title == '' ? get_string('configurethisblock', 'view') : get_string('configureblock', 'view', "'$title'"));
        $smarty->assign('strremovetitletext', $title == '' ? get_string('removethisblock', 'view') : get_string('removeblock', 'view', "'$title'"));

        return array('html' => $smarty->fetch('view/blocktypecontainerediting.tpl'), 'javascript' => $js);
    }

    public function render_viewing() {

        safe_require('blocktype', $this->get('blocktype'));
        $classname = generate_class_name('blocktype', $this->get('blocktype'));
        try {
            $content = call_static_method($classname, 'render_instance', $this);
        }
        catch (ArtefactNotFoundException $e) {
            // Whoops - where did the image go? There is possibly a bug
            // somewhere else that meant that this blockinstance wasn't
            // told that the image was previously deleted. But the block
            // instance is not allowed to treat this as a failure
            log_debug('Artefact not found when rendering a block instance. '
                . 'There might be a bug with deleting artefacts of this type? '
                . 'Original error follows:');
            log_debug($e->getMessage());
            $content = '';
        }

        $smarty = smarty_core();
        $smarty->assign('id',     $this->get('id'));
        $smarty->assign('blocktype', $this->get('blocktype'));
        // hide the title if required and no content is present
        if (call_static_method($classname, 'hide_title_on_empty_content')
            && !trim($content)) {
            return;
        }
        $smarty->assign('title', $this->get_title());

        // If this block is for just one artefact, we set the title of the
        // block to be a link to view more information about that artefact
        $configdata = $this->get('configdata');
        if (!empty($configdata['artefactid'])) {
            if (call_static_method($classname, 'has_title_link')) {
                $smarty->assign('viewartefacturl', get_config('wwwroot') . 'view/artefact.php?artefact='
                    . $configdata['artefactid'] . '&view=' . $this->get('view'));
            }
        }

        if (method_exists($classname, 'feed_url')) {
            $smarty->assign('feedlink', call_static_method($classname, 'feed_url', $this));
        }
        $smarty->assign('content', $content);

        return $smarty->fetch('view/blocktypecontainerviewing.tpl');
    }

    /**
     * Builds the configuration pieform for this blockinstance
     *
     * @return array Array with two keys: 'html' for raw html, 'javascript' for
     *               javascript to run
     */
    public function build_configure_form($new=false) {

        static $renderedform;
        if (!empty($renderedform)) {
            return $renderedform;
        }

        safe_require('blocktype', $this->get('blocktype'));
        $blocktypeclass = generate_class_name('blocktype', $this->get('blocktype'));
        $elements = call_static_method($blocktypeclass, 'instance_config_form', $this, $this->get_view()->get('template'));

        // @todo: If we know the title is going to be overridden in override_instance_title(),
        // hide or disable the title field in the form.  Currently not a problem, because the
        // blocktypes that override the instance title don't have configurable instances.
        // Maybe just remove or simplify title in the elements list below and make all the
        // blocktype classes pass the title element in their instance_config_form().

        // Block types may specify a method to generate a default title for a block
        $hasdefault = method_exists($blocktypeclass, 'get_instance_title');

        $title = $this->get('title');

        $elements = array_merge(
            array(
                'title' => array(
                    'type' => 'text',
                    'title' => get_string('blocktitle', 'view'),
                    'description' => $hasdefault ? get_string('defaulttitledescription', 'blocktype.' . blocktype_name_to_namespaced($this->get('blocktype'))) : null,
                    'defaultvalue' => $title,
                    'rules' => array('maxlength' => 255),
                    'hidewhenempty' => $hasdefault,
                    'expandtext'    => get_string('setblocktitle'),
                ),
                'blockconfig' => array(
                    'type'  => 'hidden',
                    'value' => $this->get('id'),
                ),
                'id' => array(
                    'type'  => 'hidden',
                    'value' => $this->get('view'),
                ),
                'change' => array(
                    'type'  => 'hidden',
                    'value' => 1,
                ),
                'new' => array(
                    'type'  => 'hidden',
                    'value' => $new,
                ),
            ),
            $elements
        );

        if ($new) {
            $cancel = get_string('remove');
            $elements['removeoncancel'] = array('type' => 'hidden', 'value' => 1);
            $elements['sure']           = array('type' => 'hidden', 'value' => 1);
        }
        else {
            $cancel = get_string('cancel');
        }

        // Add submit/cancel buttons
        $elements['action_configureblockinstance_id_' . $this->get('id')] = array(
            'type' => 'submitcancel',
            'value' => array(get_string('save'), $cancel),
            'goto' => View::make_base_url(),
        );

        $configdirs = array(get_config('libroot') . 'form/');
        if ($this->get('artefactplugin')) {
            $configdirs[] = get_config('docroot') . 'artefact/' . $this->get('artefactplugin') . '/form/';
        }

        $form = array(
            'name' => 'instconf',
            'renderer' => 'maharatable',
            'validatecallback' => array(generate_class_name('blocktype', $this->get('blocktype')), 'instance_config_validate'),
            'successcallback'  => array($this, 'instance_config_store'),
            'jsform' => true,
            'jssuccesscallback' => 'blockConfigSuccess',
            'jserrorcallback'   => 'blockConfigError',
            'elements' => $elements,
            'viewgroup' => $this->get_view()->get('group'),
            'group' => $this->get_view()->get('group'),
            'viewinstitution' => $this->get_view()->get('institution'),
            'institution' => $this->get_view()->get('institution'),
            'configdirs' => $configdirs,
            'plugintype' => 'blocktype',
            'pluginname' => $this->get('blocktype'),
        );

        if (param_variable('action_acsearch_id_' . $this->get('id'), false)) {
            $form['validate'] = false;
        }

        require_once('pieforms/pieform.php');
        $pieform = new Pieform($form);

        if ($pieform->is_submitted()) {
            global $SESSION;
            $SESSION->add_error_msg(get_string('errorprocessingform'));
        }

        $html = $pieform->build();
        // We probably need a new version of $pieform->build() that separates out the js
        // Temporary evil hack:
        if (preg_match('/<script type="text\/javascript">(new Pieform\(.*\);)<\/script>/', $html, $matches)) {
            $js = "var pf_{$form['name']} = " . $matches[1] . "pf_{$form['name']}.init();";
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

        $configjs = call_static_method($blocktypeclass, 'get_instance_config_javascript', $this);
        foreach($configjs as &$jsfile) {
            if(strpos($jsfile, 'http://') === false) {
                if($this->artefactplugin) {
                    $jsfile = 'artefact/' . $this->artefactplugin . '/blocktype/' .
                        $this->blocktype . '/' . $jsfile;
                }
                else {
                    $jsfile = 'blocktype/' . $this->blocktype . '/' . $jsfile;
                }
                $jsfile = '$j.getScript("' . get_config('wwwroot') . $jsfile . '");';
            }
            else {
                $jsfile = '$j.getScript("' . $jsfile . '");';
            }
        }
        $js .= implode('', $configjs);

        $renderedform = array('html' => $html, 'javascript' => $js);
        return $renderedform;
    }

    public function commit() {
        if (empty($this->dirty)) {
            return;
        }
        $fordb = new StdClass;
        foreach (get_object_vars($this) as $k => $v) {
            // The configdata is initially fetched from the database in string 
            // form. Calls to get() will convert it to an array on the fly. We 
            // ensure that it is a string again here
            if ($k == 'configdata' && is_array($v)) {
                $fordb->{$k} = serialize($v);
            }
            else {
                $fordb->{$k} = $v;
            }
        }
        if (empty($this->id)) {
            $this->id = insert_record('block_instance', $fordb, 'id', true);
        }
        else {
            update_record('block_instance', $fordb, 'id');
        }

        $this->rebuild_artefact_list();

        // Tell stuff about this
        handle_event('blockinstancecommit', $this);

        $this->dirty = false;
    }

    public function rebuild_artefact_list() {
        db_begin();

        // Remember what was in this block before saving, and always allow those artefacts to remain
        // in it, regardless of the user's current permissions.
        $old = get_records_assoc('view_artefact', 'block', $this->id, '', 'artefact, id');

        delete_records('view_artefact', 'block', $this->id);
        safe_require('blocktype', $this->get('blocktype'));
        if (!$artefacts = call_static_method(
            generate_class_name('blocktype', $this->get('blocktype')),
            'get_artefacts', $this)) {
            db_commit();
            return true;
        }

        foreach ($artefacts as $key => $id) {
            if (!$id || intval($id) == 0) {
                log_warn("get_artefacts returned an invalid artefact ID for block instance $this->id (" . $this->get('blocktype') . ")");
                unset($artefacts[$key]);
            }
        }

        if (count($artefacts) == 0) {
            db_commit();
            return true;
        }

        // Get list of allowed artefacts
        require_once('view.php');
        $searchdata = array(
            'extraselect'          => array(array('fieldname' => 'id', 'type' => 'int', 'values' => $artefacts)),
            'userartefactsallowed' => true,  // If this is a group view, the user can add personally owned artefacts
        );
        list($allowed, $count) = View::get_artefactchooser_artefacts(
            $searchdata,
            $this->get_view()->get('owner'),
            $this->get_view()->get('group'),
            $this->get_view()->get('institution'),
            true
        );

        $va = new StdClass;
        $va->view = $this->get('view');
        $va->block = $this->id;

        foreach ($artefacts as $id) {
            if (isset($allowed[$id]) || isset($old[$id])) {
                $va->artefact = $id;
                insert_record('view_artefact', $va);
            }
        }

        db_commit();
    }

    /**
     * @return View the view object this block instance is in
     */
    public function get_view() {
        if (empty($this->view_obj)) {
            require_once('view.php');
            $this->view_obj = new View($this->get('view'));
        }
        return $this->view_obj;
    }

    public function can_move($direction) {
        switch ($direction) {
            case 'left':
                return ($this->column > 1);
            case 'right':
                return ($this->column < $this->get_view()->get('numcolumns'));
            case 'up':
                return ($this->order > 1);
                break;
            case 'down':
                return ($this->order < $this->get('maxorderincolumn'));
            default:
                throw new InvalidArgumentException(get_string('invaliddirection', 'error', $direction));
        }
    }

    public function delete() {
        if (empty($this->id)) {
            $this->dirty = false;
            return;
        }
        db_begin();
        safe_require('blocktype', $this->get('blocktype'));
        call_static_method(generate_class_name('blocktype', $this->get('blocktype')), 'delete_instance', $this);
        
        delete_records('view_artefact', 'block', $this->id);
        delete_records('block_instance', 'id', $this->id);
        db_commit();

        $this->dirty = false;
    }

    /**
     * Deletes an artefact from the blockinstance.
     *
     * This is implemented in the baseclass by looking for arrays in the block 
     * instance configuration called 'artefactid' or 'artefactids', and 
     * removing the one we were looking to delete. This means two things:
     *
     * 1) In order to not have to re-implement this method for new blocktypes, 
     *    your blocktype should ALWAYS store its artefact IDs in the config data 
     *    value 'artefactid' or in the array 'artefactids'
     * 2) The block must ALWAYS continue to work even when artefacts are 
     *    removed from it
     *
     * Don't override this method without doing the right thing in bulk_delete_artefacts too.
     */
    final public function delete_artefact($artefact) {
        $configdata = $this->get('configdata');
        $changed = false;

        if (isset($configdata['artefactid'])) {
            if ($configdata['artefactid'] == $artefact) {
                $configdata['artefactid'] = null;
            }
            $changed = true;
        }

        if (isset($configdata['artefactids']) && is_array($configdata['artefactids'])) {
            $configdata['artefactids'] = array_values(array_diff($configdata['artefactids'], array($artefact)));
            $changed = true;
        }

        if ($changed) {
            $this->set('configdata', $configdata);

            // We would commit here but we don't want to rebuild the artefact list
            set_field('block_instance', 'configdata', serialize($configdata), 'id', $this->get('id'));
        }
    }

    /**
     * Deletes artefacts from the blockinstances given in $records.
     * $records should be an array of stdclass objects, each containing
     * a blockid, an artefactid, and the block's configdata
     */
    public static function bulk_delete_artefacts($records) {
        if (empty($records)) {
            return;
        }
        $blocklist = array();
        foreach ($records as $record) {
            if (isset($blocklist[$record->block])) {
                $blocklist[$record->block]->artefacts[] = $record->artefact;
            }
            else {
                $blocklist[$record->block] = (object) array(
                    'artefacts' => array($record->artefact),
                    'configdata' => unserialize($record->configdata),
                );
            }
        }
        foreach ($blocklist as $blockid => $blockdata) {
            if (isset($blockdata->configdata['artefactid'])) {
                if ($change = $blockdata->configdata['artefactid'] == $blockdata->artefacts[0]) {
                    $blockdata->configdata['artefactid'] = null;
                }
            }
            else if (isset($blockdata->configdata['artefactids'])) {
                $blockdata->configdata['artefactids'] = array_values(array_diff($blockdata->configdata['artefactids'], $blockdata->artefacts));
                $change = true;
            }
            if ($change) {
                set_field('block_instance', 'configdata', serialize($blockdata->configdata), 'id', $blockid);
            }
        }
    }

    /** 
     * Get an artefact instance, checking republish permissions
     */
    public function get_artefact_instance($id) {
        if (isset($this->artefacts[$id])) {
            return $this->artefacts[$id];
        }

        require_once(get_config('docroot') . 'artefact/lib.php');
        $a = artefact_instance_from_id($id);
        $viewowner = $this->get_view()->get('owner');
        $group = $a->get('group');
        if ($viewowner && $group) {
            // Only group artefacts can have artefact_access_role & artefact_access_usr records
            if (!count_records_sql("SELECT COUNT(ar.can_republish) FROM {artefact_access_role} ar
                INNER JOIN {group_member} g ON ar.role = g.role
                WHERE ar.artefact = ? AND g.member = ? AND g.group = ? AND ar.can_republish = 1", array($a->get('id'), $viewowner, $group))
                and !record_exists('artefact_access_usr', 'usr', $viewowner, 'artefact', $a->get('id'), 'can_republish', 1)) {
                throw new ArtefactNotFoundException(get_string('artefactnotpublishable', 'mahara', $id, $this->get_view()->get('id')));
            }
        }

        return $this->artefacts[$id] = $a;
    }


    /**
     * Builds a new block instance as a copy of this one, taking into account 
     * the Views being copied from and to.
     *
     * Blocktypes can decide whether they want to be copied to the new View. The 
     * return value of this method should indicate whether the blocktype was 
     * copied or not.
     *
     * @param View $view The view that this new blockinstance is being created for
     * @param View $template The view that this (the old) blockinstance comes from
     * @param array $artefactcopies Correspondence between original artefact IDs and IDs of copies
     * @return boolean Whether a new blockinstance was made or not.
     */
    public function copy(View $view, View $template, &$artefactcopies) {
        $blocktypeclass = generate_class_name('blocktype', $this->get('blocktype'));

        $configdata = $this->get('configdata');
        if (isset($configdata['copytype'])) {
            $copytype = $configdata['copytype'];
        }
        else {
            $copytype = call_static_method($blocktypeclass, 'default_copy_type');
        }

        $viewowner = $view->ownership();
        $templateowner = $template->ownership();
        $sameowner = ($viewowner['type'] == $templateowner['type'] && $viewowner['id'] == $templateowner['id']);

        // Check to see if the block is allowed to be copied into the new View
        //
        // Note for later: this is Blockinstance->allowed_in_view. This 
        // determines whether this blockinstance should be copied into a view. 
        // This could be a different question from BlockType::allowed_in_view! 
        // But for now they use the same method.
        if (!call_static_method($blocktypeclass, 'allowed_in_view', $view)) {
            return false;
        }
        if ($copytype == 'nocopy' && !$sameowner) {
            return false;
        }

        $newblock = new BlockInstance(0, array(
            'blocktype'  => $this->get('blocktype'),
            'title'      => $this->get('title'),
            'view'       => $view->get('id'),
            'column'     => $this->get('column'),
            'order'      => $this->get('order'),
        ));

        if ($sameowner || $copytype == 'reference') {
            $newblock->set('configdata', $configdata);
            $newblock->commit();
            return true;
        }
        $artefactids = get_column('view_artefact', 'artefact', 'block', $this->get('id'));
        if (!empty($artefactids)
            && $copytype == 'full') {
            // Copy artefacts & put the new artefact ids into the new block.
            // Artefacts may have children (defined using the parent column of the artefact table) and attachments (currently
            // only for blogposts).  If we copy an artefact we must copy all its descendents & attachments too.

            $descendants = artefact_get_descendants($artefactids);

            // We need the artefact instance before we can get its attachments
            $tocopy = array();
            $attachmentlists = array();
            foreach ($descendants as $d) {
                if (!isset($artefactcopies[$d])) {
                    $tocopy[$d] = artefact_instance_from_id($d);
                    // Get attachments.
                    $attachmentlists[$d] = $tocopy[$d]->attachment_id_list();
                    foreach ($attachmentlists[$d] as $a) {
                        if (!isset($artefactcopies[$a]) && !isset($tocopy[$a])) {
                            $tocopy[$a] = artefact_instance_from_id($a);
                        }
                    }
                }
            }

            // Copy all the artefacts we haven't copied yet
            foreach ($tocopy as $aid => $a) {
                // Save the id of the original artefact's parent
                $artefactcopies[$aid] = (object) array('oldid' => $aid, 'oldparent' => $a->get('parent'));
                if (!empty($attachmentlists[$aid])) {
                    $artefactcopies[$aid]->oldattachments = $attachmentlists[$aid];
                }
                $artefactcopies[$aid]->newid = $a->copy_for_new_owner($view->get('owner'), $view->get('group'), $view->get('institution'));
            }

            // Record new artefact ids in the new block
            if (isset($configdata['artefactid'])) {
                $configdata['artefactid'] = $artefactcopies[$configdata['artefactid']]->newid;
            }
            else {
                foreach ($configdata['artefactids'] as &$oldid) {
                    $oldid = $artefactcopies[$oldid]->newid;
                }
            }
        }
        else {
            $configdata = call_static_method($blocktypeclass, 'rewrite_blockinstance_config', $view, $configdata);
        }
        $newblock->set('configdata', $configdata);
        $newblock->commit();
        return true;
    }

    public function get_data($key, $id) {
        if (!isset($this->temp[$key][$id])) {
            $blocktypeclass = generate_class_name('blocktype', $this->get('blocktype'));
            if (!isset($this->temp[$key])) {
                $this->temp[$key] = array();
            }
            $this->temp[$key][$id] = call_static_method($blocktypeclass, 'get_instance_' . $key, $id);
        }
        return $this->temp[$key][$id];
    }
}
