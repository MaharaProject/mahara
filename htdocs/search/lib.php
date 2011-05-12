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
 * @subpackage search
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/**
 * Base search class. Provides a common interface with which searches can be
 * carried out.
 */
abstract class PluginSearch extends Plugin {

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
    public static abstract function search_user($query_string, $limit, $offset = 0);

    /**
     * Given a query string and limits, return an array of matching groups
     *
     * @param string  The query string
     * @param integer How many results to return
     * @param integer What result to start at (0 == first result)
     * @param string  Which groups to search (all, member, notmember)
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
    public static abstract function search_group($query_string, $limit, $offset=0, $type='member');

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
    public static abstract function self_search($query_string, $limit, $offset, $type = 'all');

    protected static function self_search_make_links($data) {
        $wwwroot = get_config('wwwroot');
        if ($data['count']) {
            foreach ($data['data'] as &$result) {
                switch ($result['type']) {
                    case 'artefact':
                        safe_require('artefact', get_field('artefact_installed_type', 'plugin', 'name', $result['artefacttype']));
                        $result['links'] = call_static_method(generate_artefact_class_name($result['artefacttype']), 'get_links', $result['id']);
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
}
