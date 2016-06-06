<div class="artefactchooser-item list-group-item list-group-item-default">
    {$formcontrols|safe}
    <label for="{$elementname}_{$artefact->id}">
        {$artefact->title}
        {if $artefact->draft}
        [{str tag=draft section=artefact.blog}]
        {/if}
        <span class="text-midtone text-small">{if $artefact->blog}({$artefact->blog}){/if}</span>
    </label>
    {if $artefact->group}({str tag="bygroup" section="artefact.blog" arg1="$artefact->groupurl" arg2="$artefact->groupname"}){/if}
    {if $artefact->institution}({str tag="byinstitution" section="artefact.blog" arg1="$artefact->institutionurl" arg2="$artefact->institutionname"}){/if}
    {if $artefact->description}
    <div class="text-small detail">
        {$artefact->description|clean_html|safe}</div>
    {/if}
</div>