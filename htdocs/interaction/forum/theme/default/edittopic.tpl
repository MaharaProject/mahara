{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}

<p>
{foreach from=$breadcrumbs item=item}
<a href="{$item[0]}">{$item[1]|escape}</a>
{/foreach}
</p>

<h2>{$forum|escape}</h2>
<h3>{$heading|escape}</h3>
{$editform}

{include file="columnleftend.tpl"}
{include file="footer.tpl"}
