{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}

<h2>{$heading|escape}</h2>
{include file="interaction:forum:breadcrumbs.tpl" breadcrumbs=$breadcrumbs}

{$editform}

{if $parent}
<h4>{str tag="replyto" section="interaction.forum"}</h4>
{include file="interaction:forum:simplepost.tpl" post=$parent}
{/if}

{include file="columnleftend.tpl"}
{include file="footer.tpl"}
