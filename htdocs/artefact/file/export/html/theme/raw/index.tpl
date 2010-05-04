{auto_escape off}
{include file="export:html:header.tpl"}

<h2>{str tag=Folder section=artefact.file}: {$folder|escape}</h2>

{if $folder == '/' && !$folders && !$files}
<p>{str tag=nofilesfound section=artefact.file}</p>
{else}
<table id="files" cellspacing="0">
    <colgroup style="width: 3em;">
    <colgroup>
    <colgroup>
    <colgroup style="width: 3em;">
    <colgroup style="width: 3em;">
    <tr>
        <th></th>
        <th>{str tag=Name section=artefact.file}</th>
        <th>{str tag=Description section=artefact.file}</th>
        <th>{str tag=Size section=artefact.file}</th>
        <th>{str tag=Date section=artefact.file}</th>
    </tr>
{if $folder != '/'}
    <tr class="{cycle values='r0,r1'}">
        <td><img src="{$rootpath}static/file/theme/default/static/images/folder.gif" alt="{str tag=Folder section=artefact.file}"></td>
        <td><a href="../index.html">{str tag=parentfolder section=artefact.file}</a></td>
        <td>{str tag=parentfolder section=artefact.file}</td>
        <td></td>
        <td></td>
    </tr>
{/if}
{foreach from=$folders item=folder}
    <tr class="{cycle values='r0,r1'}">
        <td><img src="{$rootpath}static/file/theme/default/static/images/folder.gif" alt="{str tag=Folder section=artefact.file}"></td>
        <td><a href="{$folder.path|rawurlencode|escape}/index.html">{$folder.title|escape}</a></td>
        <td>{$folder.description|escape}</td>
        <td>{$folder.size|escape}</td>
        <td>{$folder.ctime|escape}</td>
    </tr>
{/foreach}
{foreach from=$files item=file}
    <tr class="{cycle values='r0,r1'}">
        <td><img src="{$rootpath}static/file/theme/default/static/images/file.gif" alt="{str tag=File section=artefact.file}"></td>
        <td><a href="{$file.path|rawurlencode|escape}">{$file.title|escape}</a></td>
        <td>{$file.description|escape}</td>
        <td>{$file.size|escape}</td>
        <td>{$file.ctime|escape}</td>
    </tr>
{/foreach}
</table>
{/if}

{include file="export:html:footer.tpl"}
{/auto_escape}
