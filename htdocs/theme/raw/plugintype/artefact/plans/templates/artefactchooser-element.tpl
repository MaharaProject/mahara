<div class="artefactchooser-item list-group-item list-group-item-default">
    {$formcontrols|safe}
    <label for="{$elementname}_{$artefact->id}" title="{$artefact->title|strip_tags|str_shorten_text:60:true|safe}">{$artefact->title|strip_tags|safe}</label>
    <div class="text-small detail">{$artefact->description|str_shorten_html|safe}</div>
</div>