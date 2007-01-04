{include file="header.tpl"}

<div id="column-right">
{include file="adminmenu.tpl"}
</div>

{include file="columnleftstart.tpl"}
{if $EDITMODE}
			<h2>{str tag="editmetadata" section="view"}</h2>
{else}
			<h2>{str tag="createviewstep1" section="view"}</h2>
{/if}
			{$createview1}
{include file="columnleftend.tpl"}

{include file="footer.tpl"}
