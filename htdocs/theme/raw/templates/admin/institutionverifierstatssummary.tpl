{if $verifiertotal}
    <div class="card-body">
        {if $verifierportfolios}<div>{$verifierportfolios|safe}</div>{/if}
        <canvas class="graphcanvas" id="sitestatsverifiergraph" width="300" height="200"></canvas>
        <script>
        {literal}
        jQuery(function() {
            fetch_graph_data({'id':'sitestatsverifiergraph',
                              'type':'doughnut',
                              'graph':'institution_verifier_graph_render',
                              'extradata': {'institution': '{/literal}{$institution}{literal}'}
                             });
        });
        {/literal}
        </script>
    </div>
{else}
<p>{str tag=noviews2 section=view}</p>
{/if}
