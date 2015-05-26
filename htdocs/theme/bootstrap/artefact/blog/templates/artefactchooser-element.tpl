<div class="checkbox fullwidth">
    {$formcontrols|safe}
    <label for="{$elementname}_{$artefact->id}">{$artefact->title}{if $artefact->draft} [{str tag=draft section=artefact.blog}]{/if}
        <span class="metadata">({if $artefact->blog}{$artefact->blog}{/if})</span>
    </label>
    {if $artefact->description}
    <div class="text-small with-label">{$artefact->description|clean_html|safe}</div>
    {/if}
</div>