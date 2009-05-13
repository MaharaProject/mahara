{include file="header.tpl"}

<div id="column-right">
{include file="sidebar.tpl"}
</div>
{include file="columnleftstart.tpl"}
<h3>{str tag='editing'}: {str tag=$composite section='artefact.resume'}</h3>
{$compositeform}

{include file="columnleftend.tpl"}
{include file="footer.tpl"}
