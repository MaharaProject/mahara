{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}

<h2>{$topicsubject|escape} - {$heading|escape}</h2>
{include file="interaction:forum:breadcrumbs.tpl" breadcrumbs=$breadcrumbs}

{if $parent}
<h4>{str tag="replyto" section="interaction.forum"}</h4>
{include file="interaction:forum:simplepost.tpl" post=$parent}
{/if}

{$editform}

{include file="columnleftend.tpl"}
{include file="footer.tpl"}
