<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package    mahara
 * @subpackage search-internal
 * @author     Martyn Smith <martyn@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

safe_require('search', 'internal');

/**
 * The Solr search plugin which searches using the Solr search engine (mostly)
 */
class PluginSearchSolr extends PluginSearchInternal {
    public static function has_config() {
        return true;
    }

    public static function get_config_options() {
        $elements = array();

        $elements['solrurl'] = array(
            'type'         => 'text',
            'title'        => get_string('solrurl', 'search.solr'), 
            'defaultvalue' => get_config_plugin('search', 'solr', 'solrurl'),
        );

        return array(
            'elements' => $elements,
            'renderer' => 'table'
        );
    }

    public static function save_config_options($values) {
        set_config_plugin('search', 'solr', 'solrurl', $values['solrurl']);
    }

    public static function search_user($query_string, $limit, $offset = 0) {
        $results = self::send_query($query_string, $limit, $offset, array('type' => 'user'));

        if (is_array($results['data'])) {
            foreach ($results['data'] as &$result) {
                $new_result = array();
                foreach ($result as $key => &$value) {
                    if ($key == 'id') {
                        $new_result[$key] = $value;
                        continue;
                    }

                    $key_parts = explode('_', $key);

                    if (count($key_parts) != 2) {
                        continue;
                    }

                    if ($key_parts[0] == 'store') {
                        $new_result[$key_parts[1]] = $value;
                    }
                }
                $result = $new_result;
            }
        }

        return $results;
    }

    // This function will rebuild the solr indexes
    public static function rebuild_all() {
        self::rebuild_users();
        self::commit();
        self::optimize();
    }

    public static function rebuild_users() {
        log_debug('Starting rebuild_users()');

        self::delete_bytype('user');

        $users = get_recordset('usr');
        safe_require('artefact', 'internal');
        $publicfields = array_keys(ArtefactTypeProfile::get_public_fields());

        while ($user = $users->FetchRow()) {
            if ($user['id'] == 0) {
                continue;
            }

            $doc = array(
                'id'                  => $user['id'],
                'owner'               => $user['id'],
                'type'                => 'user',
                'text_name'           => $user['preferredname'],
                'store_institution'   => $user['institution'],
                'store_email'         => $user['email'],
                'store_username'      => $user['username'],
                'store_preferredname' => $user['preferredname'],
                'store_firstname'     => $user['firstname'],
                'store_lastname'      => $user['lastname'],
            );
            if (empty($doc['text_name'])) {
                $doc['text_name'] = $user['firstname'] . ' ' . $user['lastname'];
            }

            self::add_document($doc);
        }

        $users->close();

        log_debug('Completed rebuild_users()');
    }

    public static function commit() {
        self::send_update('<commit />');
    }

    function optimize() {
        self::send_update('<optimize />');
    }


    public static function delete_bytype($type) {
        self::send_update('<delete><query>type:' . htmlentities($type) . '</query></delete');
    }

    /**
     * Takes an XML message and sends it to Solr's update handler
     * 
     * @param  string  The message to send
     */
    private static function send_update($message) {
        require_once('snoopy/Snoopy.class.php');
        $snoopy = new Snoopy;

        $url = get_config_plugin('search', 'solr', 'solrurl') . 'update';

        if (!$snoopy->submit($url, $message)) {
            throw new Exception('Request to solr failed');
        }
    }

    private static function send_query($query, $limit, $offset, $constraints = array(), $fields = '*') {
        $q = array();

        foreach ( $constraints as $key => $value ) {
            if(empty($value)) {
                continue;
            }
            if(is_array($value)) {
                $value = implode('" OR "', $value);
            }
            array_push($q,$key . ':("' . $value . '")');
        }

        require_once('snoopy/Snoopy.class.php');
        $client = new Snoopy;

        if(!empty($query)) {
            array_push($q, '('.$query.')');
        }

        $data = array(
            'q'      => join(' AND ',$q),
            'fl'     => $fields,
            'start'  => $offset,
            'rows'   => $limit,
        );

        $url = get_config_plugin('search', 'solr', 'solrurl') . 'select';

        if (!$client->submit($url, $data)) {
            throw new Exception('Request to solr failed');
        }

        if( $client->status != 200 ) {
            log_warn('solr_send_query(Solr Error)',$client->results);
            $result = array(
                'error'   => 'Bad repsponse from Solr (HTTP ' . $client->status . ')',
                'data' => array()
            );
            return $result;
        }

        $dom = new DOMDocument;
        if (!$dom->loadXML($client->results)) {
            log_warn('solr_send_query(Solr Error)',$client->results);
            $result = array(
                'error'   => 'Query parse error',
                'data' => array()
            );
            return $result;
        }

        $root = $dom->getElementsByTagName('result'); // get root node
        $root = $root->item(0);

        $results = array(
            'count'   => $root->getAttribute('numFound'),
            'offset'  => $offset,
            'limit'   => $limit,
            'data'    => array()
        );

        // loop over results
        foreach ( $root->childNodes as $node ) {
            if( $node->nodeType != XML_ELEMENT_NODE || $node->nodeName != 'doc' ) {
                log_debug('bad node');
                continue;
            }
            $result = array();
            // loop over fields
            foreach ( $node->childNodes as $field ) {
                if ($field->nodeType != XML_ELEMENT_NODE || ( $field->nodeName != 'str' && $field->nodeName != 'int' )) {
                    continue;
                }

                $value = $field->firstChild;
                if (empty($value)) {
                    continue;
                }
                $result[$field->getAttribute('name')] = $value->wholeText;
            }

            $results['data'][] = $result;
        }

        return $results;
    }

    private static function calculate_idtype(&$doc) {
        if(empty($doc['type']) || empty($doc['id'])) {
            throw new InvalidArgumentException('Solr object missing id or type while trying to calculate idtype field');
            return;
        }

        $doc['idtype'] = $doc['type'].'.'.$doc['id'];
    }

    private static function add_document($data) {
        self::calculate_idtype($data);

        $dom = new DOMDocument;

        $node_add = $dom->createElement('add');
        $dom->appendChild($node_add);

        $node_doc = $dom->createElement('doc');
        $node_add->appendChild($node_doc);

        foreach ( $data as $key => $value )
        {
            $node_field = $dom->createElement('field');
            $text = $dom->createTextNode($value);
            $node_field->appendChild($text);
            $node_field->setAttribute('name', $key);
            $node_doc->appendChild($node_field);
        }

        self::send_update($dom->saveXML());
    }
}

?>
