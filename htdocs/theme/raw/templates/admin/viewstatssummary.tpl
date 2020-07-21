{if $viewcount == 0}
<p class="lead small-text">{str tag=noviews1 section=view}</p>
{/if}
{if $blocktypecounts}
    <div class="card-body">
        <h3>{str tag=blockcountsbytype section=admin}</h3>
        <canvas class="graphcanvas" id="sitestatsblocktypesgraph"></canvas>
        <script>
        {literal}
        jQuery(function() {
            fetch_graph_data({'id':'sitestatsblocktypesgraph','type':'horizontalbar','graph':'block_type_graph',
                'extradata': {
                    'configs': {
                        'showlegendcallback': false
                    }
                }
            });
        });
        {/literal}
        </script>
    </div>
{/if}
{if $viewtypes}
    <div class="card-body">
        <h3>{str tag=viewsbytype section=admin}</h3>
        <canvas class="graphcanvas" id="sitestatsviewtypesgraph"></canvas>
        <script>
        {literal}
        jQuery(function() {
            fetch_graph_data({'id':'sitestatsviewtypesgraph','type':'doughnut','graph':'view_type_graph_render'});
        });
        {/literal}
        </script>
    </div>
{/if}
