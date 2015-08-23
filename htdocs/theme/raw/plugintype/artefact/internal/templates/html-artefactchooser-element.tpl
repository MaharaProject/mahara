<div class="artefactchooser-item list-group-item list-group-item-default">
    {$formcontrols|safe}
    <label class="lead text-small mbs" for="{$elementname}_{$artefact->id}">
        {$artefact->title|str_shorten_text:60:true}
    </label>
    {if $artefact->ownerurl}({str tag=by section=view}
        <a href="{$artefact->ownerurl}" class="metadata">{$artefact->ownername}</a>)
    {/if}
    <div class="with-label text-small" for="{$elementname}_{$artefact->id}">
        {$artefact->description|str_shorten_html:80:true|strip_tags|safe}
    </div>
</div>
