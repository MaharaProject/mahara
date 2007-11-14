{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
                <h2>{$group->name|escape}</h2>
                <h3>{$heading|escape}</h3>
                {$form}
{include file="columnleftend.tpl"}
{include file="footer.tpl"}

