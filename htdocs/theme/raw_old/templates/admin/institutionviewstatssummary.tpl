{if $viewcount == 0}
<p>{str tag=noviews section=view}</p>
{/if}
{if $blocktypecounts}
<p>{str tag=blockcountsbytype section=admin}:
<ul>
{foreach from=$blocktypecounts item=item}
  <li>{str tag=title section=blocktype.$item->langsection}: {$item->blocks}</li>
{/foreach}
</ul>
</p>
{/if}
{if $viewtypes}
    <div id="site-stats-graph" class="site-stats-graph">
        <canvas class="graphcanvas" id="sitestatsviewtypesgraph" width="300" height="200"></canvas>
        <script type="application/javascript">
        {literal}
        jQuery(function() {
            fetch_graph_data({'id':'sitestatsviewtypesgraph',
                              'type':'doughnut',
                              'graph':'institution_view_type_graph_render',
                              'extradata': {'institution': '{/literal}{$institution}{literal}'}
                             });
        });
        {/literal}
        </script>
    </div>
{/if}
