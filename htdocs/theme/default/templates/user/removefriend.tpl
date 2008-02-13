{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}

<h2>{$heading}</h2>

{include file="user/simpleuser.tpl" user=$user}

{$form}

{include file="columnleftend.tpl"}
{include file="footer.tpl"}
