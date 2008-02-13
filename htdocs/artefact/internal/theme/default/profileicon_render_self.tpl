<div class="fr filedata-icon" style="text-align: center;">
    <h4>{str tag=Preview section=artefact.internal}</h4>
    <img src="{$previewpath|escape}" alt="">
</div>
<div>
    <div class="fl filedata-icon"><a href="{$downloadpath|escape}"><img src="{$iconpath|escape}" alt="{$description|escape}"></a></div>
    <h4><a href="{$downloadpath|escape}">{$title|escape}</a></h4>
</div>

<table class="filedata">
    <tr><th>{str tag=Type section=artefact.internal}:</th><td>{str tag=$artefacttype section=artefact.internal}</td></tr>
    <tr><th>{str tag=Description section=artefact.internal}:</th><td>{$description|escape}</td></tr>
    <tr><th>{str tag=Owner section=artefact.internal}:</th><td>{$owner}</td></tr>
    <tr><th>{str tag=Created section=artefact.internal}:</th><td>{$created}</td></tr>
    <tr><th>{str tag=lastmodified section=artefact.internal}:</th><td>{$modified}</td></tr>
    <tr><th>{str tag=Size section=artefact.internal}:</th><td>{$size|escape}</td></tr>
    <tr><th>{str tag=Download section=artefact.internal}:</th><td><a href="{$downloadpath|escape}">{str tag=Download section=artefact.internal}</a></td></tr>
</table>
