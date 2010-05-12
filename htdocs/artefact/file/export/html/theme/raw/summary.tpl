{auto_escape off}
<p><a href="{$rootpath}files/file/index.html">Browse your file collection</a></p>
<ul>
    <li>{str tag=Files section=artefact.file}: {$filecount|escape}</li>
    <li>{str tag=Folders section=artefact.file}: {$foldercount|escape}</li>
    <li>{str tag=spaceused section=artefact.file}: {$spaceused|display_size|escape}</li>
</ul>
{/auto_escape}
