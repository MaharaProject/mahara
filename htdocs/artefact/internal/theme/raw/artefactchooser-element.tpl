    <tr>
        <td class="iconcell" rowspan="2">
            {$formcontrols|safe}
        </td>
        <th><label for="{$elementname}_{$artefact->id}" title="{$artefact->title|strip_tags|str_shorten_text:60:true|safe}">{str tag=$artefact->artefacttype section=artefact.internal}</label></th>
    </tr>
    <tr>
        <td>{if $artefact->description}{$artefact->description}{/if}</td>
    </tr>
