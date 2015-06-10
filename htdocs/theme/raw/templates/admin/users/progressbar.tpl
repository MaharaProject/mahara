{include file="header.tpl"}
<div class="panel panel-body">
    <div id="progressbar-construct">
	    <p>{str tag="profilecompletenessdesc1" section="admin"}</p>
	    <p>{str tag="profilecompletenesspreview" section="admin"}</p>
	    {if !$enabled}
	    <p>{str tag=progressbardisablednote section=admin args=$WWWROOT}</p>
	    {/if}
	    {$institutionselector|safe}
	    {$progressbarform|safe}
    </div>
</div>
{include file="footer.tpl"}
