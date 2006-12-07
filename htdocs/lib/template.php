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
 * @subpackage core
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();
define('TEMPLATE_RENDER_READONLY', 1);
define('TEMPLATE_RENDER_EDITMODE', 2);
require_once('artefact.php');

function template_parse($templatename) {

    $t = array();
    
    $template = template_locate($templatename, false);
    
    $fragment = file_get_contents($template['fragment']);

    preg_match_all('/(.*?)\{\{(.*?)\}\}/xms', $fragment, $matches, PREG_SET_ORDER);
    
    $strlen = 0;
    $blockids = array();
    foreach ($matches as $m) {
        $temp = array('type'    => 'html',
                      'content' => $m[1],
                      );
        $t[] = $temp;
        $temp = array('type'    => 'block', 
                      'data'    => template_parse_block($m[2]),
                      );
        $t[] = $temp;
        $blockids[] = $temp['data']['id'];

        $strlen += strlen($m[0]);
           
    }

    if (count($blockids) != count(array_unique($blockids))) {
        $dups = array_unique(array_diff_assoc($blockids, array_unique($blockids)));
        throw new TemplateParserException("This template ($templatename) has duplicate block ids: " . implode(', ', $dups));
    }

    $temp = array('type'    => 'html',
                  'content' => substr($fragment, $strlen),
                  );

    $t[] = $temp;

    $template['parseddata'] = $t;
    return $template;
}

function template_parse_block($blockstr) {
    $data = array();
    $bits = explode(' ', $blockstr);

    // the first bit should be 'block'
    if ($bits[0] != 'block') {
        throw new TemplateParserException("Invalid block section $blockstr");
    }

    array_shift($bits);
    foreach ($bits as $b) {
        $keyvalue = explode('=', $b);
        $data[$keyvalue[0]] = substr($keyvalue[1], 1, -1);
    }

    if (!isset($data['id']) || empty($data['id']) || strpos($data['id'], 'tpl_') !== 0) {
        throw new TemplateParserException("Invalid block section $blockstr - must have an id beginning with tpl_");
    }

    if (!isset($data['type']) || empty($data['type'])) {
        throw new TemplateParserException("Invalid block section $blockstr - must have a type");
    }
    
    $types = array('artefact', 'label', 'title', 'author', 'description');
    if (!in_array($data['type'], $types)){
        throw new TemplateParserException("Invalid block section $blockstr (type " . $data['type'] 
                                           . " not one of " . implode(', ', $types));
    }
 
    if (!isset($data['tagtype'])) {
        $data['tagtype'] = 'div';
    }

    if ($data['type']  != 'artefact') {
        // no more validation to do.
        return $data;
    }

    if (isset($data['artefacttype'])) {
        if (!$plugin = get_field('artefact_installed_type', 'plugin', 'name', $data['artefacttype'])) {
            throw new TemplateParserException("artefacttype " . $data['artefacttype'] . " is not installed");
        }
     
        if (isset($data['format'])) { // check the artefacttype can render to this format.
            safe_require('artefact', $plugin);

            if (!artefact_can_render_to($data['artefacttype'], $data['format'])) {
                throw new TemplateParserException("Artefacttype " . $data['artefacttype'] . " can't render to format "
                                                   . $format['format']);
            }
        }
        
    }

    if (isset($data['plugintype'])) {
        try {
            safe_require('artefact', $data['plugintype']);
        }
        catch (Exception $e) {
            throw new TemplateParserException("Couldn't find plugin type " . $data['plugintype']);
        }
    }

    if (isset($data['defaultartefacttype'])) {
        if (isset($data['artefacttype']) && $data['artefacttype'] != $data['defaultartefacttype']) {
            throw new TemplateParserException("Default artefact type " . $data['defaultartefacttype']
                                               . " doesn't make sense given artefact type " . $data['artefacttype']);
        }
        else if (isset($data['plugintype']) 
                 && !in_array($data['defaultartefacttype'], 
                    call_static_method(generate_class_name($data['plugintype']), 'get_artefact_types'))) {
            throw new TemplateParserException("Default artefact type " . $data['defaultartefacttype']
                                               ." doesn't make sense given plugin type " . $data['plugintype']);
        }
        if (!$plugin = get_field('artefact_installed_type', 'plugin', 'name', $data['defaultartefacttype'])) {
            throw new TemplateParserException("Default artefact type  " . $data['defaultartefacttype'] 
                                               . " is not installed");
        }
        // look for a default format...
        if (!isset($data['defaultformat'])) {
            if (isset($data['format'])) {
                $data['defaultformat'] = $data['format'];
            }
            else {
                throw new TemplateParserException("Default artefact type " . $data['defaultartefacttype']
                                                   ." specified but with no format method (couldn't find in either "
                                                   ." default format, or fallback format field");
            }
        }
        // check the default artefact type can render to the given default format
        safe_require('artefact', $plugin);
        if (!artefact_can_render_to($data['defaultartefacttype'], $data['defaultformat'])) {
            throw new TemplateParserException("Default artefact type " . $data['defaultartefacttype'] 
                                               . " can't render to defaultformat " . $format['defaultformat']);
        }
        
        // check this default artefact is a 0 or 1 artefact
        if (!call_static_method(generate_artefact_class_name($data['defaultartefacttype']), 'is_0_or_1')) {
            throw new TemplateParserException("Default artefact type " . $data['defaultartefacttype']
                                               ." is not a 0 or 1 type artefact");
        }
    }

    // @todo resizing stuff maybe
    
    return $data;        
}

function template_locate($templatename, $fetchdb=true) {

    // check dataroot first for custom templates
    $templatedir = 'templates/' . $templatename . '/';
    $fragment = $templatedir . 'fragment.template';
    $css = $templatedir . 'fragment.css';

    $template = array();

    $thumbnails = array('jpg'  => 'image/jpeg',
                        'jpeg' => 'image/jpeg',
                        'png'  => 'image/png',
                        'gif'  => 'image/gif');

    if ($path = realpath(get_config('dataroot') . $fragment)) {
        $template['fragment'] = $path;
        if (is_readable(get_config('dataroot') . $css)) {
            $template['css'] = get_config('dataroot') . $css;
        }
        foreach ($thumbnails as $t => $contenttype) {
            if (is_readable(get_config('dataroot') . $templatedir . 'thumbnail.' . $t)) {
                $template['thumbnailcontenttype'] = $contenttype;
                $template['thumbnail'] = get_config('dataroot') . $templatedir . 'thumbnail.' . $t;
                break;
            }
        }
        if ($dbstuff = get_record('template', 'name', $templatename)) {
            $template['cacheddata'] = unserialize($dbstuff->cacheddata);
            $template['category'] = $dbstuff->category;
        }
        $template['location'] = get_config('datarootroot') . 'templates/' . $templatename . '/';
        return $template;
    }

    if ($path = realpath(get_config('libroot') . $fragment)) {
        $template['fragment'] = $path;
        if (is_readable(get_config('libroot') . $css)) {
            $template['css'] = get_config('libroot') . $css;
        }
        foreach ($thumbnails as $t => $contenttype) {
            if (is_readable(get_config('libroot') . $templatedir . 'thumbnail.' . $t)) {
                $template['thumbnailcontenttype'] = $contenttype;
                $template['thumbnail'] = get_config('libroot') . $templatedir . 'thumbnail.' . $t;
                break;
            }
        }
        if ($dbstuff = get_record('template', 'name', $templatename)) {
            $template['cacheddata'] = unserialize($dbstuff->cacheddata);
            $template['category'] = $dbstuff->category;
        }
        $template['location'] = get_config('libroot') . 'templates/' . $templatename . '/';
        return $template;
    }

    throw new TemplateParserException("Invalid template name $templatename, couldn't find");
}

/**
 * renders a template in either edit mode or read only mode
 *
 * @param array $template a parsed template see {@link template_parse}
 * @param mode either TEMPLATE_RENDER_READONLY or TEMPLATE_RENDER_EDITMODE
 * @param array 
 *
 * @returns string the html of the rendered template
 */
function template_render($template, $mode, $data=array()) {
    if (isset($template['parseddata'])) {
        $td = $template['parseddata'];
    }
    else {
        $td = $template['cacheddata'];
    }

    $droplist = array();
    $html = '';

    foreach ($td as $t) {
        if ($t['type'] == 'html') {
            $html .= $t['content'];
        }
        else {
            if ($mode == TEMPLATE_RENDER_READONLY) {
                $html .= 'READONLY';
            }
            else {
                $t = $t['data'];

                $attr = array(
                    'id'    => $t['id'],
                    'class' => array('block'),
                );

                if (isset($t['width']) && isset($t['height'])) {
                    $attr['style'][] = 'width: ' . $t['width'] . 'px;height: ' . $t['height'] . 'px;';
                }

                $block = '';

                switch ($t['type']) {
                    case 'label';
                        $block .= template_render_label($t, $data);
                        break;
                    case 'title';
                        if (isset($data['title'])) {
                            $block .= hsc($data['title']);
                        }
                        break;
                    case 'author';
                        if (isset($data['author'])) {
                            $block .= hsc($data['author']);
                        }
                        break;
                    case 'description';
                        if (isset($data['description'])) {
                            $block .= hsc($data['description']);
                        }
                        break;
                    case 'artefact';
                        $classes = array('block');

                        // @todo, this shouldn't be hardcoded
                        $droplist[$t['id']] = array('render_full');

                        // @todo need to populate with data if it's available
                        if ( isset($data[$t['id']]) ) {
                            $artefact = artefact_instance_from_id($data[$t['id']]['id']);
                            // @todo, custom rendering
                            $block .= $artefact->render(FORMAT_ARTEFACT_LISTSELF, null);
                        }
                        else {
                            $block .= '<i>' . get_string('empty_block', 'view') . '</i>';
                        }

                        break;
                }

                // span or div?
                if (isset($t['tagtype']) && $t['tagtype'] == 'span') {
                    $html .= '<span';
                    $html .= template_render_attributes($attr);
                    $html .= '>';
                    $html .= $block;
                    $html .= '</span>';
                }
                else {
                    $html .= '<div';
                    $html .= template_render_attributes($attr);
                    $html .= '>';
                    $html .= $block;
                    $html .= '</div>';
                }
            }
        }
    }

    $droplist = json_encode($droplist);
    $spinner_url = json_encode(theme_get_image_path('loading.gif'));
    $wwwroot = get_config('wwwroot');

    $json_emptylabel = json_encode(get_string('emptylabel', 'view'));
    $javascript = <<<EOF
<script type="text/javascript">
    var droplist = $droplist;

    function setLabel(element) {
        var value = '';
        if ($(element).labelValue) {
            value = $(element).labelValue;
        }

        var input = INPUT({'type': 'text', 'id': element + '_labelinput'});
        input.value = value;
        replaceChildNodes(element, input);
        input.focus();

        connect(input, 'onkeypress', function (e) {
            if (e.key().code == 13) {
                saveLabel(element);
                e.stop();
            }
            if (e.key().code == 27) {
                saveLabel(element, true);
                e.stop();
            }
            return false;
        });
    }

    function saveLabel(element, revert) {
        if (!revert) {
            $(element).labelValue = $(element + '_labelinput').value;
        }

        if ( $(element).labelValue ) {
            var label = SPAN({'class': 'clickable'}, $(element).labelValue);
            connect(label, 'onclick', function () { setLabel(element) });
            replaceChildNodes(element, label, INPUT({'type': 'hidden', 'name': 'template[' + $(element).id + '][value]', 'value': $(element).labelValue}));
        }
        else {
            var label = createDOM('EM', {'class': 'clickable'}, $json_emptylabel);
            connect(label, 'onclick', function () { setLabel(element) });
            replaceChildNodes(element, label);
        }
    }

    function blockdrop(element, target) {
        replaceChildNodes(target, IMG({ src: {$spinner_url} }));
        var d = loadJSONDoc('{$wwwroot}json/renderartefact.php', {'id': element.artefactid} );
        d.addCallbacks(
            function (response) {
                target.innerHTML = response.data;
                debugObject(target);
                appendChildNodes(target,
                    INPUT({'type': 'hidden', 'name': 'template[' + target.id + '][id]', 'value': element.artefactid })
                );
            },
            function (error) {
                alert('TODO: error');
            }
        );
    }

    addLoadEvent(function () {
        for ( id in droplist ) {
            new Droppable(id, {
                accept: droplist[id],
                ondrop: blockdrop,
                hoverclass: 'block_targetted',
                activeclass: 'block_potential'
            });
        }
    });

</script>
EOF;

    return $javascript . $html;
}

/*
 * @todo: some documentation
 */
function template_render_label($block, $data) {
    if (isset($data[$block['id']]['value'])) {
        return '<script type="text/javascript">addLoadEvent(function() { $(' . json_encode($block['id']) . ').labelValue = ' . json_encode($data[$block['id']]['value']) . ';});</script><span class="clickable" onclick="setLabel(' . hsc(json_encode($block['id'])) . ');">' . hsc($data[$block['id']]['value']) . '</span>';
    }
    else {
        return '<em class="clickable" onclick="setLabel(' . hsc(json_encode($block['id'])) . ');">' . get_string('emptylabel', 'view') . '</em>';
    }
}

// @todo : some documentation
function template_render_attributes($attr) {
    if (!is_array($attr) || count($attr) == 0) {
        return '';
    }

    $html = '';

    foreach ( $attr as $key => $value ) {
        if (is_array($value)) {
            $html .= ' ' . $key . '="' . join(' ', array_map('hsc', $value)) . '"';
        }
        else {
            $html .= ' ' . $key . '="' . hsc($value) . '"';
        }
    }

    return $html;
}

?>
