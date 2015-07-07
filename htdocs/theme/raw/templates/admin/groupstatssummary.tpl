{if !$grouptypecounts}
<p class="lead text-small">{str tag=nogroups section=group}</p>
{else}

{if $groupgraph}
    <div id="site-stats-graph" class="site-stats-graph panel-body pull-right">
        <canvas class="graphcanvas" id="sitestatsgroupgraph"></canvas>
        <script type="application/javascript">
        {literal}
            jQuery(function() {
                fetch_graph_data({'id':'sitestatsgroupgraph','type':'doughnut','graph':'group_type_graph_render'});
            });
        {/literal}
        </script>
    </div>
{/if}
<div>
<h4>{str tag=groupcountsbytype section=admin}:</h4>

    <ul>
    {foreach from=$grouptypecounts item=item}
      <li class="">{str tag=name section=grouptype.$item->grouptype}: {$item->groups}</li>
    {/foreach}
    </ul>
</div>
<div>
<h4>{str tag=groupcountsbyjointype section=admin}:</h4>
    <ul>
    {foreach from=$jointypecounts item=item}
      <li>{str tag=membershiptype.$item->jointype section=group}: {$item->groups}</li>
    {/foreach}
    </ul>
</div>

{/if}
