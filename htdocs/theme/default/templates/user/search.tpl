{include file="header.tpl"}

{include file="adminmenu.tpl"}

<div class="content">
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

{include file="footer.tpl"}
