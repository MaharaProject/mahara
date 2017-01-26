<div class="artefactchooser-item list-group-item list-group-item-default">
    {$formcontrols|safe}
    <label class="lead text-small" for="{$elementname}_{$artefact->id}">
        {$artefact->title|str_shorten_text:60:true}
    </label>
    {if $artefact->ownerurl}
    <span class="text-small text-midtone">({str tag=by section=view} <a href="{$artefact->ownerurl}">{$artefact->ownername}</a>)</span>
    {/if}
    <div class="text-small detail" for="{$elementname}_{$artefact->id}">
        {$artefact->description|str_shorten_html:120:true|strip_tags|safe}
    </div>
</div>
