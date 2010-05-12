{auto_escape off}
    <tr title="{$artefact->hovertitle|escape}">
        <td>
            {$formcontrols}
        <td>
        <th><label for="{$elementname}_{$artefact->id}">{$artefact->title|escape}</label></th>
    </tr>
{/auto_escape}
