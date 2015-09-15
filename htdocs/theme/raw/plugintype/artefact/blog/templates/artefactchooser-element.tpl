<div class="artefactchooser-item list-group-item list-group-item-default">
    {$formcontrols|safe}
    <label for="{$elementname}_{$artefact->id}">
        {$artefact->title}
        {if $artefact->draft} 
        [{str tag=draft section=artefact.blog}]
        {/if}
        <span class="text-midtone text-small">{if $artefact->blog}({$artefact->blog}){/if}</span>
    </label>
    {if $artefact->description}
    <div class="text-small detail">
        {$artefact->description|clean_html|safe}</div>
    {/if}
</div>