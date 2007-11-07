    <tr>
        <td style="width: 20px;" rowspan="2">
            {$formcontrols}
        </td>
        <th><label for="{$elementname}_{$artefact->id}">{if $artefact->blog}{$artefact->blog|escape}: {/if}{$artefact->title|escape}</label></th>
    </tr>
    <tr>
        <td>{if $artefact->description}{$artefact->description}{/if}</td>
    </tr>
