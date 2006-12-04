{include file="header.tpl"}

{include file="adminmenu.tpl"}

<div class="content">
<div class="box-cnrs"><span class="cnr-tl"><span class="cnr-tr"><span class="cnr-bl"><span class="cnr-br">
	<div class="maincontent">
	
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

	</div>
	</span></span></span></span></div>	
</div>

{include file="footer.tpl"}
