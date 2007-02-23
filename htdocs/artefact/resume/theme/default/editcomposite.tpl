{include file="header.tpl"}

<div id="column-right">
{include file="sidebar.tpl"}
</div>
{include file="columnleftstart.tpl"}
<h3>{str tag='editing'}: {str tag=$composite section='artefact.resume'}</h3>
<a href="{$WWWROOT}artefact/resume">{str tag='backtoresume' section='artefact.resume'}</a>
{$compositeform}

{include file="columnleftend.tpl"}
{include file="footer.tpl"}
