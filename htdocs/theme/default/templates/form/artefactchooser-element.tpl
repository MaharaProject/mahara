    <tr title="{$artefact->hovertitle|escape}">
        <td style="width: 20px;">
            {if $selectone}<input type="radio" class="radio"id="{$elementname}_{$artefact->id}" name="{$elementname}" value="{$artefact->id|escape}"{if $value == $artefact->id} checked="checked"{/if}>
            {else}<input type="checkbox" id="{$elementname}_{$artefact->id}" name="{$elementname}[{$artefact->id}]"{if $value && in_array($artefact->id, $value)} checked="checked"{/if}>
            <input type="hidden" name="{$elementname}_onpage[]" value="{$artefact->id}">
            {/if}</td>
        <th><label for="{$elementname}_{$artefact->id}">{$artefact->title|escape}</label></th>
    </tr>
