    <tr>
        <td class="iconcell" rowspan="2">
            {$formcontrols|safe}
        </td>
        <th><label for="{$elementname}_{$artefact->id}" title="{$artefact->title|strip_tags|str_shorten_text:60:true|safe}">{str tag=$artefact->artefacttype section=artefact.resume}</label></th>
    </tr>
    <tr>
        <td>{$artefact->description|str_shorten_html:100:true|strip_tags|safe}</td>
    </tr>
