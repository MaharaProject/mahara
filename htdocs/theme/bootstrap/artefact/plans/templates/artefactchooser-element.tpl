<div class="fullwidth list-group-item">
    {$formcontrols|safe}
    <label for="{$elementname}_{$artefact->id}" title="{$artefact->title|strip_tags|str_shorten_text:60:true|safe}">{$artefact->title|strip_tags|safe}</label>
    <div class="with-label text-small">{$artefact->description|str_shorten_html|safe}</div>
</div>