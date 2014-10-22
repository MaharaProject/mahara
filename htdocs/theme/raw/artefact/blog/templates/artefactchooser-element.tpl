    <tr>
        <td class="iconcell" rowspan="2">
            {$formcontrols|safe}
        </td>
        <th><label for="{$elementname}_{$artefact->id}">{if $artefact->blog}{$artefact->blog}: {/if}{$artefact->title}{if $artefact->draft} [{str tag=draft section=artefact.blog}]{/if}</label></th>
    </tr>
    <tr>
        <td>{if $artefact->description}{$artefact->description|clean_html|safe}{/if}</td>
    </tr>