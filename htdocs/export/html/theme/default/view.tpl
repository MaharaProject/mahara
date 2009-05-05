{include file="export:html:header.tpl"}

<h2 id="view-title">{$viewtitle|escape}{if $ownername} {str tag=by section=view} {$ownername|escape}{/if}</h2>

<p id="view-description">{$viewdescription}</p>

{$view}

{include file="export:html:footer.tpl"}
