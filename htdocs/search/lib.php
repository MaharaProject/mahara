<?php
/**
 *
 * @package    mahara
 * @subpackage search
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

/**
 * Helper interface to hold IPluginSearch's abstract static methods
 */
interface IPluginSearch {
    /**
     * Given a query string and limits, return an array of matching users
     *
     * NOTE: user with ID zero or that are NOT active should never be returned
     *
     * @param string  The query string
     * @param integer How many results to return
     * @param integer What result to start at (0 == first result)
     * @return array  A data structure containing results looking like ...
     *         $results = array(
     *               count   => integer, // total number of results
     *               limit   => integer, // how many results are returned
     *               offset  => integer, // starting from which result
     *               data    => array(   // the result records
     *                   array(
     *                       id            => integer,
     *                       username      => string,
     *                       institution   => string,
     *                       firstname     => string,
     *                       lastname      => string,
     *                       preferredname => string,
     *                       email         => string,
     *                   ),
     *                   array(
     *                       id            => integer,
     *                       username      => string,
     *                       institution   => string,
     *                       firstname     => string,
     *                       lastname      => string,
     *                       preferredname => string,
     *                       email         => string,
     *                   ),
     *                   array(...),
     *               ),
     *           );
     */
    public static function search_user($query_string, $limit, $offset = 0);

    /**
     * Given a query string and limits, return an array of matching groups
     *
     * @param string  The query string
     * @param integer How many results to return
     * @param integer What result to start at (0 == first result)
     * @param string  Which groups to search (all, member, notmember)
     * @param string  Category the group belongs to
     * @param string  The institution the group belongs
     * @return array  A data structure containing results looking like ...
     *         $results = array(
     *               count   => integer, // total number of results
     *               limit   => integer, // how many results are returned
     *               offset  => integer, // starting from which result
     *               data    => array(   // the result records
     *                   array(
     *                       id            => integer,
     *                       name          => string,
     *                       description   => string,
     *                       jointype      => string,
     *                       owner         => string,
     *                       ctime         => string,
     *                       mtime         => string,
     *                   ),
     *                   array(
     *                       id            => integer,
     *                       name          => string,
     *                       description   => string,
     *                       jointype      => string,
     *                       owner         => string,
     *                       ctime         => string,
     *                       mtime         => string,
     *                   ),
     *                   array(...),
     *               ),
     *           );
     */
    public static function search_group($query_string, $limit, $offset=0, $type='member', $category='', $institution='all');

    /**
     * Returns search results for users in a particular group
     *
     * It's called by and tightly coupled with get_group_user_search_results() in searchlib.php. Look there for
     * the exact meaning of its parameters and expected return values.
     */
    public static function group_search_user($group, $query_string, $constraints, $offset, $limit, $membershiptype, $order, $friendof, $orderbyoptionidx=null);

    /**
     * Given a query string and limits, return an array of matching objects
     * owned by the current user.  Possible return types are ...
     *   - artefact
     *   - view
     *   - @todo potentially other types such as group could be searched by this too
     *
     * Implementations of this search should search across tags for artefacts
     * and views at a minimum. Ideally the search would also index
     * title/description and other metadata for these objects.
     *
     * @param string  The query string
     * @param integer How many results to return
     * @param integer What result to start at (0 == first result)
     * @param string  Type to search for (either 'all' or one of the types above).
     *
     */
    public static function self_search($query_string, $limit, $offset, $type = 'all');
}

/**
 * Base search class. Provides a common interface with which searches can be
 * carried out.
 */
abstract class PluginSearch extends Plugin implements IPluginSearch {

    public static function get_plugintype_name() {
        return 'search';
    }

    /**
     * This function gets called when the sitewide search plugin is switched to
     * this one. It's the chance for the plugin to do any post-configuration
     * initialization it might need. (The same stuff you'd probably do after
     * changing the plugin's configuration via its extension config page.)
     */
    public static function initialize_sitewide() {
        return true;
    }

    /**
     * This function gets called when the sitewide search plugin is switched AWAY
     * from this one. It's the chance for the plugin to disable anything that would
     * cause problems now that the search is no longer in use.
     */
    public static function cleanup_sitewide() {
        return true;
    }

    /**
     * This function gets called everytime the site options are saved. It is used to
     * detect whether the search plugin can connect to any servers it requires.
     * Defaults to true.
     */
    public static function can_connect() {
        return true;
    }

    /**
     * This function determines whether the plugin is currently available to be chosen
     * as the sitewide search plugin (i.e. get_config('searchplugin'))
     */
    public static function is_available_for_site_setting() {
        return true;
    }

    /**
     * This function determines whether the plugin allows a search box to display for
     * non-logged in users - only useful if results returned by search are allowed to
     * be seen by the public
     */
    public static function publicform_allowed() {
        return false;
    }

    /**
     * This function indicates whether the plugin should take the raw $query string
     * when its group_search_user function is called, or whether it should get the
     * parsed query string.
     *
     * @return boolean
     */
    public static function can_process_raw_group_search_user_queries() {
        return false;
    }

    protected static function self_search_make_links(&$data) {
        $wwwroot = get_config('wwwroot');
        if ($data['count']) {
            foreach ($data['data'] as &$result) {
                switch ($result['type']) {
                    case 'artefact':
                        safe_require('artefact', get_field('artefact_installed_type', 'plugin', 'name', $result['artefacttype']));
                        $artefact = artefact_instance_from_id($result['id']);
                        if ($artefact->in_view_list() && $views = $artefact->get_views_instances()) {
                            foreach ($views as $view) {
                                $result['views'][$view->get('title')] = get_config('wwwroot') . 'artefact/artefact.php?artefact='
                                    . $result['id'] . '&view=' . $view->get('id');
                            }
                        }
                        if ($links = $artefact->get_links($result['id'])) {
                            $result['links'] = $links;
                        }
                        break;
                    case 'view':
                        $result['links'] = array(
                            '_default'                        => $wwwroot . 'view/view.php?id=' . $result['id'],
                            // TODO: these are certainly broken!
                            get_string('editviewinformation') => $wwwroot . 'view/editmetadata.php?viewid=' . $result['id'],
                            get_string('editview')            => $wwwroot . 'view/edit.php?viewid=' . $result['id'],
                            get_string('editaccess')          => $wwwroot . 'view/editaccess.php?viewid=' . $result['id'],
                        );
                        break;
                    default:
                        break;
                }
            }
        }
    }


    /**
     * Generates the search form used in the page headers
     * @return string
     */
    public static function header_search_form() {
        return pieform(array(
                'name'                => 'usf',
                'action'              => get_config('wwwroot') . 'user/find.php',
                'renderer'            => 'oneline',
                'autofocus'           => false,
                'validate'            => false,
                'presubmitcallback'   => '',
                'class'               => 'header-search-form',
                'elements'            => array(
                        'query' => array(
                                'type'           => 'text',
                                'defaultvalue'   => '',
                                'title'          => get_string('searchusers'),
                                'placeholder'    => get_string('searchusers'),
                                'hiddenlabel'    => true,
                        ),
                        'submit' => array(
                            'type' => 'button',
                            'class' => 'btn-primary input-group-btn',
                            'usebuttontag' => true,
                            'value' => '<span class="icon icon-search icon-lg" role="presentation" aria-hidden="true"></span><span class="sr-only">'. get_string('go') . '</span>',
                        )
                )
        ));
    }
}
