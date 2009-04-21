{include file="export:html:header.tpl"}

<h2>Index of {$folder}</h2>

{if $folder != '/'}<p><a href="../index.html">Parent Folder</a>{/if}
{if $files || $folders}
<ul>
{foreach from=$folders item=folder}
    <li>Folder: <a href="{$folder->title|rawurlencode|escape}/index.html">{$folder->title|escape}</a></li>
{/foreach}
{foreach from=$files item=file}
    <li>File: <a href="{$file->title|rawurlencode|escape}">{$file->title|escape}</a></li>
{/foreach}
</ul>
{else}
<p>This folder is empty.</p>
{/if}

{include file="export:html:footer.tpl"}
