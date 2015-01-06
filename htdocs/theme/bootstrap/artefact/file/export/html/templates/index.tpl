{include file="export:html:header.tpl"}

<h2>{str tag=Folder section=artefact.file}: {$folder}</h2>

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
        <td><img src="{$rootpath}static/file/theme/raw/static/images/folder.png" alt="{str tag=Folder section=artefact.file}"></td>
        <td><a href="../index.html">{str tag=parentfolder section=artefact.file}</a></td>
        <td>{str tag=parentfolder section=artefact.file}</td>
        <td></td>
        <td></td>
    </tr>
{/if}
{foreach from=$folders item=folder}
    <tr class="{cycle values='r0,r1'}">
        <td><img src="{$rootpath}static/file/theme/raw/static/images/folder.png" alt="{str tag=Folder section=artefact.file}"></td>
        <td><a href="{$folder.path|rawurlencode|safe}/index.html">{$folder.title}</a></td>
        <td>{$folder.description}</td>
        <td>{$folder.size}</td>
        <td>{$folder.ctime}</td>
    </tr>
{/foreach}
{foreach from=$files item=file}
    <tr class="{cycle values='r0,r1'}">
        <td><img src="{$rootpath}static/file/theme/raw/static/images/file.png" alt="{str tag=File section=artefact.file}"></td>
        <td><a href="{$file.path|rawurlencode|safe}">{$file.title}</a></td>
        <td>{$file.description}</td>
        <td>{$file.size}</td>
        <td>{$file.ctime}</td>
    </tr>
{/foreach}
</table>
{/if}

{include file="export:html:footer.tpl"}
