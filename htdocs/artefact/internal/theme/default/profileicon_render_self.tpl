<div class="fr filedata-icon" style="text-align: center;">
    <h4>Preview</h4>
    <img src="{$previewpath|escape}" alt="">
</div>
<div>
    <div class="fl filedata-icon"><a href="{$downloadpath|escape}"><img src="{$iconpath|escape}" alt="{$description|escape}"></a></div>
    <h4><a href="{$downloadpath|escape}">{$title|escape}</a></h4>
</div>

<table class="filedata">
    <tr><th>{str tag=type}:</th><td>{str tag=$artefacttype section=artefact.internal}</td></tr>
    <tr><th>{str tag=owner}:</th><td>{$owner}</td></tr>
    <tr><th>{str tag=created}:</th><td>{$created}</td></tr>
    <tr><th>{str tag=lastmodified}:</th><td>{$modified}</td></tr>
    <tr><th>{str tag=description}:</th><td>{$description|escape}</td></tr>
    <tr><th>{str tag=size}:</th><td>{$size|escape}</td></tr>
    <tr><th>{str tag=download}:</th><td><a href="{$downloadpath|escape}">{str tag=download}</a></td></tr>
</table>
