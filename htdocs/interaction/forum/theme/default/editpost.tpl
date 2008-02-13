{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}

<h2>{$heading|escape}</h2>
{include file="interaction:forum:breadcrumbs.tpl" breadcrumbs=$breadcrumbs}

{$editform}

<h4>{str tag="replyto" section="interaction.forum"}</h4>
{include file="interaction:forum:simplepost.tpl" post=$parent groupowner=$parent->groupowner}

{include file="columnleftend.tpl"}
{include file="footer.tpl"}
