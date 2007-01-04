{include file="header.tpl"}

<div id="column-right">
{include file="adminmenu.tpl"}
</div>

{include file="columnleftstart.tpl"}
    			<div class="searchform">
				<h2>{str tag="search"}</h2>
				{$SEARCHFORM}
				</div>
				{if ($QUERYPOSTED)}
				<div id="results">
				{else}
				<div id="results" style="display:none;">
				{/if}
				<h2>{str tag="results"}</h2>
				<table id="searchresults">
					<tbody>
					</tbody>
				</table>
				</div>
{include file="columnleftend.tpl"}
{include file="footer.tpl"}
