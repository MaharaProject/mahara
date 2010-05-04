{auto_escape off}
<div class="fr filedata-icon" style="text-align: center;">
    <h4>{str tag=Preview section=artefact.file}</h4>
    <img src="{$previewpath|escape}" alt="">
</div>
<div>
    <div class="fl filedata-icon"><a href="{$downloadpath|escape}"><img src="{$iconpath|escape}" alt="{$description|escape}"></a></div>
    <h4><a href="{$downloadpath|escape}">{$title|escape}</a></h4>
</div>

<table class="filedata">
    <tr><th>{str tag=Type section=artefact.file}:</th><td>{str tag=$artefacttype section=artefact.file}</td></tr>
    <tr><th>{str tag=Description section=artefact.file}:</th><td>{$description|escape}</td></tr>
    <tr><th>{str tag=Owner section=artefact.file}:</th><td>{$owner}</td></tr>
    <tr><th>{str tag=Created section=artefact.file}:</th><td>{$created}</td></tr>
    <tr><th>{str tag=lastmodified section=artefact.file}:</th><td>{$modified}</td></tr>
    <tr><th>{str tag=Size section=artefact.file}:</th><td>{$size|escape}</td></tr>
    <tr><th>{str tag=Download section=artefact.file}:</th><td><a href="{$downloadpath|escape}">{str tag=Download section=artefact.file}</a></td></tr>
</table>
{/auto_escape}
