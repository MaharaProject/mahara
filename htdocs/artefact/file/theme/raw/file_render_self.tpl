<div>
    <h4><div class="fl filedata-thumb"><a href="{$downloadpath}"><img src="{$iconpath}" alt="{$description}"></a></div> <a href="{$downloadpath}">{$title}</a></h4>
</div>

<table class="filedata">
    <tr><th>{str tag=Type section=artefact.file}:</th><td>{$filetype}</td></tr>
    <tr><th>{str tag=Description section=artefact.file}:</th><td>{$description}</td></tr>
    <tr class="tags"><th>{str tag=tags}:</th><td>{list_tags owner=$owner tags=$tags}</td></tr>
    <tr><th>{str tag=Owner section=artefact.file}:</th><td>{$ownername}</td></tr>
    <tr><th>{str tag=Created section=artefact.file}:</th><td>{$created}</td></tr>
    <tr><th>{str tag=lastmodified section=artefact.file}:</th><td>{$modified}</td></tr>
    <tr><th>{str tag=Size section=artefact.file}:</th><td>{$size}</td></tr>
    <tr><th>{str tag=Download section=artefact.file}:</th><td><a href="{$downloadpath}">{str tag=Download section=artefact.file}</a></td></tr>
</table>
