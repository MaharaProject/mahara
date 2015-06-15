<div>
    {$formcontrols|safe}
    <label for="{$elementname}_{$artefact->id}">
    	{$artefact->title|str_shorten_text:60:true}
    </label>
    {if $artefact->ownerurl}({str tag=by section=view} 
        <a href="{$artefact->ownerurl}" class="metadata">{$artefact->ownername}</a>)
    {/if}
    <div class="with-label text-small">
    	{$artefact->description|str_shorten_html:80:true|strip_tags|safe}
    </div>
</div>
