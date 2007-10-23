    <tr title="{$artefact->hovertitle|escape}">
        <td style="width: 20px;">{if $selectone}<input type="radio" class="radio"id="{$elementname}_{$artefact->id}" name="{$elementname}" value="{$artefact->id|escape}"{if $value == $artefact->id} checked="checked"{/if}>{else}<input type="checkbox">{/if}</td>
        <td style="width: 22px;"><img src="{$artefact->icon|escape}" alt="*"></td>
        <th><label for="{$elementname}_{$artefact->id}">{if $artefact->description}{$artefact->description|escape}{else}{$artefact->title|escape}{/if}</label></th>
    </tr>
