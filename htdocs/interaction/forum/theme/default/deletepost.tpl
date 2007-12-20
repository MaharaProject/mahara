{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}

<h2>{$heading|escape}</h2>

{include file="interaction:forum:breadcrumbs.tpl" breadcrumbs=$breadcrumbs}

<div class="message">{$deleteform}</div>

{include file="interaction:forum:simplepost.tpl" post=$post}

{include file="columnleftend.tpl"}
{include file="footer.tpl"}
