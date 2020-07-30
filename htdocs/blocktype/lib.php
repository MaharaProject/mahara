<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();


/**
 * Helper interface to hold IPluginBlocktype's abstract static methods
 */
interface IPluginBlocktype {
    public static function get_title();

    public static function get_description();

    /**
     * Should be an array of blocktype categories that this block should be included in,
     * for determining how it shows up in the page editor's pallette of blocks.
     * See the function get_blocktype_categories() in lib/upgrade.php for the full list.
     *
     * A block can belong to multiple categories.
     *
     * The special category "shortcut" will make the blocktype show up on the top of the
     * block pallette instead of in a category.
     *
     * Blocktypes can have a sortorder in each category, that determines how they are
     * ordered in the category. To give a sortorder, put the category as the array key,
     * and the sortorder as the array value, like so:
     *
     * return array(
     *     'shortcut' => 1000,
     *     'general'  => 500,
     * );
     *
     * If no sortorder is provided, the blocktype's sortorder will default to 100,000.
     * Core blocktypes should have sortorders separated by 1,000 to give space for 3rd-party
     * blocks in between.
     *
     * Blocktypess with the same sortorder are sorted by blocktype name.
     *
     * @return array
     */
    public static function get_categories();

    public static function render_instance(BlockInstance $instance, $editing=false, $versioning=false);
}

/**
 * Base blocktype plugin class
 * @abstract
 */
abstract class PluginBlocktype extends Plugin implements IPluginBlocktype {

    /**
     * Default sortorder for a blocktype that has no sortorder defined for a
     * particular blocktype category that it's in. See IPluginBlocktype::get_categories()
     * for a full explanation of blocktype sortorder.
     * @var int
     */
    public static $DEFAULT_SORTORDER = 100000;

    /**
     * Used in the get_blocktype_list_icon() method
     */
    const BLOCKTYPE_LIST_ICON_PNG = 0;
    const BLOCKTYPE_LIST_ICON_FONTAWESOME = 1;

    public static function get_plugintype_name() {
        return 'blocktype';
    }

    /**
     * Optionally specify a place for a block to link to. This will be rendered in the block header
     * in templates
     * @var BlockInstance
     * @return String or false
     */
    public static function get_link(BlockInstance $instance) {
        return false;
    }

    public static function get_theme_path($pluginname) {
        if (($artefactname = blocktype_artefactplugin($pluginname))) {
            // Path for block plugins that sit under an artefact
            return 'artefact/' . $artefactname . '/blocktype/' . $pluginname;
        }
        else {
            return parent::get_theme_path($pluginname);
        }
    }


    /**
     * This function returns an array of menu items
     * to be displayed in the top right navigation menu
     *
     * See the function find_menu_children() in lib/web.php
     * for a description of the expected array structure.
     *
     * @return array
     */
    public static function right_nav_menu_items() {
        return array();
    }

    /**
     * If the theme wants to display CSS icons for Mahara blocks, then it will
     * call this method to find out the name of the CSS icon to use. If this
     * method returns false, it will fall back to using the thumbnail.png
     *
     * In the core themes, these icons come from FontAwesome.
     * See htdocs/theme/raw/sass/lib/font-awesome/_icons.scss
     * for the full list of icons loaded by Mahara. (Note that this may change
     * from one Mahara version to another, as we upgrade FontAwesome.)
     * (Also note that the .scss files are stripped from the Mahara packaged
     * ZIP file. IF you don't have them, look in our git repository:
     * https://git.mahara.org/mahara/mahara/blob/master/htdocs/theme/raw/sass/lib/font-awesome/_icons.scss
     *
     * For the core blocktypes, we have "aliased" the name of the block
     * to the appropriate icon. See theme/raw/sass/lib/typography/_icons.scss.
     *
     * @param string $blocktypename The name of the blocktype
     * (since blocktype classes don't always know their own name as a string)
     * @return mixed Name of icon, or boolean false to fall back to thumbnail.png
     */
    public static function get_css_icon($blocktypename) {
        return false;
    }

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

    /**
     * Indicates whether this block can be loaded by Ajax after the page is done. This
     * improves page-load times by allowing blocks to be rendered in parallel instead
     * of in serial.
     *
     * You should avoid enabling this for:
     * - Blocks with particularly finicky Javascript contents
     * - Blocks that need to write to the session (the Ajax loader uses the session in read-only)
     * - Blocks that won't take long to render (static content, external content)
     * - Blocks that use hide_title_on_empty_content() (since you have to compute the content first
     * in order for that to work)
     *
     * @return boolean
     */
    public static function should_ajaxify() {
        return false;
    }

    /**
     * Allows block types to override the instance's title.
     *
     * For example: My Views, My Groups, My Friends, Wall
     */
    public static function override_instance_title(BlockInstance $instance) {
    }

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

    /**
    * This function must be implemented in the subclass if it requires
    * javascript. It returns an array of javascript files, either local
    * or remote.
    */
    public static function get_instance_javascript(BlockInstance $instance) {
        return array();
    }

   /**
    * This function must be implemented in the subclass if it requires
    * toolbar options for the view. It returns an array of button <a> tags and/or
    * html to display in the toobar area.
    */
    public static function get_instance_toolbars(BlockInstance $instance) {
        return array();
    }

    /**
    * This function must be implemented in the subclass if it requires
    * css file outside of sass compiled css. It returns an array of css files, either local
    * or remote.
    */
    public static function get_instance_css(BlockInstance $instance) {
        return array();
    }

    /**
     * Inline js to be executed when a block is rendered.
     */
    public static function get_instance_inline_javascript(BlockInstance $instance) {
    }

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
    * Some blocktype plugins will have related artefacts based on artefactid.
    * They should implement this function to keep a list of which ones. The
    * result of this method is used to work out what artefacts where present at
    * the time of the version creation to save in view_versioning.
    *
    * Note that it should just handle child level artefacts.
    */
    public static function get_current_artefacts(BlockInstance $instance) {
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
    public static function artefactchooser_element($default=null) {
    }

    /**
     *
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

    public static function category_description_from_name($name) {
        $description = get_string('blocktypecategorydesc.'. $name, 'view');
        return $description;
    }

    public static function get_blocktypes_for_category($category, View $view) {
        $sql = 'SELECT bti.name, bti.artefactplugin
            FROM {blocktype_installed} bti
            JOIN {blocktype_installed_category} btic ON btic.blocktype = bti.name
            JOIN {blocktype_installed_viewtype} btiv ON btiv.blocktype = bti.name
            WHERE btic.category = ? AND bti.active = 1 AND btiv.viewtype = ?
            ORDER BY btic.sortorder, bti.name';
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
            $classname = generate_class_name('blocktype', $namespaced);
            if (call_static_method($classname, 'allowed_in_view', $view)) {
                $blocktypes[] = array(
                    'name'           => $bt->name,
                    'title'          => call_static_method($classname, 'get_title'),
                    'description'    => call_static_method($classname, 'get_description'),
                    'singleonly'     => call_static_method($classname, 'single_only'),
                    'artefactplugin' => $bt->artefactplugin,
                    'thumbnail_path' => get_config('wwwroot') . 'thumb.php?type=blocktype&bt=' . $bt->name . ((!empty($bt->artefactplugin)) ? '&ap=' . $bt->artefactplugin : ''),
                    'cssicon'        => call_static_method($classname, 'get_css_icon', $bt->name),
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

    /**
     * Takes extra config data for an existing blockinstance of this class
     * and rewrites it so it can be used to configure a new block instance being put
     * in a new view
     *
     * This is used at view copy time, to give blocktypes the chance to change
     * the extra configuration for a block based on aspects about the new view
     *
     * As an example - when the 'Text' blocktype is copied, we
     * want it so that all image urls in the $configdata['text'] are
     * pointing to the new images.
     *
     * @param View $view The view that the blocktype will be placed into (e.g.
     *                   the View being created as a result of the copy)
     * @param BlockInstance $block The new block
     * @param array $configdata The configuration data for the old blocktype
     * @param array $artefactcopies The mapping of old artefact ids to new ones
     * @return array            The new configuration data.
     */
    public static function rewrite_blockinstance_extra_config(View $view, BlockInstance $block, $configdata, $artefactcopies) {
        return $configdata;
    }

    /**
     * Rewrite extra config data for a blockinstance of this class when
     * importing its view from Leap
     *
     * As an example - when the 'text' blocktype is imported, we
     * want all image urls in the $configdata['text'] are
     * pointing to the new images.
     *
     * @param array $artefactids The mapping of leap entries to their artefact ID
     *      see more PluginImportLeap->artefactids
     * @param array $configdata The imported configuration data for the blocktype
     * @return array            The new configuration data.
     */
    public static function import_rewrite_blockinstance_extra_config_leap(array $artefactids, array $configdata) {
        return $configdata;
    }


    /**
     * Rewrite a block instance's relationships to views & collections at the end of the leap import process.
     *
     * (For instance the navigation block stores a collection ID, and needs to know the new ID the
     * collection wound up with.)
     *
     * This method is called at the end of the import process. You will probably want to access the
     * $importer->viewids, $importer->collectionids, and/or $importer->artefactids fields
     *
     * @param int $blockinstanceid ID of the block instance.
     * @param PluginImportLeap $importer The importer object.
     */
    public static function import_rewrite_blockinstance_relationships_leap($blockinstanceid, $importer) {
        // Do nothing, in the default case
    }

    /*
     * The copy_type of a block affects how it should be copied when its view gets copied.
     * nocopy:       The block doesn't appear in the new view at all.
     * shallow:      A new block of the same type is created in the new view with a configuration as specified by the
     *               rewrite_blockinstance_config method
     * reference:    Block configuration is copied as-is.  If the block contains artefacts, the original artefact ids are
     *               retained in the new block's configuration even though they may have a different owner from the view.
     * full:         All artefacts referenced by the block are copied to the new owner's portfolio, and ids in the new
     *               block are updated to point to the copied artefacts.
     * fullinclself: All artefacts referenced by the block are copied, whether we are copying to a new owner's portfolio
     *               or our own one, and ids in the new block are updated to point to the copied artefacts.
     *
     * If the old owner and the new owner are the same, reference is used unless 'fullinclself' is specified.
     * If a block contains no artefacts, reference and full are equivalent.
     */
    public static function default_copy_type() {
        return 'shallow';
    }

    /*
     * The ignore_copy_artefacttypes of a block affects which artefacttypes should be ignored when copying.
     * You can specify which artefacts to ignore by an array of artefacttypes.
     */
    public static function ignore_copy_artefacttypes() {
        return array();
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

    /**
     * Defines if the block is viewable by the logged in user
     *
     * This method should be overridden in the child class, if peer role
     * should be able to see the block
     *
     * @param array user access role for the view
     * @return boolean whether display the block content for the roles
     */
    public static function display_for_roles($roles) {
        return !(count($roles) == 1 && $roles[0] == 'peer');
    }

}


/**
 * Mahara core blocks should extend this class. (Currently it only controls styling,
 * useful as a way of mapping the behavior of core blocks to theme items that are
 * not easily queried by the code.)
 */
abstract class MaharaCoreBlocktype extends PluginBlockType {

    /**
     * Use a css icon based on the name of the block
     * (These are defined in typography.scss)
     *
     * @param string $blocktypename
     * @return string
     */
    public static function get_css_icon($blocktypename) {
        return $blocktypename;
    }
}

/**
 * Old half-used "SystemBlockType" class. Deprecated, but still included because
 * some 3rd-party blocktypes use it.
 *
 * It was never clearly described what the purpose of this blocktype is; but most
 * likely its purpose was to indicate blocks that don't "contain" artefacts, such
 * as the "new views" block.
 *
 * But as long as your block isn't storing an item called "artefactid" or "artefactids"
 * in its blocktype.config field, then the default implementation of get_artefacts()
 * doesn't really matter.
 */
abstract class SystemBlockType extends PluginBlockType {
    public static function get_artefacts(BlockInstance $instance) {
        return array();
    }

    public final static function artefactchooser_element($default=null) {
    }
}


class BlockInstance {

    const RETRACTABLE_NO = 0;
    const RETRACTABLE_YES = 1;
    const RETRACTABLE_RETRACTED = 2;

    private $id;
    private $blocktype;
    private $artefactplugin;
    private $title;
    private $configdata = array();
    private $dirty;
    private $view;
    private $view_obj;
    private $row;
    private $column;
    private $order;
    private $canmoveleft;
    private $canmoveright;
    private $canmoveup;
    private $canmovedown;
    private $maxorderincolumn;
    private $artefacts = array();
    private $temp = array();
    private $tags = array();
    private $inedit = false;

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
        if ($field == 'tags') {
            $typecast = is_postgres() ? '::varchar' : '';
            $this->tags = get_column_sql("
            SELECT
                (CASE
                    WHEN t.tag LIKE 'tagid_%' THEN CONCAT(i.displayname, ': ', t2.tag)
                    ELSE t.tag
                END) AS tag, t.resourceid
            FROM {tag} t
            LEFT JOIN {tag} t2 ON t2.id" . $typecast . " = SUBSTRING(t.tag, 7)
            LEFT JOIN {institution} i ON i.name = t2.ownerid
            WHERE t.resourcetype = ? AND t.resourceid = ?
            ORDER BY tag", array('blocktype', $this->get('id')));
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
            if ($field == 'tags') {
                $this->set_tags($value);
            }
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

    private function set_tags($tags) {
        global $USER;

        if (empty($this->view_obj)) {
            $this->get_view();
        }

        if ($this->view_obj->get('group')) {
            $ownertype = 'group';
            $ownerid = $this->view_obj->get('group');
        }
        else if ($this->view_obj->get('institution')) {
            $ownertype = 'institution';
            $ownerid = $this->view_obj->get('institution');
        }
        else {
            $ownertype = 'user';
            $ownerid = $this->view_obj->get('owner');
        }
        $this->tags = check_case_sensitive($tags, 'tag');
        delete_records('tag', 'resourcetype', 'blocktype', 'resourceid', $this->get('id'));
        foreach (array_unique($this->tags) as $tag) {
            // truncate the tag before insert it into the database
            $tag = substr($tag, 0, 128);
            $tag = check_if_institution_tag($tag);
            insert_record('tag',
                (object)array(
                    'resourcetype' => 'blocktype',
                    'resourceid' => $this->get('id'),
                    'ownertype' => $ownertype,
                    'ownerid' => $ownerid,
                    'tag' => $tag,
                    'ctime' => db_format_timestamp(time()),
                    'editedby' => $USER->get('id'),
                )
            );
        }
    }

    // returns false if it finds a bad attachment
    // returns true if all attachments are allowed
    private function verify_attachment_permissions($id) {
        global $USER;

        if (is_array($id)) {
            foreach ($id as $id) {
                $file = artefact_instance_from_id($id);
                if (!$USER->can_view_artefact($file)) {
                    // bail out now as at least one attachment is bad
                    return false;
                }
            }
        }
        else {
            $file = artefact_instance_from_id($id);
            if (!$USER->can_view_artefact($file)) {
                return false;
            }
        }
        return true;
    }

    public function instance_config_store(Pieform $form, $values) {
        global $SESSION, $USER;

        // Destroy form values we don't care about
        unset($values['sesskey']);
        unset($values['blockinstance']);
        unset($values['action_configureblockinstance_id_' . $this->get('id')]);
        unset($values['blockconfig']);
        unset($values['id']);
        unset($values['change']);
        unset($values['new']);
        if (isset($values['retractable'])) {
            switch ($values['retractable']) {
                case BlockInstance::RETRACTABLE_YES:
                    $values['retractable'] = 1;
                    $values['retractedonload'] = 0;
                    break;
                case BlockInstance::RETRACTABLE_RETRACTED:
                    $values['retractable'] = 1;
                    $values['retractedonload'] = 1;
                    break;
                case BlockInstance::RETRACTABLE_NO:
                default:
                    $values['retractable'] = 0;
                    $values['retractedonload'] = 0;
                    break;
            }
        }

        // make sure that user is allowed to publish artefact. This is to stop
        // hacking of form value to attach other users private data.
        $badattachment = false;
        if (isset($values['blocktemplate']) && !empty($values['blocktemplate'])) {
            // Ignore check on artefactids as they are not relating to actual artefacts
        }
        else {
            if (!empty($values['artefactid'])) {
                $badattachment = !$this->verify_attachment_permissions($values['artefactid']);
            }
            if (!empty($values['artefactids'])) {
                $badattachment = !$this->verify_attachment_permissions($values['artefactids']);
            }
        }

        if ($badattachment) {
            $result['message'] = get_string('unrecoverableerror', 'error');
            $form->set_error(null, $result['message']);
            $form->reply(PIEFORM_ERR, $result);
            exit();
        }

        $redirect = '/view/blocks.php?id=' . $this->get('view');
        if (param_boolean('new', false)) {
            $redirect .= '&new=1';
        }
        if ($category = param_alpha('c', '')) {
            $redirect .= '&c='. $category;
        }

        $result = array(
            'goto' => $redirect,
        );

        if (is_callable(array(generate_class_name('blocktype', $this->get('blocktype')), 'instance_config_save'))) {
            try {
                $values = call_static_method(generate_class_name('blocktype', $this->get('blocktype')), 'instance_config_save', $values, $this);
            }
            catch (MaharaException $e) {
                $result['message'] = $e instanceof UserException ? $e->getMessage() : get_string('unrecoverableerror', 'error');
                $form->set_error(null, $result['message']);
                $form->reply(PIEFORM_ERR, $result);
            }
        }

        $title = (isset($values['title'])) ? $values['title'] : '';
        unset($values['title']);

        if (isset($values['tags'])) {
            $this->set('tags', $values['tags']);
            unset($values['tags']);
        }

        // A block may return a list of other blocks that need to be
        // redrawn after configuration of this block.
        $torender = !empty($values['_redrawblocks']) && $form->submitted_by_js() ? $values['_redrawblocks'] : array();
        unset($values['_redrawblocks']);

        $this->set('configdata', $values);
        $this->set('title', $title);

        $this->commit();

        try {
            $rendered = $this->render_editing(false, false, $form->submitted_by_js());
        }
        catch (HTMLPurifier_Exception $e) {
            $message = get_string('blockconfigurationrenderingerror', 'view') . ' ' . $e->getMessage();
            $form->reply(PIEFORM_ERR, array('message' => $message));
        }

        $result = array(
            'error'   => false,
            'message' => get_string('blockinstanceconfiguredsuccessfully', 'view'),
            'data'    => $rendered,
            'blockid' => $this->get('id'),
            'viewid'  => $this->get('view'),
            'goto'    => $redirect,
        );

        // Render all the other blocks in the torender list
        $result['otherblocks'] = array();
        foreach ($torender as $blockid) {
            if ($blockid != $result['blockid']) {
                $otherblock = new BlockInstance($blockid);
                $result['otherblocks'][] = array(
                    'blockid' => $blockid,
                    'data'    => $otherblock->render_editing(false, false, true),
                );
            }
        }

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

    public function to_stdclass() {
        return (object)get_object_vars($this);
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
        global $USER;

        safe_require('blocktype', $this->get('blocktype'));
        $movecontrols = array();
        $this->inedit = true;
        $blocktypeclass = generate_class_name('blocktype', $this->get('blocktype'));
        try {
            $title = $this->get_title();
        }
        catch (NotFoundException $e) {
            log_debug('Cannot render block title. Original error follows: ' . $e->getMessage());
            $title = get_string('notitle', 'view');
        }

        if ($configure) {
            list($content, $js, $css) = array_values($this->build_configure_form($new));
        }
        else {
            try {
              $user_roles = get_column('view_access', 'role', 'usr', $USER->get('id'), 'view', $this->view);
              if (!call_static_method($blocktypeclass, 'display_for_roles', $user_roles)) {
                  $content = '';
                  $css = '';
                  $js = '';
              }
              else   {
                $content = call_static_method(generate_class_name('blocktype', $this->get('blocktype')), 'render_instance', $this, true);
                $jsfiles = call_static_method($blocktypeclass, 'get_instance_javascript', $this);
                $inlinejs = call_static_method($blocktypeclass, 'get_instance_inline_javascript', $this);
                $js = $this->get_get_javascript_javascript($jsfiles) . $inlinejs;
                $css = '';
              }
            }
            catch (NotFoundException $e) {
                // Whoops - where did the image go? There is possibly a bug
                // somewhere else that meant that this blockinstance wasn't
                // told that the image was previously deleted. But the block
                // instance is not allowed to treat this as a failure
                log_debug('Artefact not found when rendering a block instance. '
                    . 'There might be a bug with deleting artefacts of this type? '
                    . 'Original error follows:');
                log_debug($e->getMessage());
                $content = '';
                $js = '';
                $css = '';
            }

            if (!defined('JSON') && !$jsreply) {
                if ($this->get('canmoveleft')) {
                    $movecontrols[] = array(
                        'column' => $this->get('column') - 1,
                        'order'  => $this->get('order'),
                        'title'  => $title == '' ? get_string('movethisblockleft', 'view') : get_string('moveblockleft', 'view', "'$title'"),
                        'arrow'  => "icon icon-long-arrow-left",
                        'dir'    => 'left',
                    );
                }
                if ($this->get('canmovedown')) {
                    $movecontrols[] = array(
                        'column' => $this->get('column'),
                        'order'  => $this->get('order') + 1,
                        'title'  => $title == '' ? get_string('movethisblockdown', 'view') : get_string('moveblockdown', 'view', "'$title'"),
                        'arrow'  => 'icon icon-long-arrow-down',
                        'dir'    => 'down',
                    );
                }
                if ($this->get('canmoveup')) {
                    $movecontrols[] = array(
                        'column' => $this->get('column'),
                        'order'  => $this->get('order') - 1,
                        'title'  => $title == '' ? get_string('movethisblockup', 'view') : get_string('moveblockup', 'view', "'$title'"),
                        'arrow'  => 'icon icon-long-arrow-up',
                        'dir'    => 'up',
                    );
                }
                if ($this->get('canmoveright')) {
                    $movecontrols[] = array(
                        'column' => $this->get('column') + 1,
                        'order'  => $this->get('order'),
                        'title'  => $title == '' ? get_string('movethisblockright', 'view') : get_string('moveblockright', 'view', "'$title'"),
                        'arrow'  => 'icon icon-long-arrow-right',
                        'dir'    => 'right',
                    );
                }
            }
        }

        $configtitle = $title == '' ? call_static_method($blocktypeclass, 'get_title') : $title;

        $smarty = smarty_core();
        $id = $this->get('id');
        $smarty->assign('id',     $id);
        $smarty->assign('viewid', $this->get('view'));
        $smarty->assign('title',  $title);
        $smarty->assign('row',    $this->get('row'));
        $smarty->assign('column', $this->get('column'));
        $smarty->assign('order',  $this->get('order'));
        $smarty->assign('blocktype', $this->get('blocktype'));
        $smarty->assign('movecontrols', $movecontrols);
        $smarty->assign('configurable', call_static_method($blocktypeclass, 'has_instance_config'));
        $smarty->assign('configure', $configure); // Used by the javascript to rewrite the block, wider.
        $smarty->assign('configtitle',  $configtitle);
        $smarty->assign('content', $content);
        $smarty->assign('javascript', defined('JSON'));
        $smarty->assign('strnotitle', get_string('notitle', 'view'));
        $smarty->assign('strmovetitletext', $title == '' ? get_string('movethisblock', 'view') : get_string('moveblock', 'view', "'$title'"));
        $smarty->assign('strmovetitletexttooltip', get_string('moveblock2', 'view'));
        $smarty->assign('strconfigtitletext', $title == '' ? get_string('configurethisblock1', 'view', $id) : get_string('configureblock1', 'view', "'$title'", $id));
        $smarty->assign('strconfigtitletexttooltip', get_string('configureblock2', 'view'));
        $smarty->assign('strremovetitletext', $title == '' ? get_string('removethisblock1', 'view', $id) : get_string('removeblock1', 'view', "'$title'", $id));
        $smarty->assign('strremovetitletexttooltip', get_string('removeblock2', 'view'));
        $smarty->assign('lockblocks', ($this->get_view()->get('lockblocks') && $this->get_view()->get('owner'))); // Only lock blocks for user's portfolio pages

        if (!$configure && $title) {
            $configdata = $this->get('configdata');
            if (isset($configdata['retractable']) && $configdata['retractable']) {
                $smarty->assign('retractable', true);
                if (defined('JSON') || $jsreply) {
                    $jssmarty = smarty_core();
                    $jssmarty->assign('id', $this->get('id'));
                    $js .= $jssmarty->fetch('view/retractablejs.tpl');
                }
            }
        }
        if (is_array($css)) {
            $css = array_unique($css);
        }
        return array('html' => $smarty->fetch('view/blocktypecontainerediting.tpl'), 'javascript' => $js, 'pieformcss' => $css);
    }



    public function order_artefacts_by_title($ids){
      $result = array();
      if ($ids) {
          $artefacts =  get_records_sql_array(
              'SELECT a.id, a.title FROM {artefact} a WHERE a.id in ( '. join(',', array_fill(0, count($ids), '?')) . ')', $ids
          );
          if ($artefacts) {
              uasort($artefacts, array("BlockInstance", "my_files_cmp"));
              foreach ($artefacts as $artefact) {
                  $result[] = $artefact->id;
              }
          }
      }
      return $result;
    }

    public static function my_files_cmp($a, $b) {
        return strnatcasecmp($a->title, $b->title);
    }

    /**
     * To render the html of a block for viewing
     *
     * @param boolean $exporting  Indicate the rendering is for an export
     *                            If we are doing an export we can't render the block to be loaded via ajax
     * @param boolean $versioning Indicate the rendering is for an older view version
     *
     * @return the rendered block
     */
    public function render_viewing($exporting=false, $versioning=false) {
        global $USER;

        if (!safe_require_plugin('blocktype', $this->get('blocktype'))) {
            return;
        }

        $smarty = smarty_core();

        $user_roles = get_column('view_access', 'role', 'usr', $USER->get('id'), 'view', $this->view);

        $classname = generate_class_name('blocktype', $this->get('blocktype'));
        $displayforrole = call_static_method($classname, 'display_for_roles', $user_roles);
        $checkview = $this->get_view();
        if ($checkview->get('owner') == NULL ||
            ($USER->is_admin_for_user($checkview->get('owner')) && $checkview->is_objectionable())) {
            $displayforrole = true;
        }
        if (!$displayforrole) {
            $content = '';
            $smarty->assign('loadbyajax', false);
        }
        else if (get_config('ajaxifyblocks') && call_static_method($classname, 'should_ajaxify') && $exporting === false && $versioning === false) {
            $content = '';
            $smarty->assign('loadbyajax', true);
        }
        else {
            $smarty->assign('loadbyajax', false);
            try {
                $content = call_static_method($classname, 'render_instance', $this, false, $versioning);
            }
            catch (NotFoundException $e) {
                //Ignore not found error when fetching old verions of view
                if (!$versioning) {
                    // Whoops - where did the image go? There is possibly a bug
                    // somewhere else that meant that this blockinstance wasn't
                    // told that the image was previously deleted. But the block
                    // instance is not allowed to treat this as a failure
                    log_debug('Artefact not found when rendering a block instance. '
                        . 'There might be a bug with deleting artefacts of this type? '
                        . 'Original error follows:');
                    log_debug($e->getMessage());
                }
                $content = '';
            }
        }

        $smarty->assign('id',     $this->get('id'));
        $smarty->assign('blocktype', $this->get('blocktype'));
        // hide the title if required and no content is present
        if (call_static_method($classname, 'hide_title_on_empty_content')
            && !trim($content)) {
            return;
        }
        try {
            $title = $this->get_title();
        }
        catch (NotFoundException $e) {
            log_debug('Cannot render block title. Original error follows: ' . $e->getMessage());
            $title = get_string('notitle', 'view');
        }
        $smarty->assign('title', $title);

        // If this block is for just one artefact, we set the title of the
        // block to be a link to view more information about that artefact
        $configdata = $this->get('configdata');
        if (!empty($configdata['artefactid']) && $displayforrole) {
            if (call_static_method($classname, 'has_title_link')) {
                $smarty->assign('viewartefacturl', get_config('wwwroot') . 'artefact/artefact.php?artefact='
                    . $configdata['artefactid'] . '&view=' . $this->get('view') . '&block=' . $this->get('id'));
            }
        }

        if ($displayforrole) {
            if (method_exists($classname, 'feed_url')) {
                $smarty->assign('feedlink', call_static_method($classname, 'feed_url', $this));
            }

            $smarty->assign('link', call_static_method($classname, 'get_link', $this));
        }

        $smarty->assign('content', $content);
        if (isset($configdata['retractable']) && $title && !$exporting) {
            $smarty->assign('retractable', $configdata['retractable']);
            if (isset($configdata['retractedonload'])) {
                $smarty->assign('retractedonload', $configdata['retractedonload']);
            }
        }
        $smarty->assign('versioning', $versioning);
        return $smarty->fetch('view/blocktypecontainerviewing.tpl');
    }

    /**
     * Builds the configuration pieform for this blockinstance
     *
     * @return array Array with two keys: 'html' for raw html, 'javascript' for
     *               javascript to run, 'css' for dynamic css to add to header
     */
    public function build_configure_form($new=false) {

        static $renderedform;
        if (!empty($renderedform)) {
            return $renderedform;
        }

        $notretractable = get_config_plugin('blocktype', $this->get('blocktype'), 'notretractable');

        safe_require('blocktype', $this->get('blocktype'));
        $blocktypeclass = generate_class_name('blocktype', $this->get('blocktype'));
        $elements = call_static_method($blocktypeclass, 'instance_config_form', $this, $this->get_view()->get('template'));

        // Block types may specify a method to generate a default title for a block
        $hasdefault = method_exists($blocktypeclass, 'get_instance_title');

        $title = $this->get('title');
        $configdata = $this->get('configdata');
        $retractable = (isset($configdata['retractable']) ? $configdata['retractable'] : false);
        $retractedonload = (isset($configdata['retractedonload']) ? $configdata['retractedonload'] : $retractable);

        if (call_static_method($blocktypeclass, 'override_instance_title', $this)) {
            $titleelement = array(
                'type' => 'hidden',
                'value' => $title,
            );
        }
        else {
            $titleelement = array(
                'type' => 'text',
                'title' => get_string('blocktitle', 'view'),
                'description' => $hasdefault ? get_string('defaulttitledescription', 'blocktype.' . blocktype_name_to_namespaced($this->get('blocktype'))) : null,
                'defaultvalue' => $title,
                'rules' => array('maxlength' => 255),
                'hidewhenempty' => $hasdefault,
                'expandtext'    => get_string('setblocktitle'),
            );
        }
        $elements = array_merge(
            array(
                'title' => $titleelement,
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

        if (!$notretractable) {
            $elements = array_merge(
                $elements,
                array (
                    'retractable' => array(
                        'type'         => 'select',
                        'title'        => get_string('retractable', 'view'),
                        'description'  => get_string('retractabledescription', 'view'),
                        'options' => array(
                                BlockInstance::RETRACTABLE_NO => get_string('no'),
                                BlockInstance::RETRACTABLE_YES => get_string('yes'),
                                BlockInstance::RETRACTABLE_RETRACTED => get_string('retractedonload', 'view')
                        ),
                        'defaultvalue' => $retractable + $retractedonload,
                    ),
                )
            );
        }

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
            'class' => 'btn-secondary',
            'value' => array(get_string('save'), $cancel),
            'goto' => View::make_base_url(),
        );

        $configdirs = array(get_config('libroot') . 'form/');
        if ($this->get('artefactplugin')) {
            $configdirs[] = get_config('docroot') . 'artefact/' . $this->get('artefactplugin') . '/form/';
        }

        $form = array(
            'name' => 'instconf',
            'renderer' => 'div',
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
        else if (preg_match('/<script>(new Pieform\(.*\);)<\/script>/', $html, $matches)) {
            $js = "var pf_{$form['name']} = " . $matches[1] . "pf_{$form['name']}.init();";
        }
        else {
            $js = '';
        }

        // We need to load any javascript required for the pieform. We do this
        // by checking for an api function that has been added especially for
        // the purpose, but that is not part of Pieforms. Maybe one day later
        // it will be though
        foreach ($elements as $key => $element) {
            $element['name'] = $key;
            $function = 'pieform_element_' . $element['type'] . '_views_js';
            if (is_callable($function)) {
                $js .= call_user_func_array($function, array($pieform, $element));
            }
        }

        $configjs = call_static_method($blocktypeclass, 'get_instance_config_javascript', $this);
        if (is_array($configjs)) {
            $js .= $this->get_get_javascript_javascript($configjs);
        }
        else if (is_string($configjs)) {
            $js .= $configjs;
        }

        $js .= "
            jQuery(function ($) {
                $('#instconf_title').on('change', function() {
                    $('#instconf_retractable').prop('disabled', ($('#instconf_title').prop('value') == ''));
                });
            });";

        // We need to load any dynamic css required for the pieform. We do this
        // by checking for an api function that has been added especially for
        // the purpose, but that is not part of Pieforms. Maybe one day later
        // it will be though
        $css = array();
        foreach ($elements as $key => $element) {
            $element['name'] = $key;
            $function = 'pieform_element_' . $element['type'] . '_views_css';
            if (is_callable($function)) {
                $css[] = call_user_func_array($function, array($pieform, $element));
            }
        }

        $renderedform = array('html' => $html, 'javascript' => $js, 'css' => $css);
        return $renderedform;
    }

    public function commit() {
        if (empty($this->dirty)) {
            return;
        }
        $fordb = new stdClass();
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
        safe_require('blocktype', blocktype_name_to_namespaced($this->get('blocktype')));
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

        $va = new stdClass();
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
                $colsperrow = $this->get_view()->get('columnsperrow');
                return ($this->column < $colsperrow[$this->row]->columns);
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
        if (empty($this->id) || ($this->get_view()->get('lockblocks') && $this->get_view()->get('owner'))) {
            $this->dirty = false;
            return;
        }
        $ignorefields = array('order', 'dirty',
            'ignoreconfigdata' => array('retractable',
                'removeoncancel',
                'sure',
                'retractedonload'
            )
        );
        //Propagate the deletion of the block
        handle_event('deleteblockinstance', $this, $ignorefields);

        db_begin();
        safe_require('blocktype', $this->get('blocktype'), 'lib.php', 'require_once', true);
        $classname = generate_class_name('blocktype', $this->get('blocktype'));
        if (is_callable($classname . '::delete_instance')) {
            call_static_method($classname, 'delete_instance', $this);
        }
        delete_records('view_artefact', 'block', $this->id);
        delete_records('block_instance', 'id', $this->id);
        delete_records('tag', 'resourcetype', 'blocktype', 'resourceid', $this->id);
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
     * Removes specified artefacts from every block instance that has
     * any of them selected. (The block instances remain in place, but
     * with that artefact no longer selected.)
     *
     * @param array $artefactids
     */
    public static function bulk_remove_artefacts($artefactids) {

        if (empty($artefactids)) {
            return;
        }

        $paramstr = substr(str_repeat('?, ', count($artefactids)), 0, -2);
        $records = get_records_sql_array("
            SELECT va.block, va.artefact, bi.configdata
            FROM {view_artefact} va JOIN {block_instance} bi ON va.block = bi.id
            WHERE va.artefact IN ($paramstr)", $artefactids);

        if (empty($records)) {
            return;
        }

        // Collate the SQL results so we have a list of blocks, where
        // each block has its current configdata, and a list of artefacts
        // to remove
        $blocklist = array();
        foreach ($records as $record) {
            // Initialize an array record for this block
            if (!isset($blocklist[$record->block])) {
                $blocklist[$record->block] = (object) array(
                    'artefactstoremove' => array(),
                    'configdata' => unserialize($record->configdata),
                );
            }

            $blocklist[$record->block]->artefactstoremove[] = $record->artefact;
        }

        // Go through the collated block list, and remove the specified
        // artefacts from each one's configdata
        foreach ($blocklist as $blockid => $blockdata) {
            $change = false;
            if (isset($blockdata->configdata['artefactid'])) {
                if ($change = $blockdata->configdata['artefactid'] == $blockdata->artefactstoremove[0]) {
                    $blockdata->configdata['artefactid'] = null;
                }
            }
            else if (isset($blockdata->configdata['artefactids'])) {
                $blockdata->configdata['artefactids'] = array_values(array_diff($blockdata->configdata['artefactids'], $blockdata->artefactstoremove));
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

    public function save_artefact_instance($artefact) {
        $this->artefacts[$artefact->get('id')] = $artefact;
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
            'view_obj'   => $view,
            'row'        => $this->get('row'),
            'column'     => $this->get('column'),
            'order'      => $this->get('order'),
        ));

        if (($sameowner && $copytype != 'fullinclself') || $copytype == 'reference') {
            $newblock->set('configdata', $configdata);
            $newblock->commit();
            // Copy any tagged block tags - we need to commit before here so the block instance has an ID value
            if ($tags = $this->get('tags')) {
                $newblock->set('tags', $tags);
                $newblock->commit();
            }

            if ($this->get('blocktype') == 'taggedposts' && $copytype == 'tagsonly') {
                $this->copy_tags($newblock->get('id'));
            }
            return true;
        }

        if ($ignore = call_static_method($blocktypeclass, 'ignore_copy_artefacttypes', $view)) {
            $artefactids = (array)get_column_sql('
                SELECT artefact FROM {view_artefact} va
                JOIN {artefact} a ON a.id = va.artefact
                WHERE va.block = ?
                AND a.artefacttype NOT IN (' . join(',', array_map('db_quote', $ignore)) . ')', array($this->get('id')));
        }
        else {
            $artefactids = get_column('view_artefact', 'artefact', 'block', $this->get('id'));
        }

        if (!empty($artefactids)
            && ($copytype == 'full' || $copytype == 'fullinclself')) {
            // Copy artefacts & put the new artefact ids into the new block.
            // Artefacts may have children (defined using the parent column of the artefact table) and attachments (currently
            // only for blogposts).  If we copy an artefact we must copy all its descendents & attachments too.

            require_once(get_config('docroot') . 'artefact/lib.php');
            $descendants = artefact_get_descendants($artefactids);

            // We need the artefact instance before we can get its attachments
            $tocopy = array();
            $attachmentlists = array();
            $embedlists = array();
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
                    // Get embedded file artefacts
                    $embedlists[$d] = $tocopy[$d]->embed_id_list();
                    foreach ($embedlists[$d] as $a) {
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
                if (!empty($embedlists[$aid])) {
                    $artefactcopies[$aid]->oldembeds= $embedlists[$aid];
                }
                $artefactcopies[$aid]->newid = $a->copy_for_new_owner($view->get('owner'), $view->get('group'), $view->get('institution'));
            }

            // Record new artefact ids in the new block
            if (isset($configdata['artefactid'])) {
                $configdata['artefactid'] = $artefactcopies[$configdata['artefactid']]->newid;
            }
            if (isset($configdata['artefactids'])) {
                foreach ($configdata['artefactids'] as &$oldid) {
                    $oldid = $artefactcopies[$oldid]->newid;
                }
            }
        }
        else {
            $configdata = call_static_method($blocktypeclass, 'rewrite_blockinstance_config', $view, $configdata);
        }

        // Rewrite the extra configuration of block
        $configdata = call_static_method($blocktypeclass, 'rewrite_blockinstance_extra_config', $view, $newblock, $configdata, $artefactcopies);

        $newblock->set('configdata', $configdata);
        $newblock->commit();
        // Copy any tagged block tags - we need to commit before here so the block instance has an ID value
        if ($tags = $this->get('tags')) {
            $newblock->set('tags', $tags);
            $newblock->commit();
        }
        if ($this->get('blocktype') == 'taggedposts' && $copytype == 'tagsonly') {
            $this->copy_tags($newblock->get('id'));
        }

        return true;
    }

    public function copy_tags($newid) {
        // Need to copy the tags to the new block
        if ($tagrecords = get_records_array('blocktype_taggedposts_tags', 'block_instance', $this->get('id'), 'tagtype desc, tag', 'tag, tagtype')) {
            foreach ($tagrecords as $tags) {
                $tagobject = new stdClass();
                $tagobject->block_instance = $newid;
                $tagobject->tag = $tags->tag;
                $tagobject->tagtype = $tags->tagtype;
                insert_record('blocktype_taggedposts_tags', $tagobject);
            }
        }
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

            $js .= "jQuery.ajax({url: '{$file}', dataType: 'script', cache:true";
            if (is_array($jsfile) && !empty($jsfile['initjs'])) {
                // Pass success callback to getScript
                $js .= ", success: function(data){\n" . $jsfile['initjs'] . "\n}";
            }
            $js .= "});\n";
        }
        return $js;
    }

    /**
     * This function returns an array of menu items to be displayed
     * on a group page when viewed by group members.
     * Each item should be a stdClass() object containing -
     * - title language pack key
     * - url relative to wwwroot
     * @return array
     */
    public static function group_tabs($groupid, $role) {
        return array();
    }
}

function require_blocktype_plugins() {
    static $plugins = null;
    if (is_null($plugins)) {
        $plugins = plugins_installed('blocktype');
        foreach ($plugins as $plugin) {
            safe_require('blocktype', $plugin->name);
        }
    }
    return $plugins;
}

function blocktype_get_types_from_filter($filter) {
    static $contenttype_blocktype = null;

    if (is_null($contenttype_blocktype)) {
        $contenttype_blocktype = array();
        foreach (require_blocktype_plugins() as $plugin) {
            $classname = generate_class_name('blocktype', $plugin->name);
            if (!is_callable($classname . '::get_blocktype_type_content_types')) {
                continue;
            }
            $blocktypetypes = call_static_method($classname, 'get_blocktype_type_content_types');
            foreach ($blocktypetypes as $blocktype => $contenttypes) {
                if (!empty($contenttypes)) {
                    foreach ($contenttypes as $ct) {
                        $contenttype_blocktype[$ct][] = $blocktype;
                    }
                }
            }
        }
    }

    if (empty($contenttype_blocktype[$filter])) {
        return null;
    }

    return $contenttype_blocktype[$filter];
}
