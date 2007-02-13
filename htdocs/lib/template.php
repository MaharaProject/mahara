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

/**
 * render templates in readonly mode (used for viewing templates)
 */
define('TEMPLATE_RENDER_READONLY', 1);
 
/**
 * render templates in editmode mode (for view wizard)
 */
define('TEMPLATE_RENDER_EDITMODE', 2);

/**
 * display format for author names in views - firstname
 */
define('FORMAT_NAME_FIRSTNAME', 1);

/**
 * display format for author names in views - lastname
 */
define('FORMAT_NAME_LASTNAME', 2);

/**
 * display format for author names in views - firstname lastname
 */
define('FORMAT_NAME_FIRSTNAMELASTNAME', 3);

/**
 * display format for author names in views - preferred name
 */
define('FORMAT_NAME_PREFERREDNAME', 4);

/**
 * display format for author names in views - student id 
*/
define('FORMAT_NAME_STUDENTID', 5);

/**
 * display format for author names in views - obeys display_name 
 */
define('FORMAT_NAME_DISPLAYNAME', 6);

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
    $bits = preg_split('/\s+/', $blockstr);

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
        else if (
            isset($data['plugintype']) 
            && !in_array(
                $data['defaultartefacttype'], 
                call_static_method(generate_class_name('artefact', $data['plugintype']), 'get_artefact_types')
            )
        ) {
            throw new TemplateParserException(
                "Default artefact type " . $data['defaultartefacttype'] ." doesn't make sense given plugin type " . $data['plugintype']
                . '. Default artefact type should be one of: '
                . join(', ', call_static_method(generate_class_name('artefact', $data['plugintype']), 'get_artefact_types'))
            );
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
        
        // check this default artefact is a singular artefact
        if (!call_static_method(generate_artefact_class_name($data['defaultartefacttype']), 'is_singular')) {
            throw new TemplateParserException("Default artefact type " . $data['defaultartefacttype']
                                               ." is not a singular type artefact");
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
function template_render($template, $mode, $data=array(), $view_id=null) {
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
            $t = $t['data'];
            
            $attr = array(
                'id'    => $t['id'],
                'class' => array(),
            );

            $options = array();

            if ($view_id && $mode == TEMPLATE_RENDER_READONLY) {
                $options['viewid'] = $view_id;
            }

            if (isset($t['width']) && isset($t['height'])) {
                $attr['style'][] = 'width: ' . $t['width'] . 'px;height: ' . $t['height'] . 'px;';
                $options['width'] = $t['width'];
                $options['height'] = $t['height'];
            }
            
            $block = '';
            
            switch ($t['type']) {
                case 'label';
                    if ($mode == TEMPLATE_RENDER_EDITMODE) {
                        $attr['class'][] = 'block';
                        $block .= template_render_label_editmode($t, $data);
                    }
                    else {
                        if (isset($data[$t['id']])) {
                            $block .= $data[$t['id']]['value'];
                        }
                    }
                    break;
                case 'title';
                    if (isset($data['title'])) {
                        $block .= hsc($data['title']);
                    }
                    break;
                case 'author';
                        if (isset($data['ownerformat'])) {
                            $block .= template_render_author($data['ownerformat']);
                        }
                        else {
                            $block .= $data['author'];
                        }
                    break;
                case 'description';
                    if (isset($data['description'])) {
                        $block .= $data['description'];
                    }
                    break;
                case 'artefact';
                    $attr['class'][] = 'block';

                    $droplist[$t['id']] = array(
                        'artefacttype' => isset($t['artefacttype']) ? $t['artefacttype'] : null,
                        'plugintype'   => isset($t['plugintype'])   ? $t['plugintype'] : null,
                        'format'       => isset($t['format'])       ? $t['format'] : null,
                        'options'      => $options,
                    );

                    if ( isset($data[$t['id']]) ) {
                        $format = FORMAT_ARTEFACT_LISTSELF;

                        if (isset($data[$t['id']]['format'])) {
                            $format = $data[$t['id']]['format'];
                        }

                        if ($format == FORMAT_ARTEFACT_LISTSELF) {
                            $droplist[$t['id']]['oldformat'] = $droplist[$t['id']]['format'];
                            $droplist[$t['id']]['format'] = FORMAT_ARTEFACT_LISTSELF;
                        }

                        $block .= template_render_artefact_block($t['id'], $data[$t['id']]['id'], $format, $options, $mode);
                    }
                    else if ( isset($t['defaultartefacttype']) ) {
                        $artefact = null;

                        try {
                            $artefact = artefact_instance_from_type($t['defaultartefacttype']);
                        }
                        catch (ArtefactNotFoundException $e) {
                        }

                        if ($artefact === null) {
                            if ($mode == TEMPLATE_RENDER_EDITMODE) {
                                $block .= template_render_empty_artefact_block();
                            }
                        }
                        else {
                            $block .= template_render_artefact_block($t['id'], $artefact, $t['defaultformat'], $options, $mode);
                        }
                    }
                    else {
                        if ($mode == TEMPLATE_RENDER_EDITMODE) {
                            $block .= template_render_empty_artefact_block();
                        }
                    }
                    
                    break;
            }

            if ($mode != TEMPLATE_RENDER_READONLY || !empty($block)) {
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
    $javascript = '';
    if ($mode == TEMPLATE_RENDER_EDITMODE) {
        $droplist = json_encode($droplist);
        $spinner_url = json_encode(theme_get_url('images/loading.gif'));
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
        connect(input, 'onblur', function (e) {
            saveLabel(element);
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

    function removeListItem(x) {
        var ul = x.parentNode.parentNode;
        if (ul.childNodes.length == 1) {
            ul.parentNode.moveTarget.acceptData.format = ul.parentNode.moveTarget.acceptData.oldformat;
            replaceChildNodes(ul.parentNode, SPAN({ 'class': 'empty_block' }, get_string('empty_block')));
        }
        else {
            removeElement(x.parentNode);
        }
    }

    function blockdrop(element, target, format) {
        var srcData = element.moveSource.acceptData;
        var dstData = target.moveTarget.acceptData;

        if ( dstData.format ) {
            format = dstData.format;
        }

        if ( srcData.rendersto.length == 1 ) {
            format = srcData.rendersto[0];
        }

        if (format) {
            var real_target = target;

            if (format == 'listself') {
                if(target.childNodes[0].nodeName != 'UL') {
                    real_target = LI();
                    replaceChildNodes(target, UL(null, real_target));
                    dstData.oldformat = dstData.format;
                    dstData.format = ['listself'];
                }
                else {
                    real_target = LI();
                    appendChildNodes(target.childNodes[0], real_target);
                }
            }

            replaceChildNodes(real_target, IMG({ src: {$spinner_url} }));
            var render_options = {
                'id': element.artefactid,
                'format': format
            };
            for (key in dstData.options) {
                render_options['options[' + key + ']'] = dstData.options[key];
            }
            sendjsonrequest('{$wwwroot}json/renderartefact.php', render_options, 'GET', function (response) {
                if (!response.error) {
                    real_target.innerHTML = response.data;
                    forEach(getElementsByTagAndClassName('script', null, real_target), function(script) {
                        eval(script.innerHTML);
                    });
                    if(format == 'listself') {
                        appendChildNodes(real_target, A({ href: '', onclick: 'removeListItem(this); return false;' }, '[x]'));
                    }
                    appendChildNodes(real_target,
                                     INPUT({'type': 'hidden', 'name': 'template[' + target.id + '][id][]', 'value': element.artefactid }),
                                     INPUT({'type': 'hidden', 'name': 'template[' + target.id + '][format]', 'value': format })
                                     );
                }
            });
        }
        else {
            formatlist = [];
            forEach (srcData.rendersto, function (fmt) {
                var li = LI({'class': 'clickable'}, get_string('format.' + fmt))
                connect(li, 'onclick', function() { blockdrop(element,target,fmt); });
                formatlist.push(li);
            });
            // need to pick a format
            replaceChildNodes(target, P(null,get_string('chooseformat')), UL(null, formatlist));
        }
    }

    addLoadEvent(function () {
        for ( id in droplist ) {
            new MoveTarget(id, {
                ondrop: blockdrop,
                hoverClass: 'block_targetted',
                activeClass: 'block_potential',
                acceptData: droplist[id],
                acceptFunction: function (src, dst) {
                    if (dst.plugintype && dst.plugintype != src.plugin) {
                        return false;
                    }
                    if (dst.artefacttype && dst.artefacttype != src.type) {
                        return false;
                    }
                    if (dst.format && !some(src.rendersto, function (rtype) { return dst.format == rtype; })) {
                        return false;
                    }
                    return true;
                }
            });
        }
    });

</script>
EOF;
    }
    return $javascript . $html;
}

/*
 * @todo: some documentation
 */
function template_render_label_editmode($block, $data) {
    if (isset($data[$block['id']]['value'])) {
        return '<script type="text/javascript">addLoadEvent(function() { $(' . json_encode($block['id']) . ').labelValue = ' . json_encode($data[$block['id']]['value']) . ';});</script><span class="clickable" onclick="setLabel(' . hsc(json_encode($block['id'])) . ');">' . hsc($data[$block['id']]['value']) . '</span>'
        . '<input type="hidden" name="template[' . $block['id'] . '][value]" value="' . hsc($data[$block['id']]['value']) . '">';
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
        if (is_array($value) && count($value) > 0) {
            $html .= ' ' . $key . '="' . join(' ', array_map('hsc', $value)) . '"';
        }
        else if (!is_array($value)) {
            $html .= ' ' . $key . '="' . hsc($value) . '"';
        }
    }

    return $html;
}

/**
 * This function formats a user's name
 * according to their view preference
 *
 * @param constant $format FORMAT_NAME_(FIRSTNAME|FIRSTNAMELASTNAME|LASTNAME|PREFERREDNAME|STUDENTID)
 * @param object $user must contain those ^^ fields (or id, in which case a db lookup will be done)
 *
 * @return string formatted name
 */
function template_format_owner($format, $user) {
    
    if (is_int($user)) {
        $user = get_record('usr', 'id', $user);
    }

    if (!is_object($user)) {
        return ''; // @todo throw exception?
    }

    switch ($format) {
        case FORMAT_NAME_FIRSTNAME:
            return $user->firstname;
        case FORMAT_NAME_LASTNAME:
            return $user->lastname;
        case FORMAT_NAME_FIRSTNAMELASTNAME:
            return $user->firstname . ' ' . $user->lastname;
        case FORMAT_NAME_PREFERREDNAME:
            return $user->preferredname;
        case FORMAT_NAME_STUDENTID:
            return $user->studentid;
        case FORMAT_NAME_DISPLAYNAME:
        default:
            return display_name($user);
    }
}

function template_render_empty_artefact_block() {
    return '<span class="empty_block">' . get_string('empty_block', 'view') . '</span>';
}

/**
 * This function renders an artefact block
 *
 * @param string name of the block
 * @param mixed the artefact(s) to render (can either be an ArtefactType
 * object, an integer artefact id, or an array of artefact ids)
 * @param string format to render the artefact(s) to
 *
 * @return string html rendered data
 */
function template_render_artefact_block($blockname, $artefact, $format, $options = array(), $mode) {
    $block = '';

    $options['blockid'] = $blockname;

    if ($artefact instanceof ArtefactType) {
        $rendered = $artefact->render($format, $options);
        $block .= $rendered['html'];
        if ($mode == TEMPLATE_RENDER_EDITMODE) {
            $block .= '<input type="hidden" name="template[' . $blockname . '][id]" value="' . hsc($artefact->get('id')) . '">';
            $block .= '<input type="hidden" name="template[' . $blockname . '][format]" value="' . hsc($format) . '">';
        }
    }
    else if ($format == FORMAT_ARTEFACT_LISTSELF) {
        if (!is_array($artefact)) {
            $artefact = array($artefact);
        }
        $block .= '<ul>';
        foreach ($artefact as $id) {
            $block .= '<li>';
            $instance = artefact_instance_from_id($id);
            $rendered = $instance->render($format, $options);
            $block .= $rendered['html'];
            if ($mode == TEMPLATE_RENDER_EDITMODE) {
                $block .= '<a href="" onclick="removeListItem(this);return false;">[x]</a>';
                $block .= '<input type="hidden" name="template[' . $blockname . '][id][]" value="' . hsc($instance->get('id')) . '">';
                $block .= '<input type="hidden" name="template[' . $blockname . '][format]" value="' . hsc($format) . '">';
            }
            $block .= '</li>';
        }
        $block .= '</ul>';
    }
    else {
        if (is_array($artefact)) {
            $artefact = $artefact[0];
        }

        $artefact = artefact_instance_from_id($artefact);
        $rendered = $artefact->render($format, $options);
        $block .= $rendered['html'];
        if ($mode == TEMPLATE_RENDER_EDITMODE) {
            $block .= '<input type="hidden" name="template[' . $blockname . '][id]" value="' . hsc($artefact->get('id')) . '">';
            $block .= '<input type="hidden" name="template[' . $blockname . '][format]" value="' . hsc($format) . '">';
        }
    }

    return $block;
}

function template_render_author($format) {
    global $USER;
    switch ($format) {
        case FORMAT_NAME_FIRSTNAME:
            return hsc($USER->get('firstname'));
            break;
        case FORMAT_NAME_LASTNAME:
            return hsc($USER->get('firstname'));
            break;
        case FORMAT_NAME_FIRSTNAMELASTNAME:
            return hsc(full_name());
            break;
        case FORMAT_NAME_PREFERREDNAME:
            return hsc($USER->get('preferredname'));
            break;
        case FORMAT_NAME_STUDENTID:
            return hsc((string)get_field('artefact', 'title', 'owner', $USER->get('id'), 'artefacttype', 'studentid'));
            break;
        default:
            return hsc(display_name($USER));
            break;
    }
}

?>
