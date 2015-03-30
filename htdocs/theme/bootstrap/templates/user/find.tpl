{include file="header.tpl"}
<div class="ptl pbl">
    {$form|safe}
</div>
{if $results}
<div class="panel panel-default mtl">
    <h2 id="searchresultsheading" class="panel-heading">{str tag=Results}</h2>
    <div id="friendslist" class="panel-body listing">
        {$results.tablerows|safe}
    </div>
</div>
{$results.pagination|safe}
{/if}
{include file="footer.tpl"}
