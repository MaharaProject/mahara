{include file="export:html:header.tpl"}

{if $collectionmenu}
<div class="breadcrumbs collection">
   <ul>
     <li class="collectionname">{$collectionname}</li>
{foreach from=$collectionmenu item=item}
     | <li{if $item.id == $viewid} class="selected"{/if}><a href="{$rootpath}views/{$item.url}">{$item.text}</a></li>
{/foreach}
   </ul>
</div>
<div class="cb"></div>
{/if}

<p id="view-description">{$viewdescription|clean_html|safe}</p>

{$view|safe}

{include file="export:html:footer.tpl"}
