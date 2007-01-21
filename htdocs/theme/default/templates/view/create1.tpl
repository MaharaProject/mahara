{include file="header.tpl"}

{include file="columnfullstart.tpl"}
{if $EDITMODE}
			<h2>{str tag="editmetadata" section="view"}</h2>
{else}
			<h2>{str tag="createviewstep1" section="view"}</h2>
{/if}
			{$createview1}
{include file="columnfullend.tpl"}

{include file="footer.tpl"}
