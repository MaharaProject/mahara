{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
                <h2>{$group->name|escape}</h2>

{include file="group/tabstart.tpl" current="members"}

                <p>Member listing goes here!</p>

{include file="group/tabend.tpl"}

{include file="columnleftend.tpl"}
{include file="footer.tpl"}
