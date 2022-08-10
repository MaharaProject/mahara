<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-blog-export-leap
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/*
 * For more information about blog LEAP export, see:
 * https://wiki.mahara.org/wiki/Developer_Area/Import//Export/LEAP_Export/Blog_Artefact_Plugin
 */

defined('INTERNAL') || die();

class LeapExportElementBlogpost extends LeapExportElement {

    public function get_content_type() {
        return 'html';
    }

    public function get_categories() {
        if (!$this->artefact->get('published')) {
            return array(
                array(
                    'scheme' => 'readiness',
                    'term'   => 'Unready',
                )
            );
        }
        return array();
    }
}

class LeapExportElementBlog extends LeapExportElement {

    public function get_leap_type() {
        return 'selection';
    }

    public function get_categories() {
        return array(
            array(
                'scheme' => 'selection_type',
                'term'   => 'Blog',
            )
        );
    }

    public function get_content_type() {
        return 'html';
    }
}

class LeapExportTaggedPosts {

    /**
    * This function inserts tag data into a taggedpost's block configdata
    * The tag is put into the configdata as an array of ['tagsin'] or ['tagsout']
    * based on its type (included: 1 or excluded: 0)
    * @param array &$config    by reference, the configdata array containing block data
    * @return array &$config   the configdata array
    */
    public function get_blocktype_export_data(&$config, $view, $oldlayout=false) {
        if ($oldlayout) {
           foreach ($config as &$row) {
               foreach($row as $columns => &$column) {
                   foreach ($column as &$blocks) {
                       foreach($blocks as &$block) {
                           if ($block['blocktype'] == 'taggedposts') {
                                $sql = '
                                SELECT tag, tagtype
                                FROM {blocktype_taggedposts_tags}
                                WHERE block_instance = ?
                                ';
                                if ($tags = get_records_sql_array($sql, array($block['id']))) {
                                    foreach ($tags as $tag) {
                                        if ($tag->tagtype == PluginBlocktypeTaggedposts::TAGTYPE_INCLUDE) {
                                            isset($block['config']['tagsin']) ? array_push($block['config']['tagsin'], $tag->tag) : $block['config']['tagsin'] = array($tag->tag);
                                        }
                                        else {
                                            $excludedtag = '-' . $tag->tag;
                                            isset($block['config']['tagsout']) ? array_push($block['config']['tagsout'], $excludedtag) : $block['config']['tagsout'] = array($excludedtag);
                                        }
                                    }
                                }
                            }
                       }#end foreach blocks
                   }#end foreach column
               }#end foreach row
           }#end foreach config
        }
        else {
            $sql = '
            SELECT bid.block, bid.positionx, bid.positiony, tp.tag, tp.tagtype
            FROM {block_instance_dimension} AS bid INNER JOIN {block_instance} bi on bid.block = bi.id
            INNER JOIN {blocktype_taggedposts_tags} AS tp
            ON bid.block = tp.block_instance WHERE bi.view = ?
            ';
            if ($taggedpostplacement = get_records_sql_array($sql, array($view))) {
                foreach ($taggedpostplacement as $tpp) {
                    foreach ($config as &$cg) {
                        if ($cg['positionx'] == $tpp->positionx && $cg['positiony'] == $tpp->positiony) {
                            if ($tpp->tagtype == PluginBlocktypeTaggedposts::TAGTYPE_INCLUDE) {
                                isset($cg['config']['tagsin']) ? array_push($cg['config']['tagsin'], $tpp->tag) : $cg['config']['tagsin'] = array($tpp->tag);
                            }
                            else {
                                $excludedtag = '-' . $tpp->tag;
                                isset($cg['config']['tagsout']) ? array_push($cg['config']['tagsout'], $excludedtag) : $cg['config']['tagsout'] = array($excludedtag);
                            }
                        }
                    }
                }
            }
        }

        return $config;
    }

}
