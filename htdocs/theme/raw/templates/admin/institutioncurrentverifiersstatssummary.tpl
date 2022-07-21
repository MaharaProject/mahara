{if $viewcount == 0}
<p>{str tag=noviews2 section=view}</p>
{/if}
<div class="card-body">
    <canvas class="graphcanvas" id="sitestatscurrentverifiersgraph" width="300" height="200"></canvas>
    <script>
    {literal}
    jQuery(function() {
        fetch_graph_data({'id':'sitestatscurrentverifiersgraph',
                          'type':'line',
                          'graph':'institution_current_verifiers_graph_render',
                          'extradata': {'institution': '{/literal}{$institution}{literal}'}
                         });
    });
    {/literal}
    </script>
</div>
