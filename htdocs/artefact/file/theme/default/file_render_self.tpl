<div>
    <div class="fl filedata-icon"><a href="{$downloadpath|escape}"><img src="{$iconpath|escape}" alt="{$description|escape}"></a></div>
    <h4><a href="{$downloadpath|escape}">{$title|escape}</a></h4>
</div>

<table class="filedata">
    <tr><th>Type:</th><td>{$filetype}</td></tr>
    <tr><th>Owner:</th><td>{$owner}</td></tr>
    <tr><th>Created:</th><td>{$created}</td></tr>
    <tr><th>Last modified:</th><td>{$modified}</td></tr>
    <tr><th>Description:</th><td>{$description|escape}</td></tr>
    <tr><th>Size:</th><td>{$size|escape}</td></tr>
    <tr><th>{str tag=download section=artefact.file}:</th><td><a href="{$downloadpath|escape}">{str tag=download section=artefact.file}</a></td></tr>
</table>
