    <tr>
        <td class="iconcell" rowspan="2">
            {$formcontrols|safe}
        </td>
        <th><label for="{$elementname}_{$artefact->id}">{$artefact->title|str_shorten_text:60:true}</label></th>
    </tr>
    <tr>
        <td>{$artefact->description|str_shorten_html:80:true|strip_tags|safe}</td>
    </tr>
