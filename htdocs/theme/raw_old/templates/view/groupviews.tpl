{include file="header.tpl"}

{if $views}
    <div id="groupviews" class="view-container">
        <div class="panel panel-default">
            <h2 class="panel-heading hidefocus" tabindex="-1">Results</h2>
            <div class="list-group">
                {$viewresults|safe}
            </div>
        </div>
    </div>
    <div>{$pagination|safe}</div>
{else}
<div class="no-results">{str tag="noviewstosee" section="group"}</div>
{/if}

{include file="footer.tpl"}
