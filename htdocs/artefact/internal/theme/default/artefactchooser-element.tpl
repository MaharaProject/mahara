    <tr>
        <td style="width: 20px;" rowspan="2">
            {if $selectone}<input type="radio" class="radio"id="{$elementname}_{$artefact->id}" name="{$elementname}" value="{$artefact->id|escape}"{if $value == $artefact->id} checked="checked"{/if}>
            {else}<input type="checkbox" id="{$elementname}_{$artefact->id}" name="{$elementname}[{$artefact->id}]"{if $value && in_array($artefact->id, $value)} checked="checked"{/if}>
            <input type="hidden" name="{$elementname}_onpage[]" value="{$artefact->id}">
            {/if}</td>
        <th><label for="{$elementname}_{$artefact->id}" title="{$artefact->title|strip_tags|substr:0:60|escape}">{str tag=$artefact->artefacttype section=artefact.internal}</label></th>
    </tr>
    <tr>
        <td>{if $artefact->description}{$artefact->description}{/if}</td>
    </tr>
