{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
<h2>{$heading|escape}</h2>
{include file="group/simplegroup.tpl" group=$group}
{$form}
{include file="columnleftend.tpl"}
{include file="footer.tpl"}
