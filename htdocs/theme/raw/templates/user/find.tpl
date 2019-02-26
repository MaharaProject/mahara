{include file="header.tpl"}
    {$form|safe}
{if $results}
<div class="card view-container">
    <h2 id="searchresultsheading" class="card-header">{str tag=Results}</h2>
    <div id="friendslist" class="list-group">
        {$results.tablerows|safe}
    </div>
</div>
{$results.pagination|safe}
{/if}
{include file="footer.tpl"}
