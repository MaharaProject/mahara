    <tr title="{$artefact->hovertitle|escape}">
        <td style="width: 20px;">
            {$formcontrols}
        </td>
        <td style="width: 22px;"><label for="{$elementname}_{$artefact->id}"><img src="{$artefact->icon|escape}" alt="*"></label></td>
        <th><label for="{$elementname}_{$artefact->id}">{if $artefact->description}{$artefact->description|escape}{else}{$artefact->title|escape}{/if}{if $artefact->artefacttype == 'profileicon'} ({str tag=profileicon section=artefact.internal}){/if}</label></th>
    </tr>
