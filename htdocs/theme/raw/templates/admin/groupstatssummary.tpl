{if !$grouptypecounts}
<p class="lead text-small">{str tag=nogroups section=group}</p>
{else}
<div>
<h3>{str tag=groupcountsbytype section=admin}</h3>
    <ul class="list-group list-group-lite unstyled">
    {foreach from=$grouptypecounts item=item}
      <li class="list-group-item">{str tag=name section=grouptype.$item->grouptype}: {$item->groupcount}</li>
    {/foreach}
    </ul>
</div>
<div>
<h3>{str tag=groupcountsbyjointype section=admin}</h3>
    <ul class="list-group list-group-lite unstyled">
    {foreach from=$jointypecounts item=item}
      <li class="list-group-item">{str tag=membershiptype.$item->jointype section=group}: {$item->groupcount}</li>
    {/foreach}
    </ul>
</div>
    {if $groupgraph}
    <h3>{str tag=groupsbytype section=statistics}</h3>
    <div class="card-body">
        <canvas class="graphcanvas" id="sitestatsgroupgraph"></canvas>
        <script>
        {literal}
            jQuery(function() {
                fetch_graph_data({'id':'sitestatsgroupgraph','type':'doughnut','graph':'group_type_graph_render'});
            });
        {/literal}
        </script>
    </div>
    {/if}
{/if}
