{if $viewcount == 0}
<p>{str tag=noviews1 section=view}</p>
{/if}
{if $blocktypecounts}
    <h3>{str tag=blockcountsbytype section=admin}</h3>
    <ul class="list-group list-group-lite unstyled">
    {foreach from=$blocktypecounts item=item}
        <li class="list-group-item">{str tag=title section=blocktype.$item->langsection}: {$item->blocks}</li>
    {/foreach}
    </ul>
    </p>
{/if}
{if $viewtypes}
    <div class="card-body">
        <h3>{str tag=viewsbytype section=admin}</h3>
        <canvas class="graphcanvas" id="sitestatsviewtypesgraph" width="300" height="200"></canvas>
        <script>
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
