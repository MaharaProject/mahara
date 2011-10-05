    <tr>
        <td class="iconcell" rowspan="2">
            {$formcontrols|safe}
        </td>
        <th><label for="{$elementname}_{$artefact->id}">{$artefact->title|str_shorten_text:60:true}</label></th>
        <td>{if $artefact->ownerurl}({str tag=by section=view} <a href="{$artefact->ownerurl}">{$artefact->ownername}</a>){/if}</td>
    </tr>
    <tr>
        <td colspan=2>{$artefact->description|str_shorten_html:80:true|strip_tags|safe}</td>
    </tr>
