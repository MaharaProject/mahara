{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}

<p>
<a href="{$breadcrumbs[0][0]|escape}">{$breadcrumbs[0][1]|escape}</a>
{foreach from=$breadcrumbs[1] item=item}
&raquo <a href="{$item[0]|escape}">{$item[1]|escape}</a>
{/foreach}
</p>

<h2>{$topicsubject|escape}</h2>
<h3>{$heading|escape}</h3>
{$editform}

{include file="columnleftend.tpl"}
{include file="footer.tpl"}
